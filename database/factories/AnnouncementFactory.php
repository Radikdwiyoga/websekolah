<?php

namespace Database\Factories;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'slug' => null,
            'body' => fake()->paragraphs(3, true),
            'status' => AnnouncementStatus::Draft,
            'target_type' => AnnouncementTarget::All,
            'published_at' => null,
            'is_important' => false,
            'send_whatsapp' => false,
            'attachment_path' => null,
            'created_by' => User::factory()->asAdmin(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnnouncementStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function important(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_important' => true,
        ]);
    }
}
