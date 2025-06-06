<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Get the CSP nonce for the current request
     */
    public static function cspNonce(): string
    {
        return request()->attributes->get('csp_nonce', '');
    }
    
    /**
     * Generate nonce attribute for script tags
     */
    public static function nonceAttr(): string
    {
        $nonce = self::cspNonce();
        return $nonce ? "nonce=\"{$nonce}\"" : '';
    }
} 