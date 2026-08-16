<?php

namespace App\Http\Requests;

use App\Rules\YouTubeUrl;
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
            'video_url' => ['required', 'url', new YouTubeUrl],
            'video_title' => ['required', 'string'],
        ];
    }
}
