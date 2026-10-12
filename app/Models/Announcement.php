<?php

namespace App\Models;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget as AnnouncementTargetEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'slug',
    'body',
    'status',
    'target_type',
    'published_at',
    'is_important',
    'send_whatsapp',
    'attachment_path',
    'created_by',
])]
class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AnnouncementStatus::class,
            'target_type' => AnnouncementTargetEnum::class,
            'published_at' => 'datetime',
            'is_important' => 'boolean',
            'send_whatsapp' => 'boolean',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(AnnouncementTarget::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function waLogs(): HasMany
    {
        return $this->hasMany(WhatsAppNotificationLog::class);
    }

    public function isPublished(): bool
    {
        return in_array($this->status, [AnnouncementStatus::Published, AnnouncementStatus::Scheduled], true)
            && ($this->published_at === null || $this->published_at->isPast());
    }

    public function isImage(): bool
    {
        if (! $this->attachment_path) {
            return false;
        }

        $extension = strtolower(pathinfo($this->attachment_path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
    }

    /**
     * Announcements that are currently visible (published, not scheduled later).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [AnnouncementStatus::Published, AnnouncementStatus::Scheduled])
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Announcements intended for broad audiences, suitable for the public homepage.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->published()
            ->whereNot('target_type', AnnouncementTargetEnum::ClassRoom);
    }
}
