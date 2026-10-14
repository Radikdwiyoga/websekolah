<x-app-layout>
    <x-slot name="title">Tambah Mata Pelajaran</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Tambah Mata Pelajaran"
            description="Daftarkan mata pelajaran baru beserta kodenya."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Mata Pelajaran', 'route' => 'admin.subjects.index'], ['label' => 'Tambah']]"
        />

        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf

            @include('admin.subjects._form', ['subject' => null])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.subjects.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
