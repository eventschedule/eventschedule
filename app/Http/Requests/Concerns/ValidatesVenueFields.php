<?php

namespace App\Http\Requests\Concerns;

use App\Utils\UrlUtils;

/**
 * Bounds the venue fields the AP event form posts, and cleans the pasted website before the
 * length rule measures it.
 *
 * Every field here is copied onto a venue Role by EventRepo::saveEvent() and lands in a
 * varchar(255). Under a strict connection an over-long value is a QueryException (MySQL 1406),
 * not a truncation, so it 500s the form and costs the user the event they were entering -
 * which is exactly what a 390-character Facebook link shim in venue_website did.
 *
 * Only EventController::store() and ::update() resolve these FormRequests. The import, guest
 * submission, WhatsApp webhook and curator-import paths build a plain Request and go straight
 * to saveEvent(), so they are covered by the Role saving hook instead, not by these rules.
 */
trait ValidatesVenueFields
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function venueFieldRules(): array
    {
        return [
            'venue_website' => ['nullable', 'string', 'max:255'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_name_en' => ['nullable', 'string', 'max:255'],
            'venue_address1' => ['nullable', 'string', 'max:255'],
            'venue_address2' => ['nullable', 'string', 'max:255'],
            'venue_city' => ['nullable', 'string', 'max:255'],
            'venue_state' => ['nullable', 'string', 'max:255'],
            // roles.postal_code is a plain string() column, so 255 - not the 20 that venue_phone uses.
            'venue_postal_code' => ['nullable', 'string', 'max:255'],
            // The picker submits alpha-2; 3 still admits an alpha-3 ("ISR") for
            // CountryUtils::normalizeCountryCode() to map down. That normalizer does not bound
            // length - it returns an unrecognized value unchanged - so this rule is the bound.
            'venue_country_code' => ['nullable', 'string', 'max:3'],
            // Nothing normalizes language_code at all, so this rule is the only thing between the
            // request and a varchar(255). Same shape as translation_language_code on RoleUpdateRequest.
            'venue_language_code' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('app.supported_languages')))],
        ];
    }

    /**
     * Unwrap the shim before max:255 runs, so the paste that prompted all this passes validation
     * (390 characters in, 23 stored) and only a genuinely over-long URL is refused.
     */
    protected function prepareForValidation(): void
    {
        // has(), never unconditional. Merging a value for an ABSENT key makes the key present,
        // and EventRepo::saveEvent() reads has('venue_website') as "the interactive form cleared
        // this field" whenever venue_details_editable is set - so an unguarded merge would wipe
        // the stored website of a venue the request never mentioned.
        if ($this->has('venue_website')) {
            $this->merge([
                'venue_website' => UrlUtils::normalizeWebsiteUrl($this->input('venue_website')),
            ]);
        }
    }
}
