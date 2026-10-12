<x-app-layout>
    <x-slot name="title">Kelola Pengumuman</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Kelola Pengumuman"
            description="Buat, terbitkan, dan arsipkan pengumuman sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Kelola Pengumuman']]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.announcements.create')" icon="plus">Buat Pengumuman</x-button>
            </x-slot>
        </x-page-header>

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full sm:w-64">
                    <x-input-label for="status" value="Status" />
                    <x-select id="status" name="status" class="mt-1">
                        <option value="">Semua status</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected($selectedStatus === $statusOption->value)>{{ $statusOption->label() }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-button type="submit" variant="outline" icon="search">Tampilkan</x-button>
            </form>
        </x-card>

        <x-card>
            @if ($announcements->isEmpty())
                <x-empty-state icon="announcement" title="Belum ada pengumuman">
                    Buat pengumuman pertama untuk warga sekolah.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Judul</th>
                                <th class="pb-2 pr-4">Target</th>
                                <th class="pb-2 pr-4">Tanggal</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2 pr-4">Penting / WA</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($announcements as $announcement)
                                <tr>
                                    <td class="py-2.5 pr-4">
                                        <p class="font-medium text-neutral-900">{{ $announcement->title }}</p>
                                        <p class="line-clamp-1 max-w-md text-xs text-neutral-500">{{ $announcement->body }}</p>
                                    </td>
                                    <td class="py-2.5 pr-4">
                                        <x-badge :variant="$announcement->target_type->variant()">{{ $announcement->target_type->label() }}</x-badge>
                                        @if ($announcement->target_type === \App\Enums\AnnouncementTarget::ClassRoom)
                                            <p class="mt-1 text-xs text-neutral-500">
                                                {{ $announcement->targets->pluck('schoolClass.name')->filter()->implode(', ') ?: '—' }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-neutral-600">
                                        {{ $announcement->published_at?->translatedFormat('d M Y H:i') ?? '—' }}
                                    </td>
                                    <td class="py-2.5 pr-4">
                                        <x-badge :variant="$announcement->status->variant()">{{ $announcement->status->label() }}</x-badge>
                                    </td>
                                    <td class="py-2.5 pr-4">
                                        <div class="flex items-center gap-2">
                                            @if ($announcement->is_important)
                                                <x-badge variant="warning">Penting</x-badge>
                                            @endif
                                            @if ($announcement->send_whatsapp)
                                                <x-badge variant="info">WA</x-badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <x-button :href="route('admin.announcements.show', $announcement)" variant="ghost" size="sm">Lihat</x-button>
                                            <x-button :href="route('admin.announcements.edit', $announcement)" variant="ghost" size="sm" icon="edit">Ubah</x-button>

                                            @if ($announcement->status !== \App\Enums\AnnouncementStatus::Published)
                                                <form method="POST" action="{{ route('admin.announcements.publish', $announcement) }}">
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm" icon="send">Terbitkan</x-button>
                                                </form>
                                            @endif

                                            @if ($announcement->status !== \App\Enums\AnnouncementStatus::Archived)
                                                <form method="POST" action="{{ route('admin.announcements.archive', $announcement) }}">
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm">Arsipkan</x-button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>