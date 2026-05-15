<?php

/**
 * Web accessibility program configuration.
 *
 * Counsel should confirm which jurisdictions and standards apply to your deployment.
 * See resources/lang/accessibility.php for user-facing declaration text.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Contact for accessibility feedback
    |--------------------------------------------------------------------------
    */
    'contact_email' => env('ACCESSIBILITY_CONTACT_EMAIL', 'contact@eventschedule.com'),

    /*
    |--------------------------------------------------------------------------
    | Target technical standard (label only; must match legal review)
    |--------------------------------------------------------------------------
    */
    'wcag_target_label' => env('ACCESSIBILITY_WCAG_TARGET_LABEL', 'WCAG 2.1 Level AA'),

    /*
    |--------------------------------------------------------------------------
    | Whether the Israeli Standard 5568 is cited in localized copy
    |--------------------------------------------------------------------------
    */
    'reference_israeli_standard_5568' => filter_var(env('ACCESSIBILITY_REFERENCE_IS_5568', true), FILTER_VALIDATE_BOOLEAN),

    /*
    |--------------------------------------------------------------------------
    | Declared first-response SLA (business days) for accessibility reports
    |--------------------------------------------------------------------------
    */
    'response_sla_business_days' => (int) env('ACCESSIBILITY_RESPONSE_SLA_BUSINESS_DAYS', 10),

    /*
    |--------------------------------------------------------------------------
    | Last review date shown on the public declaration (YYYY-MM-DD)
    |--------------------------------------------------------------------------
    */
    'declaration_last_reviewed' => env('ACCESSIBILITY_LAST_REVIEWED', '2026-05-03'),

];
