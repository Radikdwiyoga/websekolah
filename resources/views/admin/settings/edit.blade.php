<x-app-layout>
    <x-slot name="title">Edit Pengaturan: {{ $name }}</x-slot>

    <div class="mt-6">
        <x-page-header
            title="Edit Pengaturan: {{ $name }}"
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Pengaturan', 'route' => 'admin.settings.index'], ['label' => 'Edit']]"
        />

        <div class="card">
            <div class="card-header flex flex-col sm:flex-row gap-2">
                <h4 class="mb-0 flex-1">{{ $name }}</h4>
                <div class="text-sm text-neutral-500 flex items-center gap-2">
                    <x-icon name="info" class="align-middle" />
                    <span>Key: <code class="font-mono text-xs px-1 bg-neutral-100 rounded">{{ $key }}</code></span>
                </div>
            </div>

            <div class="card-body p-6">
                <form method="POST" action="{{ route('admin.settings.update', $key) }}" enctype="multipart/form-data" data-submit-loading>
                    @csrf
                    @method('POST')

                    <input type="hidden" name="setting_type" value="{{ $type->value ?? 'string' }}">

                    <div class="mb-4">
                        <x-input-label for="value" value="Nilai" />
                        <input
                            type="text"
                            id="value"
                            name="value"
                            class="mt-1 block w-full rounded-md border border-neutral-300 bg-white px-3.5 py-2.5 text-sm text-neutral-950 shadow-sm focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                            value="{{ old('value', $current_value) }}"
                            placeholder="Masukkan nilai pengaturan..."
                            autocomplete="off"
                        >
                        <x-input-error :messages="$errors->get('value')" class="mt-1" />
                    </div>

                    <div class="mt-6">
                        <x-button type="submit" icon="check" data-submit-loading data-loading-text="Menyimpan…">Simpan Perubahan</x-button>
                        <a href="{{ route('admin.settings.index') }}" class="x-button variant-outline ml-2 icon="chevron-left">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>