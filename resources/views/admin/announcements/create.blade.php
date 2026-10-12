<x-app-layout>
    <x-slot name="title">Buat Pengumuman</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Buat Pengumuman"
            description="Terbitkan informasi untuk seluruh warga sekolah atau target tertentu."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Kelola Pengumuman', 'route' => 'admin.announcements.index'], ['label' => 'Buat Pengumuman']]"
        />

        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" data-submit-loading>
            @csrf
            @include('admin.announcements._form', ['classes' => $classes])
        </form>
    </div>
</x-app-layout>