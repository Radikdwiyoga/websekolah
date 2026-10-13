<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceMethod;
App\Models\AttendanceRecord;
App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use BaconQrCode\BaconQrCode;
use BaconQrCode\Renderer\Renderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    /**
     * Generate QR code for student attendance check-in.
     *
     * @return \Illuminate\Http\Response
     */
    public function generate(Student $student): JsonResponse
    {
        $session = AttendanceSession::query()
            ->where('class_id', $student->class_id)
            ->whereDate('date', now()->toDateString())
            ->where('status', 'open')
            ->with('records')
            ->first();

        if (! $session || ! $session->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi absensi tidak aktif untuk kelas ini hari ini.',
            ], 400);
        }

        // Generate QR code data
        $qrData = [
            'student_id' => $student->id,
            'class_id' => $student->class_id,
            'session_id' => $session->id,
            'timestamp' => now()->timestamp,
        ];

        $qrCodeSvg = BaconQrCode\QrCode::render(
            json_encode($qrData),
            Renderer::IMAGE_SVG
        );

        return response()->json([
            'success' => true,
            'qr_code_svg' => $qrCodeSvg,
            'student_name' => $student->user?->name ?? 'Siswa',
            'class_name' => $student->class?->name ?? 'Kelas',
            'session_open' => $session->isOpen(),
        ]);
    }

    /**
     * Process QR code scan check-in.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkIn(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,user_id',
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:attendance_sessions,id',
            'qr_data' => 'required|string',
        ]);

        $student = Student::find($data['student_id'])->load('user');
        $session = AttendanceSession::find($data['session_id']);

        // Verify session belongs to class and today
        if ($session->class_id != $data['class_id']) {
            return back()->with('error', 'Sesi absensi tidak sesuai kelas.');
        }

        if ($session->date?->toDateString() !== now()->toDateString()) {
            return back()->with('error', 'Sesi absensi bukan untuk hari ini.');
        }

        if (! $session->isOpen()) {
            return back()->with('error', 'Sesi absensi sudah ditutup.');
        }

        // Check if student already checked in
        if ($session->records()->where('student_id', $data['student_id'])->exists()) {
            return back()->with('error', 'Siswa sudah melakukan absensi pada sesi ini.');
        }

        // Perform check-in
        try {
            $record = $this->attendance->checkInQrCode(
                $session,
                $student,  // Pass Student model, not $student->user
                $request->user()
            );

            return back()
                ->with('success', 'Absensi via QR Code tercatat: ' . ($record->status === \App\Enums\AttendanceStatus::Hadir ? 'Hadir' : 'Terlambat'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}