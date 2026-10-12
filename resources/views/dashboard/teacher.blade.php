<x-app-layout>
    <x-slot name="title">Dashboard Guru</x-slot>

    <div class="space-y-4">
        <x-page-header :title="$greeting.', '.$user->name" description="Kelola kelas, absensi, dan tugas Anda di sini." />

        @if (! $teacher)
            <x-empty-state icon="profile" title="Profil guru belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-stat-card label="Kelas Diampu" :value="$kpi['kelas']" icon="school" tone="primary" />
                <x-stat-card label="Total Siswa" :value="$kpi['siswa']" icon="students" tone="info" />
                <x-stat-card label="Tugas Aktif" :value="$kpi['tugasAktif']" icon="task" tone="warning" />
                <x-stat-card label="Perlu Dinilai" :value="$kpi['perluDinilai']" icon="report" tone="danger" />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Kelas yang diampu --}}
                <x-card class="h-full lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Kelas yang Diampu</h2>
                        <x-icon name="school" :size="5" class="text-neutral-400" />
                    </div>

                    @if ($kelasDiampu->isEmpty())
                        <div class="mt-4">
                            <x-empty-state icon="school" title="Belum ada penugasan mengajar">
                                Anda belum diampu mengajar kelas apa pun pada tahun ajaran ini.
                            </x-empty-state>
                        </div>
                    @else
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($kelasDiampu as $kelas)
                                <div class="rounded-lg border border-neutral-200 p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-neutral-900">{{ $kelas['schoolClass']->name }}</p>
                                            <p class="mt-0.5 text-xs text-neutral-500">{{ $kelas['schoolClass']->level }}</p>
                                        </div>
                                        <x-badge variant="neutral">{{ $kelas['studentCount'] }} siswa</x-badge>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                        @foreach ($kelas['subjects'] as $subject)
                                            <x-badge variant="primary">{{ $subject }}</x-badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>

                {{-- Sesi absensi hari ini --}}
                <x-card class="h-full">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Sesi Absensi Hari Ini</h2>
                        <x-icon name="attendance" :size="5" class="text-neutral-400" />
                    </div>

                    @if ($sessionsToday->isEmpty())
                        <div class="mt-4">
                            <x-empty-state icon="calendar" title="Belum ada sesi hari ini">
                                Belum ada sesi absensi dibuka untuk kelas Anda hari ini.
                            </x-empty-state>
                        </div>
                    @else
                        <ul class="mt-4 divide-y divide-neutral-100">
                            @foreach ($sessionsToday as $session)
                                <li class="flex items-center justify-between gap-2 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-neutral-900">{{ $session->schoolClass?->name ?? '—' }}</p>
                                        <p class="mt-0.5 text-xs text-neutral-500">{{ $session->open_at }} – {{ $session->close_at }}</p>
                                    </div>
                                    <x-badge :variant="$session->isOpen() ? 'success' : 'neutral'" dot="true" class="shrink-0">
                                        {{ $session->status->label() }}
                                    </x-badge>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>

            {{-- Tugas perlu dinilai --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Tugas Perlu Dinilai</h2>
                    <x-icon name="report" :size="5" class="text-neutral-400" />
                </div>

                @if ($ungraded->isEmpty())
                    <div class="mt-4">
                        <x-empty-state icon="report" title="Tidak ada pengumpulan tertunda">
                            Semua pengumpulan tugas sudah dinilai. Mantap!
                        </x-empty-state>
                    </div>
                @else
                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($ungraded as $assignment)
                            <div class="rounded-lg border border-neutral-200 p-4">
                                <p class="text-xs font-medium text-neutral-500">{{ $assignment->subject?->name ?? 'Umum' }}</p>
                                <p class="mt-1 truncate text-sm font-semibold text-neutral-900">{{ $assignment->title }}</p>
                                <p class="mt-0.5 text-xs text-neutral-500">Kelas {{ $assignment->schoolClass?->name ?? '—' }}</p>
                                <p class="mt-3 text-xs font-semibold text-neutral-700">
                                    {{ $assignment->submitted_count }} pengumpulan belum dinilai
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-card>
        @endif
    </div>
</x-app-layout>