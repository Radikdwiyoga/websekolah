<x-app-layout>
    <x-slot name="title">Tambah Siswa</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Siswa"
            description="Daftarkan siswa baru. Akun login dibuat otomatis dengan username sama dengan NIS."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Siswa', 'route' => 'admin.students.index'], ['label' => 'Tambah Siswa']]"
        />

        <form method="POST" action="{{ route('admin.students.store') }}" data-submit-loading>
            @csrf
            @include('admin.students._form', ['student' => null, 'classes' => $classes, 'selectedClassId' => $selectedClassId])

            <div class="mt-4 flex items-center justify-end gap-3">
                <x-button :href="route('admin.students.index')" variant="outline">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Siswa</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
