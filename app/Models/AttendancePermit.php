<?php

namespace App\Models;

use App\Enums\PermitStatus;
use App\Enums\PermitType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'attendance_session_id',
    'date',
    'type',
    'reason',
    'attachment_path',
    'status',
    'review_note',
    'reviewed_by',
    'reviewed_at',
])]
class AttendancePermit extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'type' => PermitType::class,
            'status' => PermitStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === PermitStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === PermitStatus::Approved;
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment_path);
    }

    /**
     * @param  Builder<AttendancePermit>  $query
     * @return Builder<AttendancePermit>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PermitStatus::Pending);
    }

    /**
     * @param  Builder<AttendancePermit>  $query
     * @return Builder<AttendancePermit>
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }
}
