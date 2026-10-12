<x-guest-layout>
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-md sm:p-8">
                <h1 class="text-2xl font-bold text-neutral-950">Atur Ulang Kata Sandi</h1>
                <p class="mt-1 text-sm text-neutral-500">Buat kata sandi baru untuk akun Anda.</p>

                <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5" data-submit-loading>
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email', $request->email)" required autofocus autocomplete="email" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Kata Sandi Baru" />
                        <x-text-input id="password" name="password" type="password" class="mt-1.5" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" />
                    </div>

                    <div class="flex items-center justify-end">
                        <x-button type="submit" data-loading-text="Menyimpan…">Simpan Kata Sandi</x-button>
                    </div>
                </form>

                <div class="mt-6 border-t border-neutral-100 pt-4 text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-neutral-500 transition hover:text-primary-800">
                        <x-icon name="chevron-left" :size="4" />
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
