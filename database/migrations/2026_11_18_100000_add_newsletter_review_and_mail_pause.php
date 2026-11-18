<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Protections against a schedule's newsletter being used to mail strangers through the
 * platform's own sender (2026-11-18: a new account on a free trial imported a list and sent a
 * phishing newsletter in the name of a real conference). App\Services\AudienceMailGuard reads
 * every column here.
 *
 *  - users.newsletter_trusted_at: this account's newsletters are not held for review. Set by an
 *    operator's approval, and backfilled below for accounts that had already sent a newsletter
 *    to more than the review threshold. Trust is read from this column alone, never worked out
 *    from newsletters, whose status and counts a restored backup can carry.
 *  - users.mail_disabled_at / mail_disabled_reason: email sending is paused on the account (a
 *    watched word, a card already used on another account, or an operator's hand), and
 *    mail_access_requested_at is when the owner last asked to have it back. mail_cleared_at is an
 *    operator's "this account is fine": the automatic checks do not pause it again.
 *  - users.mail_flagged_at / mail_flag_reason: what would have paused an account that has paid,
 *    which is shown to an operator and stops nothing.
 *  - newsletters.held_at / review_note / approved_at / review_mix: a newsletter held for review
 *    (status 'review'), what the operator told the owner when it was not approved, the operator's
 *    approval, which lets that one newsletter past the hold (an edit to what it says or who it
 *    goes to clears it), and who it was for when it was held, for the review page.
 *  - payment_fingerprints: the card behind each checkout (Stripe's fingerprint, never the number),
 *    to see one card turning up on several accounts. When an account is deleted its rows lose the
 *    account and keep only a one-way hash (hashed_at), for 12 months.
 *  - subscriptions.last_paid_at: when Stripe last told us an invoice above zero was paid. "The
 *    owner has paid" means this, not a status: a trial that ends unpaid goes to past_due.
 *    Backfilled for subscriptions that are active and past any trial, which Stripe only reaches
 *    with a paid invoice.
 *  - sales.origin / role_subscribers.origin: a row the owner put there ('import', 'api',
 *    'restore') rather than one the person made, which is not an opt-in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'newsletter_trusted_at')) {
                $table->timestamp('newsletter_trusted_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'mail_disabled_at')) {
                $table->timestamp('mail_disabled_at')->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'mail_disabled_reason')) {
                $table->string('mail_disabled_reason', 32)->nullable();
            }
            if (! Schema::hasColumn('users', 'mail_access_requested_at')) {
                $table->timestamp('mail_access_requested_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'mail_cleared_at')) {
                $table->timestamp('mail_cleared_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'mail_flagged_at')) {
                $table->timestamp('mail_flagged_at')->nullable()->index();
            }
            if (! Schema::hasColumn('users', 'mail_flag_reason')) {
                $table->string('mail_flag_reason', 32)->nullable();
            }
        });

        Schema::table('newsletters', function (Blueprint $table) {
            if (! Schema::hasColumn('newsletters', 'held_at')) {
                $table->timestamp('held_at')->nullable();
            }
            if (! Schema::hasColumn('newsletters', 'review_note')) {
                $table->text('review_note')->nullable();
            }
            if (! Schema::hasColumn('newsletters', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('newsletters', 'review_mix')) {
                $table->json('review_mix')->nullable();
            }
        });

        if (! Schema::hasTable('payment_fingerprints')) {
            Schema::create('payment_fingerprints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->string('fingerprint', 64)->index();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('hashed_at')->nullable()->index();
            });
        }

        if (! Schema::hasColumn('subscriptions', 'last_paid_at')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->timestamp('last_paid_at')->nullable();
            });
        }

        foreach (['sales', 'role_subscribers'] as $table) {
            if (! Schema::hasColumn($table, 'origin')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('origin', 16)->nullable();
                });
            }
        }

        // Paying today: active and past its trial.
        DB::table('subscriptions')
            ->whereNull('last_paid_at')
            ->where('stripe_status', 'active')
            ->where(fn ($q) => $q->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', now()))
            ->update(['last_paid_at' => DB::raw('updated_at')]);

        // Accounts that already sent a schedule newsletter to more than the review threshold, and
        // either have paid (the backfill above ran first) or sent it before November 2026, when
        // the trial abuse began. Never a blocked account.
        $threshold = max(0, (int) config('usage.newsletter_review_threshold', 10));
        DB::table('users')
            ->whereNull('newsletter_trusted_at')
            ->whereNull('blocked_at')
            ->whereIn('id', fn ($q) => $q->select('roles.user_id')
                ->from('newsletters')
                ->join('roles', 'roles.id', '=', 'newsletters.role_id')
                ->whereNotNull('roles.user_id')
                ->where('newsletters.status', 'sent')
                ->where('newsletters.sent_count', '>', $threshold)
                ->where('newsletters.sent_at', '<', now())
                ->where(fn ($vouched) => $vouched
                    ->where('newsletters.sent_at', '<', '2026-11-01 00:00:00')
                    ->orWhereExists(fn ($paid) => $paid->selectRaw('1')
                        ->from('subscriptions')
                        ->join('roles as paying', 'paying.id', '=', 'subscriptions.role_id')
                        ->whereColumn('paying.user_id', 'roles.user_id')
                        ->whereNotNull('subscriptions.last_paid_at'))))
            ->update(['newsletter_trusted_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_fingerprints');

        foreach (['sales', 'role_subscribers'] as $table) {
            if (Schema::hasColumn($table, 'origin')) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn('origin'));
            }
        }

        if (Schema::hasColumn('subscriptions', 'last_paid_at')) {
            Schema::table('subscriptions', fn (Blueprint $table) => $table->dropColumn('last_paid_at'));
        }

        Schema::table('newsletters', function (Blueprint $table) {
            foreach (['held_at', 'review_note', 'approved_at', 'review_mix'] as $column) {
                if (Schema::hasColumn('newsletters', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mail_disabled_at')) {
                $table->dropIndex(['mail_disabled_at']);
            }
            if (Schema::hasColumn('users', 'mail_flagged_at')) {
                $table->dropIndex(['mail_flagged_at']);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['newsletter_trusted_at', 'mail_disabled_at', 'mail_disabled_reason', 'mail_access_requested_at', 'mail_cleared_at', 'mail_flagged_at', 'mail_flag_reason'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
