@props(['announcement'])

<x-card {{ $attributes }} class="flex h-full flex-col overflow-hidden">
    @if ($announcement->isImage())
        <div class="relative -mx-5 -mt-5 mb-4 aspect-video overflow-hidden bg-neutral-100 sm:-mx-6 sm:-mt-6">
            <img src="{{ route('announcements.file', $announcement) }}" alt="{{ $announcement->title }}" class="h-full w-full object-cover transition duration-300 hover:scale-105" loading="lazy">
        </div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <time class="text-xs text-neutral-500">
            {{ $announcement->published_at?->translatedFormat('d F Y') }}
        </time>
        @if ($announcement->is_important)
            <x-badge variant="warning" dot>Penting</x-badge>
        @endif
    </div>

    <h3 class="mt-2 text-base font-semibold leading-snug text-neutral-900">
        <a href="{{ route('announcements.show', $announcement) }}" class="transition hover:text-primary-700">
            {{ $announcement->title }}
        </a>
    </h3>

    @if ($announcement->body)
        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-neutral-600">{{ $announcement->body }}</p>
    @endif

    <div class="mt-4 pt-2">
        <a href="{{ route('announcements.show', $announcement) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-700 hover:text-primary-800">
            Baca selengkapnya &rarr;
        </a>
    </div>
</x-card>