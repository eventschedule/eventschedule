<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\ActivationNudge;
use App\Models\Role;
use App\Services\DemoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Emails schedules that activated and then stalled.
 *
 * SendOnboardingNudges stops dead at whereDoesntHave('roles'), and so does everything else:
 * the dashboard "Get Started" panel is gated on having no schedules, and the step indicator
 * ends at "create event". The product went silent exactly when someone became interesting.
 *
 * The 2026-08-30 growth export is what this is aimed at. Of 438 schedules that publish an
 * event, 144 ever create a ticket type and 27 ever take money - and a schedule that has sold
 * in the last six months is a real paying customer 55.6% of the time against 0.47% for one
 * that has never sold. Selling is the whole conversion mechanism and nothing pointed at it.
 *
 * Each key is an INDEPENDENT trigger, not a stage: a schedule can go idle without ever having
 * reached the tickets state. Delivery is recorded per (role, key) in schedule_nudges, where the
 * unique index is the claim - see the migration.
 */
class SendActivationNudges extends Command
{
    protected $signature = 'app:send-activation-nudges
        {--apply : Send the emails. Without this the command only reports.}
        {--key= : Run one nudge only, for testing.}';

    protected $description = 'Nudge schedules that stalled after creating their first schedule';

    /**
     * Every trigger is bounded at BOTH ends, and that is the load-bearing part.
     *
     * A lower bound stops us nagging someone who is mid-task. The UPPER bound is what stops the
     * first run emailing the entire historical base: 226 schedules have never had an event and
     * 542 are dormant, so an unbounded no_event or idle query is a mailshot to every account
     * this app has ever had. SendOnboardingNudges learned the same lesson with MAX_AGE_DAYS.
     *
     * The existing backlog is reached by the dashboard next-steps panel instead, which nobody
     * has to receive.
     */
    private const WINDOWS = [
        // Schedule created between 1 and 14 days ago, still with no event at all.
        'no_event' => ['min_hours' => 24, 'max_days' => 14],
        // First paid sale landed in the last 7 days. Congratulating someone on a sale from
        // last year reads as a bug, not a nudge.
        'first_sale' => ['max_days' => 7],
        // Most recent event sat this far in the past. Two separate windows so idle_60 cannot
        // also match everything idle_30 already covered.
        'idle_30' => ['min_days' => 30, 'max_days' => 60],
        'idle_60' => ['min_days' => 60, 'max_days' => 90],
    ];

    /** Ceiling on one run, so a backlog drains over several passes instead of in one burst. */
    private function batch(): int
    {
        return max(1, (int) config('usage.activation_nudge_batch', 200));
    }

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return 0;
        }

        $apply = (bool) $this->option('apply');
        $only = $this->option('key');
        $budget = $this->batch();
        $sent = 0;

        foreach ($this->nudges() as $key => $resolver) {
            if ($budget <= 0) {
                break;
            }

            if ($only && $only !== $key) {
                continue;
            }

            foreach ($resolver($budget) as $role) {
                if ($budget <= 0) {
                    break;
                }

                if (! $apply) {
                    $this->line("  would send {$key} for {$role->name} to {$role->user->email}");
                    $sent++;
                    $budget--;

                    continue;
                }

                // Claim BEFORE sending. routes/console.php and AppController::translateData
                // hold different mutexes, so a concurrent run can otherwise read the same rows
                // and email everyone twice. insertOrIgnore against the unique index is atomic,
                // so exactly one runner claims each (role, key).
                $claimed = DB::table('schedule_nudges')->insertOrIgnore([
                    'role_id' => $role->id,
                    'nudge_key' => $key,
                    'created_at' => now(),
                ]);

                if ($claimed === 0) {
                    continue;
                }

                $budget--;

                try {
                    // Queued, and in the recipient's own language. Sending inline blocks the
                    // scheduled chain (this also runs inside a web request), and Mail::to()
                    // with a bare address renders in the CLI locale.
                    SendQueuedEmail::dispatch(
                        new ActivationNudge($role, $key),
                        $role->user->email,
                        $role->id,
                        $role->user->language_code ?? app()->getLocale()
                    );

                    $this->info("Sent {$key} nudge for {$role->name}.");
                    $sent++;
                } catch (\Exception $e) {
                    // The claim STANDS, for the same reason it does in SendOnboardingNudges:
                    // the row is the only record that this nudge was attempted, and deleting it
                    // would let a concurrent runner send the same email again on the next tick.
                    // "Sent at most once" is worth more than retrying one nudge.
                    $this->error("Failed {$key} nudge for {$role->name}: {$e->getMessage()}");
                    Log::error('Failed to send activation nudge', [
                        'role_id' => $role->id,
                        'nudge_key' => $key,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->newLine();
        $this->info($apply ? "Activation nudges: {$sent} sent." : "DRY RUN - {$sent} would be sent. Re-run with --apply.");

        return 0;
    }

    /**
     * Ordered by how much the nudge is worth if it lands, so a run that hits the batch ceiling
     * spends it on the tickets and payment asks rather than on idle reminders.
     */
    private function nudges(): array
    {
        return [
            'no_ticket_type' => fn (int $limit) => $this->dueForNoTicketType($limit),
            'no_gateway' => fn (int $limit) => $this->dueForNoGateway($limit),
            'first_sale' => fn (int $limit) => $this->dueForFirstSale($limit),
            'no_event' => fn (int $limit) => $this->dueForNoEvent($limit),
            'idle_30' => fn (int $limit) => $this->dueForIdle('idle_30', $limit),
            'idle_60' => fn (int $limit) => $this->dueForIdle('idle_60', $limit),
        ];
    }

    /**
     * Every nudge shares this: a live schedule, a real owner who still wants email, and no
     * delivery of this key already recorded.
     */
    private function base(string $key)
    {
        return Role::query()
            ->with('user')
            ->where('is_deleted', false)
            ->whereNotNull('user_id')
            // Demo data is not a customer. Both halves are needed: the shared demo account owns
            // the seeded schedules, and demo-* subdomains are handed out per visitor.
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            // is_subscribed is the account-wide email opt-out the unsubscribe link sets.
            ->whereHas('user', fn ($q) => $q->where('is_subscribed', true)
                ->whereNotNull('email')
                ->where('email', '!=', DemoService::DEMO_EMAIL))
            ->whereNotExists(function ($q) use ($key) {
                $q->select(DB::raw(1))
                    ->from('schedule_nudges')
                    ->whereColumn('schedule_nudges.role_id', 'roles.id')
                    ->where('schedule_nudges.nudge_key', $key);
            })
            ->orderBy('id');
    }

    /** Events on this schedule that the public can actually see. */
    private function publicEvents($query)
    {
        return $query->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false);
    }

    /** A schedule created recently that still has nothing on its page. */
    private function dueForNoEvent(int $limit)
    {
        $w = self::WINDOWS['no_event'];

        return $this->base('no_event')
            ->where('created_at', '<=', now()->subHours($w['min_hours']))
            ->where('created_at', '>=', now()->subDays($w['max_days']))
            ->whereDoesntHave('events')
            ->limit($limit)->get();
    }

    /**
     * The one that matters most: a page with something upcoming on it and no way to buy.
     *
     * Scoped to an UPCOMING public event, so it reaches someone with a date still to sell
     * rather than someone whose season ended. No plan gate - the free plan sells 25 paid
     * tickets a month and takes no platform fee, which is the point of the email.
     */
    private function dueForNoTicketType(int $limit)
    {
        return $this->base('no_ticket_type')
            ->whereHas('events', fn ($q) => $this->publicEvents($q)->where('events.starts_at', '>=', now()))
            ->whereDoesntHave('events.tickets', fn ($q) => $q->where('tickets.is_deleted', false))
            ->limit($limit)->get();
    }

    /** Paid tickets set up, but nothing connected to take the money with. */
    private function dueForNoGateway(int $limit)
    {
        return $this->base('no_gateway')
            ->whereHas('events.tickets', fn ($q) => $q->where('tickets.is_deleted', false)
                ->where('tickets.price', '>', 0))
            ->whereHas('user', fn ($q) => $q->whereNull('stripe_account_id')
                ->whereNull('payfast_merchant_id')
                ->whereNull('invoiceninja_api_key'))
            ->limit($limit)->get();
    }

    /** First money in, recently enough that saying so still makes sense. */
    private function dueForFirstSale(int $limit)
    {
        $w = self::WINDOWS['first_sale'];

        return $this->base('first_sale')
            ->whereHas('events.sales', fn ($q) => $q->where('sales.status', 'paid')
                ->where('sales.is_deleted', false)
                ->whereNotIn('sales.payment_method', ['rsvp', 'import'])
                ->where('sales.paid_at', '>=', now()->subDays($w['max_days'])))
            ->limit($limit)->get();
    }

    /**
     * Published before, nothing upcoming, and the last thing on the page fell inside this
     * window. The window is what keeps idle_60 from re-sending to everyone idle_30 reached,
     * and keeps the first run from mailing every dormant schedule in the database.
     */
    private function dueForIdle(string $key, int $limit)
    {
        $w = self::WINDOWS[$key];

        return $this->base($key)
            ->whereHas('events', fn ($q) => $this->publicEvents($q)
                ->whereBetween('events.starts_at', [now()->subDays($w['max_days']), now()->subDays($w['min_days'])]))
            ->whereDoesntHave('events', fn ($q) => $q->where('events.starts_at', '>=', now()))
            ->limit($limit)->get();
    }
}
