<x-app-layout>
    <x-slot name="title">Edit Mata Pelajaran</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Mata Pelajaran"
            description="Perbarui kode, nama, dan deskripsi mata pelajaran."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Mata Pelajaran', 'route' => 'admin.subjects.index'], ['label' => $subject->name]]"
        />

        <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
            @csrf
            @method('PUT')

            @include('admin.subjects._form', ['subject' => $subject])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.subjects.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
