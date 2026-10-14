<x-app-layout>
    <x-slot name="title">Tahun Ajaran</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tahun Ajaran"
            description="Kelola tahun ajaran aktif dan semester berjalan."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Tahun Ajaran']]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.school-years.create')" icon="plus">Tambah Tahun Ajaran</x-button>
            </x-slot>
        </x-page-header>

        <x-card>
            @if ($schoolYears->isEmpty())
                <x-empty-state icon="calendar" title="Belum ada tahun ajaran">
                    Tambahkan tahun ajaran baru untuk memulai operasional sekolah.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Tahun Ajaran</th>
                                <th class="pb-2 pr-4">Semester</th>
                                <th class="pb-2 pr-4">Periode</th>
                                <th class="pb-2 pr-4">Jumlah Kelas</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($schoolYears as $year)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-neutral-900">{{ $year->name }}</td>
                                    <td class="py-3 pr-4 text-neutral-600">{{ $year->semester?->label() ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-xs text-neutral-500">
                                        {{ $year->start_date?->format('d M Y') ?? '—' }} s.d. {{ $year->end_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-700">{{ $year->classes_count }} Kelas</td>
                                    <td class="py-3 pr-4">
                                        @if ($year->is_active)
                                            <x-badge variant="success" dot>Aktif Berjalan</x-badge>
                                        @else
                                            <x-badge variant="neutral">Tidak Aktif</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button :href="route('admin.school-years.edit', $year)" variant="ghost" size="sm" icon="edit">Edit</x-button>
                                            @if (! $year->is_active)
                                                <form method="POST" action="{{ route('admin.school-years.activate', $year) }}">
                                                    @csrf
                                                    <x-button size="sm" variant="outline" type="submit">Jadikan Aktif</x-button>
                                                </form>
                                            @else
                                                <span class="text-xs font-semibold text-success-700">Sedang Aktif</span>
                                            @endif
                                        </div>
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
