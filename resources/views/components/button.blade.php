@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
])

@php
    $variants = [
        'primary' => 'bg-primary-700 text-white shadow-sm hover:bg-primary-800 focus-visible:ring-primary-500/50',
        'secondary' => 'border border-primary-100 bg-primary-50 text-primary-800 shadow-sm hover:bg-primary-100 focus-visible:ring-primary-500/40',
        'outline' => 'border border-neutral-300 bg-white text-neutral-700 shadow-sm hover:bg-neutral-50 hover:text-neutral-900 focus-visible:ring-primary-500/40',
        'ghost' => 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 focus-visible:ring-primary-500/40',
        'danger' => 'bg-danger-700 text-white shadow-sm hover:bg-danger-700/90 focus-visible:ring-danger-500/50',
    ];

    $sizes = [
        'sm' => 'h-9 gap-1.5 px-3 text-sm',
        'md' => 'h-11 gap-2 px-4 text-sm',
        'lg' => 'h-12 gap-2 px-5 text-base',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center rounded-md font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($disabled || $loading) {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <x-icon name="spinner" />
        @elseif ($icon)
            <x-icon :name="$icon" />
        @endif
        {{ $slot }}
    </button>
@endif