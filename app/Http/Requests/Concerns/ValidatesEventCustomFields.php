<?php

namespace App\Http\Requests\Concerns;

use App\Models\Role;

trait ValidatesEventCustomFields
{
    protected function eventCustomFieldRules(): array
    {
        $role = $this->resolveRoleForCustomFields();

        return $role ? $role->getEventCustomFieldValidationRules() : [];
    }

    protected function eventCustomFieldAttributes(): array
    {
        $role = $this->resolveRoleForCustomFields();

        return $role ? $role->getEventCustomFieldValidationAttributes() : [];
    }

    protected function resolveRoleForCustomFields(): ?Role
    {
        $subdomain = $this->route('subdomain');
        if (! $subdomain) {
            return null;
        }

        return Role::subdomain($subdomain)->first();
    }
}
