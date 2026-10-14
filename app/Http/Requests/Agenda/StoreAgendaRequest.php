<?php

namespace App\Http\Requests\Agenda;

use App\Enums\AgendaMediaType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isVideo = $this->input('media_type') === AgendaMediaType::Video->value;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'media_type' => ['required', Rule::in(AgendaMediaType::values())],
            'event_date' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'media' => $isVideo
                ? ['nullable', 'file', 'mimes:mp4,webm,mov,ogv', 'max:20480']
                : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'video_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $type = $this->input('media_type');
                $hasFile = $this->hasFile('media');

                if ($type === AgendaMediaType::Photo->value && ! $hasFile) {
                    $validator->errors()->add('media', 'Unggah foto agenda terlebih dahulu.');
                }

                if ($type === AgendaMediaType::Video->value && ! $hasFile && blank($this->input('video_url'))) {
                    $validator->errors()->add('video_url', 'Isi tautan video atau unggah berkas video terlebih dahulu.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'judul agenda',
            'description' => 'deskripsi agenda',
            'media_type' => 'jenis media',
            'media' => 'berkas media',
            'video_url' => 'tautan video',
            'event_date' => 'tanggal agenda',
            'sort_order' => 'urutan',
        ];
    }
}
