<?php

namespace App\Services;

use App\Enums\SettingType;
use App\Models\ApplicationSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * Settings loaded once per service instance, keyed by setting key.
     *
     * @var Collection<string, ApplicationSetting>|null
     */
    private ?Collection $settings = null;

    /**
     * Load every setting once per instance, keyed by setting key.
     *
     * @return Collection<string, ApplicationSetting>
     */
    public function all(): Collection
    {
        return $this->settings ??= ApplicationSetting::query()->get()->keyBy('key');
    }

    /**
     * Read a setting value, decoded according to the setting type.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key)?->typedValue() ?? $default;
    }

    /**
     * Persist a single setting, creating it when it does not exist yet.
     */
    public function set(string $key, mixed $value, ?string $group = null, ?SettingType $type = null): ApplicationSetting
    {
        $setting = ApplicationSetting::query()->firstOrNew(['key' => $key]);

        $setting->value = $this->encode($value, $type ?? $setting->type ?? SettingType::Text);
        $setting->type = $type ?? $setting->type ?? SettingType::Text;
        $setting->group = $group ?? $setting->group ?? 'general';
        $setting->save();

        $this->settings = null;

        return $setting;
    }

    /**
     * Persist several key/value settings in one go.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values, ?string $group = null): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    /**
     * Resolve the public URL of a path stored on the public disk.
     */
    public function publicUrl(?string $key): ?string
    {
        $path = $key === null ? null : $this->get($key);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function encode(mixed $value, SettingType $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            SettingType::Boolean => filter_var($value, FILTER_VALIDATE_BOOL) ? '1' : '0',
            SettingType::Json => is_array($value) ? json_encode($value) : (string) $value,
            SettingType::Integer => (string) (int) $value,
            SettingType::Text => (string) $value,
        };
    }
}
