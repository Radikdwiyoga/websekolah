<x-app-layout>
    <x-slot name="title">Edit Tahun Ajaran</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Tahun Ajaran"
            description="Perbarui nama, semester, dan periode tahun ajaran."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Tahun Ajaran', 'route' => 'admin.school-years.index'], ['label' => $schoolYear->name]]"
        />

        <form method="POST" action="{{ route('admin.school-years.update', $schoolYear) }}">
            @csrf
            @method('PUT')

            @include('admin.school-years._form', ['schoolYear' => $schoolYear])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.school-years.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
