<?php

namespace Database\Factories;

use App\Enums\AgendaMediaType;
use App\Models\SchoolAgenda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolAgenda>
 */
class SchoolAgendaFactory extends Factory
{
    protected $model = SchoolAgenda::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'media_type' => AgendaMediaType::Photo,
            'media_path' => 'agenda/'.fake()->uuid().'.jpg',
            'video_url' => null,
            'event_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function photo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'media_type' => AgendaMediaType::Photo,
            'media_path' => 'agenda/'.fake()->uuid().'.jpg',
            'video_url' => null,
        ]);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes): array => [
            'media_type' => AgendaMediaType::Video,
            'media_path' => null,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_published' => false,
        ]);
    }
}
