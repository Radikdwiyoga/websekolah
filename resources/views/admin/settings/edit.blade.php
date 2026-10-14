<x-app-layout>
    <x-slot name="title">Edit Pengaturan: {{ $name }}</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Pengaturan"
            :description="$name"
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Pengaturan', 'route' => 'admin.settings.index'], ['label' => 'Edit']]"
        />

        @php
            $displayValue = match (true) {
                is_array($current_value) => json_encode($current_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                is_bool($current_value) => $current_value ? '1' : '0',
                default => (string) ($current_value ?? ''),
            };
        @endphp

        <x-card>
            <form method="POST" action="{{ route('admin.settings.update', $key) }}" data-submit-loading class="space-y-5">
                @csrf
                @method('POST')

                <div>
                    <x-input-label for="value" value="Nilai" />

                    @if ($type === \App\Enums\SettingType::Boolean)
                        <x-select id="value" name="value" class="mt-1">
                            <option value="1" @selected(old('value', $displayValue) === '1')>Ya / Aktif</option>
                            <option value="0" @selected(old('value', $displayValue) === '0')>Tidak / Nonaktif</option>
                        </x-select>
                    @elseif ($type === \App\Enums\SettingType::Integer)
                        <x-text-input id="value" name="value" type="number" :value="old('value', $displayValue)" class="mt-1 w-full" autocomplete="off" />
                    @elseif ($type === \App\Enums\SettingType::Json)
                        <x-textarea id="value" name="value" rows="8" class="mt-1 font-mono" placeholder="{ }">{{ old('value', $displayValue) }}</x-textarea>
                    @else
                        <x-textarea id="value" name="value" rows="3" class="mt-1">{{ old('value', $displayValue) }}</x-textarea>
                    @endif

                    <x-input-error :messages="$errors->get('value')" class="mt-1" />

                    <p class="mt-2 flex items-center gap-1.5 text-xs text-neutral-500">
                        <x-icon name="info" :size="3.5" />
                        Key: <code class="rounded bg-neutral-100 px-1 font-mono text-[11px] text-neutral-700">{{ $key }}</code>
                        &middot; Jenis: {{ $type->label() }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t border-neutral-100 pt-5">
                    <x-button :href="route('admin.settings.index')" variant="ghost" icon="chevron-left">Batal</x-button>
                    <x-button type="submit" icon="check">Simpan Perubahan</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
