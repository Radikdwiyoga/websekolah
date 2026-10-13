<?php

namespace App\Http\Requests\Attendance;

use App\Models\AttendanceSession;
use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('attendanceSession');

        return $session instanceof AttendanceSession && $this->user()->can('checkIn', $session);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
