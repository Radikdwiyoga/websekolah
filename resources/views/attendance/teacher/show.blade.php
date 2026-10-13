<x-app-layout>
    <x-slot name="title">Absensi {{ $class->name }}</x-slot>

    <div class="space-y-6">
        <x-page-header
            :title="'Absensi '.$class->name"
            :description="'Kelas '.$class->level.' &middot; '.$students->count().' siswa aktif'"
            :breadcrumb="[
                ['label' => 'Input Absensi', 'route' => 'teacher.attendance.index'],
                ['label' => $class->name],
            ]" />

        {{-- Sesi hari ini --}}
        <x-card class="{{ $session ? '' : 'border-warning-200 bg-warning-50/40' }}">
            @if ($session)
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $session->isOpen() ? 'bg-success-100 text-success-700' : 'bg-neutral-100 text-neutral-500' }}">
                            <x-icon name="attendance" :size="6" />
                        </span>

                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-neutral-900">Sesi Absensi Hari Ini</h2>
                                <x-badge :variant="$session->isOpen() ? 'success' : 'neutral'" dot>
                                    {{ $session->status->label() }}
                                </x-badge>
                            </div>

                            <p class="mt-1 text-sm text-neutral-500">
                                {{ $session->open_at }} – {{ $session->close_at }}
                                @if ($session->late_after)
                                    &middot; Terlambat setelah {{ $session->late_after }}
                                @endif
                            </p>

                            @if ($session->isOpen())
                                <p class="mt-1 text-xs text-neutral-400">
                                    {{ $remainingMinutes > 0 ? 'Sisa '.$remainingMinutes.' menit sebelum jendela berakhir.' : 'Jendela absensi telah berakhir, tutup sesi atau perbaiki kehadiran.' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($session->isOpen())
                        <form method="POST" action="{{ route('teacher.attendance.close', $session) }}" onsubmit="return confirm('Tutup sesi absensi? Siswa yang belum absen akan ditandai alfa.')">
                            @csrf
                            <x-button variant="danger" type="submit" icon="x-circle" class="shrink-0">Tutup Sesi</x-button>
                        </form>
                    @endif
                </div>
            @else
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-warning-100 text-warning-700">
                            <x-icon name="calendar" :size="6" />
                        </span>

                        <div>
                            <h2 class="text-base font-semibold text-neutral-900">Sesi Absensi Belum Dibuka</h2>
                            <p class="mt-1 text-sm text-neutral-500">
                                Buka sesi untuk hari ini. Jendela absensi
                                {{ $settings->get('attendance_open_time', '07:00') }} –
                                {{ $settings->get('attendance_close_time', '09:00') }},
                                terlambat setelah {{ $settings->get('attendance_late_after', '07:15') }}.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('teacher.attendance.open', $class) }}">
                        @csrf
                        <x-button type="submit" icon="plus" class="shrink-0">Buka Sesi Absensi</x-button>
                    </form>
                </div>
            @endif
        </x-card>

        {{-- Scan QR code siswa --}}
        @if (! $students->isEmpty())
            <x-card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Scan QR Absensi</h2>
                        <p class="mt-1 text-sm text-neutral-500">Pindai kartu QR siswa dengan kamera, unggah gambar QR, atau pakai alat pemindai, lalu tandai kehadirannya.</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                        <x-icon name="qr-code" :size="5" />
                    </span>
                </div>

                @if ($session && $session->isOpen())
                    <form method="POST" action="{{ route('teacher.attendance.scan-qr', $session) }}" class="mt-4 space-y-4" data-qr-scanner>
                        @csrf

                        <div>
                            <x-input-label for="qr_payload" value="Hasil scan QR code" />
                            <textarea id="qr_payload" name="qr_payload" rows="2" required data-qr-input
                                class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Arahkan kamera ke QR code, unggah gambar, atau tempel/skan data QR di sini">{{ old('qr_payload') }}</textarea>
                            <x-input-error :messages="$errors->get('qr_payload')" class="mt-1" />
                        </div>

                        <div data-qr-video-wrapper class="hidden overflow-hidden rounded-lg border border-neutral-200">
                            <video data-qr-video class="h-64 w-full bg-neutral-950 object-cover" autoplay muted playsinline></video>
                        </div>

                        <p class="text-xs text-neutral-500" data-qr-status></p>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-button type="button" variant="secondary" icon="search" data-qr-start>Aktifkan Kamera</x-button>

                            <label class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-md border border-neutral-200 bg-white px-4 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-neutral-100">
                                <x-icon name="upload" :size="5" />
                                Unggah Gambar QR
                                <input type="file" accept="image/*" data-qr-file class="sr-only">
                            </label>

                            <x-button type="submit" icon="check">Tandai Kehadiran</x-button>
                        </div>
                    </form>
                @else
                    <x-alert tone="warning" class="mt-4">
                        {{ $session
                            ? 'Sesi absensi hari ini sudah ditutup. Scan QR hanya tersedia saat sesi sedang berjalan.'
                            : 'Buka sesi absensi untuk hari ini terlebih dahulu, lalu scan QR siswa di sini.' }}
                    </x-alert>
                @endif
            </x-card>
        @endif

        {{-- Daftar kehadiran --}}
        @if ($session)
            <form method="POST" action="{{ route('teacher.attendance.update', $session) }}">
                @csrf

                <x-card>
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-neutral-900">Daftar Kehadiran Siswa</h2>
                        <x-icon name="students" :size="5" class="text-neutral-400" />
                    </div>

                    @if ($students->isEmpty())
                        <div class="mt-4">
                            <x-empty-state icon="students" title="Belum ada siswa aktif di kelas ini" />
                        </div>
                    @else
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full lg:min-w-[680px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-neutral-200 text-xs uppercase tracking-wide text-neutral-500">
                                        <th class="py-2.5 pr-3 font-semibold">No</th>
                                        <th class="py-2.5 pr-3 font-semibold">Nama</th>
                                        <th class="py-2.5 pr-3 font-semibold">Status Saat Ini</th>
                                        <th class="py-2.5 pr-3 font-semibold">Perbarui Status</th>
                                        <th class="py-2.5 font-semibold">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100">
                                    @foreach ($students as $index => $student)
                                        @php
                                            $record = $recordsByStudent->get($student->id);
                                            $current = $record?->status ?? \App\Enums\AttendanceStatus::BelumAbsen;
                                        @endphp

                                        <tr>
                                            <td class="py-3 pr-3 text-neutral-500">{{ $index + 1 }}</td>
                                            <td class="py-3 pr-3">
                                                <p class="font-medium text-neutral-900">{{ $student->user?->name ?? 'Tanpa nama' }}</p>
                                                <p class="text-xs text-neutral-500">NIS {{ $student->nis ?? '—' }}</p>
                                            </td>
                                            <td class="py-3 pr-3">
                                                <x-badge :variant="$current->variant()">{{ $current->label() }}</x-badge>
                                            </td>
                                            <td class="py-3 pr-3">
                                                <x-select name="status[{{ $student->id }}]">
                                                    @foreach ($statuses as $statusOption)
                                                        <option value="{{ $statusOption->value }}" @selected($current === $statusOption)>
                                                            {{ $statusOption->label() }}
                                                        </option>
                                                    @endforeach
                                                </x-select>
                                            </td>
                                            <td class="py-3">
                                                <x-text-input
                                                    name="note[{{ $student->id }}]"
                                                    value="{{ $record?->note }}"
                                                    placeholder="Opsional"
                                                    class="max-w-[160px]" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 flex flex-col items-start justify-between gap-3 border-t border-neutral-100 pt-4 sm:flex-row sm:items-center">
                            <p class="text-xs text-neutral-500">Perubahan status dicatat dalam log audit.</p>
                            <x-button type="submit" icon="check" data-submit-loading>Simpan Kehadiran</x-button>
                        </div>
                    @endif
                </x-card>
            </form>
        @endif
    </div>
</x-app-layout>