<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-EVENT interest capture: "tell me when tickets go on sale / if anything changes".
 *
 * Distinct from role_subscribers, which is per-SCHEDULE and is a standing subscription to
 * everything a schedule publishes. The two consents are deliberately not the same ask, so they are
 * deliberately not the same row: agreeing to hear about one named event is not agreeing to a
 * mailing list, and folding the first into the second is the kind of scope creep the unticked
 * checkout box at event/rsvp.blade.php:437 exists to avoid.
 *
 * Shaped from role_subscribers rather than from ticket_waitlists, which is the nearer table but the
 * wrong model: it carries no confirmed_at, no unsubscribe token and no ip_address, and the
 * List-Unsubscribe header on WaitlistNotification points at RoleController::unsubscribe(), which
 * matches on Role::where('email') - so a Gmail one-click POST, which carries neither an address nor
 * a signature, fails. Anything sending bulk mail needs a token of its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            // The occurrence, matching ticket_waitlists.event_date. NOT NULL with '' for an event
            // that has no date at all: MySQL treats NULLs as distinct, so a nullable column would
            // let one address take unlimited rows on the same event through the unique index below.
            $table->string('event_date')->default('');

            $table->string('email');

            // Nullable, unlike role_subscribers. This form asks for one field. A name is worth
            // requiring on a standing subscription the owner will mail repeatedly; it is pure
            // friction on a single "tell me about this one event".
            $table->string('name')->nullable();
            $table->string('locale', 10)->nullable();

            // event_page | checkout
            $table->string('source', 20)->default('event_page');

            // Single opt-in, stamped on create - the same split docs/FEATURES.md already draws
            // between the panel (double) and checkout (single). This is the narrow, specific ask:
            // one affirmative act, about one named event, for a bounded set of messages. The column
            // stays because AudienceResolver's rule is that an unconfirmed row is never mailed, and
            // because an unverified or rate-limited schedule may yet want to require a confirm.
            $table->timestamp('confirmed_at')->nullable();

            // Permanent, and used ONLY for unsubscribe, so no URL ever carries an email address.
            $table->char('token', 64)->unique();

            // Single-use, and deliberately separate from the token above. See the role_subscribers
            // migration for the Safe Links failure this split prevents.
            $table->char('confirm_token', 64)->nullable()->unique();

            // One send each, claimed by stamping the column. Separate columns rather than a status
            // enum: the two sends are independent, and a row can legitimately get the on-sale
            // message and then the reminder.
            $table->timestamp('tickets_notified_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Makes the public endpoint idempotent under a double submit: the controller catches
            // 1062 and reports success, the way WaitlistController::join() does, rather than
            // reading first and racing.
            $table->unique(['event_id', 'event_date', 'email']);

            // The two send queries: "confirmed rows on this occurrence that have not had X yet".
            $table->index(['event_id', 'event_date', 'confirmed_at']);
            $table->index(['event_id', 'tickets_notified_at']);
            $table->index(['event_id', 'reminder_sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_interests');
    }
};
