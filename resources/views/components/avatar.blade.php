@props(['name' => '', 'size' => 'md', 'src' => null])

@php
    $sizes = [
        'xs' => 'h-6 w-6 text-xs',
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
    ];

    $initials = '';
    foreach (array_slice(explode(' ', trim((string) $name)), 0, 2) as $word) {
        if ($word !== '') {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
    }
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" class="inline-flex shrink-0 rounded-full object-cover {{ $sizes[$size] ?? $sizes['md'] }}" {{ $attributes }} />
@else
    <span class="inline-flex shrink-0 items-center justify-center rounded-full bg-primary-100 font-semibold text-primary-800 {{ $sizes[$size] ?? $sizes['md'] }}" {{ $attributes }}>
        {{ $initials !== '' ? $initials : '?' }}
    </span>
@endif