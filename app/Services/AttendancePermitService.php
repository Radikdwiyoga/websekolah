<?php

namespace App\Services;

use App\Enums\AttendanceMethod;
use App\Enums\PermitStatus;
use App\Enums\SystemLogStatus;
use App\Models\AttendancePermit;
use App\Models\AttendanceSession;
use App\Models\AttendanceStatusChange;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendancePermitService
{
    /**
     * Create a pending izin/sakit request on behalf of a student.
     *
     * @param  array{date: string, type: string, reason: string, attachment?: UploadedFile|null}  $data
     */
    public function submit(Student $student, array $data, User $submitter): AttendancePermit
    {
        $permit = AttendancePermit::query()->create([
            'student_id' => $student->id,
            'date' => $data['date'],
            'type' => $data['type'],
            'reason' => $data['reason'],
            'attachment_path' => $this->storeAttachment($data['attachment'] ?? null),
            'status' => PermitStatus::Pending,
        ]);

        $this->logAudit($permit, 'submit', 'Pengajuan izin/sakit dibuat', $submitter);

        return $permit;
    }

    /**
     * Approve a request and, when a matching session exists, record the
     * student as izin/sakit for that date.
     */
    public function approve(AttendancePermit $permit, User $reviewer, ?string $note = null): void
    {
        DB::transaction(function () use ($permit, $reviewer, $note): void {
            $permit->update([
                'status' => PermitStatus::Approved,
                'review_note' => $note,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $this->applyToAttendance($permit, $reviewer);
        });

        $this->logAudit($permit, 'approve', 'Pengajuan izin/sakit disetujui', $reviewer);
    }

    public function reject(AttendancePermit $permit, User $reviewer, ?string $note = null): void
    {
        $permit->update([
            'status' => PermitStatus::Rejected,
            'review_note' => $note,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->logAudit($permit, 'reject', 'Pengajuan izin/sakit ditolak', $reviewer);
    }

    public function delete(AttendancePermit $permit, User $actor): void
    {
        $this->deleteStoredAttachment($permit->attachment_path);

        $permit->delete();

        $this->logAudit($permit, 'delete', 'Pengajuan izin/sakit dibatalkan', $actor);
    }

    public function downloadAttachment(AttendancePermit $permit): StreamedResponse
    {
        if (! $permit->hasAttachment() || ! Storage::disk('local')->exists($permit->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $permit->attachment_path,
            basename($permit->attachment_path),
        );
    }

    /**
     * Upsert the student's attendance record for the permit date when a session
     * exists, keeping an audit trail of the status change.
     */
    private function applyToAttendance(AttendancePermit $permit, User $reviewer): void
    {
        $student = $permit->student;

        if (! $student) {
            return;
        }

        $session = AttendanceSession::query()
            ->where('class_id', $student->class_id)
            ->whereDate('date', $permit->date->toDateString())
            ->first();

        if (! $session) {
            return;
        }

        $status = $permit->type->attendanceStatus();
        $record = $session->records()->firstOrNew(['student_id' => $student->id]);
        $previous = $record->exists ? $record->status : null;

        $record->fill([
            'status' => $status,
            'checked_in_at' => null,
            'method' => AttendanceMethod::Manual,
            'note' => 'Izin/sakit disetujui: '.$permit->type->label(),
            'updated_by' => $reviewer->id,
        ])->save();

        if ($previous !== null && $previous !== $status) {
            AttendanceStatusChange::create([
                'attendance_record_id' => $record->id,
                'from_status' => $previous,
                'to_status' => $status,
                'reason' => 'Pengajuan izin/sakit disetujui',
                'changed_by' => $reviewer->id,
            ]);
        }

        if ($permit->attendance_session_id !== $session->id) {
            $permit->update(['attendance_session_id' => $session->id]);
        }
    }

    private function storeAttachment(?UploadedFile $attachment): ?string
    {
        return $attachment?->store('permits', 'local');
    }

    private function deleteStoredAttachment(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    private function logAudit(AttendancePermit $permit, string $action, string $description, ?User $actor): void
    {
        SystemLog::query()->create([
            'user_id' => $actor?->id,
            'module' => 'permit',
            'action' => $action,
            'description' => $description.' ('.$permit->type->label().' '.$permit->date?->toDateString().')',
            'status' => SystemLogStatus::Success,
        ]);
    }
}
