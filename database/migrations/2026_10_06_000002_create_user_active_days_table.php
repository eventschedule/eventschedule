<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Active users" used to be read off users.updated_at, which moves when calendar sync refreshes a
 * token or an onboarding email is stamped, and does not move when a person signs in and looks
 * around. These two tables replace that guess with a record (App\Services\ActiveDays).
 *
 * user_active_days: one row per account per day the person loaded a page of the app while signed in.
 * No IP address, no page, no count: only that the day happened. It is personal data, so it is in
 * the data export, goes with the account, and is pruned (PrunePersonalData::ACTIVE_DAY_DAYS).
 *
 * active_users_daily: the totals for each day, written before the account rows are pruned. No
 * personal data, kept for good, so a chart can reach back further than the account rows do.
 *
 * `counted` is false for a row this migration seeded from the security log, so the first weeks of
 * the chart are not blank. Such a row means "signed in, or created or edited an event, that day":
 * it misses anyone who stayed signed in and only looked, so it runs low, and the page says so.
 */
return new class extends Migration
{
    private const SIGN_INS = ['auth.login', 'auth.google_login', 'auth.facebook_login'];

    private const EDITS = ['event.create', 'event.update'];

    public function up(): void
    {
        // Each create is guarded, and that is the only reason a retry works: DDL commits on its
        // own in MySQL, so a run that dies while seeding (a deploy timeout, a selfhost updating
        // from the browser) leaves both tables behind and the migration unrecorded. Unguarded,
        // every later `migrate` would stop here on "table already exists".
        if (! Schema::hasTable('user_active_days')) {
            Schema::create('user_active_days', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->date('date');
                $table->boolean('counted')->default(true);

                $table->primary(['user_id', 'date']);
                $table->index('date');
            });
        }

        if (! Schema::hasTable('active_users_daily')) {
            Schema::create('active_users_daily', function (Blueprint $table) {
                $table->date('date')->primary();
                $table->unsignedInteger('active_7d');
                $table->unsignedInteger('active_30d');
                $table->unsignedInteger('organizers_7d');
                $table->boolean('counted')->default(true);
            });
        }

        $this->seedFromSecurityLog();
    }

    public function down(): void
    {
        Schema::dropIfExists('active_users_daily');
        Schema::dropIfExists('user_active_days');
    }

    /**
     * A plain read, then small inserts. Never INSERT ... SELECT: under InnoDB's default isolation
     * that holds shared locks on every audit_logs row it reads, and the old containers are still
     * writing to that table while this runs.
     *
     * A sign-in, or an event created or edited in a browser. EventRepo logs every create and
     * update through one call, whatever made it: an import or a scheduled job (the console's user
     * agent is "Symfony"), and the API or a webhook, whose client names itself. None of those is
     * the owner opening the app, so only a browser's edit counts here. This is narrower than the
     * growth payload's activity proxy (GrowthExportService::signupRows()), which asks whether the
     * owner did anything at all.
     *
     * The log is kept 90 days, so that is all there is. Its timestamps are PHP now() in the app
     * timezone, the clock ActiveDays::record() uses too.
     */
    private function seedFromSecurityLog(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        $days = DB::table('audit_logs')
            ->whereNotNull('user_id')
            ->where('created_at', '>=', now()->subDays(90))
            ->where(function ($query) {
                $query->whereIn('action', self::SIGN_INS)
                    ->orWhere(function ($edits) {
                        $edits->whereIn('action', self::EDITS)
                            ->where('user_agent', 'like', 'Mozilla/%');
                    });
            })
            ->selectRaw('user_id, DATE(created_at) as day')
            ->distinct()
            ->get();

        foreach ($days->chunk(1000) as $chunk) {
            // insertOrIgnore: an account deleted since the read fails the foreign key, and a second
            // run of a half-finished migration (see up()) meets its own rows. Neither is worth
            // stopping for.
            DB::table('user_active_days')->insertOrIgnore($chunk->map(fn ($row) => [
                'user_id' => $row->user_id,
                'date' => $row->day,
                'counted' => false,
            ])->all());
        }
    }
};
