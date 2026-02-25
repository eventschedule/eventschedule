<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventPollStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:500'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:200'],
        ];
    }
}
