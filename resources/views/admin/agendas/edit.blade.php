<x-app-layout>
    <x-slot name="title">Ubah Agenda</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Ubah Agenda"
            description="Perbarui detail dan media agenda sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Agenda & Galeri', 'route' => 'admin.agendas.index'], ['label' => $agenda->title]]"
        />

        <form method="POST" action="{{ route('admin.agendas.update', $agenda) }}" enctype="multipart/form-data" class="space-y-6" data-submit-loading>
            @csrf
            @method('PUT')

            @include('admin.agendas._form', ['agenda' => $agenda])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.agendas.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
