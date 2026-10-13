<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class QrCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStudent();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'qr_payload' => ['required', 'string'],
        ];
    }
}
