<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One thing a feed has shown us: the ledger of what was read, and the queue of what is still
 * to be done about it.
 *
 * external_id is the source's own id for it, as long as the source likes; external_key is its
 * sha256, which is what the unique index and every lookup use. ascii_bin, so that two ids which
 * differ only in case are two items.
 *
 * state:
 *   new        seen, not yet made into an event
 *   imported   the feed made event_id, and keeps it up to date
 *   matched    the schedule already had this event: event_id is a link and nothing more. It is
 *              never written, cancelled or deleted by the feed
 *   skipped    the owner said not this one, and can still add it
 *   dismissed  the owner deleted the event the feed made. It stays deleted
 *   removed    the feed deleted the event because it left the source. A source that lists it
 *              again brings it back
 *   decide     something about it is the owner's to decide (it left, was cancelled or moved,
 *              and people are registered)
 *
 * An item that says `imported` and has no event (event_id is nulled when the event goes) lost
 * it some other way than the two above, and the next read brings it back as a draft.
 *
 * imported is what the feed last wrote, field by field, read back from the saved row: a field
 * that still holds it follows the source, and one that does not was changed by the owner and is
 * left alone. pending is what the source says now for the fields the owner changed.
 * decided_hash is the hash of the guarded facts (start, venue, cancelled, gone) the owner last
 * decided about, so the same difference is not raised twice.
 *
 * starts_at is the source's start, kept on the item because an item can outlive its event or
 * never have one, and both the detail pass and pruning ask when it is.
 *
 * detail_url and image_source are addresses at the source, encrypted like the feed's own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_feed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_feed_id')->constrained()->cascadeOnDelete();
            $table->char('external_key', 64)->charset('ascii')->collation('ascii_bin');
            $table->text('external_id');
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('state', 12)->default('new');
            $table->dateTime('starts_at')->nullable();
            $table->char('list_hash', 64)->nullable();
            $table->char('detail_hash', 64)->nullable();
            $table->text('detail_url')->nullable();
            $table->dateTime('detail_checked_at')->nullable();
            $table->json('imported')->nullable();
            $table->json('pending')->nullable();
            $table->char('decided_hash', 64)->nullable();
            // Counted on every read that misses the item, for as long as its event is still to
            // come: the reader stops at a cap, and the column has room past it all the same.
            $table->unsignedSmallInteger('missing_reads')->default(0);
            $table->boolean('cancelled_by_feed')->default(false);
            // The cancelled_at the feed's own cancellation stamped. A cancelled event is put
            // back when the source lists it again only while this is still the event's own:
            // one the owner restored and cancelled again is theirs.
            $table->dateTime('feed_cancelled_at')->nullable();
            // How often the item's own page did not answer. After a few it is left out.
            $table->unsignedTinyInteger('page_tries')->default(0);
            $table->text('image_source')->nullable();
            $table->boolean('image_pending')->default(false);
            // How often that picture could not be had. After a few it is not asked for again.
            $table->unsignedTinyInteger('picture_tries')->default(0);
            $table->dateTime('publish_requested_at')->nullable();
            $table->dateTime('first_seen_at')->nullable();
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['event_feed_id', 'external_key']);
            $table->index(['event_feed_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_feed_items');
    }
};
