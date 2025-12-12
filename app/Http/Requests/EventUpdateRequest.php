<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'flyer_media_asset_id' => $this->normalizeNullableInteger($this->input('flyer_media_asset_id')),
            'flyer_media_variant_id' => $this->normalizeNullableInteger($this->input('flyer_media_variant_id')),
        ]);
    }

    protected function normalizeNullableInteger($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : $value;
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
