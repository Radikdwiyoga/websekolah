<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClass\StoreSchoolClassRequest;
use App\Http\Requests\SchoolClass\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Services\SchoolClassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminClassController extends Controller
{
    public function __construct(
        private readonly SchoolClassService $classes,
    ) {}

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

        $schoolYears = SchoolYear::query()->orderByDesc('start_date')->get();

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

    public function create(Request $request): View
    {
        return view('admin.classes.create', [
            'schoolYears' => $this->schoolYears(),
            'teachers' => $this->teachers(),
            'selectedYearId' => $request->integer('school_year_id') ?: SchoolYear::query()->where('is_active', true)->value('id'),
        ]);
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        $class = $this->classes->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.classes.show', $class)
            ->with('success', 'Kelas '.$class->name.' berhasil ditambahkan.');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('admin.classes.edit', [
            'class' => $schoolClass->load('schoolYear'),
            'schoolYears' => $this->schoolYears(),
            'teachers' => $this->teachers(),
        ]);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $this->classes->update($schoolClass, $request->validated(), $request->user());

        return redirect()
            ->route('admin.classes.show', $schoolClass)
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * @return Collection<int, SchoolYear>
     */
    private function schoolYears(): Collection
    {
        return SchoolYear::query()->orderByDesc('start_date')->get();
    }

    /**
     * @return Collection<int, Teacher>
     */
    private function teachers(): Collection
    {
        return Teacher::query()->with('user')->orderBy('id')->get();
    }
}
