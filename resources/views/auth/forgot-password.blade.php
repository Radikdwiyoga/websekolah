<x-guest-layout>
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-md sm:p-8">
                <h1 class="text-2xl font-bold text-neutral-950">Lupa Kata Sandi</h1>
                <p class="mt-1 text-sm text-neutral-500">
                    Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.
                </p>

                <x-auth-session-status class="mt-6" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5" data-submit-loading>
                    @csrf

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email')" required autofocus autocomplete="email" placeholder="nama@sekolah.test" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-primary-700 hover:text-primary-800">
                            Kembali ke login
                        </a>
                        <x-button type="submit" data-loading-text="Mengirim…">Kirim Tautan</x-button>
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
