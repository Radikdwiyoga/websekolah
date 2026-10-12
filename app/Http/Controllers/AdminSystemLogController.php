<?php

namespace App\Http\Controllers;

use App\Enums\SystemLogStatus;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSystemLogController extends Controller
{
    /**
     * Display a paginated list of system logs with filters.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $module = $request->query('module');
        $status = SystemLogStatus::tryFrom((string) $request->query('status', ''));

        $logs = SystemLog::query()
            ->with('user')
            ->when($search, fn ($query) => $query->where('description', 'like', "%{$search}%"))
            ->when($module, fn ($query) => $query->where('module', $module))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $modules = SystemLog::query()->distinct()->pluck('module')->filter()->values();

        return view('admin.system-logs.index', [
            'logs' => $logs,
            'modules' => $modules,
            'selectedModule' => $module,
            'selectedStatus' => $status,
            'search' => $search,
            'statuses' => SystemLogStatus::cases(),
        ]);
    }
}
