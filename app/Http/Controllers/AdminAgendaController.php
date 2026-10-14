<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agenda\StoreAgendaRequest;
use App\Http\Requests\Agenda\UpdateAgendaRequest;
use App\Models\SchoolAgenda;
use App\Services\SchoolAgendaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAgendaController extends Controller
{
    public function __construct(
        private readonly SchoolAgendaService $agendas,
    ) {}

    /**
     * List the promotional photo/video items for the public homepage.
     */
    public function index(): View
    {
        return view('admin.agendas.index', [
            'agendas' => SchoolAgenda::query()
                ->orderByDesc('is_published')
                ->orderBy('sort_order')
                ->orderByDesc('event_date')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.agendas.create');
    }

    public function store(StoreAgendaRequest $request): RedirectResponse
    {
        $agenda = $this->agendas->create(
            $request->validated(),
            $request->file('media'),
            $request->user(),
        );

        return redirect()
            ->route('admin.agendas.index')
            ->with('success', 'Agenda '.$agenda->title.' berhasil ditambahkan.');
    }

    public function edit(SchoolAgenda $agenda): View
    {
        return view('admin.agendas.edit', [
            'agenda' => $agenda,
        ]);
    }

    public function update(UpdateAgendaRequest $request, SchoolAgenda $agenda): RedirectResponse
    {
        $this->agendas->update(
            $agenda,
            $request->validated(),
            $request->file('media'),
            $request->user(),
        );

        return redirect()
            ->route('admin.agendas.index')
            ->with('success', 'Agenda '.$agenda->title.' berhasil diperbarui.');
    }

    public function destroy(Request $request, SchoolAgenda $agenda): RedirectResponse
    {
        $this->agendas->delete($agenda, $request->user());

        return redirect()
            ->route('admin.agendas.index')
            ->with('success', 'Agenda berhasil dihapus.');
    }

    public function togglePublished(Request $request, SchoolAgenda $agenda): RedirectResponse
    {
        $this->agendas->togglePublished($agenda, $request->user());

        return redirect()
            ->route('admin.agendas.index')
            ->with('success', $agenda->is_published
                ? 'Agenda berhasil diterbitkan.'
                : 'Agenda berhasil disembunyikan.');
    }
}
