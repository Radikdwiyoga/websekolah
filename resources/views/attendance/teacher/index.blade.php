<x-app-layout>
    <x-slot name="title">Input Absensi</x-slot>

    <div class="space-y-6">
        <x-page-header title="Input Absensi" description="Buka sesi absensi untuk kelas Anda dan catat kehadiran siswa." />

        @if (! $teacher)
            <x-empty-state icon="profile" title="Profil guru belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @elseif ($classes->isEmpty())
            <x-empty-state icon="school" title="Belum ada kelas yang diampu">
                Anda belum diampu mengajar kelas apa pun pada tahun ajaran ini.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($classes as $item)
                    @php($class = $item['class'])

                    <x-card class="flex h-full flex-col">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-neutral-900">{{ $class->name }}</p>
                                <p class="mt-0.5 text-xs text-neutral-500">Kelas {{ $class->level }} &middot; {{ $item['studentCount'] }} siswa</p>
                            </div>

                            @if ($item['session'])
                                <x-badge :variant="$item['session']->isOpen() ? 'success' : 'neutral'" dot class="shrink-0">
                                    {{ $item['session']->status->label() }}
                                </x-badge>
                            @else
                                <x-badge variant="neutral" class="shrink-0">Belum dibuka</x-badge>
                            @endif
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @forelse ($item['subjects'] as $subject)
                                <x-badge variant="primary">{{ $subject }}</x-badge>
                            @empty
                                <x-badge variant="neutral">Wali kelas</x-badge>
                            @endforelse
                        </div>

                        @if ($item['session'])
                            <p class="mt-3 text-xs text-neutral-500">
                                Jendela sesi: {{ $item['session']->open_at }} – {{ $item['session']->close_at }}
                            </p>
                        @endif

                        <div class="mt-4 flex-1"></div>

                        <div class="mt-4 border-t border-neutral-100 pt-4">
                            <x-button
                                :variant="$item['session'] ? 'secondary' : 'primary'"
                                size="sm"
                                icon="attendance"
                                :href="route('teacher.attendance.show', $class)">
                                {{ $item['session'] ? 'Lihat & Input Kehadiran' : 'Input Absensi' }}
                            </x-button>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>