<?php

namespace App\Http\Controllers\Traits;

use App\Models\Role;
use Illuminate\Http\Request;

trait ResolvesGuestLanguage
{
    /**
     * Which of a schedule's two languages a guest data endpoint should render in.
     *
     * The calendar and past-events endpoints used to read neither `?lang` nor any locale, so they
     * answered purely from the `translate` session flag left behind by the page load. That made a
     * cookie-less request (a shared API URL, a CDN fill, a prefetch) silently return the authored
     * language, and left `app()->getLocale()` at the app default so `__()`-driven payload fields
     * such as category names came back in English on a Hebrew schedule.
     *
     * Deliberately unlike RoleController::viewGuest's block: this never writes the session flag (a
     * GET data endpoint must not change the parent page's language) and never redirects. Only the
     * two languages the visitor's switcher actually offers are honored; anything else falls through
     * to the session/authored default rather than half-applying.
     */
    protected function resolveGuestDisplayLanguage(Request $request, Role $role): string
    {
        $authored = is_valid_language_code($role->language_code) ? $role->language_code : 'en';
        $target = is_valid_language_code($role->translation_language_code) ? $role->translation_language_code : 'en';

        // Guard the array form (?lang[]=) the same way viewGuest does.
        $lang = is_string($request->query('lang')) ? $request->query('lang') : null;

        $resolved = ($lang !== null && ($lang === $target || $lang === $authored))
            ? $lang
            : (session()->has('translate') ? $target : $authored);

        app()->setLocale($resolved);

        return $resolved;
    }
}
