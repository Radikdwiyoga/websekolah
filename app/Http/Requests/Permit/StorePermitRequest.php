<?php

namespace App\Http\Requests\Permit;

use App\Enums\PermitType;
use App\Models\AttendancePermit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AttendancePermit::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Parents must pick which child the request is for. Students may
            // omit it; the controller forces their own student record.
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', Rule::enum(PermitType::class)],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'anak',
            'date' => 'tanggal',
            'type' => 'jenis',
            'reason' => 'alasan',
            'attachment' => 'lampiran',
        ];
    }
}
