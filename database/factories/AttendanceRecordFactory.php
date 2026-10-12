<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_session_id' => AttendanceSession::factory(),
            'student_id' => Student::factory(),
            'status' => AttendanceStatus::BelumAbsen,
            'checked_in_at' => null,
            'method' => null,
            'note' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceStatus::Hadir,
            'checked_in_at' => now(),
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceStatus::Terlambat,
            'checked_in_at' => now(),
        ]);
    }
}
