<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The admin's edit of a schedule's identity: name, subdomain and contact details.
 *
 * Separate from AdminPlanUpdateRequest, which backs the plan route - adding these fields there
 * would silently widen what a plan save is allowed to write.
 */
class AdminScheduleDetailsRequest extends FormRequest
{
    private ?Role $roleForValidation = null;

    private function role(): Role
    {
        return $this->roleForValidation ??= Role::findOrFail(UrlUtils::decodeId($this->route('role')));
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $role = $this->role();

        return [
            'name' => ['required', 'string', 'max:255'],
            // Nullable and not unique: roles.email lost its unique index in
            // 2024_08_18_154754_add_schedule_column and became nullable in 2024_09_17_111106.
            // NoFakeEmail is deliberately not applied - an operator should be able to record
            // whatever address the customer actually has, including one our heuristics dislike.
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
            // Grandfathered: a value the row ALREADY carries is accepted as-is. This field is
            // posted on every save of the form, so applying the format, reserved and uniqueness
            // rules to a stored value would lock the admin out of editing a schedule's NAME until
            // they renamed it - and rows created before the current rules can carry a name that
            // no longer validates (subdomains over 50 characters predate the cap). Same reasoning
            // as RoleController::update()'s "only a CHANGE is cleaned" guard. Only a change is
            // checked.
            'new_subdomain' => $this->input('new_subdomain') === $role->subdomain
                ? ['required', 'string']
                : [
                    'required', 'string', 'min:3', 'max:50',
                    'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                    function ($attribute, $value, $fail) use ($role) {
                        if (in_array($value, Role::RESERVED_SUBDOMAINS, true)) {
                            $fail(__('messages.subdomain_reserved'));

                            return;
                        }

                        if (Role::where('subdomain', $value)->where('id', '!=', $role->id)->exists()) {
                            $fail(__('messages.subdomain_taken'));
                        }
                    },
                ],
        ];
    }
}
