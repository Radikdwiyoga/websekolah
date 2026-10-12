@php
    $isEdit = $isEdit ?? false;
    $currentPair = $assignment ? $assignment->class_id.':'.$assignment->subject_id : old('pair');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6" data-submit-loading>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <x-card class="space-y-5">
        <div>
            <x-input-label for="title" value="Judul Tugas" />
            <x-text-input id="title" name="title" class="mt-1" :value="old('title', $assignment?->title)" required />
            <x-input-error :messages="$errors->get('title')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="pair" value="Kelas &amp; Mata Pelajaran" />
                <x-select id="pair" name="pair" class="mt-1" required>
                    <option value="">Pilih kelas dan mata pelajaran</option>
                    @foreach ($pairs as $pair)
                        <option value="{{ $pair['class']->id }}:{{ $pair['subject']->id }}" @selected($currentPair === $pair['class']->id.':'.$pair['subject']->id)>
                            {{ $pair['class']->name }} &mdash; {{ $pair['subject']->name }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('class_id')" />
                <x-input-error :messages="$errors->get('subject_id')" />
            </div>

            <div>
                <x-input-label for="deadline" value="Tenggat (opsional)" />
                <x-text-input
                    id="deadline"
                    name="deadline"
                    type="datetime-local"
                    class="mt-1"
                    :value="old('deadline', $assignment?->deadline?->format('Y-m-d\TH:i'))" />
                <x-input-error :messages="$errors->get('deadline')" />
            </div>
        </div>

        @unless ($isEdit)
            <div>
                <x-input-label value="Jenis Soal" />
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($types as $type)
                        <label class="flex cursor-pointer items-start gap-2 rounded-lg border border-neutral-300 px-3 py-2.5 text-sm transition hover:border-primary-400 has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                            <input type="radio" name="type" value="{{ $type->value }}" class="mt-0.5 text-primary-700" @checked(old('type') === $type->value) required>
                            <span>
                                <span class="block font-medium text-neutral-800">{{ $type->label() }}</span>
                                <span class="block text-xs text-neutral-500">{{ $type->isAutoGraded() ? 'Dinilai otomatis' : 'Dinilai manual oleh guru' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('type')" />
            </div>
        @endunless

        <div>
            <x-input-label for="description" value="Deskripsi (opsional)" />
            <x-textarea id="description" name="description" class="mt-1" rows="4">{{ old('description', $assignment?->description) }}</x-textarea>
            <x-input-error :messages="$errors->get('description')" />
        </div>
    </x-card>

    <x-card class="space-y-5">
        <h2 class="text-base font-semibold text-neutral-900">Pengaturan Penilaian</h2>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="grade_weight" value="Bobot Nilai" />
                <x-text-input id="grade_weight" name="grade_weight" type="number" min="1" max="100" class="mt-1"
                    :value="old('grade_weight', $assignment?->grade_weight ?? $defaultGradeWeight)" required />
                <x-input-error :messages="$errors->get('grade_weight')" />
            </div>

            <div>
                <x-input-label for="late_deduction_percent" value="Potongan Nilai Terlambat (%)" />
                <x-text-input id="late_deduction_percent" name="late_deduction_percent" type="number" min="0" max="100" class="mt-1"
                    :value="old('late_deduction_percent', $assignment?->late_deduction_percent ?? 0)" required />
                <x-input-error :messages="$errors->get('late_deduction_percent')" />
            </div>
        </div>

        <label class="flex items-center gap-3">
            <input type="hidden" name="allow_resubmit" value="0">
            <input type="checkbox" name="allow_resubmit" value="1" class="rounded border-neutral-300 text-primary-700 focus:ring-primary-500/40"
                @checked(old('allow_resubmit', $assignment?->allow_resubmit ?? true))>
            <span class="text-sm text-neutral-700">Izinkan siswa mengirim ulang jawaban selama tenggat belum lewat</span>
        </label>
    </x-card>

    <div class="flex flex-wrap items-center justify-end gap-3">
        <x-button :href="$cancelUrl" variant="ghost">Batal</x-button>
        <x-button type="submit" icon="check">{{ $submitLabel }}</x-button>
    </div>
</form>
