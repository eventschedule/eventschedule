<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleVideosSaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'role_id' => ['required', 'integer'],
            'videos' => ['array', 'max:2'],
        ];

        if (! empty($this->videos)) {
            $rules['videos.*.url'] = ['required', 'url'];
            $rules['videos.*.title'] = ['required', 'string'];
        }

        return $rules;
    }
}
