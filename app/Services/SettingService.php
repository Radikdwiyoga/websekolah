<?php

namespace App\Services;

use App\Models\ApplicationSetting;
use Illuminate\Support\Collection;

class SettingService
{
    /**
     * Settings loaded once per service instance, keyed by setting key.
     *
     * @var Collection<string, ApplicationSetting>|null
     */
    private ?Collection $settings = null;

    /**
     * @return Collection<string, ApplicationSetting>
     */
    public function all(): Collection
    {
        return $this->settings ??= ApplicationSetting::query()->get()->keyBy('key');
    }

    /**
     * Get a setting value, decoded according to its configured type.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->all()->get($key);

        return $setting?->typedValue() ?? $default;
    }

    public function has(string $key): bool
    {
        return $this->all()->has($key);
    }
}
