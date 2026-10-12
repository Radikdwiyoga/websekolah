<x-app-layout>
    <x-slot name="title">{{ $assignment->title }}</x-slot>

    @php
        $type = $assignment->type;
        $oldQuestions = old('questions');
        $builderRows = is_array($oldQuestions) && count($oldQuestions) > 0 ? array_values($oldQuestions) : [null];
    @endphp

    <div class="space-y-6">
        <x-page-header
            :title="$assignment->title"
            :description="$assignment->subject?->name.' • Kelas '.$assignment->schoolClass?->name"
            :breadcrumb="[
                ['label' => 'Tugas', 'route' => 'teacher.assignments.index'],
                ['label' => $assignment->title],
            ]">
            <x-slot name="actions">
                @if ($assignment->isDraft())
                    <x-button :href="route('teacher.assignments.edit', $assignment)" variant="outline" icon="edit">Ubah</x-button>
                @endif

                @if ($assignment->isDraft())
                    <form method="POST" action="{{ route('teacher.assignments.publish', $assignment) }}">
                        @csrf
                        <x-button type="submit" icon="check">Publikasikan</x-button>
                    </form>
                @elseif ($assignment->isPublished())
                    <form method="POST" action="{{ route('teacher.assignments.close', $assignment) }}" onsubmit="return confirm('Tutup tugas ini? Siswa tidak dapat mengirim jawaban lagi.')">
                        @csrf
                        <x-button type="submit" variant="danger" icon="x-circle">Tutup Tugas</x-button>
                    </form>
                @endif
            </x-slot>
        </x-page-header>

        {{-- Ringkasan --}}
        <x-card>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Status</p>
                    <div class="mt-1"><x-badge :variant="$assignment->status->variant()" dot>{{ $assignment->status->label() }}</x-badge></div>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Jenis Soal</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">{{ $type->label() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Tenggat</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">
                        {{ $assignment->deadline?->translatedFormat('d M Y, H.i') ?? 'Tanpa tenggat' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Bobot Nilai</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">{{ (float) $assignment->grade_weight }}</p>
                </div>
            </div>

            @if ($assignment->description)
                <p class="mt-5 border-t border-neutral-100 pt-4 text-sm text-neutral-600">{{ $assignment->description }}</p>
            @endif

            <p class="mt-4 text-xs text-neutral-500">
                Potongan terlambat {{ $assignment->late_deduction_percent }}% &middot;
                {{ $assignment->allow_resubmit ? 'Kirim ulang diizinkan sampai tenggat' : 'Kirim ulang tidak diizinkan' }}
            </p>
        </x-card>

        {{-- Statistik pengumpulan --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card label="Siswa" :value="$stats['students']" icon="students" tone="primary" />
            <x-stat-card label="Dikumpulkan" :value="$stats['submitted']" icon="upload" tone="info" />
            <x-stat-card label="Perlu Dinilai" :value="$stats['pending']" icon="clock" tone="warning" />
            <x-stat-card label="Sudah Dinilai" :value="$stats['graded']" icon="grade" tone="success" />
        </div>

        {{-- Soal --}}
        <x-card>
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-neutral-900">Daftar Soal</h2>
                <span class="text-sm text-neutral-500">{{ $assignment->questions->count() }} soal</span>
            </div>

            @if ($assignment->questions->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="task" :title="$type === \App\Enums\AssignmentType::FileUpload ? 'Tugas unggah berkas' : 'Belum ada soal'">
                        {{ $type === \App\Enums\AssignmentType::FileUpload
                            ? 'Siswa akan mengunggah berkas jawaban; tidak diperlukan daftar soal.'
                            : 'Tambahkan soal pada formulir di bawah.' }}
                    </x-empty-state>
                </div>
            @else
                <ol class="mt-4 space-y-3">
                    @foreach ($assignment->questions as $index => $question)
                        <li class="rounded-lg border border-neutral-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-neutral-900">
                                        <span class="text-neutral-400">{{ $index + 1 }}.</span>
                                        {{ $question->question_text }}
                                    </p>

                                    @if ($question->type === \App\Enums\AssignmentType::MultipleChoice)
                                        <ul class="mt-2 grid gap-1 sm:grid-cols-2">
                                            @foreach ($question->options ?? [] as $key => $option)
                                                <li class="flex items-center gap-2 text-sm {{ in_array($key, $question->correct_answers ?? [], true) ? 'font-semibold text-success-700' : 'text-neutral-600' }}">
                                                    <span class="font-medium">{{ $key }}.</span> {{ $option }}
                                                    @if (in_array($key, $question->correct_answers ?? [], true))
                                                        <x-icon name="check" :size="4" />
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif ($question->type === \App\Enums\AssignmentType::ShortAnswer)
                                        <p class="mt-2 text-xs text-neutral-500">Jawaban diterima: {{ implode(', ', $question->correct_answers ?? []) }}</p>
                                    @endif

                                    <p class="mt-2 text-xs text-neutral-400">Poin {{ (float) $question->points }}</p>
                                </div>

                                @if ($assignment->isDraft())
                                    <form method="POST" action="{{ route('teacher.assignments.questions.destroy', [$assignment, $question]) }}" onsubmit="return confirm('Hapus soal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 text-sm font-medium text-danger-700 transition hover:text-danger-700/80">
                                            <x-icon name="trash" :size="4" />
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($assignment->isDraft() && $type !== \App\Enums\AssignmentType::FileUpload)
                <form method="POST" action="{{ route('teacher.assignments.questions.store', $assignment) }}" class="mt-5 border-t border-neutral-100 pt-5"
                    data-repeater
                    data-repeater-next="{{ count($builderRows) }}"
                    data-submit-loading>
                    @csrf

                    <div data-repeater-rows class="space-y-4">
                        @foreach ($builderRows as $rowIndex => $builderRow)
                            @include('assignments.teacher._question-row', ['index' => $rowIndex, 'type' => $type, 'row' => $builderRow])
                        @endforeach
                    </div>

                    <template data-repeater-template>
                        @include('assignments.teacher._question-row', ['index' => '__INDEX__', 'type' => $type, 'row' => null])
                    </template>

                    <div class="mt-4 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                        <button type="button" data-repeater-add class="inline-flex items-center gap-1.5 rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-neutral-50">
                            <x-icon name="plus" :size="4" />
                            Tambah Soal
                        </button>

                        <x-button type="submit" icon="check">Simpan Soal</x-button>
                    </div>

                    <x-input-error :messages="$errors->get('questions')" class="mt-3" />
                </form>
            @endif
        </x-card>

        {{-- Pengumpulan siswa --}}
        <x-card>
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-neutral-900">Pengumpulan Siswa</h2>
                <span class="text-sm text-neutral-500">{{ $stats['students'] - $stats['submitted'] }} belum mengumpulkan</span>
            </div>

            @if ($submissions->isEmpty())
                <div class="mt-4">
                    <x-empty-state icon="upload" title="Belum ada pengumpulan">
                        Pengumpulan siswa akan muncul di sini setelah mereka mengirim jawaban.
                    </x-empty-state>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full lg:min-w-[720px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-xs uppercase tracking-wide text-neutral-500">
                                <th class="py-2.5 pr-3 font-semibold">Nama</th>
                                <th class="py-2.5 pr-3 font-semibold">Status</th>
                                <th class="py-2.5 pr-3 font-semibold">Nilai</th>
                                <th class="py-2.5 pr-3 font-semibold">Dikumpulkan</th>
                                <th class="py-2.5 pr-3 font-semibold">Terlambat</th>
                                <th class="py-2.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($submissions as $submission)
                                @php $score = $submission->teacher_score ?? $submission->total_score; @endphp
                                <tr>
                                    <td class="py-3 pr-3">
                                        <p class="font-medium text-neutral-900">{{ $submission->student?->user?->name ?? 'Tanpa nama' }}</p>
                                        <p class="text-xs text-neutral-500">NIS {{ $submission->student?->nis ?? '—' }}</p>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <x-badge :variant="$submission->status->variant()">{{ $submission->status->label() }}</x-badge>
                                    </td>
                                    <td class="py-3 pr-3 font-semibold text-neutral-900">
                                        {{ $score === null ? '—' : rtrim(rtrim(number_format((float) $score, 2, ',', '.'), '0'), ',') }}
                                    </td>
                                    <td class="py-3 pr-3 text-neutral-600">
                                        {{ $submission->submitted_at?->translatedFormat('d M Y, H.i') ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-3">
                                        @if ($submission->is_late)
                                            <x-badge variant="danger">Terlambat</x-badge>
                                        @else
                                            <span class="text-neutral-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @if ($submission->file_path)
                                                <x-button :href="route('teacher.assignments.submissions.file', [$assignment, $submission])" variant="ghost" size="sm" icon="download">Berkas</x-button>
                                            @endif
                                            <x-button :href="route('teacher.grades.edit', $submission)" variant="outline" size="sm" icon="grade">Nilai</x-button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        @if ($assignment->isDraft())
            <x-card class="border-danger-100">
                <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Hapus Draft</h2>
                        <p class="mt-1 text-sm text-neutral-500">Draft tugas dapat dihapus. Tugas yang sudah dipublikasikan tidak dapat dihapus.</p>
                    </div>

                    <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus draft tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger" icon="trash">Hapus Draft</x-button>
                    </form>
                </div>
            </x-card>
        @endif
    </div>
</x-app-layout>
