<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementService $announcements) {}

    /**
     * List the published announcements targeted at the signed-in user.
     */
    public function index(Request $request): View
    {
        return view('announcements.index', [
            'announcements' => $this->announcements->visibleFor($request->user()),
        ]);
    }

    /**
     * Show one announcement and mark it as read for the signed-in user.
     */
    public function show(Announcement $announcement, Request $request): View
    {
        $isPublic = $announcement->isPublished() && $announcement->target_type !== AnnouncementTarget::ClassRoom;

        if (! $isPublic) {
            abort_unless(auth()->check(), 403);
            $this->authorize('view', $announcement);
        }

        if ($request->user()) {
            $this->announcements->markRead($announcement, $request->user());
        }

        return view('announcements.show', [
            'announcement' => $announcement->load(['targets.schoolClass', 'createdBy']),
        ]);
    }

    /**
     * Stream the announcement attachment as download.
     */
    public function attachment(Announcement $announcement, Request $request): StreamedResponse
    {
        $isPublic = $announcement->isPublished() && $announcement->target_type !== AnnouncementTarget::ClassRoom;

        if (! $isPublic) {
            abort_unless(auth()->check(), 403);
            $this->authorize('view', $announcement);
        }

        return $this->announcements->downloadAttachment($announcement);
    }

    /**
     * Serve the announcement attachment inline for image rendering and previews.
     */
    public function file(Announcement $announcement): Response
    {
        $isPublic = $announcement->isPublished() && $announcement->target_type !== AnnouncementTarget::ClassRoom;

        if (! $isPublic) {
            abort_unless(auth()->check(), 403);
            $this->authorize('view', $announcement);
        }

        if (! $announcement->attachment_path || ! Storage::disk('local')->exists($announcement->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->response(
            $announcement->attachment_path,
            basename($announcement->attachment_path),
            [
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
