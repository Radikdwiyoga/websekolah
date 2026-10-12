<?php

namespace Database\Factories;

use App\Enums\SystemLogStatus;
use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemLog>
 */
class SystemLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'module' => fake()->randomElement(['auth', 'attendance', 'assignment', 'announcement']),
            'action' => fake()->randomElement(['create', 'update', 'delete', 'login']),
            'description' => fake()->sentence(),
            'context' => null,
            'ip_address' => fake()->ipv4(),
            'status' => SystemLogStatus::Success,
        ];
    }

    public function failure(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SystemLogStatus::Failure,
        ]);
    }
}
