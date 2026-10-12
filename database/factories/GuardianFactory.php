<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guardian>
 */
class GuardianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->asParent(),
            'phone' => fake()->numerify('08##########'),
            'occupation' => fake()->jobTitle(),
            'address' => fake()->address(),
            'whatsapp_opt_in' => true,
        ];
    }

    public function withoutWhatsApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'whatsapp_opt_in' => false,
        ]);
    }
}
