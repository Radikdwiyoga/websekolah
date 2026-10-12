<?php

namespace App\Http\Requests\Announcement;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement instanceof Announcement && $this->user()?->can('update', $announcement) ?? false;
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
