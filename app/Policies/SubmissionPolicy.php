<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /** Only enrolled students can submit, and only for their own submission. */
    public function create(User $user, Assignment $assignment): bool
    {
        return $user->isStudent()
            && $assignment->course->students()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, Submission $submission): bool
    {
        return $user->isAdmin()
            || $submission->student_id === $user->id
            || $submission->assignment->course->teacher_id === $user->id;
    }

    /** Grading is teacher/admin only. */
    public function grade(User $user, Submission $submission): bool
    {
        return $user->isAdmin() || $submission->assignment->course->teacher_id === $user->id;
    }
}
