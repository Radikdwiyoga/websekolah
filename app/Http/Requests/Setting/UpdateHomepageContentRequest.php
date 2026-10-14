<?php

namespace App\Http\Requests\Setting;

use App\Services\HomepageContentService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Drop fully empty repeater rows before validation so an untouched "add"
     * row does not trigger "title is required" errors.
     */
    protected function prepareForValidation(): void
    {
        foreach (['advantages', 'programs'] as $key) {
            $items = $this->input($key);

            if (! is_array($items)) {
                continue;
            }

            $filtered = array_values(array_filter($items, static function ($item): bool {
                if (! is_array($item)) {
                    return false;
                }

                return filled($item['title'] ?? null) || filled($item['description'] ?? null);
            }));

            $this->merge([$key => $filtered]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'advantages_heading' => ['nullable', 'string', 'max:255'],
            'advantages_description' => ['nullable', 'string', 'max:500'],
            'advantages' => ['nullable', 'array'],
            'advantages.*.icon' => ['required', 'string', Rule::in(HomepageContentService::ICONS)],
            'advantages.*.tone' => ['required', 'string', Rule::in(array_keys(HomepageContentService::TONES))],
            'advantages.*.title' => ['required', 'string', 'max:255'],
            'advantages.*.description' => ['nullable', 'string', 'max:500'],
            'programs_heading' => ['nullable', 'string', 'max:255'],
            'programs_description' => ['nullable', 'string', 'max:500'],
            'programs' => ['nullable', 'array'],
            'programs.*.icon' => ['required', 'string', Rule::in(HomepageContentService::ICONS)],
            'programs.*.tone' => ['required', 'string', Rule::in(array_keys(HomepageContentService::TONES))],
            'programs.*.title' => ['required', 'string', 'max:255'],
            'programs.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'hero_title' => 'judul halaman utama',
            'hero_subtitle' => 'subjudul halaman utama',
            'advantages_heading' => 'judul keunggulan',
            'advantages_description' => 'deskripsi keunggulan',
            'advantages.*.title' => 'judul keunggulan',
            'advantages.*.description' => 'deskripsi keunggulan',
            'programs_heading' => 'judul program',
            'programs_description' => 'deskripsi program',
            'programs.*.title' => 'judul program',
            'programs.*.description' => 'deskripsi program',
        ];
    }
}
