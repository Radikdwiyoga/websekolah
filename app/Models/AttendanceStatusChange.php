<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['attendance_record_id', 'from_status', 'to_status', 'reason', 'changed_by'])]
class AttendanceStatusChange extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => AttendanceStatus::class,
            'to_status' => AttendanceStatus::class,
        ];
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
