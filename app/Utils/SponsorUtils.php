<?php

namespace App\Utils;

class SponsorUtils
{
    /**
     * The sponsor list a save posted (existing_sponsors on the schedule form,
     * existing_event_sponsors on the event form), with each entry's logo kept only when it is one of
     * $storedLogos: the logos this same schedule or event already holds.
     *
     * The list is JSON the browser builds, and a logo in it is a bare filename. Whatever the saved
     * list later drops is deleted as an orphan, and so is every logo when the schedule or event is
     * deleted, so a name that is not already this row's is another row's file waiting to be deleted.
     * It never enters the stored list. The rest of that entry (name, link, tier) is kept, and shows
     * without a logo, as a sponsor with no logo always has. New uploads are appended after this, so
     * they are unaffected.
     *
     * @param  mixed  $submitted  the decoded JSON
     * @param  array<int, mixed>  $storedLogos
     * @return list<array<string, mixed>>
     */
    public static function keepStoredLogos(mixed $submitted, array $storedLogos): array
    {
        if (! is_array($submitted)) {
            return [];
        }

        $sponsors = [];

        foreach ($submitted as $sponsor) {
            // Not a sponsor at all, and the guest page cannot render one: getSponsorLogos() writes
            // logo_url into each entry, which throws on a string.
            if (! is_array($sponsor)) {
                continue;
            }

            if (array_key_exists('logo', $sponsor) && ! in_array($sponsor['logo'], $storedLogos, true)) {
                unset($sponsor['logo']);
            }

            $sponsors[] = $sponsor;
        }

        return $sponsors;
    }
}
