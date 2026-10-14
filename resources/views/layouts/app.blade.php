<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Website Sekolah') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen overflow-x-hidden bg-neutral-100 font-sans text-neutral-900 antialiased">
    @php
        $user = auth()->user();
        $menu = $user ? config('navigation.'.$user->role->value, []) : [];
        $pageTitle = $title ?? 'Dashboard';
    @endphp

    <div class="min-h-full">
        {{-- Mobile drawer toggle (no-JS, peer checkbox) --}}
        <input type="checkbox" id="nav-drawer" class="peer sr-only" aria-hidden="true">

        {{-- Mobile top bar --}}
        <header class="sticky top-0 z-40 flex h-14 items-center justify-between border-b border-neutral-200 bg-white px-4 sm:hidden">
            <label for="nav-drawer" class="-ml-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-md text-neutral-600 transition hover:bg-neutral-100" aria-label="Buka menu navigasi">
                <x-icon name="menu" :size="6" />
            </label>
            <x-layout.brand />
            <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-neutral-500" aria-hidden="true">
                <x-icon name="bell" :size="5" />
            </span>
        </header>

        {{-- Mobile drawer backdrop --}}
        <div class="peer-checked:block fixed inset-0 z-40 hidden bg-neutral-950/40 sm:hidden" aria-hidden="true"></div>

        {{-- Mobile drawer --}}
        <aside class="peer-checked:translate-x-0 fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-white shadow-lg transition-transform duration-200 sm:hidden">
            <div class="flex h-14 items-center justify-between border-b border-neutral-200 px-4">
                <x-layout.brand />
                <label for="nav-drawer" class="-mr-2 flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-neutral-500 transition hover:bg-neutral-100" aria-label="Tutup menu navigasi">
                    <x-icon name="x" :size="5" />
                </label>
            </div>

            <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 py-3">
                <x-layout.nav-links :items="$menu" always-show-labels />
            </nav>

            <div class="border-t border-neutral-200 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button type="submit" variant="outline" size="sm" icon="logout" class="w-full">Keluar</x-button>
                </form>
            </div>
        </aside>

        {{-- Persistent sidebar: collapsed on tablet, expanded on desktop --}}
        <x-layout.sidebar :user="$user" />

        {{-- Main column --}}
        <div class="flex min-h-screen flex-col sm:pl-18 lg:pl-60">
            {{-- Desktop top bar --}}
            <header class="sticky top-0 z-20 hidden h-16 w-full items-center justify-between gap-4 border-b border-neutral-200 bg-white/80 px-6 backdrop-blur sm:flex lg:px-8">
                <div class="min-w-0">
                    <p class="text-lg font-semibold leading-tight text-neutral-900">{{ $pageTitle }}</p>
                </div>

                <div class="flex items-center gap-3">
                    @if ($user)
                        <x-badge variant="primary" dot>{{ $user->role->label() }}</x-badge>
                        <span class="hidden h-6 w-px bg-neutral-200 sm:block" aria-hidden="true"></span>
                        <x-avatar :name="$user->name" size="sm" />
                        <span class="hidden max-w-48 truncate text-sm font-medium text-neutral-700 lg:block">{{ $user->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-button type="submit" variant="ghost" size="sm" icon="logout">Keluar</x-button>
                        </form>
                    @endif
                </div>
            </header>

            <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col">
                <main class="flex-1 px-4 py-4 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <x-alert tone="success" class="mb-6">{{ session('status') }}</x-alert>
                    @endif

                    {{ $slot }}
                </main>

                <footer class="px-4 pb-8 sm:px-6 lg:px-8">
                    <p class="text-xs text-neutral-400">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Website Sekolah') }}
                        @if ($user)
                            &middot; {{ $user->role->label() }}
                        @endif
                    </p>
                </footer>
            </div>
        </div>
    </div>

    {{-- Flash toasts --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6">
        @foreach (['success' => session('success'), 'danger' => session('error'), 'info' => session('info')] as $type => $message)
            @if ($message)
                <x-toast :type="$type">{{ $message }}</x-toast>
            @endif
        @endforeach
    </div>

    @stack('scripts')
</body>
</html>