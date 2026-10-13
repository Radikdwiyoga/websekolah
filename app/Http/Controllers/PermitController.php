<?php

namespace App\Http\Controllers;

use App\Enums\PermitStatus;
use App\Http\Requests\Permit\ReviewPermitRequest;
use App\Models\AttendancePermit;
use App\Services\AttendancePermitService;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermitController extends Controller
{
    public function __construct(
        private readonly AttendancePermitService $permits,
        private readonly AttendanceService $attendance,
    ) {}

    /**
     * Review queue for teachers (their classes) and admins (all classes).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->string('status')->toString();

        $base = AttendancePermit::query()
            ->with(['student.user', 'student.schoolClass', 'reviewedBy'])
            ->when(! $user->isAdmin(), function ($query) use ($user): void {
                $classIds = $user->teacher ? $this->attendance->teacherClassIds($user->teacher) : [];

                $query->whereHas('student', fn ($student) => $student->whereIn('class_id', $classIds));
            });

        $permits = (clone $base)
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByRaw("case when status = 'pending' then 0 else 1 end")
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('permits.teacher.index', [
            'permits' => $permits,
            'status' => $status,
            'statuses' => PermitStatus::cases(),
            'pendingCount' => (clone $base)->pending()->count(),
        ]);
    }

    public function review(AttendancePermit $permit, ReviewPermitRequest $request): RedirectResponse
    {
        $decision = $request->string('decision')->toString();
        $note = $request->filled('note') ? $request->string('note')->toString() : null;

        if ($decision === PermitStatus::Approved->value) {
            $this->permits->approve($permit, $request->user(), $note);

            return redirect()
                ->route('teacher.permits.index')
                ->with('success', 'Pengajuan izin/sakit disetujui.');
        }

        $this->permits->reject($permit, $request->user(), $note);

        return redirect()
            ->route('teacher.permits.index')
            ->with('success', 'Pengajuan izin/sakit ditolak.');
    }
}
