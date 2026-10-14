<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceDetailSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $backup
     */
    public function __construct(private readonly array $backup) {}

    public function title(): string
    {
        return 'Detail Absensi';
    }

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['Tanggal', 'Kelas', 'Nama Siswa', 'Status', 'Metode', 'Catatan'];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        /** @var Collection<int, array<string, mixed>> $records */
        $records = $this->backup['records'];

        return $records->map(fn (array $record): array => [
            $record['date'],
            $record['class'],
            $record['student'],
            $record['status'],
            $record['method'],
            $record['note'],
        ])->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
