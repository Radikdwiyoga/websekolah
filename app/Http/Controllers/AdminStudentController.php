<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
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

        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('admin.students.index', [
            'students' => $students,
            'classes' => $classes,
            'search' => $search,
            'selectedClassId' => $classId,
        ]);
    }
}
