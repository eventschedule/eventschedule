<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Read-only listing rows received from federated instances. Deliberately NOT
        // stored as `events`: an Event needs a local user and an accepted event_role
        // row to be visible anywhere, and Event::getCanonicalUrl() always resolves to
        // a local subdomain - which would self-canonicalize a federated listing to
        // this site instead of its origin, the opposite of what federation is for.
        Schema::create('federated_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('federated_instance_id')->constrained()->cascadeOnDelete();
            $table->string('external_id', 64);   // the origin's encoded event id: stable across renames, unlike the URL
            $table->string('url', 1024);         // the backlink, and the only place a click goes

            $table->string('name');
            $table->text('short_description')->nullable();
            $table->string('language', 10)->nullable();

            // Times are UTC instants plus an IANA zone. Render in the event's own zone:
            // formatting the instant in the viewer's or server's zone shifts evening
            // events by a day (see Event::getStartDateTime()).
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('timezone', 64)->nullable();
            // Resolved occurrence datetimes for recurring events, computed by the sender
            // with Event::matchesDate() so the recurrence rules are never reimplemented here.
            $table->json('occurrences')->nullable();
            // Denormalised first-future occurrence: you cannot ORDER BY or index inside
            // a JSON array, and browse both orders and paginates by date.
            $table->dateTime('next_occurrence_at')->nullable();
            // Hash of the resolved occurrence set, so a recurring event is only re-pushed
            // when its dates actually change rather than on every run.
            $table->string('occurrences_hash', 64)->nullable();

            $table->string('schedule_name')->nullable();
            $table->string('schedule_url', 1024)->nullable();
            // The remote image as advertised by the sender, and the local copy once
            // fetched. Rows are only listed with a local copy: hotlinking would leak
            // visitor IPs to arbitrary third-party hosts and need img-src opened up.
            $table->string('image_url', 1024)->nullable();
            $table->string('image_path')->nullable();

            // Present when the event has an online component. With a venue too it is
            // hybrid, alone it is online, absent it is in-person.
            $table->string('event_url', 1024)->nullable();

            // Venue flattened by the sender: events.venue_id no longer exists, the venue
            // is a Role reached through the event_role pivot, which a federated row
            // cannot traverse.
            $table->string('venue_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->decimal('geo_lat', 10, 7)->nullable();
            $table->decimal('geo_lon', 10, 7)->nullable();

            // Admin block. Kept out of the upsert's writable columns so a re-push cannot
            // clear it, and skipped by reconcile so a delete cannot resurrect it unblocked.
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->unique(['federated_instance_id', 'external_id'], 'federated_events_instance_external_unique');
            $table->index('next_occurrence_at');
            $table->index('country_code');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('federated_events');
    }
};
