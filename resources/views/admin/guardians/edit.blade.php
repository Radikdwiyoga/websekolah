<x-app-layout>
    <x-slot name="title">Edit Orang Tua</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Orang Tua"
            description="Perbarui profil wali murid dan kelola siswa yang terhubung."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Orang Tua', 'route' => 'admin.guardians.index'], ['label' => $guardian->user?->name ?? 'Edit Orang Tua']]"
        >
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.guardians.toggle-status', $guardian) }}">
                    @csrf
                    @method('PATCH')
                    <x-button
                        type="submit"
                        :variant="$guardian->user?->is_active ? 'outline' : 'secondary'"
                        :icon="$guardian->user?->is_active ? 'x-circle' : 'check'"
                    >
                        {{ $guardian->user?->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                    </x-button>
                </form>
            </x-slot>
        </x-page-header>

        <form method="POST" action="{{ route('admin.guardians.update', $guardian) }}">
            @csrf
            @method('PUT')

            @include('admin.guardians._form', ['guardian' => $guardian])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.guardians.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>

        <x-card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-neutral-900">Siswa Terhubung</h2>
                    <p class="mt-0.5 text-sm text-neutral-500">Hubungkan wali ini dengan anak/siswa yang menjadi tanggungannya.</p>
                </div>
                <x-badge variant="neutral">{{ $guardian->students->count() }} Siswa</x-badge>
            </div>

            @if ($guardian->students->isEmpty())
                <p class="mt-4 rounded-lg border border-dashed border-neutral-300 px-4 py-6 text-center text-sm text-neutral-400">
                    Belum ada siswa yang terhubung.
                </p>
            @else
                <ul class="mt-4 divide-y divide-neutral-100">
                    @foreach ($guardian->students as $student)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <div class="text-sm">
                                <span class="font-semibold text-neutral-900">{{ $student->user?->name }}</span>
                                <span class="text-neutral-500">
                                    ({{ $student->schoolClass?->name ?? 'Tanpa kelas' }})
                                    @if ($student->pivot->relation)
                                        &middot; {{ $student->pivot->relation }}
                                    @endif
                                </span>
                                @if ($student->pivot->is_primary)
                                    <x-badge variant="success" class="ml-1">Wali Utama</x-badge>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.guardians.detach-student', [$guardian, $student]) }}">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="ghost" size="sm" icon="trash">Putuskan</x-button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('admin.guardians.attach-student', $guardian) }}" class="mt-5 grid gap-3 border-t border-neutral-100 pt-5 sm:grid-cols-[2fr_1fr_auto] sm:items-end">
                @csrf
                <div>
                    <x-input-label for="student_id" value="Siswa" />
                    <x-select id="student_id" name="student_id" class="mt-1 w-full" required>
                        <option value="">Pilih siswa</option>
                        @foreach ($students as $studentOption)
                            <option value="{{ $studentOption->id }}" @selected(old('student_id') == $studentOption->id)>
                                {{ $studentOption->user?->name }} ({{ $studentOption->nis }})
                            </option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="relation" value="Relasi" />
                    <x-text-input id="relation" name="relation" :value="old('relation')" class="mt-1 w-full" placeholder="Ayah / Ibu / Wali" />
                    <x-input-error :messages="$errors->get('relation')" class="mt-1" />
                </div>
                <x-button type="submit" icon="plus">Hubungkan</x-button>
                <label class="flex items-center gap-2 sm:col-span-3">
                    <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary')) class="h-4 w-4 rounded border-neutral-300 text-primary-700 focus:ring-primary-500/40">
                    <span class="text-sm text-neutral-600">Jadikan wali utama</span>
                </label>
            </form>
        </x-card>
    </div>
</x-app-layout>
