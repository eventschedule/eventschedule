<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The owner's switch "Team members pick from these presets" (Edit schedule > Settings > Ticket
 * presets). With it on, everyone else on the team picks a preset on an event's Tickets tab and cannot
 * type prices; App\Services\TicketPresets::lockedFor() reads it and EventRepo::saveEvent() enforces
 * it. A table of its own because `roles` is at MySQL's row limit (see 2026_10_07_000005); no row
 * reads as off.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_preset_settings')) {
            return;
        }

        Schema::create('ticket_preset_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('team_uses_presets')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_preset_settings');
    }
};
