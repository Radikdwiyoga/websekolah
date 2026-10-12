<?php

namespace App\Models;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value', 'type', 'group'])]
class ApplicationSetting extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
        ];
    }

    /**
     * Decode the raw stored value according to the setting type.
     */
    public function typedValue(): mixed
    {
        return match ($this->type) {
            SettingType::Integer => is_null($this->value) ? null : (int) $this->value,
            SettingType::Boolean => filter_var($this->value, FILTER_VALIDATE_BOOL),
            SettingType::Json => is_null($this->value) ? null : json_decode($this->value, true),
            default => $this->value,
        };
    }
}
