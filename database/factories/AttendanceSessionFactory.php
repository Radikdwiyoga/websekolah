<?php

namespace Database\Factories;

use App\Enums\AttendanceSessionStatus;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => SchoolClass::factory(),
            'school_year_id' => SchoolYear::factory(),
            'date' => now()->toDateString(),
            'open_at' => '07:00',
            'close_at' => '09:00',
            'late_after' => '07:15',
            'status' => AttendanceSessionStatus::Open,
            'qr_secret' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceSessionStatus::Closed,
            'closed_at' => now(),
        ]);
    }
}
