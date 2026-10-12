<x-app-layout>
    <x-slot name="title">Nilai</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Nilai"
            description="Rekap nilai tugas Anda beserta rata-rata per mata pelajaran."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.student'], ['label' => 'Nilai']]"
        />

        @if (! $student)
            <x-empty-state icon="profile" title="Profil siswa belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @elseif ($grades->isEmpty())
            <x-empty-state icon="grade" title="Belum ada nilai">
                Nilai tugas Anda akan tampil di sini setelah guru melakukan penilaian.
            </x-empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card label="Rata-rata Nilai" :value="$average ?? '—'" icon="grade" />
                <x-stat-card label="Nilai Tertinggi" :value="rtrim(rtrim(number_format((float) $highest, 2, ',', '.'), '0'), ',')" icon="report" tone="success" />
                <x-stat-card label="Jumlah Penilaian" :value="$grades->count()" icon="task" tone="info" />
            </div>

            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Rata-rata per Mata Pelajaran</h2>
                    <x-icon name="book" :size="5" class="text-neutral-400" />
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($bySubject as $subject)
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-neutral-200 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-neutral-900">{{ $subject['subject']?->name ?? 'Umum' }}</p>
                                <p class="text-xs text-neutral-500">{{ $subject['count'] }} penilaian</p>
                            </div>
                            <x-badge variant="primary">{{ $subject['average'] }}</x-badge>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Daftar Nilai</h2>
                    <x-icon name="grade" :size="5" class="text-neutral-400" />
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Mata Pelajaran</th>
                                <th class="pb-2 pr-4">Tugas</th>
                                <th class="pb-2 pr-4">Nilai</th>
                                <th class="pb-2">Tanggal</th>
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
