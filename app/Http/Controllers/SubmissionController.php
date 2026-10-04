<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('create', [Submission::class, $assignment]);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $data['file']->store('submissions/'.$assignment->id, 'local');

        $assignment->submissions()->updateOrCreate(
            ['student_id' => $request->user()->id],
            ['file_path' => $path, 'submitted_at' => now()]
        );

        return back()->with('status', 'Assignment submitted.');
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorize('grade', $submission);

        $data = $request->validate([
            'grade' => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update($data);

        return back()->with('status', 'Grade saved.');
    }

    public function download(Submission $submission)
    {
        $this->authorize('view', $submission);

        return \Illuminate\Support\Facades\Storage::disk('local')->download($submission->file_path);
    }
}
