<?php

namespace App\Http\Requests\Assignment;

use App\Enums\AssignmentType;
use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Assignment::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'type' => ['required', Rule::enum(AssignmentType::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'deadline' => ['nullable', 'date'],
            'grade_weight' => ['required', 'numeric', 'min:1', 'max:100'],
            'late_deduction_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'allow_resubmit' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $pair = $this->input('pair');

        if (is_string($pair) && str_contains($pair, ':')) {
            [$classId, $subjectId] = explode(':', $pair, 2);

            $this->merge([
                'class_id' => (int) $classId,
                'subject_id' => (int) $subjectId,
            ]);
        }

        $this->merge([
            'allow_resubmit' => $this->boolean('allow_resubmit'),
        ]);
    }
}
