<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSchoolYearController extends Controller
{
    /**
     * Display all school years.
     */
    public function index(): View
    {
        $schoolYears = SchoolYear::query()
            ->withCount('classes')
            ->orderByDesc('start_date')
            ->get();

        return view('admin.school-years.index', [
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Set a school year as the active school year.
     */
    public function activate(SchoolYear $schoolYear): RedirectResponse
    {
        SchoolYear::query()->update(['is_active' => false]);
        $schoolYear->update(['is_active' => true]);

        return redirect()->route('admin.school-years.index')
            ->with('success', "Tahun ajaran {$schoolYear->name} sekarang aktif.");
    }
}
