<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement(['X', 'XI', 'XII']);

        return [
            'school_year_id' => SchoolYear::factory(),
            'homeroom_teacher_id' => null,
            'name' => $level.'-'.fake()->unique()->numberBetween(1, 99),
            'level' => $level,
            'capacity' => 32,
        ];
    }
}
