<?php

namespace App\Http\Controllers;

use App\Models\AttendancePermit;
use App\Services\AttendancePermitService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PermitAttachmentController extends Controller
{
    public function __construct(private readonly AttendancePermitService $permits) {}

    /**
     * Stream a permit attachment to any user allowed to view the permit.
     */
    public function __invoke(AttendancePermit $permit): StreamedResponse
    {
        $this->authorize('view', $permit);

        return $this->permits->downloadAttachment($permit);
    }
}
