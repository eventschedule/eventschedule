<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->timestamp('email_settings_failed_at')->nullable()->after('email_settings');
            $table->text('email_settings_failed_message')->nullable()->after('email_settings_failed_at');
            $table->timestamp('email_settings_failure_notified_at')->nullable()->after('email_settings_failed_message');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'email_settings_failed_at',
                'email_settings_failed_message',
                'email_settings_failure_notified_at',
            ]);
        });
    }
};
