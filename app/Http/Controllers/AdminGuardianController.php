<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminGuardianController extends Controller
{
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
}
