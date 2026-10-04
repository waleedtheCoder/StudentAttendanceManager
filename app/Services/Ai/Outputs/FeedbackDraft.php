<?php

namespace App\Services\Ai\Outputs;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

class FeedbackDraft implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Suggested grade out of 100', minimum: 0, maximum: 100)]
    public int $suggestedGrade;

    #[Constrained(description: 'Feedback addressed to the student: strengths, what to improve, and why the grade was given. Plain text, under 150 words.')]
    public string $feedback;
}
