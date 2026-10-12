@props(['type' => 'info'])

@php
    $tones = [
        'info' => ['icon' => 'info', 'iconClass' => 'text-info-500'],
        'success' => ['icon' => 'check', 'iconClass' => 'text-success-500'],
        'warning' => ['icon' => 'warning', 'iconClass' => 'text-warning-500'],
        'danger' => ['icon' => 'x-circle', 'iconClass' => 'text-danger-500'],
    ];

    $tone = $tones[$type] ?? $tones['info'];
@endphp

<div data-toast role="status" class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border border-neutral-200 bg-white p-4 shadow-lg">
    <x-icon :name="$tone['icon']" class="mt-0.5 h-5 w-5 shrink-0 {{ $tone['iconClass'] }}" />
    <p class="min-w-0 flex-1 text-sm font-medium text-neutral-800">{{ $slot }}</p>
    <button type="button" data-toast-close class="shrink-0 text-neutral-400 transition hover:text-neutral-600" aria-label="Tutup notifikasi">
        <x-icon name="x" :size="4" />
    </button>
</div>