<x-app-layout>
    <x-slot name="title">Absensi Siswa</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Absensi Siswa"
            description="Pantau sesi absensi harian dan status kehadiran siswa di setiap kelas."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Absensi']]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.attendance.recap')" variant="outline" icon="report">Lihat Rekap Absensi</x-button>
            </x-slot>
        </x-page-header>

        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-neutral-900">Jadwal Absensi Hari Ini</h2>
                    <p class="text-xs text-neutral-500">Tahun Ajaran: {{ $activeYear?->name ?? '—' }} &middot; Tanggal: {{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-neutral-600">
                    <span>Buka: <strong class="text-neutral-900">{{ $openTime }}</strong></span>
                    <span>Batas Terlambat: <strong class="text-warning-700">{{ $lateAfter }}</strong></span>
                    <span>Tutup: <strong class="text-neutral-900">{{ $closeTime }}</strong></span>
                </div>
            </div>
        </x-card>

        <x-card>
            @if ($attendanceToday->isEmpty())
                <x-empty-state icon="attendance" title="Belum ada kelas aktif">
                    Belum ada kelas terdaftar pada tahun ajaran aktif.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Kelas</th>
                                <th class="pb-2 pr-4">Status Sesi</th>
                                <th class="pb-2 pr-4 text-right">Hadir</th>
                                <th class="pb-2 pr-4 text-right">Terlambat</th>
                                <th class="pb-2 pr-4 text-right">Izin / Sakit / Alfa</th>
                                <th class="pb-2 pr-4 text-right">Belum Absen</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($attendanceToday as $row)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-neutral-900">
                                        {{ $row['schoolClass']->name }}
                                        <span class="block text-xs font-normal text-neutral-400">Total: {{ $row['studentCount'] }} siswa</span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if ($row['session']?->isOpen())
                                            <x-badge variant="success" dot>Berjalan</x-badge>
                                        @elseif ($row['session']?->isClosed())
                                            <x-badge variant="neutral">Ditutup</x-badge>
                                        @else
                                            <x-badge variant="warning">Belum dibuka</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-right font-medium text-success-700">
                                        {{ $row['hadir'] }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-medium text-warning-700">
                                        {{ $row['terlambat'] }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-medium text-danger-700">
                                        {{ $row['tidakHadir'] }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-medium text-neutral-400">
                                        {{ $row['belum'] }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <x-button :href="route('teacher.attendance.show', $row['schoolClass'])" size="sm" variant="outline">
                                            Detail Sesi
                                        </x-button>
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

