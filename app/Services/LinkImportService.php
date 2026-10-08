<?php

namespace App\Services;

use App\Exceptions\LinkImportException;
use App\Models\Event;
use App\Models\Role;
use App\Utils\GeminiUtils;
use App\Utils\IcsImportUtils;
use App\Utils\ImportAddress;
use App\Utils\JsonLdEventUtils;
use App\Utils\RemoteImage;
use App\Utils\UrlUtils;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;

/**
 * Turns a link pasted on the import page into a preview of the events behind it.
 *
 * In order of preference, because each step down is less exact and the last one costs an AI
 * request:
 *
 *   1. a calendar feed (.ics / webcal), read as written;
 *   2. the event data a web page publishes about itself;
 *   3. the page's text, read by the model - once per link, inside the schedule's daily allowance,
 *      and only when the page gave nothing at 2 or the person asked for the whole page.
 *
 * Nothing is saved here. The rows go back to the page, where each one is reviewed and saved
 * through the same endpoint as a parsed flyer.
 *
 * Every fetch goes through UrlUtils (validated, pinned to the vetted address, redirects
 * re-checked). The link comes from an editor of the schedule; guests never reach this.
 */
class LinkImportService
{
    /** Events offered per import. A repeating event or a listed series counts once. */
    public const MAX_EVENTS = 100;

    /** A page or a feed bigger than this is not read. */
    private const MAX_BYTES = 3 * 1024 * 1024;

    /** The model's input limit, the same one the import page's text box has. */
    private const MAX_TEXT = 10000;

    private const FETCH_TIMEOUT = 15;

    /** Pictures fetched for the preview: the first few rows, and not for long. */
    private const MAX_IMAGES = 25;

    private const IMAGE_SECONDS = 15;

    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    /**
     * @return array{parsed: list<array>, meta: array}
     *
     * @throws LinkImportException with a message for the person who pasted the link
     */
    public function preview(Role $role, string $link, bool $wholePage = false): array
    {
        $url = $this->normalise($link);
        $this->refuseSignInWalls($url);

        [$url, $rewrittenFrom] = $this->knownFeedFor($url);
        $fetched = $this->fetch($url, $rewrittenFrom);

        $timezone = $role->captureTimezone();
        // A venue's events happen at the venue. See ImportedTime::place().
        $keepLocalClock = ! $role->isVenue();
        $host = (string) parse_url($fetched['url'], PHP_URL_HOST);

        if ($this->isCalendar($fetched)) {
            try {
                $read = IcsImportUtils::read($fetched['body'], $timezone, $keepLocalClock);
            } catch (\InvalidArgumentException $e) {
                throw $this->refusal('unsupported');
            }

            return $this->finish($role, $read, Event::IMPORT_ICS, $host, $timezone);
        }

        if (! $this->isHtml($fetched)) {
            throw $this->refusal('unsupported');
        }

        if (! $wholePage) {
            $read = JsonLdEventUtils::read($fetched['body'], $fetched['url'], $timezone, $keepLocalClock);

            if ($read['rows']) {
                // The page may list more than it marks up. Offer the whole-page read when one is possible.
                return $this->finish($role, $read, Event::IMPORT_PAGE, $host, $timezone, [
                    'can_read_whole_page' => $this->hasAi(),
                ]);
            }
        }

        return $this->readWithAi($role, $fetched, $host, $timezone);
    }

    /**
     * The same preview for a calendar the person connected rather than linked. Its entries
     * arrive as calendar text (GoogleImportUtils) and are read, matched against the schedule,
     * capped and labelled exactly as a feed is. `$truncated` says the calendar had more entries
     * than were read, which the page passes on.
     */
    public function previewCalendar(Role $role, string $calendarText, string $source, string $name, bool $truncated = false): array
    {
        $timezone = $role->captureTimezone();

        try {
            $read = IcsImportUtils::read($calendarText, $timezone, ! $role->isVenue());
        } catch (\InvalidArgumentException $e) {
            throw $this->refusal('failed');
        }

        return $this->finish($role, $read, $source, $name, $timezone, ['calendar_truncated' => $truncated], 'calendar_empty');
    }

    /** Decide which import made a row, from the token preview() handed the page. */
    public static function sourceFromToken(?string $token, Role $role, int $userId): string
    {
        if (! is_string($token) || $token === '') {
            return Event::IMPORT_AI;
        }

        try {
            $claim = decrypt($token);
        } catch (\Throwable $e) {
            return Event::IMPORT_AI;
        }

        $valid = is_array($claim)
            && ($claim['r'] ?? null) === $role->id
            && ($claim['u'] ?? null) === $userId
            && ($claim['t'] ?? 0) > now()->subDay()->getTimestamp()
            && in_array($claim['s'] ?? null, Event::IMPORT_SOURCES, true);

        // Anything else is what this endpoint has always meant: a row off the import page.
        return $valid ? $claim['s'] : Event::IMPORT_AI;
    }

    /**
     * A token naming which import a preview came from, for the page to send back with each save.
     * Encrypted, and tied to the schedule, the person and the day, so it cannot be made up or
     * carried elsewhere. It labels the event; it grants nothing.
     */
    public static function token(string $source, Role $role, int $userId): string
    {
        return encrypt(['s' => $source, 'r' => $role->id, 'u' => $userId, 't' => now()->getTimestamp()]);
    }

    /**
     * How an address is spelled, which sites cannot be read and which links stand for a feed
     * are ImportAddress's to say: a feed asks the same three questions. What they mean to the
     * person on the import page is said here.
     */
    private function normalise(string $link): string
    {
        return ImportAddress::normalise($link) ?? throw $this->refusal('invalid_url');
    }

    private function refuseSignInWalls(string $url): void
    {
        if ($platform = ImportAddress::signInWall($url)) {
            throw new LinkImportException('needs_screenshot', __('messages.link_import_social', ['platform' => $platform]));
        }
    }

    /** @return array{0: string, 1: ?string} the address to fetch, and what it was rewritten from */
    protected function knownFeedFor(string $url): array
    {
        return ImportAddress::knownFeedFor($url);
    }

    /** @return array{body: string, type: string, url: string} */
    private function fetch(string $url, ?string $rewrittenFrom): array
    {
        try {
            $followed = UrlUtils::safeHttpGetWithUrl($url, [
                'User-Agent' => 'EventSchedule/1.0',
                'Accept' => 'text/calendar,text/html,application/xhtml+xml;q=0.9,*/*;q=0.5',
                'Accept-Language' => 'en-US,en;q=0.5',
            ], self::FETCH_TIMEOUT, 4);
        } catch (ConnectionException $e) {
            throw $this->refusal('unreachable');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Stopped by the guard's size cap: a stated length over it, or a body that ran past
            // it. Anything else is not ours to explain here.
            if (in_array($e->getHandlerContext()['errno'] ?? null, [CURLE_WRITE_ERROR, CURLE_FILESIZE_EXCEEDED], true)) {
                throw $this->refusal('too_large');
            }

            throw $e;
        }

        $response = $followed['response'];

        // Null is a blocked or unresolvable address. It reads the same as unreachable on
        // purpose: saying which would tell the asker what answers on the inside.
        if ($response === null) {
            throw $this->refusal('unreachable');
        }

        if (! $response->successful()) {
            // The feed a Google link was rewritten to answers 404 when the calendar is private.
            if ($rewrittenFrom === 'google') {
                throw $this->refusal('google_private');
            }

            throw new LinkImportException('http_error', __('messages.link_import_http_error', ['status' => $response->status()]));
        }

        $body = $response->body();

        if (strlen($body) > self::MAX_BYTES) {
            throw $this->refusal('too_large');
        }

        return [
            'body' => $body,
            'type' => strtolower((string) $response->header('Content-Type')),
            'url' => $followed['url'],
        ];
    }

    private function isCalendar(array $fetched): bool
    {
        return ImportAddress::isCalendar($fetched['type'], $fetched['body']);
    }

    private function isHtml(array $fetched): bool
    {
        return ImportAddress::isHtml($fetched['type'], $fetched['body']);
    }

    private function hasAi(): bool
    {
        return (bool) (config('services.google.gemini_key') || config('services.openai.api_key'));
    }

    private function readWithAi(Role $role, array $fetched, string $host, string $timezone): array
    {
        if (! $this->hasAi()) {
            throw $this->refusal('needs_ai');
        }

        if (! $role->canMakeAiParseRequest()) {
            throw new LinkImportException('ai_limit', __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiParseDailyLimit()]));
        }

        [$text, $truncated] = $this->pageText($fetched['body']);

        if (mb_strlen($text) < 20) {
            throw $this->refusal('no_events');
        }

        $rows = GeminiUtils::parseEvent($role, $text, null, [
            'prompt_footer' => 'footer_page',
            'timezone' => $timezone,
            'default_registration_url' => $fetched['url'],
            'drop_empty_rows' => true,
        ]);

        if (! $rows) {
            throw $this->refusal('no_events');
        }

        return [
            'parsed' => array_slice($rows, 0, self::MAX_EVENTS),
            'meta' => [
                'source' => Event::IMPORT_PAGE_AI,
                'host' => $host,
                'found' => count($rows),
                'shown' => min(count($rows), self::MAX_EVENTS),
                'already_on_schedule' => 0,
                'skipped' => [],
                'text_truncated' => $truncated,
                'can_read_whole_page' => false,
                'timezone' => $timezone,
                'import_token' => self::token(Event::IMPORT_PAGE_AI, $role, (int) auth()->id()),
            ],
        ];
    }

    /**
     * What a feed or a page's event data gave, made ready for the preview: nameless entries
     * named, what the schedule already has dropped, the count capped, pictures fetched, and the
     * same matching a parsed flyer gets.
     */
    private function finish(Role $role, array $read, string $source, string $host, string $timezone, array $extra = [], string $emptyReason = 'no_events'): array
    {
        $rows = $read['rows'];

        if (! $rows) {
            throw $this->refusal($emptyReason);
        }

        foreach ($rows as $index => $row) {
            if (trim((string) $row['event_name']) === '') {
                $rows[$index]['event_name'] = __('messages.untitled_event');
            }
        }

        $found = $this->countEvents($rows);
        $rows = $this->dropAlreadyOnSchedule($role, $rows, $timezone);
        $alreadyOnSchedule = $found - $this->countEvents($rows);
        $rows = $this->renumber($this->cap($rows));

        $rows = $this->fetchImages($rows);
        $rows = GeminiUtils::enrichParsedEvents($role, $rows, ['source' => $source, 'timezone' => $timezone]);

        return [
            'parsed' => array_values($rows),
            'meta' => array_merge([
                'source' => $source,
                'host' => $host,
                'found' => $found,
                'shown' => $this->countEvents($rows),
                'already_on_schedule' => $alreadyOnSchedule,
                'skipped' => $read['skipped'],
                'text_truncated' => false,
                'calendar_truncated' => false,
                'can_read_whole_page' => false,
                'timezone' => $timezone,
                'import_token' => self::token($source, $role, (int) auth()->id()),
            ], $extra),
        ];
    }

    /** Events, not rows: the dates of one listed series are one event to the person. */
    private function countEvents(array $rows): int
    {
        $series = [];
        $count = 0;

        foreach ($rows as $row) {
            $id = $row['series']['id'] ?? null;
            if ($id === null) {
                $count++;
            } elseif (! isset($series[$id])) {
                $series[$id] = true;
                $count++;
            }
        }

        return $count;
    }

    /**
     * Leave out what the schedule already has, among the events it created itself. What counts
     * as already there is ScheduleEventMatcher's to say.
     */
    private function dropAlreadyOnSchedule(Role $role, array $rows, string $timezone): array
    {
        $matcher = new ScheduleEventMatcher($role, $timezone);

        return array_values(array_filter($rows, fn (array $row) => $matcher->match($row) === null));
    }

    /**
     * Number each listed series again after rows were dropped or cut. The preview shows a series
     * as its first row and selects the rest with it, so "first" has to mean the first that is
     * still here: when the first date was already on the schedule, the others were left with no
     * row to stand under, were ticked all the same, and were added unseen.
     */
    private function renumber(array $rows): array
    {
        $counts = [];
        foreach ($rows as $row) {
            if ($id = $row['series']['id'] ?? null) {
                $counts[$id] = ($counts[$id] ?? 0) + 1;
            }
        }

        $positions = [];
        foreach ($rows as $index => $row) {
            $id = $row['series']['id'] ?? null;
            if ($id === null) {
                continue;
            }

            // One date left is just an event.
            if ($counts[$id] === 1) {
                $rows[$index]['series'] = null;

                continue;
            }

            $positions[$id] = ($positions[$id] ?? 0) + 1;
            $rows[$index]['series']['position'] = $positions[$id];
            $rows[$index]['series']['count'] = $counts[$id];
        }

        return $rows;
    }

    /** The first MAX_EVENTS events. A listed series is kept or cut whole. */
    private function cap(array $rows): array
    {
        $kept = [];
        $series = [];
        $count = 0;

        foreach ($rows as $row) {
            $id = $row['series']['id'] ?? null;

            if ($id !== null && isset($series[$id])) {
                $kept[] = $row;

                continue;
            }

            if ($count >= self::MAX_EVENTS) {
                continue;
            }

            if ($id !== null) {
                $series[$id] = true;
            }
            $count++;
            $kept[] = $row;
        }

        return $kept;
    }

    /**
     * Fetch each row's picture into storage/app/temp, where the preview shows it from and the
     * save attaches it from, exactly as for a flyer. Bounded in number, size and time: a feed
     * with a hundred posters must not hold the preview up.
     */
    private function fetchImages(array $rows): array
    {
        $this->pruneOldImages();
        $deadline = microtime(true) + self::IMAGE_SECONDS;
        $fetched = 0;
        $byUrl = [];

        foreach ($rows as $index => $row) {
            $url = $row['image_url'] ?? null;
            unset($rows[$index]['image_url']);

            if (! $url) {
                continue;
            }

            if (! array_key_exists($url, $byUrl)) {
                if ($fetched >= self::MAX_IMAGES || microtime(true) > $deadline) {
                    continue;
                }
                $fetched++;
                // No single picture gets longer than what is left for all of them.
                $byUrl[$url] = $this->storeImage($url, (int) max(1, min(8, ceil($deadline - microtime(true)))));
            }

            if ($byUrl[$url]) {
                $rows[$index]['social_image'] = $byUrl[$url];
            }
        }

        return $rows;
    }

    /**
     * A preview's pictures are for the page that is open now, and its token is good for a day.
     * Nothing else removes them, and every read can add twenty-five, so each read clears out
     * what is older than that before adding its own.
     */
    private function pruneOldImages(): void
    {
        $cutoff = time() - 86400;

        foreach (glob(storage_path('app/temp').'/event_*') ?: [] as $file) {
            if (is_file($file) && @filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    private function storeImage(string $url, int $seconds = 8): ?string
    {
        $image = RemoteImage::read($url, $seconds, self::MAX_IMAGE_BYTES);

        if (isset($image['reason'])) {
            return null;
        }

        ['contents' => $contents, 'extension' => $extension] = $image;

        $directory = storage_path('app/temp');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }

        // The "event_" prefix is what AppController::tempEventImage() serves and what
        // EventController::import() will attach.
        $path = $directory.'/event_'.strtolower(Str::random(32)).'.'.$extension;

        return file_put_contents($path, $contents) !== false ? $path : null;
    }

    /**
     * A page's readable text, for the model: the main content when the page marks one, without
     * scripts, navigation and forms, one line per block.
     *
     * @return array{0: string, 1: bool} the text, and whether it had to be cut
     */
    private function pageText(string $html): array
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        // The XML declaration is how DOMDocument is told the bytes are UTF-8.
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($document);

        $lead = [];
        foreach (['//title', '//meta[@property="og:description"]/@content', '//meta[@name="description"]/@content'] as $query) {
            $node = $xpath->query($query)->item(0);
            if ($node && trim($node->textContent) !== '') {
                $lead[] = trim($node->textContent);
            }
        }

        foreach (iterator_to_array($xpath->query('//script|//style|//noscript|//template|//svg|//iframe|//nav|//header|//footer|//form|//aside|//head')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        // Prefer what the page itself calls its main content, when that is not just a stub.
        $root = $document->getElementsByTagName('body')->item(0) ?? $document->documentElement;
        foreach (['//main', '//*[@role="main"]', '//article'] as $query) {
            $candidate = $xpath->query($query)->item(0);
            if ($candidate && mb_strlen(trim($candidate->textContent)) > 500) {
                $root = $candidate;

                break;
            }
        }

        $text = $root ? $this->blockText($root) : '';
        $text = implode("\n", array_unique($lead))."\n\n".$text;
        // One blank line at most, and no trailing spaces.
        $text = trim(preg_replace(["/[ \t\x{00A0}]+/u", "/ *\n */", "/\n{3,}/"], [' ', "\n", "\n\n"], $text));

        if (mb_strlen($text) <= self::MAX_TEXT) {
            return [$text, false];
        }

        $cut = mb_substr($text, 0, self::MAX_TEXT);
        $lastBreak = mb_strrpos($cut, "\n");

        return [$lastBreak > self::MAX_TEXT / 2 ? mb_substr($cut, 0, $lastBreak) : $cut, true];
    }

    /** Text with a line break after each block element, so a list of events stays a list. */
    private function blockText(\DOMNode $node): string
    {
        static $blocks = ['p', 'div', 'li', 'tr', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'section', 'article', 'ul', 'ol', 'table', 'dt', 'dd', 'time', 'address'];

        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->textContent;
        }

        $text = '';
        foreach ($node->childNodes as $child) {
            $text .= $this->blockText($child);
        }

        return in_array(strtolower($node->nodeName), $blocks, true) ? $text."\n" : $text.' ';
    }

    private function refusal(string $reason): LinkImportException
    {
        return new LinkImportException($reason, __('messages.link_import_'.$reason));
    }
}
