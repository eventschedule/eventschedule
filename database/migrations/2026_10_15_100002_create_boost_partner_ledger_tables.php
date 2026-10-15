<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The record a boost partner works from.
 *
 * boost_ad_versions: one row per version of a partner boost's ad. It is what the partner sees
 * (and compares, "Changed: headline, picture"), and what a launch or a re-approval sends to
 * Meta. image_path is OUR copy of the picture: replacing an event's flyer deletes the old file,
 * so an approved picture must not depend on it.
 *
 * boost_spend_days: spend per day in the AD ACCOUNT's timezone, from Meta's daily insights. The
 * monthly fee is summed from it, so a campaign that spans two months appears in both.
 *
 * boost_statements / boost_statement_lines: one statement per partner per month, with a line per
 * campaign (and adjustment lines for spend Meta corrected after an earlier month was billed).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('boost_ad_versions')) {
            Schema::create('boost_ad_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boost_campaign_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->string('headline', 255)->nullable();
                $table->text('primary_text')->nullable();
                $table->string('description', 255)->nullable();
                $table->string('call_to_action', 30)->nullable();
                $table->string('image_path', 500)->nullable();
                $table->string('destination_url', 2048)->nullable();
                $table->decimal('budget', 10, 2);
                $table->timestamp('end_date')->nullable();
                $table->json('targeting')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable();
                $table->unique(['boost_campaign_id', 'version']);
            });
        }

        if (! Schema::hasTable('boost_spend_days')) {
            Schema::create('boost_spend_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boost_campaign_id')->constrained()->cascadeOnDelete();
                $table->date('day');
                $table->decimal('spend', 10, 2)->default(0);
                $table->string('currency', 3);
                $table->timestamps();
                $table->unique(['boost_campaign_id', 'day']);
            });
        }

        if (! Schema::hasTable('boost_statements')) {
            Schema::create('boost_statements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boost_partner_id')->constrained()->cascadeOnDelete();
                $table->string('period', 7);
                $table->string('currency', 3);
                $table->decimal('total_spend', 10, 2)->default(0);
                $table->decimal('total_fee', 10, 2)->default(0);
                $table->decimal('total_adjustments', 10, 2)->default(0);
                $table->decimal('carried_in', 10, 2)->default(0);
                $table->decimal('charged_amount', 10, 2)->default(0);
                $table->string('status', 20)->default('pending');
                $table->string('stripe_payment_intent_id', 64)->nullable();
                $table->timestamp('charged_at')->nullable();
                $table->string('failure_code', 64)->nullable();
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamp('mailed_at')->nullable();
                $table->timestamps();
                // One statement per partner, month and currency: a partner that moved to an ad
                // account in another currency is billed in each, never summed across them.
                $table->unique(['boost_partner_id', 'period', 'currency']);
            });
        }

        if (! Schema::hasTable('boost_statement_lines')) {
            Schema::create('boost_statement_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('boost_statement_id')->constrained()->cascadeOnDelete();
                $table->foreignId('boost_campaign_id')->nullable()->constrained()->nullOnDelete();
                $table->string('period', 7);
                $table->decimal('spend', 10, 2)->default(0);
                $table->decimal('fee', 10, 2)->default(0);
                $table->decimal('adjustment', 10, 2)->default(0);
                $table->string('note', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_statement_lines');
        Schema::dropIfExists('boost_statements');
        Schema::dropIfExists('boost_spend_days');
        Schema::dropIfExists('boost_ad_versions');
    }
};
