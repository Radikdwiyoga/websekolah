<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    /**
     * Redirect the user to the dashboard that matches their role.
     */
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role->dashboardRoute());
    }

    /**
     * Display the student dashboard.
     */
    public function student(Request $request): View
    {
        return view('dashboard.student', [
            'user' => $request->user(),
        ] + $this->dashboard->student($request->user()));
    }

    /**
     * Display the teacher dashboard.
     */
    public function teacher(Request $request): View
    {
        return view('dashboard.teacher', [
            'user' => $request->user(),
        ] + $this->dashboard->teacher($request->user()));
    }

    /**
     * Display the parent dashboard for a specific child.
     */
    public function parent(Request $request): View
    {
        return view('dashboard.parent', [
            'user' => $request->user(),
        ] + $this->dashboard->parent($request->user(), $request->integer('student') ?: null));
    }

    /**
     * Display the admin dashboard.
     */
    public function admin(Request $request): View
    {
        return view('dashboard.admin', [
            'user' => $request->user(),
        ] + $this->dashboard->admin());
    }
}
