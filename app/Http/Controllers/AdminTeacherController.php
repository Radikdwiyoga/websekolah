<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\StoreTeachingAssignmentRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTeacherController extends Controller
{
    public function __construct(
        private readonly TeacherService $teachers,
    ) {}

    /**
     * Display a paginated list of teachers with search and assignment details.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $teachers = Teacher::query()
            ->with(['user', 'teachingAssignments.subject', 'teachingAssignments.schoolClass'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->paginate(15)
            ->withQueryString();

        return view('admin.teachers.index', [
            'teachers' => $teachers,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $teacher = $this->teachers->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.teachers.edit', $teacher)
            ->with('success', 'Guru '.$teacher->user->name.' berhasil ditambahkan. Login memakai username '.$teacher->user->username.'.');
    }

    /**
     * Show the edit form including the teacher's teaching assignments.
     */
    public function edit(Teacher $teacher): View
    {
        $teacher->load(['user', 'teachingAssignments.subject', 'teachingAssignments.schoolClass']);

        return view('admin.teachers.edit', [
            'teacher' => $teacher,
            'subjects' => Subject::query()->orderBy('name')->get(),
            'classes' => SchoolClass::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $this->teachers->update($teacher, $request->validated(), $request->user());

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Teacher $teacher): RedirectResponse
    {
        $active = ! $teacher->is_active;

        $this->teachers->setActive($teacher, $active, $request->user());

        return back()->with('success', $active ? 'Guru diaktifkan.' : 'Guru dinonaktifkan.');
    }

    public function assignTeaching(StoreTeachingAssignmentRequest $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validated();

        $created = $this->teachers->assignTeachings(
            $teacher,
            SchoolClass::query()->findOrFail($validated['class_id']),
            $validated['subject_ids'],
            $request->user(),
        );

        $message = $created > 0
            ? $created.' penugasan mengajar ditambahkan.'
            : 'Mata pelajaran yang dipilih sudah ditugaskan di kelas tersebut.';

        return back()->with('success', $message);
    }

    public function removeTeaching(Request $request, Teacher $teacher, TeachingAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->teacher_id === $teacher->id, 404);

        $this->teachers->removeTeaching($assignment, $request->user());

        return back()->with('success', 'Penugasan mengajar dihapus.');
    }
}
