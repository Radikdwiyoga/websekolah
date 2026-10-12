<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentGradeController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    /**
     * Display the grades for one of the guardian's children.
     */
    public function index(Request $request): View
    {
        return view('parent.grades.index', [
            'user' => $request->user(),
        ] + $this->dashboard->parentGrades($request->user(), $request->integer('student') ?: null));
    }
}
