<x-app-layout>
    <x-slot name="title">Izin & Sakit</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Izin & Sakit"
            description="Tinjau pengajuan izin atau sakit dari siswa kelas yang Anda ampu."
            :breadcrumb="[['label' => 'Dashboard', 'route' => auth()->user()->isAdmin() ? 'dashboard.admin' : 'dashboard.teacher'], ['label' => 'Izin & Sakit']]"
        />

        @if ($pendingCount > 0)
            <x-alert tone="warning" title="{{ $pendingCount }} pengajuan menunggu tinjauan">
                Setujui atau tolak pengajuan agar status kehadiran siswa tercatat dengan benar.
            </x-alert>
        @else
            <x-alert tone="success" title="Tidak ada pengajuan yang menunggu">
                Semua pengajuan izin/sakit sudah ditinjau.
            </x-alert>
        @endif

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full sm:w-64">
                    <x-input-label for="status" value="Filter Status" />
                    <x-select id="status" name="status" class="mt-1">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                        @endforeach
                    </x-select>
                </div>
                <x-button type="submit" variant="outline" icon="search">Terapkan</x-button>
                @if ($status !== '')
                    <x-button :href="route('teacher.permits.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            <x-permit-table :permits="$permits" show-student reviewable />

            @if ($permits->hasPages())
                <div class="mt-4 border-t border-neutral-100 pt-4">
                    {{ $permits->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
