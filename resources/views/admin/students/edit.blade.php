<x-app-layout>
    <x-slot name="title">Edit Siswa</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Edit Siswa"
            description="Perbarui identitas, kelas, dan akun login siswa."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Siswa', 'route' => 'admin.students.index'], ['label' => 'Edit Siswa']]"
        >
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.students.toggle-status', $student) }}">
                    @csrf
                    @method('PATCH')
                    <x-button
                        type="submit"
                        :variant="$student->is_active ? 'outline' : 'secondary'"
                        :icon="$student->is_active ? 'x-circle' : 'check'"
                    >
                        {{ $student->is_active ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa' }}
                    </x-button>
                </form>
            </x-slot>
        </x-page-header>

        <form method="POST" action="{{ route('admin.students.update', $student) }}" data-submit-loading>
            @csrf
            @method('PUT')
            @include('admin.students._form', ['classes' => $classes, 'selectedClassId' => $selectedClassId])

            <div class="mt-4 flex items-center justify-end gap-3">
                <x-button :href="route('admin.students.index')" variant="outline">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Perubahan</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
