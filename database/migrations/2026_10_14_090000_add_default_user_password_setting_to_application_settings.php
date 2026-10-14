<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the default password used when a teacher or parent account is created.
     */
    public function up(): void
    {
        DB::table('application_settings')->updateOrInsert(
            ['key' => 'default_user_password'],
            [
                'value' => 'password',
                'type' => 'text',
                'group' => 'account',
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
        DB::table('application_settings')->where('key', 'default_user_password')->delete();
    }
};
