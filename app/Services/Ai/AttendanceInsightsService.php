<?php

namespace App\Services\Ai;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\User;
use App\Services\Ai\Outputs\AttendanceInsights;
use App\Services\Ai\Outputs\FlaggedStudent;
use Illuminate\Support\Facades\Cache;

/** Summarises a course's attendance and flags students whose attendance is slipping. */
class AttendanceInsightsService
{
    private const SYSTEM = <<<'TXT'
        You help a teacher keep track of class attendance. You receive a course's attendance records and write a short,
        factual overview for the teacher, then flag the students who need attention.

        Flag a student when their attendance is clearly worse than the class (many absences, a run of recent absences,
        or frequent lateness) or is trending downward. Do not flag students who have only one or two isolated absences
        in an otherwise good record. Cite the actual numbers or dates. Keep suggested actions practical and kind; never
        speculate about a student's personal circumstances. Use only the student_id values present in the data.
        TXT;

    public function __construct(private AiAssistant $ai) {}

    public static function cacheKey(Course $course): string
    {
        return "ai:attendance-insights:{$course->id}";
    }

    /** Most recently generated insights for the course, if any. */
    public static function cached(Course $course): ?array
    {
        return Cache::get(self::cacheKey($course));
    }

    /**
     * @return array{summary: string, flagged: list<array{student_id: int, name: string, concern: string, suggested_action: string}>, generated_at: string}
     */
    public function generate(Course $course): array
    {
        $students = $course->students()->orderBy('name')->get();
        $records = $course->attendances()->orderBy('date')->get();
        $sessions = $records->pluck('date')->map->toDateString()->unique()->count();

        if ($sessions === 0) {
            throw new AiException('There is no attendance recorded for this course yet.');
        }

        $byStudent = $records->groupBy('student_id');

        $data = $students->map(function (User $student) use ($byStudent) {
            $mine = $byStudent->get($student->id, collect());

            return [
                'student_id' => $student->id,
                'name' => $student->name,
                'present' => $mine->where('status', 'present')->count(),
                'late' => $mine->where('status', 'late')->count(),
                'absent' => $mine->where('status', 'absent')->count(),
                'history' => $mine->map(fn (Attendance $a) => $a->date->toDateString().' '.$a->status)->values(),
            ];
        })->values();

        $prompt = "Course: {$course->code} - {$course->title}\n"
            ."Class sessions recorded: {$sessions}\n"
            .'Today: '.now()->toDateString()."\n\n"
            ."<attendance_data>\n".json_encode($data, JSON_PRETTY_PRINT)."\n</attendance_data>";

        $result = $this->ai->structured(self::SYSTEM, [['type' => 'text', 'text' => $prompt]], AttendanceInsights::class);

        // Keep only students who are actually on this course's roster.
        $names = $students->pluck('name', 'id');

        $insights = [
            'summary' => $result->summary,
            'flagged' => collect($result->flaggedStudents)
                ->filter(fn (FlaggedStudent $f) => $names->has($f->studentId))
                ->map(fn (FlaggedStudent $f) => [
                    'student_id' => $f->studentId,
                    'name' => $names[$f->studentId],
                    'concern' => $f->concern,
                    'suggested_action' => $f->suggestedAction,
                ])
                ->values()
                ->all(),
            'generated_at' => now()->toIso8601String(),
        ];

        Cache::put(self::cacheKey($course), $insights, now()->addWeek());

        return $insights;
    }
}
