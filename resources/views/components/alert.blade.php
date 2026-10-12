@props(['tone' => 'info', 'title' => null])

@php
    $tones = [
        'info' => ['bg' => 'bg-info-100', 'text' => 'text-info-700', 'icon' => 'info'],
        'success' => ['bg' => 'bg-success-100', 'text' => 'text-success-700', 'icon' => 'check'],
        'warning' => ['bg' => 'bg-warning-100', 'text' => 'text-warning-700', 'icon' => 'warning'],
        'danger' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-700', 'icon' => 'x-circle'],
    ];

    $tone = $tones[$tone] ?? $tones['info'];
@endphp

<div role="alert" {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg p-4 '.$tone['bg']]) }}>
    <x-icon :name="$tone['icon']" class="mt-0.5 h-5 w-5 shrink-0 {{ $tone['text'] }}" />
    <div class="min-w-0 text-sm {{ $tone['text'] }}">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div @if ($title) class="mt-1" @endif>{{ $slot }}</div>
    </div>
</div>