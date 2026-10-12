<x-app-layout>
    <x-slot name="title">Ubah Tugas</x-slot>

    <div class="mx-auto w-full max-w-3xl space-y-6">
        <x-page-header
            title="Ubah Tugas"
            description="Perbarui informasi tugas. Soal diatur pada halaman detail tugas."
            :breadcrumb="[
                ['label' => 'Tugas', 'route' => 'teacher.assignments.index'],
                ['label' => $assignment->title, 'route' => 'teacher.assignments.show'],
                ['label' => 'Ubah'],
            ]" />

        @include('assignments.teacher._form', [
            'action' => route('teacher.assignments.update', $assignment),
            'isEdit' => true,
            'assignment' => $assignment,
            'pairs' => $pairs,
            'defaultGradeWeight' => $defaultGradeWeight,
            'cancelUrl' => route('teacher.assignments.show', $assignment),
            'submitLabel' => 'Simpan Perubahan',
        ])
    </div>
</x-app-layout>
