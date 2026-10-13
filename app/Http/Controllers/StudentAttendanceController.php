<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class StudentAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly QrCodeService $qrCodes,
    ) {}

    public function index(Request $request): View
    {
        $student = $request->user()?->student;

        $session = null;
        $record = null;
        $qrSvg = null;

        if ($student) {
            $session = AttendanceSession::query()
                ->where('class_id', $student->class_id)
                ->whereDate('date', now()->toDateString())
                ->with(['schoolClass'])
                ->first();

            $record = $session?->records()->where('student_id', $student->id)->first();

            $qrSvg = $this->qrCodes->svgFor($student);
        }

        return view('attendance.student.index', [
            'student' => $student,
            'session' => $session,
            'record' => $record,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function checkIn(AttendanceSession $attendanceSession, CheckInRequest $request): RedirectResponse
    {
        try {
            $record = $this->attendance->checkIn($attendanceSession, $request->user()->student, $request->user());
        } catch (LogicException $exception) {
            return redirect()->route('student.attendance.index')->with('error', $exception->getMessage());
        }

        $message = $record->status === AttendanceStatus::Hadir
            ? 'Absensi tercatat: Hadir.'
            : 'Absensi tercatat: Terlambat.';

        return redirect()->route('student.attendance.index')->with('success', $message);
    }
}
