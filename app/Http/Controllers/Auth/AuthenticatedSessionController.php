<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(SettingService $settings): View
    {
        return view('auth.login', [
            'roles' => UserRole::cases(),
            'schoolName' => $settings->get('school_name', config('app.name', 'Website Sekolah')),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @throws ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->is_active) {
            $this->logout($request);

            throw ValidationException::withMessages([
                'username' => 'Akun Anda telah dinonaktifkan. Hubungi admin sekolah.',
            ]);
        }

        $selectedRole = $request->string('role')->toString();

        if ($request->filled('role') && $user->role?->value !== $selectedRole) {
            $this->logout($request);

            throw ValidationException::withMessages([
                'role' => 'Peran yang dipilih tidak sesuai dengan akun ini.',
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route($user->role->dashboardRoute()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->logout($request);

        return redirect('/');
    }

    /**
     * Log the user out and invalidate the session.
     */
    protected function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
