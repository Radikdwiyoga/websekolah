<?php

namespace App\Http\Requests\Agenda;

use App\Enums\AgendaMediaType;
use Illuminate\Contracts\Validation\Validator;

class UpdateAgendaRequest extends StoreAgendaRequest
{
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $agenda = $this->route('agenda');
                $type = $this->input('media_type');
                $hasFile = $this->hasFile('media');
                $hasExistingMedia = filled($agenda?->media_path);

                if ($type === AgendaMediaType::Photo->value && ! $hasFile && ! $hasExistingMedia) {
                    $validator->errors()->add('media', 'Unggah foto agenda terlebih dahulu.');
                }

                if ($type === AgendaMediaType::Video->value && ! $hasFile && blank($this->input('video_url'))) {
                    $validator->errors()->add('video_url', 'Isi tautan video atau unggah berkas video terlebih dahulu.');
                }
            },
        ];
    }
}
