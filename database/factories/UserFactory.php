<?php

namespace Database\Factories;

use App\Enums\AdminLevel;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Student,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function asStudent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Student,
        ]);
    }

    public function asTeacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Teacher,
        ]);
    }

    public function asParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Parent,
        ]);
    }

    public function asAdmin(AdminLevel $level = AdminLevel::OperationalAdmin): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
            'admin_level' => $level,
        ]);
    }

    public function asSuperAdmin(): static
    {
        return $this->asAdmin(AdminLevel::SuperAdmin);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
