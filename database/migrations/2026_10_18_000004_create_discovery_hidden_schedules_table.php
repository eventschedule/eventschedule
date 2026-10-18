<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schedules a platform admin has taken out of discovery: every event FROM the schedule leaves the
 * homepage, /browse, /search, /for-talent and the global sitemap, and the schedule leaves /search's
 * schedule results (Event::constrainNotOnHiddenSchedule()). Its own pages stay as they are.
 *
 * A table of its own, and not a column on `roles`, because `roles` sits at MySQL's 65,535-byte row
 * limit (see 2026_10_07_000005), and because this is the operator's decision, not the owner's:
 * a column there could be posted back by the schedule form. No user_id: who hid it is in the
 * audit log (AuditService::ADMIN_DISCOVERY_HIDE_SCHEDULE).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_hidden_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovery_hidden_schedules');
    }
};
