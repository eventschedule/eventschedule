<?php

namespace App\Http\Requests;

use App\Rules\NoFakeEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ScheduleTransferRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            // The form asks "Remove me from this schedule", pre-checked, so the stored
            // keep_previous_owner is its inverse. Kept in the request as the field the
            // user actually saw.
            'remove_me' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $email = strtolower(trim((string) $this->input('email')));

            if ($email !== '' && $email === strtolower((string) $this->user()?->email)) {
                $validator->errors()->add('email', __('messages.schedule_transfer_to_self'));
            }
        });
    }
}
