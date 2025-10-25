<?php

namespace App\Services;

use App\Enums\ReleaseChannel;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReleaseChannelService
{
    private const CACHE_PREFIX = 'release_channel_version:';

    public function getLatestVersion(ReleaseChannel $channel): string
    {
        $release = $this->getLatestRelease($channel);

        return $release['version'];
    }

    public function getLatestRelease(ReleaseChannel $channel): array
    {
        $cacheKey = $this->cacheKey($channel);

        return Cache::remember($cacheKey, 600, function () use ($channel) {
            $repository = $this->resolveRepository();
            $client = $this->buildClient();

            if ($channel->isBeta()) {
                return $this->fetchLatestBetaRelease($client, $repository);
            }

            return $this->fetchLatestProductionRelease($client, $repository);
        });
    }

    public function forgetCached(ReleaseChannel $channel): void
    {
        Cache::forget($this->cacheKey($channel));
    }

    public function forgetAll(): void
    {
        foreach (ReleaseChannel::cases() as $channel) {
            $this->forgetCached($channel);
        }
    }

    public static function cacheKeyFor(ReleaseChannel $channel): string
    {
        return self::CACHE_PREFIX . $channel->value;
    }

    private function cacheKey(ReleaseChannel $channel): string
    {
        return self::cacheKeyFor($channel);
    }

    private function buildClient(): PendingRequest
    {
        $client = Http::baseUrl('https://api.github.com/')
            ->acceptJson()
            ->timeout(120)
            ->withHeaders([
                'User-Agent' => 'eventschedule-updater',
            ]);

        $token = config('self-update.repository_types.github.private_access_token');

        if (! empty($token)) {
            $client = $client->withToken($token);
        }

        return $client;
    }

    private function resolveRepository(): array
    {
        $vendor = config('self-update.repository_types.github.repository_vendor');
        $name = config('self-update.repository_types.github.repository_name');

        if (empty($vendor) || empty($name)) {
            throw new RuntimeException('GitHub repository details are not configured.');
        }

        return [
            'vendor' => $vendor,
            'name' => $name,
        ];
    }

    private function fetchLatestProductionRelease(PendingRequest $client, array $repository): array
    {
        $response = $client->get(sprintf(
            'repos/%s/%s/releases/latest',
            $repository['vendor'],
            $repository['name']
        ));

        $data = $response->json();

        if ($response->failed() || ! is_array($data)) {
            throw new RuntimeException('Failed to retrieve the latest production release information.');
        }

        return $this->normalizeReleaseData($data);
    }

    private function fetchLatestBetaRelease(PendingRequest $client, array $repository): array
    {
        $response = $client->get(sprintf(
            'repos/%s/%s/releases',
            $repository['vendor'],
            $repository['name']
        ), [
            'per_page' => 10,
        ]);

        $data = $response->json();

        if ($response->failed() || ! is_array($data)) {
            throw new RuntimeException('Failed to retrieve beta release information.');
        }

        foreach ($data as $release) {
            if (is_array($release) && ! empty($release['prerelease'])) {
                return $this->normalizeReleaseData($release);
            }
        }

        throw new RuntimeException('No beta releases are currently available.');
    }

    private function normalizeReleaseData(array $release): array
    {
        if (empty($release['tag_name'])) {
            throw new RuntimeException('Release data is missing the tag_name attribute.');
        }

        $tag = (string) $release['tag_name'];

        return [
            'version' => $this->normalizeVersionString($tag),
            'tag' => $tag,
            'name' => (string) ($release['name'] ?? $release['tag_name']),
            'prerelease' => (bool) ($release['prerelease'] ?? false),
        ];
    }

    private function normalizeVersionString(string $tag): string
    {
        if ($tag === '') {
            return $tag;
        }

        if (preg_match('/^v(.+)/i', $tag, $matches) === 1) {
            return $matches[1];
        }

        return $tag;
    }
}
