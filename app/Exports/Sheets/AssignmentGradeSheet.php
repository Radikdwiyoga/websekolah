<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssignmentGradeSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $backup
     */
    public function __construct(private readonly array $backup) {}

    public function title(): string
    {
        return 'Buku Nilai';
    }

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'Kelas', 'Mata Pelajaran', 'Tugas', 'Nilai', 'Bobot', 'Dinilai Pada', 'Dinilai Oleh'];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        /** @var Collection<int, array<string, mixed>> $rows */
        $rows = $this->backup['grades'];

        return $rows->values()->map(fn (array $row, int $index): array => [
            $index + 1,
            $row['student'],
            $row['class'],
            $row['subject'],
            $row['assignment'],
            $row['score'],
            $row['weight'],
            $row['graded_at'],
            $row['graded_by'],
        ])->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
