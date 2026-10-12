@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-neutral-200 bg-white shadow-sm'.($padding ? ' p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>