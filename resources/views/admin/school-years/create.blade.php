<x-app-layout>
    <x-slot name="title">Tambah Tahun Ajaran</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Tahun Ajaran"
            description="Tambahkan periode tahun ajaran dan semester baru."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Tahun Ajaran', 'route' => 'admin.school-years.index'], ['label' => 'Tambah']]"
        />

        <form method="POST" action="{{ route('admin.school-years.store') }}">
            @csrf

            @include('admin.school-years._form', ['schoolYear' => null])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.school-years.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
