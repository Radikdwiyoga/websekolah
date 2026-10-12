@props(['icon' => 'check', 'title', 'tone' => 'primary'])

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
    <span class="flex h-12 w-12 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['primary'] }}">
        <x-icon :name="$icon" :size="6" />
    </span>
    <h3 class="mt-4 text-lg font-semibold text-neutral-900">{{ $title }}</h3>
    <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $slot }}</p>
</x-card>