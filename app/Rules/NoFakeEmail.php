<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoFakeEmail implements Rule
{
    public function passes($attribute, $value)
    {
        if (strpos($value, '@example.com') !== false) {
            return false;
        }
    
        // List of common disposable email domains
        $fakeDomains = [
            'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'trashmail.com', 'yopmail.com', 
            'dispostable.com', 'getnada.com', 'temp-mail.org', 'fakeinbox.com', 'sharklasers.com', 
            'spamgourmet.com', 'maildrop.cc', 'spambog.com', 'ethereal.email', 'throwawaymail.com', 
            'mailnesia.com', 'anonymbox.com', 'mailsac.com', 'spam4.me', 'dodgit.com', 
            'mintemail.com', 'mytrashmail.com', 'tempinbox.com', 'emailondeck.com', 'easytrashmail.com'
        ];
        
        foreach ($fakeDomains as $domain) {
            if (strpos($value, "@$domain") !== false) {
                return false;
            }
        }
        
        return true;
    }

    public function message()
    {
        return 'The :attribute field must be a permanent email address.';
    }
}
