<x-app-layout>
    <x-slot name="title">Tambah Orang Tua</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Orang Tua"
            description="Daftarkan wali murid baru. Akun login dibuat otomatis dari nama."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Orang Tua', 'route' => 'admin.guardians.index'], ['label' => 'Tambah Orang Tua']]"
        />

        <form method="POST" action="{{ route('admin.guardians.store') }}">
            @csrf
            @include('admin.guardians._form', ['guardian' => null])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.guardians.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Orang Tua</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
