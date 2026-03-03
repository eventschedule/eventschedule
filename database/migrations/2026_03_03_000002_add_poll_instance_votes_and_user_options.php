<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-instance poll votes
        Schema::table('event_poll_votes', function (Blueprint $table) {
            $table->string('event_date', 10)->nullable()->after('option_index');

            $table->dropUnique(['event_poll_id', 'user_id']);
            $table->unique(['event_poll_id', 'user_id', 'event_date']);
            $table->index(['event_poll_id', 'event_date']);
        });

        // User-added poll options
        Schema::table('event_polls', function (Blueprint $table) {
            $table->boolean('allow_user_options')->default(false)->after('sort_order');
            $table->boolean('require_option_approval')->default(false)->after('allow_user_options');
            $table->json('pending_options')->nullable()->after('require_option_approval');
        });
    }

    public function down(): void
    {
        Schema::table('event_poll_votes', function (Blueprint $table) {
            $table->dropIndex(['event_poll_id', 'event_date']);
            $table->dropUnique(['event_poll_id', 'user_id', 'event_date']);
            $table->unique(['event_poll_id', 'user_id']);
            $table->dropColumn('event_date');
        });

        Schema::table('event_polls', function (Blueprint $table) {
            $table->dropColumn(['allow_user_options', 'require_option_approval', 'pending_options']);
        });
    }
};
