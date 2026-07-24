<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventVideoSubmitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'youtube_url' => ['required', 'string', 'url', 'max:500'],
            'event_part_id' => ['nullable', 'string'],
            'event_date' => ['nullable', 'string', 'date_format:Y-m-d'],
            // Guest submissions (no account) carry these; required-ness and the Turnstile
            // check live in EventController::guestSubmitterAttributes so failures come back
            // as a flash error rather than silently redirecting with no visible message.
            'guest_name' => ['nullable', 'string'],
            'guest_email' => ['nullable', 'string'],
        ];
    }
}
