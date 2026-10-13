<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminClassController extends Controller
{
    /**
     * Display classes with homeroom teachers and student counts.
     */
    public function index(Request $request): View
    {
        $yearId = $request->integer('school_year_id') ?: SchoolYear::query()->where('is_active', true)->value('id');

        $classes = SchoolClass::query()
            ->with(['schoolYear', 'homeroomTeacher.user'])
            ->withCount('students')
            ->when($yearId, fn ($query) => $query->where('school_year_id', $yearId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $schoolYears = SchoolYear::query()->orderByDesc('starts_at')->get();

        return view('admin.classes.index', [
            'classes' => $classes,
            'schoolYears' => $schoolYears,
            'selectedYearId' => $yearId,
        ]);
    }

    /**
     * Detail of a single class with its enrolled students.
     */
    public function show(SchoolClass $schoolClass): View
    {
        $schoolClass->load(['schoolYear', 'homeroomTeacher.user'])->loadCount('students');

        $students = $schoolClass->students()
            ->with('user')
            ->orderBy('nis')
            ->paginate(20)
            ->withQueryString();

        return view('admin.classes.show', [
            'class' => $schoolClass,
            'students' => $students,
        ]);
    }
}
