<x-app-layout>
    <x-slot name="title">Pengumuman</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Pengumuman"
            description="Informasi resmi dan pengumuman terbaru dari sekolah."
            :breadcrumb="[['label' => 'Beranda', 'route' => 'home'], ['label' => 'Pengumuman']]"
        />

        @if ($announcements->isEmpty())
            <x-empty-state icon="announcement" title="Belum ada pengumuman">
                Pengumuman dari sekolah akan tampil di sini.
            </x-empty-state>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($announcements as $announcement)
                    <x-card class="flex flex-col">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge :variant="$announcement->target_type->variant()">{{ $announcement->target_type->label() }}</x-badge>
                                @if ($announcement->is_important)
                                    <x-badge variant="warning">Penting</x-badge>
                                @endif
                                @unless ($announcement->reads->contains('user_id', auth()->id()))
                                    <x-badge variant="primary">Baru</x-badge>
                                @endunless
                            </div>
                            <span class="text-xs text-neutral-400">{{ $announcement->published_at?->translatedFormat('d M Y') }}</span>
                        </div>

                        <h3 class="mt-3 text-base font-semibold leading-snug text-neutral-900">{{ $announcement->title }}</h3>
                        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-neutral-600">{{ $announcement->body }}</p>

                        <div class="mt-4">
                            <x-button :href="route('announcements.show', $announcement)" variant="outline" size="sm" icon="eye">
                                Baca selengkapnya
                            </x-button>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>