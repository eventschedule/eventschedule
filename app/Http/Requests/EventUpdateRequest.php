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
        ];
    }
}
