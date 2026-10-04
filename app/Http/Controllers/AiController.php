<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Submission;
use App\Services\Ai\AiException;
use App\Services\Ai\AttendanceInsightsService;
use App\Services\Ai\ClassQuestionAnswerer;
use App\Services\Ai\FeedbackDrafter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    /** Generate (or refresh) the AI attendance summary shown on the course's attendance page. */
    public function attendanceInsights(Course $course, AttendanceInsightsService $service): RedirectResponse
    {
        $this->authorize('mark', [Attendance::class, $course]);

        try {
            $service->generate($course);
        } catch (AiException $e) {
            return back()->with('ai_error', $e->getMessage());
        }

        return redirect()->route('courses.attendance.index', $course)->with('status', 'Attendance insights updated.');
    }

    /** Suggest a grade and feedback for a submission. Returned as JSON so the page can fill in the grading form. */
    public function feedbackDraft(Submission $submission, FeedbackDrafter $drafter): JsonResponse
    {
        $this->authorize('grade', $submission);

        try {
            $draft = $drafter->draft($submission);
        } catch (AiException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'grade' => $draft->suggestedGrade,
            'feedback' => $draft->feedback,
        ]);
    }

    /** Answer a teacher's plain-English question about their classes. */
    public function ask(Request $request, ClassQuestionAnswerer $answerer): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        try {
            $answer = $answerer->ask($request->user(), $data['question']);
        } catch (AiException $e) {
            return back()->withInput()->with('ai_error', $e->getMessage());
        }

        return back()->withInput()->with('ai_answer', $answer);
    }
}
