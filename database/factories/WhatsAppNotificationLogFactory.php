<?php

namespace Database\Factories;

use App\Enums\NotificationStatus;
use App\Models\WhatsAppNotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsAppNotificationLog>
 */
class WhatsAppNotificationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendance_record_id' => null,
            'recipient_user_id' => null,
            'recipient_phone' => fake()->numerify('08##########'),
            'message' => fake()->sentence(),
            'status' => NotificationStatus::Pending,
            'attempts' => 0,
            'error_message' => null,
            'provider_message_id' => null,
            'sent_at' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NotificationStatus::Sent,
            'attempts' => 1,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NotificationStatus::Failed,
            'attempts' => 3,
            'error_message' => 'Provider unavailable.',
        ]);
    }
}
