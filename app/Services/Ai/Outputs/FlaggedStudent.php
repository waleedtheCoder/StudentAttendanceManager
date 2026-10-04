<?php

namespace App\Services\Ai\Outputs;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

class FlaggedStudent implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'The student_id from the provided data')]
    public int $studentId;

    #[Constrained(description: 'One sentence describing the attendance pattern, citing the numbers')]
    public string $concern;

    #[Constrained(description: 'One short, practical next step for the teacher')]
    public string $suggestedAction;
}
