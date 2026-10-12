<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentStatus;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAssignmentController extends Controller
{
    /**
     * Display all assignments across the school with filtering.
     */
    public function index(Request $request): View
    {
        $classId = $request->integer('class_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;
        $status = AssignmentStatus::tryFrom((string) $request->query('status', ''));

        $assignments = Assignment::query()
            ->with(['teacher.user', 'schoolClass', 'subject'])
            ->withCount('submissions')
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $classes = SchoolClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        return view('admin.assignments.index', [
            'assignments' => $assignments,
            'classes' => $classes,
            'subjects' => $subjects,
            'selectedClassId' => $classId,
            'selectedSubjectId' => $subjectId,
            'selectedStatus' => $status,
            'statuses' => AssignmentStatus::cases(),
        ]);
    }
}
