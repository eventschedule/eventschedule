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
            $table->text('registration_url')->nullable()->change();
        });

        Schema::table('parsed_event_urls', function (Blueprint $table) {
            // 768 chars * 4 bytes (utf8mb4) = 3072 bytes, the InnoDB max key
            // length, so the unique index on this column stays valid
            $table->string('url', 768)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('registration_url')->nullable()->change();
        });

        Schema::table('parsed_event_urls', function (Blueprint $table) {
            $table->string('url')->change();
        });
    }
};
