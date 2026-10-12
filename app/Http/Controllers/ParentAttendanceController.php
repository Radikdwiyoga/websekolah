<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentAttendanceController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    /**
     * Display the attendance history for one of the guardian's children.
     */
    public function index(Request $request): View
    {
        return view('parent.attendance.index', [
            'user' => $request->user(),
        ] + $this->dashboard->parentAttendance($request->user(), $request->integer('student') ?: null));
    }
}
