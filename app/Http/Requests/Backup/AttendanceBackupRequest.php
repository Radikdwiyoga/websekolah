<?php

namespace App\Http\Requests\Backup;

use App\Enums\BackupPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceBackupRequest extends FormRequest
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
            'period' => ['required', Rule::enum(BackupPeriod::class)],
            'date' => ['nullable', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'format' => ['required', Rule::in(['excel', 'pdf'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'period' => 'periode',
            'date' => 'tanggal acuan',
            'class_id' => 'kelas',
            'format' => 'format berkas',
        ];
    }

    public function period(): BackupPeriod
    {
        return BackupPeriod::from((string) $this->input('period'));
    }

    public function exportFormat(): string
    {
        return (string) $this->input('format');
    }

    public function classId(): ?int
    {
        return $this->integer('class_id') ?: null;
    }
}
