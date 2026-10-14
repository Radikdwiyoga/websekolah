<x-app-layout>
    <x-slot name="title">Profil Sekolah</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Profil Sekolah"
            description="Kelola identitas, logo, alamat, kontak, dan media sosial sekolah."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Pengaturan', 'route' => 'admin.settings.index'], ['label' => 'Profil Sekolah']]"
        />

        <form method="POST" action="{{ route('admin.settings.school.update') }}" enctype="multipart/form-data" class="space-y-6" data-submit-loading>
            @csrf
            @method('PUT')

            {{-- Identitas --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Identitas Sekolah</h2>
                <p class="mt-1 text-xs text-neutral-500">Informasi utama yang tampil di halaman publik dan sistem.</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="school_name" value="Nama Sekolah" />
                        <x-text-input id="school_name" name="school_name" :value="old('school_name', $school['name'])" class="mt-1 w-full" placeholder="Contoh: SMA Negeri 1 Nusantara" required autofocus />
                        <x-input-error :messages="$errors->get('school_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_npsn" value="NPSN" />
                        <x-text-input id="school_npsn" name="school_npsn" :value="old('school_npsn', $school['npsn'])" class="mt-1 w-full" placeholder="Contoh: 20123456" />
                        <x-input-error :messages="$errors->get('school_npsn')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_level" value="Jenjang" />
                        <x-text-input id="school_level" name="school_level" :value="old('school_level', $school['level'])" class="mt-1 w-full" placeholder="Contoh: SMA / SMP / SD" />
                        <x-input-error :messages="$errors->get('school_level')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_accreditation" value="Akreditasi" />
                        <x-text-input id="school_accreditation" name="school_accreditation" :value="old('school_accreditation', $school['accreditation'])" class="mt-1 w-full" placeholder="Contoh: A" />
                        <x-input-error :messages="$errors->get('school_accreditation')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="headmaster_name" value="Nama Kepala Sekolah" />
                        <x-text-input id="headmaster_name" name="headmaster_name" :value="old('headmaster_name', $school['headmaster'])" class="mt-1 w-full" placeholder="Contoh: Drs. Bambang Sutrisno, M.Pd." />
                        <x-input-error :messages="$errors->get('headmaster_name')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="school_motto" value="Motto / Slogan" />
                        <x-text-input id="school_motto" name="school_motto" :value="old('school_motto', $school['motto'])" class="mt-1 w-full" placeholder="Contoh: Aman, Nyaman, dan Bermutu" />
                        <x-input-error :messages="$errors->get('school_motto')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="school_description" value="Deskripsi Singkat" />
                        <x-textarea id="school_description" name="school_description" rows="4" class="mt-1" placeholder="Gambaran singkat tentang sekolah.">{{ old('school_description', $school['description']) }}</x-textarea>
                        <x-input-error :messages="$errors->get('school_description')" class="mt-1" />
                    </div>
                </div>
            </x-card>

            {{-- Logo --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Logo Sekolah</h2>
                <p class="mt-1 text-xs text-neutral-500">Format PNG, JPG, atau WEBP. Ukuran maksimal 2 MB. Disarankan gambar persegi.</p>

                <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50">
                        @if ($school['logo_url'])
                            <img src="{{ $school['logo_url'] }}" alt="Logo {{ $school['name'] }}" class="h-full w-full object-contain">
                        @else
                            <span class="flex h-full w-full items-center justify-center text-2xl font-bold text-primary-700">
                                {{ Str::upper(Str::substr($school['name'] ?? '', 0, 2)) }}
                            </span>
                        @endif
                    </div>

                    <div class="flex-1">
                        <x-input-label for="logo" value="Ganti Logo" />
                        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp"
                            class="mt-1 block w-full text-sm text-neutral-600 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                        <x-input-error :messages="$errors->get('logo')" class="mt-1" />

                        @if ($school['logo_url'])
                            <label class="mt-3 inline-flex items-center gap-2 text-sm text-neutral-600">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-neutral-300 text-danger-600 focus:ring-danger-500" @checked(old('remove_logo'))>
                                Hapus logo saat menyimpan
                            </label>
                        @endif
                    </div>
                </div>
            </x-card>

            {{-- Alamat --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Alamat</h2>
                <p class="mt-1 text-xs text-neutral-500">Alamat lengkap sekolah yang tampil di halaman kontak.</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-input-label for="school_address" value="Alamat Jalan" />
                        <x-text-input id="school_address" name="school_address" :value="old('school_address', $school['address'])" class="mt-1 w-full" placeholder="Contoh: Jl. Pendidikan No. 12" />
                        <x-input-error :messages="$errors->get('school_address')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_city" value="Kota / Kabupaten" />
                        <x-text-input id="school_city" name="school_city" :value="old('school_city', $school['city'])" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('school_city')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_province" value="Provinsi" />
                        <x-text-input id="school_province" name="school_province" :value="old('school_province', $school['province'])" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('school_province')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_postal_code" value="Kode Pos" />
                        <x-text-input id="school_postal_code" name="school_postal_code" :value="old('school_postal_code', $school['postal_code'])" class="mt-1 w-full" />
                        <x-input-error :messages="$errors->get('school_postal_code')" class="mt-1" />
                    </div>
                </div>
            </x-card>

            {{-- Kontak --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Kontak</h2>
                <p class="mt-1 text-xs text-neutral-500">Nomor telepon, email, dan website resmi sekolah.</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="school_phone" value="Telepon" />
                        <x-text-input id="school_phone" name="school_phone" :value="old('school_phone', $school['phone'])" class="mt-1 w-full" placeholder="Contoh: 021-1234567" />
                        <x-input-error :messages="$errors->get('school_phone')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_whatsapp" value="WhatsApp" />
                        <x-text-input id="school_whatsapp" name="school_whatsapp" :value="old('school_whatsapp', $school['whatsapp'])" class="mt-1 w-full" placeholder="Contoh: 081234567890" />
                        <x-input-error :messages="$errors->get('school_whatsapp')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_email" value="Email" />
                        <x-text-input id="school_email" name="school_email" type="email" :value="old('school_email', $school['email'])" class="mt-1 w-full" placeholder="Contoh: info@sekolah.sch.id" />
                        <x-input-error :messages="$errors->get('school_email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="school_website" value="Website" />
                        <x-text-input id="school_website" name="school_website" type="url" :value="old('school_website', $school['website'])" class="mt-1 w-full" placeholder="Contoh: https://sekolah.sch.id" />
                        <x-input-error :messages="$errors->get('school_website')" class="mt-1" />
                    </div>
                </div>
            </x-card>

            {{-- Media Sosial --}}
            <x-card>
                <h2 class="text-base font-semibold text-neutral-900">Media Sosial</h2>
                <p class="mt-1 text-xs text-neutral-500">Tautan lengkap akun media sosial sekolah (mulai dengan https://).</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'youtube' => 'YouTube',
                        'tiktok' => 'TikTok',
                        'twitter' => 'X (Twitter)',
                    ] as $platform => $platformLabel)
                        <div>
                            <x-input-label for="school_social_{{ $platform }}" :value="$platformLabel" />
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-neutral-400">
                                    <x-social-icon :name="$platform" size="5" />
                                </span>
                                <x-text-input id="school_social_{{ $platform }}" name="school_social_{{ $platform }}" type="url" :value="old('school_social_'.$platform, $school['social'][$platform] ?? null)" class="w-full pl-10" placeholder="https://..." />
                            </div>
                            <x-input-error :messages="$errors->get('school_social_'.$platform)" class="mt-1" />
                        </div>
                    @endforeach
                </div>
            </x-card>

            <div class="flex justify-end gap-3">
                <x-button :href="route('admin.settings.index')" variant="ghost">Batal</x-button>
                <x-button type="submit" icon="check">Simpan Profil Sekolah</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
