<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the current user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile.show', [
            'user' => $user->loadMissing([
                'student.schoolClass',
                'teacher.teachingAssignments.subject',
                'teacher.teachingAssignments.schoolClass',
                'guardian.students.schoolClass',
            ]),
        ]);
    }

    /**
     * Update the current user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password berhasil diperbarui.');
    }
}
