<?php

namespace App\Http\Requests\Assignment;

use App\Models\AssignmentSubmission;
use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return $submission instanceof AssignmentSubmission
            && $this->user()->can('grade', $submission->assignment);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scores' => ['sometimes', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'teacher_score' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'teacher_comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
