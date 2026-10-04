<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventParseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'event_details' => ['nullable', 'string', 'max:10000'],
            'details_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
            // A link to read instead of text or an image. Only EventController::parse() looks at
            // these, and only for an editor: the guest submit form posts to guestParse(), which
            // never reads them. (That is not "never fetches": a ticket link the model finds in a
            // guest's text is still opened for its preview, through the same address guard.)
            // 'page' asks for the whole page to be read by the model even when it publishes event
            // data of its own.
            'source_url' => ['nullable', 'string', 'max:2048'],
            'source_mode' => ['nullable', 'in:auto,page'],
        ];
    }
}
