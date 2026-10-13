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
     * Generate permanent QR code for student.
     * QR code berisi: NISN, nama, kelas (tidak berubah-ubah per sesi)
     *
     * @return \Illuminate\Http\Response
     */
    public function generate(Student $student): JsonResponse
    {
        // Data permanen dari siswa (tidak berubah per sesi absensi)
        $qrData = [
            'nis' => $student->nis,
            'student_name' => $student->user?->name ?? 'Siswa',
            'class_name' => $student->class?->name ?? 'Kelas',
            'class_id' => $student->class_id,
        ];

        // Generate QR code SVG dengan data permanen
        $qrCodeSvg = BaconQrCode\QrCode::render(
            json_encode($qrData),
            Renderer::IMAGE_SVG
        );

        return response()->json([
            'success' => true,
            'qr_code_svg' => $qrCodeSvg,
            'student_name' => $student->user?->name ?? 'Siswa',
            'class_name' => $student->class?->name ?? 'Kelas',
            'nis' => $student->nis,
            'is_permanent' => true,
            'qr_purpose' => 'Absensi tetap (NISN + Nama + Kelas)',
        ]);
    }

    /**
     * Process permanent QR code scan check-in.
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

        $qrData = json_decode($request->qr_data, true);
        if (! $qrData) {
            return back()->with('error', 'Format QR code tidak valid.');
        }

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

        // Perform check-in using the permanent QR code data
        try {
            $record = $this->attendance->checkInQrCode(
                $session,
                $qrData,  // Pass QR code data yang berisi NISN, class_id, dsb
                $request->user()
            );

            $statusText = $record->status === \App\Enums\AttendanceStatus::Hadir ? 'Hadir' : 'Terlambat';

            return back()
                ->with('success', 'Absensi via QR Code Permanen tercatat: ' . $statusText . ' (NISN: '.$qrData['nis'].')');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}