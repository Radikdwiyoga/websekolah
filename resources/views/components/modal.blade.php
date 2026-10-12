@props(['name', 'title' => null, 'maxWidth' => 'md'])

@php
    $maxWidths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<dialog
    id="{{ $name }}"
    class="w-[calc(100%-2rem)] rounded-xl border border-neutral-200 bg-white shadow-lg backdrop:bg-neutral-950/45 open:flex open:flex-col {{ $maxWidths[$maxWidth] ?? $maxWidths['md'] }}"
    {{ $attributes }}
>
    <div class="flex items-center justify-between gap-4 border-b border-neutral-200 px-5 py-4">
        <h3 class="text-base font-semibold text-neutral-900">{{ $title }}</h3>
        <button type="button" data-modal-close class="-mr-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-neutral-400 transition hover:bg-neutral-100 hover:text-neutral-700" aria-label="Tutup dialog">
            <x-icon name="x" :size="5" />
        </button>
    </div>

    <div class="max-h-[70vh] overflow-y-auto p-5">{{ $slot }}</div>

    @isset($footer)
        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-neutral-200 px-5 py-4">{{ $footer }}</div>
    @endisset
</dialog>