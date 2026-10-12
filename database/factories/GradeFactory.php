<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'class_id' => null,
            'assignment_id' => null,
            'school_year_id' => null,
            'score' => fake()->randomFloat(2, 60, 100),
            'weight' => 100,
            'type' => 'assignment',
            'graded_at' => now(),
        ];
    }
}
