<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /** Any authenticated user can see the course listing (scoped per-role in the controller). */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return $user->isAdmin()
            || $course->teacher_id === $user->id
            || $course->students()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isTeacher();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }

    /** Whether the user can manage attendance/assignments/enrollment for this course. */
    public function manage(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }
}
