@props(['school', 'dark' => false, 'boxClass' => 'h-10 w-10', 'textClass' => 'text-base'])

@if (! empty($school['logo_url']))
    <img
        src="{{ $school['logo_url'] }}"
        alt="Logo {{ $school['name'] }}"
        class="{{ $boxClass }} shrink-0 rounded-lg object-contain p-0.5 shadow-sm {{ $dark ? 'bg-white/10' : 'bg-white ring-1 ring-neutral-200' }}"
    >
@else
    <span class="{{ $boxClass }} flex shrink-0 items-center justify-center rounded-lg font-bold shadow-sm {{ $dark ? 'bg-white/10 text-white' : 'bg-primary-700 text-white' }} {{ $textClass }}">
        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($school['name'], 0, 2)) }}
    </span>
@endif
