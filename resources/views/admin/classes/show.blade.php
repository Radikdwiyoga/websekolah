<x-app-layout>
    <x-slot name="title">Kelas {{ $class->name }}</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Kelas {{ $class->name }}"
            description="Kelola daftar siswa yang terdaftar pada kelas ini."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Kelas', 'route' => 'admin.classes.index'], ['label' => $class->name]]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.students.create', ['class_id' => $class->id])" icon="plus">Tambah Siswa</x-button>
            </x-slot>
        </x-page-header>

        <x-card>
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-neutral-500">Tingkat</dt>
                    <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $class->level ? 'Kelas '.$class->level : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-neutral-500">Tahun Ajaran</dt>
                    <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $class->schoolYear?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-neutral-500">Wali Kelas</dt>
                    <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $class->homeroomTeacher?->user?->name ?? 'Belum ditentukan' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-neutral-500">Jumlah Siswa</dt>
                    <dd class="mt-1 text-sm font-semibold text-neutral-900">
                        {{ $class->students_count }} Siswa
                        @if ($class->capacity)
                            <span class="font-normal text-neutral-400">/ {{ $class->capacity }} kapasitas</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-card>

        <x-card>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold text-neutral-900">Daftar Siswa</h2>
                <span class="text-xs text-neutral-400">{{ $students->total() }} siswa</span>
            </div>

            @if ($students->isEmpty())
                <x-empty-state icon="students" title="Belum ada siswa di kelas ini">
                    Tambahkan siswa untuk mulai mengelola data kelas.
                    <x-slot name="action">
                        <x-button :href="route('admin.students.create', ['class_id' => $class->id])" icon="plus">Tambah Siswa</x-button>
                    </x-slot>
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Siswa</th>
                                <th class="pb-2 pr-4">NISN / NIS</th>
                                <th class="pb-2 pr-4">Jenis Kelamin</th>
                                <th class="pb-2 pr-4">Kontak</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($students as $student)
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$student->user?->name ?? 'Siswa'" size="sm" />
                                            <div>
                                                <p class="font-medium text-neutral-900">{{ $student->user?->name ?? '—' }}</p>
                                                <p class="text-xs text-neutral-400">{{ $student->user?->email ?? $student->user?->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        <p class="font-mono text-xs font-semibold text-neutral-800">{{ $student->nisn ?? '—' }}</p>
                                        <p class="text-xs text-neutral-400">NIS: {{ $student->nis ?? '—' }}</p>
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        {{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '—') }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-500">
                                        {{ $student->user?->phone ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if ($student->is_active)
                                            <x-badge variant="success" dot>Aktif</x-badge>
                                        @else
                                            <x-badge variant="neutral">Nonaktif</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <x-button :href="route('admin.students.edit', $student)" variant="ghost" size="sm" icon="edit">Edit</x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($students->hasPages())
                    <div class="mt-4 border-t border-neutral-100 pt-4">
                        {{ $students->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-app-layout>
