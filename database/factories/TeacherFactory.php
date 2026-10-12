<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->asTeacher(),
            'nip' => fake()->unique()->numerify('##################'),
            'gender' => fake()->randomElement(['L', 'P']),
            'phone' => fake()->numerify('08##########'),
            'birth_date' => fake()->dateTimeBetween('-55 years', '-25 years'),
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
