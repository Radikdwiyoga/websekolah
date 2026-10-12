<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTeacherController extends Controller
{
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
}
