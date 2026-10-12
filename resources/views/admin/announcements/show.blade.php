<x-app-layout>
    <x-slot name="title">Detail Pengumuman</x-slot>

    <div class="space-y-6">
        <x-page-header
            :title="$announcement->title"
            description="Detail pengumuman, keterbacaan, dan status pengiriman WhatsApp."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Kelola Pengumuman', 'route' => 'admin.announcements.index'], ['label' => 'Detail']]"
        >
            <x-slot name="actions">
                <div class="flex items-center gap-2">
                    <x-button :href="route('admin.announcements.edit', $announcement)" variant="outline" icon="edit">Ubah</x-button>
                    @if ($announcement->status !== \App\Enums\AnnouncementStatus::Published)
                        <form method="POST" action="{{ route('admin.announcements.publish', $announcement) }}">
                            @csrf
                            <x-button type="submit" icon="send">Terbitkan</x-button>
                        </form>
                    @endif
                </div>
            </x-slot>
        </x-page-header>

        <div class="flex flex-wrap items-center gap-2">
            <x-badge :variant="$announcement->status->variant()">{{ $announcement->status->label() }}</x-badge>
            <x-badge :variant="$announcement->target_type->variant()">Target: {{ $announcement->target_type->label() }}</x-badge>
            @if ($announcement->is_important)
                <x-badge variant="warning">Penting</x-badge>
            @endif
            @if ($announcement->send_whatsapp)
                <x-badge variant="info">Kirim WhatsApp</x-badge>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card label="Dibaca" :value="$announcement->reads->count()" icon="eye" tone="primary" />
            <x-stat-card label="Log WhatsApp" :value="$announcement->waLogs->count()" icon="send" tone="info" />
            <x-stat-card
                label="Dipublikasikan"
                :value="$announcement->published_at?->translatedFormat('d M Y') ?? '—'"
                icon="calendar"
                tone="success"
            />
        </div>

        <x-card>
            <h2 class="text-base font-semibold text-neutral-900">Isi Pengumuman</h2>
            <div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-neutral-700">{{ $announcement->body }}</div>

            @if ($announcement->attachment_path)
                <div class="mt-4 border-t border-neutral-100 pt-4">
                    <x-button :href="route('announcements.attachment', $announcement)" variant="outline" size="sm" icon="download">
                        Unduh Lampiran
                    </x-button>
                </div>
            @endif

            @if ($announcement->target_type === \App\Enums\AnnouncementTarget::ClassRoom)
                <p class="mt-4 text-sm text-neutral-500">
                    Kelas tujuan: {{ $announcement->targets->pluck('schoolClass.name')->filter()->implode(', ') ?: '—' }}
                </p>
            @endif
        </x-card>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Keterbacaan</h2>
                    <x-icon name="eye" :size="5" class="text-neutral-400" />
                </div>

                @if ($announcement->reads->isEmpty())
                    <p class="mt-4 text-sm text-neutral-500">Belum ada yang membuka pengumuman ini.</p>
                @else
                    <ul class="mt-4 divide-y divide-neutral-100 text-sm">
                        @foreach ($announcement->reads->take(10) as $read)
                            <li class="flex items-center justify-between gap-3 py-2">
                                <span class="text-neutral-700">{{ $read->user?->name ?? 'Pengguna dihapus' }}</span>
                                <span class="text-xs text-neutral-400">{{ $read->read_at?->translatedFormat('d M Y H:i') ?? '—' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @if ($announcement->reads->count() > 10)
                        <p class="mt-2 text-xs text-neutral-500">+{{ $announcement->reads->count() - 10 }} lainnya</p>
                    @endif
                @endif
            </x-card>

            <x-card>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Log Pengiriman WhatsApp</h2>
                    <x-icon name="send" :size="5" class="text-neutral-400" />
                </div>

                @if ($announcement->waLogs->isEmpty())
                    <p class="mt-4 text-sm text-neutral-500">Tidak ada notifikasi WhatsApp untuk pengumuman ini.</p>
                @else
                    <ul class="mt-4 divide-y divide-neutral-100 text-sm">
                        @foreach ($announcement->waLogs->take(10) as $log)
                            <li class="flex items-center justify-between gap-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-neutral-700">{{ $log->recipient?->name ?? 'Penerima' }}</p>
                                    <p class="text-xs text-neutral-400">{{ $log->recipient_phone }}</p>
                                </div>
                                <x-badge :variant="$log->status->variant()">{{ $log->status->label() }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        <div>
            <x-button :href="route('admin.announcements.index')" variant="ghost" icon="chevron-left">Kembali ke daftar</x-button>
        </div>
    </div>
</x-app-layout>