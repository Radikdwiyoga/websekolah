@props(['subject' => null])

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="code" value="Kode Mata Pelajaran" />
            <x-text-input id="code" name="code" :value="old('code', $subject?->code)" class="mt-1 w-full" placeholder="Contoh: MAT-01" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Mata Pelajaran" />
            <x-text-input id="name" name="name" :value="old('name', $subject?->name)" class="mt-1 w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="description" value="Deskripsi (opsional)" />
            <x-textarea id="description" name="description" rows="3" class="mt-1 w-full">{{ old('description', $subject?->description) }}</x-textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-1" />
        </div>
    </div>
</x-card>
