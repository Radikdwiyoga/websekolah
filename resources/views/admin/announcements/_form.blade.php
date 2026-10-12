@php
    $announcement ??= null;
    $targetType = old('target_type', $announcement?->target_type?->value ?? 'all');
    $statusValue = old('status', $announcement?->status?->value ?? 'draft');
    $publishedAt = old('published_at', $announcement?->published_at?->format('Y-m-d\TH:i') ?? '');
    $selectedClassIds = old('class_ids', $announcement?->targets->pluck('class_id')->all() ?? []);
@endphp

<div class="space-y-6">
    <x-card>
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="lg:col-span-2">
                <x-input-label for="title" value="Judul" />
                <x-text-input id="title" name="title" value="{{ old('title', $announcement?->title) }}" class="mt-1" required autofocus />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div class="lg:col-span-2">
                <x-input-label for="body" value="Isi Pengumuman" />
                <x-textarea id="body" name="body" rows="8" class="mt-1" required>{{ old('body', $announcement?->body) }}</x-textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-1" />
            </div>

            <div class="lg:col-span-2">
                <x-input-label value="Target" />
                <div class="mt-1 space-y-2">
                    @foreach (\App\Enums\AnnouncementTarget::cases() as $targetOption)
                        <label class="flex items-center gap-2 text-sm text-neutral-700">
                            <input
                                type="radio"
                                name="target_type"
                                value="{{ $targetOption->value }}"
                                @checked($targetType === $targetOption->value)
                                data-target-radio
                                class="h-4 w-4 border-neutral-300 text-primary-600 focus:ring-primary-500"
                            >
                            {{ $targetOption->label() }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('target_type')" class="mt-1" />
            </div>

            <div class="lg:col-span-2" {{ $targetType === 'class' ? '' : 'hidden' }} data-class-options>
                <x-input-label value="Kelas Tujuan" />
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($classes as $classOption)
                        <label class="flex items-center gap-2 rounded-lg border border-neutral-200 px-3 py-2 text-sm text-neutral-700">
                            <input
                                type="checkbox"
                                name="class_ids[]"
                                value="{{ $classOption->id }}"
                                @checked(in_array($classOption->id, $selectedClassIds, true))
                                class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                            >
                            {{ $classOption->name }}
                        </label>
                    @empty
                        <p class="text-sm text-neutral-500">Belum ada kelas pada tahun ajaran aktif.</p>
                    @endforelse
                </div>
                <x-input-error :messages="$errors->get('class_ids')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <x-select id="status" name="status" class="mt-1">
                    @foreach (\App\Enums\AnnouncementStatus::cases() as $statusOption)
                        <option value="{{ $statusOption->value }}" @selected($statusValue === $statusOption->value)>{{ $statusOption->label() }}</option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('status')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="published_at" value="Tanggal Publikasi" />
                <input
                    type="datetime-local"
                    id="published_at"
                    name="published_at"
                    value="{{ $publishedAt }}"
                    class="mt-1 block w-full rounded-md border border-neutral-300 bg-white px-3.5 py-2.5 text-sm text-neutral-950 shadow-sm focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                >
                <x-input-error :messages="$errors->get('published_at')" class="mt-1" />
            </div>

            <div class="lg:col-span-2">
                <x-input-label for="attachment" value="Lampiran (opsional)" />
                <input
                    type="file"
                    id="attachment"
                    name="attachment"
                    class="mt-1 block w-full text-sm text-neutral-700 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-800 hover:file:bg-primary-100"
                >
                <x-input-error :messages="$errors->get('attachment')" class="mt-1" />

                @if ($announcement?->attachment_path)
                    <p class="mt-1 text-xs text-neutral-500">
                        Lampiran saat ini:
                        <a href="{{ route('announcements.attachment', $announcement) }}" class="font-medium text-primary-700 hover:underline">
                            {{ basename($announcement->attachment_path) }}
                        </a>
                        <span class="italic">(unggah berkas baru untuk menggantinya)</span>
                    </p>
                @endif
            </div>

            <div class="space-y-3 lg:col-span-2">
                <label class="flex items-center gap-2 text-sm text-neutral-700">
                    <input type="hidden" name="is_important" value="0">
                    <input
                        type="checkbox"
                        name="is_important"
                        value="1"
                        @checked((bool) old('is_important', $announcement?->is_important))
                        class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                    >
                    Tandai penting (ditampilkan paling atas, dengan penanda khusus)
                </label>

                <label class="flex items-center gap-2 text-sm text-neutral-700">
                    <input type="hidden" name="send_whatsapp" value="0">
                    <input
                        type="checkbox"
                        name="send_whatsapp"
                        value="1"
                        @checked((bool) old('send_whatsapp', $announcement?->send_whatsapp))
                        class="h-4 w-4 rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                    >
                    Kirim pemberitahuan WhatsApp ke target saat dipublikasikan
                </label>
            </div>
        </div>
    </x-card>

    <div class="flex items-center justify-end gap-3">
        <x-button :href="route('admin.announcements.index')" variant="outline">Batal</x-button>
        <x-button type="submit" icon="check" data-submit-loading data-loading-text="Menyimpan…">Simpan Pengumuman</x-button>
    </div>
</div>

<script>
    document.querySelectorAll('[data-target-radio]').forEach((radio) => {
        radio.addEventListener('change', () => {
            const options = document.querySelector('[data-class-options]');
            if (options) {
                options.hidden = radio.value !== 'class';
            }
        });
    });
</script>