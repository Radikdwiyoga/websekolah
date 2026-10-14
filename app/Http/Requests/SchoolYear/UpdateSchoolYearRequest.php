<?php

namespace App\Http\Requests\SchoolYear;

use App\Enums\Semester;
use App\Models\SchoolYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolYearRequest extends FormRequest
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
        $schoolYear = $this->route('schoolYear');
        $schoolYearId = $schoolYear instanceof SchoolYear ? $schoolYear->id : null;

        return [
            'name' => [
                'required', 'string', 'max:20',
                Rule::unique('school_years', 'name')
                    ->where(fn ($query) => $query->where('semester', $this->input('semester')))
                    ->ignore($schoolYearId),
            ],
            'semester' => ['required', Rule::enum(Semester::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'tahun ajaran',
            'semester' => 'semester',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
        ];
    }
}
