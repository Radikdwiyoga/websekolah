<?php

namespace App\Http\Requests\Attendance;

use App\Models\AttendanceSession;
use Illuminate\Foundation\Http\FormRequest;

class ScanQrAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('attendanceSession');

        return $session instanceof AttendanceSession
            && $this->user()?->can('manageClass', [AttendanceSession::class, $session->schoolClass]);
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
