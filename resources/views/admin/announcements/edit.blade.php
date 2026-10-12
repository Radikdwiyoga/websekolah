<x-app-layout>
    <x-slot name="title">Ubah Pengumuman</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Ubah Pengumuman"
            :description="'Perbarui pengumuman: '.$announcement->title"
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Kelola Pengumuman', 'route' => 'admin.announcements.index'], ['label' => 'Ubah']]"
        />

        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" enctype="multipart/form-data" data-submit-loading>
            @csrf
            @method('PUT')
            @include('admin.announcements._form', ['classes' => $classes, 'announcement' => $announcement])
        </form>
    </div>
</x-app-layout>