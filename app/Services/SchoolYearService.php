<?php

namespace App\Services;

use App\Enums\Semester;
use App\Enums\SystemLogStatus;
use App\Models\SchoolYear;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolYearService
{
    /**
     * @param  array{name: string, semester: string, start_date?: string|null, end_date?: string|null}  $data
     */
    public function create(array $data, User $actor): SchoolYear
    {
        $schoolYear = SchoolYear::query()->create([
            'name' => $data['name'],
            'semester' => $data['semester'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'is_active' => false,
        ]);

        $this->log($actor, 'create', 'Menambahkan tahun ajaran '.$this->label($schoolYear));

        return $schoolYear;
    }

    /**
     * @param  array{name: string, semester: string, start_date?: string|null, end_date?: string|null}  $data
     */
    public function update(SchoolYear $schoolYear, array $data, User $actor): void
    {
        $schoolYear->update([
            'name' => $data['name'],
            'semester' => $data['semester'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);

        $this->log($actor, 'update', 'Memperbarui tahun ajaran '.$this->label($schoolYear));
    }

    /**
     * Make this school year the only active one.
     */
    public function activate(SchoolYear $schoolYear, User $actor): void
    {
        DB::transaction(function () use ($schoolYear): void {
            SchoolYear::query()->where('id', '!=', $schoolYear->id)->update(['is_active' => false]);
            $schoolYear->update(['is_active' => true]);
        });

        $this->log($actor, 'activate', 'Mengaktifkan tahun ajaran '.$this->label($schoolYear));
    }

    private function label(SchoolYear $schoolYear): string
    {
        $semester = $schoolYear->semester instanceof Semester
            ? $schoolYear->semester->label()
            : (string) $schoolYear->semester;

        return "{$schoolYear->name} ({$semester})";
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'school_year',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
