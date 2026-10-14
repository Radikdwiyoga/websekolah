<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssignmentStudentSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $backup
     */
    public function __construct(private readonly array $backup) {}

    public function title(): string
    {
        return 'Rekap Siswa';
    }

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'Kelas', 'Jumlah Tugas', 'Dikumpulkan', 'Dinilai', 'Rata-rata Nilai'];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        /** @var Collection<int, array<string, mixed>> $rows */
        $rows = $this->backup['studentRows'];

        return $rows->values()->map(fn (array $row, int $index): array => [
            $index + 1,
            $row['name'],
            $row['class'],
            $row['assignments'],
            $row['submitted'],
            $row['graded'],
            $row['average'],
        ])->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
