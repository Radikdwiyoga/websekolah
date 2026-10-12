@props(['title', 'description' => null, 'breadcrumb' => []])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between']) }}>
    <div class="min-w-0">
        @if (! empty($breadcrumb))
            <nav aria-label="Breadcrumb" class="mb-2">
                <ol class="flex flex-wrap items-center gap-1 text-sm text-neutral-500">
                    @foreach ($breadcrumb as $index => $crumb)
                        <li class="flex items-center gap-1">
                            @if ($index > 0)
                                <x-icon name="chevron-right" :size="3.5" class="shrink-0 text-neutral-400" />
                            @endif
                            @if (($crumb['route'] ?? null) && Illuminate\Support\Facades\Route::has($crumb['route']))
                                <a href="{{ route($crumb['route']) }}" class="transition hover:text-primary-700">{{ $crumb['label'] }}</a>
                            @else
                                <span class="{{ $index === array_key_last($breadcrumb) ? 'font-medium text-neutral-900' : '' }}" aria-current="{{ $index === array_key_last($breadcrumb) ? 'page' : false }}">
                                    {{ $crumb['label'] }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <h1 class="text-h3 text-neutral-950">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-neutral-500">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>