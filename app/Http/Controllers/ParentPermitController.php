<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permit\StorePermitRequest;
use App\Models\AttendancePermit;
use App\Models\Student;
use App\Services\AttendancePermitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ParentPermitController extends Controller
{
    public function __construct(private readonly AttendancePermitService $permits) {}

    /**
     * List izin/sakit requests for the selected child.
     */
    public function index(Request $request): View
    {
        $children = $this->children($request);

        $selectedChild = $children->firstWhere('id', $request->integer('student') ?: null)
            ?? $children->first();

        $permits = $selectedChild
            ? AttendancePermit::query()
                ->with(['reviewedBy', 'student.user'])
                ->forStudent($selectedChild->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('permits.parent.index', [
            'children' => $children,
            'selectedChild' => $selectedChild,
            'selectedChildId' => $selectedChild?->id,
            'permits' => $permits,
        ]);
    }

    public function store(StorePermitRequest $request): RedirectResponse
    {
        $children = $this->children($request);
        $student = $children->firstWhere('id', $request->integer('student_id'));

        abort_if($student === null, 403);

        $this->permits->submit($student, [
            'date' => $request->string('date')->toString(),
            'type' => $request->string('type')->toString(),
            'reason' => $request->string('reason')->toString(),
            'attachment' => $request->file('attachment'),
        ], $request->user());

        return redirect()
            ->route('parent.permits.index', ['student' => $student->id])
            ->with('success', 'Pengajuan izin/sakit untuk '.($student->user?->name ?? 'anak Anda').' terkirim dan menunggu persetujuan.');
    }

    public function destroy(AttendancePermit $permit, Request $request): RedirectResponse
    {
        $this->authorize('delete', $permit);

        $this->permits->delete($permit, $request->user());

        return redirect()
            ->route('parent.permits.index', ['student' => $permit->student_id])
            ->with('success', 'Pengajuan izin/sakit dibatalkan.');
    }

    /**
     * The students linked to the signed-in guardian.
     *
     * @return Collection<int, Student>
     */
    private function children(Request $request): Collection
    {
        return ($request->user()->guardian?->students()->with(['user', 'schoolClass'])->get() ?? collect())->values();
    }
}
