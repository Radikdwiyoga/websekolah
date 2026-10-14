<x-app-layout>
    <x-slot name="title">Tambah Agenda</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Agenda"
            description="Unggah foto atau video kegiatan sekolah sebagai media promosi."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Agenda & Galeri', 'route' => 'admin.agendas.index'], ['label' => 'Tambah']]"
        />

        <form method="POST" action="{{ route('admin.agendas.store') }}" enctype="multipart/form-data" class="space-y-6" data-submit-loading>
            @csrf

            @include('admin.agendas._form', ['agenda' => null])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.agendas.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Agenda</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
