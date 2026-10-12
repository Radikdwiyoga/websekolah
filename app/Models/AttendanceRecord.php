<?php

namespace App\Models;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['attendance_session_id', 'student_id', 'status', 'checked_in_at', 'method', 'note', 'updated_by'])]
class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'method' => AttendanceMethod::class,
            'checked_in_at' => 'datetime',
        ];
    }

    public function attendanceSession(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(AttendanceStatusChange::class);
    }

    public function whatsappNotificationLogs(): HasMany
    {
        return $this->hasMany(WhatsAppNotificationLog::class);
    }
}
