<x-app-layout>
    <x-slot name="title">Edit Kelas</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Kelas"
            description="Perbarui nama, tingkat, kapasitas, dan wali kelas."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Kelas', 'route' => 'admin.classes.index'], ['label' => $class->name]]"
        />

        <form method="POST" action="{{ route('admin.classes.update', $class) }}">
            @csrf
            @method('PUT')

            @include('admin.classes._form', [
                'schoolClass' => $class,
                'schoolYears' => $schoolYears,
                'teachers' => $teachers,
            ])

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.classes.show', $class)" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
