<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

class GitHubUtils
{
    public static function getStars(): ?int
    {
        // `false` is the cached-failure marker. A plain null could not be distinguished from a
        // cache miss, so an unavailable API was re-requested on every page render - and
        // unauthenticated GitHub allows only 60 calls an hour, so once it starts rejecting, every
        // marketing page pays a blocking HTTP call that is guaranteed to fail.
        $cached = cache()->get('github_stars', 'miss');

        if ($cached !== 'miss') {
            return is_int($cached) ? $cached : null;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'EventSchedule',
            ])->timeout(5)->get('https://api.github.com/repos/eventschedule/eventschedule');

            if ($response->successful()) {
                $githubStars = $response->json('stargazers_count');
                cache()->put('github_stars', $githubStars, 3600);

                return $githubStars;
            }
        } catch (\Exception $e) {
            // Silently fail - star count won't show
        }

        cache()->put('github_stars', false, 300);

        return null;
    }
}
