<?php

namespace App\Services\Ai;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;

/**
 * Answers plain-English questions about a teacher's classes.
 *
 * Rather than letting the model write SQL, we hand it a read-only snapshot of exactly the
 * courses this user is allowed to see, so it can't reach other teachers' data.
 */
class ClassQuestionAnswerer
{
    private const SYSTEM = <<<'TXT'
        You answer a teacher's questions about their classes using only the course data provided. Answer directly and
        concisely in plain text (no Markdown headings or tables). When listing students, include the numbers that
        justify each one. If the data cannot answer the question, say so and say what information is missing. Never
        invent students, dates, or grades.
        TXT;

    public function __construct(private AiAssistant $ai) {}

    public function ask(User $user, string $question): string
    {
        $courses = ($user->isAdmin() ? Course::query() : $user->coursesTaught())
            ->with(['students', 'attendances', 'assignments.submissions.student'])
            ->orderBy('code')
            ->get();

        if ($courses->isEmpty()) {
            throw new AiException('You don\'t have any courses yet, so there is nothing to ask about.');
        }

        $snapshot = $courses->map(fn (Course $course) => $this->describeCourse($course))->values();

        $prompt = 'Today: '.now()->toDateString()."\n\n"
            ."<course_data>\n".json_encode($snapshot, JSON_PRETTY_PRINT)."\n</course_data>\n\n"
            ."<question>\n{$question}\n</question>";

        return $this->ai->text(self::SYSTEM, [['type' => 'text', 'text' => $prompt]]);
    }

    /** @return array<string, mixed> */
    private function describeCourse(Course $course): array
    {
        $attendanceByStudent = $course->attendances->groupBy('student_id');

        return [
            'code' => $course->code,
            'title' => $course->title,
            'class_sessions_recorded' => $course->attendances->pluck('date')->map->toDateString()->unique()->count(),
            'students' => $course->students->map(function (User $student) use ($attendanceByStudent) {
                $records = $attendanceByStudent->get($student->id, collect());

                return [
                    'name' => $student->name,
                    'email' => $student->email,
                    'present' => $records->where('status', 'present')->count(),
                    'late' => $records->where('status', 'late')->count(),
                    'absent' => $records->where('status', 'absent')->count(),
                    'absent_dates' => $records->where('status', 'absent')->map(fn ($a) => $a->date->toDateString())->values(),
                    'late_dates' => $records->where('status', 'late')->map(fn ($a) => $a->date->toDateString())->values(),
                ];
            })->values(),
            'assignments' => $course->assignments->map(fn (Assignment $assignment) => [
                'title' => $assignment->title,
                'due' => $assignment->due_date->toDateTimeString(),
                'submissions' => $assignment->submissions->map(fn (Submission $s) => [
                    'student' => $s->student->name,
                    'submitted_at' => $s->submitted_at->toDateTimeString(),
                    'late' => $s->submitted_at->greaterThan($assignment->due_date),
                    'grade' => $s->grade,
                ])->values(),
                'not_submitted' => $course->students
                    ->whereNotIn('id', $assignment->submissions->pluck('student_id'))
                    ->pluck('name')
                    ->values(),
            ])->values(),
        ];
    }
}
