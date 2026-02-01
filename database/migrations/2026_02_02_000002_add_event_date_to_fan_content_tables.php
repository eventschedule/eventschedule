<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_videos', function (Blueprint $table) {
            $table->string('event_date')->nullable()->after('event_part_id');
            $table->index(['event_id', 'event_date', 'is_approved']);
        });

        Schema::table('event_comments', function (Blueprint $table) {
            $table->string('event_date')->nullable()->after('event_part_id');
            $table->index(['event_id', 'event_date', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::table('event_videos', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'event_date', 'is_approved']);
            $table->dropColumn('event_date');
        });

        Schema::table('event_comments', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'event_date', 'is_approved']);
            $table->dropColumn('event_date');
        });
    }
};
