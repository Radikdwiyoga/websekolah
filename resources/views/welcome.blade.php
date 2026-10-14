<x-site-layout>
    <x-slot name="title">{{ $school['name'] }} — Website Resmi</x-slot>

    @if ($maintenance)
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            <x-alert tone="warning" title="Mode Pemeliharaan">{{ $maintenanceMessage }}</x-alert>
        </div>
    @endif

    {{-- Hero --}}
    <section id="beranda" class="scroll-mt-16 bg-primary-50">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-20">
            <div class="max-w-xl">
                <x-badge variant="primary" dot>Selamat datang di {{ $school['name'] }}</x-badge>
                <h1 class="mt-4 text-h1 text-primary-900 lg:text-display">
                    {{ $home['hero_title'] }}
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-neutral-600">
                    {{ $home['hero_subtitle'] }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button href="#tentang" size="lg" icon="book">Lihat Profil Sekolah</x-button>
                    <x-button href="#kontak" size="lg" variant="outline" icon="bell">Hubungi Kami</x-button>
                </div>
            </div>

            {{-- Illustration --}}
            <div class="relative hidden lg:block">
                <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-primary-500/20 blur-2xl" aria-hidden="true"></div>
                <div class="absolute -bottom-10 -left-10 h-44 w-44 rounded-full bg-success-500/10 blur-2xl" aria-hidden="true"></div>

                <div class="relative rounded-2xl border border-neutral-200 bg-white p-7 shadow-lg">
                    <div class="flex items-center justify-between gap-4">
                        <x-layout.school-logo :school="$school" box-class="h-12 w-12" text-class="text-lg" />
                        <x-badge variant="success" dot>Aktif</x-badge>
                    </div>

                    <p class="mt-5 text-lg font-bold text-neutral-900">{{ $school['name'] }}</p>
                    <p class="text-sm text-neutral-500">Portal absensi digital, tugas, dan penilaian.</p>

                    <ul class="mt-6 space-y-3">
                        <li class="flex items-center gap-3 rounded-lg bg-neutral-50 px-3 py-2.5 text-sm font-medium text-neutral-700">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-success-100 text-success-700"><x-icon name="attendance" :size="4" /></span>
                            Kehadiran tercatat otomatis
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-neutral-50 px-3 py-2.5 text-sm font-medium text-neutral-700">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-primary-100 text-primary-700"><x-icon name="task" :size="4" /></span>
                            Tugas dan nilai terpusat
                        </li>
                        <li class="flex items-center gap-3 rounded-lg bg-neutral-50 px-3 py-2.5 text-sm font-medium text-neutral-700">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-info-100 text-info-700"><x-icon name="bell" :size="4" /></span>
                            Notifikasi ke orang tua
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Keunggulan Sekolah --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <x-badge variant="primary">Keunggulan</x-badge>
                <h2 class="mt-3 text-h2 text-neutral-950">{{ $home['advantages_heading'] }}</h2>
                @if ($home['advantages_description'])
                    <p class="mt-3 text-neutral-600">
                        {{ $home['advantages_description'] }}
                    </p>
                @endif
            </div>

            @if (! empty($home['advantages']))
                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($home['advantages'] as $item)
                        <x-feature-card :icon="$item['icon']" :tone="$item['tone']" :title="$item['title']">
                            {{ $item['description'] }}
                        </x-feature-card>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Profil / Tentang --}}
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <section id="tentang" class="scroll-mt-16 grid gap-10 lg:grid-cols-2">
            <div class="max-w-xl">
                <x-badge variant="info">Tentang Sekolah</x-badge>
                <h2 class="mt-3 text-h2 text-neutral-950">{{ $school['name'] }}</h2>
                <p class="mt-4 leading-relaxed text-neutral-600">
                    {{ $school['description'] ?: $school['name'].' adalah sekolah yang berkomitmen menciptakan pengalaman belajar yang aman, nyaman, dan bermakna. Melalui sistem informasi sekolah ini, orang tua dapat memantau kehadiran, tugas, dan nilai anak secara transparan dan real-time.' }}
                </p>
                @if ($school['headmaster'])
                    <p class="mt-6 text-sm text-neutral-500">
                        Kepala Sekolah: <span class="font-semibold text-neutral-900">{{ $school['headmaster'] }}</span>
                    </p>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @if ($school['address'])
                    <x-card>
                        <p class="text-sm font-medium text-neutral-500">Alamat</p>
                        <p class="mt-1 text-sm font-semibold leading-relaxed text-neutral-900">{{ $school['address'] }}</p>
                    </x-card>
                @endif
                @if ($school['phone'])
                    <x-card>
                        <p class="text-sm font-medium text-neutral-500">Telepon</p>
                        <p class="mt-1 text-sm font-semibold text-neutral-900">{{ $school['phone'] }}</p>
                    </x-card>
                @endif
                @if ($school['email'])
                    <x-card>
                        <p class="text-sm font-medium text-neutral-500">Email</p>
                        <p class="mt-1 break-all text-sm font-semibold text-neutral-900">{{ $school['email'] }}</p>
                    </x-card>
                @endif
                @if ($school['npsn'])
                    <x-card>
                        <p class="text-sm font-medium text-neutral-500">NPSN</p>
                        <p class="mt-1 text-sm font-semibold text-neutral-900">{{ $school['npsn'] }}</p>
                    </x-card>
                @endif
            </div>
        </section>
    </div>

    {{-- Program Sekolah --}}
    @php
        $toneClasses = [
            'primary' => 'bg-primary-100 text-primary-700',
            'success' => 'bg-success-100 text-success-700',
            'warning' => 'bg-warning-100 text-warning-700',
            'danger' => 'bg-danger-100 text-danger-700',
            'info' => 'bg-info-100 text-info-700',
        ];
    @endphp

    <section id="program" class="scroll-mt-16 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <x-badge variant="success">Program</x-badge>
                <h2 class="mt-3 text-h2 text-neutral-950">{{ $home['programs_heading'] }}</h2>
                @if ($home['programs_description'])
                    <p class="mt-3 text-neutral-600">{{ $home['programs_description'] }}</p>
                @endif
            </div>

            @if (! empty($home['programs']))
                <div class="mt-10 grid gap-5 md:grid-cols-2">
                    @foreach ($home['programs'] as $item)
                        <x-card class="flex items-start gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $toneClasses[$item['tone']] ?? $toneClasses['primary'] }}">
                                <x-icon :name="$item['icon']" :size="5" />
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-neutral-900">{{ $item['title'] }}</h3>
                                @if ($item['description'])
                                    <p class="mt-1.5 text-sm leading-relaxed text-neutral-600">{{ $item['description'] }}</p>
                                @endif
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Agenda & Galeri Sekolah --}}
    <section id="agenda" class="scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <x-badge variant="info">Agenda &amp; Galeri</x-badge>
                <h2 class="mt-3 text-h2 text-neutral-950">Kegiatan dan Momen Sekolah</h2>
                <p class="mt-3 text-neutral-600">Dokumentasi foto dan video sebagai media promosi kegiatan sekolah.</p>
            </div>

            @if ($agendas->isEmpty())
                <div class="mt-10">
                    <x-empty-state icon="photo" title="Belum ada agenda">
                        Dokumentasi kegiatan sekolah akan tampil di sini setelah admin mengunggahnya.
                    </x-empty-state>
                </div>
            @else
                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($agendas as $agenda)
                        <x-card :padding="false" class="flex flex-col overflow-hidden">
                            <div class="aspect-video w-full overflow-hidden bg-neutral-100">
                                @if ($agenda->isPhoto() && $agenda->mediaUrl())
                                    <img src="{{ $agenda->mediaUrl() }}" alt="{{ $agenda->title }}" loading="lazy" class="h-full w-full object-cover">
                                @elseif ($agenda->isVideo() && $agenda->videoEmbedUrl())
                                    <iframe src="{{ $agenda->videoEmbedUrl() }}" title="{{ $agenda->title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="h-full w-full"></iframe>
                                @elseif ($agenda->isVideo() && $agenda->hasUploadedMedia())
                                    <video src="{{ $agenda->mediaUrl() }}" controls preload="metadata" class="h-full w-full object-cover"></video>
                                @elseif ($agenda->isVideo() && $agenda->video_url)
                                    <video src="{{ $agenda->video_url }}" controls preload="metadata" class="h-full w-full object-cover"></video>
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-neutral-400">
                                        <x-icon :name="$agenda->isVideo() ? 'play' : 'photo'" :size="8" />
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                @if ($agenda->event_date)
                                    <p class="text-xs font-medium uppercase tracking-wide text-primary-700">{{ $agenda->event_date->translatedFormat('d F Y') }}</p>
                                @endif
                                <h3 class="mt-1 text-base font-semibold text-neutral-900">{{ $agenda->title }}</h3>
                                @if ($agenda->description)
                                    <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $agenda->description }}</p>
                                @endif
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Informasi / Pengumuman --}}
    <section id="berita" class="scroll-mt-16">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <x-badge variant="warning">Berita &amp; Pengumuman</x-badge>
                    <h2 class="mt-3 text-h2 text-neutral-950">Informasi Terbaru</h2>
                </div>
            </div>

            @if ($announcements->isEmpty())
                <div class="mt-10">
                    <x-empty-state icon="announcement" title="Belum ada pengumuman">
                        Informasi sekolah akan tampil di sini setelah admin mempublikasikannya.
                    </x-empty-state>
                </div>
            @else
                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($announcements as $announcement)
                        <x-announcement-card :announcement="$announcement" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Statistik --}}
    <section class="bg-primary-50">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <h2 class="text-h2 text-primary-900">Sekolah Kami dalam Angka</h2>
                <p class="mt-3 text-neutral-600">Komunitas yang terus bertumbuh setiap tahun.</p>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-5 lg:grid-cols-4">
                <x-stat-card label="Siswa Aktif" :value="number_format($stats['students'])" icon="students" tone="primary" />
                <x-stat-card label="Guru" :value="number_format($stats['teachers'])" icon="teacher" tone="success" />
                <x-stat-card label="Rombongan Belajar" :value="number_format($stats['classes'])" icon="school" tone="info" />
                <x-stat-card label="Pengumuman" :value="number_format($announcements->count())" icon="announcement" tone="warning" />
            </div>
        </div>
    </section>

    {{-- CTA Login --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="relative overflow-hidden rounded-2xl bg-primary-900 px-6 py-12 text-center text-white shadow-lg sm:px-12">
            <div class="absolute -left-16 -top-16 h-56 w-56 rounded-full bg-primary-700/40 blur-3xl" aria-hidden="true"></div>
            <div class="absolute -bottom-20 -right-16 h-64 w-64 rounded-full bg-primary-600/30 blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto max-w-2xl">
                <h2 class="text-h2">Siap Mengelola Aktivitas Sekolah?</h2>
                <p class="mt-3 text-primary-100">
                    Guru mencatat kehadiran, siswa mengikuti pembelajaran, dan orang tua memantau perkembangan — semua dalam satu tempat.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <x-button href="{{ route('login') }}" size="lg">Masuk ke Sistem</x-button>
                </div>
            </div>
        </div>
    </section>
</x-site-layout>