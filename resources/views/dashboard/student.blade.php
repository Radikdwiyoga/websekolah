<x-app-layout>
    <x-slot name="title">Dashboard Siswa</x-slot>

    <div class="space-y-4">
        <x-page-header :title="$greeting.', '.$user->name" description="Tetap semangat belajar hari ini!" />

        @if (! $student)
            <x-empty-state icon="profile" title="Profil siswa belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card label="Absen Hari Ini"
                    :value="$todayRecord?->checked_in_at?->format('H.i') ?? '—'"
                    icon="attendance" tone="success" />
                <x-stat-card label="Tugas Aktif" :value="$tugasAktif" icon="task" tone="info" />
                <x-stat-card label="Rata-rata Nilai" :value="$nilaiRata ?? '—'" icon="grade" tone="warning" />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Absensi hari ini --}}
                <x-card class="h-full">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Absensi Hari Ini</h2>
                        <x-icon name="attendance" :size="5" class="text-neutral-400" />
                    </div>

                    @if ($todayRecord)
                        <div class="mt-6 flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $todayRecord->status->isPresent() ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700' }}">
                                <x-icon :name="$todayRecord->status->isPresent() ? 'check' : 'warning'" :size="7" />
                            </span>
                            <div>
                                <p class="text-2xl font-bold text-neutral-950">{{ $todayRecord->status->label() }}</p>
                                <p class="mt-1 text-sm text-neutral-500">
                                    Waktu: {{ $todayRecord->checked_in_at?->format('H.i') ?? '—' }}
                                </p>
                            </div>
                        </div>
                        <p class="mt-5 text-xs text-neutral-500">
                            Kelas {{ $todayRecord->attendanceSession?->schoolClass?->name ?? '—' }}
                        </p>
                    @else
                        <div class="mt-6">
                            <x-empty-state icon="calendar" title="Belum absen hari ini">
                                Sesi absensi mungkin belum dibuka oleh guru. Cek kembali secara berkala.
                            </x-empty-state>
                        </div>
                    @endif
                </x-card>

                {{-- Tugas terbaru --}}
                <x-card class="h-full lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Tugas Terbaru</h2>
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
                                        Belum ada tugas untuk kelas Anda saat ini.
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