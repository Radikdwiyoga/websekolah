@props(['itemClass' => ''])

@php
    $links = [
        'beranda' => 'Beranda',
        'tentang' => 'Tentang',
        'program' => 'Program',
        'agenda' => 'Agenda',
        'berita' => 'Berita',
        'kontak' => 'Kontak',
    ];
@endphp

<nav {{ $attributes->merge(['class' => '']) }} aria-label="Navigasi utama">
    @foreach ($links as $id => $label)
        <a href="#{{ $id }}" class="{{ $itemClass }}">{{ $label }}</a>
    @endforeach
</nav>