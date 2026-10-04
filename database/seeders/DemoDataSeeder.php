<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $teacher = User::factory()->create([
            'name' => 'Dr. Amelia Khan',
            'email' => 'teacher@example.com',
            'role' => 'teacher',
        ]);

        $students = User::factory(5)->create([
            'role' => 'student',
        ]);
        $students->prepend(User::factory()->create([
            'name' => 'Sam Student',
            'email' => 'student@example.com',
            'role' => 'student',
        ]));

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'code' => 'CS101',
            'title' => 'Introduction to Programming',
            'description' => 'Fundamentals of programming using PHP and web basics.',
        ]);

        $course->students()->attach($students->pluck('id'));

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'title' => 'Assignment 1: Hello World',
            'description' => 'Submit a short program that prints "Hello, World!" along with a brief write-up.',
            'due_date' => now()->addWeek(),
        ]);

        foreach ($students as $i => $student) {
            $course->attendances()->create([
                'student_id' => $student->id,
                'marked_by' => $teacher->id,
                'date' => now()->subDay(),
                'status' => $i % 4 === 0 ? 'absent' : ($i % 3 === 0 ? 'late' : 'present'),
            ]);
        }

        $this->command->info('Demo users — admin@example.com / teacher@example.com / student@example.com (password: password)');
    }
}
