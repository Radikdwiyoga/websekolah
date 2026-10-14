@props([
    'schoolClass' => null,
    'schoolYears' => collect(),
    'teachers' => collect(),
    'selectedYearId' => null,
])

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        @if ($schoolClass)
            <div class="sm:col-span-2">
                <x-input-label value="Tahun Ajaran" />
                <p class="mt-1 text-sm font-semibold text-neutral-900">
                    {{ $schoolClass->schoolYear?->name ?? '—' }}
                    @if ($schoolClass->schoolYear?->semester)
                        ({{ ucfirst($schoolClass->schoolYear->semester->value ?? $schoolClass->schoolYear->semester) }})
                    @endif
                </p>
                <p class="mt-1 text-xs text-neutral-400">Tahun ajaran tidak dapat diubah setelah kelas dibuat.</p>
            </div>
        @else
            <div class="sm:col-span-2">
                <x-input-label for="school_year_id" value="Tahun Ajaran" />
                <x-select id="school_year_id" name="school_year_id" class="mt-1 w-full" required>
                    <option value="">Pilih tahun ajaran</option>
                    @foreach ($schoolYears as $year)
                        <option value="{{ $year->id }}" @selected(old('school_year_id', $selectedYearId) == $year->id)>
                            {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('school_year_id')" class="mt-1" />
            </div>
        @endif

        <div>
            <x-input-label for="name" value="Nama Kelas" />
            <x-text-input id="name" name="name" :value="old('name', $schoolClass?->name)" class="mt-1 w-full" placeholder="Contoh: X-1" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="level" value="Tingkat (opsional)" />
            <x-text-input id="level" name="level" :value="old('level', $schoolClass?->level)" class="mt-1 w-full" placeholder="Contoh: X, XI, XII" />
            <x-input-error :messages="$errors->get('level')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="capacity" value="Kapasitas (opsional)" />
            <x-text-input id="capacity" name="capacity" type="number" min="1" max="200" :value="old('capacity', $schoolClass?->capacity)" class="mt-1 w-full" placeholder="Contoh: 32" />
            <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="homeroom_teacher_id" value="Wali Kelas (opsional)" />
            <x-select id="homeroom_teacher_id" name="homeroom_teacher_id" class="mt-1 w-full">
                <option value="">Belum ditentukan</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id', $schoolClass?->homeroom_teacher_id) == $teacher->id)>
                        {{ $teacher->user?->name }}
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('homeroom_teacher_id')" class="mt-1" />
        </div>
    </div>
</x-card>
