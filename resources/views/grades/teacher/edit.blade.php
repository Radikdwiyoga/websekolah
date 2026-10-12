<x-app-layout>
    <x-slot name="title">Nilai Tugas</x-slot>

    <div class="mx-auto w-full max-w-3xl space-y-6">
        <x-page-header
            title="Penilaian Jawaban"
            :description="$submission->student?->user?->name.' • '.$assignment->title"
            :breadcrumb="[
                ['label' => 'Penilaian', 'route' => 'teacher.grades.index'],
                ['label' => $assignment->title],
            ]" />

        <x-card>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Siswa</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">{{ $submission->student?->user?->name ?? 'Tanpa nama' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Dikumpulkan</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">{{ $submission->submitted_at?->translatedFormat('d M Y, H.i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Status</p>
                    <div class="mt-1 flex items-center gap-2">
                        <x-badge :variant="$submission->status->variant()">{{ $submission->status->label() }}</x-badge>
                        @if ($submission->is_late)
                            <x-badge variant="danger">Terlambat</x-badge>
                        @endif
                    </div>
                </div>
            </div>

            @if ($submission->file_path)
                <div class="mt-4 border-t border-neutral-100 pt-4">
                    <x-button :href="route('teacher.assignments.submissions.file', [$assignment, $submission])" variant="outline" size="sm" icon="download">Unduh Berkas Jawaban</x-button>
                </div>
            @endif
        </x-card>

        <form method="POST" action="{{ route('teacher.grades.update', $submission) }}" class="space-y-6" data-submit-loading>
            @csrf
            @method('PUT')

            @if ($assignment->questions->isEmpty())
                <x-card>
                    <p class="text-sm text-neutral-600">Tugas ini berupa unggah berkas. Berikan nilai akhir pada kolom di bawah.</p>
                </x-card>
            @else
                <x-card class="space-y-4">
                    <h2 class="text-base font-semibold text-neutral-900">Lembar Jawaban</h2>

                    @foreach ($assignment->questions as $index => $question)
                        @php
                            $answer = $answers->get($question->id);
                            $auto = $question->type->isAutoGraded();
                        @endphp
                        <div class="rounded-lg border border-neutral-200 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="text-sm font-medium text-neutral-900">
                                    <span class="text-neutral-400">{{ $index + 1 }}.</span> {{ $question->question_text }}
                                </p>
                                <div class="flex items-center gap-2">
                                    @if ($auto && $answer)
                                        <x-badge :variant="$answer->is_correct ? 'success' : 'danger'">
                                            {{ $answer->is_correct ? 'Benar' : 'Salah' }}
                                        </x-badge>
                                    @endif
                                    <x-badge variant="neutral">Maks {{ (float) $question->points }}</x-badge>
                                </div>
                            </div>

                            <p class="mt-2 rounded-md bg-neutral-50 px-3 py-2 text-sm text-neutral-700">
                                {{ $answer?->answer_text ?: 'Tidak dijawab' }}
                            </p>

                            <div class="mt-3 max-w-[180px]">
                                <x-input-label :for="'score_'.$question->id" value="Nilai" />
                                <x-text-input
                                    :id="'score_'.$question->id"
                                    :name="'scores['.$answer?->id.']'"
                                    type="number"
                                    step="0.5"
                                    min="0"
                                    max="100"
                                    class="mt-1"
                                    :value="old('scores.'.$answer?->id, $answer?->score)" />
                            </div>
                        </div>
                    @endforeach
                </x-card>
            @endif

            <x-card class="space-y-5">
                <h2 class="text-base font-semibold text-neutral-900">Nilai Akhir</h2>

                <div>
                    <x-input-label for="teacher_score" value="Nilai Akhir (opsional)" />
                    <x-text-input id="teacher_score" name="teacher_score" type="number" step="0.01" min="0" max="1000" class="mt-1 max-w-[200px]"
                        :value="old('teacher_score', $submission->teacher_score)" />
                    <p class="mt-1 text-xs text-neutral-500">
                        Kosongkan untuk memakai nilai otomatis ({{ $effectiveScore === null ? '—' : (float) $effectiveScore }}). Isi untuk menimpa nilai akhir.
                    </p>
                    <x-input-error :messages="$errors->get('teacher_score')" />
                </div>

                <div>
                    <x-input-label for="teacher_comment" value="Komentar untuk siswa (opsional)" />
                    <x-textarea id="teacher_comment" name="teacher_comment" class="mt-1" rows="3">{{ old('teacher_comment', $submission->teacher_comment) }}</x-textarea>
                    <x-input-error :messages="$errors->get('teacher_comment')" />
                </div>
            </x-card>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-button :href="route('teacher.grades.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Nilai</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
