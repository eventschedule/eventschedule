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

        return $ids->filter()
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
        ];

        $events = $this->checkoutEvents();
        $event = $events->first();

        if ($events->contains(fn ($candidate) => $candidate->ask_phone)) {
            $rules['phone'] = $events->contains(fn ($candidate) => $candidate->require_phone)
                ? ['required', 'string', 'max:50']
                : ['nullable', 'string', 'max:50'];
        }

        if ($this->isMultiLeg()) {
            $rules['legs'] = ['required', 'array', 'min:1'];
            $rules['legs.*.event_id'] = ['required', 'string'];
            $rules['legs.*.tickets'] = ['required', 'array', 'min:1'];
            $rules['legs.*.tickets.*'] = ['integer', 'min:1'];
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
