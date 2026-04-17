<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportAttendeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'ticket_id' => ['required', 'string'],
            'send_emails' => ['sometimes', 'boolean'],
            'default_status' => ['required', 'in:paid,unpaid'],

            'entries' => ['required', 'array', 'min:1', 'max:5000'],
            'entries.*.name' => ['nullable', 'string', 'max:255'],
            'entries.*.email' => ['required', 'email', 'max:255'],
            'entries.*.phone' => ['nullable', 'string', 'max:50'],
            'entries.*.ticket_id' => ['nullable', 'string'],
            'entries.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'entries.*.amount' => ['nullable', 'numeric', 'min:0'],
            'entries.*.status' => ['nullable', 'in:paid,unpaid'],

            'entries.*.custom_values' => ['sometimes', 'array'],
            'entries.*.custom_values.*' => ['nullable', 'string', 'max:255'],

            'entries.*.ticket_custom_values' => ['sometimes', 'array'],
            'entries.*.ticket_custom_values.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
