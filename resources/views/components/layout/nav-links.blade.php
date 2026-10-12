@props(['items' => [], 'alwaysShowLabels' => false])

@php
    $current = null;
    $isActive = static fn (array $item): bool => request()->routeIs($item['active'] ?? [$item['route']]);
@endphp

<nav {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @foreach ($items as $item)
        @php
            $section = $item['section'] ?? null;
            $exists = Illuminate\Support\Facades\Route::has($item['route']);
            $active = $exists && $isActive($item);
        @endphp

        @if ($section !== null && $section !== $current)
            @php $current = $section; @endphp
            <p class="{{ $alwaysShowLabels ? '' : 'hidden lg:block' }} px-3 pb-2 pt-5 text-xs font-semibold uppercase tracking-wider text-neutral-400">
                {{ $section }}
            </p>
        @endif

        @if ($exists)
            <a
                href="{{ route($item['route']) }}"
                title="{{ $item['label'] }}"
                class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold transition {{ $alwaysShowLabels ? '' : 'justify-center lg:justify-start' }} {{ $active ? 'bg-primary-100 text-primary-800' : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900' }}"
                @if ($active) aria-current="page" @endif
            >
                <x-icon :name="$item['icon']" :size="5" class="shrink-0" />
                <span class="{{ $alwaysShowLabels ? '' : 'hidden lg:inline' }}">{{ $item['label'] }}</span>
            </a>
        @else
            <span
                title="Fitur akan segera tersedia"
                class="flex cursor-not-allowed items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold text-neutral-400 {{ $alwaysShowLabels ? '' : 'justify-center lg:justify-start' }}"
            >
                <x-icon :name="$item['icon']" :size="5" class="shrink-0" />
                <span class="{{ $alwaysShowLabels ? '' : 'hidden lg:inline' }}">{{ $item['label'] }}</span>
            </span>
        @endif
    @endforeach
</nav>