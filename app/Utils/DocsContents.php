<?php

namespace App\Utils;

/**
 * A guide's own table of contents, read from the page that prints it.
 *
 * Every guide declares its "On this page" list in its view: the toc slot of <x-docs-page>,
 * written with <x-doc-nav-group> and <x-doc-nav-link>. The /docs home shows those same entries
 * beside each guide's name, so it reads them from the view instead of keeping a second list
 * that would drift the first time a section was added, moved or renamed.
 *
 * The slot opens the page, so only the head of the file is read unless the slot runs past it.
 * Results are kept for the request: the home page asks for some twenty guides.
 */
class DocsContents
{
    /** Entries every guide ends with. They are furniture, not a part of the guide. */
    private const FURNITURE = ['see-also', 'next-steps'];

    /** How much of a view is read first. The longest contents list today ends near 5 KB. */
    private const HEAD_BYTES = 16384;

    /** @var array<string, array<int, array{label: string, anchor: string, children: array<int, array{label: string, anchor: string}>}>> */
    private static array $memo = [];

    /** @var array<string, array<string, string>> */
    private static array $shots = [];

    /**
     * The top-level entries of a guide's contents list, in the page's order, each with the
     * entries grouped under it. Empty for a page that prints no list (a hub) or an unknown key.
     *
     * A group that is only a heading (the API reference's, the settings guide's) takes the
     * anchor of the first entry under it, so every entry here can be linked.
     *
     * @return array<int, array{label: string, anchor: string, children: array<int, array{label: string, anchor: string}>}>
     */
    public static function for(string $key): array
    {
        if (! array_key_exists($key, self::$memo)) {
            $path = resource_path('views/marketing/docs/'.$key.'.blade.php');

            self::$memo[$key] = (DocsUtils::page($key) !== null && is_file($path))
                ? self::parse(self::slot($path))
                : [];
        }

        return self::$memo[$key];
    }

    /**
     * The picture a section of a guide carries, for each top-level entry that has one: the first
     * screenshot the guide prints between that section's start and the next entry's.
     *
     * The home page shows it on the stage while that section's line is pointed at, so a guide
     * that gains or loses a screenshot changes what its line shows with no second list to edit.
     *
     * @return array<string, string>  anchor => file name under public/images/docs
     */
    public static function shots(string $key): array
    {
        if (array_key_exists($key, self::$shots)) {
            return self::$shots[$key];
        }

        $entries = self::for($key);
        $path = resource_path('views/marketing/docs/'.$key.'.blade.php');

        if ($entries === [] || ! is_file($path)) {
            return self::$shots[$key] = [];
        }

        $source = (string) file_get_contents($path);

        // Where each screenshot is printed. Its id is a file name, never an anchor, so the tags
        // are blanked out (at the same length) before the sections are looked for.
        if (! preg_match_all('~<x-doc-screenshot\b[^>]*?\bid="([^"]+)"[^>]*>~', $source, $tags, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return self::$shots[$key] = [];
        }

        $plain = $source;
        foreach ($tags as $tag) {
            $plain = substr_replace($plain, str_repeat(' ', strlen($tag[0][0])), $tag[0][1], strlen($tag[0][0]));
        }

        // Where each listed section starts, and which top-level entry it belongs to.
        $starts = [];
        foreach ($entries as $entry) {
            foreach (array_merge([$entry], $entry['children']) as $part) {
                if (preg_match('~\bid="'.preg_quote($part['anchor'], '~').'"~', $plain, $at, PREG_OFFSET_CAPTURE)) {
                    $starts[$at[0][1]] = $entry['anchor'];
                }
            }
        }
        ksort($starts);

        $found = [];
        foreach ($tags as $tag) {
            $owner = null;

            foreach ($starts as $position => $anchor) {
                if ($position > $tag[0][1]) {
                    break;
                }
                $owner = $anchor;
            }

            if ($owner !== null && ! isset($found[$owner])) {
                $found[$owner] = $tag[1][0];
            }
        }

        return self::$shots[$key] = $found;
    }

    /**
     * The inside of a view's toc slot, or '' when it has none.
     */
    private static function slot(string $path): string
    {
        $head = (string) file_get_contents($path, false, null, 0, self::HEAD_BYTES);
        $from = strpos($head, '<x-slot:toc>');

        // Not whole in the head of the file: read all of it before saying there is none.
        if (($from === false || strpos($head, '</x-slot:toc>', $from) === false) && strlen($head) === self::HEAD_BYTES) {
            $head = (string) file_get_contents($path);
            $from = strpos($head, '<x-slot:toc>');
        }

        if ($from === false) {
            return '';
        }

        $to = strpos($head, '</x-slot:toc>', $from);

        return $to === false ? '' : substr($head, $from + 12, $to - $from - 12);
    }

    /**
     * Read a toc slot's tags into entries. Public so a test can hand it markup of its own.
     *
     * @return array<int, array{label: string, anchor: string, children: array<int, array{label: string, anchor: string}>}>
     */
    public static function parse(string $slot): array
    {
        $entries = [];
        $open = null;   // index of the group being read, when inside one

        // A link that is commented out is not on the page.
        $slot = (string) preg_replace('~\{\{--.*?--\}\}~s', '', $slot);

        $found = preg_match_all(
            '~<x-doc-nav-group\b([^>]*)>|</x-doc-nav-group>|<x-doc-nav-link\b([^>]*)>(.*?)</x-doc-nav-link>~s',
            $slot,
            $tags,
            PREG_SET_ORDER
        );

        if (! $found) {
            return [];
        }

        foreach ($tags as $tag) {
            if ($tag[0] === '</x-doc-nav-group>') {
                $open = null;

                continue;
            }

            if (str_starts_with($tag[0], '<x-doc-nav-group')) {
                $entries[] = [
                    'label' => self::text(self::attribute($tag[1], 'label')),
                    'anchor' => ltrim(self::attribute($tag[1], 'href'), '#'),
                    'children' => [],
                ];
                $open = array_key_last($entries);

                continue;
            }

            $entry = [
                'label' => self::text($tag[3]),
                'anchor' => ltrim(self::attribute($tag[2], 'href'), '#'),
            ];

            if ($entry['label'] === '' || $entry['anchor'] === '' || in_array($entry['anchor'], self::FURNITURE, true)) {
                continue;
            }

            if ($open !== null) {
                $entries[$open]['children'][] = $entry;
            } else {
                $entries[] = $entry + ['children' => []];
            }
        }

        foreach ($entries as $i => $entry) {
            if ($entry['anchor'] === '' && $entry['children'] !== []) {
                $entries[$i]['anchor'] = $entry['children'][0]['anchor'];
            }
        }

        return array_values(array_filter(
            $entries,
            fn ($entry) => $entry['label'] !== ''
                && $entry['anchor'] !== ''
                && ! in_array($entry['anchor'], self::FURNITURE, true)
        ));
    }

    private static function attribute(string $attributes, string $name): string
    {
        return preg_match('~\b'.preg_quote($name, '~').'="([^"]*)"~', $attributes, $m) ? $m[1] : '';
    }

    /**
     * A label as plain text: the views write "&amp;" and the page that prints this escapes again.
     */
    private static function text(string $markup): string
    {
        return trim(html_entity_decode(strip_tags($markup), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
