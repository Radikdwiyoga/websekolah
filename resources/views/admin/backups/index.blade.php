<x-app-layout>
    <x-slot name="title">Backup &amp; Ekspor</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Backup & Ekspor"
            description="Unduh cadangan data absensi dan tugas/penilaian dalam format Excel atau PDF."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Backup & Ekspor']]"
        />

        @if (session('success'))
            <x-alert tone="success">{{ session('success') }}</x-alert>
        @endif

        {{-- Backup Absensi --}}
        <x-card>
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                    <x-icon name="calendar" class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-semibold text-neutral-900">Backup Absensi</h2>
                    <p class="mt-1 text-sm text-neutral-500">
                        Cadangan absensi harian, mingguan, atau bulanan. Data <strong>izin &amp; sakit</strong> (surat izin/sakit)
                        otomatis ikut tersimpan dalam berkas ini.
                    </p>

                    <form method="GET" action="{{ route('admin.backups.attendance') }}" class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="attendance-period" value="Periode" />
                            <x-select id="attendance-period" name="period" class="mt-1">
                                @foreach ($periods as $period)
                                    <option value="{{ $period->value }}" @selected($period->value === request('period', 'monthly'))>{{ $period->label() }}</option>
                                @endforeach
                            </x-select>
                            <p class="mt-1 text-xs text-neutral-400">Harian: tanggal pilihan; Mingguan: pekan; Bulanan: bulan.</p>
                        </div>

                        <div>
                            <x-input-label for="attendance-date" value="Tanggal Acuan" />
                            <x-text-input id="attendance-date" name="date" type="date" :value="request('date', $defaultDate)" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="attendance-class" value="Kelas" />
                            <x-select id="attendance-class" name="class_id" class="mt-1">
                                <option value="">Semua Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected((int) request('class_id') === $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:col-span-3">
                            <x-button type="submit" name="format" value="excel" icon="download">Unduh Excel</x-button>
                            <x-button type="submit" name="format" value="pdf" variant="outline" icon="download">Unduh PDF</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-card>

        {{-- Backup Tugas & Penilaian --}}
        <x-card>
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                    <x-icon name="task" class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-semibold text-neutral-900">Backup Tugas &amp; Penilaian</h2>
                    <p class="mt-1 text-sm text-neutral-500">
                        Ringkasan tugas (jumlah pengumpulan &amp; penilaian) beserta rekap nilai per siswa. Kosongkan rentang tanggal
                        untuk mengambil seluruh data.
                    </p>

                    <form method="GET" action="{{ route('admin.backups.assignments') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <x-input-label for="assignment-class" value="Kelas" />
                            <x-select id="assignment-class" name="class_id" class="mt-1">
                                <option value="">Semua Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected((int) request('class_id') === $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <x-input-label for="assignment-subject" value="Mata Pelajaran" />
                            <x-select id="assignment-subject" name="subject_id" class="mt-1">
                                <option value="">Semua Mata Pelajaran</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <x-input-label for="assignment-start" value="Dari Tanggal" />
                            <x-text-input id="assignment-start" name="start_date" type="date" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="assignment-end" value="Sampai Tanggal" />
                            <x-text-input id="assignment-end" name="end_date" type="date" class="mt-1" />
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:col-span-2 lg:col-span-4">
                            <x-button type="submit" name="format" value="excel" icon="download">Unduh Excel</x-button>
                            <x-button type="submit" name="format" value="pdf" variant="outline" icon="download">Unduh PDF</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
