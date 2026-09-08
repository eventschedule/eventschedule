<?php

namespace App\Utils;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * The repo's GitHub star count, for the "star us" badges.
 *
 * Split into a READ that never touches the network and a REFRESH that only the daily
 * app:check-github-stars command calls - the same shape AppUpdateService and app:check-version use
 * for the other thing this app asks GitHub about.
 *
 * It was not always so. getStars() used to fetch inline behind a one-hour cache, and it is called
 * from nine places: the layouts.app-admin and marketing.partials.header view composers, and seven
 * MarketingController actions. That put a blocking five-second request on the render path of
 * essentially every admin and marketing page, so a slow or unreachable GitHub delayed real page
 * loads - and because a failed lookup was only remembered for five minutes, it came back every five
 * minutes for as long as GitHub was unwell.
 */
class GitHubUtils
{
    /** The settings row holding the last successfully fetched count. */
    public const STARS_KEY = 'github_stars';

    /**
     * The stored count, or null if one has never been fetched successfully.
     *
     * Never makes an outbound request, so it is safe on a view composer that runs for every page
     * render. Never throws either: a settings table missing mid-deploy must not 500 a page.
     *
     * Read with a direct query rather than Setting::get(), and that is load-bearing. Setting::get()
     * serves the whole settings map out of Cache::rememberForever('site_settings'), invalidated by
     * Setting::set()'s Cache::forget. Hosted runs the scheduler in a separate container with
     * CACHE_STORE unset - the file driver - where that forget cannot reach the web container, so a
     * rememberForever map would keep serving a pre-write copy until the container was recreated on
     * the next deploy. settings.key is unique and therefore indexed, so this costs one index lookup;
     * a render does at most two of them.
     */
    public static function getStars(): ?int
    {
        try {
            $value = Setting::where('key', self::STARS_KEY)->value('value');
        } catch (\Throwable $e) {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Ask GitHub and store the answer. Only app:check-github-stars calls this.
     *
     * A failure stores NOTHING, on purpose: the last known count then survives indefinitely, so an
     * outage is invisible to visitors rather than blanking the badge. There is no cached-failure
     * marker any more either - that existed to stop a render path retrying a doomed call every five
     * minutes, and the only caller now runs once a day.
     *
     * Catches \Exception, not \Throwable as getStars() does, and the asymmetry is deliberate: the
     * read sits on a render path and must never break a page, whereas a genuine bug in here should
     * surface and mark the scheduled task failed rather than be swallowed as "GitHub was down".
     */
    public static function refresh(): ?int
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'EventSchedule',
            ])->timeout(5)->get('https://api.github.com/repos/eventschedule/eventschedule');

            if (! $response->successful()) {
                return null;
            }

            $stars = $response->json('stargazers_count');

            if (! is_numeric($stars)) {
                return null;
            }

            Setting::set(self::STARS_KEY, (string) (int) $stars);

            return (int) $stars;
        } catch (\Exception $e) {
            return null;
        }
    }
}
