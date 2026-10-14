<x-app-layout>
    <x-slot name="title">Agenda &amp; Galeri Sekolah</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Agenda &amp; Galeri"
            description="Kelola foto dan video agenda sekolah untuk media promosi di halaman utama."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Agenda & Galeri']]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.agendas.create')" icon="plus">Tambah Media</x-button>
            </x-slot>
        </x-page-header>

        @if ($agendas->isEmpty())
            <x-card>
                <x-empty-state icon="photo" title="Belum ada agenda">
                    Unggah foto atau video kegiatan sekolah untuk ditampilkan di beranda.
                </x-empty-state>
            </x-card>
        @else
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($agendas as $agenda)
                    <x-card class="flex flex-col">
                        <div class="relative aspect-video w-full overflow-hidden rounded-lg bg-neutral-100">
                            @if ($agenda->isPhoto() && $agenda->mediaUrl())
                                <img src="{{ $agenda->mediaUrl() }}" alt="{{ $agenda->title }}" class="h-full w-full object-cover">
                            @elseif ($agenda->isVideo() && $agenda->hasUploadedMedia())
                                <video src="{{ $agenda->mediaUrl() }}" class="h-full w-full object-cover" muted playsinline preload="metadata"></video>
                            @else
                                <span class="flex h-full w-full items-center justify-center text-neutral-400">
                                    <x-icon :name="$agenda->isVideo() ? 'play' : 'photo'" :size="8" />
                                </span>
                            @endif

                            <span class="absolute left-2 top-2">
                                <x-badge :variant="$agenda->isVideo() ? 'info' : 'primary'">{{ $agenda->media_type->label() }}</x-badge>
                            </span>
                        </div>

                        <div class="mt-4 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-neutral-900">{{ $agenda->title }}</h3>
                                <x-badge :variant="$agenda->is_published ? 'success' : 'neutral'">{{ $agenda->is_published ? 'Terbit' : 'Draf' }}</x-badge>
                            </div>
                            <p class="mt-1 text-xs text-neutral-500">
                                {{ $agenda->event_date?->translatedFormat('d F Y') ?? 'Tanpa tanggal' }}
                                @if ($agenda->sort_order)
                                    &middot; Urutan {{ $agenda->sort_order }}
                                @endif
                            </p>
                            @if ($agenda->description)
                                <p class="mt-2 line-clamp-2 text-sm text-neutral-600">{{ $agenda->description }}</p>
                            @endif
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-1.5 border-t border-neutral-100 pt-3">
                            <x-button :href="route('admin.agendas.edit', $agenda)" variant="ghost" size="sm" icon="edit">Ubah</x-button>

                            <form method="POST" action="{{ route('admin.agendas.toggle-status', $agenda) }}">
                                @csrf
                                @method('PATCH')
                                <x-button type="submit" variant="ghost" size="sm" :icon="$agenda->is_published ? 'x-circle' : 'check'">
                                    {{ $agenda->is_published ? 'Sembunyikan' : 'Terbitkan' }}
                                </x-button>
                            </form>

                            <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}" onsubmit="return confirm('Hapus agenda ini beserta medianya?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="ghost" size="sm" icon="trash" class="text-danger-700 hover:bg-danger-50">Hapus</x-button>
                            </form>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
