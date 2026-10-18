<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Support\Str;

/**
 * A schedule's list in its owner's own words: the wording of its Events graphic caption (what
 * an organizer posts to WhatsApp each week, said the same way on the list), or a wording of the
 * list's own, written with the same {variables}.
 *
 * Chosen in the schedule form (Style, Events, "Event list wording") and stored in
 * graphic_settings ('list_wording' is standard, caption or custom; 'list_template' holds the
 * list's own), because roles is at its row limit. Until an owner chooses one, nothing about the
 * list changes. The caption itself is never touched here: this reads a template and fills it
 * by the LIST's rules, so the text a schedule shares and sends does not move by a byte.
 *
 * What the caption decides and what stays the list's:
 *  - WHEN is the list's. Its day headings and each card's time line stay, so the caption's date
 *    and time words are empty here, and the lines above the name (nearly always the date and
 *    the time) are left out.
 *  - WHAT is the caption's: the line holding {event_name} is the card's heading, as written, and
 *    up to three lines under it are the card's text.
 *  - What it costs is the chip's (price, From, Sold out, Few left, Free entry), which is live and
 *    in the visitor's own format: {price} is the lowest of every ticket row as a bare number,
 *    and a currency the owner typed beside it cannot follow the event's.
 *  - {url} and {number} mean nothing on a card that is itself the link.
 *
 * An empty word takes its own label with it ("Tickets: {url}", "🕗 {time} | 📍 {venue}" reads
 * "📍 Blue Room"), formatting (*bold*, _italic_, ~strike~) is read from the template and never
 * from an event's own text, and a custom field is printed only where the event page prints it.
 *
 * Shown only to a visitor reading the language the wording is written in: the owner's own words
 * around the values cannot be translated.
 */
class ListWording
{
    /** Words the list says itself, or that mean nothing on a card. Always empty here. */
    public const LIST_OWNS = [
        'day_name', 'day_short', 'date_dmy', 'date_mdy', 'date_full_dmy', 'date_full_mdy',
        'month', 'month_pad', 'month_name', 'month_short', 'day', 'day_pad', 'year',
        'time', 'end_time', 'url', 'number',
        'currency', 'price', 'coupon_discount', 'discounted_price', 'original_price',
    ];

    /** Where it is: what a wide card's venue tile already says. */
    public const LOCATION = ['venue', 'city', 'address', 'state', 'country'];

    /** Lines under the name a card shows; the event page has the rest. */
    public const MAX_BODY_LINES = 3;

    /** A {description} is a sentence or two on a card, not the event page's whole text. */
    public const DESCRIPTION_LIMIT = 160;

    /** What separates the parts of a line: an empty part goes, and its separator with it. */
    private const SEPARATOR = '/(\s+\|\s+|\|\s*|\s+[·•]\s+|[·•]\s*|\s+[-–]\s+|,\s+|\s+\/\s+)/u';

    /** @var array<string, array|null> compiled templates, by template */
    private static array $compiled = [];

    /** The three choices, as stored and as the schedule form posts them. */
    public const MODES = ['standard', 'caption', 'custom'];

    /** @var array<string, bool> by schedule and language, for one request's list */
    private static array $applies = [];

    /** Standard (the list's own cards), caption (the Events graphic caption's wording) or custom. */
    public static function mode(?Role $role): string
    {
        $mode = ($role?->graphic_settings ?? [])['list_wording'] ?? null;

        return in_array($mode, self::MODES, true) ? $mode : 'standard';
    }

    /** Whether $role's list reads its events in its own wording for a visitor reading $lang. */
    public static function appliesTo(?Role $role, ?string $lang): bool
    {
        if (! $role || ! $lang || self::mode($role) === 'standard') {
            return false;
        }

        $settings = $role->graphic_settings ?? [];
        $key = $role->id.'|'.$lang.'|'.md5(json_encode([$settings['list_wording'] ?? null, $settings['list_template'] ?? null, $settings['text_template'] ?? null, $settings['force_english'] ?? null, $role->language_code]));

        return self::$applies[$key] ??= $lang === self::language($role)
            && self::compile(self::template($role)) !== null;
    }

    /**
     * The language the wording is written in, which is the only one its own words read in: the
     * caption's (English when its "In English" is on), or the schedule's for a wording of its own.
     */
    public static function language(Role $role): string
    {
        $settings = $role->graphic_settings ?? [];
        $english = self::mode($role) === 'caption' && $role->canForceEnglish() && ! empty($settings['force_english']);

        return $english ? 'en' : ($role->language_code ?: 'en');
    }

    /** The template the list is worded with: the caption's, or the list's own ('' when it has none). */
    public static function template(Role $role): string
    {
        $settings = $role->graphic_settings ?? [];

        if (self::mode($role) === 'custom') {
            return trim((string) ($settings['list_template'] ?? ''));
        }

        return (string) (($settings['text_template'] ?? '') ?: EventTextGenerator::getDefaultTemplate());
    }

    /**
     * An event's card in the caption's words: ['dir' => 'ltr'|'rtl', 'name' => parts, 'body' =>
     * [['p' => parts, 'loc' => bool], ...]], or null when its card is the standard one.
     *
     * A part is ['v' => text, 'k' => 'lit'|'name'|'venue'|'val', 'c' => classes, 'd' => true for
     * a literal that is only decoration (a screen reader skips it), 'l' => true for a value with
     * no letters (18+, 50 ₪), which is drawn left to right in a right-to-left line]. 'loc' marks
     * a line that only says where, which a wide card's venue tile already says.
     */
    public static function card(Event $event, Role $role, string $lang): ?array
    {
        if ($event->isPasswordProtected() || ! self::appliesTo($role, $lang)) {
            return null;
        }

        return self::render(self::template($role), $event, $role, $lang);
    }

    /** The card for $template, whatever the schedule has chosen: the schedule form's preview. */
    public static function render(string $template, Event $event, Role $role, string $lang): ?array
    {
        $compiled = self::compile($template);
        if ($compiled === null) {
            return null;
        }

        $values = self::values($event, $role, $lang, $compiled['tokens']);
        $lines = [];
        foreach ($compiled['lines'] as $i => $line) {
            $parts = self::fill($line, $values, $i === 0);
            if ($parts !== null) {
                $lines[] = $parts;
            }
            if ($i === 0 && $parts === null) {
                return null;
            }
            if (count($lines) > self::MAX_BODY_LINES) {
                break;
            }
        }

        $name = array_shift($lines);
        if (! $name || ! collect($name['p'])->contains(fn ($part) => $part['k'] === 'name')) {
            return null;
        }

        return [
            'dir' => content_dir_for_language(null, $lang),
            'name' => $name['p'],
            'body' => $lines,
        ];
    }

    /**
     * The template's lines from the one holding {event_name}, each a list of parts with the
     * formatting the template gives them. Null when the template names no event: its list keeps
     * the standard cards.
     *
     * @return array{lines: list<list<array{t: string, v: string, b: bool, i: bool, s: bool}>>, tokens: list<string>}|null
     */
    public static function compile(string $template): ?array
    {
        if (trim($template) === '') {
            return null;
        }
        if (array_key_exists($template, self::$compiled)) {
            return self::$compiled[$template];
        }

        $lines = [];
        $tokens = [];
        $started = false;
        foreach (preg_split('/\r\n|\r|\n/', $template) as $line) {
            if (! $started) {
                if (! str_contains($line, '{event_name}')) {
                    continue;
                }
                $started = true;
            }

            $parts = self::parseLine($line);
            foreach ($parts as $part) {
                if ($part['t'] === 'tok') {
                    $tokens[$part['v']] = true;
                }
            }
            $lines[] = $parts;
        }

        return self::$compiled[$template] = $started ? ['lines' => $lines, 'tokens' => array_keys($tokens)] : null;
    }

    /**
     * One line as literal text and {tokens}, with WhatsApp's formatting read the way WhatsApp
     * reads it: a marker opens only where a word starts (after a space or punctuation, before
     * something that is not a space) and closes only where one ends, so "Doors ~8pm to ~11pm"
     * and "@my_band" are text, not a strike-through and an italic.
     *
     * @return list<array{t: string, v: string, b: bool, i: bool, s: bool}>
     */
    private static function parseLine(string $line): array
    {
        // Items: one per character, one per token.
        $items = [];
        $known = self::knownTokens();
        $offset = 0;
        while (preg_match('/\{([a-z0-9_]+)\}/', $line, $m, PREG_OFFSET_CAPTURE, $offset)) {
            foreach (mb_str_split(substr($line, $offset, $m[0][1] - $offset)) as $char) {
                $items[] = ['c', $char];
            }
            $items[] = in_array($m[1][0], $known, true) ? ['tok', $m[1][0]] : ['c', $m[0][0]];
            $offset = $m[0][1] + strlen($m[0][0]);
        }
        foreach (mb_str_split(substr($line, $offset)) as $char) {
            $items[] = ['c', $char];
        }

        $isMarker = fn ($item) => $item[0] === 'c' && in_array($item[1], ['*', '_', '~'], true);
        $isSpace = fn ($item) => $item === null || ($item[0] === 'c' && preg_match('/^\s$/u', $item[1]));
        $isWord = fn ($item) => $item !== null && ($item[0] === 'tok' || preg_match('/^[\p{L}\p{N}]$/u', $item[1]));

        // Pair each opening marker with the nearest closing marker of its kind.
        $style = array_fill(0, count($items), ['b' => false, 'i' => false, 's' => false]);
        $drop = [];
        $flag = ['*' => 'b', '_' => 'i', '~' => 's'];
        $count = count($items);
        for ($open = 0; $open < $count; $open++) {
            if (isset($drop[$open]) || ! $isMarker($items[$open])) {
                continue;
            }
            $before = $items[$open - 1] ?? null;
            $after = $items[$open + 1] ?? null;
            if ($isWord($before) || $isSpace($after) || ($after && $isMarker($after) && $after[1] === $items[$open][1])) {
                continue;
            }
            for ($close = $open + 2; $close < $count; $close++) {
                if (isset($drop[$close]) || ! $isMarker($items[$close]) || $items[$close][1] !== $items[$open][1]) {
                    continue;
                }
                if ($isSpace($items[$close - 1] ?? null) || $isWord($items[$close + 1] ?? null)) {
                    continue;
                }
                $drop[$open] = $drop[$close] = true;
                for ($k = $open + 1; $k < $close; $k++) {
                    $style[$k][$flag[$items[$open][1]]] = true;
                }
                break;
            }
        }

        // Merge runs of text with the same style; a token is a part of its own.
        $parts = [];
        foreach ($items as $k => $item) {
            if (isset($drop[$k])) {
                continue;
            }
            $s = $style[$k];
            if ($item[0] === 'tok') {
                $parts[] = ['t' => 'tok', 'v' => $item[1]] + $s;

                continue;
            }
            $last = count($parts) - 1;
            if ($last >= 0 && $parts[$last]['t'] === 'lit' && $parts[$last]['b'] === $s['b'] && $parts[$last]['i'] === $s['i'] && $parts[$last]['s'] === $s['s']) {
                $parts[$last]['v'] .= $item[1];
            } else {
                $parts[] = ['t' => 'lit', 'v' => $item[1]] + $s;
            }
        }

        return $parts;
    }

    /**
     * A compiled line with this event's values, its empty parts gone: ['p' => parts, 'loc' =>
     * bool], or null when nothing worth a line is left.
     */
    private static function fill(array $line, array $values, bool $isName): ?array
    {
        // Split literal text at separators, so each part of the line stands alone.
        $seq = [];
        foreach ($line as $part) {
            if ($part['t'] === 'tok') {
                $seq[] = $part + ['val' => (string) ($values[$part['v']] ?? '')];

                continue;
            }
            foreach (preg_split(self::SEPARATOR, $part['v'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $piece) {
                $seq[] = ['t' => preg_match(self::SEPARATOR, $piece) && preg_replace(self::SEPARATOR, '', $piece) === '' ? 'sep' : 'lit', 'v' => $piece] + $part;
            }
        }

        // Chunks between separators: one whose words are all empty goes whole, label and all.
        $chunks = [[]];
        $seps = [];
        foreach ($seq as $item) {
            if ($item['t'] === 'sep') {
                $seps[] = $item;
                $chunks[] = [];
            } else {
                $chunks[count($chunks) - 1][] = $item;
            }
        }

        $kept = [];
        foreach ($chunks as $c => $chunk) {
            $toks = array_filter($chunk, fn ($item) => $item['t'] === 'tok');
            if ($toks && ! array_filter($toks, fn ($item) => trim($item['val']) !== '')) {
                continue;
            }
            $chunk = self::dropEmptyWords($chunk);
            if (! $chunk) {
                continue;
            }
            $kept[] = ['sep' => $c > 0 ? $seps[$c - 1] : null, 'items' => $chunk];
        }

        $parts = [];
        foreach ($kept as $n => $chunk) {
            if ($n > 0 && $chunk['sep']) {
                $parts[] = self::part($chunk['sep']['v'], 'lit', $chunk['sep']);
            }
            foreach ($chunk['items'] as $item) {
                if ($item['t'] === 'tok') {
                    $kind = $item['v'] === 'event_name' ? 'name' : ($item['v'] === 'venue' ? 'venue' : 'val');
                    $parts[] = self::part($item['val'], $kind, $item, $item['v']);
                } else {
                    $parts[] = self::part($item['v'], 'lit', $item);
                }
            }
        }

        // The heading's own colon introduced what came under it in a message; a card's layout
        // does that. And spaces at either end are the line's, not the text's.
        $parts = self::trimEnds($parts, $isName);
        if (! $parts) {
            return null;
        }

        $text = implode('', array_column($parts, 'v'));
        if (! preg_match('/[\p{L}\p{N}]/u', $text)) {
            return null;
        }

        $parts = self::glue($parts);
        $words = array_filter($parts, fn ($part) => $part['k'] !== 'lit' && trim($part['v']) !== '');
        $loc = $words && ! array_filter($words, fn ($part) => ! in_array($part['token'], self::LOCATION, true));

        return ['p' => array_map(fn ($part) => array_filter([
            'v' => $part['v'],
            'k' => $part['k'],
            'c' => $part['c'],
            'd' => $part['d'] ?: null,
            'l' => $part['l'] ?: null,
        ], fn ($value) => $value !== null && $value !== ''), $parts), 'loc' => (bool) $loc];
    }

    /**
     * Inside a chunk, an empty word takes the text between it and the word before it, and a
     * suffix that touches it: "{event_name} at {time}" reads "Name", "{price}₪" reads nothing.
     */
    private static function dropEmptyWords(array $chunk): array
    {
        $out = [];
        $count = count($chunk);
        for ($k = 0; $k < $count; $k++) {
            $item = $chunk[$k];
            if ($item['t'] !== 'tok' || trim($item['val']) !== '') {
                $out[] = $item;

                continue;
            }
            // Back to the last word kept (or the chunk's start).
            while ($out && end($out)['t'] === 'lit') {
                array_pop($out);
            }
            // And the suffix touching it, up to a space.
            if (isset($chunk[$k + 1]) && $chunk[$k + 1]['t'] === 'lit') {
                $rest = preg_replace('/^\S+/u', '', $chunk[$k + 1]['v']);
                if ($rest === '') {
                    $k++;
                } else {
                    $chunk[$k + 1]['v'] = $rest;
                }
            }
        }

        return $out;
    }

    private static function part(string $text, string $kind, array $style, ?string $token = null): array
    {
        $classes = trim(($style['b'] ? 'gk-w-b ' : '').($style['i'] ? 'gk-w-i ' : '').($style['s'] ? 'gk-w-s' : ''));

        return [
            'v' => $text,
            'k' => $kind,
            'c' => $classes,
            'd' => $kind === 'lit' && trim($text) !== '' && ! preg_match('/[\p{L}\p{N}]/u', $text),
            'l' => $kind !== 'lit' && ! preg_match('/\p{L}/u', $text),
            'token' => $token,
        ];
    }

    /** Separators and spaces left at either end go; a heading also loses a closing colon. */
    private static function trimEnds(array $parts, bool $isName): array
    {
        $junk = $isName ? '/^[\s|·•,:\-–\/]+$/u' : '/^[\s|·•,\-–\/]+$/u';
        while ($parts && $parts[0]['k'] === 'lit' && preg_match($junk, $parts[0]['v'])) {
            array_shift($parts);
        }
        while ($parts && end($parts)['k'] === 'lit' && preg_match($junk, end($parts)['v'])) {
            array_pop($parts);
        }
        if ($parts && $parts[0]['k'] === 'lit') {
            $parts[0]['v'] = ltrim($parts[0]['v']);
        }
        $last = count($parts) - 1;
        if ($last >= 0 && $parts[$last]['k'] === 'lit') {
            $parts[$last]['v'] = $isName ? rtrim($parts[$last]['v'], " \t:") : rtrim($parts[$last]['v']);
            if ($parts[$last]['v'] === '') {
                array_pop($parts);
            }
        }

        return $parts;
    }

    /** A decoration keeps to the word beside it: no 📍 or 🔥 left alone at the end of a line. */
    private static function glue(array $parts): array
    {
        $count = count($parts);
        for ($k = 0; $k < $count; $k++) {
            // Not a separator: a long line still breaks at its " | ".
            if ($parts[$k]['k'] !== 'lit' || ! $parts[$k]['d'] || preg_match('/[|·•,\/\-–]/u', $parts[$k]['v'])) {
                continue;
            }
            if (isset($parts[$k + 1]) && $parts[$k + 1]['k'] !== 'lit') {
                $parts[$k]['v'] = preg_replace('/\s+$/u', "\u{00A0}", $parts[$k]['v']);
            }
            if (isset($parts[$k - 1]) && $parts[$k - 1]['k'] !== 'lit') {
                $parts[$k]['v'] = preg_replace('/^\s+/u', "\u{00A0}", $parts[$k]['v']);
            }
        }

        return $parts;
    }

    /**
     * The values a card prints, by the list's rules: the language the visitor reads, the venue
     * left to the venue's own schedule, a custom field only where the event page prints it.
     */
    public static function values(Event $event, Role $role, string $lang, array $want): array
    {
        $values = [];
        $want = array_diff($want, self::LIST_OWNS);
        $venue = $event->venue;
        $ownVenue = $venue && (int) $venue->id === (int) $role->id;

        foreach ($want as $token) {
            $values[$token] = match ($token) {
                'event_name' => $event->nameInLanguage($lang, $role),
                'short_description' => (string) $event->shortDescriptionInLanguage($lang, $role),
                'description' => Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], ' ', (string) $event->descriptionHtmlInLanguage($lang, $role))), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), self::DESCRIPTION_LIMIT),
                'venue' => $ownVenue ? '' : ($venue ? (string) $venue->textInLanguage('name', $lang) : (string) $event->getVenueDisplayName(true, $lang)),
                'city' => $ownVenue || ! $venue ? '' : (string) $venue->textInLanguage('city', $lang),
                'address' => $ownVenue || ! $venue ? '' : (string) $venue->textInLanguage('address1', $lang),
                'state' => $ownVenue || ! $venue ? '' : (string) $venue->textInLanguage('state', $lang),
                'country' => $ownVenue || ! $venue ? '' : (string) $venue->country,
                'duration' => (string) ($event->duration ?? ''),
                'coupon_code' => (string) ($event->coupon_code ?? ''),
                default => '',
            };
        }

        if (array_filter($want, fn ($token) => str_starts_with($token, 'custom_'))) {
            $values = array_merge($values, self::customValues($event, $role));
        }

        return $values;
    }

    /**
     * {custom_N} is field N of THIS schedule's fields, so it is filled only from answers that
     * are this schedule's, and only those the event page prints (CustomFieldDisplay).
     */
    private static function customValues(Event $event, Role $role): array
    {
        $values = [];
        if (! $event->customFieldValuesBelongTo($role)) {
            return $values;
        }

        $answers = collect(CustomFieldDisplay::forEventPage($event, $role))->pluck('value', 'key');
        $fallback = 1;
        foreach ($role->event_custom_fields ?? [] as $key => $field) {
            $index = $field['index'] ?? $fallback;
            $fallback++;
            if ($index >= 1 && $index <= 10 && isset($answers[(string) $key])) {
                $values['custom_'.$index] = (string) $answers[(string) $key];
            }
        }

        return $values;
    }

    /** The {tokens} the caption knows, so an unknown one is printed as the caption prints it. */
    private static function knownTokens(): array
    {
        static $known = null;

        return $known ??= array_merge(self::LIST_OWNS, self::LOCATION, [
            'event_name', 'short_description', 'description', 'duration', 'coupon_code',
        ], array_map(fn ($n) => 'custom_'.$n, range(1, 10)));
    }

    /**
     * A line's parts as HTML, for a card the server draws (event/partials/more-events), where the
     * whole card is one link: the name and the venue are text there. The Vue cards draw the same
     * parts with role/partials/wording-part. Every part's text is escaped.
     */
    public static function html(array $parts): string
    {
        $html = '';
        foreach ($parts as $part) {
            $class = isset($part['c']) ? ' class="'.e($part['c']).'"' : '';
            if (($part['k'] ?? 'lit') === 'lit') {
                $html .= '<span'.$class.(! empty($part['d']) ? ' aria-hidden="true"' : '').'>'.e($part['v']).'</span>';
            } else {
                $html .= '<bdi'.$class.(! empty($part['l']) ? ' dir="ltr"' : '').'>'.e($part['v']).'</bdi>';
            }
        }

        return $html;
    }

    /** For tests: forget what one request worked out. */
    public static function flush(): void
    {
        self::$compiled = [];
        self::$applies = [];
    }
}
