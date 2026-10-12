<x-app-layout>
    <x-slot name="title">Buat Tugas</x-slot>

    <div class="mx-auto w-full max-w-3xl space-y-6">
        <x-page-header
            title="Buat Tugas"
            description="Simpan sebagai draft, lalu tambahkan soal sebelum dipublikasikan ke siswa."
            :breadcrumb="[
                ['label' => 'Tugas', 'route' => 'teacher.assignments.index'],
                ['label' => 'Buat'],
            ]" />

        @include('assignments.teacher._form', [
            'action' => route('teacher.assignments.store'),
            'isEdit' => false,
            'assignment' => null,
            'pairs' => $pairs,
            'types' => $types,
            'defaultGradeWeight' => $defaultGradeWeight,
            'cancelUrl' => route('teacher.assignments.index'),
            'submitLabel' => 'Simpan Draft',
        ])
    </div>
</x-app-layout>
