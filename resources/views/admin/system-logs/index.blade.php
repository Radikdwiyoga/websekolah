<x-app-layout>
    <x-slot name="title">System Log</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="System Log"
            description="Riwayat aktivitas dan audit trail sistem sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'System Log']]"
        />

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari Aktivitas" />
                    <x-text-input id="search" name="search" :value="$search" placeholder="Deskripsi log..." class="mt-1 w-full" />
                </div>
                <div class="w-full sm:w-48">
                    <x-input-label for="module" value="Modul" />
                    <x-select id="module" name="module" class="mt-1">
                        <option value="">Semua Modul</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod }}" @selected($selectedModule === $mod)>{{ ucfirst($mod) }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="w-full sm:w-48">
                    <x-input-label for="status" value="Status" />
                    <x-select id="status" name="status" class="mt-1">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" @selected($selectedStatus?->value === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-button type="submit" variant="outline" icon="search">Filter</x-button>
                @if ($search || $selectedModule || $selectedStatus)
                    <x-button :href="route('admin.system-logs.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            @if ($logs->isEmpty())
                <x-empty-state icon="log" title="Belum ada riwayat aktivitas">
                    Tidak ada aktivitas sistem yang tercatat dengan kriteria ini.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Waktu</th>
                                <th class="pb-2 pr-4">Pengguna</th>
                                <th class="pb-2 pr-4">Aktivitas</th>
                                <th class="pb-2 pr-4">Modul / Aksi</th>
                                <th class="pb-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="py-3 pr-4 whitespace-nowrap text-xs text-neutral-500">
                                        {{ $log->created_at?->translatedFormat('d M Y, H.i.s') }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            <x-avatar :name="$log->user?->name ?? 'Sistem'" size="xs" />
                                            <div>
                                                <p class="text-xs font-semibold text-neutral-900">{{ $log->user?->name ?? 'Sistem' }}</p>
                                                <p class="text-[10px] text-neutral-400">{{ $log->user?->role?->label() ?? 'System' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <p class="text-xs font-medium text-neutral-900">{{ $log->description }}</p>
                                    </td>
                                    <td class="py-3 pr-4 whitespace-nowrap text-xs text-neutral-500">
                                        <span class="rounded bg-neutral-100 px-1.5 py-0.5 font-mono text-[11px] text-neutral-700">{{ $log->module }}</span>
                                        @if ($log->action)
                                            <span class="text-neutral-400">&middot; {{ $log->action }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <x-badge :variant="$log->status->variant()">{{ $log->status->label() }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="mt-4 border-t border-neutral-100 pt-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-app-layout>

