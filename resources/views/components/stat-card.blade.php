@props(['label', 'value' => null, 'icon' => null, 'tone' => 'primary'])

@php
    $tones = [
        'primary' => 'bg-primary-100 text-primary-700',
        'success' => 'bg-success-100 text-success-700',
        'warning' => 'bg-warning-100 text-warning-700',
        'danger' => 'bg-danger-100 text-danger-700',
        'info' => 'bg-info-100 text-info-700',
    ];
@endphp

<x-card {{ $attributes }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-neutral-500">{{ $label }}</p>
            <p class="mt-1 truncate text-3xl font-bold text-neutral-950">{{ $value ?? $slot }}</p>
        </div>

        @if ($icon)
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['primary'] }}">
                <x-icon :name="$icon" :size="6" />
            </span>
        @endif
    </div>

    @isset($footer)
        <div class="mt-3 border-t border-neutral-100 pt-3 text-sm text-neutral-500">{{ $footer }}</div>
    @endisset
</x-card>