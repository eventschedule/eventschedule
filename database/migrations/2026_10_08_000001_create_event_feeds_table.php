<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An address a schedule keeps reading for events: a calendar, a feed of posts, a page that
 * lists events, or a provider's feed. Read about once an hour by app:import-feeds.
 *
 * url is the address, encrypted at rest: for a calendar's private link or a provider's feed it
 * IS the credential. url_hash (sha256 of the address as normalised) is what the uniqueness and
 * a schedule merge compare, since an encrypted value cannot be compared; host is the one part
 * of the address shown where the address itself must not be (the operator's list).
 *
 * added_by is the member who added it. Their removal pauses the feed rather than deleting it,
 * so it is nulled, not cascaded.
 *
 * What the owner chose: publish_mode (publish | draft) for new events, left_action (keep |
 * cancel | delete) for an event that leaves the source, the sub-schedule and category new
 * events are filed under, whether organizers are added, and source_timezone, the zone an
 * unzoned time is read in. It is stored when the feed is added so that a later change of the
 * schedule's own zone moves nothing.
 *
 * can_see_leaving: whether this kind of source lists everything it has, so that absence means
 * gone. A feed of posts lists its newest few, and for it only "Leave it" is offered.
 *
 * The reader's own state: next_check_at (what "due" means, indexed), the last check and the
 * last good one, last_status (a reason KEY, never a message: a message can quote the address),
 * failure_count, the validators for a conditional GET, and paused_at with why.
 *
 * baseline_batch marks the first read's events (it is their events.import_batch), which is what
 * "Undo first read" removes and what the subscriber digest leaves out. held_leaving is what the
 * breaker is holding back: a read that would take a large share of the coming events at once.
 *
 * waiting_count and decide_count are kept here rather than counted from the items because the
 * tab's badge reads them on every admin page of the schedule.
 *
 * Not in backups: no integration credential is.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 255);
            $table->text('url');
            $table->char('url_hash', 64)->charset('ascii')->collation('ascii_bin');
            $table->string('host', 255);
            $table->string('kind', 20);
            $table->string('publish_mode', 10)->default('draft');
            $table->string('left_action', 10)->default('keep');
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('category_id')->nullable();
            $table->boolean('add_organizer')->default(false);
            $table->string('source_timezone', 64);
            $table->boolean('can_see_leaving')->default(false);
            $table->dateTime('paused_at')->nullable();
            $table->string('pause_reason', 30)->nullable();
            $table->dateTime('next_check_at')->nullable()->index();
            $table->dateTime('last_checked_at')->nullable();
            $table->dateTime('last_success_at')->nullable();
            $table->string('last_status', 40)->nullable();
            $table->unsignedSmallInteger('failure_count')->default(0);
            $table->string('etag', 255)->nullable();
            $table->string('last_modified', 255)->nullable();
            $table->char('baseline_batch', 12)->nullable();
            $table->dateTime('baseline_done_at')->nullable();
            $table->json('held_leaving')->nullable();
            $table->unsignedInteger('waiting_count')->default(0);
            $table->unsignedInteger('decide_count')->default(0);
            $table->json('stats')->nullable();
            $table->timestamps();

            $table->unique(['role_id', 'url_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_feeds');
    }
};
