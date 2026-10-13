@props([
    'route',
    'children' => null,
    'selectedChildId' => null,
    'title' => 'Ajukan Izin / Sakit',
    'description' => null,
])

<x-card>
    <div class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700">
            <x-icon name="permit" :size="5" />
        </span>
        <div class="min-w-0">
            <h2 class="text-base font-semibold text-neutral-900">{{ $title }}</h2>
            @if ($description)
                <p class="mt-0.5 text-sm text-neutral-500">{{ $description }}</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ $route }}" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf

        @if ($children && $children->isNotEmpty())
            <div>
                <x-input-label for="permit-student" value="Untuk Anak" />
                <x-select id="permit-student" name="student_id" class="mt-1" required>
                    @foreach ($children as $child)
                        <option value="{{ $child->id }}" @selected((int) old('student_id', $selectedChildId) === $child->id)>
                            {{ $child->user?->name ?? 'Siswa' }} — {{ $child->schoolClass?->name ?? 'Tanpa kelas' }}
                        </option>
                    @endforeach
                </x-select>
                <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="permit-date" value="Tanggal" />
                <x-text-input id="permit-date" name="date" type="date" :value="old('date', now()->toDateString())" class="mt-1 w-full" required />
                <x-input-error :messages="$errors->get('date')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="permit-type" value="Jenis" />
                <x-select id="permit-type" name="type" class="mt-1" required>
                    <option value="izin" @selected(old('type') === 'izin')>Izin</option>
                    <option value="sakit" @selected(old('type') === 'sakit')>Sakit</option>
                </x-select>
                <x-input-error :messages="$errors->get('type')" class="mt-1" />
            </div>
        </div>

        <div>
            <x-input-label for="permit-reason" value="Alasan" />
            <x-textarea id="permit-reason" name="reason" rows="3" class="mt-1" required placeholder="Jelaskan alasan izin atau sakit...">{{ old('reason') }}</x-textarea>
            <x-input-error :messages="$errors->get('reason')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="permit-attachment" value="Lampiran (opsional)" />
            <input
                id="permit-attachment"
                name="attachment"
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                class="mt-1 block w-full text-sm text-neutral-600 file:mr-3 file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
            />
            <p class="mt-1 text-xs text-neutral-400">JPG, PNG, atau PDF maksimal 8 MB. Contoh: surat dokter.</p>
            <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
        </div>

        <div class="flex justify-end">
            <x-button type="submit" icon="send">Kirim Pengajuan</x-button>
        </div>
    </form>
</x-card>
