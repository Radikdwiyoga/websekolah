<x-app-layout>
    <x-slot name="title">Pengumuman</x-slot>

    <div class="space-y-6">
        <x-page-header
            :title="$announcement->title"
            :breadcrumb="[['label' => 'Beranda', 'route' => 'home'], ['label' => 'Pengumuman', 'route' => 'announcements.index'], ['label' => 'Detail']]"
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

            <div class="mt-4 whitespace-pre-line text-sm leading-relaxed text-neutral-700">{{ $announcement->body }}</div>

            @if ($announcement->attachment_path)
                <div class="mt-5 border-t border-neutral-100 pt-4">
                    <x-button :href="route('announcements.attachment', $announcement)" variant="outline" size="sm" icon="download">
                        Unduh Lampiran
                    </x-button>
                </div>
            @endif
        </x-card>

        <div>
            <x-button :href="route('announcements.index')" variant="ghost" icon="chevron-left">Kembali ke daftar pengumuman</x-button>
        </div>
    </div>
</x-app-layout>