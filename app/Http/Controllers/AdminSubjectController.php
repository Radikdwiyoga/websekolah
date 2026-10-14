<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubjectController extends Controller
{
    public function __construct(
        private readonly SubjectService $subjects,
    ) {}

    /**
     * Display all subjects along with assigned teachers.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $subjects = Subject::query()
            ->with(['teachingAssignments.teacher.user', 'teachingAssignments.schoolClass'])
            ->when($search, function ($query, string $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return view('admin.subjects.index', [
            'subjects' => $subjects,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $subject = $this->subjects->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran '.$subject->name.' berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', [
            'subject' => $subject,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->subjects->update($subject, $request->validated(), $request->user());

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }
}
