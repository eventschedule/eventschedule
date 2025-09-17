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
        Schema::table('events', function (Blueprint $table) {
            $table->string('google_event_id')->nullable();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable();
            $table->string('google_webhook_id')->nullable();
            $table->string('google_webhook_resource_id')->nullable();
            $table->timestamp('google_webhook_expires_at')->nullable();
            $table->enum('sync_direction', ['to', 'from', 'both'])->nullable();
            $table->text('request_terms')->nullable();
            $table->text('request_terms_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'google_calendar_id', 
                'google_webhook_id', 
                'google_webhook_resource_id', 
                'google_webhook_expires_at', 
                'sync_direction',
                'google_calendar_id',
                'request_terms',
                'request_terms_en',
            ]);
        });
    }
};
