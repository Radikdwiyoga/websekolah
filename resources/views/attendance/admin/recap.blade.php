<x-app-layout>
    <x-slot name="title">Rekap Absensi</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Rekap Absensi"
            description="Rekapitulasi kehadiran siswa seluruh kelas per periode."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Rekap Absensi']]"
        />

        @include('attendance.partials.recap', ['recap' => $recap, 'exportRoute' => 'admin.attendance.recap.export', 'backupRoute' => 'admin.backups.index'])
    </div>
</x-app-layout>
