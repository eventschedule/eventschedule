<?php

namespace App\Models;

use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A named ticket setup on a schedule (Edit schedule > Settings > Ticket presets) that an event's
 * Tickets tab starts from (issue #130). Read and written through App\Services\TicketPresets, which
 * also knows who a schedule's presets bind.
 *
 * `settings` keeps the shape `roles.default_tickets` had: currency_code, payment_method,
 * payment_instructions, the option fields, tickets[] (type, price, quantity, description,
 * custom_fields, volume_discount, max_per_order; a pass as Ticket::toClonePayload() wrote it),
 * addons[] and promo_codes[], plus rsvp_limit. Promo codes' ticket ids, which name another event's
 * ticket types, are never kept. A pass is kept but never laid over an event (ticketRows()).
 */
class TicketPreset extends Model
{
    public const KINDS = ['rsvp', 'tickets', 'none'];

    /** The option fields an event takes from a preset as they are, with what they read as when unset. */
    public const OPTION_FIELDS = [
        'payment_instructions' => null,
        'expire_unpaid_tickets' => 0,
        'installments_enabled' => false,
        'installment_count' => null,
        'installment_final_days_before' => 14,
        'installment_min_order_amount' => null,
        'ticket_notes' => null,
        'terms_url' => null,
        'total_tickets_mode' => 'individual',
        'ask_phone' => false,
        'require_phone' => false,
        'country_code_phone' => false,
        'individual_tickets' => false,
        'individual_ticket_fields' => false,
        'sell_after_start' => false,
        'show_unavailable_tickets' => false,
    ];

    protected $fillable = ['role_id', 'name', 'kind', 'is_default', 'sort_order', 'settings'];

    protected $casts = [
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function encodeId(): string
    {
        return UrlUtils::encodeId($this->id);
    }

    /** Its name, or "Usual tickets" for the one copied from the old single default, which had none. */
    public function label(): string
    {
        $name = trim((string) $this->name);

        return $name !== '' ? $name : __('messages.ticket_preset_usual');
    }

    /**
     * How people sign up. The preset copied from `default_tickets` never said, so it reads as
     * selling tickets when it holds any and as not needed otherwise; App\Services\TicketPresets
     * still treats its stored null as "keep the last event's mode" where that matters.
     */
    public function effectiveKind(): string
    {
        if (in_array($this->kind, self::KINDS, true)) {
            return $this->kind;
        }

        return $this->ticketRows() ? 'tickets' : 'none';
    }

    public function currency(?string $fallback = null): string
    {
        $code = strtoupper((string) ($this->settings['currency_code'] ?? ''));

        return in_array($code, MoneyUtils::currencyCodes(), true) ? $code : ($fallback ?: 'USD');
    }

    public function paymentMethod(): string
    {
        $method = (string) ($this->settings['payment_method'] ?? '');

        return in_array($method, payment_gateways()->selectableKeys(), true) ? $method : 'cash';
    }

    /**
     * The ticket types a preset sells by itself, each with `src`, its place in settings['tickets']
     * (what the settings editor posts back, so a type keeps what the editor does not show). A pass
     * is left out: its coverage and usage belong to the events it was set up for, and laid over
     * another event it would become a ticket that admits once. It stays in the settings, and the
     * owner's form still opens with it (TicketPresets::applyToModel()), as the old default did.
     *
     * @return array<int, array{src: int, type: ?string, price: float, quantity: ?int, description: ?string}>
     */
    public function ticketRows(): array
    {
        $rows = [];

        foreach ((array) ($this->settings['tickets'] ?? []) as $index => $row) {
            if (! is_array($row) || ! empty($row['is_pass'])) {
                continue;
            }

            $rows[] = [
                'src' => (int) $index,
                'type' => isset($row['type']) && trim((string) $row['type']) !== '' ? trim((string) $row['type']) : null,
                'price' => (float) ($row['price'] ?? 0),
                'quantity' => isset($row['quantity']) && $row['quantity'] !== '' && (int) $row['quantity'] > 0 ? (int) $row['quantity'] : null,
                'description' => isset($row['description']) && $row['description'] !== '' ? (string) $row['description'] : null,
                'custom_fields' => is_array($row['custom_fields'] ?? null) ? $row['custom_fields'] : null,
                'volume_discount' => is_array($row['volume_discount'] ?? null) ? $row['volume_discount'] : null,
                'max_per_order' => ! empty($row['max_per_order']) ? (int) $row['max_per_order'] : null,
            ];
        }

        return $rows;
    }

    public function rsvpLimit(): ?int
    {
        $limit = (int) ($this->settings['rsvp_limit'] ?? 0);

        return $limit > 0 ? $limit : null;
    }

    /** "€5", "€3 to €5", or "Free" when every type costs nothing. */
    public function priceRange(?string $fallbackCurrency = null): string
    {
        $prices = array_column($this->ticketRows(), 'price');

        if (! $prices || max($prices) <= 0) {
            return __('messages.free');
        }

        $currency = $this->currency($fallbackCurrency);
        $low = min($prices);
        $high = max($prices);

        return $low == $high
            ? MoneyUtils::format($high, $currency)
            : __('messages.ticket_preset_price_range', ['low' => MoneyUtils::format($low, $currency), 'high' => MoneyUtils::format($high, $currency)]);
    }

    /** What a tile says under its name: "Entry €5, Reduced €3", "Free registration · 80 places", "Not needed". */
    public function tileLine(?string $fallbackCurrency = null): string
    {
        return match ($this->effectiveKind()) {
            'rsvp' => $this->rsvpLimit()
                ? __('messages.free_registration').' · '.trans_choice('messages.ticket_preset_places', $this->rsvpLimit(), ['count' => $this->rsvpLimit()])
                : __('messages.free_registration'),
            'none' => __('messages.not_needed'),
            default => $this->typesLine($fallbackCurrency),
        };
    }

    /** "Entry €5, Reduced €3", or the price alone for one unnamed type. */
    public function typesLine(?string $fallbackCurrency = null): string
    {
        $currency = $this->currency($fallbackCurrency);
        $parts = [];

        foreach ($this->ticketRows() as $row) {
            $price = $row['price'] > 0 ? MoneyUtils::format($row['price'], $currency) : __('messages.free');
            $parts[] = $row['type'] ? $row['type'].' '.$price : $price;
        }

        return $parts ? implode(', ', $parts) : __('messages.free');
    }

    /** The settings list's line: "Sell tickets · Entry €5, Reduced €3 · Cash". */
    public function summary(?User $payee = null, ?string $fallbackCurrency = null): string
    {
        if ($this->effectiveKind() !== 'tickets') {
            return $this->tileLine($fallbackCurrency);
        }

        $method = payment_gateways()->get($this->paymentMethod());

        return implode(' · ', array_filter([
            __('messages.sell_tickets'),
            $this->typesLine($fallbackCurrency),
            $method?->label($payee),
        ]));
    }

    /** The chip on the owner's Tickets tab: "Regular · €3 to €5", or the name alone when nothing is sold. */
    public function chipLabel(?string $fallbackCurrency = null): string
    {
        return $this->effectiveKind() === 'tickets'
            ? $this->label().' · '.$this->priceRange($fallbackCurrency)
            : $this->label();
    }

    /**
     * Everything the Tickets tab posts, the way the form posts it, so that EventRepo::saveEvent()
     * can apply a preset by running its usual blocks on it. Every TICKET_PANEL_FIELDS field is
     * named: fill() keeps the stored value of any field that is absent, so a field left out would
     * keep the event's old value behind the preset (a link under "Tickets elsewhere", a coupon).
     * Those belong to one event and are emptied, as pressing "Not needed" empties them.
     */
    public function toEventInput(?string $fallbackCurrency = null): array
    {
        $kind = $this->effectiveKind();
        $settings = (array) $this->settings;
        $selling = $kind === 'tickets';

        $input = [
            'tickets_enabled' => $selling ? 1 : 0,
            'rsvp_enabled' => $kind === 'rsvp' ? 1 : 0,
            'rsvp_limit' => $kind === 'rsvp' ? $this->rsvpLimit() : null,
            'registration_url' => null,
            'ticket_price' => null,
            'coupon_code' => null,
            'coupon_discount_type' => 'fixed',
            'coupon_discount' => null,
            'ticket_currency_code' => $this->currency($fallbackCurrency),
            'payment_method' => $this->paymentMethod(),
            'seating_plan_id' => '',
            'addon_image_data' => [],
            'custom_fields' => json_encode(is_array($settings['custom_fields'] ?? null) ? $settings['custom_fields'] : new \stdClass),
        ];

        foreach (self::OPTION_FIELDS as $field => $default) {
            $value = $settings[$field] ?? $default;
            $input[$field] = is_bool($default) ? ($value ? 1 : 0) : $value;
        }
        $input['expire_unpaid_tickets_checkbox'] = (int) ($input['expire_unpaid_tickets'] ?? 0) > 0 ? 1 : 0;

        // Never a blank row to fill a gap: a blank row is a free ticket, which is what issue #130
        // was about. A preset whose only types are passes applies none here.
        $input['tickets'] = $selling ? array_map(fn (array $row) => [
            'type' => $row['type'],
            'price' => $row['price'],
            'quantity' => $row['quantity'],
            'description' => $row['description'],
            'custom_fields' => json_encode($row['custom_fields'] ?: new \stdClass),
            'volume_discount' => $row['volume_discount'] ? json_encode($row['volume_discount']) : '',
            'max_per_order' => $row['max_per_order'],
        ], $this->ticketRows()) : [];

        $input['addons'] = $selling ? array_values(array_filter(array_map(fn ($addon) => is_array($addon) && ! empty($addon['type']) ? [
            'type' => $addon['type'],
            'quantity' => $addon['quantity'] ?? null,
            'price' => $addon['price'] ?? null,
            'description' => $addon['description'] ?? null,
            'max_per_order' => $addon['max_per_order'] ?? null,
            'url' => $addon['url'] ?? null,
        ] : null, (array) ($settings['addons'] ?? [])))) : [];

        $input['promo_codes'] = $selling ? array_values(array_filter(array_map(fn ($code) => is_array($code) && ! empty($code['code']) ? [
            'code' => $code['code'],
            'type' => $code['type'] ?? 'percentage',
            'value' => $code['value'] ?? 0,
            'max_uses' => $code['max_uses'] ?? null,
            'is_active' => ! empty($code['is_active']) ? 1 : 0,
        ] : null, (array) ($settings['promo_codes'] ?? [])))) : [];

        return $input;
    }

    /**
     * Whether an event's tickets are still what this preset would give it: the same way of signing
     * up, currency, payment and every ticket type's name, price and quantity. Used to say "Regular,
     * older prices" after the owner changes a preset that events were saved with.
     */
    public function matches(Event $event): bool
    {
        $kind = $this->effectiveKind();
        $eventKind = $event->tickets_enabled ? 'tickets' : ($event->rsvp_enabled ? 'rsvp' : 'none');

        if ($kind !== $eventKind) {
            return false;
        }

        if ($kind === 'rsvp') {
            return (int) $this->rsvpLimit() === (int) $event->rsvp_limit;
        }

        if ($kind !== 'tickets') {
            return true;
        }

        if ($this->currency($event->ticket_currency_code) !== strtoupper((string) $event->ticket_currency_code)
            || $this->paymentMethod() !== (string) $event->payment_method
            || trim((string) ($this->settings['payment_instructions'] ?? '')) !== trim((string) $event->payment_instructions)) {
            return false;
        }

        $mine = array_map(fn ($row) => [(string) $row['type'], round($row['price'], 2), $row['quantity']], $this->ticketRows());
        $theirs = $event->tickets->reject(fn ($ticket) => (bool) $ticket->is_pass)->map(fn ($ticket) => [
            (string) $ticket->type,
            round((float) $ticket->price, 2),
            $ticket->quantity ? (int) $ticket->quantity : null,
        ])->all();

        $sort = fn (array $rows) => collect($rows)->sortBy(fn ($row) => json_encode($row))->values()->all();

        return $sort($mine) === $sort($theirs);
    }
}
