<?php

namespace App\Http\Requests\Backup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignmentBackupRequest extends FormRequest
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
        return [
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'format' => ['required', Rule::in(['excel', 'pdf'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'kelas',
            'subject_id' => 'mata pelajaran',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal akhir',
            'format' => 'format berkas',
        ];
    }

    public function exportFormat(): string
    {
        return (string) $this->input('format');
    }

    public function classId(): ?int
    {
        return $this->integer('class_id') ?: null;
    }

    public function subjectId(): ?int
    {
        return $this->integer('subject_id') ?: null;
    }
}
