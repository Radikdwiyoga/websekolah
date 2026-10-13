@props([
    'permits',
    'showStudent' => false,
    'destroyRoute' => null,
    'reviewable' => false,
])

@if ($permits->isEmpty())
    <x-empty-state icon="permit" title="Belum ada pengajuan izin/sakit">
        Pengajuan izin atau sakit akan tampil di sini.
    </x-empty-state>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-200 text-left text-xs font-medium uppercase tracking-wide text-neutral-500">
                    @if ($showStudent)
                        <th class="pb-2 pr-4">Siswa</th>
                    @endif
                    <th class="pb-2 pr-4">Tanggal</th>
                    <th class="pb-2 pr-4">Jenis</th>
                    <th class="pb-2 pr-4">Alasan</th>
                    <th class="pb-2 pr-4">Lampiran</th>
                    <th class="pb-2 pr-4">Status</th>
                    <th class="pb-2 pr-4">Catatan</th>
                    <th class="pb-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @foreach ($permits as $permit)
                    <tr>
                        @if ($showStudent)
                            <td class="py-3 pr-4">
                                <p class="font-medium text-neutral-900">{{ $permit->student?->user?->name ?? '—' }}</p>
                                <p class="text-xs text-neutral-400">{{ $permit->student?->schoolClass?->name ?? 'Tanpa kelas' }}</p>
                            </td>
                        @endif

                        <td class="py-3 pr-4 whitespace-nowrap text-neutral-600">
                            {{ $permit->date?->translatedFormat('d M Y') ?? '—' }}
                        </td>

                        <td class="py-3 pr-4">
                            <x-badge :variant="$permit->type->value === 'sakit' ? 'warning' : 'info'">{{ $permit->type->label() }}</x-badge>
                        </td>

                        <td class="py-3 pr-4 text-neutral-600">
                            <p class="max-w-xs truncate" title="{{ $permit->reason }}">{{ $permit->reason }}</p>
                        </td>

                        <td class="py-3 pr-4">
                            @if ($permit->hasAttachment())
                                <a href="{{ route('permits.attachment', $permit) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-700 hover:underline">
                                    <x-icon name="file" :size="4" />
                                    Lihat
                                </a>
                            @else
                                <span class="text-xs text-neutral-400">—</span>
                            @endif
                        </td>

                        <td class="py-3 pr-4">
                            <x-badge :variant="$permit->status->variant()" dot>{{ $permit->status->label() }}</x-badge>
                        </td>

                        <td class="py-3 pr-4 text-xs text-neutral-500">
                            {{ $permit->review_note ?: '—' }}
                        </td>

                        <td class="py-3 text-right">
                            @if ($reviewable && $permit->isPending())
                                <form method="POST" action="{{ route('teacher.permits.review', $permit) }}" class="flex flex-col items-end gap-2">
                                    @csrf
                                    <input
                                        type="text"
                                        name="note"
                                        maxlength="500"
                                        placeholder="Catatan (opsional)"
                                        class="w-40 rounded-md border border-neutral-300 px-2.5 py-1.5 text-xs focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                                    />
                                    <div class="flex gap-2">
                                        <x-button type="submit" name="decision" value="approved" size="sm" icon="check">Setujui</x-button>
                                        <x-button type="submit" name="decision" value="rejected" size="sm" variant="danger" icon="x">Tolak</x-button>
                                    </div>
                                </form>
                            @elseif ($destroyRoute && $permit->isPending())
                                <form method="POST" action="{{ route($destroyRoute, $permit) }}" onsubmit="return confirm('Batalkan pengajuan izin/sakit ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="ghost" size="sm" icon="trash">Batalkan</x-button>
                                </form>
                            @else
                                <span class="text-xs text-neutral-400">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
