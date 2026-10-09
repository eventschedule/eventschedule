<?php

namespace App\Services\Blog;

/**
 * The user guide's own words on what a post is about, as plain text for the prompt.
 *
 * A post that explains how to do something has to name the screen and the button. Given only a
 * one-line fact per feature, the model made them up ("Navigate to the Passes section, click
 * Create New Pass"; a buyer who "enters their pass code at checkout"). The guide already says
 * these things correctly, so the sections that match the brief's facts are sent with the post,
 * and the prompts allow a screen or a step to be named only when the excerpts name it.
 *
 * The text is read from the guide's Blade source and stripped, not rendered: a rendered guide
 * page needs a request, and nothing dynamic on it (a price, a signed-in person's own fields)
 * belongs in a prompt anyway.
 */
class BlogGuide
{
    /** Words of guide text sent with one post, and the most one section may contribute. */
    public const MAX_WORDS = 4500;

    public const MAX_SECTION_WORDS = 1400;

    /**
     * The sections of the guide that match these facts, best match first, within the word budget.
     *
     * @param  list<string>  $factIds
     */
    public static function excerpts(array $factIds, int $maxWords = self::MAX_WORDS): string
    {
        // guide page => the words that mark its relevant sections
        $wanted = [];
        $facts = BlogFacts::all();

        foreach ($factIds as $id) {
            $page = $facts[$id]['guide'] ?? null;

            if ($page) {
                $wanted[$page] = array_values(array_unique(array_merge($wanted[$page] ?? [], (array) ($facts[$id]['match'] ?? []))));
            }
        }

        $scored = [];

        foreach ($wanted as $page => $words) {
            foreach (self::sections($page) as $section) {
                $lower = mb_strtolower($section['text']);
                $title = mb_strtolower($section['title']);
                $hits = 0;

                foreach ($words as $word) {
                    $word = mb_strtolower($word);
                    $hits += substr_count($lower, $word) + (str_contains($title, $word) ? 5 : 0);
                }

                if ($hits > 0) {
                    // Density, not volume: a long section mentions every word a few times.
                    $scored[] = [$hits / (sqrt($section['words']) + 1), $page, $section];
                }
            }
        }

        usort($scored, fn ($a, $b) => $b[0] <=> $a[0]);

        $out = [];
        $total = 0;

        foreach ($scored as [, $page, $section]) {
            $text = $section['text'];
            $words = $section['words'];

            if ($words > self::MAX_SECTION_WORDS) {
                $text = implode(' ', array_slice(preg_split('~\s+~', $text) ?: [], 0, self::MAX_SECTION_WORDS)).' ...';
                $words = self::MAX_SECTION_WORDS;
            }

            if ($total + $words > $maxWords) {
                continue;
            }

            $out[] = '--- from the guide page '.self::url($page).', section "'.$section['title'].'"'."\n".$text;
            $total += $words;
        }

        return $out === [] ? '(no guide text applies to this topic)' : implode("\n\n", $out);
    }

    public static function url(string $page): string
    {
        return BlogLinks::base().'/docs/'.$page;
    }

    public static function exists(string $page): bool
    {
        return is_file(self::path($page));
    }

    private static function path(string $page): string
    {
        return resource_path('views/marketing/docs/'.basename($page).'.blade.php');
    }

    /**
     * A guide page as its h2 sections of plain text.
     *
     * @return list<array{title: string, text: string, words: int}>
     */
    public static function sections(string $page): array
    {
        if (! self::exists($page)) {
            return [];
        }

        $source = (string) file_get_contents(self::path($page));

        $strip = [
            '~\{\{--.*?--\}\}~s' => '',
            '~<(script|style)\b[^>]*>.*?</\1>~is' => '',
            '~<svg\b.*?</svg>~is' => '',
            '~<x-slot[^>]*name="(?:title|description|structuredData|headMeta)".*?</x-slot>~is' => '',
            '~@php\b.*?@endphp~s' => '',
            '~<h2\b[^>]*>~i' => "\n@@SECTION@@",
            '~<h3\b[^>]*>~i' => "\n### ",
            '~<li\b[^>]*>~i' => "\n- ",
            '~</(?:p|h2|h3|h4|tr|div|section)>~i' => "\n",
            '~</t[dh]>~i' => ' | ',
            '~<[^>]+>~' => '',
            '~\{\{.*?\}\}|\{!!.*?!!\}~s' => '',
            '~^\s*@\w+.*$~m' => '',
        ];

        $text = preg_replace(array_keys($strip), array_values($strip), $source) ?? '';
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace(['~[ \t]+~', '~\n\s*\n+~'], [' ', "\n"], $text) ?? '';

        $sections = [];

        foreach (array_slice(explode('@@SECTION@@', $text), 1) as $chunk) {
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $sections[] = [
                'title' => trim(strtok($chunk, "\n") ?: ''),
                'text' => $chunk,
                'words' => str_word_count($chunk),
            ];
        }

        return $sections;
    }
}
