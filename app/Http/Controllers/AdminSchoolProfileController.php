<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateSchoolProfileRequest;
use App\Services\SchoolProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSchoolProfileController extends Controller
{
    public function __construct(private readonly SchoolProfileService $profiles) {}

    /**
     * Show the school profile form (identity, logo, address, contact, social media).
     */
    public function edit(): View
    {
        return view('admin.settings.school', [
            'school' => $this->profiles->all(),
        ]);
    }

    /**
     * Persist the school profile.
     */
    public function update(UpdateSchoolProfileRequest $request): RedirectResponse
    {
        $this->profiles->update(
            $request->safe()->except(['logo', 'remove_logo']),
            $request->file('logo'),
            $request->boolean('remove_logo'),
            $request->user(),
        );

        return redirect()
            ->route('admin.settings.school.edit')
            ->with('success', 'Profil sekolah berhasil diperbarui.');
    }
}
