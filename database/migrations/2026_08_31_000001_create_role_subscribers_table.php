<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * People who asked a schedule to email them about new events, WITHOUT creating an account.
 *
 * The account-less half of a schedule's audience. The other half is role_user at level 'follower',
 * and AudienceResolver unions the two, deduping on lowercased email. Following used to cost a full
 * user account (RoleController::follow() redirects a signed-out visitor to sign_up), which is why
 * 139k guest page views produced 764 followers.
 *
 * Two deliberate absences:
 *
 * - No unsubscribed_at. Suppression lives in newsletter_unsubscribes, already unique on
 *   (role_id, email), so one unsubscribe link stops both newsletters and announcements from this
 *   schedule. A mirrored flag here would be a second source of truth and would drift.
 * - No email verification of any kind beyond confirmed_at. This is not an account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('locale', 10)->nullable();

            // guest_panel | guest_modal | checkout (also RSVP) | import
            $table->string('source', 20)->default('guest_panel');

            // Only confirmed rows are ever mailed. The repo has no bounce, complaint or suppression
            // handling anywhere, so an address nobody confirmed must cost one stray message rather
            // than a permanent subscription.
            $table->timestamp('confirmed_at')->nullable();

            // Permanent, and used ONLY for unsubscribe, so no URL ever carries an email address.
            $table->char('token', 64)->unique();

            // Single-use, and deliberately separate from the token above.
            //
            // A confirmation link is a GET that mutates, and corporate mail gateways (Safe Links,
            // Proofpoint) fetch every URL in an inbound message. If confirming also lifted a
            // suppression and the link stayed live forever, then merely RECEIVING the original
            // confirmation email - months later, after unsubscribing - would silently resurrect
            // the subscription. Cleared on use and cleared on unsubscribe, so an old email is
            // inert either way.
            $table->char('confirm_token', 64)->nullable()->unique();

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Makes the public endpoint idempotent under a double submit: the controller catches
            // 1062 and reports success, the way WaitlistController::join() does, rather than
            // reading first and racing.
            $table->unique(['role_id', 'email']);
            $table->index(['role_id', 'confirmed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_subscribers');
    }
};
