<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendancePermitSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $backup
     */
    public function __construct(private readonly array $backup) {}

    public function title(): string
    {
        return 'Izin & Sakit';
    }

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['Tanggal', 'Kelas', 'Nama Siswa', 'Jenis', 'Alasan', 'Status', 'Ditinjau Oleh', 'Waktu Tinjau', 'Catatan'];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        /** @var Collection<int, array<string, mixed>> $permits */
        $permits = $this->backup['permits'];

        return $permits->map(fn (array $permit): array => [
            $permit['date'],
            $permit['class'],
            $permit['student'],
            $permit['type'],
            $permit['reason'],
            $permit['status'],
            $permit['reviewer'],
            $permit['reviewed_at'],
            $permit['note'],
        ])->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
