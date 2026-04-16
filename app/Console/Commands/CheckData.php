<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Sale;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CheckData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-data {check? : The specific check to run} {--fix : Attempt to fix the detected issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check data and optionally fix issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $errors = [];
        $shouldFix = $this->option('fix');
        $check = $this->argument('check');

        if (! $check || $check === 'role-ownership') {
            $this->checkRoleOwnership($errors, $shouldFix);
        }
        if (! $check || $check === 'event-slugs' || $check === 'event-subdomains') {
            $this->checkEvents($errors, $check);
        }
        if (! $check || $check === 'creator-roles') {
            $this->checkEventCreatorRoles($errors, $shouldFix);
        }
        if (! $check || $check === 'encryption') {
            $this->checkEncryption($errors);
        }
        if (! $check || $check === 'orphaned-events') {
            $this->checkOrphanedEvents($errors);
        }
        if (! $check || $check === 'unverified-roles') {
            $this->checkUnverifiedClaimedRoles($errors);
        }
        if (! $check || $check === 'role-subdomains') {
            $this->checkRoleSubdomains($errors);
        }
        if (! $check || $check === 'sales-analytics') {
            $this->checkSalesAnalytics($errors, $shouldFix);
        }
        if (! $check || $check === 'ticket-sold') {
            $this->checkTicketSold($errors, $shouldFix);
        }
        if (! $check || $check === 'rsvp-sold') {
            $this->checkRsvpSold($errors, $shouldFix);
        }

        if (count($errors) > 0) {
            $this->error('Errors found:');
            $this->info(implode("\n", $errors));
        } else {
            $this->info('No errors found');
        }
    }

    private function checkRoleOwnership(array &$errors, bool $shouldFix): void
    {
        $roles = Role::with('members')->where('is_deleted', false)->get();

        foreach ($roles as $role) {
            if ($role->isClaimed() && ! $role->owner()) {
                $error = 'No owner for role '.$role->id.': '.$role->name;

                if (! $shouldFix) {
                    $errors[] = $error;
                } else {
                    $this->error("Attempting to fix role {$role->id}");

                    $roleUser = RoleUser::where('role_id', $role->id)->first();

                    if ($roleUser && $roleUser->user_id == $role->user_id) {
                        $this->info('Found matching role_user: correcting...');
                        $roleUser->level = 'owner';
                        $roleUser->save();
                    } else {
                        $errors[] = $error;
                    }
                }
            }
        }
    }

    private function checkEvents(array &$errors, ?string $check): void
    {
        $events = Event::with(['venue', 'roles', 'user'])->get();

        foreach ($events as $event) {
            if ((! $check || $check === 'event-slugs') && ! $event->slug) {
                $errors[] = 'No slug for event '.$event->id.': '.$event->name.' ('.$event->user->id.': '.$event->user->name.')';
            }

            if ((! $check || $check === 'event-subdomains')) {
                $data = $event->getGuestUrlData();

                if (! $data['subdomain']) {
                    $error = 'No subdomain for event '.$event->id.': '.$event->name.' ('.$event->user->id.': '.$event->user->name.') - ';

                    foreach ($event->roles as $role) {
                        $error .= $role->name.' ('.$role->type.'), ';
                    }

                    $error = rtrim($error, ', ');

                    $errors[] = $error;
                }
            }
        }
    }

    private function checkEventCreatorRoles(array &$errors, bool $shouldFix): void
    {
        $events = Event::with('roles')->whereNotNull('creator_role_id')->get();

        foreach ($events as $event) {
            $roleIds = $event->roles->pluck('id')->toArray();

            if (! in_array($event->creator_role_id, $roleIds)) {
                $error = 'Creator role not in event roles for event '.$event->id.': '.$event->name;

                if ($shouldFix) {
                    $claimedRole = $event->roles->first(function ($role) {
                        return $role->isClaimed();
                    });

                    if ($claimedRole) {
                        $event->creator_role_id = $claimedRole->id;
                        $event->save();
                        $this->info("Fixed creator_role_id for event {$event->id} to role {$claimedRole->id}");
                    } else {
                        $errors[] = $error;
                    }
                } else {
                    $errors[] = $error;
                }
            }
        }
    }

    private function checkEncryption(array &$errors): void
    {
        $users = DB::table('users')
            ->whereNotNull('invoiceninja_api_key')
            ->orWhereNotNull('invoiceninja_webhook_secret')
            ->get(['id', 'name', 'email', 'invoiceninja_api_key', 'invoiceninja_webhook_secret']);

        foreach ($users as $user) {
            foreach (['invoiceninja_api_key', 'invoiceninja_webhook_secret'] as $field) {
                if ($user->$field === null) {
                    continue;
                }

                try {
                    Crypt::decryptString($user->$field);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    $errors[] = "Invalid encrypted {$field} for user {$user->id}: {$user->name} ({$user->email})";
                }
            }
        }

        $roles = DB::table('roles')
            ->whereNotNull('email_settings')
            ->orWhereNotNull('caldav_settings')
            ->get(['id', 'email_settings', 'caldav_settings']);

        foreach ($roles as $role) {
            foreach (['email_settings', 'caldav_settings'] as $field) {
                if ($role->$field === null) {
                    continue;
                }

                try {
                    Crypt::decryptString($role->$field);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    $owner = DB::table('role_user')
                        ->join('users', 'users.id', '=', 'role_user.user_id')
                        ->where('role_user.role_id', $role->id)
                        ->where('role_user.level', 'owner')
                        ->first(['users.name', 'users.email']);

                    $ownerName = $owner->name ?? 'N/A';
                    $ownerEmail = $owner->email ?? 'N/A';
                    $errors[] = "Invalid encrypted {$field} for role {$role->id} (owner: {$ownerName}, {$ownerEmail})";
                }
            }
        }
    }

    private function checkOrphanedEvents(array &$errors): void
    {
        $events = Event::doesntHave('roles')->get();

        foreach ($events as $event) {
            $errors[] = 'No roles for event '.$event->id.': '.$event->name;
        }
    }

    private function checkUnverifiedClaimedRoles(array &$errors): void
    {
        $roles = Role::whereNotNull('user_id')
            ->whereNull('email_verified_at')
            ->whereNull('phone_verified_at')
            ->get();

        foreach ($roles as $role) {
            $errors[] = 'Unverified role with owner '.$role->id.': '.$role->name;
        }
    }

    private function checkRoleSubdomains(array &$errors): void
    {
        $roles = Role::where('is_deleted', false)->where(function ($query) {
            $query->whereNull('subdomain')->orWhere('subdomain', '');
        })->get();

        foreach ($roles as $role) {
            $errors[] = 'No subdomain for role '.$role->id.': '.$role->name;
        }
    }

    private function checkSalesAnalytics(array &$errors, bool $shouldFix): void
    {
        // Actual paid sales aggregated per event
        $actualData = DB::table('sales')
            ->select('event_id', DB::raw('COUNT(*) as sales_count'), DB::raw('COALESCE(SUM(payment_amount), 0) as revenue'))
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->where(function ($q) {
                $q->whereNull('group_id')
                    ->orWhereColumn('group_id', 'id');
            })
            ->groupBy('event_id')
            ->get()
            ->keyBy('event_id');

        // Actual promo sales aggregated per event
        $actualPromoData = DB::table('sales')
            ->select('event_id', DB::raw('COUNT(*) as promo_sales_count'), DB::raw('COALESCE(SUM(discount_amount), 0) as promo_discount_total'))
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->where('discount_amount', '>', 0)
            ->where(function ($q) {
                $q->whereNull('group_id')
                    ->orWhereColumn('group_id', 'id');
            })
            ->groupBy('event_id')
            ->get()
            ->keyBy('event_id');

        // Cached analytics aggregated per event
        $cachedData = DB::table('analytics_events_daily')
            ->select('event_id', DB::raw('COALESCE(SUM(sales_count), 0) as sales_count'), DB::raw('COALESCE(SUM(revenue), 0) as revenue'))
            ->groupBy('event_id')
            ->havingRaw('SUM(sales_count) > 0 OR SUM(revenue) > 0')
            ->get()
            ->keyBy('event_id');

        // Cached promo analytics aggregated per event
        $cachedPromoData = DB::table('analytics_events_daily')
            ->select('event_id', DB::raw('COALESCE(SUM(promo_sales_count), 0) as promo_sales_count'), DB::raw('COALESCE(SUM(promo_discount_total), 0) as promo_discount_total'))
            ->groupBy('event_id')
            ->havingRaw('SUM(promo_sales_count) > 0 OR SUM(promo_discount_total) > 0')
            ->get()
            ->keyBy('event_id');

        // Check all events that appear in any dataset
        $allEventIds = $actualData->keys()->merge($cachedData->keys())
            ->merge($actualPromoData->keys())->merge($cachedPromoData->keys())->unique();

        foreach ($allEventIds as $eventId) {
            $actual = $actualData->get($eventId);
            $cached = $cachedData->get($eventId);

            $actualCount = $actual ? (int) $actual->sales_count : 0;
            $actualRevenue = $actual ? round((float) $actual->revenue, 2) : 0;
            $cachedCount = $cached ? (int) $cached->sales_count : 0;
            $cachedRevenue = $cached ? round((float) $cached->revenue, 2) : 0;

            $actualPromo = $actualPromoData->get($eventId);
            $cachedPromo = $cachedPromoData->get($eventId);

            $actualPromoCount = $actualPromo ? (int) $actualPromo->promo_sales_count : 0;
            $actualPromoDiscount = $actualPromo ? round((float) $actualPromo->promo_discount_total, 2) : 0;
            $cachedPromoCount = $cachedPromo ? (int) $cachedPromo->promo_sales_count : 0;
            $cachedPromoDiscount = $cachedPromo ? round((float) $cachedPromo->promo_discount_total, 2) : 0;

            $salesMismatch = $actualCount !== $cachedCount || abs($actualRevenue - $cachedRevenue) > 0.01;
            $promoMismatch = $actualPromoCount !== $cachedPromoCount || abs($actualPromoDiscount - $cachedPromoDiscount) > 0.01;

            if ($salesMismatch) {
                $errors[] = "Sales analytics mismatch for event {$eventId}: actual={$actualCount} sales / \${$actualRevenue}, cached={$cachedCount} sales / \${$cachedRevenue}";
            }

            if ($promoMismatch) {
                $errors[] = "Promo analytics mismatch for event {$eventId}: actual={$actualPromoCount} promo sales / \${$actualPromoDiscount} discount, cached={$cachedPromoCount} promo sales / \${$cachedPromoDiscount} discount";
            }

            if ($shouldFix && ($salesMismatch || $promoMismatch)) {
                // Reset all analytics columns for this event
                DB::table('analytics_events_daily')
                    ->where('event_id', $eventId)
                    ->update(['sales_count' => 0, 'revenue' => 0, 'promo_sales_count' => 0, 'promo_discount_total' => 0]);

                // Re-aggregate all four columns from sales grouped by date
                $salesByDate = DB::table('sales')
                    ->select(
                        DB::raw('DATE(created_at) as sale_date'),
                        DB::raw('COUNT(*) as cnt'),
                        DB::raw('COALESCE(SUM(payment_amount), 0) as rev'),
                        DB::raw('SUM(CASE WHEN discount_amount > 0 THEN 1 ELSE 0 END) as promo_cnt'),
                        DB::raw('COALESCE(SUM(CASE WHEN discount_amount > 0 THEN discount_amount ELSE 0 END), 0) as promo_disc')
                    )
                    ->where('event_id', $eventId)
                    ->where('status', 'paid')
                    ->where('is_deleted', false)
                    ->where(function ($q) {
                        $q->whereNull('group_id')
                            ->orWhereColumn('group_id', 'id');
                    })
                    ->groupBy('sale_date')
                    ->get();

                foreach ($salesByDate as $row) {
                    DB::statement(
                        'INSERT INTO analytics_events_daily (event_id, date, sales_count, revenue, promo_sales_count, promo_discount_total)
                         VALUES (?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE sales_count = ?, revenue = ?, promo_sales_count = ?, promo_discount_total = ?',
                        [$eventId, $row->sale_date, $row->cnt, $row->rev, $row->promo_cnt, $row->promo_disc,
                            $row->cnt, $row->rev, $row->promo_cnt, $row->promo_disc]
                    );
                }

                $this->info("Fixed sales analytics for event {$eventId}");
            }
        }
    }

    private function checkTicketSold(array &$errors, bool $shouldFix): void
    {
        // Actual ticket quantities from sale_tickets joined with active sales
        $actualData = DB::table('sale_tickets')
            ->join('sales', 'sale_tickets.sale_id', '=', 'sales.id')
            ->select('sale_tickets.ticket_id', 'sales.event_date', DB::raw('SUM(sale_tickets.quantity) as total_sold'))
            ->whereIn('sales.status', ['paid', 'unpaid', 'amount_mismatch'])
            ->where('sales.is_deleted', false)
            ->groupBy('sale_tickets.ticket_id', 'sales.event_date')
            ->get();

        // Build lookup: ticket_id => [date => quantity]
        $actualByTicket = [];
        foreach ($actualData as $row) {
            $actualByTicket[$row->ticket_id][$row->event_date] = (int) $row->total_sold;
        }

        // Get all tickets that have a sold JSON value
        $tickets = Ticket::whereNotNull('sold')->where('sold', '!=', '')->get();

        // Also check tickets that have actual sales but no cached sold value
        $ticketIdsWithSales = array_keys($actualByTicket);
        $ticketIdsWithCache = $tickets->pluck('id')->toArray();
        $missingCacheIds = array_diff($ticketIdsWithSales, $ticketIdsWithCache);
        if (! empty($missingCacheIds)) {
            $tickets = $tickets->merge(Ticket::whereIn('id', $missingCacheIds)->get());
        }

        foreach ($tickets as $ticket) {
            $cachedSold = $ticket->sold ? json_decode($ticket->sold, true) : [];
            $actualSold = $actualByTicket[$ticket->id] ?? [];

            // Compare all dates from both sides
            $allDates = array_unique(array_merge(array_keys($cachedSold), array_keys($actualSold)));

            foreach ($allDates as $date) {
                $cachedQty = $cachedSold[$date] ?? 0;
                $actualQty = $actualSold[$date] ?? 0;

                if ((int) $cachedQty !== $actualQty) {
                    $errors[] = "Ticket sold mismatch for ticket {$ticket->id} on {$date}: actual={$actualQty}, cached={$cachedQty}";
                }
            }

            if ($shouldFix) {
                $newSold = ! empty($actualSold) ? json_encode($actualSold) : null;
                $currentSold = ! empty($cachedSold) ? json_encode($cachedSold) : null;

                if ($newSold !== $currentSold) {
                    $ticket->sold = $newSold;
                    $ticket->save();
                    $this->info("Fixed sold data for ticket {$ticket->id}");
                }
            }
        }
    }

    private function checkRsvpSold(array &$errors, bool $shouldFix): void
    {
        // Actual RSVP counts from paid RSVP sales
        $actualData = DB::table('sales')
            ->select('event_id', 'event_date', DB::raw('COUNT(*) as rsvp_count'))
            ->where('payment_method', 'rsvp')
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->groupBy('event_id', 'event_date')
            ->get();

        // Build lookup: event_id => [date => count]
        $actualByEvent = [];
        foreach ($actualData as $row) {
            $actualByEvent[$row->event_id][$row->event_date] = (int) $row->rsvp_count;
        }

        // Get all events that have rsvp_sold data
        $events = Event::whereNotNull('rsvp_sold')->where('rsvp_sold', '!=', '')->get();

        // Also check events that have actual RSVP sales but no cached value
        $eventIdsWithSales = array_keys($actualByEvent);
        $eventIdsWithCache = $events->pluck('id')->toArray();
        $missingCacheIds = array_diff($eventIdsWithSales, $eventIdsWithCache);
        if (! empty($missingCacheIds)) {
            $events = $events->merge(Event::whereIn('id', $missingCacheIds)->get());
        }

        foreach ($events as $event) {
            $cachedRsvp = $event->rsvp_sold ? json_decode($event->rsvp_sold, true) : [];
            $actualRsvp = $actualByEvent[$event->id] ?? [];

            // Compare all dates from both sides
            $allDates = array_unique(array_merge(array_keys($cachedRsvp), array_keys($actualRsvp)));

            foreach ($allDates as $date) {
                $cachedCount = $cachedRsvp[$date] ?? 0;
                $actualCount = $actualRsvp[$date] ?? 0;

                if ((int) $cachedCount !== $actualCount) {
                    $errors[] = "RSVP sold mismatch for event {$event->id} on {$date}: actual={$actualCount}, cached={$cachedCount}";
                }
            }

            if ($shouldFix) {
                $newRsvp = ! empty($actualRsvp) ? json_encode($actualRsvp) : null;
                $currentRsvp = ! empty($cachedRsvp) ? json_encode($cachedRsvp) : null;

                if ($newRsvp !== $currentRsvp) {
                    $event->rsvp_sold = $newRsvp;
                    $event->save();
                    $this->info("Fixed rsvp_sold data for event {$event->id}");
                }
            }
        }
    }
}
