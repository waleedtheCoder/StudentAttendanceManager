<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function view(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin()
            || $assignment->course->teacher_id === $user->id
            || $assignment->course->students()->where('users.id', $user->id)->exists();
    }

    public function create(User $user, \App\Models\Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || $assignment->course->teacher_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin() || $assignment->course->teacher_id === $user->id;
    }
}
