<x-app-layout>
    <x-slot name="title">Pengaturan Sistem</x-slot>

    <div class="mt-6">
        <x-page-header
            title="Pengaturan Sistem"
            description="Konfigurasi global untuk website sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Pengaturan']]"
        />

        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Pengaturan</h4>
                <a href="javascript:void(0)" class="text-sm text-primary-600 hover:underline" id="btn-tambah-baru">
                    <x-icon name="plus" class="align-middle mr-1" /> Tambah pengaturan baru
                </a>
            </div>
            <div class="card-body p-0">
                <x-table>
                    <thead>
                        <tr>
                            <th class="text-left text-xs font-medium uppercase tracking-wide text-neutral-500 px-6 py-3">Pengaturan</th>
                            <th class="text-left text-xs font-medium uppercase tracking-wide text-neutral-500 px-6 py-3">Jenis</th>
                            <th class="text-left text-xs font-medium uppercase tracking-wide text-neutral-500 px-6 py-3">Nilai Saat Ini</th>
                            <th class="text-left text-xs font-medium uppercase tracking-wide text-neutral-500 px-6 py-3">Grup</th>
                            <th class="text-right text-xs font-medium uppercase tracking-wide text-neutral-500 px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($settings as $group => $items)
                            @foreach ($items as $setting)
                                <tr class="border-b transition-colors hover:bg-neutral-50">
                                    <td class="px-6 py-4 font-medium text-neutral-900">
                                        <span class="truncate max-w-xs">{{ $setting->key }}</span>
                                        @if ($setting->group)
                                            <span class="text-xs text-neutral-400 block">({{ $setting->group }})</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-medium text-neutral-500">{{ $setting->type->label() ?? $setting->type }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-mono text-sm text-neutral-700 whitespace-nowrap">{{ $setting->typedValue() }}</div>
                                        @if (is_string($setting->typedValue()))
                                            <div class="text-xs text-neutral-400 mt-1">{{ str_limit($setting->typedValue(), 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-neutral-500">{{ $group }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.settings.edit', $setting->key) }}" class="text-sm text-primary-600 hover:underline">
                                            <x-icon name="edit" class="align-middle mr-1" /> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </x-table>
            </div>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="mt-4">
                <x-button :href="route('admin.settings.edit', 'new')" variant="outline" icon="plus">
                    Buat Pengaturan Baru
                </x-button>
            </div>
        @endif
    </div>
</x-app-layout>