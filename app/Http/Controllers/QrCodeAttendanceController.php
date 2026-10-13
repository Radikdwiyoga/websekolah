<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\QrCheckInRequest;
use App\Http\Requests\Attendance\ScanQrAttendanceRequest;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LogicException;

class QrCodeAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly QrCodeService $qrCodes,
    ) {}

    /**
     * Student self check-in by scanning their own permanent QR code.
     */
    public function checkIn(QrCheckInRequest $request): RedirectResponse
    {
        $student = $request->user()?->student;

        $payload = $this->qrCodes->decode($request->validated('qr_payload'));

        if ($payload === null) {
            return back()->with('error', 'QR code tidak valid atau tidak dikenali.');
        }

        if (! $student || (int) $payload['student_id'] !== $student->id) {
            return back()->with('error', 'QR code ini bukan milik Anda.');
        }

        $session = AttendanceSession::query()
            ->where('class_id', $student->class_id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (! $session) {
            return back()->with('error', 'Belum ada sesi absensi untuk kelas Anda hari ini.');
        }

        try {
            $record = $this->attendance->checkInQr($session, $student, $request->user());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Absensi via QR code tercatat: '.$record->status->label().'.');
    }

    /**
     * Teacher (or admin) scans a student's permanent QR code to record their
     * attendance for the open session of the class being viewed.
     */
    public function scan(ScanQrAttendanceRequest $request, AttendanceSession $attendanceSession): RedirectResponse
    {
        $payload = $this->qrCodes->decode($request->validated('qr_payload'));

        if ($payload === null) {
            return back()->with('error', 'QR code tidak valid atau tidak dikenali.');
        }

        $student = Student::query()->find($payload['student_id']);

        if (! $student) {
            return back()->with('error', 'Siswa pada QR code tidak ditemukan.');
        }

        if ($student->class_id !== $attendanceSession->class_id) {
            return back()->with('error', 'QR code ini milik siswa kelas lain.');
        }

        try {
            $record = $this->attendance->checkInQr($attendanceSession, $student, $request->user());
        } catch (LogicException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $name = $student->user?->name ?? 'Siswa';

        return back()->with('success', $name.' tercatat '.$record->status->label().' melalui QR code.');
    }

    /**
     * Stream a student's permanent QR code as an SVG image.
     */
    public function image(Request $request): Response
    {
        $student = $request->user()?->student;

        abort_unless($student, 404);

        return response($this->qrCodes->svgFor($student, 320), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr-absensi-'.($student->nis ?: $student->id).'.svg"',
        ]);
    }
}
