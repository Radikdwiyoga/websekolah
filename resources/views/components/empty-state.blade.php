@props(['icon' => 'inbox', 'title' => 'Belum ada data'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-neutral-300 px-6 py-10 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-100 text-neutral-400">
        <x-icon :name="$icon" :size="6" />
    </span>
    <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ $title }}</h3>
    @if ((string) $slot !== '')
        <p class="mt-1 max-w-sm text-sm text-neutral-500">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>