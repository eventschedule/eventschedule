<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminPlanUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'plan_type' => ['required', 'in:free,pro,enterprise'],
            'plan_term' => ['nullable', 'in:month,year'],
            'plan_expires' => ['nullable', 'date'],
        ];
    }
}
