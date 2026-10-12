<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentSubmission>
 */
class AssignmentSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'student_id' => Student::factory(),
            'submitted_at' => now(),
            'is_late' => false,
            'status' => SubmissionStatus::Submitted,
            'total_score' => null,
            'teacher_score' => null,
            'teacher_comment' => null,
            'file_path' => null,
        ];
    }

    public function graded(float $score = 85): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubmissionStatus::Graded,
            'total_score' => $score,
            'teacher_score' => $score,
            'graded_at' => now(),
        ]);
    }
}
