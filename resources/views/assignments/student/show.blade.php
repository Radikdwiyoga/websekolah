<x-app-layout>
    <x-slot name="title">{{ $assignment->title }}</x-slot>

    @php
        $type = $assignment->type;
        $canSubmit = auth()->user()->can('submit', $assignment);
        $graded = $submission?->status === \App\Enums\SubmissionStatus::Graded;
        $hasQuestions = $assignment->questions->isNotEmpty();
        $totalQuestions = $assignment->questions->count();
    @endphp

    <div class="mx-auto w-full max-w-3xl space-y-6">
        <x-page-header
            :title="$assignment->title"
            :description="$assignment->subject?->name.' • Kelas '.$assignment->schoolClass?->name"
            :breadcrumb="[
                ['label' => 'Tugas', 'route' => 'student.assignments.index'],
                ['label' => $assignment->title],
            ]" />

        {{-- Informasi tugas --}}
        <x-card>
            <div class="flex flex-wrap items-center gap-2">
                <x-badge :variant="$assignment->status->variant()" dot>{{ $assignment->status->label() }}</x-badge>
                <x-badge variant="neutral">{{ $type->label() }}</x-badge>
                @if ($submission?->is_late)
                    <x-badge variant="danger">Dikumpulkan terlambat</x-badge>
                @endif
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Tenggat</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">{{ $assignment->deadline?->translatedFormat('d M Y, H.i') ?? 'Tanpa tenggat' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-neutral-400">Potongan Terlambat</p>
                    <p class="mt-1 text-sm font-medium text-neutral-800">{{ $assignment->late_deduction_percent }}%</p>
                </div>
            </div>

            @if ($assignment->description)
                <p class="mt-4 border-t border-neutral-100 pt-4 text-sm text-neutral-600">{{ $assignment->description }}</p>
            @endif
        </x-card>

        {{-- Hasil penilaian --}}
        @if ($graded)
            <x-card class="border-success-100 bg-success-50/40">
                <div class="flex flex-col items-center gap-2 py-2 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-success-100 text-success-700">
                        <x-icon name="grade" :size="7" />
                    </span>
                    <p class="text-sm text-neutral-600">Nilai Anda</p>
                    <p class="text-3xl font-bold text-neutral-950">{{ $effectiveScore === null ? '—' : (float) $effectiveScore }}</p>

                    @if ($submission->teacher_comment)
                        <p class="mt-2 max-w-md rounded-md bg-white px-3 py-2 text-sm text-neutral-600">{{ $submission->teacher_comment }}</p>
                    @endif
                </div>
            </x-card>
        @endif

        @if ($canSubmit)
            {{-- Form pengerjaan --}}
            <form method="POST" action="{{ route('student.assignments.save', $assignment) }}" enctype="multipart/form-data" class="space-y-5"
                data-answer-progress
                data-answer-total="{{ $totalQuestions }}">
                @csrf
                <input type="hidden" name="finalize" value="1">

                <x-card>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-neutral-700">Progres Pengerjaan</span>
                        <span data-progress-label class="text-neutral-500"></span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-pill bg-neutral-100">
                        <div data-progress-bar class="h-full rounded-pill bg-primary-600 transition-all" style="width: 0%"></div>
                    </div>
                    <p class="mt-2 text-xs text-neutral-500">Jawaban dapat diubah selama masih dalam batas waktu.</p>
                </x-card>

                @if ($type === \App\Enums\AssignmentType::FileUpload)
                    <x-card>
                        <x-input-label for="file" value="Berkas Jawaban" />
                        <input id="file" name="file" type="file" class="mt-1 block w-full text-sm text-neutral-700 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary-800 hover:file:bg-primary-100" />

                        @if ($submission?->file_path)
                            <p class="mt-3 text-sm text-neutral-600">
                                Berkas terkirim sebelumnya.
                                <a href="{{ route('student.assignments.file', $assignment) }}" class="font-medium text-primary-700 underline">Unduh berkas</a>
                            </p>
                        @endif

                        <x-input-error :messages="$errors->get('file')" />
                    </x-card>
                @else
                    @foreach ($assignment->questions as $index => $question)
                        @php $answer = $answers->get($question->id); @endphp

                        <x-card data-question>
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium text-neutral-900">
                                    <span class="text-neutral-400">{{ $index + 1 }}.</span> {{ $question->question_text }}
                                </p>
                                <x-badge variant="neutral">{{ (float) $question->points }} poin</x-badge>
                            </div>

                            <div class="mt-3 space-y-2">
                                @if ($question->type === \App\Enums\AssignmentType::MultipleChoice)
                                    @foreach ($question->options ?? [] as $key => $option)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-md border border-neutral-200 px-3 py-2 text-sm transition hover:border-primary-400 has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" class="text-primary-700" @checked(($answer?->answer_text ?? '') === $key)>
                                            <span><span class="font-semibold">{{ $key }}.</span> {{ $option }}</span>
                                        </label>
                                    @endforeach
                                @elseif ($question->type === \App\Enums\AssignmentType::ShortAnswer)
                                    <x-text-input :name="'answers['.$question->id.']'" :value="$answer?->answer_text" placeholder="Tulis jawaban singkat" />
                                @else
                                    <x-textarea :name="'answers['.$question->id.']'" rows="4" placeholder="Tulis jawaban esai Anda">{{ $answer?->answer_text }}</x-textarea>
                                @endif
                            </div>
                        </x-card>
                    @endforeach
                @endif

                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($type !== \App\Enums\AssignmentType::FileUpload)
                        <button type="submit" name="finalize" value="0" class="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-neutral-300 bg-white px-4 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-neutral-50">
                            Simpan Draft
                        </button>
                    @endif

                    <x-button
                        type="submit"
                        icon="send"
                        onclick="return confirm('Kirim jawaban sekarang? Setelah dikirim, jawaban masih dapat diubah selama batas waktu belum lewat.')">
                        Kirim Jawaban
                    </x-button>
                </div>
            </form>
        @else
            {{-- Tampilan jawaban tersimpan --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Jawaban Anda</h2>

                @if (! $submission)
                    <div class="mt-4">
                        <x-empty-state icon="info" title="Belum ada jawaban tersimpan">
                            {{ $assignment->isClosed() ? 'Tugas telah ditutup.' : 'Anda belum mengerjakan tugas ini.' }}
                        </x-empty-state>
                    </div>
                @elseif ($type === \App\Enums\AssignmentType::FileUpload)
                    <div class="mt-4">
                        <x-button :href="route('student.assignments.file', $assignment)" variant="outline" size="sm" icon="download">Unduh Berkas Jawaban</x-button>
                    </div>
                @else
                    <ol class="mt-4 space-y-3">
                        @foreach ($assignment->questions as $index => $question)
                            @php $answer = $answers->get($question->id); @endphp
                            <li class="rounded-lg border border-neutral-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm font-medium text-neutral-900">
                                        <span class="text-neutral-400">{{ $index + 1 }}.</span> {{ $question->question_text }}
                                    </p>
                                    @if ($graded && $question->type->isAutoGraded() && $answer)
                                        <x-badge :variant="$answer->is_correct ? 'success' : 'danger'">{{ $answer->is_correct ? 'Benar' : 'Salah' }}</x-badge>
                                    @endif
                                </div>
                                <p class="mt-2 rounded-md bg-neutral-50 px-3 py-2 text-sm text-neutral-700">{{ $answer?->answer_text ?: 'Tidak dijawab' }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-card>

            @if (! $submission && ! $assignment->isClosed())
                <x-alert tone="warning">Tugas ini tidak dapat dikerjakan lagi.</x-alert>
            @endif
        @endif
    </div>
</x-app-layout>
