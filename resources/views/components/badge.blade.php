@props(['variant' => 'neutral', 'dot' => false])

@php
    $variants = [
        'success' => 'bg-success-100 text-success-700',
        'warning' => 'bg-warning-100 text-warning-700',
        'danger' => 'bg-danger-100 text-danger-700',
        'info' => 'bg-info-100 text-info-700',
        'neutral' => 'bg-neutral-100 text-neutral-700',
        'primary' => 'bg-primary-100 text-primary-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-pill px-2.5 py-1 text-xs font-semibold '.($variants[$variant] ?? $variants['neutral'])]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>