<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherClassController extends Controller
{
    /**
     * Display classes and subjects assigned to the logged-in teacher.
     */
    public function index(Request $request): View
    {
        $teacher = $request->user()?->teacher;

        $assignments = $teacher ? $teacher->teachingAssignments()
            ->with(['schoolClass.students' => fn ($q) => $q->where('is_active', true), 'subject', 'schoolYear'])
            ->get() : collect();

        return view('teacher.classes.index', [
            'assignments' => $assignments,
            'teacher' => $teacher,
        ]);
    }
}
