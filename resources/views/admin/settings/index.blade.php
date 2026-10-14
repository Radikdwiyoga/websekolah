<x-app-layout>
    <x-slot name="title">Pengaturan Sistem</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Pengaturan Sistem"
            description="Konfigurasi global untuk website sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Pengaturan']]"
        />

        {{-- Profil Sekolah --}}
        <x-card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                        <x-icon name="school" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Profil Sekolah</h2>
                        <p class="mt-1 text-sm text-neutral-500">Kelola nama, logo, alamat, kontak, dan media sosial sekolah.</p>
                    </div>
                </div>
                <x-button :href="route('admin.settings.school.edit')" icon="edit">Kelola Profil Sekolah</x-button>
            </div>
        </x-card>

        {{-- Halaman Utama --}}
        <x-card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                        <x-icon name="dashboard" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Halaman Utama</h2>
                        <p class="mt-1 text-sm text-neutral-500">Atur teks hero, keunggulan, dan program yang tampil di beranda.</p>
                    </div>
                </div>
                <x-button :href="route('admin.settings.homepage.edit')" icon="edit">Kelola Halaman Utama</x-button>
            </div>
        </x-card>

        {{-- Agenda & Galeri --}}
        <x-card>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                        <x-icon name="photo" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Agenda &amp; Galeri</h2>
                        <p class="mt-1 text-sm text-neutral-500">Unggah foto atau video agenda sekolah sebagai media promosi.</p>
                    </div>
                </div>
                <x-button :href="route('admin.agendas.index')" icon="edit">Kelola Agenda</x-button>
            </div>
        </x-card>

        @forelse ($settings as $group => $items)
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">{{ $groupLabels[$group] ?? ucfirst($group) }}</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Pengaturan</th>
                                <th class="pb-2 pr-4">Jenis</th>
                                <th class="pb-2 pr-4">Nilai Saat Ini</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($items as $setting)
                                @php($value = $setting->typedValue())
                                <tr>
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-neutral-900">{{ $labels[$setting->key] ?? \Illuminate\Support\Str::headline($setting->key) }}</p>
                                        <code class="font-mono text-[11px] text-neutral-400">{{ $setting->key }}</code>
                                    </td>
                                    <td class="py-3 pr-4 whitespace-nowrap">
                                        <span class="text-xs font-medium text-neutral-500">{{ $setting->type->label() }}</span>
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if (is_bool($value))
                                            <x-badge :variant="$value ? 'success' : 'neutral'">{{ $value ? 'Aktif' : 'Nonaktif' }}</x-badge>
                                        @elseif (is_array($value))
                                            <code class="font-mono text-xs text-neutral-700">{{ \Illuminate\Support\Str::limit(json_encode($value), 60) }}</code>
                                        @elseif (filled($value))
                                            <span class="text-sm text-neutral-700">{{ \Illuminate\Support\Str::limit((string) $value, 60) }}</span>
                                        @else
                                            <span class="text-xs italic text-neutral-400">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right align-middle">
                                        <a href="{{ route('admin.settings.edit', $setting->key) }}" class="inline-flex items-center gap-1 text-sm font-medium text-primary-700 transition hover:text-primary-900">
                                            <x-icon name="edit" :size="4" /> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @empty
            <x-card>
                <x-empty-state icon="settings" title="Belum ada pengaturan">
                    Belum ada pengaturan yang tersimpan pada sistem.
                </x-empty-state>
            </x-card>
        @endforelse
    </div>
</x-app-layout>
