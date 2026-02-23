<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Rules\ValidTurnstile;
use Illuminate\Foundation\Http\FormRequest;

class TicketCheckoutRequest extends FormRequest
{
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
            'tickets' => ['required', 'array'],
            'tickets.*' => ['integer', 'min:1'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ];

        if (! auth()->user() && $this->create_account && config('app.hosted')) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:'.User::class, new NoFakeEmail];
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        return $rules;
    }
}
