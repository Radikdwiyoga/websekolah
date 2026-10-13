<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permit\StorePermitRequest;
use App\Models\AttendancePermit;
use App\Services\AttendancePermitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPermitController extends Controller
{
    public function __construct(private readonly AttendancePermitService $permits) {}

    /**
     * List the signed-in student's own izin/sakit requests.
     */
    public function index(Request $request): View
    {
        $student = $request->user()?->student;

        $permits = $student
            ? AttendancePermit::query()
                ->with('reviewedBy')
                ->forStudent($student->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('permits.student.index', [
            'student' => $student,
            'permits' => $permits,
        ]);
    }

    public function store(StorePermitRequest $request): RedirectResponse
    {
        $student = $request->user()->student;

        abort_if($student === null, 403);

        $this->permits->submit($student, [
            'date' => $request->string('date')->toString(),
            'type' => $request->string('type')->toString(),
            'reason' => $request->string('reason')->toString(),
            'attachment' => $request->file('attachment'),
        ], $request->user());

        return redirect()
            ->route('student.permits.index')
            ->with('success', 'Pengajuan izin/sakit terkirim dan menunggu persetujuan.');
    }

    public function destroy(AttendancePermit $permit, Request $request): RedirectResponse
    {
        $this->authorize('delete', $permit);

        $this->permits->delete($permit, $request->user());

        return redirect()
            ->route('student.permits.index')
            ->with('success', 'Pengajuan izin/sakit dibatalkan.');
    }
}
