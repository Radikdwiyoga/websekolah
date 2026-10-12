<x-app-layout>
    <x-slot name="title">Tugas</x-slot>

    <div class="space-y-6">
        <x-page-header title="Tugas" description="Daftar tugas kelas Anda beserta status pengerjaannya." />

        @if (! $student)
            <x-empty-state icon="profile" title="Profil siswa belum tersedia">
                Hubungi administrator untuk melengkapi data profil Anda.
            </x-empty-state>
        @elseif ($assignments->isEmpty())
            <x-empty-state icon="task" title="Belum ada tugas">
                Belum ada tugas yang diberikan untuk kelas ini.
            </x-empty-state>
        @else
            <div class="space-y-3">
                @foreach ($assignments as $assignment)
                    @php
                        $submission = $assignment->submissions->first();

                        $status = match (true) {
                            $submission && in_array($submission->status, [\App\Enums\SubmissionStatus::Submitted, \App\Enums\SubmissionStatus::Graded], true) => ['label' => 'Selesai', 'variant' => 'success'],
                            $submission && $submission->status === \App\Enums\SubmissionStatus::Draft => ['label' => 'Dalam Pengerjaan', 'variant' => 'info'],
                            $assignment->isOverdue() => ['label' => 'Terlambat', 'variant' => 'danger'],
                            default => ['label' => 'Belum Dikerjakan', 'variant' => 'neutral'],
                        };
                    @endphp

                    <x-card>
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-badge variant="neutral">{{ $assignment->subject?->name }}</x-badge>
                                    <x-badge :variant="$status['variant']" dot>{{ $status['label'] }}</x-badge>
                                </div>

                                <h2 class="mt-2 text-base font-semibold text-neutral-900">
                                    <a href="{{ route('student.assignments.show', $assignment) }}" class="transition hover:text-primary-700">
                                        {{ $assignment->title }}
                                    </a>
                                </h2>

                                <p class="mt-1 text-sm text-neutral-500">
                                    {{ $assignment->type->label() }}
                                    @if ($assignment->deadline)
                                        &middot; Tenggat {{ $assignment->deadline->translatedFormat('d M Y, H.i') }}
                                    @endif
                                </p>
                            </div>

                            <x-button :href="route('student.assignments.show', $assignment)" variant="outline" size="sm" icon="eye">Buka</x-button>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
