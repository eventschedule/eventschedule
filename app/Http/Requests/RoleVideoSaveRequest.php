<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleVideoSaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer'],
            'video_url' => ['required', 'url'],
            'video_title' => ['required', 'string'],
        ];
    }
}
