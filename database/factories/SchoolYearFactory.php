<?php

namespace Database\Factories;

use App\Enums\Semester;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolYear>
 */
class SchoolYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(2000, 2099);

        return [
            'name' => $startYear.'/'.($startYear + 1),
            'semester' => fake()->randomElement(Semester::cases()),
            'start_date' => $startYear.'-07-01',
            'end_date' => ($startYear + 1).'-06-30',
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
