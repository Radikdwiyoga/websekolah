<x-app-layout>
    <x-slot name="title">Izin & Sakit</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Izin & Sakit"
            description="Ajukan izin atau sakit untuk anak Anda dan pantau persetujuannya."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.parent'], ['label' => 'Izin & Sakit']]"
        >
            <x-slot name="actions">
                <x-child-selector :children="$children" :selected-child-id="$selectedChildId" />
            </x-slot>
        </x-page-header>

        @if (! $selectedChild)
            <x-empty-state icon="guardian" title="Belum ada anak terhubung">
                Akun Anda belum terhubung ke data siswa. Hubungi administrator sekolah.
            </x-empty-state>
        @else
            <x-alert tone="info" title="Cara mengajukan">
                Pilih anak, tanggal, dan jenis (izin/sakit), lalu tuliskan alasan. Guru kelas akan meninjau pengajuan tersebut.
            </x-alert>

            <x-permit-form
                :route="route('parent.permits.store')"
                :children="$children"
                :selected-child-id="$selectedChildId"
                title="Ajukan Izin / Sakit untuk Anak"
            />

            <x-card>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">
                        Riwayat Pengajuan — {{ $selectedChild->user?->name ?? 'Anak' }}
                    </h2>
                    <x-icon name="permit" :size="5" class="text-neutral-400" />
                </div>

                <x-permit-table :permits="$permits" destroy-route="parent.permits.destroy" />
            </x-card>
        @endif
    </div>
</x-app-layout>
