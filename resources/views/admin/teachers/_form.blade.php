@props(['teacher' => null])

@php
    $gender = old('gender', $teacher?->gender);
    $birthDate = old('birth_date', $teacher?->birth_date?->toDateString());
@endphp

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" name="name" :value="old('name', $teacher?->user?->name)" class="mt-1 w-full" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="nip" value="NIP (opsional)" />
            <x-text-input id="nip" name="nip" :value="old('nip', $teacher?->nip)" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('nip')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="gender" value="Jenis Kelamin" />
            <x-select id="gender" name="gender" class="mt-1 w-full">
                <option value="">Belum ditentukan</option>
                <option value="L" @selected($gender === 'L')>Laki-laki</option>
                <option value="P" @selected($gender === 'P')>Perempuan</option>
            </x-select>
            <x-input-error :messages="$errors->get('gender')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="birth_date" value="Tanggal Lahir (opsional)" />
            <x-text-input id="birth_date" name="birth_date" type="date" :value="$birthDate" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('birth_date')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="No. HP (opsional)" />
            <x-text-input id="phone" name="phone" :value="old('phone', $teacher?->phone)" class="mt-1 w-full" placeholder="08xxxxxxxxxx" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $teacher?->user?->email)" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="address" value="Alamat (opsional)" />
            <x-textarea id="address" name="address" rows="3" class="mt-1 w-full">{{ old('address', $teacher?->address) }}</x-textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-1" />
        </div>
    </div>
</x-card>
