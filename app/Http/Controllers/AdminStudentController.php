<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    public function __construct(
        private readonly StudentService $students,
    ) {}

    /**
     * Display a paginated list of students with search and class filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $classId = $request->integer('class_id') ?: null;

        $students = Student::query()
            ->with(['user', 'schoolClass'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nisn', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->orderBy('class_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classes' => $this->classOptions(),
            'search' => $search,
            'selectedClassId' => $classId,
        ]);
    }

    /**
     * Show the form to register a new student.
     */
    public function create(Request $request): View
    {
        return view('admin.students.create', [
            'classes' => $this->classOptions(),
            'selectedClassId' => $request->integer('class_id') ?: null,
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = $this->students->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Siswa '.$student->user->name.' berhasil ditambahkan.');
    }

    /**
     * Show the form to edit a student's identity and account data.
     */
    public function edit(Student $student): View
    {
        return view('admin.students.edit', [
            'student' => $student->load('user'),
            'classes' => $this->classOptions(),
            'selectedClassId' => $student->class_id,
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->students->update($student, $request->validated(), $request->user());

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Toggle a student's active status (also controls whether they can log in).
     */
    public function toggleStatus(Request $request, Student $student): RedirectResponse
    {
        $active = ! $student->is_active;

        $this->students->setActive($student, $active, $request->user());

        return back()->with('success', $active ? 'Siswa berhasil diaktifkan.' : 'Siswa berhasil dinonaktifkan.');
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function classOptions(): Collection
    {
        return SchoolClass::query()
            ->with('schoolYear')
            ->orderBy('school_year_id')
            ->orderBy('name')
            ->get();
    }
}
