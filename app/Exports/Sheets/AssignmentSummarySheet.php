<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssignmentSummarySheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $backup
     */
    public function __construct(private readonly array $backup) {}

    public function title(): string
    {
        return 'Ringkasan Tugas';
    }

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['No', 'Judul Tugas', 'Kelas', 'Mata Pelajaran', 'Guru', 'Tipe', 'Status', 'Tenggat', 'Jumlah Siswa', 'Dikumpulkan', 'Dinilai', 'Rata-rata Nilai'];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        /** @var Collection<int, array<string, mixed>> $rows */
        $rows = $this->backup['assignmentRows'];

        return $rows->values()->map(fn (array $row, int $index): array => [
            $index + 1,
            $row['title'],
            $row['class'],
            $row['subject'],
            $row['teacher'],
            $row['type'],
            $row['status'],
            $row['deadline'],
            $row['students'],
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
