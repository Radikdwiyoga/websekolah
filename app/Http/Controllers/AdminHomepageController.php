<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateHomepageContentRequest;
use App\Services\HomepageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminHomepageController extends Controller
{
    public function __construct(private readonly HomepageContentService $homepage) {}

    /**
     * Show the homepage content form (hero, keunggulan, program).
     */
    public function edit(): View
    {
        return view('admin.settings.homepage', [
            'home' => $this->homepage->all(),
        ]);
    }

    /**
     * Persist the homepage content.
     */
    public function update(UpdateHomepageContentRequest $request): RedirectResponse
    {
        $this->homepage->update($request->validated(), $request->user());

        return redirect()
            ->route('admin.settings.homepage.edit')
            ->with('success', 'Konten halaman utama berhasil diperbarui.');
    }
}
