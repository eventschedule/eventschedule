<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a boost needs when it runs through a boost partner (see the boost_partners migration).
 *
 * - version / approved_version: approval is per VERSION of the ad. Any change to the headline,
 *   text, description, button, picture or link raises version, and only an approval naming
 *   the current version can launch it.
 * - paid_budget: payment is per BUDGET. The partner records its own Stripe reference and the
 *   amount its customer paid; a later change of budget no longer matches and needs paying again.
 * - meta_ad_account_id / meta_page_id / account_*: the account a campaign was submitted and
 *   launched under. Stop, sync and the monthly fee always use it, even after the partner
 *   reconnects to another one.
 * - schedule_* / event_name: event_id and role_id are nullOnDelete, so a campaign list and a
 *   statement could otherwise no longer name what was advertised.
 *
 * The table is narrow (about 15 short varchars before this), so the columns are added here
 * rather than in a side table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('boost_campaigns', 'boost_partner_id')) {
                $table->foreignId('boost_partner_id')->nullable()->constrained('boost_partners')->nullOnDelete();
            }
            if (! Schema::hasColumn('boost_campaigns', 'meta_ad_account_id')) {
                $table->string('meta_ad_account_id', 64)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'meta_page_id')) {
                $table->string('meta_page_id', 64)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'meta_page_name')) {
                $table->string('meta_page_name', 255)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'account_timezone')) {
                $table->string('account_timezone', 64)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'schedule_subdomain')) {
                $table->string('schedule_subdomain', 255)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'schedule_name')) {
                $table->string('schedule_name', 255)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'event_name')) {
                $table->string('event_name', 255)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'version')) {
                $table->unsignedInteger('version')->default(1);
            }
            if (! Schema::hasColumn('boost_campaigns', 'approved_version')) {
                $table->unsignedInteger('approved_version')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('boost_campaigns', 'approval_note')) {
                $table->string('approval_note', 1000)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'declined_at')) {
                $table->timestamp('declined_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'decline_reason')) {
                $table->string('decline_reason', 30)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'payment_confirmed_at')) {
                $table->timestamp('payment_confirmed_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'payment_reference')) {
                $table->string('payment_reference', 255)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'payment_currency')) {
                $table->string('payment_currency', 3)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'paid_budget')) {
                $table->decimal('paid_budget', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'payment_confirmed_by')) {
                $table->foreignId('payment_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('boost_campaigns', 'stopped_at')) {
                $table->timestamp('stopped_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'stop_reason')) {
                $table->string('stop_reason', 500)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'stop_code')) {
                $table->string('stop_code', 30)->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'stopped_by')) {
                $table->foreignId('stopped_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('boost_campaigns', 'pause_pending_at')) {
                $table->timestamp('pause_pending_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'launched_at')) {
                $table->timestamp('launched_at')->nullable();
            }
            if (! Schema::hasColumn('boost_campaigns', 'spend_final_at')) {
                $table->timestamp('spend_final_at')->nullable();
            }
        });

        if (! Schema::hasIndex('boost_campaigns', 'boost_campaigns_partner_updated_index')) {
            Schema::table('boost_campaigns', function (Blueprint $table) {
                $table->index(['boost_partner_id', 'updated_at'], 'boost_campaigns_partner_updated_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropIndex('boost_campaigns_partner_updated_index');
            $table->dropConstrainedForeignId('boost_partner_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('payment_confirmed_by');
            $table->dropConstrainedForeignId('stopped_by');
            $table->dropColumn([
                'meta_ad_account_id', 'meta_page_id', 'meta_page_name', 'account_timezone',
                'schedule_subdomain', 'schedule_name', 'event_name', 'version', 'approved_version',
                'approved_at', 'approval_note', 'declined_at', 'decline_reason',
                'payment_confirmed_at', 'payment_reference', 'payment_amount', 'payment_currency',
                'paid_budget', 'stopped_at', 'stop_reason', 'stop_code', 'pause_pending_at',
                'submitted_at', 'launched_at', 'spend_final_at',
            ]);
        });
    }
};
