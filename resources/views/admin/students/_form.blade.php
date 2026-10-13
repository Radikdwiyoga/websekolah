@props([
    'student' => null,
    'classes' => collect(),
    'selectedClassId' => null,
])

@php
    $currentClassId = (int) old('class_id', $student?->class_id ?? $selectedClassId ?? 0);
    $currentGender = old('gender', $student?->gender);
@endphp

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" name="name" :value="old('name', $student?->user?->name)" class="mt-1 w-full" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="nis" value="NIS" />
            <x-text-input id="nis" name="nis" :value="old('nis', $student?->nis)" class="mt-1 w-full" placeholder="Contoh: 2026001" required />
            <p class="mt-1 text-xs text-neutral-400">NIS juga dipakai sebagai username untuk login siswa.</p>
            <x-input-error :messages="$errors->get('nis')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="nisn" value="NISN (opsional)" />
            <x-text-input id="nisn" name="nisn" :value="old('nisn', $student?->nisn)" class="mt-1 w-full" placeholder="Nomor Induk Siswa Nasional" />
            <x-input-error :messages="$errors->get('nisn')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="gender" value="Jenis Kelamin" />
            <x-select id="gender" name="gender" class="mt-1 w-full">
                <option value="">Belum ditentukan</option>
                <option value="L" @selected($currentGender === 'L')>Laki-laki</option>
                <option value="P" @selected($currentGender === 'P')>Perempuan</option>
            </x-select>
            <x-input-error :messages="$errors->get('gender')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="birth_date" value="Tanggal Lahir (opsional)" />
            <x-text-input id="birth_date" name="birth_date" type="date" :value="old('birth_date', $student?->birth_date?->toDateString())" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('birth_date')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="class_id" value="Kelas" />
            <x-select id="class_id" name="class_id" class="mt-1 w-full">
                <option value="">Belum ada kelas</option>
                @foreach ($classes as $classOption)
                    <option value="{{ $classOption->id }}" @selected($currentClassId === $classOption->id)>
                        {{ $classOption->name }}@if (! $classOption->schoolYear?->is_active) ({{ $classOption->schoolYear?->name }})@endif
                    </option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('class_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $student?->user?->email)" class="mt-1 w-full" placeholder="siswa@contoh.sch.id" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="Telepon (opsional)" />
            <x-text-input id="phone" name="phone" :value="old('phone', $student?->user?->phone)" class="mt-1 w-full" placeholder="08xxxxxxxxxx" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="password" value="Password (opsional)" />
            <x-text-input id="password" name="password" type="password" class="mt-1 w-full" autocomplete="new-password" />
            <p class="mt-1 text-xs text-neutral-400">
                @if ($student)
                    Kosongkan bila tidak ingin mengubah password siswa.
                @else
                    Kosongkan untuk memakai password bawaan dari Pengaturan.
                @endif
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="address" value="Alamat (opsional)" />
            <x-textarea id="address" name="address" rows="3" class="mt-1 w-full">{{ old('address', $student?->address) }}</x-textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-1" />
        </div>
    </div>
</x-card>
