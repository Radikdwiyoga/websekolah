<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->asStudent(),
            'class_id' => null,
            'nis' => fake()->unique()->numerify('##########'),
            'nisn' => fake()->unique()->numerify('##########'),
            'gender' => fake()->randomElement(['L', 'P']),
            'birth_date' => fake()->dateTimeBetween('-19 years', '-14 years'),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
