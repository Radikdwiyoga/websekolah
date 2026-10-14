<x-app-layout>
    <x-slot name="title">Data Orang Tua</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Data Orang Tua"
            description="Daftar wali murid terdaftar, nomor WhatsApp notifikasi, dan siswa terkait."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Orang Tua']]"
        >
            <x-slot name="actions">
                <x-button :href="route('admin.guardians.create')" icon="plus">Tambah Orang Tua</x-button>
            </x-slot>
        </x-page-header>

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <x-input-label for="search" value="Cari Orang Tua" />
                    <x-text-input id="search" name="search" :value="$search" placeholder="Nama, nomor telepon, atau nama siswa..." class="mt-1 w-full" />
                </div>
                <x-button type="submit" variant="outline" icon="search">Cari</x-button>
                @if ($search)
                    <x-button :href="route('admin.guardians.index')" variant="ghost">Reset</x-button>
                @endif
            </form>
        </x-card>

        <x-card>
            @if ($guardians->isEmpty())
                <x-empty-state icon="guardian" title="Belum ada data orang tua">
                    Tambahkan wali murid untuk mulai menghubungkan mereka dengan siswa.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Nama Wali</th>
                                <th class="pb-2 pr-4">No. WhatsApp / HP</th>
                                <th class="pb-2 pr-4">Siswa Terhubung</th>
                                <th class="pb-2 pr-4">Pekerjaan</th>
                                <th class="pb-2 pr-4">Status Akun</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($guardians as $guardian)
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$guardian->user?->name" />
                                            <div>
                                                <p class="font-medium text-neutral-900">{{ $guardian->user?->name }}</p>
                                                <p class="text-xs text-neutral-500">{{ $guardian->user?->email ?? $guardian->user?->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 font-mono text-xs text-neutral-600">
                                        {{ $guardian->phone ?? $guardian->user?->phone ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if ($guardian->students->isEmpty())
                                            <span class="text-xs text-neutral-400">Belum terhubung siswa</span>
                                        @else
                                            <div class="space-y-1">
                                                @foreach ($guardian->students as $student)
                                                    <div class="flex items-center gap-1.5 text-xs text-neutral-700">
                                                        <span class="font-semibold">{{ $student->user?->name }}</span>
                                                        <span class="text-neutral-400">({{ $student->schoolClass?->name ?? '—' }})</span>
                                                        @if ($student->pivot->is_primary)
                                                            <x-badge variant="success">Wali Utama</x-badge>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-xs text-neutral-600">{{ $guardian->occupation ?? '—' }}</td>
                                    <td class="py-3 pr-4">
                                        @if ($guardian->user?->is_active)
                                            <x-badge variant="success" dot>Aktif</x-badge>
                                        @else
                                            <x-badge variant="neutral">Nonaktif</x-badge>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button :href="route('admin.guardians.edit', $guardian)" variant="ghost" size="sm" icon="edit">Edit</x-button>
                                            <form method="POST" action="{{ route('admin.guardians.toggle-status', $guardian) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-button type="submit" variant="ghost" size="sm">
                                                    {{ $guardian->user?->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($guardians->hasPages())
                    <div class="mt-4 border-t border-neutral-100 pt-4">
                        {{ $guardians->links() }}
                    </div>
                @endif
            @endif
        </x-card>
    </div>
</x-app-layout>
