<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CarpoolOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', 'in:to_event,from_event,round_trip'],
            'city' => ['required', 'string', 'max:255'],
            'departure_time' => ['nullable', 'date_format:H:i'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            'total_spots' => ['required', 'integer', 'min:1', 'max:10'],
            'note' => ['nullable', 'string', 'max:500'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
