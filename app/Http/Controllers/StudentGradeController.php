<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentGradeController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    /**
     * Display the signed-in student's grades and average.
     */
    public function index(Request $request): View
    {
        return view('grades.student.index', [
            'user' => $request->user(),
        ] + $this->dashboard->studentGrades($request->user()));
    }
}
