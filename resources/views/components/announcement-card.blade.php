@props(['announcement'])

<x-card {{ $attributes }} class="flex h-full flex-col">
    <div class="flex items-center justify-between gap-3">
        <time class="text-xs text-neutral-500">
            {{ $announcement->published_at?->translatedFormat('d F Y') }}
        </time>
        @if ($announcement->is_important)
            <x-badge variant="warning" dot>Penting</x-badge>
        @endif
    </div>

    <h3 class="mt-2 text-base font-semibold leading-snug text-neutral-900">{{ $announcement->title }}</h3>

    @if ($announcement->body)
        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-neutral-600">{{ $announcement->body }}</p>
    @endif
</x-card>