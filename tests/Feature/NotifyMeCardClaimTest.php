<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Keeps the marketing site from describing the "Notify me" card as something every page has.
 *
 * Until 2026-09-17 every upcoming public event page carried "Tell me when tickets go on sale". It is
 * now the "Notify me" card, a free per-schedule switch under Settings > Advanced
 * (roles.show_event_interest) that is OFF by default - existing schedules included, because the
 * migration's column default applied to them - and EventInterestController::store() refuses new
 * sign-ups while it is off. About eighty sentences across fifty marketing pages said a page simply
 * offers it ("its page offers ...", "a visitor leaves just an email address on the event page").
 * Each was true the day it was written and false the day the default flipped, and none of them
 * mentions anything a search for the setting would find.
 *
 * Three checks, because any one alone passes on the wrong fix:
 *  - the corrected phrasings must not come back;
 *  - a sentence saying a page offers the card (or that a visitor can press or use it) must say, in
 *    that sentence or the one before, that it has to be switched on;
 *  - a page that quotes the card by name must say so somewhere, so copy that was deleted rather
 *    than corrected, or rewritten in a voice the patterns above do not know, still fails.
 *
 * Scanned: every marketing view (the user guide included, since its pages describe the card too),
 * the comparison and alternative copy in MarketingController, and public/llms-full.txt, which AI
 * agents are pointed at. Comments are stripped first - they quote the old claim on purpose - and so
 * is MarketingController::getDocSearchIndex(), whose entries name the switch and would otherwise
 * satisfy the page check for every FAQ in that file.
 */
class NotifyMeCardClaimTest extends TestCase
{
    /** The card's title, as a page quotes it. Matched case-insensitively. */
    private const CARD_TITLE = 'tell me when tickets go on sale';

    /**
     * Wording that tells the reader the card has to be turned on first: the switch named by its
     * label, or, on pages that speak to visitors rather than organizers, the hedge they get instead.
     *
     * Deliberately NOT a bare "switch on". Pages say "switch it on" about booking requests and
     * registration too, and a page reverted to the old copy passed on one of those. The u flag is
     * load-bearing: without it the curly quotes are matched a byte at a time, and /pricing, which
     * writes “Notify me”, reads as unqualified.
     */
    private const QUALIFIER = '~(?:["“]|&ldquo;)notify me(?:["”]|&rdquo;) card|where the organizer offers it~iu';

    /** A sentence that puts the card on a page: "offers ...", "press ...", "use ...", "click ...". */
    private const OFFER_CLAIM = '~\b(offers?|press|click|use)\s+["“]?tell me (when tickets go on sale|if anything changes)~iu';

    /**
     * The phrasings the 2026-09-17 sweep corrected. Each one described the card as always there,
     * and each is written so that it matches the old sentence and not its corrected form.
     */
    private const STALE = [
        '~before (it sells|tickets are (ready|on sale)|its tickets are on sale|you add its tickets) and (its|the|each)( event| performance)? page offers~i',
        '~every plan\. until tickets go on sale, an event page offers~i',
        '~every plan\. until an event sells, its page offers~i',
        '~once it is public, every performance offers~i',
        '~until they open, the event page offers~i',
        '~announce before you sell\. until tickets open, the event page offers~i',
        '~not on sale yet, the event page also offers~i',
        '~\'an event with nothing on sale yet offers~i',
        '~<p[^>]*>\s*an upcoming public event page offers~i',
        '~on the event page a visitor can leave an email address, and nothing else~i',
        '~every plan\. on a public event page a fan can leave~i',
        '~every plan\. from add to calendar on the event page, a visitor leaves~i',
        '~every plan\. on the page for a class date~i',
        '~every plan\. on a session that is not on sale yet~i',
        '~yes\. on an event with nothing on sale yet, a visitor can press~i',
        '~yes\. beside the register button, "tell me if anything changes"~i',
        '~yes\. somebody not ready to take a place can press~i',
        '~yes, on every plan\. a public session~i',
        '~yes\. anyone can leave just an email address on the night~i',
        '~yes\. on that date.{1,2}s page a guest can leave~i',
        '~on an event page, a visitor can leave just an email address under~i',
        '~every plan\. on an event page, visitors leave an email address~i',
        '~the notify-me form, built into the event page\. visitors leave~i',
        '~every plan\. on an event with nothing on sale yet, visitors click~i',
        '~the free interest list takes just an email address~i',
        '~every plan\. on an event that is not selling yet, visitors leave~i',
        '~\band an interest list on each event that emails~i',
        '~no, it is free on every plan\. a visitor leaves just an email address~i',
        '~\'on an event page, "tell me when tickets go on sale"~i',
        '~no\. on the event page, "tell me when tickets go on sale" takes~i',
        '~ask to hear when tickets go on sale and often submit~i',
        '~open the event and use tell me when tickets go on sale~i',
        '~calendar feed and asking to be told when tickets go on sale~i',
        '~and on an upcoming public event "tell me when tickets go on sale"~i',
        '~\'the interest list: an email address and nothing else~i',
        '~on the event page a click opens in a new tab, along with~i',
        '~and on an upcoming event, a box to leave an email address~i',
        '~beside the buy button, which takes an email address~i',
        '~not ready to buy\. they leave just an email address~i',
        '~announce it before it sells, and collectors can ask~i',
        '~before it goes on sale, people can leave an email address~i',
        '~on a single show, anyone can leave just an email address~i',
        '~announce a show before it goes on sale and fans can leave~i',
        '~announce the show before tickets exist and a fan can leave~i',
        '~nothing but an email address\. announce the show~i',
        '~whole run\. anyone who joins the~i',
        '~before tickets are ready and parents can ask~i',
        '~not on sale yet\? fans can leave just an email address~i',
        '~post it before tickets open and shoppers can leave~i',
        '~before tickets open and guests can ask to be told~i',
        '~\'a guest leaves an email address on the dinner~i',
        '~announce it before tickets open and patrons can leave~i',
        '~until it opens, fans can leave an email address~i',
        '~in march\. somebody not ready to register can press~i',
        '~announced before tickets are ready\? fans can leave~i',
        '~announce the feature before tickets are on sale and people can leave~i',
        '~(?<!, )on a show that is not on sale yet, (a fan|fans) can leave~i',
        '~(?<!, )for a night that is not on sale yet, a visitor can leave~i',
        '~or not ready to book\? a student can leave~i',
        '~fees\. before tickets go on sale, visitors can ask to be told~i',
        '~"tell me when tickets go on sale", and everyone registered~i',
        '~not selling yet, a visitor can leave just an email address~i',
        '~\'before anything is on sale, a visitor can leave~i',
        '~it is free on every plan, and it never offers anyone a seat~i',
        '~tickets are not on sale yet, its page lets them~i',
        '~yes\. put the dinner up with a date~i',
        '~go on sale\. anyone who leaves an email on the event page~i',
        '~(?<!, )on a public event, (a visitor who clicks through|anyone not ready to buy)~i',
    ];

    /**
     * Pages that quote the card by name without claiming a page offers it. Keyed file => a snippet
     * that must still be there, so an exemption cannot outlive the sentence that earned it.
     */
    private const QUOTES_WITHOUT_OFFERING = [
        // Says the sign-up is NOT offered on a hidden event, which the switch does not change.
        'private-events.blade.php' => 'sign-up is not offered on it',
    ];

    /** @return array<string, string> label => absolute path */
    private function scannedFiles(): array
    {
        $root = resource_path('views/marketing').'/';
        $files = [];

        foreach (File::allFiles($root) as $file) {
            $relative = str_replace($root, '', $file->getPathname());

            // The bundled legal documents are not copy.
            if (in_array($relative, ['privacy.blade.php', 'terms.blade.php', 'self-hosting-terms.blade.php'], true)) {
                continue;
            }

            $files[$relative] = $file->getPathname();
        }

        // The FAQ and feature arrays for the comparison and alternative pages live here.
        $files['MarketingController.php'] = app_path('Http/Controllers/MarketingController.php');
        $files['public/llms-full.txt'] = public_path('llms-full.txt');

        return $files;
    }

    /**
     * A file as the checks should read it: without comments, and for the controller without the
     * docs search index. The comment patterns are MarketingAudienceClaimTest's; the line-comment
     * arm must not carry /s, or `.*$` runs to the end of the file.
     */
    private function contents(string $label, string $path): string
    {
        $body = File::get($path);

        if ($label === 'MarketingController.php') {
            $start = strpos($body, 'function getDocSearchIndex(');

            if ($start !== false) {
                $end = preg_match('~\n    (?:public|protected|private)(?: static)? function ~', $body, $m, PREG_OFFSET_CAPTURE, $start)
                    ? $m[0][1]
                    : strlen($body);
                $body = substr($body, 0, $start).substr($body, $end);
            }
        }

        $body = preg_replace('~\{\{--.*?--\}\}|/\*.*?\*/~s', ' ', $body);

        return preg_replace('~^[ \t]*//.*$~m', ' ', $body);
    }

    public function test_the_corrected_phrasings_do_not_come_back(): void
    {
        $offenders = [];

        foreach ($this->scannedFiles() as $label => $path) {
            $contents = $this->contents($label, $path);

            foreach (self::STALE as $pattern) {
                if (preg_match($pattern, $contents, $m)) {
                    $offenders[] = "{$label}: {$m[0]}";
                }
            }
        }

        $this->assertSame([], $offenders,
            "Copy describes the \"Notify me\" card as always on. It is off by default and switched on per schedule:\n"
            .implode("\n", $offenders));
    }

    public function test_a_sentence_that_puts_the_card_on_a_page_says_it_is_switched_on(): void
    {
        $offenders = [];

        foreach ($this->scannedFiles() as $label => $path) {
            $sentences = preg_split('~(?<=[.!?])\s+~', $this->contents($label, $path));

            foreach ($sentences as $index => $sentence) {
                if (! preg_match(self::OFFER_CLAIM, $sentence)) {
                    continue;
                }

                // "Yes, once you switch on the card. Somebody can then press ..." is fine.
                $context = ($sentences[$index - 1] ?? '').' '.$sentence;

                if (! preg_match(self::QUALIFIER, $context)) {
                    $offenders[] = "{$label}: ".trim(mb_substr($sentence, 0, 160));
                }
            }
        }

        $this->assertSame([], $offenders,
            "A sentence offers the \"Notify me\" card without saying it has to be switched on:\n"
            .implode("\n", $offenders));
    }

    public function test_a_page_that_names_the_card_says_it_is_switched_on(): void
    {
        $offenders = [];

        foreach ($this->scannedFiles() as $label => $path) {
            $contents = $this->contents($label, $path);

            if (! str_contains(mb_strtolower($contents), self::CARD_TITLE)
                || isset(self::QUOTES_WITHOUT_OFFERING[$label])) {
                continue;
            }

            if (! preg_match(self::QUALIFIER, $contents)) {
                $offenders[] = $label;
            }
        }

        $this->assertSame([], $offenders,
            "These pages quote \"Tell me when tickets go on sale\" but never say the card is switched on:\n"
            .implode("\n", $offenders));
    }

    public function test_the_exemptions_have_no_stale_entries(): void
    {
        $files = $this->scannedFiles();

        foreach (self::QUOTES_WITHOUT_OFFERING as $label => $snippet) {
            $this->assertArrayHasKey($label, $files, "{$label} is exempt but no longer scanned.");
            $this->assertStringContainsString($snippet, File::get($files[$label]),
                "{$label} is exempt for a sentence it no longer contains. Remove the exemption.");
        }
    }
}
