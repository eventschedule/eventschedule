<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesEventCustomFields;
use Illuminate\Foundation\Http\FormRequest;

class EventCreateRequest extends FormRequest
{
    use ValidatesEventCustomFields;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return array_merge([
            'name' => ['required', 'string', 'max:255'],

            'flyer_image_url' => ['image', 'max:2500'],

            'addons.*.url' => ['nullable', 'url', 'max:2000'],
            'addon_image_data.*' => ['nullable', 'string', 'max:3500000'],

            'venue_email' => ['nullable', 'email', 'max:255'],
            'venue_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
            'members.*.phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
        ], $this->eventCustomFieldRules());
    }

    public function attributes(): array
    {
        return $this->eventCustomFieldAttributes();
    }
}
