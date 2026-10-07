<?php

namespace App\Http\Controllers;

use App\Utils\GuestFunnel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * POST /api/guest-count - the three guest-page counts only a browser can see (App\Utils\GuestFunnel):
 * a tap from a list into an event, a form opened, Add to calendar used.
 *
 * In routes/api.php for the reason the Realtime beacon is: no session, no cookies, no CSRF, and it
 * answers same-origin on every host. The body is {"s": "<stage>", "k": "<the day's token>"} and
 * nothing else. No identifier is sent or stored: the token is the same for everybody that day
 * (GuestFunnel::beaconToken()), the count is one per visitor per day by the daily-salted hash the
 * page-view counters already use, and what is kept is a number on a row that has one row a day.
 *
 * Always 204 for a well-formed body, counted or not, so the page learns nothing from the answer.
 */
class GuestFunnelBeaconController extends Controller
{
    private const MAX_BODY = 256;

    public function store(Request $request): Response
    {
        if (strlen($request->getContent()) > self::MAX_BODY) {
            return response()->noContent(422);
        }

        $data = json_decode($request->getContent(), true);
        $stage = is_array($data) ? ($data['s'] ?? null) : null;

        if (! is_string($stage) || ! in_array($stage, GuestFunnel::BEACON_STAGES, true)) {
            return response()->noContent(422);
        }

        // The beacon's address has no host, so a real one is always same-origin. Anything else is
        // another site making its visitors' browsers post here. Browsers too old to send the
        // header are let through, as the Realtime beacon lets them.
        $fetchSite = $request->header('Sec-Fetch-Site');

        if ($fetchSite === null || $fetchSite === 'same-origin') {
            $token = $data['k'] ?? null;
            GuestFunnel::countFromBeacon($stage, is_string($token) ? $token : null, $request);
        }

        return response()->noContent();
    }
}
