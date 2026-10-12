<?php

namespace Database\Factories;

use App\Enums\AnnouncementTarget as AnnouncementTargetEnum;
use App\Models\Announcement;
use App\Models\AnnouncementTarget;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnouncementTarget>
 */
class AnnouncementTargetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'announcement_id' => Announcement::factory(),
            'target_type' => AnnouncementTargetEnum::All,
            'class_id' => null,
        ];
    }

    public function forClass(?SchoolClass $class = null): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => AnnouncementTargetEnum::ClassRoom,
            'class_id' => $class?->id ?? SchoolClass::factory(),
        ]);
    }
}
