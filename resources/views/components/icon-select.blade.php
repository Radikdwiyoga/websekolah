@props([
    'name',
    'value' => null,
    'icons' => [],
    'labels' => [],
])

@php
    $selected = in_array($value, $icons, true) ? $value : ($icons[0] ?? 'check');
    $iconLabel = static fn (string $icon): string => $labels[$icon] ?? ucfirst($icon);
@endphp

{{-- Custom dropdown so each option shows the real icon instead of its name.
     A hidden input carries the value, and app.js handles the open/select
     behaviour via event delegation (works for repeater rows cloned later). --}}
<div class="relative" data-icon-select>
    <input type="hidden" name="{{ $name }}" value="{{ $selected }}" data-icon-select-input>

    <button
        type="button"
        data-icon-select-trigger
        aria-haspopup="listbox"
        {{ $attributes->merge(['class' => 'flex w-full items-center gap-2 rounded-md border border-neutral-300 bg-white px-3.5 py-2.5 text-left text-sm text-neutral-950 shadow-sm transition focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30']) }}
    >
        <span data-icon-select-preview class="flex h-5 w-5 shrink-0 items-center justify-center text-primary-700">
            <x-icon :name="$selected" :size="5" />
        </span>
        <span data-icon-select-label class="flex-1 truncate">{{ $iconLabel($selected) }}</span>
        <x-icon name="chevron-down" :size="4" class="shrink-0 text-neutral-400" />
    </button>

    <div
        data-icon-select-menu
        role="listbox"
        class="absolute z-20 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-md border border-neutral-200 bg-white py-1 shadow-lg"
    >
        @foreach ($icons as $icon)
            <button
                type="button"
                role="option"
                data-icon-select-option
                data-value="{{ $icon }}"
                data-label="{{ $iconLabel($icon) }}"
                aria-selected="{{ $icon === $selected ? 'true' : 'false' }}"
                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm transition hover:bg-neutral-50 {{ $icon === $selected ? 'bg-primary-50 text-primary-800' : 'text-neutral-700' }}"
            >
                <span data-icon-select-icon class="flex h-5 w-5 shrink-0 items-center justify-center text-primary-700">
                    <x-icon :name="$icon" :size="5" />
                </span>
                <span class="flex-1 truncate">{{ $iconLabel($icon) }}</span>
                <span data-icon-select-check class="shrink-0 text-primary-700 {{ $icon === $selected ? '' : 'hidden' }}">
                    <x-icon name="check" :size="4" />
                </span>
            </button>
        @endforeach
    </div>
</div>
