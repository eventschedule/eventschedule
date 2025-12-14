<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\VenueRoom;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
        return [
            'flyer_image' => ['nullable', 'image', 'max:2500'],
            'flyer_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'flyer_media_variant_id' => ['nullable', 'integer', 'exists:media_asset_variants,id'],
            'slug' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'venue_room_id' => ['nullable', 'string', function ($attribute, $value, $fail) {
                $roomId = UrlUtils::decodeId($value);

                if (! $roomId) {
                    return;
                }

                $venueId = $this->input('venue_id') ? UrlUtils::decodeId($this->input('venue_id')) : null;
                $room = VenueRoom::find($roomId);

                if (! $room) {
                    $fail(__('messages.invalid_room_selection'));
                    return;
                }

                if ($venueId && $room->venue_id !== $venueId) {
                    $fail(__('messages.room_not_in_venue'));
                }
            }],
            'event_password' => [
                Rule::requiredIf(function () {
                    return (bool) $this->input('tickets_enabled')
                        && (!empty($this->input('event_url')))
                        && is_array($this->input('tickets'))
                        && count($this->input('tickets')) > 0;
                }),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
 