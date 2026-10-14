<x-app-layout>
    <x-slot name="title">Tambah Guru</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Guru"
            description="Daftarkan guru baru. Akun login dibuat otomatis dari nama."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Guru', 'route' => 'admin.teachers.index'], ['label' => 'Tambah Guru']]"
        />

        <form method="POST" action="{{ route('admin.teachers.store') }}" data-submit-loading>
            @csrf
            @include('admin.teachers._form', ['teacher' => null])

            <div class="mt-4 flex items-center justify-end gap-3">
                <x-button :href="route('admin.teachers.index')" variant="outline">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Guru</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
