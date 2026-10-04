<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function create(Course $course): View
    {
        $this->authorize('create', [Assignment::class, $course]);

        return view('assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('create', [Assignment::class, $course]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
        ]);

        $assignment = $course->assignments()->create($data);

        return redirect()->route('assignments.show', $assignment)->with('status', 'Assignment created.');
    }

    public function show(Request $request, Assignment $assignment): View
    {
        $this->authorize('view', $assignment);

        $assignment->load('course');

        $user = $request->user();

        if ($user->isStudent()) {
            $mySubmission = $assignment->submissions()->where('student_id', $user->id)->first();

            return view('assignments.show-student', compact('assignment', 'mySubmission'));
        }

        $submissions = $assignment->submissions()->with('student')->get();

        return view('assignments.show-teacher', compact('assignment', 'submissions'));
    }

    public function edit(Assignment $assignment): View
    {
        $this->authorize('update', $assignment);

        return view('assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date'],
        ]);

        $assignment->update($data);

        return redirect()->route('assignments.show', $assignment)->with('status', 'Assignment updated.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $course = $assignment->course;
        $assignment->delete();

        return redirect()->route('courses.show', $course)->with('status', 'Assignment deleted.');
    }
}
