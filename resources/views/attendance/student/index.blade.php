<x-app-layout>
    <x-slot name="title">Kehadiran Saya</x-slot>

    <div class="mx-auto w-full max-w-xl space-y-6">
        <x-page-header title="Absensi Hari Ini" description="Lakukan absensi di jendela waktu yang telah ditentukan guru." />

        @if (! $student)
            <x-empty-state icon="profile" title="Profil siswa belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @elseif (! $session)
            <x-empty-state icon="calendar" title="Sesi absensi belum dibuka">
                Guru Anda belum membuka sesi absensi untuk hari ini. Silakan cek kembali secara berkala.
            </x-empty-state>
        @elseif ($record)
            @php
                $isPresent = $record->status->isPresent();
                $isLate = $record->status === \App\Enums\AttendanceStatus::Terlambat;

                $iconTone = match (true) {
                    $isLate => 'bg-warning-100 text-warning-700',
                    $isPresent => 'bg-success-100 text-success-700',
                    $record->status === \App\Enums\AttendanceStatus::Alfa => 'bg-danger-100 text-danger-700',
                    default => 'bg-info-100 text-info-700',
                };

                $headline = match (true) {
                    $isPresent && ! $isLate => 'Anda sudah hadir',
                    $isLate => 'Anda tercatat terlambat',
                    default => 'Status kehadiran: '.$record->status->label(),
                };
            @endphp

            <x-card>
                <div class="flex flex-col items-center gap-4 py-2 text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full {{ $iconTone }}">
                        <x-icon :name="$isPresent ? 'check' : 'info'" :size="8" />
                    </span>

                    <div>
                        <h2 class="text-xl font-bold text-neutral-950">{{ $headline }}</h2>

                        @if ($record->status->isPresent())
                            <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-xs text-neutral-500">Waktu</p>
                                    <p class="mt-0.5 font-semibold text-neutral-900">{{ $record->checked_in_at?->format('H.i') ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500">Status</p>
                                    <p class="mt-0.5 font-semibold {{ $isLate ? 'text-warning-700' : 'text-success-700' }}">{{ $record->status->label() }}</p>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-neutral-500">
                                {{ match ($record->status) {
                                    \App\Enums\AttendanceStatus::Izin => 'Kehadiran Anda dicatat izin. Hubungi guru jika ada perbedaan.',
                                    \App\Enums\AttendanceStatus::Sakit => 'Kehadiran Anda dicatat sakit. Hubungi guru jika ada perbedaan.',
                                    \App\Enums\AttendanceStatus::Alfa => 'Anda tercatat tidak hadir tanpa keterangan pada sesi ini. Hubungi guru jika ada perbedaan.',
                                    default => 'Status kehadiran Anda belum tercatat pada sesi ini.',
                                } }}
                            </p>

                            @if ($record->note)
                                <p class="mt-3 rounded-md bg-neutral-100 px-3 py-2 text-xs text-neutral-600">Keterangan: {{ $record->note }}</p>
                            @endif
                        @endif
                    </div>
                </div>

                <p class="mt-4 border-t border-neutral-100 pt-4 text-center text-xs text-neutral-500">
                    Kelas {{ $session->schoolClass?->name }} &middot; Sesi {{ $session->open_at }} – {{ $session->close_at }}
                </p>
            </x-card>
        @else
            <x-card class="border-primary-100 bg-primary-50/40">
                <div class="flex flex-col items-center gap-4 py-2 text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                        <x-icon name="attendance" :size="8" />
                    </span>

                    <div>
                        <h2 class="text-xl font-bold text-neutral-950">Sesi absensi sedang berjalan</h2>
                        <p class="mt-1 text-sm text-neutral-600">Kelas {{ $session->schoolClass?->name }}</p>
                        <p class="mt-0.5 text-sm text-neutral-600">{{ $session->open_at }} – {{ $session->close_at }}</p>
                    </div>

                    <form method="POST" action="{{ route('student.attendance.check-in', $session) }}" class="w-full sm:w-auto">
                        @csrf
                        <x-button type="submit" size="lg" icon="check" class="w-full sm:w-auto" data-submit-loading>
                            Absen Sekarang
                        </x-button>
                    </form>
                </div>
            </x-card>
        @endif
    </div>
</x-app-layout>