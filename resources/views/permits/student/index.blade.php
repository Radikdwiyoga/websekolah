<x-app-layout>
    <x-slot name="title">Izin & Sakit</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Izin & Sakit"
            description="Ajukan izin atau sakit dan pantau status persetujuannya."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.student'], ['label' => 'Izin & Sakit']]"
        />

        @if (! $student)
            <x-empty-state icon="guardian" title="Data siswa tidak ditemukan">
                Akun Anda belum terhubung ke data siswa. Hubungi administrator sekolah.
            </x-empty-state>
        @else
            <x-alert tone="info" title="Cara mengajukan">
                Pilih tanggal, jenis (izin/sakit), dan tuliskan alasan. Setelah dikirim, guru kelas akan meninjau pengajuan Anda.
            </x-alert>

            <x-permit-form :route="route('student.permits.store')" />

            <x-card>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-neutral-900">Riwayat Pengajuan</h2>
                    <x-icon name="permit" :size="5" class="text-neutral-400" />
                </div>

                <x-permit-table :permits="$permits" destroy-route="student.permits.destroy" />
            </x-card>
        @endif
    </div>
</x-app-layout>
