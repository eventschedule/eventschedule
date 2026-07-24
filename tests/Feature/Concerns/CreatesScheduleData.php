<?php

namespace Tests\Feature\Concerns;

use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Group;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Shared helpers for Feature tests that need a schedule + event + tickets.
 *
 * Feature tests run with app.is_testing=true, so plan gates are bypassed and
 * routes are path-based (AP: /..., guest: /{subdomain}/...).
 */
trait CreatesScheduleData
{
    protected function createOwner(bool $admin = false): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'timezone' => 'America/New_York',
        ]);

        if ($admin) {
            $user->is_admin = true;
            $user->save();
        }

        return $user;
    }

    protected function createRole(User $user, string $type = 'venue', array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = 'test'.strtolower(Str::random(10));
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = $attrs['name'] ?? 'Test Schedule';
        // Avoid example.com — the hosted RoleUpdateRequest rejects it via NoFakeEmail.
        $role->email = $attrs['email'] ?? 'schedule@gmail.com';
        $role->timezone = $attrs['timezone'] ?? 'America/New_York';
        // Claimed roles (verified email + owner) are required for guest pages to render.
        $role->email_verified_at = $attrs['email_verified_at'] ?? now();
        // Default to enterprise so feature tests exercise the feature, not the plan gate.
        $role->plan_type = $attrs['plan_type'] ?? 'enterprise';
        $role->plan_expires = $attrs['plan_expires'] ?? now()->addYear()->format('Y-m-d');

        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    protected function createEvent(Role $role, array $attrs = []): Event
    {
        $event = new Event;
        $event->user_id = $attrs['user_id'] ?? $role->user_id;
        $event->name = $attrs['name'] ?? 'Test Event';
        // Noon UTC keeps the UTC calendar date equal to the local (non-UTC) date.
        $event->starts_at = $attrs['starts_at'] ?? Carbon::now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s');
        $event->duration = $attrs['duration'] ?? 2;
        $event->is_draft = $attrs['is_draft'] ?? false;

        foreach ($attrs as $key => $value) {
            if (in_array($key, ['name', 'starts_at', 'duration', 'is_draft'])) {
                continue;
            }
            $event->{$key} = $value;
        }

        if (! $event->slug) {
            $event->slug = Str::slug($event->name).'-'.strtolower(Str::random(4));
        }

        $event->save();

        $event->roles()->attach($role->id, [
            'is_accepted' => $attrs['is_accepted'] ?? true,
            'group_id' => $attrs['group_id'] ?? null,
        ]);

        return $event->fresh();
    }

    protected function createTicket(Event $event, array $attrs = []): Ticket
    {
        $ticket = new Ticket;
        $ticket->event_id = $event->id;
        $ticket->type = $attrs['type'] ?? 'General';
        $ticket->quantity = $attrs['quantity'] ?? 50;
        $ticket->price = $attrs['price'] ?? 0;
        $ticket->is_addon = $attrs['is_addon'] ?? false;
        $ticket->is_pass = $attrs['is_pass'] ?? false;

        foreach ($attrs as $key => $value) {
            if (in_array($key, ['type', 'quantity', 'price', 'is_addon', 'is_pass'])) {
                continue;
            }
            $ticket->{$key} = $value;
        }

        $ticket->save();

        return $ticket->fresh();
    }

    protected function createSale(Event $event, Role $role, array $attrs = [], ?Ticket $ticket = null, int $qty = 1): Sale
    {
        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = $attrs['name'] ?? 'Buyer Name';
        $sale->email = $attrs['email'] ?? 'buyer@example.com';
        $sale->event_date = $attrs['event_date'] ?? Carbon::parse($event->starts_at)->format('Y-m-d');
        $sale->status = $attrs['status'] ?? 'paid';
        $sale->payment_method = $attrs['payment_method'] ?? 'stripe';
        $sale->payment_amount = $attrs['payment_amount'] ?? 0;
        $sale->secret = $attrs['secret'] ?? Str::random(32);

        foreach ($attrs as $key => $value) {
            if (in_array($key, ['name', 'email', 'event_date', 'status', 'payment_method', 'payment_amount', 'secret'])) {
                continue;
            }
            $sale->{$key} = $value;
        }

        $sale->save();

        if ($ticket) {
            $seats = [];
            for ($i = 1; $i <= $qty; $i++) {
                $seats[$i] = null; // null = not checked in
            }

            $saleTicket = new SaleTicket;
            $saleTicket->sale_id = $sale->id;
            $saleTicket->ticket_id = $ticket->id;
            $saleTicket->quantity = $qty;
            $saleTicket->seats = json_encode($seats);
            $saleTicket->save();
        }

        return $sale->fresh();
    }

    /**
     * Appointment type. Defaults to a free 30-min "cash" type open Mon-Fri 09:00-17:00
     * (weekly_windows keys are "0"=Sunday.."6"=Saturday, wall-clock in the role timezone).
     */
    protected function createAppointmentType(Role $role, array $attrs = []): AppointmentType
    {
        $type = new AppointmentType;
        $type->role_id = $role->id;
        $type->name = $attrs['name'] ?? '30 Minute Meeting';
        $type->slug = $attrs['slug'] ?? 'meeting-'.strtolower(Str::random(6));
        $type->duration_minutes = $attrs['duration_minutes'] ?? 30;
        $type->weekly_windows = $attrs['weekly_windows'] ?? [
            '0' => [],
            '1' => [['start' => '09:00', 'end' => '17:00']],
            '2' => [['start' => '09:00', 'end' => '17:00']],
            '3' => [['start' => '09:00', 'end' => '17:00']],
            '4' => [['start' => '09:00', 'end' => '17:00']],
            '5' => [['start' => '09:00', 'end' => '17:00']],
            '6' => [],
        ];
        $type->price = $attrs['price'] ?? 0;
        $type->payment_method = $attrs['payment_method'] ?? 'cash';
        $type->is_active = $attrs['is_active'] ?? true;

        foreach ($attrs as $key => $value) {
            if (in_array($key, ['name', 'slug', 'duration_minutes', 'weekly_windows', 'price', 'payment_method', 'is_active'])) {
                continue;
            }
            $type->{$key} = $value;
        }

        $type->save();

        return $type->fresh();
    }

    /** Sub-schedule (Group) attached to the given schedule. */
    protected function createGroup(Role $role, array $attrs = []): Group
    {
        $group = $role->groups()->create([
            'name' => $attrs['name'] ?? 'Test Group',
            'slug' => $attrs['slug'] ?? 'test-group-'.strtolower(Str::random(4)),
        ] + $attrs);

        return $group->fresh();
    }

    /** Curator schedule owned by the given user. */
    protected function createCurator(User $user, array $attrs = []): Role
    {
        return $this->createRole($user, 'curator', $attrs);
    }

    /** Venue schedule with a full street address (populates the *_normalized dedup columns). */
    protected function createVenueWithAddress(User $user, array $attrs = []): Role
    {
        return $this->createRole($user, 'venue', array_merge([
            'name' => 'Address Venue',
            'address1' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
            'country_code' => 'us',
        ], $attrs));
    }

    /** Weekly recurring event (all days on unless days_of_week passed). */
    protected function createRecurringEvent(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, array_merge([
            'days_of_week' => '1111111',
            'recurring_frequency' => 'weekly',
        ], $attrs));
    }

    /** Connect a user to a schedule at the given membership level. */
    protected function followRole(User $user, Role $role, string $level = 'follower'): void
    {
        $user->roles()->attach($role->id, ['level' => $level, 'created_at' => now()]);
    }

    /** Full guest event URL path: /{subdomain}/{slug}/{encodedId}[/{date}] */
    protected function guestEventUrl(Role $role, Event $event, ?string $date = null): string
    {
        $params = [
            'subdomain' => $role->subdomain,
            'slug' => $event->slug,
            'id' => UrlUtils::encodeId($event->id),
        ];

        if ($date) {
            $params['date'] = $date;

            return route('event.view_guest_full', $params);
        }

        return route('event.view_guest_with_id', $params);
    }
}
