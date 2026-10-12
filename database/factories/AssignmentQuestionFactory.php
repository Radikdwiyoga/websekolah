<?php

namespace Database\Factories;

use App\Enums\AssignmentType;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentQuestion>
 */
class AssignmentQuestionFactory extends Factory
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
            'order' => 1,
            'type' => AssignmentType::MultipleChoice,
            'question_text' => fake()->sentence().'?',
            'options' => [
                'A' => fake()->word(),
                'B' => fake()->word(),
                'C' => fake()->word(),
                'D' => fake()->word(),
            ],
            'correct_answers' => ['A'],
            'points' => 1,
        ];
    }

    public function essay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AssignmentType::Essay,
            'options' => null,
            'correct_answers' => null,
            'points' => 10,
        ]);
    }
}
