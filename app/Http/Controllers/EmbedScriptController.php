<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * embed.js, the script the Embed dialog's "Show it on your website" code puts on an organizer's
 * own page (resources/embed/embed.js, and partials/embed-frame for the calendar's half).
 *
 * Served by a route rather than from public/, because public/ is cached for a year and this
 * address is frozen into customers' websites the day they paste the code: a fix has to reach
 * every one of them within minutes, so it is cached for five. Read from resources/ on each
 * request (it is a few kilobytes) so a deploy changes it with no build step. No session, no
 * cookies: the route sits outside the web middleware group.
 */
class EmbedScriptController extends Controller
{
    public const MAX_AGE = 300;

    public function __invoke(): Response
    {
        $script = file_get_contents(resource_path('embed/embed.js'));

        return response($script, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age='.self::MAX_AGE,
            'X-Content-Type-Options' => 'nosniff',
            // A script tag on any site loads it; this lets one with crossorigin="anonymous" too.
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
