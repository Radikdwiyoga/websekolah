@props(['dark' => false, 'responsiveName' => false])

<a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ config('app.name', 'Website Sekolah') }}">
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-700 text-base font-bold text-white shadow-sm">
        WS
    </span>
    <span class="{{ $responsiveName ? 'hidden lg:inline' : '' }} text-lg font-semibold {{ $dark ? 'text-white' : 'text-primary-900' }}">
        {{ config('app.name', 'Website Sekolah') }}
    </span>
</a>