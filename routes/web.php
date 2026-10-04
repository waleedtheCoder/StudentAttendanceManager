<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('courses', CourseController::class);
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::delete('/courses/{course}/enroll', [CourseController::class, 'unenroll'])->name('courses.unenroll');

    Route::get('/courses/{course}/attendance', [AttendanceController::class, 'index'])->name('courses.attendance.index');
    Route::get('/courses/{course}/attendance/mark', [AttendanceController::class, 'create'])->name('courses.attendance.create');
    Route::post('/courses/{course}/attendance', [AttendanceController::class, 'store'])->name('courses.attendance.store');

    Route::get('/courses/{course}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
});

require __DIR__.'/auth.php';
