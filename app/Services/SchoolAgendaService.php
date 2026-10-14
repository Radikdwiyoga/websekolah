<?php

namespace App\Services;

use App\Enums\AgendaMediaType;
use App\Enums\SystemLogStatus;
use App\Models\SchoolAgenda;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SchoolAgendaService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $media, User $actor): SchoolAgenda
    {
        $agenda = SchoolAgenda::query()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'media_type' => $data['media_type'],
            'media_path' => $this->storeMedia($media),
            'video_url' => $this->resolveVideoUrl($data),
            'event_date' => $data['event_date'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->log($actor, 'create', 'Menambahkan agenda sekolah '.$agenda->title);

        return $agenda;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SchoolAgenda $agenda, array $data, ?UploadedFile $media, User $actor): void
    {
        $mediaPath = $agenda->media_path;

        if ($media !== null) {
            $this->deleteMedia($agenda->media_path);
            $mediaPath = $this->storeMedia($media);
        }

        $agenda->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'media_type' => $data['media_type'],
            'media_path' => $mediaPath,
            'video_url' => $this->resolveVideoUrl($data),
            'event_date' => $data['event_date'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->log($actor, 'update', 'Memperbarui agenda sekolah '.$agenda->title);
    }

    public function delete(SchoolAgenda $agenda, User $actor): void
    {
        $this->deleteMedia($agenda->media_path);

        $agenda->delete();

        $this->log($actor, 'delete', 'Menghapus agenda sekolah '.$agenda->title);
    }

    public function togglePublished(SchoolAgenda $agenda, User $actor): void
    {
        $agenda->update(['is_published' => ! $agenda->is_published]);

        $this->log(
            $actor,
            'update',
            ($agenda->is_published ? 'Menerbitkan' : 'menyembunyikan').' agenda sekolah '.$agenda->title,
        );
    }

    /**
     * External video URLs only apply to video items; photos always ignore it.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveVideoUrl(array $data): ?string
    {
        if (($data['media_type'] ?? null) !== AgendaMediaType::Video->value) {
            return null;
        }

        $url = trim((string) ($data['video_url'] ?? ''));

        return $url === '' ? null : $url;
    }

    private function storeMedia(?UploadedFile $media): ?string
    {
        return $media?->store('agenda', 'public');
    }

    private function deleteMedia(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'settings',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
