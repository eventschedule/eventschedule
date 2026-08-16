<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleVideoRemoveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * The schedule is identified by an encoded hash rather than the raw integer id the older
     * save-video endpoints take, per the project rule that user-visible ids are encoded.
     *
     * video_url is not constrained to a YouTube URL: the column has held unvalidated values for a
     * long time, and those entries are exactly the ones an owner most needs to be able to delete.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'role_hash' => ['required', 'string'],
            'video_url' => ['required', 'string', 'max:1000'],
        ];
    }
}
