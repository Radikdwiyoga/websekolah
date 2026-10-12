<x-app-layout>
    <x-slot name="title">Tugas</x-slot>

    <div class="space-y-6">
        <x-page-header title="Tugas" description="Buat tugas, pantau pengumpulan, dan nilai pekerjaan siswa.">
            <x-slot name="actions">
                <x-button :href="route('teacher.assignments.create')" icon="plus">Buat Tugas</x-button>
            </x-slot>
        </x-page-header>

        @if (! $teacher)
            <x-empty-state icon="profile" title="Profil guru belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @else
            <x-card>
                <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <x-input-label for="class_id" value="Kelas" />
                        <x-select id="class_id" name="class_id" class="mt-1">
                            <option value="">Semua kelas</option>
                            @foreach ($classes as $classOption)
                                <option value="{{ $classOption->id }}" @selected($activeClassId === $classOption->id)>{{ $classOption->name }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="flex-1">
                        <x-input-label for="status" value="Status" />
                        <x-select id="status" name="status" class="mt-1">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}" @selected($activeStatus === $statusOption)>{{ $statusOption->label() }}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <x-button type="submit" variant="outline" icon="search" class="sm:w-auto">Terapkan</x-button>
                </form>
            </x-card>

            @if ($assignments->isEmpty())
                <x-empty-state icon="task" title="Belum ada tugas">
                    Buat tugas pertama untuk kelas yang Anda ampu.
                </x-empty-state>
            @else
                <div class="space-y-3">
                    @foreach ($assignments as $assignment)
                        <x-card>
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-badge :variant="$assignment->status->variant()" dot>{{ $assignment->status->label() }}</x-badge>
                                        <x-badge variant="neutral">{{ $assignment->type->label() }}</x-badge>
                                    </div>

                                    <h2 class="mt-2 text-base font-semibold text-neutral-900">
                                        <a href="{{ route('teacher.assignments.show', $assignment) }}" class="transition hover:text-primary-700">
                                            {{ $assignment->title }}
                                        </a>
                                    </h2>

                                    <p class="mt-1 text-sm text-neutral-500">
                                        {{ $assignment->subject?->name }} &middot; Kelas {{ $assignment->schoolClass?->name }}
                                        @if ($assignment->deadline)
                                            &middot; Tenggat {{ $assignment->deadline->translatedFormat('d M Y H.i') }}
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-neutral-900">{{ $assignment->submissions_count }} pengumpulan</p>
                                        <p class="text-xs text-neutral-500">
                                            {{ $assignment->pending_count }} perlu dinilai &middot; {{ $assignment->graded_count }} selesai
                                        </p>
                                    </div>

                                    <x-button :href="route('teacher.assignments.show', $assignment)" variant="outline" size="sm" icon="eye">Detail</x-button>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
