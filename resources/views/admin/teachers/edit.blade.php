<x-app-layout>
    <x-slot name="title">Edit Guru</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Guru"
            description="Perbarui profil guru dan kelola penugasan mengajarnya."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Guru', 'route' => 'admin.teachers.index'], ['label' => $teacher->user?->name ?? 'Edit Guru']]"
        >
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.teachers.toggle-status', $teacher) }}">
                    @csrf
                    @method('PATCH')
                    <x-button
                        type="submit"
                        :variant="$teacher->is_active ? 'outline' : 'secondary'"
                        :icon="$teacher->is_active ? 'x-circle' : 'check'"
                    >
                        {{ $teacher->is_active ? 'Nonaktifkan Guru' : 'Aktifkan Guru' }}
                    </x-button>
                </form>
            </x-slot>
        </x-page-header>

        <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}">
            @csrf
            @method('PUT')

            @include('admin.teachers._form', ['teacher' => $teacher])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.teachers.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>

        <x-card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-neutral-900">Penugasan Mengajar</h2>
                    <p class="mt-0.5 text-sm text-neutral-500">Tentukan mata pelajaran dan kelas yang diampu guru ini.</p>
                </div>
                <x-badge variant="neutral">{{ $teacher->teachingAssignments->count() }} Penugasan</x-badge>
            </div>

            @if ($teacher->teachingAssignments->isEmpty())
                <p class="mt-4 rounded-lg border border-dashed border-neutral-300 px-4 py-6 text-center text-sm text-neutral-400">
                    Belum ada penugasan mengajar.
                </p>
            @else
                <ul class="mt-4 divide-y divide-neutral-100">
                    @foreach ($teacher->teachingAssignments as $assignment)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <div class="text-sm">
                                <span class="font-semibold text-neutral-900">{{ $assignment->subject?->name ?? '—' }}</span>
                                <span class="text-neutral-500">di {{ $assignment->schoolClass?->name ?? '—' }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.teachers.unassign', [$teacher, $assignment]) }}">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="ghost" size="sm" icon="trash">Hapus</x-button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('admin.teachers.assign', $teacher) }}" class="mt-5 space-y-4 border-t border-neutral-100 pt-5">
                @csrf

                <div class="sm:max-w-xs">
                    <x-input-label for="class_id" value="Kelas" />
                    <x-select id="class_id" name="class_id" class="mt-1 w-full" required>
                        <option value="">Pilih kelas</option>
                        @foreach ($classes as $classOption)
                            <option value="{{ $classOption->id }}" @selected(old('class_id') == $classOption->id)>{{ $classOption->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('class_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label value="Mata Pelajaran" />
                    <p class="text-xs text-neutral-500">Centang satu atau lebih mata pelajaran yang diampu di kelas ini.</p>
                    <div class="mt-1 max-h-64 overflow-y-auto rounded-md border border-neutral-300 bg-white p-3">
                        @if ($subjects->isEmpty())
                            <p class="text-sm text-neutral-400">Belum ada mata pelajaran. Tambahkan dulu melalui menu Mata Pelajaran.</p>
                        @else
                            <div class="grid gap-1 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($subjects as $subjectOption)
                                    <label class="flex items-center gap-2.5 rounded-md px-2 py-1.5 transition hover:bg-neutral-50">
                                        <input
                                            type="checkbox"
                                            name="subject_ids[]"
                                            value="{{ $subjectOption->id }}"
                                            @checked(in_array($subjectOption->id, old('subject_ids', [])))
                                            class="h-4 w-4 rounded border-neutral-300 text-primary-700 focus:ring-primary-500/40"
                                        >
                                        <span class="text-sm text-neutral-700">{{ $subjectOption->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('subject_ids')" class="mt-1" />
                    <x-input-error :messages="$errors->get('subject_ids.*')" class="mt-1" />
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" icon="plus">Tambah Penugasan</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
