<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\User;

class AttendancePolicy
{
    /** Only the teacher of the course (or an admin) can mark attendance. */
    public function mark(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin()
            || $attendance->course->teacher_id === $user->id
            || $attendance->student_id === $user->id;
    }
}
