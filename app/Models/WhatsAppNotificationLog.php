<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'announcement_id',
    'attendance_record_id',
    'recipient_user_id',
    'recipient_phone',
    'message',
    'status',
    'attempts',
    'error_message',
    'provider_message_id',
    'sent_at',
])]
class WhatsAppNotificationLog extends Model
{
    use HasFactory;

    /**
     * The table is intentionally not the pluralized "whats_app_notification_logs".
     */
    protected $table = 'whatsapp_notification_logs';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => NotificationStatus::class,
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
