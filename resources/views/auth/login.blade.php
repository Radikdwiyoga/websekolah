<x-guest-layout>
    <div class="flex min-h-screen flex-col lg:flex-row">
        {{-- Branding panel --}}
        <aside class="relative flex flex-col justify-between overflow-hidden bg-primary-900 px-8 py-12 text-white lg:w-1/2 lg:px-14 lg:py-16">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-primary-700/40 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-primary-600/30 blur-3xl"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-white/10 text-base font-bold">WS</span>
                    <span class="text-lg font-semibold">{{ $schoolName }}</span>
                </div>
            </div>

            <div class="relative mt-12 max-w-md lg:mt-0">
                <h1 class="text-3xl font-bold leading-tight lg:text-4xl">
                    Bersama Membangun Generasi Masa Depan
                </h1>
                <p class="mt-4 text-base text-primary-100">
                    Absensi digital, notifikasi ke orang tua, dan tugas sekolah dalam satu platform yang mudah digunakan.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-primary-100">
                    <li class="flex items-center gap-3">
                        <span aria-hidden="true" class="text-success-500">&#10003;</span> Absensi digital per kelas
                    </li>
                    <li class="flex items-center gap-3">
                        <span aria-hidden="true" class="text-success-500">&#10003;</span> Tugas dan penilaian otomatis
                    </li>
                    <li class="flex items-center gap-3">
                        <span aria-hidden="true" class="text-success-500">&#10003;</span> Rekap absensi dan nilai
                    </li>
                </ul>
            </div>

            <p class="relative mt-12 text-xs text-primary-100/70">
                &copy; {{ date('Y') }} {{ $schoolName }}
            </p>
        </aside>

        {{-- Form panel --}}
        <main class="flex flex-1 items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-md sm:p-8">
                    <h2 class="text-2xl font-bold text-neutral-950">Masuk ke Akun</h2>
                    <p class="mt-1 text-sm text-neutral-500">Silakan pilih peran lalu masukkan kredensial Anda.</p>

                    <x-auth-session-status class="mt-6" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" data-submit-loading>
                        @csrf

                        <fieldset>
                            <legend class="block text-sm font-medium text-neutral-700">Masuk sebagai</legend>
                            <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach ($roles as $role)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="{{ $role->value }}" class="peer sr-only" @checked(old('role') === $role->value)>
                                        <span class="flex items-center justify-center rounded-md border border-neutral-300 px-2 py-2 text-center text-xs font-semibold text-neutral-600 transition peer-checked:border-primary-600 peer-checked:bg-primary-100 peer-checked:text-primary-800 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500">
                                            {{ $role->label() }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('role')" />
                        </fieldset>

                        <div>
                            <x-input-label for="username" value="NIS / Username" />
                            <x-text-input id="username" name="username" type="text" class="mt-1.5" :value="old('username')" required autofocus autocomplete="username" placeholder="Masukkan NIS atau username" />
                            <x-input-error :messages="$errors->get('username')" />
                        </div>

                        <div>
                            <x-input-label for="password" value="Kata Sandi" />
                            <x-text-input id="password" name="password" type="password" class="mt-1.5" required autocomplete="current-password" placeholder="Masukkan kata sandi" />
                            <x-input-error :messages="$errors->get('password')" />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label class="flex items-center gap-2 text-sm text-neutral-600">
                                <input type="checkbox" name="remember" value="1" class="rounded-sm border-neutral-300 text-primary-700 focus:ring-primary-500" @checked(old('remember'))>
                                Ingat saya
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-700 hover:text-primary-800">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>

                        <x-button type="submit" class="w-full" data-loading-text="Memproses…">
                            Masuk
                        </x-button>
                    </form>

                    <div class="mt-6 border-t border-neutral-100 pt-4 text-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-neutral-500 transition hover:text-primary-800">
                            <x-icon name="chevron-left" :size="4" />
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-guest-layout>
