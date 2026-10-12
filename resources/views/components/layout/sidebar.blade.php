@props(['user'])

@php
    $menu = config('navigation.'.$user->role->value, []);
@endphp

<aside {{ $attributes->merge(['class' => 'fixed inset-y-0 left-0 z-30 hidden w-[72px] flex-col border-r border-neutral-200 bg-white sm:flex lg:w-60']) }}>
    <div class="flex h-16 items-center border-b border-neutral-200 px-4 lg:px-6">
        <x-layout.brand responsive-name />
    </div>

    <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 pb-2 pt-2">
        <x-layout.nav-links :items="$menu" />
    </nav>

    <div class="border-t border-neutral-200 p-3">
        <div class="flex items-center gap-3 rounded-lg px-1 py-2 lg:px-2">
            <x-avatar :name="$user->name" size="sm" />
            <div class="hidden min-w-0 flex-1 lg:block">
                <p class="truncate text-sm font-semibold leading-tight text-neutral-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-neutral-500">{{ $user->role->label() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="lg:ml-auto">
                @csrf
                <button type="submit" class="hidden text-neutral-400 transition hover:text-danger-700 lg:flex" title="Keluar" aria-label="Keluar">
                    <x-icon name="logout" :size="5" />
                </button>
            </form>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
            @csrf
            <button type="submit" class="flex w-full items-center justify-center rounded-md px-2 py-2 text-neutral-400 transition hover:bg-neutral-100 hover:text-danger-700" title="Keluar" aria-label="Keluar">
                <x-icon name="logout" :size="5" />
            </button>
        </form>
    </div>
</aside>