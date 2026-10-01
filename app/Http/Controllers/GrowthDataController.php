<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\GrowthExportService;
use App\Utils\AdminDateRange;
use App\Utils\RealtimeTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * GET /api/internal/growth - the growth payload, for `php artisan app:pull-growth`.
 *
 * Internal. It is not part of the public REST API: not in public/api/openapi.json, not on
 * /for-ai-agents, and not the Pro "REST API access" feature that docs/FEATURES.md maps
 * Controllers/Api/* to, which is why it lives outside that directory. It replaced the "Download
 * JSON" button on /admin/growth so the operator's dev machine can fetch the data without a browser
 * session and hand it to Claude for analysis - see docs/GROWTH_DATA.md and the growth-review skill.
 *
 * Authenticated by one shared bearer token (GROWTH_DATA_TOKEN), read from the Authorization header
 * only. A query parameter would put the token in every access log and Sentry breadcrumb it passes
 * through; Sentry already filters Authorization, and SentryScrubber does again as a backstop.
 *
 * The payload is pseudonymous by construction (GrowthExportService), but it is still the whole
 * business in one file, so: hosted only, disabled outright unless a long token is configured,
 * throttled per real client IP (the `growth_data` limiter), one build at a time, never cached, and
 * every pull audit-logged, as is a rejected token (once per caller per hour).
 */
class GrowthDataController extends Controller
{
    /** Anything shorter disables the endpoint rather than guarding it with a guessable secret. */
    public const MIN_TOKEN_LENGTH = 32;

    /**
     * Longer than a build can run, by a wide margin. public/.user.ini's 90-second cap is no bound:
     * on Linux max_execution_time counts CPU time only, not time waiting on MySQL, and this build is
     * mostly that wait. Cloudflare gives up on the CLIENT at about 100 seconds while the build keeps
     * going, so a lock shorter than the real run would let the retry start a second build beside it.
     * The finally below releases it the moment a build ends; the TTL only matters when a worker
     * dies mid-build, and then it blocks pulls for at most this long.
     */
    private const LOCK_SECONDS = 600;

    public function show(Request $request, GrowthExportService $growth): JsonResponse
    {
        $expected = (string) config('app.growth_data_token');

        // 404 rather than 401 while disabled: there is nothing to authenticate against. A selfhost
        // install has no tiers or subscriptions, so the payload would be empty or misleading -
        // the same reason /admin/growth is hosted-only.
        if (! config('app.hosted') || strlen($expected) < self::MIN_TOKEN_LENGTH) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $given = $request->bearerToken();
        if (! is_string($given) || $given === '' || ! hash_equals($expected, $given)) {
            // A fixed code, never the value sent. no_bearer is what a proxy stripping the
            // Authorization header looks like, which is otherwise indistinguishable from a typo.
            //
            // At most one row per caller, per code, per hour: the path is public (open source), so
            // a row per refused request would let anyone grow audit_logs at will. The first refusal
            // is what an investigation needs; the throttle already bounds the rest.
            $code = ($given === null || $given === '') ? 'growth_data:no_bearer' : 'growth_data:mismatch';
            $ip = RealtimeTracker::clientIp($request);
            if (Cache::add('growth_data_refused:'.$code.':'.hash_hmac('sha256', $ip, (string) config('app.key')), 1, 3600)) {
                AuditService::log(AuditService::API_AUTH_FAILED, newValues: ['client_ip' => $ip], metadata: $code);
            }

            return response()->json(['error' => __('messages.unauthorized')], 401);
        }

        // Hosted is one small container: a build holds an FPM worker for its whole run, so even
        // the limiter's ten a minute could otherwise take several workers at once.
        $lock = Cache::lock('growth_data_build', self::LOCK_SECONDS);
        if (! $lock->get()) {
            return response()->json(['error' => 'A pull is already running.'], 429, ['Retry-After' => '30']);
        }

        try {
            $range = $request->query('range', 'last_30_days');
            $dates = AdminDateRange::for($range);

            // Logged BEFORE the build, so a pull that times out or runs out of memory is still on
            // record - and its missing duration says how it ended.
            $started = hrtime(true);
            $audit = AuditService::log(
                AuditService::ADMIN_GROWTH_DATA_PULL,
                newValues: [
                    'client_ip' => RealtimeTracker::clientIp($request),
                    'range' => is_string($range) && in_array($range, AdminDateRange::RANGES, true) ? $range : 'all_time',
                ],
            );

            $data = $growth->build($dates['start'], $dates['end'], $dates['previous_start'], $dates['previous_end']);

            // The cost of a build, so growth toward the worker's memory ceiling shows up here
            // long before it shows up as an out-of-memory error. A diagnostic: if the write fails,
            // the pull still succeeds - AuditService::log() itself never breaks its caller either.
            try {
                $audit?->update(['new_values' => array_merge($audit->new_values ?? [], [
                    'duration_ms' => (int) round((hrtime(true) - $started) / 1e6),
                    'peak_memory_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
                ])]);
            } catch (\Throwable $e) {
                report($e);
            }

            return response()->json(
                $data,
                200,
                ['Cache-Control' => 'no-store, private'],
                // PRESERVE_ZERO_FRACTION: an amount of 60.0 stays a float (60.0), not an int (60).
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
            );
        } finally {
            $lock->release();
        }
    }
}
