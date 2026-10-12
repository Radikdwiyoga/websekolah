<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementStatus;
use App\Http\Requests\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Services\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminAnnouncementController extends Controller
{
    public function __construct(
        private readonly AnnouncementService $announcements,
    ) {}

    /**
     * Manage every announcement, with an optional status filter.
     */
    public function index(Request $request): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::query()
                ->when($request->query('status'), fn ($query, string $status) => $query->where('status', $status))
                ->with(['targets.schoolClass', 'createdBy'])
                ->latest('updated_at')
                ->get(),
            'statusOptions' => AnnouncementStatus::cases(),
            'selectedStatus' => $request->query('status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'classes' => $this->activeYearClasses(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $announcement = $this->announcements->create(
            $request->validated(),
            $request->file('attachment'),
            $request->user(),
        );

        return redirect()
            ->route('admin.announcements.show', $announcement)
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Admin detail: full content, read progress, and WhatsApp delivery log.
     */
    public function show(Announcement $announcement): View
    {
        return view('admin.announcements.show', [
            'announcement' => $announcement->load(['targets.schoolClass', 'createdBy', 'reads.user', 'waLogs']),
        ]);
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement->load('targets'),
            'classes' => $this->activeYearClasses(),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->announcements->update(
            $announcement,
            $request->validated(),
            $request->file('attachment'),
            $request->user(),
        );

        return redirect()
            ->route('admin.announcements.show', $announcement)
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement, Request $request): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $this->announcements->delete($announcement, $request->user());

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function publish(Announcement $announcement, Request $request): RedirectResponse
    {
        $this->authorize('publish', $announcement);

        $this->announcements->publish($announcement, $request->user());

        return redirect()
            ->route('admin.announcements.show', $announcement)
            ->with('success', 'Pengumuman berhasil diterbitkan.');
    }

    public function archive(Announcement $announcement, Request $request): RedirectResponse
    {
        $this->authorize('archive', $announcement);

        $this->announcements->archive($announcement, $request->user());

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil diarsipkan.');
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function activeYearClasses(): Collection
    {
        $yearId = SchoolYear::query()->where('is_active', true)->value('id');

        return SchoolClass::query()
            ->when($yearId, fn ($query) => $query->where('school_year_id', $yearId))
            ->orderBy('name')
            ->get();
    }
}
