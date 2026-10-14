<?php

namespace App\Models;

use App\Enums\AgendaMediaType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'description',
    'media_type',
    'media_path',
    'video_url',
    'event_date',
    'is_published',
    'sort_order',
])]
class SchoolAgenda extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'media_type' => AgendaMediaType::class,
            'event_date' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Public URL of the uploaded photo or video, if any.
     */
    public function mediaUrl(): ?string
    {
        if (! $this->media_path) {
            return null;
        }

        return Storage::disk('public')->url($this->media_path);
    }

    public function isPhoto(): bool
    {
        return $this->media_type === AgendaMediaType::Photo;
    }

    public function isVideo(): bool
    {
        return $this->media_type === AgendaMediaType::Video;
    }

    /**
     * Whether the agenda item has an uploaded file (photo or video).
     */
    public function hasUploadedMedia(): bool
    {
        return filled($this->media_path);
    }

    /**
     * Whether a video item points at an external URL instead of a file.
     */
    public function hasExternalVideo(): bool
    {
        return $this->isVideo() && filled($this->video_url);
    }

    /**
     * Convert a YouTube or Vimeo URL into an embeddable player URL. Returns
     * null when the URL is not a recognised provider (e.g. a direct mp4 link).
     */
    public function videoEmbedUrl(): ?string
    {
        $url = trim((string) $this->video_url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    /**
     * Agenda items that are visible on the public website.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Order by manual position first, then the most recent event date.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('event_date')
            ->latest('id');
    }
}
