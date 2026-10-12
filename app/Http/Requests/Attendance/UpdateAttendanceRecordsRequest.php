<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('attendanceSession');

        return $session instanceof AttendanceSession && $this->user()->can('update', $session);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::enum(AttendanceStatus::class)],
            'note' => ['sometimes', 'array'],
            'note.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
