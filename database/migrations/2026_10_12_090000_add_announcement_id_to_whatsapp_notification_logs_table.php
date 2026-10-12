<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link WhatsApp notification logs to the announcement that triggered them
     * so admins can review per-announcement delivery (DESIGN.md §20, §22).
     */
    public function up(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->foreignId('announcement_id')
                ->nullable()
                ->after('attendance_record_id')
                ->constrained('announcements')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('announcement_id');
        });
    }
};
