<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentAssignmentController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    /**
     * Display the assignment status for one of the guardian's children.
     */
    public function index(Request $request): View
    {
        return view('parent.assignments.index', [
            'user' => $request->user(),
        ] + $this->dashboard->parentAssignments($request->user(), $request->integer('student') ?: null));
    }
}
