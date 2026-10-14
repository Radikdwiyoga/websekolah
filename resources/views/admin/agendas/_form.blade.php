@props(['agenda' => null])

@php
    $currentType = old('media_type', $agenda?->media_type?->value ?? \App\Enums\AgendaMediaType::Photo->value);
@endphp

<x-card>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="title" value="Judul Agenda" />
            <x-text-input id="title" name="title" :value="old('title', $agenda?->title)" class="mt-1 w-full" placeholder="Contoh: Peringatan HUT Kemerdekaan RI" required autofocus />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="description" value="Deskripsi" />
            <x-textarea id="description" name="description" rows="3" class="mt-1" placeholder="Keterangan singkat kegiatan.">{{ old('description', $agenda?->description) }}</x-textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="media_type" value="Jenis Media" />
            <x-select id="media_type" name="media_type" class="mt-1" data-conditional-select="#agenda-media-fields">
                @foreach (\App\Enums\AgendaMediaType::cases() as $type)
                    <option value="{{ $type->value }}" @selected($currentType === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-select>
            <x-input-error :messages="$errors->get('media_type')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="event_date" value="Tanggal Agenda" />
            <x-text-input id="event_date" name="event_date" type="date" :value="old('event_date', $agenda?->event_date?->format('Y-m-d'))" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('event_date')" class="mt-1" />
        </div>
    </div>

    {{-- Media --}}
    <div id="agenda-media-fields" class="mt-5 space-y-4 border-t border-neutral-100 pt-5">
        {{-- Foto --}}
        <div data-conditional-value="photo">
            <x-input-label for="media_photo" value="Unggah Foto" />
            <p class="mt-0.5 text-xs text-neutral-500">Format JPG, PNG, atau WEBP. Maksimal 4 MB.</p>

            @if ($agenda?->isPhoto() && $agenda->mediaUrl())
                <div class="mt-2 h-32 w-48 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-50">
                    <img src="{{ $agenda->mediaUrl() }}" alt="{{ $agenda->title }}" class="h-full w-full object-cover">
                </div>
            @endif

            <input id="media_photo" name="media" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-neutral-600 file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
            <x-input-error :messages="$errors->get('media')" class="mt-1" />
        </div>

        {{-- Video --}}
        <div data-conditional-value="video">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="video_url" value="Tautan Video (YouTube/Vimeo)" />
                    <p class="mt-0.5 text-xs text-neutral-500">Opsional jika mengunggah berkas video.</p>
                    <x-text-input id="video_url" name="video_url" type="url" :value="old('video_url', $agenda?->video_url)" class="mt-1 w-full" placeholder="https://www.youtube.com/watch?v=..." />
                    <x-input-error :messages="$errors->get('video_url')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="media_video" value="Atau Unggah Berkas Video" />
                    <p class="mt-0.5 text-xs text-neutral-500">Format MP4, WEBM, atau MOV. Maksimal 20 MB.</p>
                    <input id="media_video" name="media" type="file" accept="video/mp4,video/webm,video/quicktime" class="mt-2 block w-full text-sm text-neutral-600 file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                </div>
            </div>

            @if ($agenda?->isVideo() && $agenda->hasUploadedMedia())
                <div class="mt-3 h-40 w-64 overflow-hidden rounded-lg border border-neutral-200 bg-neutral-950">
                    <video src="{{ $agenda->mediaUrl() }}" class="h-full w-full object-cover" controls preload="metadata"></video>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-5 grid gap-4 border-t border-neutral-100 pt-5 sm:grid-cols-2">
        <div>
            <x-input-label for="sort_order" value="Urutan Tampil" />
            <p class="mt-0.5 text-xs text-neutral-500">Angka lebih kecil tampil lebih dahulu.</p>
            <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" :value="old('sort_order', $agenda?->sort_order ?? 0)" class="mt-1 w-full" />
            <x-input-error :messages="$errors->get('sort_order')" class="mt-1" />
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 text-sm font-medium text-neutral-700">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $agenda?->is_published ?? true)) class="rounded border-neutral-300 text-primary-700 focus:ring-primary-500/40">
                Tampilkan di halaman utama
            </label>
        </div>
    </div>
</x-card>
