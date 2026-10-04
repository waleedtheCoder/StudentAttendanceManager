<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $courses = Course::with('teacher')->withCount('students')->latest()->get();
        } elseif ($user->isTeacher()) {
            $courses = $user->coursesTaught()->withCount('students')->latest()->get();
        } else {
            $courses = $user->enrolledCourses()->with('teacher')->get();
        }

        $availableCourses = $user->isStudent()
            ? Course::whereDoesntHave('students', fn ($q) => $q->where('users.id', $user->id))->with('teacher')->get()
            : collect();

        return view('courses.index', compact('courses', 'availableCourses'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Course::class);

        return view('courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $course = $request->user()->coursesTaught()->create($data);

        return redirect()->route('courses.show', $course)->with('status', 'Course created.');
    }

    public function show(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $course->load(['teacher', 'students', 'assignments']);

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code,'.$course->id],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $course->update($data);

        return redirect()->route('courses.show', $course)->with('status', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course deleted.');
    }

    /** Student self-enrolls into a course. */
    public function enroll(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);

        $course->students()->syncWithoutDetaching([$request->user()->id]);

        return back()->with('status', 'Enrolled in '.$course->title.'.');
    }

    /** Student drops a course. */
    public function unenroll(Request $request, Course $course): RedirectResponse
    {
        abort_unless($request->user()->isStudent(), 403);

        $course->students()->detach($request->user()->id);

        return back()->with('status', 'Left '.$course->title.'.');
    }
}
