<?php

namespace App\Exports;

use App\Exports\Sheets\AttendanceDetailSheet;
use App\Exports\Sheets\AttendancePermitSheet;
use App\Exports\Sheets\AttendanceRecapSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceBackupExport implements Export, WithMultipleSheets
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
            new AttendanceRecapSheet($this->backup),
            new AttendanceDetailSheet($this->backup),
            new AttendancePermitSheet($this->backup),
        ];
    }
}
