<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A boost partner: an account whose schedules boost under ITS OWN Facebook Page and Meta ad
 * account, with every boost approved and marked paid by that account before it launches.
 *
 * One row per account (user_id unique), created by a platform admin at /admin/boost. Its
 * schedules are the ones it owns (roles.user_id). From the moment the row exists, a boost on
 * one of those schedules can never run under the platform's own Page, even before the partner
 * connects (BoostPartner::forRole()).
 *
 * The connection is one of two methods:
 *  - partner_share: the partner shares its ad account and Page with our Meta Business, and the
 *    campaigns run with OUR system-user token. We cannot tell which of our accounts shared an
 *    asset, so a platform admin confirms the IDs once (confirmed_at).
 *  - login: Facebook Login for Business, which stores the partner's own token (meta_token).
 *
 * The fee card is a plain Stripe customer on the platform account, not Cashier: Cashier's
 * customer model is Role, and this card belongs to the account.
 *
 * It lives in its own table and not on users or roles, which are wide (roles is at MySQL's row
 * limit).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('boost_partners')) {
            return;
        }

        Schema::create('boost_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamp('enabled_at')->nullable();
            $table->foreignId('enabled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('connection_method', 20)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('meta_user_id', 64)->nullable();
            $table->text('meta_token')->nullable();
            $table->timestamp('meta_token_expires_at')->nullable();

            $table->string('page_id', 64)->nullable();
            $table->string('page_name', 255)->nullable();
            $table->string('ad_account_id', 64)->nullable();
            $table->string('ad_account_name', 255)->nullable();
            $table->string('ad_account_currency', 3)->nullable();
            $table->string('ad_account_timezone', 64)->nullable();
            $table->string('ad_account_business_name', 255)->nullable();

            $table->string('connection_error', 500)->nullable();
            $table->timestamp('connection_checked_at')->nullable();
            $table->timestamp('needs_attention_at')->nullable();

            $table->string('payment_instructions', 500)->nullable();

            $table->decimal('fee_rate', 5, 4)->default(0.2000);
            $table->string('stripe_customer_id', 64)->nullable();
            $table->string('stripe_payment_method_id', 64)->nullable();
            $table->string('pm_brand', 32)->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->decimal('carry_forward', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_partners');
    }
};
