<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Services\Ai\AiAssistant;
use App\Services\Ai\AiException;
use App\Services\Ai\AttendanceInsightsService;
use App\Services\Ai\Outputs\AttendanceInsights;
use App\Services\Ai\Outputs\FeedbackDraft;
use App\Services\Ai\Outputs\FlaggedStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AiFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $student;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->student = User::factory()->create(['role' => 'student', 'name' => 'Sam Student']);

        $this->course = Course::create([
            'teacher_id' => $this->teacher->id,
            'code' => 'CS101',
            'title' => 'Intro to Programming',
        ]);
        $this->course->students()->attach($this->student->id);
    }

    private function fakeAi(): Mockery\MockInterface
    {
        $ai = Mockery::mock(AiAssistant::class);
        $ai->shouldReceive('isConfigured')->andReturn(true);
        $this->app->instance(AiAssistant::class, $ai);

        return $ai;
    }

    private function markAttendance(string $date, string $status): void
    {
        $this->course->attendances()->create([
            'student_id' => $this->student->id,
            'marked_by' => $this->teacher->id,
            'date' => $date,
            'status' => $status,
        ]);
    }

    private function submission(UploadedFile $file): Submission
    {
        $assignment = Assignment::create([
            'course_id' => $this->course->id,
            'title' => 'Hello World',
            'description' => 'Print hello world.',
            'due_date' => now()->addDay(),
        ]);

        return $assignment->submissions()->create([
            'student_id' => $this->student->id,
            'file_path' => $file->store('submissions/'.$assignment->id, 'local'),
            'submitted_at' => now(),
        ]);
    }

    public function test_teacher_can_generate_attendance_insights(): void
    {
        $this->markAttendance('2026-09-01', 'absent');
        $this->markAttendance('2026-09-02', 'absent');

        $flagged = new FlaggedStudent;
        $flagged->studentId = $this->student->id;
        $flagged->concern = 'Absent for both recorded sessions.';
        $flagged->suggestedAction = 'Check in with Sam.';

        $hallucinated = new FlaggedStudent;
        $hallucinated->studentId = 99999;
        $hallucinated->concern = 'Not a real student.';
        $hallucinated->suggestedAction = 'n/a';

        $result = new AttendanceInsights;
        $result->summary = 'Attendance is low.';
        $result->flaggedStudents = [$flagged, $hallucinated];

        $this->fakeAi()->shouldReceive('structured')
            ->once()
            ->withArgs(fn ($system, $content, $format) => $format === AttendanceInsights::class
                && str_contains($content[0]['text'], 'Sam Student')
                && str_contains($content[0]['text'], '2026-09-01 absent'))
            ->andReturn($result);

        $this->actingAs($this->teacher)
            ->post(route('courses.attendance.insights', $this->course))
            ->assertRedirect(route('courses.attendance.index', $this->course));

        $cached = AttendanceInsightsService::cached($this->course);
        $this->assertSame('Attendance is low.', $cached['summary']);
        $this->assertCount(1, $cached['flagged'], 'students not on the roster are dropped');
        $this->assertSame('Sam Student', $cached['flagged'][0]['name']);

        $this->actingAs($this->teacher)
            ->get(route('courses.attendance.index', $this->course))
            ->assertSee('Attendance is low.')
            ->assertSee('Check in with Sam.');
    }

    public function test_insights_need_attendance_records(): void
    {
        $this->fakeAi()->shouldNotReceive('structured');

        $this->actingAs($this->teacher)
            ->from(route('courses.attendance.index', $this->course))
            ->post(route('courses.attendance.insights', $this->course))
            ->assertSessionHas('ai_error', 'There is no attendance recorded for this course yet.');
    }

    public function test_students_cannot_generate_or_see_insights(): void
    {
        $this->fakeAi()->shouldNotReceive('structured');
        $this->markAttendance('2026-09-01', 'present');

        $this->actingAs($this->student)
            ->post(route('courses.attendance.insights', $this->course))
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('courses.attendance.index', $this->course))
            ->assertOk()
            ->assertDontSee('AI Attendance Insights');
    }

    public function test_teacher_gets_feedback_draft_for_text_submission(): void
    {
        Storage::fake('local');
        $submission = $this->submission(UploadedFile::fake()->createWithContent('hello.txt', 'echo "Hello, World!";'));

        $draft = new FeedbackDraft;
        $draft->suggestedGrade = 88;
        $draft->feedback = 'Nice work.';

        $this->fakeAi()->shouldReceive('structured')
            ->once()
            ->withArgs(fn ($system, $content, $format) => $format === FeedbackDraft::class
                && $content[0]['type'] === 'text'
                && str_contains($content[0]['text'], 'Hello, World!')
                && str_contains($content[1]['text'], 'Print hello world.'))
            ->andReturn($draft);

        $this->actingAs($this->teacher)
            ->postJson(route('submissions.feedback-draft', $submission))
            ->assertOk()
            ->assertExactJson(['grade' => 88, 'feedback' => 'Nice work.']);
    }

    public function test_pdf_submissions_are_sent_as_documents(): void
    {
        Storage::fake('local');
        $submission = $this->submission(UploadedFile::fake()->createWithContent('essay.pdf', "%PDF-1.4\n%%EOF"));

        $draft = new FeedbackDraft;
        $draft->suggestedGrade = 70;
        $draft->feedback = 'OK.';

        $this->fakeAi()->shouldReceive('structured')
            ->once()
            ->withArgs(fn ($system, $content) => $content[0]['type'] === 'document'
                && $content[0]['source']['mediaType'] === 'application/pdf')
            ->andReturn($draft);

        $this->actingAs($this->teacher)
            ->postJson(route('submissions.feedback-draft', $submission))
            ->assertOk();
    }

    public function test_unsupported_files_get_a_clear_error(): void
    {
        Storage::fake('local');
        $submission = $this->submission(UploadedFile::fake()->createWithContent('archive.zip', "PK\x03\x04\x00\xff\xfe"));

        $this->fakeAi()->shouldNotReceive('structured');

        $this->actingAs($this->teacher)
            ->postJson(route('submissions.feedback-draft', $submission))
            ->assertStatus(422)
            ->assertJsonPath('message', 'AI drafts work with PDF, image, and plain-text or code files. Please grade this submission manually.');
    }

    public function test_students_cannot_request_feedback_drafts(): void
    {
        Storage::fake('local');
        $submission = $this->submission(UploadedFile::fake()->createWithContent('hello.txt', 'hi'));

        $this->fakeAi()->shouldNotReceive('structured');

        $this->actingAs($this->student)
            ->postJson(route('submissions.feedback-draft', $submission))
            ->assertForbidden();
    }

    public function test_teacher_can_ask_about_only_their_own_classes(): void
    {
        $this->markAttendance('2026-09-01', 'absent');

        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        Course::create(['teacher_id' => $otherTeacher->id, 'code' => 'BIO200', 'title' => 'Secret Biology']);

        $this->fakeAi()->shouldReceive('text')
            ->once()
            ->withArgs(fn ($system, $content) => str_contains($content[0]['text'], 'CS101')
                && str_contains($content[0]['text'], 'Sam Student')
                && ! str_contains($content[0]['text'], 'BIO200')
                && str_contains($content[0]['text'], 'Who was absent?'))
            ->andReturn('Sam Student was absent on 2026-09-01.');

        $this->actingAs($this->teacher)
            ->from(route('dashboard'))
            ->post(route('ai.ask'), ['question' => 'Who was absent?'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('ai_answer', 'Sam Student was absent on 2026-09-01.');

        $this->actingAs($this->teacher)
            ->get(route('dashboard'))
            ->assertSee('Sam Student was absent on 2026-09-01.');
    }

    public function test_students_cannot_use_class_questions(): void
    {
        $this->fakeAi()->shouldNotReceive('text');

        $this->actingAs($this->student)
            ->post(route('ai.ask'), ['question' => 'Who was absent?'])
            ->assertForbidden();
    }

    public function test_ai_errors_are_shown_to_the_user(): void
    {
        $this->fakeAi()->shouldReceive('text')->andThrow(new AiException('Claude is busy right now. Please try again in a minute.'));

        $this->actingAs($this->teacher)
            ->from(route('dashboard'))
            ->post(route('ai.ask'), ['question' => 'Who was absent?'])
            ->assertSessionHas('ai_error', 'Claude is busy right now. Please try again in a minute.');
    }

    public function test_ai_controls_are_hidden_without_an_api_key(): void
    {
        config(['services.anthropic.key' => null]);

        $this->actingAs($this->teacher)
            ->get(route('dashboard'))
            ->assertSee('AI features are off')
            ->assertDontSee('Ask a question');
    }
}
