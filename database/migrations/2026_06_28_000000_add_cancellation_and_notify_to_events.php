<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false);
            }
            if (! Schema::hasColumn('events', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('is_cancelled');
            }
            if (! Schema::hasColumn('events', 'attendees_notified_at')) {
                $table->timestamp('attendees_notified_at')->nullable()->after('cancelled_at');
            }
            if (! Schema::hasColumn('events', 'ical_sequence')) {
                $table->unsignedInteger('ical_sequence')->default(0)->after('attendees_notified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            foreach (['is_cancelled', 'cancelled_at', 'attendees_notified_at', 'ical_sequence'] as $column) {
                if (Schema::hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
