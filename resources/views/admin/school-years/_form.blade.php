@props(['schoolYear' => null])

@php
    use App\Enums\Semester;
@endphp

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="name" value="Tahun Ajaran" />
            <x-text-input id="name" name="name" :value="old('name', $schoolYear?->name)" class="mt-1 w-full" placeholder="Contoh: 2026/2027" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="semester" value="Semester" />
            <x-select id="semester" name="semester" class="mt-1 w-full" required>
                @foreach (Semester::cases() as $semester)
                    <option value="{{ $semester->value }}" @selected(old('semester', $schoolYear?->semester?->value) === $semester->value)>
                        {{ $semester->label() }}
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('semester')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="start_date" value="Tanggal Mulai (opsional)" />
            <x-text-input id="start_date" name="start_date" type="date" :value="old('start_date', $schoolYear?->start_date?->toDateString())" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="end_date" value="Tanggal Selesai (opsional)" />
            <x-text-input id="end_date" name="end_date" type="date" :value="old('end_date', $schoolYear?->end_date?->toDateString())" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
        </div>
    </div>
</x-card>
