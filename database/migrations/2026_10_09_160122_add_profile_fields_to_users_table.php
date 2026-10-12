<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('role', 20)->default('student')->after('username')->index();
            $table->string('admin_level', 30)->nullable()->after('role');
            $table->string('phone', 20)->nullable()->after('admin_level');
            $table->boolean('is_active')->default(true)->after('phone')->index();
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropUnique('users_email_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
            $table->dropColumn(['username', 'role', 'admin_level', 'phone', 'is_active', 'last_login_at']);
        });
    }
};
