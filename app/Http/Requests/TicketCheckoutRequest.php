<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Rules\ValidTurnstile;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\FormRequest;

class TicketCheckoutRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('tickets')) {
            $this->merge([
                'tickets' => array_filter($this->tickets, fn ($qty) => $qty > 0),
            ]);
        }

        if (is_array($this->input('legs'))) {
            $this->merge([
                'legs' => array_map(function ($leg) {
                    if (isset($leg['tickets']) && is_array($leg['tickets'])) {
                        $leg['tickets'] = array_filter($leg['tickets'], fn ($qty) => $qty > 0);
                    }

                    return $leg;
                }, $this->input('legs')),
            ]);
        }
    }

    /** True when the buyer is paying for several events in one checkout. */
    private function isMultiLeg(): bool
    {
        return is_array($this->input('legs')) && $this->input('legs') !== [];
    }

    /**
     * Validation fails before the controller runs, so the cart's marker has to be set here too -
     * otherwise a refused cart checkout looks, to the panel, exactly like the single-event form on
     * the same page failing, and the panel would either steal those errors or ignore its own.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->isMultiLeg()) {
            session()->flash('cart_submitted', true);
        }

        parent::failedValidation($validator);
    }

    /**
     * The events this checkout touches. One for a single-event purchase, several for a cart.
     *
     * Rules that are per-event on the form - the phone field in particular - become the union
     * across legs: if any event asks, the buyer is asked; if any requires, it is required.
     */
    private function checkoutEvents(): \Illuminate\Support\Collection
    {
        $ids = $this->isMultiLeg()
            ? collect($this->input('legs'))->pluck('event_id')
            : collect([$this->event_id]);

        // Scalars only: this runs while the rules are still being built, so nothing has rejected a
        // crafted legs[0][event_id][]=x yet, and UrlUtils::decodeId() is untyped - an array reaches
        // Sqids and throws a TypeError, turning a bad request into a 500.
        return $ids->filter(fn ($id) => is_string($id) || is_numeric($id))
            ->map(fn ($id) => Event::find(UrlUtils::decodeId($id)))
            ->filter()
            ->values();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'cf-turnstile-response' => [new ValidTurnstile],
            // The single-event path reaches Carbon the same way the legs do, via
            // canSellTickets(), so it needs the same scalar guard.
            'event_date' => ['nullable', 'string', 'max:10'],
            // Presence means "pay monthly". Everything about whether that is ALLOWED is decided
            // server-side in checkout(); this only keeps a hand-posted value to a scalar.
            'installments' => ['nullable', 'string', 'max:1'],

        ];

        // The mandate has to be genuinely given, not merely displayed. The checkbox used to carry
        // no name at all, so it was never submitted and only disabled the button in the browser -
        // while createPlan() stamped mandate_accepted_at unconditionally. That made the one
        // artefact defending a disputed charge a record we could not stand behind.
        //
        // Applied conditionally rather than as `nullable|accepted|required_with`: `nullable` does
        // NOT exempt `accepted`, so that form rejected every ordinary checkout in the app.
        if ($this->filled('installments')) {
            $rules['installments_consent'] = ['accepted'];
        }

        $events = $this->checkoutEvents();
        $event = $events->first();

        if ($events->contains(fn ($candidate) => $candidate->ask_phone)) {
            $rules['phone'] = $events->contains(fn ($candidate) => $candidate->require_phone)
                ? ['required', 'string', 'max:50']
                : ['nullable', 'string', 'max:50'];
        }

        if ($this->isMultiLeg()) {
            // Capped: checkoutEvents() below resolves one Event per leg, and checkout() then
            // row-locks every leg's tickets inside a single transaction. Unbounded, one request
            // could hold ticket locks across arbitrarily many events for as long as it ran.
            $rules['legs'] = ['required', 'array', 'min:1', 'max:20'];
            $rules['legs.*.event_id'] = ['required', 'string'];
            $rules['legs.*.tickets'] = ['required', 'array', 'min:1'];
            $rules['legs.*.tickets.*'] = ['integer', 'min:1'];
            // Nullable: a one-time event legitimately posts none, and resolveCheckoutLegs()
            // falls back to the event's own start date. Typed, because it is handed straight to
            // Carbon by canSellTickets() -> getStartDateTime() BEFORE checkout()'s try block, so
            // an array here was an uncaught TypeError. resolveCheckoutLegs already guards
            // event_id against exactly this; event_date was missed.
            $rules['legs.*.event_date'] = ['nullable', 'string', 'max:10'];
            $rules['legs.*.promo_code'] = ['nullable', 'string', 'max:50'];
        } else {
            // Payment link mode: tickets are selected on Invoice Ninja, not here. It cannot appear
            // in a cart at all - cartEligibilityError() refuses Invoice Ninja outright.
            $isPaymentLink = $event
                && $event->payment_method === 'invoiceninja'
                && $event->user->invoiceninja_mode === 'payment_link';

            if (! $isPaymentLink) {
                $rules['tickets'] = ['required', 'array'];
                $rules['tickets.*'] = ['integer', 'min:1'];
            }
        }

        $rules['addons'] = ['nullable', 'array'];
        $rules['addons.*'] = ['integer', 'min:0'];

        $rules['gift_card_code'] = ['nullable', 'string', 'max:20'];

        if (! auth()->user() && $this->create_account && config('app.hosted')) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:'.User::class, new NoFakeEmail];
            $rules['password'] = ['required', 'string', 'min:8'];
            $rules['terms'] = ['accepted'];
        }

        // Individual tickets: validate guest data
        if ($event && $event->individual_tickets && $this->has('guests') && is_array($this->guests) && count($this->guests) > 1) {
            $rules['guests.*.name'] = ['required', 'string', 'max:255'];
            $rules['guests.*.email'] = ['required', 'string', 'email', 'max:255'];
            if ($event->ask_phone) {
                $rules['guests.*.phone'] = $event->require_phone
                    ? ['required', 'string', 'max:50']
                    : ['nullable', 'string', 'max:50'];
            }
        }

        return $rules;
    }
}
