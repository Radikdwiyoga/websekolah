@props(['children', 'selectedChildId' => null])

@if ($children->isNotEmpty())
    <form method="GET" class="w-full sm:w-auto">
        <x-select name="student" onchange="this.form.submit()" aria-label="Pilih anak" class="w-full sm:w-72">
            @foreach ($children as $child)
                <option value="{{ $child->id }}" @selected($child->id === $selectedChildId)>
                    {{ $child->user->name }} — {{ $child->schoolClass?->name ?? 'Tanpa kelas' }}
                </option>
            @endforeach
        </x-select>

        <noscript>
            <x-button type="submit" size="sm" class="mt-2">Tampilkan</x-button>
        </noscript>
    </form>
@endif
