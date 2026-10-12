<x-app-layout>
    <x-slot name="title">Data Siswa</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Data Siswa"
            description="Daftar seluruh siswa terdaftar, NISN, kelas, dan status keaktifan."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Siswa']]"
        />

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari Siswa" />
                    <x-text-input id="search" name="search" :value="$search" placeholder="Nama, NISN, atau NIS..." class="mt-1 w-full" />
                </div>
                <div class="w-full sm:w-64">
                    <x-input-label for="class_id" value="Filter Kelas" />
                    <x-select id="class_id" name="class_id" class="mt-1">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected($selectedClassId === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-button type="submit" variant="outline" icon="search">Cari</x-button>
                @if ($search || $selectedClassId)
                    <x-button :href="route('admin.students.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            @if ($students->isEmpty())
                <x-empty-state icon="students" title="Tidak ada data siswa">
                    Belum ada siswa yang cocok dengan filter atau pencarian.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Siswa</th>
                                <th class="pb-2 pr-4">NISN / NIS</th>
                                <th class="pb-2 pr-4">Kelas</th>
                                <th class="pb-2 pr-4">Jenis Kelamin</th>
                                <th class="pb-2 pr-4">Kontak</th>
                                <th class="pb-2 text-right">Status</th>
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
                                        <p class="font-mono text-xs font-semibold text-neutral-800">{{ $student->nisn }}</p>
                                        <p class="text-xs text-neutral-400">NIS: {{ $student->nis ?? '—' }}</p>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-badge variant="info">{{ $student->schoolClass?->name ?? 'Belum ada kelas' }}</x-badge>
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        {{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : ($student->gender ?? '—')) }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-500">
                                        {{ $student->user?->phone ?? '—' }}
                                    </td>
                                    <td class="py-3 text-right">
                                        @if ($student->is_active)
                                            <x-badge variant="success" dot>Aktif</x-badge>
                                        @else
                                            <x-badge variant="neutral">Nonaktif</x-badge>
                                        @endif
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
