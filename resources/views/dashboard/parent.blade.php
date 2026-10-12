<x-app-layout>
    <x-slot name="title">Dashboard Orang Tua</x-slot>

    <div class="space-y-4">
        <x-page-header :title="$greeting.', '.$user->name" description="Berikut informasi perkembangan belajar anak.">
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
                <x-stat-card label="Rata-rata Nilai" :value="$kpi['nilaiRata'] ?? '—'" icon="grade" tone="primary" />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Riwayat absensi --}}
                <x-card class="h-full lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Riwayat Absensi</h2>
                        <x-icon name="attendance" :size="5" class="text-neutral-400" />
                    </div>

                    @if ($riwayat->isEmpty())
                        <div class="mt-4">
                            <x-empty-state icon="calendar" title="Belum ada riwayat absensi">
                                Riwayat kehadiran anak akan tampil di sini.
                            </x-empty-state>
                        </div>
                    @else
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-neutral-200 text-left text-xs text-neutral-500">
                                        <th class="pb-2 pr-4 font-medium">Tanggal</th>
                                        <th class="pb-2 pr-4 font-medium">Status</th>
                                        <th class="pb-2 font-medium">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @foreach ($riwayat as $record)
                                        <tr>
                                            <td class="py-2.5 pr-4 text-neutral-700">
                                                {{ $record->attendanceSession?->date?->translatedFormat('d M Y') ?? '—' }}
                                            </td>
                                            <td class="py-2.5 pr-4">
                                                <x-badge :variant="$record->status->variant()">
                                                    {{ $record->status->label() }}
                                                </x-badge>
                                            </td>
                                            <td class="py-2.5 text-neutral-700">
                                                {{ $record->checked_in_at?->format('H.i') ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>

                {{-- Tugas anak --}}
                <x-card class="h-full">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Tugas Anak</h2>
                        <x-icon name="task" :size="5" class="text-neutral-400" />
                    </div>

                    <ul class="mt-4 divide-y divide-neutral-100">
                        @forelse ($tugas as $item)
                            <li class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-neutral-500">{{ $item['assignment']->subject?->name ?? 'Umum' }}</p>
                                    <p class="mt-0.5 truncate text-sm font-semibold text-neutral-900">{{ $item['assignment']->title }}</p>
                                    @if ($item['assignment']->deadline)
                                        <p class="mt-0.5 text-xs text-neutral-500">
                                            Batas: {{ $item['assignment']->deadline->translatedFormat('d M Y, H.i') }}
                                        </p>
                                    @endif
                                </div>
                                <x-badge :variant="$item['status']['variant']" class="shrink-0">{{ $item['status']['label'] }}</x-badge>
                            </li>
                        @empty
                            <li>
                                <div class="pt-2">
                                    <x-empty-state icon="task" title="Belum ada tugas">
                                        Belum ada tugas yang tercatat untuk anak Anda.
                                    </x-empty-state>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </x-card>
            </div>
        @endif
    </div>
</x-app-layout>