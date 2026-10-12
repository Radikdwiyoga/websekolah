@php
    $layout = auth()->check() ? 'app-layout' : 'site-layout';
@endphp

<x-dynamic-component :component="$layout">
    <x-slot name="title">{{ $announcement->title }}</x-slot>

    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6">
        <x-page-header
            :title="$announcement->title"
            :breadcrumb="auth()->check() ? [['label' => 'Dashboard', 'route' => 'dashboard'], ['label' => 'Pengumuman', 'route' => 'announcements.index'], ['label' => 'Detail']] : [['label' => 'Beranda', 'route' => 'home'], ['label' => 'Pengumuman']]"
        />

        <div class="flex flex-wrap items-center gap-2">
            <x-badge :variant="$announcement->target_type->variant()">Target: {{ $announcement->target_type->label() }}</x-badge>
            @if ($announcement->is_important)
                <x-badge variant="warning">Penting</x-badge>
            @endif
        </div>

        <x-card>
            <p class="text-sm text-neutral-500">
                Dipublikasikan {{ $announcement->published_at?->translatedFormat('d F Y H:i') ?? '—' }}
                @if ($announcement->createdBy)
                    · oleh {{ $announcement->createdBy->name }}
                @endif
            </p>

            @if ($announcement->isImage())
                <div class="mt-4 overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                    <img src="{{ route('announcements.file', $announcement) }}" alt="{{ $announcement->title }}" class="max-h-[500px] w-full object-contain">
                </div>
            @endif

            <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-neutral-700">{{ $announcement->body }}</div>

            @if ($announcement->attachment_path)
                <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-neutral-100 pt-4">
                    <x-button :href="route('announcements.attachment', $announcement)" variant="outline" size="sm" icon="download">
                        Unduh Lampiran
                    </x-button>
                </div>
            @endif
        </x-card>

        <div>
            @if (auth()->check())
                <x-button :href="route('announcements.index')" variant="ghost" icon="chevron-left">Kembali ke daftar pengumuman</x-button>
            @else
                <x-button :href="route('home')" variant="ghost" icon="chevron-left">Kembali ke Beranda</x-button>
            @endif
        </div>
    </div>
</x-dynamic-component>