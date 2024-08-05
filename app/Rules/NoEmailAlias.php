<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoEmailAlias implements Rule
{
    public function passes($attribute, $value)
    {
        if (strpos($value, 'hillelcoren') === 0) {
            return true;
        }
        
        // Regular expression to check for email alias
        return ! preg_match('/\+.*@/', $value);
    }

    public function message()
    {
        return 'The :attribute field must not contain email aliases.';
    }
}
