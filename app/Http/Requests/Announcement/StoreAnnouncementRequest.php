<?php

namespace App\Http\Requests\Announcement;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Announcement::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'target_type' => ['required', Rule::in(['all', 'students', 'parents', 'class'])],
            'class_ids' => ['required_if:target_type,class', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published', 'archived'])],
            'published_at' => ['required_if:status,scheduled', 'nullable', 'date'],
            'is_important' => ['nullable', 'boolean'],
            'send_whatsapp' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:8192'],
        ];
    }
}
