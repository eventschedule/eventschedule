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
    'declaration_last_reviewed' => env('ACCESSIBILITY_LAST_REVIEWED', '2026-09-04'),

    /*
    |--------------------------------------------------------------------------
    | The measured record published under "Conformance status"
    |--------------------------------------------------------------------------
    |
    | A conformance statement is worth more when it says what was actually
    | measured, over what, and when. These two values are interpolated into
    | accessibility.section_status_measured, so re-measuring the public site
    | means editing config rather than twelve translation files.
    |
    | Scope is the PUBLIC marketing and documentation pages only. The admin
    | portal, the guest portal and the calendar views are not in this figure,
    | and the declaration says so.
    |
    | 153, not the 157 URLs the sweep visits: four of those are legacy /docs
    | redirects that resolve to pages already in the set, so counting them would
    | count four pages twice. It is the same 153 config/sitemap_lastmod.php dates.
    |
    */
    'public_pages_measured' => (int) env('ACCESSIBILITY_PUBLIC_PAGES_MEASURED', 153),
    'public_measurement_date' => env('ACCESSIBILITY_PUBLIC_MEASUREMENT_DATE', '2026-09-04'),

];
