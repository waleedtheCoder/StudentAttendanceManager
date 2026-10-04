# Student Attendance Manager

A role based student management system built with Laravel. Teachers create courses, mark attendance, and grade assignments. Students enroll in courses, check their attendance history, and submit assignments.

## Features

* Role based access control for admin, teacher, and student accounts, enforced with Laravel policies and a custom middleware
* Course management including creation, editing, and self service enrollment for students
* Attendance marking per course and per date, with a history view scoped to what each role is allowed to see
* Assignments with file upload submissions and teacher grading
* Eloquent relationships covering courses, enrollments, attendance records, assignments, and submissions

## Tech Stack

* Laravel 11
* Blade with Laravel Breeze for authentication
* Tailwind CSS
* SQLite for local development

## Setup

```
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

If PHP or Composer are not on your system PATH, call them with their full path, for example `C:\xampp\php\php.exe artisan serve`.

## Demo Accounts

The seeder creates one account per role. Password for all of them is `password`.

* admin@example.com
* teacher@example.com
* student@example.com

## Project Structure

* `app/Models` holds the Eloquent models: User, Course, Enrollment, Attendance, Assignment, Submission
* `app/Policies` holds the authorization rules for each model
* `app/Http/Controllers` holds the request handling logic, split by resource
* `resources/views` holds the Blade templates, grouped by feature area

## Testing

Run the default Laravel test suite with:

```
php artisan test
```
