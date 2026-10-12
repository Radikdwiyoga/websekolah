<x-app-layout>
    <x-slot name="title">Nilai Anak</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Nilai Anak"
            description="Rekap nilai tugas anak Anda beserta rata-ratanya."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.parent'], ['label' => 'Nilai Anak']]"
        >
            <x-slot name="actions">
                <x-child-selector :children="$children" :selected-child-id="$selectedChildId" />
            </x-slot>
        </x-page-header>

        @if (! $selectedChild)
            <x-empty-state icon="guardian" title="Belum ada anak terhubung">
                Akun Anda belum terhubung ke data siswa. Hubungi administrator sekolah.
            </x-empty-state>
        @elseif ($grades->isEmpty())
            <x-empty-state icon="grade" title="Belum ada nilai">
                Nilai tugas anak Anda akan tampil di sini setelah guru melakukan penilaian.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card label="Rata-rata Nilai" :value="$average ?? '—'" icon="grade" tone="primary" />
                <x-stat-card label="Nilai Tertinggi" :value="rtrim(rtrim(number_format((float) $grades->max('score'), 2, ',', '.'), '0'), ',')" icon="report" tone="success" />
                <x-stat-card label="Jumlah Penilaian" :value="$grades->count()" icon="task" tone="info" />
            </div>

            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Daftar Nilai</h2>
                    <x-icon name="grade" :size="5" class="text-neutral-400" />
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs text-neutral-500">
                                <th class="pb-2 pr-4 font-medium">Mata Pelajaran</th>
                                <th class="pb-2 pr-4 font-medium">Tugas</th>
                                <th class="pb-2 pr-4 font-medium">Nilai</th>
                                <th class="pb-2 font-medium">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($grades as $grade)
                                <tr>
                                    <td class="py-2.5 pr-4 font-medium text-neutral-900">{{ $grade->subject?->name ?? 'Umum' }}</td>
                                    <td class="py-2.5 pr-4 text-neutral-700">{{ $grade->assignment?->title ?? 'Penilaian Umum' }}</td>
                                    <td class="py-2.5 pr-4">
                                        <x-badge variant="primary">{{ rtrim(rtrim(number_format((float) $grade->score, 2, ',', '.'), '0'), ',') }}</x-badge>
                                    </td>
                                    <td class="py-2.5 text-neutral-500">{{ $grade->graded_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</x-app-layout>
