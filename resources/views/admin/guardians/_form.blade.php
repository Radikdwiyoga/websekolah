@props(['guardian' => null])

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" name="name" :value="old('name', $guardian?->user?->name)" class="mt-1 w-full" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="No. WhatsApp / HP (opsional)" />
            <x-text-input id="phone" name="phone" :value="old('phone', $guardian?->phone)" class="mt-1 w-full" placeholder="08xxxxxxxxxx" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="occupation" value="Pekerjaan (opsional)" />
            <x-text-input id="occupation" name="occupation" :value="old('occupation', $guardian?->occupation)" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('occupation')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $guardian?->user?->email)" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="address" value="Alamat (opsional)" />
            <x-textarea id="address" name="address" rows="3" class="mt-1 w-full">{{ old('address', $guardian?->address) }}</x-textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <label class="flex items-center gap-2.5">
                <input type="hidden" name="whatsapp_opt_in" value="0">
                <input
                    type="checkbox"
                    name="whatsapp_opt_in"
                    value="1"
                    @checked(old('whatsapp_opt_in', $guardian?->whatsapp_opt_in ?? true))
                    class="h-4 w-4 rounded border-neutral-300 text-primary-700 focus:ring-primary-500/40"
                >
                <span class="text-sm text-neutral-700">Setuju menerima notifikasi WhatsApp</span>
            </label>
            <x-input-error :messages="$errors->get('whatsapp_opt_in')" class="mt-1" />
        </div>
    </div>
</x-card>
