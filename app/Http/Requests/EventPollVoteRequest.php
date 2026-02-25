<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventPollVoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'option_index' => ['required', 'integer', 'min:0', 'max:9'],
        ];
    }
}
