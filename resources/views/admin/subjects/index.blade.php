<x-app-layout>
    <x-slot name="title">Data Mata Pelajaran</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Data Mata Pelajaran"
            description="Daftar mata pelajaran dan guru yang ditugaskan mengajar."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Mata Pelajaran']]"
        />

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari Mata Pelajaran" />
                    <x-text-input id="search" name="search" :value="$search" placeholder="Nama mata pelajaran atau kode..." class="mt-1 w-full" />
                </div>
                <x-button type="submit" variant="outline" icon="search">Cari</x-button>
                @if ($search)
                    <x-button :href="route('admin.subjects.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            @if ($subjects->isEmpty())
                <x-empty-state icon="book" title="Tidak ada mata pelajaran">
                    Belum ada mata pelajaran yang cocok dengan pencarian.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Kode</th>
                                <th class="pb-2 pr-4">Nama Mata Pelajaran</th>
                                <th class="pb-2 pr-4">Guru Pengampu</th>
                                <th class="pb-2 text-right">Penugasan Kelas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($subjects as $subject)
                                <tr>
                                    <td class="py-3 pr-4 font-mono text-xs font-bold text-neutral-700">
                                        {{ $subject->code }}
                                    </td>
                                    <td class="py-3 pr-4 font-semibold text-neutral-900">
                                        {{ $subject->name }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        @php
                                            $teachers = $subject->teachingAssignments->pluck('teacher.user.name')->filter()->unique();
                                        @endphp
                                        @if ($teachers->isEmpty())
                                            <span class="text-xs text-neutral-400">Belum ada guru</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($teachers as $teacherName)
                                                    <x-badge variant="neutral">{{ $teacherName }}</x-badge>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right text-xs text-neutral-500">
                                        {{ $subject->teachingAssignments->count() }} Penugasan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>

