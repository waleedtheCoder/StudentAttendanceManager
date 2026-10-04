<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /** Attendance history for a course. Teacher sees everyone, student sees only their own rows. */
    public function index(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $user = $request->user();

        $query = $course->attendances()->with('student')->orderByDesc('date');

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
        }

        $attendances = $query->get()->groupBy(fn (Attendance $a) => $a->date->toDateString());

        return view('attendance.index', compact('course', 'attendances'));
    }

    /** Show the "mark attendance" form for a given date (defaults to today). */
    public function create(Request $request, Course $course): View
    {
        $this->authorize('mark', [Attendance::class, $course]);

        $date = $request->query('date', now()->toDateString());

        $students = $course->students()->orderBy('name')->get();

        $existing = $course->attendances()
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('attendance.create', compact('course', 'students', 'existing', 'date'));
    }

    /** Save attendance for every student on the given date. */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('mark', [Attendance::class, $course]);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:present,absent,late'],
        ]);

        foreach ($data['statuses'] as $studentId => $status) {
            $course->attendances()->updateOrCreate(
                ['student_id' => $studentId, 'date' => $data['date']],
                ['status' => $status, 'marked_by' => $request->user()->id]
            );
        }

        return redirect()
            ->route('courses.attendance.index', $course)
            ->with('status', 'Attendance saved for '.$data['date'].'.');
    }
}
