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

        $event = Event::find(UrlUtils::decodeId($this->event_id));

        if ($event && $event->ask_phone) {
            $rules['phone'] = $event->require_phone
                ? ['required', 'string', 'max:50']
                : ['nullable', 'string', 'max:50'];
        }

        // Payment link mode: tickets are selected on Invoice Ninja, not here
        $isPaymentLink = $event
            && $event->payment_method === 'invoiceninja'
            && $event->user->invoiceninja_mode === 'payment_link';

        if (! $isPaymentLink) {
            $rules['tickets'] = ['required', 'array'];
            $rules['tickets.*'] = ['integer', 'min:1'];
        }

        $rules['addons'] = ['nullable', 'array'];
        $rules['addons.*'] = ['integer', 'min:0'];

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
