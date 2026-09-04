<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * `app:send-event-announcements` is real and scheduled hourly on both rails. It
 * mails a digest of newly published public events to a schedule's CONFIRMED
 * role_subscribers, floored at usage.audience_announcement_min_hours per schedule
 * and outside the newsletter allowance.
 *
 * Before it shipped, "nothing is emailed automatically" was true, and an earlier
 * audit had removed the opposite claim as a fabrication - so the campaign wrote
 * the negation into page after page on purpose. It is now wrong, and it has been
 * corrected twice: once across roughly a dozen pages, and again here on seven
 * more that phrased it as "nothing goes out on its own", "no job that emails your
 * followers", "all of them are yours to trigger".
 *
 * The distinction that has to survive, because both halves are load-bearing:
 *
 *   - EMAIL SUBSCRIBERS (role_subscribers, double opt-in) get the digest, on
 *     their own, and it does not touch the newsletter allowance.
 *   - ACCOUNT FOLLOWERS (signed in, pressed Follow) get nothing automatically.
 *     A newsletter the owner writes is the only thing that reaches them.
 *   - A NEWSLETTER never sends itself. "No newsletter goes out on its own" is
 *     still true and must keep working, which is why the rule below is scoped
 *     rather than a ban on the words.
 */
class MarketingAudienceClaimTest extends TestCase
{
    /**
     * A sentence saying no mail is ever sent automatically. True of a NEWSLETTER,
     * false of the new-event digest, so a sentence that names the newsletter is
     * exempt - and so is one with no audience in it at all, because the same words
     * describe unrelated things (the selfhost translation-sharing toggle says
     * "nothing is ever sent automatically" about suggestion submissions).
     */
    private const NEGATION = '/\b(?:nothing|no)\b(?:(?!newsletter)[^.])'
        .'{0,90}?\b(?:emails?|emailed|e-mails?|sent|send|goes\s+out|go\s+out)\b'
        .'(?:(?!newsletter)[^.]){0,70}?\b(?:automatic(?:ally)?|on\s+its\s+own|by\s+itself|of\s+its\s+own\s+accord)\b/i';

    /** The claim only matters when it is about who hears from a schedule. */
    private const AUDIENCE = '/\b(?:followers?|subscribers?|fans?|attendees?|guests?|audience|your\s+list|mailing\s+list)\b/i';

    /**
     * "Nothing is sent to an ACCOUNT FOLLOWER automatically" is true, so a page may
     * say it - but only if it says ACCOUNT follower, and only if the page also
     * explains the list that does hear on its own.
     *
     * The bare plural does not qualify on purpose. The app calls the signed-in
     * list "Followers" and the email list "Subscribers", so "nothing is emailed to
     * your followers automatically" is technically true and reliably read as the
     * flat negation - which is the sentence this whole test exists to keep off the
     * site. Naming the list is the cost of making the claim.
     */
    private const SCOPED_TO_FOLLOWERS = '/\baccount\s+followers?\b/i';

    /** @return array<string, string> */
    private function marketingSources(): array
    {
        $paths = array_merge(
            File::glob(resource_path('views/marketing/*.blade.php')),
            File::glob(resource_path('views/marketing/*/*.blade.php')),
            File::glob(resource_path('views/marketing/*/*/*.blade.php')),
            [
                lang_path('en/marketing.php'),
                app_path('Http/Controllers/MarketingController.php'),
            ]
        );

        $out = [];
        foreach ($paths as $p) {
            if (! is_file($p)) {
                continue;
            }
            $body = file_get_contents($p);
            // Comments quote the wrong claim on purpose. The line-comment arm must
            // NOT carry /s, or `.*$` runs to the end of the file.
            $body = preg_replace('~\{\{--.*?--\}\}|/\*.*?\*/~s', ' ', $body);
            $body = preg_replace('~^[ \t]*//.*$~m', ' ', $body);
            $out[str_replace(base_path().'/', '', $p)] = $body;
        }

        return $out;
    }

    public function test_no_marketing_surface_says_nothing_is_ever_mailed_automatically(): void
    {
        $offences = [];

        foreach ($this->marketingSources() as $path => $body) {
            $explainsTheDigest = (bool) preg_match('/\bdigest\b/i', $body);

            if (! preg_match_all(self::NEGATION, $body, $m)) {
                continue;
            }

            foreach ($m[0] as $hit) {
                if (! preg_match(self::AUDIENCE, $hit)) {
                    continue;  // not about who hears from a schedule
                }
                if (preg_match(self::SCOPED_TO_FOLLOWERS, $hit) && $explainsTheDigest) {
                    continue;  // true, and the page says what the other list gets
                }
                $offences[] = $path.': "'.trim(preg_replace('/\s+/', ' ', $hit)).'"';
            }
        }

        $this->assertSame([], $offences, implode("\n", array_merge(
            ['app:send-event-announcements mails a digest of newly published events to confirmed '
                .'subscribers on its own. Say that a NEWSLETTER never sends itself, which is true, '
                .'rather than that nothing does.'],
            $offences
        )));
    }

    public function test_the_digest_floor_is_stated_in_the_unit_the_command_uses(): void
    {
        $hours = (int) config('usage.audience_announcement_min_hours');
        $this->assertGreaterThan(0, $hours, 'the announcement floor has no configured value');

        $wrong = [];
        $seen = 0;

        foreach ($this->marketingSources() as $path => $body) {
            // "at most one every three days" / "one digest every 72 hours".
            preg_match_all('/\b(?:one|once)\s+(?:digest\s+)?every\s+(\d{1,3}|one|two|three|four|five|six|seven)\s+(hours?|days?)\b/i', $body, $m, PREG_SET_ORDER);
            foreach ($m as $hit) {
                $seen++;
                $words = ['one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7];
                $value = $words[strtolower($hit[1])] ?? (int) $hit[1];
                $asHours = str_starts_with(strtolower($hit[2]), 'day') ? $value * 24 : $value;
                if ($asHours !== $hours) {
                    $wrong[] = "{$path}: says {$hit[1]} {$hit[2]} ({$asHours}h)";
                }
            }
        }

        $this->assertSame([], $wrong,
            "usage.audience_announcement_min_hours is {$hours}; these say otherwise:\n".implode("\n", $wrong));
        $this->assertGreaterThan(0, $seen, 'nothing states the digest floor any more, so this pins nothing');
    }
}
