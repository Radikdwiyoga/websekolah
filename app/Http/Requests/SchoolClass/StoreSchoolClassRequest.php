<?php

namespace App\Http\Requests\SchoolClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
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
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('classes', 'name')->where(
                    fn ($query) => $query->where('school_year_id', $this->input('school_year_id')),
                ),
            ],
            'level' => ['nullable', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
            'school_year_id' => ['required', 'integer', 'exists:school_years,id'],
            'homeroom_teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kelas',
            'level' => 'tingkat',
            'capacity' => 'kapasitas',
            'school_year_id' => 'tahun ajaran',
            'homeroom_teacher_id' => 'wali kelas',
        ];
    }
}
