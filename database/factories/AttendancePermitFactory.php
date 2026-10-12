<?php

namespace Database\Factories;

use App\Enums\PermitStatus;
use App\Enums\PermitType;
use App\Models\AttendancePermit;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendancePermit>
 */
class AttendancePermitFactory extends Factory
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
            'attendance_session_id' => null,
            'date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'type' => fake()->randomElement(PermitType::cases()),
            'reason' => fake()->sentence(),
            'status' => PermitStatus::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PermitStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PermitStatus::Rejected,
            'reviewed_at' => now(),
        ]);
    }
}
