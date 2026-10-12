<?php

namespace App\Http\Requests\Assignment;

use App\Models\Assignment;
use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof Assignment && $this->user()->can('submit', $assignment);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $settings = app(SettingService::class);
        $maxKilobytes = (int) $settings->get('upload_max_size_kb', 5120);
        $extensions = $settings->get('upload_allowed_extensions', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

        if (is_string($extensions)) {
            $extensions = json_decode($extensions, true) ?: [];
        }

        return [
            'answers' => ['sometimes', 'array'],
            'answers.*' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:'.$maxKilobytes, 'mimes:'.implode(',', (array) $extensions)],
            'finalize' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'finalize' => $this->boolean('finalize'),
        ]);
    }
}
