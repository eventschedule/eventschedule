<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'timezone')) {
            Schema::table('events', function (Blueprint $table) {
                // The IANA timezone the event's wall-clock was captured in (the
                // schedule's timezone at creation). Records the intent so the stored
                // UTC starts_at is self-describing and off-timezone events can be
                // detected without guessing from the creator's account timezone.
                $table->string('timezone')->nullable()->after('starts_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'timezone')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('timezone');
            });
        }
    }
};
