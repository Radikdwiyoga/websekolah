<x-app-layout>
    <x-slot name="title">Penilaian</x-slot>

    <div class="space-y-6">
        <x-page-header title="Penilaian" description="Periksa jawaban esai dan berkas, lalu beri nilai akhir." />

        <x-card>
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-neutral-900">Perlu Dinilai</h2>
                <x-badge variant="{{ $pending->isEmpty() ? 'neutral' : 'warning' }}">{{ $pending->count() }} tugas</x-badge>
            </div>

            @if ($pending->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="check" title="Semua sudah dinilai">
                        Tidak ada pengumpulan yang menunggu penilaian saat ini.
                    </x-empty-state>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full lg:min-w-[720px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-xs uppercase tracking-wide text-neutral-500">
                                <th class="py-2.5 pr-3 font-semibold">Siswa</th>
                                <th class="py-2.5 pr-3 font-semibold">Tugas</th>
                                <th class="py-2.5 pr-3 font-semibold">Kelas</th>
                                <th class="py-2.5 pr-3 font-semibold">Dikumpulkan</th>
                                <th class="py-2.5 pr-3 font-semibold">Terlambat</th>
                                <th class="py-2.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($pending as $submission)
                                <tr>
                                    <td class="py-3 pr-3">
                                        <p class="font-medium text-neutral-900">{{ $submission->student?->user?->name ?? 'Tanpa nama' }}</p>
                                        <p class="text-xs text-neutral-500">NIS {{ $submission->student?->nis ?? '—' }}</p>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <p class="font-medium text-neutral-800">{{ $submission->assignment?->title }}</p>
                                        <p class="text-xs text-neutral-500">{{ $submission->assignment?->subject?->name }}</p>
                                    </td>
                                    <td class="py-3 pr-3 text-neutral-600">{{ $submission->assignment?->schoolClass?->name }}</td>
                                    <td class="py-3 pr-3 text-neutral-600">{{ $submission->submitted_at?->translatedFormat('d M Y, H.i') ?? '—' }}</td>
                                    <td class="py-3 pr-3">
                                        @if ($submission->is_late)
                                            <x-badge variant="danger">Terlambat</x-badge>
                                        @else
                                            <span class="text-neutral-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <x-button :href="route('teacher.grades.edit', $submission)" size="sm" icon="grade">Nilai</x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <x-card>
            <h2 class="text-base font-semibold text-neutral-900">Baru Dinilai</h2>

            @if ($recent->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="grade" title="Belum ada nilai tersimpan">
                        Nilai yang Anda simpan akan tampil di sini.
                    </x-empty-state>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full md:min-w-[600px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-xs uppercase tracking-wide text-neutral-500">
                                <th class="py-2.5 pr-3 font-semibold">Siswa</th>
                                <th class="py-2.5 pr-3 font-semibold">Tugas</th>
                                <th class="py-2.5 pr-3 font-semibold">Nilai</th>
                                <th class="py-2.5 pr-3 font-semibold">Dinilai</th>
                                <th class="py-2.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($recent as $submission)
                                @php $score = $submission->teacher_score ?? $submission->total_score; @endphp
                                <tr>
                                    <td class="py-3 pr-3 font-medium text-neutral-900">{{ $submission->student?->user?->name ?? 'Tanpa nama' }}</td>
                                    <td class="py-3 pr-3 text-neutral-600">{{ $submission->assignment?->title }}</td>
                                    <td class="py-3 pr-3 font-semibold text-neutral-900">{{ $score === null ? '—' : (float) $score }}</td>
                                    <td class="py-3 pr-3 text-neutral-600">{{ $submission->graded_at?->translatedFormat('d M Y, H.i') ?? '—' }}</td>
                                    <td class="py-3 text-right">
                                        <x-button :href="route('teacher.grades.edit', $submission)" variant="outline" size="sm" icon="edit">Ubah Nilai</x-button>
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
