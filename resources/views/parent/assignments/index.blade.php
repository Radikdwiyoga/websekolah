<x-app-layout>
    <x-slot name="title">Tugas Anak</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tugas Anak"
            description="Pantau status pengerjaan tugas anak Anda."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.parent'], ['label' => 'Tugas Anak']]"
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
            @php
                $counts = [
                    'belum' => $tasks->where('status.key', 'belum')->count(),
                    'dikerjakan' => $tasks->where('status.key', 'dikerjakan')->count(),
                    'selesai' => $tasks->where('status.key', 'selesai')->count(),
                    'terlambat' => $tasks->where('status.key', 'terlambat')->count(),
                ];
            @endphp

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-stat-card label="Belum Dikerjakan" :value="$counts['belum']" icon="task" tone="primary" />
                <x-stat-card label="Dalam Pengerjaan" :value="$counts['dikerjakan']" icon="edit" tone="info" />
                <x-stat-card label="Selesai" :value="$counts['selesai']" icon="check" tone="success" />
                <x-stat-card label="Terlambat" :value="$counts['terlambat']" icon="warning" tone="danger" />
            </div>

            @if ($tasks->isEmpty())
                <x-empty-state icon="task" title="Belum ada tugas">
                    Belum ada tugas yang diberikan untuk kelas anak Anda saat ini.
                </x-empty-state>
            @else
                <div class="space-y-3">
                    @foreach ($tasks as $item)
                        <x-card>
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-badge variant="neutral">{{ $item['assignment']->subject?->name ?? 'Umum' }}</x-badge>
                                        <x-badge :variant="$item['status']['variant']" dot>{{ $item['status']['label'] }}</x-badge>
                                        @if ($item['score'] !== null)
                                            <x-badge variant="primary">Nilai {{ rtrim(rtrim(number_format((float) $item['score'], 2, ',', '.'), '0'), ',') }}</x-badge>
                                        @endif
                                    </div>

                                    <h2 class="mt-2 text-base font-semibold text-neutral-900">{{ $item['assignment']->title }}</h2>

                                    <p class="mt-1 text-sm text-neutral-500">
                                        {{ $item['assignment']->type->label() }}
                                        @if ($item['assignment']->deadline)
                                            &middot; Tenggat {{ $item['assignment']->deadline->translatedFormat('d M Y, H.i') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
