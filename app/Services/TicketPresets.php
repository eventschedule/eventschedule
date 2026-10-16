<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketPreset;
use App\Models\TicketPresetSetting;
use App\Models\User;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Ticket presets (issue #130): named ticket setups a schedule's events start from, and the owner's
 * switch that has everyone else on the team pick one instead of typing prices.
 *
 * Who the switch binds is lockedFor(), and EventRepo::saveEvent() is where it is enforced, so the
 * event form, the API and every import obey it alike. A bound person's save never reaches the
 * ticket blocks with prices of their own: a preset's fields are laid over the request
 * (applyTo()), or the stored tickets are kept as they are.
 */
class TicketPresets
{
    /** The sales that mean somebody has a ticket: never an expired checkout, which is nobody's. */
    public const LIVE_SALE_STATUSES = ['unpaid', 'paid', 'amount_mismatch', 'refunded'];

    /** @return Collection<int, TicketPreset> */
    public static function for(?Role $role): Collection
    {
        if (! $role || ! $role->exists) {
            return collect();
        }

        return $role->ticketPresets()->get();
    }

    public static function defaultFor(?Role $role): ?TicketPreset
    {
        $presets = self::for($role);

        return $presets->firstWhere('is_default', true) ?? $presets->first();
    }

    /** A preset of this schedule by raw or encoded id, never another schedule's. */
    public static function find(?Role $role, $id): ?TicketPreset
    {
        if (! $role || $id === null || $id === '' || ! is_scalar($id)) {
            return null;
        }

        // An int is a raw id (a clone or template payload); a string, from a request, is always an
        // encoded one, even when it happens to be all digits.
        $raw = is_int($id) ? $id : UrlUtils::decodeId((string) $id);

        return $raw ? TicketPreset::where('role_id', $role->id)->find($raw) : null;
    }

    /** The switch is offered where a schedule can have a team: Enterprise on hosted, always on selfhost. */
    public static function teamSwitchOffered(Role $role): bool
    {
        return ! config('app.hosted') || $role->isEnterprise();
    }

    public static function teamUsesPresets(?Role $role): bool
    {
        return $role && $role->exists && (bool) $role->ticketPresetSetting->team_uses_presets;
    }

    /**
     * Whether this person's saves on this schedule's events take their tickets from a preset only.
     *
     * Bound: a signed-in owner or admin MEMBER of the schedule that owns the event, who is not its
     * owner, while the owner's switch is on and the schedule has presets. Nobody else: not a guest
     * submitting to a curator (saveEvent() runs for them too, with the schedule's owner standing
     * in), not a feed or an import with no one signed in, not another schedule's editor (a venue
     * editing a talent's event). Membership alone, not planAllowsTeamAccess(): a switch left on
     * through a downgrade keeps binding whoever can still save.
     */
    public static function lockedFor(?User $user, ?Role $owning): bool
    {
        if (! $user || ! $owning || ! $owning->exists || (int) $owning->user_id === (int) $user->id) {
            return false;
        }

        if (! self::teamUsesPresets($owning) || ! $owning->ticketPresets()->exists()) {
            return false;
        }

        return DB::table('role_user')
            ->where('role_id', $owning->id)
            ->where('user_id', $user->id)
            ->whereIn('level', ['owner', 'admin'])
            ->exists();
    }

    /** Who may change a schedule's presets: its owner, and its editors while the switch is off. */
    public static function mayEdit(?User $user, Role $role): bool
    {
        if (! $user || ! $role->exists) {
            return false;
        }

        if ((int) $role->user_id === (int) $user->id) {
            return true;
        }

        return ! self::teamUsesPresets($role) && $user->isEditor($role->subdomain);
    }

    /** How many orders hold a ticket to this event, on any date. */
    public static function liveSaleCount(Event $event): int
    {
        if (! $event->exists) {
            return 0;
        }

        return $event->sales()
            ->where('is_deleted', false)
            ->whereIn('status', self::LIVE_SALE_STATUSES)
            ->count();
    }

    /**
     * A stored event whose tickets a preset can no longer replace: somebody has a ticket, or a
     * seating plan maps its seats to the ticket types it has (a preset's types carry no bands).
     */
    public static function frozen(?Event $event): bool
    {
        if (! $event || ! $event->exists) {
            return false;
        }

        return (bool) $event->seating_plan_id || self::liveSaleCount($event) > 0;
    }

    /** The owner's name as the sentences say it, or "The schedule's owner" when there is none. */
    public static function ownerName(Role $role): string
    {
        $name = trim((string) $role->user?->name);

        return $name !== '' ? $name : __('messages.ticket_preset_owner_fallback');
    }

    /** The account a preset's payment would go to must be able to take it. */
    public static function assertPayable(TicketPreset $preset, ?User $payee, ?string $fallbackCurrency = null): void
    {
        if ($preset->effectiveKind() !== 'tickets') {
            return;
        }

        $method = $preset->paymentMethod();
        $available = payment_gateways()->availableFor($payee, $preset->currency($fallbackCurrency));

        if (! array_key_exists($method, $available)) {
            $label = payment_gateways()->get($method)?->label($payee) ?? $method;

            throw ValidationException::withMessages([
                'ticket_preset_id' => __('messages.ticket_preset_payment_unavailable', ['preset' => $preset->label(), 'method' => $label]),
            ]);
        }
    }

    /** Lay a preset's fields over a request, as the Tickets tab would have posted them. */
    public static function applyTo($request, TicketPreset $preset, ?string $fallbackCurrency = null): void
    {
        $request->merge($preset->toEventInput($fallbackCurrency));
    }

    /** Set a new event's ticket fields from a preset, for the form to open on (EventController::create()). */
    public static function applyToModel(Event $event, TicketPreset $preset, ?string $fallbackCurrency = null): array
    {
        $input = $preset->toEventInput($fallbackCurrency ?: $event->ticket_currency_code);

        $event->ticket_currency_code = $input['ticket_currency_code'];
        // A preset that names no method (an old default saved before methods were kept, or a
        // gateway since removed) leaves what the caller started the event on, which is
        // DEFAULT_PAYMENT_METHOD where it applies: what the old single default did.
        if (in_array($preset->settings['payment_method'] ?? null, payment_gateways()->selectableKeys(), true)) {
            $event->payment_method = $input['payment_method'];
        }
        foreach (TicketPreset::OPTION_FIELDS as $field => $default) {
            $event->{$field} = is_bool($default) ? (bool) $input[$field] : $input[$field];
        }
        $event->custom_fields = json_decode($input['custom_fields'], true) ?: null;

        // A preset with no recorded mode (the one copied from the old single default) leaves the
        // mode to the caller, which keeps today's rule: whatever the schedule's last event used.
        if ($preset->kind !== null) {
            $event->tickets_enabled = (bool) $input['tickets_enabled'];
            $event->rsvp_enabled = (bool) $input['rsvp_enabled'];
            $event->rsvp_limit = $input['rsvp_limit'];
        }

        // Every stored row as it was saved, a pass and what it covers included: the form a new event
        // opens on is the owner's to edit, and this is what the old single default restored.
        $rows = array_values(array_filter((array) ($preset->settings['tickets'] ?? []), 'is_array'));
        $event->tickets = collect($rows ? array_map(function (array $row) {
            $ticket = new Ticket;
            foreach ($row as $key => $value) {
                $ticket->{$key} = $value;
            }

            return $ticket;
        }, $rows) : [new Ticket]);

        $event->addons = array_map(function (array $addon) {
            $ticket = new Ticket;
            foreach ($addon as $key => $value) {
                $ticket->{$key} = $value;
            }
            $ticket->is_addon = true;

            return $ticket;
        }, (array) ($preset->settings['addons'] ?? []));

        $event->ticket_preset_id = $preset->id;

        return $input['promo_codes'];
    }

    /** The settings a preset keeps, read off an event's Tickets tab as saved. */
    public static function settingsFromEvent(Event $event): array
    {
        $event->loadMissing(['tickets', 'addons', 'promoCodes']);

        $settings = [
            'currency_code' => $event->ticket_currency_code,
            'payment_method' => $event->payment_method,
            'custom_fields' => $event->custom_fields,
            'rsvp_limit' => $event->rsvp_limit,
            // As the old "Save as default" kept them (Ticket::toClonePayload()), passes included, with
            // the cap per order, which that payload leaves out.
            'tickets' => $event->tickets->map(fn (Ticket $ticket) => $ticket->toClonePayload() + ['max_per_order' => $ticket->max_per_order])->values()->all(),
            'addons' => $event->addons->map(fn (Ticket $addon) => [
                'type' => $addon->type,
                'quantity' => $addon->quantity,
                'price' => $addon->price,
                'description' => $addon->description,
                'max_per_order' => $addon->max_per_order,
                'url' => $addon->url,
            ])->values()->all(),
            'promo_codes' => $event->promoCodes->map(fn ($code) => [
                'code' => $code->code,
                'type' => $code->type,
                'value' => $code->value,
                'max_uses' => $code->max_uses,
                'is_active' => $code->is_active,
            ])->values()->all(),
        ];

        foreach (array_keys(TicketPreset::OPTION_FIELDS) as $field) {
            $settings[$field] = $event->{$field};
        }

        return $settings;
    }

    public static function kindOf(Event $event): string
    {
        return $event->tickets_enabled ? 'tickets' : ($event->rsvp_enabled ? 'rsvp' : 'none');
    }

    /**
     * "Save these tickets as a preset" on the event form: a preset of the same name (ignoring case)
     * is replaced, a new name adds one. Written to the event's OWN schedule, not the one in the
     * address it was reached through.
     */
    public static function saveFromEvent(Role $role, Event $event, string $name, bool $makeDefault): TicketPreset
    {
        $name = mb_substr(trim($name), 0, 100);
        $presets = self::for($role);
        // No name: the preset the event stands on, else the default, as the old switch updated the
        // one default. Never another nameless preset on every save.
        $preset = $name !== ''
            ? $presets->first(fn (TicketPreset $p) => mb_strtolower($p->label()) === mb_strtolower($name))
            : ($presets->firstWhere('id', (int) $event->ticket_preset_id) ?? self::defaultFor($role));
        $wasEmpty = $presets->isEmpty();

        $settings = self::settingsFromEvent($event);

        if ($preset) {
            // What the event form does not show stays as the preset had it.
            $preset->settings = array_merge((array) $preset->settings, $settings);
            $preset->kind = self::kindOf($event);
            $preset->save();
        } else {
            $preset = TicketPreset::create([
                'role_id' => $role->id,
                'name' => $name !== '' ? $name : null,
                'kind' => self::kindOf($event),
                'is_default' => false,
                'sort_order' => (int) $presets->max('sort_order') + 1,
                'settings' => $settings,
            ]);
        }

        if ($makeDefault || $wasEmpty) {
            self::makeDefault($role, $preset);
        }

        if (! $event->ticket_preset_id || (int) $event->ticket_preset_id === (int) $preset->id) {
            DB::table('events')->where('id', $event->id)->update(['ticket_preset_id' => $preset->id]);
        }

        return $preset;
    }

    public static function makeDefault(Role $role, TicketPreset $preset): void
    {
        TicketPreset::where('role_id', $role->id)->where('id', '!=', $preset->id)->update(['is_default' => false]);
        $preset->forceFill(['is_default' => true])->save();
    }

    /**
     * Save the list from Settings > Ticket presets. Rows are matched by their encoded id; a row the
     * page no longer has is removed. What the editor does not show (add-ons, promo codes, options a
     * preset was saved with from an event) is kept. The first row is the default when none is.
     *
     * @param  array<int, array<string, mixed>>  $rows  As validated by RoleUpdateRequest.
     */
    public static function saveFromSchedule(Role $role, array $rows, ?array $seen = null, ?string $defaultSeen = null): void
    {
        $existing = self::for($role)->keyBy('id');
        $kept = [];
        $defaultId = null;
        // The presets the page drew. One saved since from an event (another tab, another member)
        // was never on it, and a page that did not list it cannot have removed it. Null (a caller
        // that is not the page): every one.
        $seenIds = $seen === null ? null : array_map('intval', array_values(array_filter(array_map(fn ($id) => is_scalar($id) ? UrlUtils::decodeId((string) $id) : null, $seen))));

        DB::transaction(function () use ($role, $rows, $existing, $seenIds, $defaultSeen, &$kept, &$defaultId) {
            foreach (array_values($rows) as $order => $row) {
                $raw = ! empty($row['id']) ? UrlUtils::decodeId((string) $row['id']) : null;
                $preset = $raw ? $existing->get($raw) : null;

                $settings = $preset ? (array) $preset->settings : self::copiedSettings($role, $row['copy_event'] ?? null);
                $kind = in_array($row['kind'] ?? null, TicketPreset::KINDS, true) ? $row['kind'] : ($preset?->kind);

                $settings['currency_code'] = strtoupper((string) ($row['currency_code'] ?? ($settings['currency_code'] ?? '')));
                $settings['payment_method'] = (string) ($row['payment_method'] ?? ($settings['payment_method'] ?? 'cash'));
                // Only what the row sent: the editor sends instructions and a limit only for the way of
                // signing up that has them, and the preset copied from the old default has none.
                if (array_key_exists('payment_instructions', $row)) {
                    $settings['payment_instructions'] = trim((string) $row['payment_instructions']) !== '' ? (string) $row['payment_instructions'] : null;
                }
                if (array_key_exists('rsvp_limit', $row)) {
                    $settings['rsvp_limit'] = ! empty($row['rsvp_limit']) ? (int) $row['rsvp_limit'] : null;
                }

                if (array_key_exists('tickets', $row)) {
                    $stored = (array) ($settings['tickets'] ?? []);
                    $types = [];
                    foreach (array_values((array) $row['tickets']) as $ticket) {
                        // A type keeps what the editor does not show (its description, custom fields,
                        // a volume discount), matched by the place it came from (`src`), never by its
                        // place in the list, which removing a type above it moves.
                        $src = isset($ticket['src']) && $ticket['src'] !== '' ? (int) $ticket['src'] : null;
                        $before = $src !== null && is_array($stored[$src] ?? null) && empty($stored[$src]['is_pass']) ? $stored[$src] : [];

                        $types[] = array_merge($before, [
                            'type' => isset($ticket['type']) && trim((string) $ticket['type']) !== '' ? trim((string) $ticket['type']) : null,
                            'price' => round((float) ($ticket['price'] ?? 0), 2),
                            'quantity' => isset($ticket['quantity']) && $ticket['quantity'] !== '' && (int) $ticket['quantity'] > 0 ? (int) $ticket['quantity'] : null,
                        ]);
                    }
                    // A pass the editor does not show stays as it was saved.
                    foreach ($stored as $storedType) {
                        if (is_array($storedType) && ! empty($storedType['is_pass'])) {
                            $types[] = $storedType;
                        }
                    }
                    $settings['tickets'] = $types;
                }

                $attributes = [
                    'name' => mb_substr(trim((string) ($row['name'] ?? '')), 0, 100) ?: null,
                    'kind' => $kind,
                    'sort_order' => $order,
                    'settings' => $settings,
                ];

                if ($preset) {
                    $preset->fill($attributes)->save();
                } else {
                    $preset = TicketPreset::create($attributes + ['role_id' => $role->id, 'is_default' => false]);
                }

                $kept[] = $preset->id;
                if (! empty($row['is_default'])) {
                    $defaultId = $preset->id;
                }
            }

            foreach ($existing as $id => $preset) {
                if (! in_array($id, $kept, true) && ($seenIds === null || in_array((int) $id, $seenIds, true))) {
                    self::forget($preset);
                }
            }

            // The default the page opened with, posted back unchanged, leaves the default where it
            // is now: it may have been moved since, from an event's "Use for new events".
            $defaultSeenId = $defaultSeen ? UrlUtils::decodeId($defaultSeen) : null;
            $current = TicketPreset::where('role_id', $role->id)->where('is_default', true)->value('id');
            if ($defaultSeenId && $defaultId === (int) $defaultSeenId && $current) {
                $defaultId = (int) $current;
            }

            $defaultId ??= $current ?? ($kept[0] ?? null);
            TicketPreset::where('role_id', $role->id)->update(['is_default' => false]);
            if ($defaultId) {
                TicketPreset::where('id', $defaultId)->update(['is_default' => true]);
            }
        });

        $role->unsetRelation('ticketPresets');
    }

    /** The first preset made on the settings page starts from the tickets of the schedule's newest event. */
    private static function copiedSettings(Role $role, $encodedEventId): array
    {
        if (! $encodedEventId) {
            return [];
        }

        $event = Event::where('creator_role_id', $role->id)->find(UrlUtils::decodeId((string) $encodedEventId));

        if (! $event) {
            return [];
        }

        $settings = self::settingsFromEvent($event);
        unset($settings['tickets'], $settings['currency_code'], $settings['payment_method'], $settings['payment_instructions'], $settings['rsvp_limit']);

        return $settings;
    }

    /** Remove a preset, and the link of every event that came from it. */
    public static function forget(TicketPreset $preset): void
    {
        DB::table('events')->where('ticket_preset_id', $preset->id)->update(['ticket_preset_id' => null]);
        $preset->delete();
    }

    public static function setTeamUsesPresets(Role $role, bool $on): void
    {
        TicketPresetSetting::updateOrCreate(['role_id' => $role->id], ['team_uses_presets' => $on]);
        $role->unsetRelation('ticketPresetSetting');
    }

    /**
     * What Settings > Ticket presets on the schedule form starts with (resources/js/components/
     * TicketPresetsEditor.vue). After a refused save the rows come back as they were posted, laid
     * over the stored ones, so nothing typed is lost and the row with the problem opens again.
     */
    public static function scheduleEditorState(Role $role, User $user): array
    {
        $owner = $role->user;
        $presets = self::for($role);
        $fallbackCurrency = strtoupper((string) $role->events()->whereNotNull('ticket_currency_code')->orderByDesc('events.id')->value('ticket_currency_code'));
        if (! in_array($fallbackCurrency, MoneyUtils::currencyCodes(), true)) {
            $fallbackCurrency = MoneyUtils::getCurrencyForCountry($role->country_code);
        }

        $uses = DB::table('events')
            ->whereIn('ticket_preset_id', $presets->pluck('id'))
            ->where(fn ($q) => $q->where('starts_at', '>=', now())->orWhere(fn ($r) => $r->whereNotNull('days_of_week')->where('days_of_week', '!=', '')))
            ->groupBy('ticket_preset_id')
            ->selectRaw('ticket_preset_id, COUNT(*) as uses')
            ->pluck('uses', 'ticket_preset_id');

        $rows = $presets->map(fn (TicketPreset $preset) => [
            'id' => $preset->encodeId(),
            'name' => (string) $preset->name,
            'label' => $preset->label(),
            'kind' => $preset->kind,
            'is_default' => (bool) $preset->is_default,
            'currency_code' => $preset->currency($fallbackCurrency),
            'payment_method' => $preset->paymentMethod(),
            'payment_instructions' => (string) ($preset->settings['payment_instructions'] ?? ''),
            'rsvp_limit' => $preset->rsvpLimit(),
            'tickets' => array_map(fn ($row) => ['src' => $row['src'], 'type' => $row['type'], 'price' => $row['price'], 'quantity' => $row['quantity']], $preset->ticketRows()),
            // Passes are kept as saved and not edited here (saveFromSchedule() puts them back).
            'passes' => count(array_filter((array) ($preset->settings['tickets'] ?? []), fn ($row) => is_array($row) && ! empty($row['is_pass']))),
            'uses' => (int) ($uses[$preset->id] ?? 0),
            'uses_text' => ($uses[$preset->id] ?? 0) > 0
                ? trans_choice('messages.ticket_preset_uses', (int) $uses[$preset->id], ['count' => (int) $uses[$preset->id], 'preset' => $preset->label()])
                : '',
        ])->values()->all();

        $old = session()->getOldInput('ticket_presets_submitted') ? (array) session()->getOldInput('ticket_presets', []) : null;
        // After a refused save, what the page showed when it was FIRST opened: the redrawn rows
        // leave out a preset that was removed there, and a seen list built from them would keep it.
        $seen = $old !== null ? array_values(array_filter((array) session()->getOldInput('ticket_presets_seen', []), 'is_scalar')) : null;
        $defaultSeen = $old !== null ? (string) session()->getOldInput('ticket_presets_default_seen', '') : null;
        if ($old !== null) {
            $stored = collect($rows)->keyBy('id');
            $errors = session('errors');
            $rows = array_values(array_map(function ($row, $index) use ($stored, $errors) {
                $before = ! empty($row['id']) ? ($stored[$row['id']] ?? []) : [];

                return array_merge($before, [
                    'id' => (string) ($row['id'] ?? ''),
                    'name' => (string) ($row['name'] ?? ''),
                    'kind' => $row['kind'] ?? ($before['kind'] ?? null),
                    'is_default' => ! empty($row['is_default']),
                    'currency_code' => $row['currency_code'] ?? ($before['currency_code'] ?? null),
                    'payment_method' => $row['payment_method'] ?? ($before['payment_method'] ?? null),
                    'payment_instructions' => (string) ($row['payment_instructions'] ?? ''),
                    'rsvp_limit' => $row['rsvp_limit'] ?? null,
                    'tickets' => array_values((array) ($row['tickets'] ?? ($before['tickets'] ?? []))),
                    'copy_event' => $row['copy_event'] ?? '',
                    'failed' => $errors && collect($errors->getBag('default')->keys())->contains(fn ($key) => str_starts_with($key, "ticket_presets.{$index}.")),
                ]);
            }, $old, array_keys($old)));
        }

        // The first preset starts from the tickets of the schedule's newest event, so an owner who
        // already sells does not type them again.
        $copyFrom = null;
        if ($presets->isEmpty()) {
            $newest = Event::where('creator_role_id', $role->id)->with('tickets')->orderByDesc('id')->first();
            if ($newest && ($newest->tickets_enabled || $newest->rsvp_enabled)) {
                $copyFrom = [
                    'kind' => self::kindOf($newest),
                    'currency_code' => $newest->ticket_currency_code ?: $fallbackCurrency,
                    'payment_method' => $newest->payment_method ?: 'cash',
                    'payment_instructions' => (string) $newest->payment_instructions,
                    'rsvp_limit' => $newest->rsvp_limit,
                    'tickets' => $newest->tickets->reject(fn (Ticket $ticket) => (bool) $ticket->is_pass)->map(fn (Ticket $ticket) => ['type' => $ticket->type, 'price' => (float) $ticket->price, 'quantity' => $ticket->quantity])->values()->all(),
                    'copy_event' => UrlUtils::encodeId($newest->id),
                    'copied_note' => __('messages.ticket_preset_copied_from', ['event' => $newest->translatedName() ?? $newest->name]),
                ];
            }
        }

        $currencies = collect(json_decode((string) file_get_contents(base_path('storage/currencies.json')), true) ?: [])
            ->map(fn ($currency) => ['code' => $currency['value'], 'label' => $currency['label']])->values();

        $methods = collect(payment_gateways()->configuredFor($owner))
            ->filter(fn ($driver) => $driver->isSelectableForEvents())
            ->map(fn ($driver, $key) => ['key' => $key, 'label' => $driver->label($owner)])
            ->values()->all();

        return [
            'presets' => $rows,
            'canEdit' => self::mayEdit($user, $role),
            'copyFrom' => $copyFrom,
            'defaults' => [
                'currency_code' => $fallbackCurrency,
                'payment_method' => payment_gateways()->defaultMethodFor($owner, $fallbackCurrency),
            ],
            'currencies' => $currencies->all(),
            'methods' => $methods,
            'instructionMethods' => array_values(array_filter(payment_gateways()->selectableKeys(), fn ($key) => payment_gateways()->usesPaymentInstructions($key))),
            'symbols' => $currencies->mapWithKeys(fn ($currency) => [$currency['code'] => MoneyUtils::symbol($currency['code'])])->all(),
            'zeroDecimal' => $currencies->pluck('code')->filter(fn ($code) => MoneyUtils::decimalsFor($code) === 0)->values()->all(),
            'planNote' => config('app.hosted') && ! $role->canSellPaidTickets() ? __('messages.ticket_preset_plan_note') : '',
            'switchOffered' => (int) $role->user_id === (int) $user->id && self::teamSwitchOffered($role),
            'switchSaved' => self::teamUsesPresets($role),
            'seen' => $seen,
            'defaultSeenId' => $defaultSeen,
            't' => [
                'empty' => __('messages.ticket_presets_empty'),
                'default' => __('messages.ticket_preset_default'),
                'make_default' => __('messages.ticket_preset_make_default'),
                'edit' => __('messages.edit'),
                'remove' => __('messages.remove'),
                'name' => __('messages.name'),
                'how' => __('messages.ticket_preset_how'),
                'kind_rsvp' => __('messages.free_registration'),
                'kind_tickets' => __('messages.sell_tickets'),
                'kind_none' => __('messages.not_needed'),
                'no_kind' => __('messages.ticket_preset_no_kind'),
                'price' => __('messages.price'),
                'quantity' => __('messages.quantity'),
                'type' => __('messages.type'),
                'type_placeholder' => __('messages.ticket_type_placeholder'),
                'required' => __('messages.required'),
                'unlimited' => __('messages.unlimited'),
                'add_type' => __('messages.ticket_preset_add_type'),
                'price_help' => __('messages.ticket_preset_price_help'),
                'payment' => __('messages.payment'),
                'currency' => __('messages.currency'),
                'instructions' => __('messages.payment_instructions'),
                'limit' => __('messages.ticket_preset_limit'),
                'done' => __('messages.done'),
                'free' => __('messages.free'),
                'untitled' => __('messages.ticket_preset_untitled'),
                'price_needed' => __('messages.ticket_preset_price_needed'),
                'passes_only' => __('messages.ticket_preset_passes'),
                'saved_with' => __('messages.ticket_preset_saved_with_schedule'),
                'switch_needs_preset' => __('messages.ticket_preset_team_needs_presets'),
                'switch_needs_kind' => __('messages.ticket_preset_needs_kind', ['preset' => ':preset']),
                'switch_bar_on' => __('messages.ticket_presets_team_bar_on'),
                'switch_bar_off' => __('messages.ticket_presets_team_bar_off'),
            ],
        ];
    }

    /**
     * Everything the event form's Tickets tab needs about presets, for create() and edit() alike.
     *
     * $owning is the schedule whose presets these are: the event's own (ticketingRole()), never the
     * one in the address. $copiedFrom names a hand-set event a bound member's copy came from.
     */
    public static function formState(Role $owning, ?Event $event, User $user, ?string $copiedFrom = null): array
    {
        $isBooking = $event?->exists && $event->isAppointment();
        $presets = self::for($owning);
        $locked = ! $isBooking && $presets->isNotEmpty() && self::lockedFor($user, $owning);
        $current = $event?->ticket_preset_id ? $presets->firstWhere('id', (int) $event->ticket_preset_id) : null;
        $liveSales = $event?->exists ? self::liveSaleCount($event) : 0;
        $isOwner = (int) $owning->user_id === (int) $user->id;

        return [
            'locked' => $locked,
            'presets' => self::forForm($owning, $event, $locked, $presets),
            'current_id' => $current?->encodeId(),
            'current_label' => $current?->label(),
            'frozen' => $event?->exists && ($event->seating_plan_id || $liveSales > 0),
            'live_sales' => $liveSales,
            'stale' => $current && $event?->exists && ! $current->matches($event),
            'owner_name' => self::ownerName($owning),
            'is_owner' => $isOwner,
            // "None yet" is said only to an owner who has a team to bind; one working alone keeps
            // the bottom switch, which is where a preset is made from an event. Counted only then.
            'has_team' => $presets->isEmpty() && $isOwner
                && DB::table('role_user')->where('role_id', $owning->id)->whereIn('level', ['owner', 'admin'])->count() > 1,
            'may_edit' => self::mayEdit($user, $owning),
            'manage_url' => route('role.edit', ['subdomain' => $owning->subdomain]).'#settings-tab-tickets',
            'copied_from' => $copiedFrom,
            'event_tickets_line' => $event ? self::eventTicketsLine($event) : '',
            'event_types_line' => $event ? self::eventTypesLine($event) : '',
            'event_payment_line' => $event ? self::eventPaymentLine($event) : '',
        ];
    }

    /** "Entry €5 · Reduced €3 · no limit", "Free registration · 80 places", "Not needed": what an event has now. */
    public static function eventTicketsLine(Event $event): string
    {
        if ($event->rsvp_enabled && ! $event->tickets_enabled) {
            return $event->rsvp_limit
                ? __('messages.free_registration').' · '.trans_choice('messages.ticket_preset_places', (int) $event->rsvp_limit, ['count' => (int) $event->rsvp_limit])
                : __('messages.free_registration');
        }

        if (! $event->tickets_enabled) {
            return __('messages.not_needed');
        }

        $currency = $event->ticket_currency_code ?: 'USD';
        $tickets = collect($event->tickets)->filter(fn ($ticket) => $ticket->exists || $ticket->type || $ticket->price);
        $parts = $tickets->map(function ($ticket) use ($currency) {
            $price = (float) $ticket->price > 0 ? MoneyUtils::format($ticket->price, $currency) : __('messages.free');

            return $ticket->type ? $ticket->type.' '.$price : $price;
        })->all();

        $limited = $tickets->filter(fn ($ticket) => $ticket->quantity)->sum('quantity');
        $parts[] = $limited && $tickets->every(fn ($ticket) => $ticket->quantity)
            ? trans_choice('messages.ticket_preset_places', (int) $limited, ['count' => (int) $limited])
            : __('messages.ticket_preset_no_limit');

        return implode(' · ', $parts);
    }

    /** "Entry €5, Reduced €3": an event's ticket types and prices, as a tile says a preset's. */
    public static function eventTypesLine(Event $event): string
    {
        if (! $event->tickets_enabled) {
            return self::eventTicketsLine($event);
        }

        $currency = $event->ticket_currency_code ?: 'USD';

        return collect($event->tickets)
            ->filter(fn ($ticket) => $ticket->exists || $ticket->type || $ticket->price)
            ->map(function ($ticket) use ($currency) {
                $price = (float) $ticket->price > 0 ? MoneyUtils::format($ticket->price, $currency) : __('messages.free');

                return $ticket->type ? $ticket->type.' '.$price : $price;
            })
            ->implode(', ') ?: __('messages.free');
    }

    public static function eventPaymentLine(Event $event): string
    {
        if (! $event->tickets_enabled) {
            return '';
        }

        $label = payment_gateways()->get($event->payment_method ?: 'cash')?->label($event->user) ?? '';
        $instructions = trim((string) $event->payment_instructions);

        return trim($label.($instructions !== '' ? ' · '.$instructions : ''));
    }

    /**
     * What the event form needs to draw a schedule's presets: as chips for the owner and as tiles
     * for a bound team member. `input` is the Tickets tab a chip fills in; a bound member's page
     * is not sent it, since their save never carries ticket fields.
     */
    public static function forForm(Role $owning, ?Event $event, bool $locked, ?Collection $presets = null): array
    {
        $payee = $event?->exists ? $event->user : $owning->user;
        $fallbackCurrency = $event?->ticket_currency_code ?: MoneyUtils::getCurrencyForCountry($owning->country_code);

        return ($presets ?? self::for($owning))->map(function (TicketPreset $preset) use ($payee, $fallbackCurrency, $locked) {
            $usable = true;
            $reason = null;
            try {
                self::assertPayable($preset, $payee, $fallbackCurrency);
            } catch (ValidationException $e) {
                $usable = false;
                $reason = collect($e->errors())->flatten()->first();
            }

            $item = [
                'id' => $preset->encodeId(),
                'label' => $preset->label(),
                'kind' => $preset->effectiveKind(),
                'is_default' => (bool) $preset->is_default,
                'chip' => $preset->chipLabel($fallbackCurrency),
                'tile' => $preset->effectiveKind() === 'tickets' ? $preset->typesLine($fallbackCurrency) : $preset->tileLine($fallbackCurrency),
                'tickets_line' => $preset->effectiveKind() === 'tickets' ? $preset->typesLine($fallbackCurrency) : $preset->tileLine($fallbackCurrency),
                'payment_line' => $preset->effectiveKind() === 'tickets'
                    ? trim((payment_gateways()->get($preset->paymentMethod())?->label($payee) ?? '').(trim((string) ($preset->settings['payment_instructions'] ?? '')) !== '' ? ' · '.trim((string) $preset->settings['payment_instructions']) : ''))
                    : '',
                'payment_label' => payment_gateways()->get($preset->paymentMethod())?->label($payee) ?? $preset->paymentMethod(),
                'usable' => $usable,
                'reason' => $reason,
            ];

            if (! $locked) {
                $item['input'] = $preset->toEventInput($fallbackCurrency);
                $item['kind_set'] = $preset->kind !== null;
            }

            return $item;
        })->values()->all();
    }
}
