<?php

namespace Database\Factories;

use App\Enums\SettingType;
use App\Models\ApplicationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationSetting>
 */
class ApplicationSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2, false),
            'value' => fake()->word(),
            'type' => SettingType::Text,
            'group' => 'general',
        ];
    }

    public function boolean(bool $value = true): static
    {
        return $this->state(fn (array $attributes) => [
            'value' => $value ? '1' : '0',
            'type' => SettingType::Boolean,
        ]);
    }
}
