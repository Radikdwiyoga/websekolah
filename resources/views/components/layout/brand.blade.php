@props(['dark' => false, 'responsiveName' => false])

@php
    $school = app(\App\Services\SchoolProfileService::class)->all();
@endphp

<a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $school['name'] }}">
    <x-layout.school-logo :school="$school" :dark="$dark" box-class="h-10 w-10" text-class="text-base" />
    <span class="{{ $responsiveName ? 'hidden lg:inline' : '' }} text-lg font-semibold {{ $dark ? 'text-white' : 'text-primary-900' }}">
        {{ $school['name'] }}
    </span>
</a>
