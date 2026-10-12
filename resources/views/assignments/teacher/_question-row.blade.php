@php
    /** @var int|string $index */
    $row = $row ?? null;
    $choiceKeys = ['A', 'B', 'C', 'D'];
@endphp

<div data-repeater-row class="rounded-lg border border-neutral-200 bg-neutral-50/60 p-4">
    <div class="flex items-center justify-between gap-3">
        <span class="text-sm font-semibold text-neutral-600">Soal</span>
        <button type="button" data-repeater-remove class="inline-flex items-center gap-1 text-sm font-medium text-danger-700 transition hover:text-danger-700/80">
            <x-icon name="trash" :size="4" />
            Hapus
        </button>
    </div>

    <div class="mt-3">
        <x-input-label :for="'question_text_'.$index" value="Pertanyaan" />
        <x-textarea :id="'question_text_'.$index" :name="'questions['.$index.'][question_text]'" class="mt-1" rows="2">{{ $row['question_text'] ?? '' }}</x-textarea>
    </div>

    @if ($type === \App\Enums\AssignmentType::MultipleChoice)
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach ($choiceKeys as $choice)
                <div>
                    <x-input-label :for="'option_'.$choice.'_'.$index" :value="'Pilihan '.$choice" />
                    <x-text-input :id="'option_'.$choice.'_'.$index" :name="'questions['.$index.'][options]['.$choice.']'" class="mt-1" :value="$row['options'][$choice] ?? ''" />
                </div>
            @endforeach
        </div>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div>
                <x-input-label :for="'correct_'.$index" value="Jawaban Benar" />
                <x-select :id="'correct_'.$index" :name="'questions['.$index.'][correct_answer]'" class="mt-1">
                    @foreach ($choiceKeys as $choice)
                        <option value="{{ $choice }}" @selected(($row['correct_answer'] ?? 'A') === $choice)>{{ $choice }}</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-input-label :for="'points_'.$index" value="Poin" />
                <x-text-input :id="'points_'.$index" :name="'questions['.$index.'][points]'" type="number" step="0.5" min="0.5" max="100" class="mt-1" :value="$row['points'] ?? 1" />
            </div>
        </div>
    @elseif ($type === \App\Enums\AssignmentType::ShortAnswer)
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <x-input-label :for="'answers_'.$index" value="Jawaban Benar (satu per baris)" />
                <x-textarea :id="'answers_'.$index" :name="'questions['.$index.'][correct_answers]'" class="mt-1" rows="2">{{ $row['correct_answers'] ?? '' }}</x-textarea>
            </div>

            <div>
                <x-input-label :for="'points_'.$index" value="Poin" />
                <x-text-input :id="'points_'.$index" :name="'questions['.$index.'][points]'" type="number" step="0.5" min="0.5" max="100" class="mt-1" :value="$row['points'] ?? 1" />
            </div>
        </div>
    @else
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <p class="rounded-md bg-info-50 px-3 py-2 text-sm text-info-700">Jawaban esai akan dinilai manual oleh guru.</p>
            </div>

            <div>
                <x-input-label :for="'points_'.$index" value="Poin" />
                <x-text-input :id="'points_'.$index" :name="'questions['.$index.'][points]'" type="number" step="0.5" min="0.5" max="100" class="mt-1" :value="$row['points'] ?? 10" />
            </div>
        </div>
    @endif
</div>
