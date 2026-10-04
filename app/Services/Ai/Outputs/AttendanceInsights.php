<?php

namespace App\Services\Ai\Outputs;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

class AttendanceInsights implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: '2-4 sentence overview of attendance in this course for the teacher')]
    public string $summary;

    #[Constrained(description: 'Students whose attendance is a concern, most serious first. Empty if nobody stands out.', itemClass: FlaggedStudent::class)]
    public array $flaggedStudents;
}
