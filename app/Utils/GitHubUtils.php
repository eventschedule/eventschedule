<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

class GitHubUtils
{
    public static function getStars(): ?int
    {
        $githubStars = cache()->get('github_stars');
        if ($githubStars === null) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'EventSchedule',
                ])->timeout(5)->get('https://api.github.com/repos/eventschedule/eventschedule');

                if ($response->successful()) {
                    $githubStars = $response->json('stargazers_count');
                    cache()->put('github_stars', $githubStars, 3600);
                }
            } catch (\Exception $e) {
                // Silently fail - star count won't show
            }
        }

        return $githubStars;
    }
}
