<x-app-layout>
    <x-slot name="title">Kehadiran Anak</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Kehadiran Anak"
            description="Riwayat kehadiran anak Anda pada sesi absensi sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.parent'], ['label' => 'Kehadiran Anak']]"
        >
            <x-slot name="actions">
                <x-child-selector :children="$children" :selected-child-id="$selectedChildId" />
            </x-slot>
        </x-page-header>

        @if (! $selectedChild)
            <x-empty-state icon="guardian" title="Belum ada anak terhubung">
                Akun Anda belum terhubung ke data siswa. Hubungi administrator sekolah.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-stat-card label="Hadir" :value="$kpi['hadir'].' hari'" icon="attendance" tone="success" />
                <x-stat-card label="Terlambat" :value="$kpi['terlambat'].' hari'" icon="warning" tone="warning" />
                <x-stat-card label="Tidak Hadir" :value="$kpi['tidakHadir'].' hari'" icon="x-circle" tone="danger" />
                <x-stat-card label="Total Sesi" :value="$records->count().' sesi'" icon="report" tone="info" />
            </div>

            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Riwayat Absensi</h2>
                    <x-icon name="attendance" :size="5" class="text-neutral-400" />
                </div>

                @if ($records->isEmpty())
                    <div class="mt-4">
                        <x-empty-state icon="calendar" title="Belum ada riwayat absensi">
                            Riwayat kehadiran anak akan tampil di sini setelah sesi absensi dibuka.
                        </x-empty-state>
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 text-left text-xs text-neutral-500">
                                    <th class="pb-2 pr-4 font-medium">Tanggal</th>
                                    <th class="pb-2 pr-4 font-medium">Kelas</th>
                                    <th class="pb-2 pr-4 font-medium">Status</th>
                                    <th class="pb-2 pr-4 font-medium">Waktu</th>
                                    <th class="pb-2 font-medium">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach ($records as $record)
                                    <tr>
                                        <td class="py-2.5 pr-4 text-neutral-700">
                                            {{ $record->attendanceSession?->date?->translatedFormat('d M Y') ?? '—' }}
                                        </td>
                                        <td class="py-2.5 pr-4 text-neutral-700">
                                            {{ $record->attendanceSession?->schoolClass?->name ?? '—' }}
                                        </td>
                                        <td class="py-2.5 pr-4">
                                            <x-badge :variant="$record->status->variant()">{{ $record->status->label() }}</x-badge>
                                        </td>
                                        <td class="py-2.5 pr-4 text-neutral-700">
                                            {{ $record->checked_in_at?->format('H.i') ?? '—' }}
                                        </td>
                                        <td class="py-2.5 text-neutral-500">{{ $record->note ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @endif
    </div>
</x-app-layout>
