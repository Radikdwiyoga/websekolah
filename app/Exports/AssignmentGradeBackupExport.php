<?php

namespace App\Exports;

use App\Exports\Sheets\AssignmentGradeSheet;
use App\Exports\Sheets\AssignmentStudentSheet;
use App\Exports\Sheets\AssignmentSummarySheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AssignmentGradeBackupExport implements Export, WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $backup
     */
    public function __construct(private readonly array $backup) {}

    /**
     * @return array<int, Export>
     */
    public function sheets(): array
    {
        return [
            new AssignmentSummarySheet($this->backup),
            new AssignmentStudentSheet($this->backup),
            new AssignmentGradeSheet($this->backup),
        ];
    }
}
