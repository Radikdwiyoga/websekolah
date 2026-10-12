<x-app-layout>
    <x-slot name="title">Dashboard Admin</x-slot>

    <div class="space-y-4">
        <x-page-header :title="$greeting.', '.$user->name" description="Ringkasan operasional sekolah hari ini." />

        {{-- KPI --}}
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">
            <x-stat-card label="Total Siswa" :value="$kpi['siswa']" icon="students" tone="primary" />
            <x-stat-card label="Total Guru" :value="$kpi['guru']" icon="teacher" tone="info" />
            <x-stat-card label="Kehadiran Hari Ini" :value="$kpi['kehadiranHariIni']" icon="attendance" tone="success" />
            <x-stat-card label="Notifikasi WA" :value="$kpi['notifWa']" icon="bell" tone="warning" />
            <x-stat-card label="Tugas Aktif" :value="$kpi['tugasAktif']" icon="task" tone="danger" />
        </div>

        {{-- Kehadiran hari ini + antrean penilaian --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <x-card class="h-full lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Absensi Hari Ini</h2>
                    <x-icon name="attendance" :size="5" class="text-neutral-400" />
                </div>

                @if ($attendanceToday->isEmpty())
                    <div class="mt-4">
                        <x-empty-state icon="school" title="Belum ada kelas">
                            Belum ada kelas pada tahun ajaran aktif.
                        </x-empty-state>
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 text-left text-xs font-medium text-neutral-500">
                                    <th class="pb-2 pr-4">Kelas</th>
                                    <th class="pb-2 pr-4">Sesi</th>
                                    <th class="pb-2 pr-4 text-right">Hadir</th>
                                    <th class="pb-2 pr-4 text-right">Terlambat</th>
                                    <th class="pb-2 pr-4 text-right">Tidak Hadir</th>
                                    <th class="pb-2 text-right">Belum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach ($attendanceToday as $row)
                                    <tr>
                                        <td class="py-2.5 pr-4 font-medium text-neutral-900">{{ $row['schoolClass']->name }}</td>
                                        <td class="py-2.5 pr-4">
                                            @if ($row['session'])
                                                <x-badge :variant="$row['session']->isOpen() ? 'success' : 'neutral'">
                                                    {{ $row['session']->status->label() }}
                                                </x-badge>
                                            @else
                                                <span class="text-xs text-neutral-400">Belum dibuka</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['hadir'] }} / {{ $row['studentCount'] }}</td>
                                        <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['terlambat'] }}</td>
                                        <td class="py-2.5 pr-4 text-right text-neutral-700">{{ $row['tidakHadir'] }}</td>
                                        <td class="py-2.5 text-right text-neutral-700">{{ $row['belum'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

            <x-card class="h-full">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Tugas Perlu Dinilai</h2>
                    <x-icon name="report" :size="5" class="text-neutral-400" />
                </div>

                <p class="mt-2 text-sm text-neutral-500">{{ $gradingQueue['count'] }} pengumpulan menunggu penilaian.</p>

                @if ($gradingQueue['assignments']->isEmpty())
                    <p class="mt-4 text-sm text-neutral-400">Semua pengumpulan sudah dinilai.</p>
                @else
                    <ul class="mt-2 divide-y divide-neutral-100">
                        @foreach ($gradingQueue['assignments'] as $assignment)
                            <li class="py-3">
                                <p class="text-xs font-medium text-neutral-500">{{ $assignment->subject?->name ?? 'Umum' }} &middot; Kelas {{ $assignment->schoolClass?->name ?? '—' }}</p>
                                <p class="mt-0.5 truncate text-sm font-semibold text-neutral-900">{{ $assignment->title }}</p>
                                <p class="mt-0.5 text-xs text-neutral-500">{{ $assignment->pending_count }} pengumpulan &middot; {{ $assignment->teacher?->user?->name ?? '—' }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        {{-- Notifikasi WhatsApp + aktivitas sistem --}}
        <div class="grid gap-6 lg:grid-cols-2">
            <x-card class="h-full">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Notifikasi WhatsApp Terbaru</h2>
                    <x-icon name="send" :size="5" class="text-neutral-400" />
                </div>

                @if ($notifications->isEmpty())
                    <div class="mt-4">
                        <x-empty-state icon="send" title="Belum ada notifikasi">
                            Log notifikasi WhatsApp akan tampil di sini.
                        </x-empty-state>
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-neutral-100">
                        @foreach ($notifications as $log)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-neutral-900">{{ $log->recipient_phone }}</p>
                                    <p class="mt-0.5 truncate text-xs text-neutral-500">{{ $log->message }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-400">{{ $log->created_at?->translatedFormat('d M Y, H.i') }}</p>
                                </div>
                                <x-badge :variant="$log->status->variant()" class="shrink-0">{{ $log->status->label() }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>

            <x-card class="h-full">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Aktivitas Sistem Terbaru</h2>
                    <x-icon name="log" :size="5" class="text-neutral-400" />
                </div>

                @if ($activity->isEmpty())
                    <div class="mt-4">
                        <x-empty-state icon="log" title="Belum ada aktivitas">
                            Catatan aktivitas sistem akan tampil di sini.
                        </x-empty-state>
                    </div>
                @else
                    <ul class="mt-4 divide-y divide-neutral-100">
                        @foreach ($activity as $log)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-neutral-900">{{ $log->description }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-500">{{ $log->module }} &middot; {{ $log->user?->name ?? 'Sistem' }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-400">{{ $log->created_at?->translatedFormat('d M Y, H.i') }}</p>
                                </div>
                                <x-badge :variant="$log->status->variant()" class="shrink-0">{{ $log->status->label() }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        {{-- Aksi cepat + kesehatan sistem --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="text-base font-semibold text-neutral-900">Aksi Cepat</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($quickActions as $action)
                        @php($available = $action['route'] !== null && \Illuminate\Support\Facades\Route::has($action['route']))
                        <x-card class="flex flex-col">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-700">
                                <x-icon :name="$action['icon']" :size="5" />
                            </span>
                            <h3 class="mt-3 text-sm font-semibold text-neutral-900">{{ $action['title'] }}</h3>
                            <p class="mt-1 text-xs text-neutral-500">{{ $action['description'] }}</p>
                            @if ($available)
                                <a href="{{ route($action['route']) }}" class="mt-3 text-xs font-medium text-primary-700 hover:text-primary-800">Buka &rarr;</a>
                            @else
                                <span class="mt-3 text-xs font-medium text-neutral-400">Fitur akan segera tersedia</span>
                            @endif
                        </x-card>
                    @endforeach
                </div>
            </div>

            <x-card class="h-full">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Kesehatan Sistem</h2>
                    <x-icon name="wrench" :size="5" class="text-neutral-400" />
                </div>

                <ul class="mt-4 divide-y divide-neutral-100">
                    @foreach ($health as $row)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm text-neutral-600">{{ $row['label'] }}</span>
                            <x-badge :variant="$row['variant']" dot="true">{{ $row['value'] }}</x-badge>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        </div>
    </div>
</x-app-layout>
