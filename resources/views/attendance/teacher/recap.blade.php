<x-app-layout>
    <x-slot name="title">Rekap Absensi</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Rekap Absensi"
            description="Rekapitulasi kehadiran siswa pada kelas yang Anda ampu per periode."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.teacher'], ['label' => 'Rekap Absensi']]"
        />

        @include('attendance.partials.recap', ['recap' => $recap, 'exportRoute' => 'teacher.attendance.recap.export'])
    </div>
</x-app-layout>
