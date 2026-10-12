<x-app-layout>
    <x-slot name="title">Data Kelas</x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Data Kelas"
            description="Daftar kelas, tingkat, jurusan, wali kelas, dan jumlah siswa."
            :breadcrumb="[['label' => 'Dashboard', 'route' => 'dashboard.admin'], ['label' => 'Data Kelas']]"
        />

        <x-card>
            <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="w-full sm:w-72">
                    <x-input-label for="school_year_id" value="Tahun Ajaran" />
                    <x-select id="school_year_id" name="school_year_id" class="mt-1">
                        @foreach ($schoolYears as $year)
                            <option value="{{ $year->id }}" @selected($selectedYearId === $year->id)>
                                {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <x-button type="submit" variant="outline" icon="search">Tampilkan</x-button>
            </form>
        </x-card>

        <x-card>
            @if ($classes->isEmpty())
                <x-empty-state icon="school" title="Belum ada data kelas">
                    Belum ada kelas terdaftar pada tahun ajaran ini.
                </x-empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                                <th class="pb-2 pr-4">Nama Kelas</th>
                                <th class="pb-2 pr-4">Tingkat</th>
                                <th class="pb-2 pr-4">Jurusan</th>
                                <th class="pb-2 pr-4">Tahun Ajaran</th>
                                <th class="pb-2 pr-4">Wali Kelas</th>
                                <th class="pb-2 text-right">Jumlah Siswa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @foreach ($classes as $class)
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-neutral-900">
                                        {{ $class->name }}
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        Kelas {{ $class->level }}
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-600">
                                        {{ $class->major ?? 'Umum' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <x-badge :variant="$class->schoolYear?->is_active ? 'success' : 'neutral'">
                                            {{ $class->schoolYear?->name ?? '—' }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3 pr-4 text-neutral-700">
                                        {{ $class->homeroomTeacher?->user?->name ?? 'Belum ditentukan' }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-bold text-primary-700">
                                            {{ $class->students_count }} Siswa
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>

