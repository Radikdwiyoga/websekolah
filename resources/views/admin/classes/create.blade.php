<x-app-layout>
    <x-slot name="title">Tambah Kelas</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Kelas"
            description="Buat kelas baru pada tahun ajaran tertentu."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Kelas', 'route' => 'admin.classes.index'], ['label' => 'Tambah Kelas']]"
        />

        <form method="POST" action="{{ route('admin.classes.store') }}">
            @csrf

            @include('admin.classes._form', [
                'schoolClass' => null,
                'schoolYears' => $schoolYears,
                'teachers' => $teachers,
                'selectedYearId' => $selectedYearId,
            ])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.classes.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Kelas</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
