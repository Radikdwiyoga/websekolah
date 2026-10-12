<x-app-layout>
    <x-slot name="title">Kelas Saya</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Kelas Saya"
            description="Daftar kelas dan mata pelajaran yang Anda ampu pada tahun ajaran ini."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.teacher'], ['label' => 'Kelas Saya']]"
        />

        <x-card>
            @if ($assignments->isEmpty())
                <x-empty-state icon="school" title="Belum ada penugasan kelas">
                    Anda belum ditugaskan mengajar kelas pada tahun ajaran aktif.
                </x-empty-state>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($assignments as $item)
                        <div class="flex flex-col justify-between rounded-lg border border-neutral-200 bg-neutral-50 p-4 transition hover:border-primary-300 hover:bg-white">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="rounded bg-primary-100 px-2 py-0.5 text-xs font-bold text-primary-800">
                                        {{ $item->schoolClass?->name }}
                                    </span>
                                    <span class="text-xs text-neutral-400">
                                        {{ $item->schoolClass?->students->count() ?? 0 }} Siswa
                                    </span>
                                </div>
                                <h3 class="mt-3 text-base font-semibold text-neutral-900">{{ $item->subject?->name }}</h3>
                                <p class="text-xs text-neutral-500">Tahun Ajaran: {{ $item->schoolYear?->name }}</p>
                            </div>

                            <div class="mt-5 flex items-center gap-2 border-t border-neutral-200/60 pt-3">
                                <x-button :href="route('teacher.attendance.show', $item->schoolClass)" size="sm" variant="outline">
                                    Absensi
                                </x-button>
                                <x-button :href="route('teacher.assignments.index', ['class_id' => $item->schoolClass?->id])" size="sm" variant="ghost">
                                    Tugas
                                </x-button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>

