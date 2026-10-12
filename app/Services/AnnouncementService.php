<?php

namespace App\Services;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Enums\NotificationStatus;
use App\Enums\SystemLogStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\AnnouncementTarget as AnnouncementTargetModel;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\User;
use App\Models\WhatsAppNotificationLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    public function __construct(private readonly AttendanceService $attendance) {}

    /**
     * Published announcements the user is allowed to see, newest first with
     * important ones on top.
     *
     * @return Collection<int, Announcement>
     */
    public function visibleFor(User $user): Collection
    {
        $query = Announcement::query()->published()->with(['targets.schoolClass', 'reads']);

        if (! $user->isAdmin()) {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('target_type', AnnouncementTarget::All);

                if ($user->isStudent()) {
                    $query->orWhere('target_type', AnnouncementTarget::Students);
                }

                if ($user->isParent()) {
                    $query->orWhere('target_type', AnnouncementTarget::Parents);
                }

                $classIds = $this->classIdsFor($user);

                if ($classIds !== []) {
                    $query->orWhere(function (Builder $classQuery) use ($classIds): void {
                        $classQuery
                            ->where('target_type', AnnouncementTarget::ClassRoom)
                            ->whereHas('targets', fn (Builder $targets): Builder => $targets->whereIn('class_id', $classIds));
                    });
                }
            });
        }

        return $query
            ->orderByDesc('is_important')
            ->latest('published_at')
            ->get();
    }

    /**
     * Whether a single announcement is visible to the user.
     */
    public function isVisibleTo(Announcement $announcement, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->visibleFor($user)->contains('id', $announcement->id);
    }

    /**
     * Record that the user has opened an announcement. Admins are exempt: they
     * manage announcements and would otherwise inflate the read counts.
     */
    public function markRead(Announcement $announcement, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        AnnouncementRead::query()->updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $user->id],
            ['read_at' => now()],
        );
    }

    /**
     * Store a new announcement (admin action).
     *
     * @param  array{title: string, body: string, target_type: string, class_ids?: array<int, int>, status: string, published_at?: string|null, is_important?: bool, send_whatsapp?: bool}  $data
     */
    public function create(array $data, ?UploadedFile $attachment, User $actor): Announcement
    {
        $publishedAt = $this->resolvePublishedAt(AnnouncementStatus::tryFrom($data['status']), $data['published_at'] ?? null);

        $announcement = Announcement::query()->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => $data['status'],
            'target_type' => $data['target_type'],
            'published_at' => $publishedAt,
            'is_important' => (bool) ($data['is_important'] ?? false),
            'send_whatsapp' => (bool) ($data['send_whatsapp'] ?? false),
            'attachment_path' => $this->storeAttachment($attachment),
            'created_by' => $actor->id,
        ]);

        $this->syncClassTargets($announcement, $data['class_ids'] ?? []);
        $this->logAudit($announcement, 'create', 'Pengumuman dibuat', $actor);
        $this->dispatchWhatsApp($announcement);

        return $announcement;
    }

    /**
     * Update an existing announcement (admin action).
     *
     * @param  array{title: string, body: string, target_type: string, class_ids?: array<int, int>, status: string, published_at?: string|null, is_important?: bool, send_whatsapp?: bool}  $data
     */
    public function update(Announcement $announcement, array $data, ?UploadedFile $attachment, User $actor): void
    {
        $wasPublished = $announcement->isPublished();
        $publishedAt = $this->resolvePublishedAt(AnnouncementStatus::tryFrom($data['status']), $data['published_at'] ?? null);

        $announcement->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => $data['status'],
            'target_type' => $data['target_type'],
            'published_at' => $publishedAt,
            'is_important' => (bool) ($data['is_important'] ?? false),
            'send_whatsapp' => (bool) ($data['send_whatsapp'] ?? false),
        ]);

        if ($attachment !== null) {
            $this->deleteStoredAttachment($announcement->attachment_path);
            $announcement->update(['attachment_path' => $this->storeAttachment($attachment)]);
        }

        $this->syncClassTargets($announcement, $data['class_ids'] ?? []);
        $this->logAudit($announcement, 'update', 'Pengumuman diperbarui', $actor);

        // Dispatch WhatsApp only on the transition into a published state so
        // repeated edits do not enqueue duplicate messages.
        if (! $wasPublished && $announcement->isPublished()) {
            $this->dispatchWhatsApp($announcement);
        }
    }

    public function publish(Announcement $announcement, User $actor): void
    {
        $wasPublished = $announcement->isPublished();

        $announcement->update([
            'status' => AnnouncementStatus::Published,
            'published_at' => $announcement->published_at ?? now(),
        ]);

        $this->logAudit($announcement, 'publish', 'Pengumuman diterbitkan', $actor);

        if (! $wasPublished) {
            $this->dispatchWhatsApp($announcement);
        }
    }

    public function archive(Announcement $announcement, User $actor): void
    {
        $announcement->update(['status' => AnnouncementStatus::Archived]);

        $this->logAudit($announcement, 'archive', 'Pengumuman diarsipkan', $actor);
    }

    public function delete(Announcement $announcement, User $actor): void
    {
        $announcement->delete();

        $this->logAudit($announcement, 'delete', 'Pengumuman dihapus', $actor);
    }

    /**
     * Stream the stored attachment to an authorised user.
     */
    public function downloadAttachment(Announcement $announcement)
    {
        if (! $announcement->attachment_path || ! Storage::disk('local')->exists($announcement->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $announcement->attachment_path,
            basename($announcement->attachment_path),
        );
    }

    /**
     * Create a mock WhatsApp notification log per intended recipient. This is
     * the development stand-in for a real provider (DESIGN.md §20).
     *
     * @return int number of recipients queued
     */
    public function dispatchWhatsApp(Announcement $announcement): int
    {
        if (! $announcement->send_whatsapp || ! $announcement->isPublished()) {
            return 0;
        }

        $queued = 0;

        foreach ($this->recipientUsers($announcement) as $user) {
            $phone = $this->phoneFor($user);

            if ($phone === null || $phone === '') {
                continue;
            }

            WhatsAppNotificationLog::query()->create([
                'announcement_id' => $announcement->id,
                'recipient_user_id' => $user->id,
                'recipient_phone' => $phone,
                'message' => 'Pengumuman: '.$announcement->title.'. Silakan buka aplikasi untuk detail lengkap.',
                'status' => NotificationStatus::Pending,
            ]);

            $queued++;
        }

        return $queued;
    }

    /**
     * The active users who should receive a WhatsApp copy of an announcement.
     *
     * @return Collection<int, User>
     */
    public function recipientUsers(Announcement $announcement): Collection
    {
        $query = User::query()->where('is_active', true);

        return match ($announcement->target_type) {
            AnnouncementTarget::Students => $query->where('role', UserRole::Student)->whereHas('student')->get(),
            AnnouncementTarget::Parents => $query->where('role', UserRole::Parent)->whereHas('guardian', fn (Builder $q): Builder => $q->where('whatsapp_opt_in', true))->get(),
            AnnouncementTarget::ClassRoom => $this->classRoomRecipients($announcement),
            default => $query->get(),
        };
    }

    /**
     * Classes the user belongs to for class-targeted announcements.
     *
     * @return array<int, int>
     */
    public function classIdsFor(User $user): array
    {
        if ($user->isStudent() && $user->student) {
            return [$user->student->class_id];
        }

        if ($user->isParent() && $user->guardian) {
            return $user->guardian->students()
                ->pluck('class_id')
                ->filter()
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if ($user->isTeacher() && $user->teacher) {
            return $this->attendance->teacherClassIds($user->teacher);
        }

        return [];
    }

    /**
     * @param  array<int, int>  $classIds
     */
    private function syncClassTargets(Announcement $announcement, array $classIds): void
    {
        if ($announcement->target_type !== AnnouncementTarget::ClassRoom) {
            $announcement->targets()->delete();

            return;
        }

        $announcement->targets()->delete();

        foreach ($classIds as $classId) {
            AnnouncementTargetModel::query()->create([
                'announcement_id' => $announcement->id,
                'target_type' => AnnouncementTarget::ClassRoom,
                'class_id' => $classId,
            ]);
        }
    }

    private function resolvePublishedAt(?AnnouncementStatus $status, ?string $value): ?Carbon
    {
        if ($status === AnnouncementStatus::Published && ($value === null || $value === '')) {
            return now();
        }

        return $value ? Carbon::parse($value) : null;
    }

    private function storeAttachment(?UploadedFile $attachment): ?string
    {
        return $attachment?->store('announcements', 'local');
    }

    private function deleteStoredAttachment(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    private function logAudit(Announcement $announcement, string $action, string $description, ?User $actor): void
    {
        SystemLog::query()->create([
            'user_id' => $actor?->id,
            'module' => 'announcement',
            'action' => $action,
            'description' => $description.': '.$announcement->title,
            'status' => SystemLogStatus::Success,
        ]);
    }

    /**
     * @return Collection<int, User>
     */
    private function classRoomRecipients(Announcement $announcement): Collection
    {
        $classIds = $announcement->targets()->pluck('class_id')->all();

        if ($classIds === []) {
            return collect();
        }

        $classStudents = Student::query()->whereIn('class_id', $classIds)->get();
        $studentIds = $classStudents->pluck('id');

        $guardianIds = DB::table('guardian_student')
            ->whereIn('student_id', $studentIds)
            ->pluck('guardian_id');

        $userIds = $classStudents->pluck('user_id')
            ->merge(Guardian::query()->whereIn('id', $guardianIds)->pluck('user_id'))
            ->filter()
            ->unique();

        return User::query()
            ->whereIn('id', $userIds)
            ->where('is_active', true)
            ->get();
    }

    private function phoneFor(User $user): ?string
    {
        if ($user->isParent() && $user->guardian?->phone) {
            return $user->guardian->phone;
        }

        return $user->phone;
    }
}
