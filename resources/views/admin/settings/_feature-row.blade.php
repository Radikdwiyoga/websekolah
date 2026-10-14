@props(['type', 'index', 'row' => null])

@php
    $icons = \App\Services\HomepageContentService::ICONS;
    $tones = \App\Services\HomepageContentService::TONES;
@endphp

<div data-repeater-row class="rounded-lg border border-neutral-200 bg-neutral-50/60 p-4">
    <div class="flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
            {{ $type === 'advantages' ? 'Keunggulan' : 'Program' }}
        </p>
        <button type="button" data-repeater-remove class="inline-flex items-center gap-1 text-sm font-medium text-danger-700 transition hover:text-danger-700/80">
            <x-icon name="trash" :size="4" />
            Hapus
        </button>
    </div>

    <div class="mt-3 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label :for="$type.'_icon_'.$index" value="Ikon" />
            <x-icon-select
                :id="$type.'_icon_'.$index"
                :name="$type.'['.$index.'][icon]'"
                :value="old($type.'.'.$index.'.icon', $row['icon'] ?? 'check')"
                :icons="\App\Services\HomepageContentService::ICONS"
                :labels="\App\Services\HomepageContentService::ICON_LABELS"
                class="mt-1"
            />
            <x-input-error :messages="$errors->get($type.'.'.$index.'.icon')" class="mt-1" />
        </div>

        <div>
            <x-input-label :for="$type.'_tone_'.$index" value="Warna" />
            <x-select :id="$type.'_tone_'.$index" :name="$type.'['.$index.'][tone]'" class="mt-1">
                @foreach ($tones as $value => $label)
                    <option value="{{ $value }}" @selected(old($type.'.'.$index.'.tone', $row['tone'] ?? 'primary') === $value)>{{ $label }}</option>
                @endforeach
            </x-select>
        </div>

        <div class="sm:col-span-2">
            <x-input-label :for="$type.'_title_'.$index" value="Judul" />
            <x-text-input :id="$type.'_title_'.$index" :name="$type.'['.$index.'][title]'" class="mt-1 w-full" :value="old($type.'.'.$index.'.title', $row['title'] ?? '')" placeholder="Contoh: Pendidikan Berkualitas" />
            <x-input-error :messages="$errors->get($type.'.'.$index.'.title')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label :for="$type.'_description_'.$index" value="Deskripsi" />
            <x-textarea :id="$type.'_description_'.$index" :name="$type.'['.$index.'][description]'" rows="2" class="mt-1">{{ old($type.'.'.$index.'.description', $row['description'] ?? '') }}</x-textarea>
            <x-input-error :messages="$errors->get($type.'.'.$index.'.description')" class="mt-1" />
        </div>
    </div>
</div>
