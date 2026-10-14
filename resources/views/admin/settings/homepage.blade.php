<x-app-layout>
    <x-slot name="title">Halaman Utama</x-slot>

    @php
        $advantages = old('advantages', $home['advantages']);
        $programs = old('programs', $home['programs']);
        $advantages = is_array($advantages) ? array_values($advantages) : [];
        $programs = is_array($programs) ? array_values($programs) : [];
    @endphp

    <div class="space-y-6">
        <x-page-header
            title="Halaman Utama"
            description="Kelola teks hero, keunggulan, dan program yang tampil di halaman beranda."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Pengaturan', 'route' => 'admin.settings.index'], ['label' => 'Halaman Utama']]"
        />

        <form method="POST" action="{{ route('admin.settings.homepage.update') }}" class="space-y-6" data-submit-loading>
            @csrf
            @method('PUT')

            {{-- Hero --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Bagian Pembuka (Hero)</h2>
                <p class="mt-1 text-xs text-neutral-500">Judul dan subjudul besar yang tampil paling atas di beranda.</p>

                <div class="mt-5 space-y-4">
                    <div>
                        <x-input-label for="hero_title" value="Judul" />
                        <x-text-input id="hero_title" name="hero_title" :value="old('hero_title', $home['hero_title'])" class="mt-1 w-full" placeholder="Contoh: Bersama Membangun Generasi Masa Depan" />
                        <x-input-error :messages="$errors->get('hero_title')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="hero_subtitle" value="Subjudul" />
                        <x-textarea id="hero_subtitle" name="hero_subtitle" rows="2" class="mt-1">{{ old('hero_subtitle', $home['hero_subtitle']) }}</x-textarea>
                        <x-input-error :messages="$errors->get('hero_subtitle')" class="mt-1" />
                    </div>
                </div>
            </x-card>

            {{-- Keunggulan --}}
            <x-card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Keunggulan Sekolah</h2>
                        <p class="mt-1 text-xs text-neutral-500">Kartu alasan mengapa orang tua memilih sekolah ini.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="advantages_heading" value="Judul Bagian" />
                        <x-text-input id="advantages_heading" name="advantages_heading" :value="old('advantages_heading', $home['advantages_heading'])" class="mt-1 w-full" placeholder="Contoh: Mengapa Memilih Sekolah Kami?" />
                        <x-input-error :messages="$errors->get('advantages_heading')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="advantages_description" value="Deskripsi Bagian" />
                        <x-text-input id="advantages_description" name="advantages_description" :value="old('advantages_description', $home['advantages_description'])" class="mt-1 w-full" placeholder="Ringkasan singkat bagian keunggulan." />
                        <x-input-error :messages="$errors->get('advantages_description')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-5 border-t border-neutral-100 pt-5"
                    data-repeater
                    data-repeater-next="{{ count($advantages) }}">
                    <div data-repeater-rows class="space-y-4">
                        @forelse ($advantages as $index => $row)
                            @include('admin.settings._feature-row', ['type' => 'advantages', 'index' => $index, 'row' => $row])
                        @empty
                            @include('admin.settings._feature-row', ['type' => 'advantages', 'index' => 0, 'row' => null])
                        @endforelse
                    </div>

                    <template data-repeater-template>
                        @include('admin.settings._feature-row', ['type' => 'advantages', 'index' => '__INDEX__', 'row' => null])
                    </template>

                    <button type="button" data-repeater-add class="mt-4 inline-flex items-center gap-1.5 rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-neutral-50">
                        <x-icon name="plus" :size="4" />
                        Tambah Keunggulan
                    </button>
                </div>
            </x-card>

            {{-- Program --}}
            <x-card>
                <div>
                    <h2 class="text-base font-semibold text-neutral-900">Program &amp; Layanan</h2>
                    <p class="mt-1 text-xs text-neutral-500">Program unggulan yang ditawarkan sekolah.</p>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="programs_heading" value="Judul Bagian" />
                        <x-text-input id="programs_heading" name="programs_heading" :value="old('programs_heading', $home['programs_heading'])" class="mt-1 w-full" placeholder="Contoh: Program dan Layanan Sekolah" />
                        <x-input-error :messages="$errors->get('programs_heading')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="programs_description" value="Deskripsi Bagian" />
                        <x-text-input id="programs_description" name="programs_description" :value="old('programs_description', $home['programs_description'])" class="mt-1 w-full" placeholder="Opsional." />
                        <x-input-error :messages="$errors->get('programs_description')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-5 border-t border-neutral-100 pt-5"
                    data-repeater
                    data-repeater-next="{{ count($programs) }}">
                    <div data-repeater-rows class="space-y-4">
                        @forelse ($programs as $index => $row)
                            @include('admin.settings._feature-row', ['type' => 'programs', 'index' => $index, 'row' => $row])
                        @empty
                            @include('admin.settings._feature-row', ['type' => 'programs', 'index' => 0, 'row' => null])
                        @endforelse
                    </div>

                    <template data-repeater-template>
                        @include('admin.settings._feature-row', ['type' => 'programs', 'index' => '__INDEX__', 'row' => null])
                    </template>

                    <button type="button" data-repeater-add class="mt-4 inline-flex items-center gap-1.5 rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-neutral-50">
                        <x-icon name="plus" :size="4" />
                        Tambah Program
                    </button>
                </div>
            </x-card>

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.settings.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Halaman Utama</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
