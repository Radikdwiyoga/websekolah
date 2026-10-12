<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeachingAssignment>
 */
class TeachingAssignmentFactory extends Factory
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
        ];
    }
}
