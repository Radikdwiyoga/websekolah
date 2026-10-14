<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolYear\StoreSchoolYearRequest;
use App\Http\Requests\SchoolYear\UpdateSchoolYearRequest;
use App\Models\SchoolYear;
use App\Services\SchoolYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSchoolYearController extends Controller
{
    public function __construct(
        private readonly SchoolYearService $schoolYears,
    ) {}

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

    public function create(): View
    {
        return view('admin.school-years.create');
    }

    public function store(StoreSchoolYearRequest $request): RedirectResponse
    {
        $schoolYear = $this->schoolYears->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.school-years.index')
            ->with('success', 'Tahun ajaran '.$schoolYear->name.' berhasil ditambahkan.');
    }

    public function edit(SchoolYear $schoolYear): View
    {
        return view('admin.school-years.edit', [
            'schoolYear' => $schoolYear,
        ]);
    }

    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear): RedirectResponse
    {
        $this->schoolYears->update($schoolYear, $request->validated(), $request->user());

        return redirect()
            ->route('admin.school-years.index')
            ->with('success', 'Data tahun ajaran berhasil diperbarui.');
    }

    /**
     * Set a school year as the active school year.
     */
    public function activate(SchoolYear $schoolYear, Request $request): RedirectResponse
    {
        $this->schoolYears->activate($schoolYear, $request->user());

        return redirect()->route('admin.school-years.index')
            ->with('success', "Tahun ajaran {$schoolYear->name} sekarang aktif.");
    }
}
