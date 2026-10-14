<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guardian\AttachStudentRequest;
use App\Http\Requests\Guardian\StoreGuardianRequest;
use App\Http\Requests\Guardian\UpdateGuardianRequest;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\GuardianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGuardianController extends Controller
{
    public function __construct(
        private readonly GuardianService $guardians,
    ) {}

    /**
     * Display a paginated list of parents/guardians with linked students.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $guardians = Guardian::query()
            ->with(['user', 'students.user', 'students.schoolClass'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('phone', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('students.user', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->paginate(15)
            ->withQueryString();

        return view('admin.guardians.index', [
            'guardians' => $guardians,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.guardians.create');
    }

    public function store(StoreGuardianRequest $request): RedirectResponse
    {
        $guardian = $this->guardians->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.guardians.edit', $guardian)
            ->with('success', 'Orang tua/wali '.$guardian->user->name.' berhasil ditambahkan. Login memakai username '.$guardian->user->username.'.');
    }

    /**
     * Show the edit form and the linked students management.
     */
    public function edit(Guardian $guardian): View
    {
        $guardian->load(['user', 'students.user', 'students.schoolClass']);

        return view('admin.guardians.edit', [
            'guardian' => $guardian,
            'students' => Student::query()->with(['user', 'schoolClass'])->orderBy('nis')->get(),
        ]);
    }

    public function update(UpdateGuardianRequest $request, Guardian $guardian): RedirectResponse
    {
        $this->guardians->update($guardian, $request->validated(), $request->user());

        return redirect()
            ->route('admin.guardians.index')
            ->with('success', 'Data orang tua/wali berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Guardian $guardian): RedirectResponse
    {
        $active = ! $guardian->user->is_active;

        $this->guardians->setActive($guardian, $active, $request->user());

        return back()->with('success', $active ? 'Akun orang tua/wali diaktifkan.' : 'Akun orang tua/wali dinonaktifkan.');
    }

    public function attachStudent(AttachStudentRequest $request, Guardian $guardian): RedirectResponse
    {
        $validated = $request->validated();

        $student = Student::query()->findOrFail($validated['student_id']);

        $this->guardians->attachStudent(
            $guardian,
            $student,
            $validated['relation'] ?? null,
            (bool) ($validated['is_primary'] ?? false),
            $request->user(),
        );

        return back()->with('success', 'Siswa berhasil dihubungkan.');
    }

    public function detachStudent(Request $request, Guardian $guardian, Student $student): RedirectResponse
    {
        $this->guardians->detachStudent($guardian, $student, $request->user());

        return back()->with('success', 'Siswa berhasil diputuskan.');
    }
}
