<?php

namespace Database\Factories;

use App\Models\AssignmentAnswer;
use App\Models\AssignmentQuestion;
use App\Models\AssignmentSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentAnswer>
 */
class AssignmentAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_submission_id' => AssignmentSubmission::factory(),
            'assignment_question_id' => AssignmentQuestion::factory(),
            'answer_text' => fake()->sentence(),
            'file_path' => null,
            'is_correct' => null,
            'score' => null,
        ];
    }
}
