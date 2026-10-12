<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
        $this->authorize('view', $announcement);

        $this->announcements->markRead($announcement, $request->user());

        return view('announcements.show', [
            'announcement' => $announcement->load(['targets.schoolClass', 'createdBy']),
        ]);
    }

    /**
     * Stream the announcement attachment to an authorised user.
     */
    public function attachment(Announcement $announcement, Request $request): StreamedResponse
    {
        $this->authorize('view', $announcement);

        return $this->announcements->downloadAttachment($announcement);
    }
}
