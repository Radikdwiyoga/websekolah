<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Website Sekolah') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="flex min-h-screen flex-col overflow-x-hidden bg-neutral-50 font-sans text-neutral-950 antialiased">
    @php
        $settings = app(\App\Services\SettingService::class);
        $school = [
            'name' => $settings->get('school_name', config('app.name', 'Website Sekolah')),
            'npsn' => $settings->get('school_npsn'),
            'address' => $settings->get('school_address'),
            'phone' => $settings->get('school_phone'),
            'email' => $settings->get('school_email'),
            'headmaster' => $settings->get('headmaster_name'),
        ];
    @endphp

    {{-- Mobile drawer toggle --}}
    <input type="checkbox" id="site-drawer" class="peer sr-only" aria-hidden="true">

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-neutral-200 bg-white/85 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="#beranda" class="flex min-w-0 items-center gap-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-700 text-base font-bold text-white shadow-sm">WS</span>
                <span class="truncate text-lg font-semibold text-primary-900">{{ $school['name'] }}</span>
            </a>

            <x-layout.site-nav-links
                class="hidden items-center gap-1 lg:flex"
                item-class="block rounded-md px-3 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 hover:text-primary-800"
            />

            <div class="flex shrink-0 items-center gap-2">
                <x-button href="{{ route('login') }}" size="sm" icon="profile">Masuk</x-button>
                <label for="site-drawer" class="-mr-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-md text-neutral-600 transition hover:bg-neutral-100 lg:hidden" aria-label="Buka menu navigasi">
                    <x-icon name="menu" :size="6" />
                </label>
            </div>
        </div>
    </header>

    {{-- Mobile drawer backdrop --}}
    <div class="peer-checked:block fixed inset-0 z-40 hidden bg-neutral-950/40 lg:hidden" aria-hidden="true"></div>

    {{-- Mobile drawer --}}
    <aside class="peer-checked:translate-x-0 fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-white shadow-lg transition-transform duration-200 lg:hidden">
        <div class="flex h-16 items-center justify-between border-b border-neutral-200 px-4">
            <a href="#beranda" class="flex min-w-0 items-center gap-2.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary-700 text-sm font-bold text-white">WS</span>
                <span class="truncate text-base font-semibold text-primary-900">{{ $school['name'] }}</span>
            </a>
            <label for="site-drawer" class="-mr-2 flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-neutral-500 transition hover:bg-neutral-100" aria-label="Tutup menu navigasi">
                <x-icon name="x" :size="5" />
            </label>
        </div>

        <div class="scrollbar-thin flex-1 overflow-y-auto p-4">
            <x-layout.site-nav-links
                class="space-y-1"
                item-class="block rounded-md px-3 py-3 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-100 hover:text-primary-800"
            />
        </div>

        <div class="border-t border-neutral-200 p-4">
            <x-button href="{{ route('login') }}" class="w-full">Masuk</x-button>
        </div>
    </aside>

    {{-- Page content --}}
    <main class="flex-1">{{ $slot }}</main>

    {{-- Footer --}}
    <footer id="kontak" class="scroll-mt-16 bg-primary-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 text-base font-bold">WS</span>
                        <p class="text-lg font-semibold">{{ $school['name'] }}</p>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-primary-100/80">
                        Bersama membangun generasi masa depan melalui pendidikan yang aman, nyaman, dan bermutu.
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-primary-100/60">Kontak Sekolah</h3>
                    <ul class="mt-4 space-y-3 text-sm text-primary-100/90">
                        @if ($school['address'])
                            <li class="flex gap-3">
                                <x-icon name="school" class="mt-0.5 h-5 w-5 shrink-0 text-primary-100/50" />
                                <span>{{ $school['address'] }}</span>
                            </li>
                        @endif
                        @if ($school['phone'])
                            <li class="flex gap-3">
                                <x-icon name="bell" class="mt-0.5 h-5 w-5 shrink-0 text-primary-100/50" />
                                <span>{{ $school['phone'] }}</span>
                            </li>
                        @endif
                        @if ($school['email'])
                            <li class="flex gap-3">
                                <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-primary-100/50" />
                                <span class="break-all">{{ $school['email'] }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-primary-100/60">Informasi</h3>
                    <ul class="mt-4 space-y-3 text-sm text-primary-100/90">
                        @if ($school['npsn'])
                            <li>NPSN: <span class="font-medium text-white">{{ $school['npsn'] }}</span></li>
                        @endif
                        @if ($school['headmaster'])
                            <li>Kepala Sekolah: <span class="font-medium text-white">{{ $school['headmaster'] }}</span></li>
                        @endif
                        <li>Jam belajar: 07.00 – 16.00 WIB</li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col items-start justify-between gap-3 border-t border-white/10 pt-6 text-sm text-primary-100/70 sm:flex-row sm:items-center">
                <p>&copy; {{ date('Y') }} {{ $school['name'] }}. Hak cipta dilindungi.</p>
                <a href="{{ route('login') }}" class="font-medium transition hover:text-white">Masuk untuk Guru &amp; Staf</a>
            </div>
        </div>
    </footer>
</body>
</html>