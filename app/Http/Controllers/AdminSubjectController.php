<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubjectController extends Controller
{
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
}
