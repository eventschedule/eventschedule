<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('rsvp_enabled')->default(false)->after('tickets_enabled');
            $table->unsignedInteger('rsvp_limit')->nullable()->after('rsvp_enabled');
            $table->text('rsvp_sold')->nullable()->after('rsvp_limit');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['rsvp_enabled', 'rsvp_limit', 'rsvp_sold']);
        });
    }
};
