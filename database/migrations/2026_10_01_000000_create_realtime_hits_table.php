<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per page view for /admin/realtime, kept about an hour after the visitor's last
     * activity and then deleted (RealtimeTracker::prune(), realtime:prune on both cron rails).
     *
     * This is the one exception to "there is no raw event log in this codebase", and it is bounded
     * on purpose: no IP address, no user-agent string, no referrer path, and an hour of rows at most.
     * A visitor who has not accepted cookies is stored with no identifier at all (consented = 0,
     * visitor_key and user_id null), so such a row cannot be linked to the person or to their other
     * page views.
     *
     * No foreign keys: every page view inserts here, and an FK would take a shared lock on the
     * parent roles/events row per insert and race the hourly demo reset that deletes those rows
     * (see CounterUtils). A row whose schedule or event is gone just renders without a name.
     *
     * Every timestamp is written from PHP in UTC; never useCurrent()/NOW(), because no connection
     * timezone is configured and a selfhost MySQL on server time would be hours off.
     */
    public function up(): void
    {
        Schema::create('realtime_hits', function (Blueprint $table) {
            $table->id();
            $table->char('hit_key', 32)->charset('ascii')->collation('ascii_bin');
            $table->char('visitor_key', 16)->charset('ascii')->collation('ascii_bin')->nullable();
            $table->boolean('consented')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->string('surface', 8);
            $table->string('path', 255);
            // The route template of a page whose real path names the visitor (an app page's
            // schedule subdomain, a ticket order's id), kept beside an identified row's real path so
            // a withdrawal can swap it in. Count-only rows store the template as `path` instead.
            $table->string('path_template', 255)->nullable();
            $table->string('title', 150)->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->string('source_channel', 12)->nullable();
            $table->string('source_name', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->boolean('is_entrance')->default(false);
            $table->char('country', 2)->nullable();
            $table->string('device', 8)->default('unknown');
            $table->string('browser', 20)->nullable();
            $table->string('os', 20)->nullable();
            $table->unsignedSmallInteger('hb')->default(60);
            $table->dateTime('started_at');
            $table->dateTime('last_seen_at');
            $table->dateTime('engaged_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            $table->unique('hit_key');
            $table->index('last_seen_at');
            $table->index(['visitor_key', 'last_seen_at']);
            $table->index(['user_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realtime_hits');
    }
};
