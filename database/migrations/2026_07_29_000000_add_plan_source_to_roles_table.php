<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records HOW a paid plan was granted when Stripe was not involved.
     *
     * plan_type/plan_expires cannot answer this on their own: an admin grant
     * (AdminController::updateSchedule) and a referral reward (ReferralController) write identical
     * rows, and only the former should carry the Event Schedule credit in the guest footer.
     *
     * Null for Stripe subscribers, who are also identifiable by plan_expires being nulled on every
     * Stripe path - but the guest layout re-checks hasActiveEnterpriseSubscription() at render time
     * rather than trusting this column alone.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('plan_source', 20)->nullable()->after('plan_expires');
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('plan_source');
        });
    }

    /**
     * Tag the plans that are already live.
     *
     * Deliberately NOT derived from the audit log, which looks like the precise source because
     * AdminController logs every manual plan change: `audit:prune` deletes entries after 90 days
     * (scheduled in both routes/console.php and AppController), while a granted plan can sit on a
     * far-future plan_expires indefinitely. Most existing grants therefore have no audit row left.
     * The referrals table is never pruned.
     *
     * Set-based rather than plucking ids into PHP: on a production roles table the id list would
     * be large enough to risk max_allowed_packet and a long metadata lock.
     */
    private function backfill(): void
    {
        $today = now()->format('Y-m-d');
        $referralWindow = now()->subDays(30);

        DB::transaction(function () use ($today, $referralWindow) {
            // Every plan currently carried by the columns rather than by Stripe starts out as a
            // hand grant. Excluding roles that have ever held a subscription row is deliberately
            // conservative - the render-time gate re-checks Stripe anyway, so this only has to be
            // right about WHICH non-Stripe path granted the plan, and erring towards "untagged"
            // can never brand someone wrongly.
            DB::table('roles')
                ->where('plan_type', '!=', 'free')
                ->whereNotNull('plan_expires')
                ->where('plan_expires', '>=', $today)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('subscriptions')
                        ->whereColumn('subscriptions.role_id', 'roles.id');
                })
                ->update(['plan_source' => 'admin']);

            // Then correct the ones a referral reward is currently paying for. A reward runs 30
            // days (ReferralController), so an older credit is not what the role holds today.
            //
            // This is the one case where the backfill cannot match the runtime rule: at runtime
            // ReferralController preserves an existing 'admin', but here there is no way to tell
            // a referral that extended an admin grant from one that created the plan outright, so
            // a stacked grant is tagged 'referral' and shows no credit.
            DB::table('roles')
                ->where('plan_source', 'admin')
                ->whereExists(function ($query) use ($referralWindow) {
                    $query->select(DB::raw(1))
                        ->from('referrals')
                        ->whereColumn('referrals.credited_role_id', 'roles.id')
                        ->where('referrals.status', 'credited')
                        ->where('referrals.credited_at', '>=', $referralWindow);
                })
                ->update(['plan_source' => 'referral']);
        });
    }
};
