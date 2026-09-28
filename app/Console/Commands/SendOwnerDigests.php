<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\OwnerDigest;
use App\Models\AnalyticsDaily;
use App\Models\Event;
use App\Models\Role;
use App\Services\DemoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The weekly owner digest: last week's numbers and the coming week's dates, one email per owner
 * covering every schedule of theirs that is active.
 *
 * Retention is the problem it answers: 12 of the 401 schedules created up to 2026-02 had an event
 * in the last 90 days. Every other owner email is triggered by a single event (a sale, a request),
 * so a schedule running quietly heard nothing from us at all.
 *
 * Bounded like the activation nudges, because it reaches people who have not asked for anything:
 *  - only schedules with a public event in the last 90 days or an occurrence still to come, so it
 *    is never a mailshot to the 542 dormant schedules;
 *  - a schedule with nothing to report (no views, no sign-ups, no sales, nothing coming up) is
 *    left out, and an owner with no schedule left gets no email that week;
 *  - one email per owner per ISO week, claimed with a unique index before sending;
 *  - the owner's own weekly_digest notification setting (default on) and users.is_subscribed.
 *
 * Deliberately NOT scheduled yet, on either rail, like app:send-activation-nudges: run it by hand
 * (no flag is a dry run, --apply sends) and schedule it once a real pass has been read. The claim
 * is per ISO week, so once it runs daily the first run of each week (Monday) is the one that sends.
 */
class SendOwnerDigests extends Command
{
    protected $signature = 'app:send-owner-digests
        {--apply : Send the emails. Without it, print what would be sent}
        {--user= : Only this owner (user id)}';

    protected $description = 'Send each active schedule owner a weekly summary of their schedules';

    /** How many upcoming dates one schedule's section lists. */
    private const UPCOMING_LIMIT = 5;

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return self::SUCCESS;
        }

        $apply = (bool) $this->option('apply');
        $week = now()->format('o-\WW');
        $since = now()->subDays(7);
        $batch = max(1, (int) config('usage.owner_digest_batch', 500));

        $owners = $this->candidateRoles($week)->groupBy('user_id');
        $sent = 0;

        foreach ($owners as $userId => $roles) {
            if ($sent >= $batch) {
                $this->info("Batch limit of {$batch} reached; the rest are due on the next run.");
                break;
            }

            $user = $roles->first()->user;
            $locale = $user->language_code ?: config('app.locale');

            $sections = $roles
                ->filter(fn (Role $role) => $role->getEditorsWantingNotification('weekly_digest')->contains('id', $user->id))
                ->map(fn (Role $role) => $this->section($role, $since, $locale))
                ->filter()
                ->values()
                ->all();

            if (empty($sections)) {
                continue;
            }

            if (! $apply) {
                $this->line("would send {$user->email}: ".implode(', ', array_column($sections, 'name')));
                $sent++;

                continue;
            }

            // The claim, before the send. Stands if the dispatch throws: "only ever moves forward"
            // is what makes two scheduler rails safe.
            $claimed = DB::table('owner_digests')->insertOrIgnore([
                'user_id' => $user->id,
                'week' => $week,
                'schedules' => count($sections),
                'created_at' => now(),
            ]);

            if ($claimed === 0) {
                continue;
            }

            try {
                SendQueuedEmail::dispatch(
                    new OwnerDigest($user, $sections),
                    $user->email,
                    // Platform mailer, never a schedule's own SMTP: this is our email about their
                    // account, it must not count against their allowance, and a schedule's SMTP
                    // failure window would drop it after the claim was written.
                    null,
                    $locale
                );
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Failed to queue owner digest', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                $this->error("Failed for {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info(($apply ? 'Queued' : 'Would send')." {$sent} weekly digest(s) for {$week}.");

        return self::SUCCESS;
    }

    /** Active schedules whose owner can be emailed and has not had this week's digest. */
    private function candidateRoles(string $week): Collection
    {
        $public = fn ($q) => $q->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false);

        return Role::query()
            ->where('is_deleted', false)
            ->whereNotNull('user_id')
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->whereHas('user', fn ($u) => $u->where('is_subscribed', true)
                ->where('email', '!=', DemoService::DEMO_EMAIL)
                ->whereNotNull('email_verified_at'))
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('owner_digests')
                ->whereColumn('owner_digests.user_id', 'roles.user_id')
                ->where('owner_digests.week', $week))
            ->where(fn ($q) => $q
                ->whereHas('events', fn ($e) => $public($e)->where('events.starts_at', '>=', now()->subDays(90)))
                ->orWhereHas('events', fn ($e) => $public($e)->hasUpcomingOccurrence()))
            ->when($this->option('user'), fn ($q, $userId) => $q->where('user_id', (int) $userId))
            ->with('user')
            ->orderBy('user_id')
            ->orderBy('name')
            ->get();
    }

    /** One schedule's part of the email, or null when there is nothing to tell. */
    private function section(Role $role, Carbon $since, string $locale): ?array
    {
        $views = (int) AnalyticsDaily::forRoles([$role->id])
            ->inDateRange($since, now())
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));

        // accountOnlyFollowers(): a confirmed email subscriber also gets a follower row, and is
        // counted once, below, as a subscriber.
        $followers = $role->accountOnlyFollowers()->wherePivot('created_at', '>=', $since)->count();
        $subscribers = $role->subscribers()->confirmed()->where('confirmed_at', '>=', $since)->count();

        // Sales on events this schedule created: the money is the creator's. One row per order -
        // individual-ticket group rows point at their primary through group_id.
        $sales = fn () => DB::table('sales')
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->where('events.creator_role_id', $role->id)
            ->where('sales.status', 'paid')
            ->where('sales.is_deleted', false)
            ->where(fn ($q) => $q->whereNull('sales.group_id')->orWhereColumn('sales.group_id', 'sales.id'))
            ->where('sales.created_at', '>=', $since);

        $tickets = $sales()->whereNotIn('sales.payment_method', ['rsvp', 'import'])->count();
        $rsvps = $sales()->where('sales.payment_method', 'rsvp')->count();

        $upcoming = $this->upcoming($role, $locale);

        if ($views + $followers + $subscribers + $tickets + $rsvps === 0 && empty($upcoming)) {
            return null;
        }

        return [
            'name' => $role->name,
            'url' => app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false)),
            'views' => $views,
            'followers' => $followers,
            'subscribers' => $subscribers,
            'tickets' => $tickets,
            'rsvps' => $rsvps,
            'upcoming' => $upcoming,
        ];
    }

    /**
     * The next seven days' dates on this schedule's page, recurring series expanded, in the
     * schedule's own timezone (Event::adminOccurrenceDates()).
     */
    private function upcoming(Role $role, string $locale): array
    {
        $events = $role->events()
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->where('events.is_cancelled', false)
            ->wherePivot('is_accepted', true)
            ->hasUpcomingOccurrence()
            ->with('creatorRole')
            ->limit(50)
            ->get();

        $occurrences = [];

        foreach ($events as $event) {
            /** @var Event $event */
            $today = Carbon::parse($event->scheduleToday())->startOfDay();
            $last = $today->copy()->addDays(7);

            foreach ($event->adminOccurrenceDates(0, 7, 14) as $date) {
                $day = Carbon::parse($date);
                if ($day->betweenIncluded($today, $last)) {
                    $occurrences[] = ['sort' => $date, 'name' => $event->name, 'date' => $day->locale($locale)->translatedFormat('D j M')];
                }
            }
        }

        usort($occurrences, fn ($a, $b) => strcmp($a['sort'], $b['sort']));

        return array_map(
            fn ($o) => ['name' => $o['name'], 'date' => $o['date']],
            array_slice($occurrences, 0, self::UPCOMING_LIMIT)
        );
    }
}
