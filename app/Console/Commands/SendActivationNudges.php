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

        // At most ONE nudge per OWNER per run, whatever the batch allows.
        //
        // Every trigger is per-schedule and the only other ceiling is the global batch, so a
        // single owner could be handed a run's worth of mail at once: one account on this
        // install owns 37 schedules, 34 of them dormant with history. The nudges() order below
        // is what decides which one they get - tickets and payments before idle reminders.
        //
        // A skipped role is NOT claimed, so it is still due on the next run and simply drains
        // one at a time. For an owner with dozens of stalled schedules that is the intended
        // outcome, not a limitation to engineer around.
        $seenUsers = [];

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

                if (isset($seenUsers[$role->user_id])) {
                    continue;
                }

                if (! $apply) {
                    $seenUsers[$role->user_id] = true;
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

                $seenUsers[$role->user_id] = true;
                $budget--;

                try {
                    // Queued, and in the recipient's own language. Sending inline blocks the
                    // scheduled chain (this also runs inside a web request), and Mail::to()
                    // with a bare address renders in the CLI locale.
                    // roleId NULL, deliberately, so this goes out on the PLATFORM mailer.
                    // Passing the schedule id routes SendQueuedEmail through
                    // RoleMailerService::sendForRole(), which sends via the schedule's own SMTP
                    // when it has one - our message, from their domain - meters it as
                    // EMAIL_TICKET against their allowance, and worst, DROPS it silently while
                    // their SMTP is inside its 24h failure window. The claim above is already
                    // written by then, so that nudge would never be sent or retried.
                    // Same rule as SendOnboardingNudges and WindDownReminder.
                    SendQueuedEmail::dispatch(
                        new ActivationNudge($role, $key),
                        $role->user->email,
                        null,
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

    /**
     * Events this schedule is actually responsible for, mirroring Event::scopeManagedThrough().
     *
     * The `events` relation is a plain pivot, so without this a schedule is nudged about events
     * it does not own. Two cases, both real:
     *
     * 1. A DECLINE does not detach. EventController::decline() and ::uncurate() leave the row at
     *    is_accepted = false, so a venue that turned an event down would be told to put tickets
     *    on it.
     * 2. A CURATOR that merely lists someone else's event would be told to price it - and since
     *    the editor's Tickets panel follows canViewEventData(), which has a curator exception,
     *    the email would link to a page where they cannot act.
     *
     * The accepted branch is NOT narrowed to events the schedule created. A venue that accepted
     * a talent's event can manage its tickets and is exactly who this is for; scoping on
     * creator_role_id alone would silently stop nudging the main persona.
     */
    private function ownedEvents($query)
    {
        return $query->where(function ($q) {
            // My own schedule's event: mine whatever the pivot says.
            $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                // Somebody else's: only once this schedule accepted it, and never for a curator,
                // which owns only what it created.
                ->orWhere(fn ($w) => $w->where('event_role.is_accepted', true)
                    ->where('roles.type', '!=', 'curator'));
        });
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
            ->whereHas('events', fn ($q) => $this->ownedEvents($this->publicEvents($q))
                ->where('events.starts_at', '>=', now()))
            // Any ticket type on any event this schedule owns counts as "they know how".
            ->whereDoesntHave('events', fn ($q) => $this->ownedEvents($q)
                ->whereHas('tickets', fn ($t) => $t->where('tickets.is_deleted', false)))
            ->limit($limit)->get();
    }

    /**
     * Paid tickets set up, but nothing connected to take the money with.
     *
     * The "connected" test is payment_gateways()->connectedFor(), whose docblock says it is what
     * the event form keys this same nudge off. It is a PHP-side check, so the SQL only
     * pre-filters to schedules with a paid ticket type and the collection is filtered after -
     * that candidate set is under a hundred schedules on this install.
     *
     * Testing the credential columns by hand got it wrong in BOTH directions. It missed
     * users.payment_url, so an organizer taking money through a payment link was told to connect
     * something; and it read stripe_account_id, which is written when Connect onboarding STARTS,
     * where canAcceptStripePayments() reads stripe_completed_at, written only once Stripe
     * confirms charges_enabled - so someone who abandoned onboarding halfway could not take money
     * and was skipped by the very nudge meant for them.
     */
    private function dueForNoGateway(int $limit)
    {
        return $this->base('no_gateway')
            ->whereHas('events', fn ($q) => $this->ownedEvents($q)
                ->whereHas('tickets', fn ($t) => $t->where('tickets.is_deleted', false)
                    ->where('tickets.price', '>', 0)))
            ->limit($limit)->get()
            ->filter(fn (Role $role) => empty(payment_gateways()->connectedFor($role->user)))
            ->values();
    }

    /**
     * The FIRST money in, recently enough that saying so still makes sense.
     *
     * Both halves are load-bearing. "A paid sale in the last 7 days" alone is not "first": the
     * once-per-(role, key) claim only makes it read that way for a schedule that had never sold
     * when this shipped. On the first run every established seller qualifies, and the four
     * schedules carrying 89% of all ticket volume - 328, 127, 85 and 36 lifetime sales - would
     * each be congratulated on their first.
     *
     * So the second clause requires that no qualifying sale exists OUTSIDE the window. It repeats
     * every filter from the first, or an old RSVP row would disqualify a genuine first sale, and
     * it counts a null paid_at as old: Sale::saving() stamps it so nulls are legacy only, but
     * `paid_at < $cut` is false for null and an undated old sale would otherwise slip through.
     */
    private function dueForFirstSale(int $limit)
    {
        $cutoff = now()->subDays(self::WINDOWS['first_sale']['max_days']);

        $paidSale = fn ($q) => $q->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.payment_method', ['rsvp', 'import']);

        return $this->base('first_sale')
            ->whereHas('events', fn ($q) => $this->ownedEvents($q)
                ->whereHas('sales', fn ($s) => $paidSale($s)->where('sales.paid_at', '>=', $cutoff)))
            ->whereDoesntHave('events', fn ($q) => $this->ownedEvents($q)
                ->whereHas('sales', fn ($s) => $paidSale($s)
                    ->where(fn ($w) => $w->whereNull('sales.paid_at')
                        ->orWhere('sales.paid_at', '<', $cutoff))))
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
            ->whereHas('events', fn ($q) => $this->ownedEvents($this->publicEvents($q))
                ->whereBetween('events.starts_at', [now()->subDays($w['max_days']), now()->subDays($w['min_days'])]))
            // Anything upcoming at all means the page is working, draft included - someone
            // mid-edit is not idle.
            ->whereDoesntHave('events', fn ($q) => $this->ownedEvents($q)
                ->where('events.starts_at', '>=', now()))
            ->limit($limit)->get();
    }
}
