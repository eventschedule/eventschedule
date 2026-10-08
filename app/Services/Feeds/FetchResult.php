<?php

namespace App\Services\Feeds;

/**
 * What one fetch of a feed's address came to. `status` is a reason KEY, which is what is kept
 * on the feed (event_feeds.last_status) and what the screens translate: never a message, because
 * a transport's message quotes the address it failed on.
 */
final class FetchResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $httpStatus = null,
        public readonly string $body = '',
        public readonly string $contentType = '',
        /** Where the body was finally served from, for resolving its relative links. */
        public readonly string $url = '',
        public readonly ?string $etag = null,
        public readonly ?string $lastModified = null,
        /** Seconds the server asked us to stay away, when it said. */
        public readonly ?int $retryAfter = null,
    ) {}

    public function ok(): bool
    {
        return $this->status === FeedFetcher::OK;
    }

    public function unchanged(): bool
    {
        return $this->status === FeedFetcher::NOT_MODIFIED;
    }
}
