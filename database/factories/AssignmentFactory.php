<?php

namespace Database\Factories;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'class_id' => SchoolClass::factory(),
            'subject_id' => Subject::factory(),
            'school_year_id' => SchoolYear::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(AssignmentType::cases()),
            'deadline' => fake()->dateTimeBetween('now', '+2 weeks'),
            'grade_weight' => 100,
            'late_deduction_percent' => 10,
            'status' => AssignmentStatus::Draft,
            'allow_resubmit' => true,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::Published,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::Closed,
        ]);
    }
}
