<?php

namespace App\Http\Requests\SchoolClass;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
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
        $class = $this->route('schoolClass');
        $classId = $class instanceof SchoolClass ? $class->id : null;
        $schoolYearId = $class instanceof SchoolClass ? $class->school_year_id : null;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query->where('school_year_id', $schoolYearId))
                    ->ignore($classId),
            ],
            'level' => ['nullable', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
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
            'homeroom_teacher_id' => 'wali kelas',
        ];
    }
}
