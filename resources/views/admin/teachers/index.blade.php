<x-app-layout>
    <x-slot name="title">Data Guru</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Data Guru"
            description="Daftar tenaga pengajar, NIP, mata pelajaran yang diampu, dan status."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Guru']]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.teachers.create')" icon="plus">Tambah Guru</x-button>
            </x-slot>
        </x-page-header>

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari Guru" />
                    <x-text-input id="search" name="search" :value="$search" placeholder="Nama atau NIP..." class="mt-1 w-full" />
                </div>
                <x-button type="submit" variant="outline" icon="search">Cari</x-button>
                @if ($search)
                    <x-button :href="route('admin.teachers.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            @if ($teachers->isEmpty())
                <x-empty-state icon="teacher" title="Belum ada data guru">
                    Tambahkan guru untuk mulai mengelola tenaga pengajar.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Guru</th>
                                <th class="pb-2 pr-4">NIP</th>
                                <th class="pb-2 pr-4">Penugasan Mengajar</th>
                                <th class="pb-2 pr-4">No. HP</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($teachers as $teacher)
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$teacher->user?->name" />
                                            <div>
                                                <p class="font-medium text-neutral-900">{{ $teacher->user?->name }}</p>
                                                <p class="text-xs text-neutral-500">{{ $teacher->user?->email ?? $teacher->user?->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 font-mono text-xs text-neutral-600">{{ $teacher->nip ?? '—' }}</td>
                                    <td class="py-3 pr-4">
                                        @if ($teacher->teachingAssignments->isEmpty())
                                            <span class="text-xs text-neutral-400">Belum ada penugasan</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($teacher->teachingAssignments as $assignment)
                                                    <x-badge variant="neutral">
                                                        {{ $assignment->subject?->name }} ({{ $assignment->schoolClass?->name }})
                                                    </x-badge>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-500">{{ $teacher->phone ?? $teacher->user?->phone ?? '—' }}</td>
                                    <td class="py-3 pr-4">
                                        @if ($teacher->is_active)
                                            <x-badge variant="success" dot>Aktif</x-badge>
                                        @else
                                            <x-badge variant="neutral">Nonaktif</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button :href="route('admin.teachers.edit', $teacher)" variant="ghost" size="sm" icon="edit">Edit</x-button>
                                            <form method="POST" action="{{ route('admin.teachers.toggle-status', $teacher) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-button type="submit" variant="ghost" size="sm">
                                                    {{ $teacher->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($teachers->hasPages())
                    <div class="mt-4 border-t border-neutral-100 pt-4">
                        {{ $teachers->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-app-layout>
