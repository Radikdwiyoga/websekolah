<x-app-layout>
    <x-slot name="title">Profil Saya</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Profil Saya"
            description="Informasi akun pengguna dan pengaturan keamanan sandi."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard'], ['label' => 'Profil Saya']]"
        />

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Kartu Info Pengguna --}}
            <x-card class="flex flex-col items-center text-center">
                <x-avatar :name="$user->name" size="lg" />
                <h2 class="mt-4 text-lg font-bold text-neutral-900">{{ $user->name }}</h2>
                <p class="text-xs text-neutral-500">{{ $user->email ?? $user->username }}</p>
                <div class="mt-3">
                    <x-badge variant="primary" dot>{{ $user->role->label() }}</x-badge>
                </div>

                <div class="mt-6 w-full divide-y divide-neutral-100 border-t border-neutral-100 pt-4 text-left text-xs">
                    <div class="flex justify-between py-2">
                        <span class="text-neutral-500">Username</span>
                        <span class="font-medium text-neutral-800">{{ $user->username }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-neutral-500">No. Telepon / WA</span>
                        <span class="font-medium text-neutral-800">{{ $user->phone ?? '—' }}</span>
                    </div>

                    @if ($user->student)
                        <div class="flex justify-between py-2">
                            <span class="text-neutral-500">NISN</span>
                            <span class="font-mono font-medium text-neutral-800">{{ $user->student->nisn }}</span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-neutral-500">Kelas</span>
                            <span class="font-medium text-neutral-800">{{ $user->student->schoolClass?->name ?? '—' }}</span>
                        </div>
                    @endif

                    @if ($user->teacher)
                        <div class="flex justify-between py-2">
                            <span class="text-neutral-500">NIP</span>
                            <span class="font-mono font-medium text-neutral-800">{{ $user->teacher->nip ?? '—' }}</span>
                        </div>
                    @endif

                    @if ($user->guardian)
                        <div class="flex justify-between py-2">
                            <span class="text-neutral-500">Pekerjaan</span>
                            <span class="font-medium text-neutral-800">{{ $user->guardian->occupation ?? '—' }}</span>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Form Ubah Password --}}
            <x-card class="lg:col-span-2">
                <h3 class="text-base font-semibold text-neutral-900">Ubah Password</h3>
                <p class="text-xs text-neutral-500">Pastikan akun Anda menggunakan kata sandi yang aman dan tidak mudah ditebak.</p>

                <form method="POST" action="{{ route('profile.update-password') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="current_password" value="Password Saat Ini" />
                        <x-text-input id="current_password" name="current_password" type="password" class="mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password Baru" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" icon="check">Simpan Perubahan Password</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>

