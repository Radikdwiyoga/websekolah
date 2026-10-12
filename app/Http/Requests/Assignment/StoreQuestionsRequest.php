<?php

namespace App\Http\Requests\Assignment;

use App\Enums\AssignmentType;
use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof Assignment && $this->user()->can('manageQuestions', $assignment);
    }

    /**
     * Drop fully empty rows so a stray blank row never blocks the request.
     */
    protected function prepareForValidation(): void
    {
        $questions = collect($this->input('questions', []))
            ->filter(fn ($row): bool => is_array($row) && trim((string) ($row['question_text'] ?? '')) !== '')
            ->values()
            ->all();

        $this->merge(['questions' => $questions]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $assignment = $this->route('assignment');

        $rules = [
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string', 'max:2000'],
            'questions.*.points' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ];

        if ($assignment instanceof Assignment && $assignment->type === AssignmentType::MultipleChoice) {
            $rules['questions.*.options'] = ['required', 'array'];

            foreach (['A', 'B', 'C', 'D'] as $key) {
                $rules["questions.*.options.{$key}"] = ['required', 'string', 'max:500'];
            }

            $rules['questions.*.correct_answer'] = ['required', Rule::in(['A', 'B', 'C', 'D'])];
        }

        if ($assignment instanceof Assignment && $assignment->type === AssignmentType::ShortAnswer) {
            $rules['questions.*.correct_answers'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }
}
