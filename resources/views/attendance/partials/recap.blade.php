@php
    $exportParams = array_filter([
        'month' => $recap['month'],
        'class_id' => $recap['selectedClassId'],
        'status' => $recap['status']?->value,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="space-y-6">
    <x-card>
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="flex-1">
                <x-input-label for="month" value="Periode" />
                <input
                    type="month"
                    id="month"
                    name="month"
                    value="{{ $recap['month'] }}"
                    class="mt-1 block w-full rounded-md border border-neutral-300 bg-white px-3.5 py-2.5 text-sm text-neutral-950 shadow-sm focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                >
            </div>

            <div class="flex-1">
                <x-input-label for="class_id" value="Kelas" />
                <x-select id="class_id" name="class_id" class="mt-1">
                    <option value="">Semua kelas</option>
                    @foreach ($recap['classes'] as $classOption)
                        <option value="{{ $classOption->id }}" @selected($recap['selectedClassId'] === $classOption->id)>{{ $classOption->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="flex-1">
                <x-input-label for="status" value="Status" />
                <x-select id="status" name="status" class="mt-1">
                    <option value="">Semua status</option>
                    @foreach ($recap['statuses'] as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($recap['status'] === $statusOption)>{{ $statusOption->label() }}</option>
                    @endforeach
                </x-select>
            </div>

            <x-button type="submit" variant="outline" icon="search">Tampilkan</x-button>
        </form>
    </x-card>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        <x-stat-card label="Total Siswa" :value="$recap['summary']['siswa']" icon="students" />
        <x-stat-card label="Hadir" :value="$recap['summary']['hadir']" icon="check" tone="success" />
        <x-stat-card label="Terlambat" :value="$recap['summary']['terlambat']" icon="clock" tone="warning" />
        <x-stat-card label="Izin" :value="$recap['summary']['izin']" icon="info" tone="info" />
        <x-stat-card label="Sakit" :value="$recap['summary']['sakit']" icon="report" tone="info" />
        <x-stat-card label="Alfa" :value="$recap['summary']['alfa']" icon="x-circle" tone="danger" />
    </div>

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-neutral-900">Rekap {{ $recap['monthLabel'] }}</h2>
            <x-button :href="route($exportRoute, $exportParams)" variant="outline" size="sm" icon="download">Unduh Excel</x-button>
        </div>

        @if ($recap['rows']->isEmpty())
            <div class="mt-4">
                <x-empty-state icon="report" title="Belum ada data rekap">
                    Tidak ada catatan kehadiran pada periode dan filter yang dipilih.
                </x-empty-state>
            </div>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                            <th class="pb-2 pr-4">No</th>
                            <th class="pb-2 pr-4">Nama</th>
                            <th class="pb-2 pr-4">Kelas</th>
                            <th class="pb-2 pr-4 text-right">Hadir</th>
                            <th class="pb-2 pr-4 text-right">Terlambat</th>
                            <th class="pb-2 pr-4 text-right">Izin</th>
                            <th class="pb-2 pr-4 text-right">Sakit</th>
                            <th class="pb-2 pr-4 text-right">Alfa</th>
                            <th class="pb-2 pr-4 text-right">Total</th>
                            <th class="pb-2 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($recap['rows'] as $index => $row)
                            <tr>
                                <td class="py-2.5 pr-4 text-neutral-500">{{ $index + 1 }}</td>
                                <td class="py-2.5 pr-4 font-medium text-neutral-900">{{ $row['student']->user?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-neutral-600">{{ $row['student']->schoolClass?->name ?? '—' }}</td>
                                <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['hadir'] }}</td>
                                <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['terlambat'] }}</td>
                                <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['izin'] }}</td>
                                <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['sakit'] }}</td>
                                <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['alfa'] }}</td>
                                <td class="py-2.5 pr-4 text-right font-medium text-neutral-900">{{ $row['total'] }}</td>
                                <td class="py-2.5 text-right">
                                    @if ($row['percentage'] === null)
                                        <span class="text-neutral-400">—</span>
                                    @else
                                        <x-badge :variant="$row['percentage'] >= 90 ? 'success' : ($row['percentage'] >= 75 ? 'warning' : 'danger')">
                                            {{ $row['percentage'] }}%
                                        </x-badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
