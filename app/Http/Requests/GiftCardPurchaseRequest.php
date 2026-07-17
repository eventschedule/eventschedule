<?php

namespace App\Http\Requests;

use App\Rules\ValidTurnstile;
use Illuminate\Foundation\Http\FormRequest;

class GiftCardPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'amount' => ['required', 'numeric', 'gt:0'],
            'purchaser_name' => ['required', 'string', 'max:255'],
            'purchaser_email' => ['required', 'string', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
            'cf-turnstile-response' => [new ValidTurnstile],
        ];

        if (! $this->boolean('send_to_self')) {
            $rules['recipient_name'] = ['required', 'string', 'max:255'];
            $rules['recipient_email'] = ['required', 'string', 'email', 'max:255'];
        }

        return $rules;
    }
}
