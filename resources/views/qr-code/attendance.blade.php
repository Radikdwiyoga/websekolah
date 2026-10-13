{{-- Kartu QR Absensi Permanen: identitas siswa (NISN, nama, kelas) yang tidak berubah per sesi. --}}

<div id="qr-print-area" class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-neutral-900">Kartu QR Absensi</h2>
            <p class="mt-1 text-sm text-neutral-500">
                QR code permanen. Tunjukkan atau cetak kartu ini untuk discan oleh guru saat absensi.
            </p>
        </div>
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700">
            <x-icon name="qr-code" :size="6" />
        </span>
    </div>

    <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row">
        <div class="rounded-xl border border-neutral-200 bg-white p-3">
            <div class="h-44 w-44 text-neutral-900 [&>svg]:h-full [&>svg]:w-full">
                {!! $qrSvg !!}
            </div>
        </div>

        <dl class="w-full flex-1 space-y-3 text-sm">
            <div class="flex items-baseline justify-between gap-4 border-b border-neutral-100 pb-2">
                <dt class="text-neutral-500">Nama</dt>
                <dd class="font-semibold text-neutral-900">{{ $student->user?->name ?? '—' }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-4 border-b border-neutral-100 pb-2">
                <dt class="text-neutral-500">NISN</dt>
                <dd class="font-semibold text-neutral-900">{{ $student->nisn ?? '—' }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-4 border-b border-neutral-100 pb-2">
                <dt class="text-neutral-500">NIS</dt>
                <dd class="font-semibold text-neutral-900">{{ $student->nis ?? '—' }}</dd>
            </div>
            <div class="flex items-baseline justify-between gap-4">
                <dt class="text-neutral-500">Kelas</dt>
                <dd class="font-semibold text-neutral-900">{{ $student->schoolClass?->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <p class="text-xs text-neutral-400">QR code bersifat permanen dan tidak perlu digenerate ulang.</p>
        <div class="flex gap-2">
            <x-button :href="route('student.attendance.qr.image')" variant="ghost" size="sm" icon="download" download>Unduh QR</x-button>
            <x-button type="button" variant="secondary" size="sm" icon="file" data-qr-print>Cetak Kartu QR</x-button>
        </div>
    </div>
</div>

@push('styles')
    <style>
        @media print {
            body * { visibility: hidden; }
            #qr-print-area, #qr-print-area * { visibility: visible; }
            #qr-print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
@endpush
