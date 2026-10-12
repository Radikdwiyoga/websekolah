<x-app-layout>
    <x-slot name="title">Tugas & Penilaian</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tugas & Penilaian"
            description="Pantau seluruh tugas aktif, tenggat waktu, dan pengumpulan tugas siswa."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Tugas & Penilaian']]"
        />

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full sm:w-60">
                    <x-input-label for="class_id" value="Kelas" />
                    <x-select id="class_id" name="class_id" class="mt-1">
                        <option value="">Semua Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected($selectedClassId === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full sm:w-60">
                    <x-input-label for="subject_id" value="Mata Pelajaran" />
                    <x-select id="subject_id" name="subject_id" class="mt-1">
                        <option value="">Semua Mapel</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full sm:w-48">
                    <x-input-label for="status" value="Status" />
                    <x-select id="status" name="status" class="mt-1">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" @selected($selectedStatus?->value === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-button type="submit" variant="outline" icon="search">Filter</x-button>
                @if ($selectedClassId || $selectedSubjectId || $selectedStatus)
                    <x-button :href="route('admin.assignments.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            @if ($assignments->isEmpty())
                <x-empty-state icon="task" title="Belum ada tugas">
                    Belum ada tugas yang cocok dengan filter.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Judul Tugas</th>
                                <th class="pb-2 pr-4">Guru</th>
                                <th class="pb-2 pr-4">Kelas & Mapel</th>
                                <th class="pb-2 pr-4">Tenggat</th>
                                <th class="pb-2 pr-4">Pengumpulan</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($assignments as $assignment)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-neutral-900">
                                        {{ $assignment->title }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-700">
                                        {{ $assignment->teacher?->user?->name ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-600">
                                        <span class="font-medium text-neutral-900">{{ $assignment->schoolClass?->name }}</span> &middot; {{ $assignment->subject?->name }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-500">
                                        {{ $assignment->deadline?->translatedFormat('d M Y, H.i') ?? 'Tanpa tenggat' }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs font-semibold text-primary-700">
                                        {{ $assignment->submissions_count }} Siswa
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-badge :variant="$assignment->status->variant()">{{ $assignment->status->label() }}</x-badge>
                                    </td>
                                    <td class="py-3 text-right">
                                        <x-button :href="route('teacher.assignments.show', $assignment)" size="sm" variant="outline">
                                            Detail
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($assignments->hasPages())
                    <div class="mt-4 border-t border-neutral-100 pt-4">
                        {{ $assignments->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-app-layout>

