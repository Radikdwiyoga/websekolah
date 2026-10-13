<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the default password used when a student account is created.
     */
    public function up(): void
    {
        DB::table('application_settings')->updateOrInsert(
            ['key' => 'student_default_password'],
            [
                'value' => 'password',
                'type' => 'text',
                'group' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('application_settings')->where('key', 'student_default_password')->delete();
    }
};
