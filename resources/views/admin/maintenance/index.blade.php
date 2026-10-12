<x-app-layout>
    <x-slot name="title">Maintenance</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Maintenance Sistem"
            description="Kelola mode pemeliharaan website dan pembersihan cache sistem."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Maintenance']]"
        />

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Mode Maintenance --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Mode Pemeliharaan (Maintenance)</h2>
                        <p class="text-xs text-neutral-500">Saat aktif, pengunjung umum akan melihat pesan pemeliharaan pada beranda.</p>
                    </div>
                    @if ($maintenance)
                        <x-badge variant="warning" dot>Aktif (Maintenance)</x-badge>
                    @else
                        <x-badge variant="success" dot>Normal (Online)</x-badge>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.maintenance.toggle') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="message" value="Pesan Pemeliharaan" />
                        <x-textarea id="message" name="message" rows="3" class="mt-1 w-full">{{ $message }}</x-textarea>
                        <p class="mt-1 text-xs text-neutral-400">Pesan ini akan ditampilkan kepada pengunjung ketika maintenance aktif.</p>
                    </div>

                    <div class="pt-2">
                        @if ($maintenance)
                            <x-button type="submit" variant="danger" icon="wrench">
                                Matikan Mode Pemeliharaan
                            </x-button>
                        @else
                            <x-button type="submit" icon="wrench">
                                Aktifkan Mode Pemeliharaan
                            </x-button>
                        @endif
                    </div>
                </form>
            </x-card>

            {{-- Cache & Optimasi --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Pembersihan Cache & Optimalisasi</h2>
                <p class="text-xs text-neutral-500">Bersihkan cache tampilan blade dan cache aplikasi jika terdapat pembaruan yang belum tampil.</p>

                <div class="mt-6 space-y-4">
                    <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                        <h3 class="text-sm font-semibold text-neutral-900">Cache Aplikasi & View</h3>
                        <p class="mt-1 text-xs text-neutral-500">Menghapus file cache terkompilasi agar perubahan template dan pengaturan segera terbaca.</p>
                        <div class="mt-4">
                            <form method="POST" action="{{ route('admin.maintenance.clear-cache') }}">
                                @csrf
                                <x-button type="submit" variant="outline" size="sm" icon="trash">
                                    Bersihkan Cache Sekarang
                                </x-button>
                            </form>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>

