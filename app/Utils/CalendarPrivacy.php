<?php

namespace App\Utils;

use Illuminate\Support\HtmlString;

/**
 * What in a calendar entry belongs to the person whose calendar it is, and not on a public page.
 *
 * A standing calendar sync once published somebody's whole work calendar: Teams dial-in numbers
 * with their access codes, Zoom links carrying the meeting's password, and an email thread about
 * a child's doctor that the calendar had made into an entry. Every import that reads a calendar
 * asks this class the same questions, and so does the operator's page that looks for it after
 * the fact (CalendarExposureWatch), so the two can never disagree about what was found.
 *
 * It finds the shape of a credential or a thread, never a topic: a list of medical words would
 * flag every blood drive and pediatric first-aid class, and the entry that started this was an
 * email thread, which is found by its form. A bare meeting link is noted and never flagged, since
 * a public webinar has one. No I/O here.
 */
class CalendarPrivacy
{
    /** A join link that carries the meeting's password in its address. */
    public const LINK_PASSWORD = 'link_password';

    /** A meeting ID, a passcode, or a dial-in number with its PIN. */
    public const ACCESS_CODES = 'access_codes';

    /** An email (a reply, a forward, a pasted thread) that became a calendar entry. */
    public const EMAIL_THREAD = 'email_thread';

    /** A join link with nothing secret in it. Context, never a flag. */
    public const MEETING_LINK = 'meeting_link';

    /** The kinds that mean private, in the order the pages list them. */
    public const FLAGS = [self::LINK_PASSWORD, self::ACCESS_CODES, self::EMAIL_THREAD];

    /** Why an import holds an entry back, as calendar_import_holds.reason stores it. */
    public const HOLD_MEETING_DETAILS = 'meeting_details';

    public const HOLD_EMAIL_THREAD = 'email_thread';

    /** At most this many matches are kept per kind: they pick what is shown, not what is found. */
    private const KEEP = 3;

    /**
     * The hosts and paths a meeting is joined at. A provider's own name on its own ("Zoom") is a
     * different question, asked by isProviderName().
     */
    private const JOIN_PATH = '(?:[a-z0-9-]+\.)*(?:'
        .'zoom(?:gov)?\.(?:us|com)/(?:j|w|s|my|wc/join|webinar/register)/'
        .'|teams\.microsoft\.com/(?:l/meetup-join|meet)/'
        .'|teams\.live\.com/meet/'
        .'|meet\.google\.com/[a-z]{3}-[a-z]{4}-[a-z]{3}'
        .'|tel\.meet/[a-z]{3}-[a-z]{4}-[a-z]{3}'
        .'|webex\.com/(?:meet|join|[a-z0-9-]+/j\.php|[a-z0-9-]+/e\.php)'
        .'|(?:global\.gotomeeting\.com/join|meet\.goto\.com)/'
        .'|bluejeans\.com/\d'
        .'|chime\.aws/\d'
        .')';

    /** One address, as far as it goes: up to whitespace or the brackets Markdown puts round it. */
    private const LINK = '~(?:\bhttps?://|\bwww\.|\b(?=[a-z0-9-]+(?:\.[a-z0-9-]+)*\.[a-z]{2,}/))[^\s<>()\[\]"\']+~iu';

    /** The same test PlaceLookupService used: a scheme, a www., or a host followed by a path. */
    private const HAS_LINK = '~(?:[a-z][a-z0-9+.-]*://|\bwww\.|\b[a-z0-9-]+(?:\.[a-z0-9-]+)*\.[a-z]{2,}/\S)~iu';

    /** Query keys that carry a meeting's password. tk, MTID and p (Teams' short links) only count on a meeting host. */
    private const PASSWORD_KEY = '~[?&;](?:pwd|passcode|password|meetingpassword)=~iu';

    private const MEETING_PASSWORD_KEY = '~[?&;](?:tk|mtid|p)=~iu';

    /**
     * Labels a meeting's number is printed under, in the wording Zoom, Teams, Meet and Webex use
     * in the languages the app speaks. Followed by digits.
     */
    private const ID_LABELS = 'meeting\s*id|meeting\s*number|webinar\s*id|conference\s*id|phone\s*conference\s*id'
        .'|participant\s*(?:id|code)|attendee\s*(?:id|code)|access\s*code|dial-?in\s*code'
        .'|besprechungs-?id|meeting-?id|konferenz-?id|telefonkonferenz-?id|zugriffscode'
        .'|id\.?\s*de\s*(?:la\s*)?r[ée]union|id\.?\s*de\s*conf[ée]rence(?:\s*t[ée]l[ée]phonique)?|code\s*d.acc[eè]s'
        .'|id\.?\s*de\s*(?:la\s*)?reuni[oó]n|id\.?\s*de\s*conferencia(?:\s*de\s*tel[eé]fono)?|c[oó]digo\s*de\s*acceso'
        .'|id\s*(?:della\s*)?riunione|id\s*conferenza|codice\s*(?:di\s*)?accesso'
        .'|vergadering-?id|conferentie-?id|toegangscode'
        .'|id\s*da\s*reuni[aã]o|id\s*de\s*confer[eê]ncia|c[oó]digo\s*de\s*acesso'
        .'|id\s*(?:ul\s*)?(?:întâlnirii|intalnirii|ședinței|sedintei)|cod\s*de\s*acces'
        .'|koosoleku\s*id|konverentsi\s*id|pääsukood'
        .'|идентификатор\s*(?:конференции|собрания|встречи)|код\s*доступа'
        .'|מזהה\s*(?:פגישה|ישיבה|ועידה)|קוד\s*גישה'
        .'|معرف\s*(?:الاجتماع|المؤتمر)|رمز\s*الوصول';

    /** Labels a meeting's password is printed under. Followed by a colon and a value. */
    private const CODE_LABELS = 'passcode|pass\s*code|password|meeting\s*password|pin'
        .'|kenncode|kennwort|passwort|code\s*secret|mot\s*de\s*passe'
        .'|contrase[ñn]a|clave|passcode|codice\s*segreto|wachtwoord|senha|parol[aă]|cod\s*secret'
        .'|parool|pääsukood|пароль|секретный\s*код|код\s*доступа|סיסמה|סיסמא|קוד\s*סודי'
        .'|كلمة\s*(?:المرور|السر)|رمز\s*المرور';

    /** A provider's own name standing in for a place ("Microsoft Teams Meeting"). */
    private const PROVIDER_NAME = '~^\s*(?:(?:microsoft\s+)?teams(?:\s+(?:meeting|call|besprechung|réunion|reunión|riunione|vergadering|reunião|întâlnire|koosolek|собрание|ישיבה|פגישה|اجتماع))?'
        .'|zoom(?:\s+(?:meeting|call|webinar))?'
        .'|google\s+meet(?:\s+(?:meeting|call))?|meet'
        .'|(?:cisco\s+)?webex(?:\s+(?:meeting|call))?'
        .'|skype(?:\s+(?:meeting|call|for\s+business))?'
        .'|go\s*to\s*meeting|bluejeans|(?:amazon\s+)?chime'
        .')\s*$~iu';

    /**
     * Findings in an entry, by kind: each one names the field it was in and the text that matched.
     *
     * @return array<string, list<array{field: string, match: string}>>
     */
    public static function findings(?string $name, ?string $description, ?string $location): array
    {
        $fields = array_filter([
            'name' => self::normalise((string) $name),
            'description' => self::normalise((string) $description),
            'location' => self::normalise((string) $location),
        ], fn (string $text) => $text !== '');

        $found = [];
        $add = function (string $kind, string $field, string $match) use (&$found) {
            $match = trim($match);
            if ($match === '' || count($found[$kind] ?? []) >= self::KEEP) {
                return;
            }
            $found[$kind][] = ['field' => $field, 'match' => $match];
        };

        foreach ($fields as $field => $text) {
            foreach (self::links($text) as $link) {
                $onMeetingHost = (bool) preg_match('~'.self::JOIN_PATH.'~iu', $link);

                if (preg_match(self::PASSWORD_KEY, $link) || ($onMeetingHost && preg_match(self::MEETING_PASSWORD_KEY, $link))) {
                    $add(self::LINK_PASSWORD, $field, $link);
                } elseif ($onMeetingHost) {
                    $add(self::MEETING_LINK, $field, $link);
                }
            }

            foreach (self::accessCodes($text) as $match) {
                $add(self::ACCESS_CODES, $field, $match);
            }
        }

        foreach (self::threadMarks($fields['name'] ?? '', $fields['description'] ?? '') as [$field, $match]) {
            $add(self::EMAIL_THREAD, $field, $match);
        }

        return $found;
    }

    /**
     * The kinds found that mean private, in FLAGS order.
     *
     * @param  array<string, mixed>  $findings
     * @return list<string>
     */
    public static function flags(array $findings): array
    {
        return array_values(array_filter(self::FLAGS, fn (string $kind) => ! empty($findings[$kind])));
    }

    /**
     * Why an import should hold this entry back from a public page, or null.
     *
     * @param  array<string, mixed>  $findings
     */
    public static function holdReason(array $findings): ?string
    {
        if (! empty($findings[self::LINK_PASSWORD]) || ! empty($findings[self::ACCESS_CODES])) {
            return self::HOLD_MEETING_DETAILS;
        }

        return ! empty($findings[self::EMAIL_THREAD]) ? self::HOLD_EMAIL_THREAD : null;
    }

    /**
     * What an owner's decision about an entry is tied to: the same entry with other words in it
     * is a new question ("publish it anyway" was said about the text as it stood).
     */
    public static function textHash(?string $name, ?string $description, ?string $location): string
    {
        return sha1(self::normalise((string) $name)."\x1f".self::normalise((string) $description)."\x1f".self::normalise((string) $location));
    }

    /**
     * The place a calendar location names, or null when it names none.
     *
     * A location with a password or a code in it is no place at all: the venue it used to become
     * was a public page named after the link. Otherwise the links are taken out and so is a
     * provider's name standing alone ("Microsoft Teams Meeting; Room 4" keeps "Room 4"), and
     * what is left is the place.
     */
    public static function venueFromLocation(?string $location): ?string
    {
        $location = self::normalise((string) $location);
        if ($location === '') {
            return null;
        }

        $findings = self::findings(null, null, $location);
        if (! empty($findings[self::LINK_PASSWORD]) || ! empty($findings[self::ACCESS_CODES])) {
            return null;
        }

        // With the links gone, a bracket that held one is empty: "12 Main St (https://...)".
        $withoutLinks = preg_replace('~\(\s*\)|\[\s*\]~u', '', preg_replace(self::LINK, ' ', $location));
        $parts = preg_split('~\s*(?:[;|\n]|\s/\s)\s*~u', $withoutLinks) ?: [];

        $kept = [];
        foreach ($parts as $part) {
            $part = preg_replace('~^[\s,:;–-]+|[\s,:;–-]+$~u', '', preg_replace('~\s+~u', ' ', $part));
            if ($part === '' || self::isProviderName($part) || ! preg_match('~[\p{L}\p{N}]~u', $part)) {
                continue;
            }
            $kept[] = $part;
        }

        return $kept ? implode('; ', $kept) : null;
    }

    /** A provider's name with nothing else ("Zoom", "Microsoft Teams Meeting"). */
    public static function isProviderName(string $text): bool
    {
        return (bool) preg_match(self::PROVIDER_NAME, $text);
    }

    /** An address anywhere in the text, with or without a scheme. */
    public static function hasLink(string $text): bool
    {
        return (bool) preg_match(self::HAS_LINK, $text);
    }

    /**
     * The text, escaped, cut to $max around the match, with the match marked.
     */
    public static function highlight(string $text, ?string $match, int $max = 120): HtmlString
    {
        $text = self::normalise($text);
        $at = $match !== null && $match !== '' ? mb_stripos($text, $match) : false;
        $length = $at === false ? 0 : min(mb_strlen($match), $max);

        if (mb_strlen($text) > $max) {
            $start = $at === false ? 0 : max(0, min($at - intdiv(max(0, $max - $length), 2), mb_strlen($text) - $max));
            $cut = mb_substr($text, $start, $max);
            $more = $start + $max < mb_strlen($text);
            $text = ($start > 0 ? '…' : '').$cut.($more ? '…' : '');

            if ($at !== false) {
                $at = $at - $start + ($start > 0 ? 1 : 0);
                $length = min($length, mb_strlen($cut) - ($at - ($start > 0 ? 1 : 0)));
            }
        }

        if ($at === false || $length <= 0) {
            return new HtmlString(e($text));
        }

        return new HtmlString(
            e(mb_substr($text, 0, $at))
            // A code or a link reads left to right in a right-to-left page; the words around it
            // keep their own direction.
            .'<mark class="spam-word" dir="ltr">'.e(mb_substr($text, $at, $length)).'</mark>'
            .e(mb_substr($text, $at + $length))
        );
    }

    /**
     * Text as a person reads it: entities decoded, odd spaces made plain, and the escapes the
     * HTML-to-Markdown conversion adds (\# \_ \[) taken back out, since a sync stores Markdown.
     */
    public static function normalise(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('~[\x{00A0}\x{2007}\x{202F}]~u', ' ', $text) ?? $text;
        $text = preg_replace('~[\x{200B}-\x{200D}\x{2060}\x{FEFF}]~u', '', $text) ?? $text;
        $text = preg_replace('/\\\\([\\\\`*_{}\[\]()#+\-.!|>~])/u', '$1', $text) ?? $text;
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return trim($text);
    }

    /** @return list<string> */
    private static function links(string $text): array
    {
        if (! preg_match_all(self::LINK, $text, $m)) {
            return [];
        }

        return array_values(array_unique(array_map(fn (string $link) => rtrim($link, '.,;:!?*_'), $m[0])));
    }

    /** @return list<string> */
    private static function accessCodes(string $text): array
    {
        $patterns = [
            // A number printed under its label: "Meeting ID: 812 3456 7890", "Conference ID 541 485 493#".
            '~\b(?:'.self::ID_LABELS.')\b(?:\s*\([^)\n]{0,40}\))?\s*[:：#]?\s*\d[\d \-]{4,}\d\s*#?~iu',
            // A password after its label and a colon: "Passcode: AbC12d", "PIN: 123 456 789#".
            // The colon is the difference between "PIN: 4417" and "pin your seat".
            '~\b(?:'.self::CODE_LABELS.')\b\s*[:：=]\s*[^\s,;]{4,}~iu',
            // A code a phone asks for, which ends in #: "Code: 583920174#".
            '~\b(?:code|pin|cod|código|codice|kód|код|קוד|رمز)\b\s*[:：#]?\s*\d[\d \-]{4,}\d\s*#~iu',
            // One tap: a number, two commas or more, the meeting's number. "+15550100199,,583920174#".
            '~(?:\+|\b00|tel:\+?)\d[\d \-().]{6,}\d\s*,,+\s*\d{4,}[\d#*,]*~u',
        ];

        $found = [];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $m)) {
                array_push($found, ...$m[0]);
            }
        }

        return array_values(array_unique(array_map('trim', $found)));
    }

    /**
     * Marks of an email: a reply or forward prefix on the title, an [EXTERNAL] tag a mail server
     * adds, or a header block (From, Sent, To, Subject) or a quoted reply line in the description.
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function threadMarks(string $name, string $description): array
    {
        $marks = [];

        // The prefix needs its colon AND a space after it, so a festival called "Re:Union" passes.
        if (preg_match('~^\s*(?:(?:re|fwd?|aw|wg|tr|sv|vs|rv|enc|antw|odp)\s*(?:\[\d+\])?\s*[:：]\s+)+~iu', $name, $m)) {
            $marks[] = ['name', $m[0]];
        }

        $external = '~\[\s*(?:external|ext|extern|externe|externo|externa|esterno|extern[ăa])\s*\]~iu';
        foreach (['name' => $name, 'description' => $description] as $field => $text) {
            if ($text !== '' && preg_match($external, $text, $m)) {
                $marks[] = [$field, $m[0]];
            }
        }

        if ($description === '') {
            return $marks;
        }

        if (preg_match('~-{3,}\s*(?:original message|ursprüngliche nachricht|message d.origine|mensaje original|oorspronkelijk bericht|messaggio originale|mensagem original|mesaj original|исходное сообщение|הודעה מקורית|الرسالة الأصلية)\s*-{3,}~iu', $description, $m)) {
            $marks[] = ['description', $m[0]];
        }

        if (preg_match('~^\s*(?:on|am|le|el|op|il|em|în)\s.{5,120}\s(?:wrote|schrieb|a écrit|escribió|schreef|ha scritto|escreveu|a scris)\s*:?\s*$~imu', $description, $m)) {
            $marks[] = ['description', $m[0]];
        }

        // A header block: a Subject, a From and a To or Sent line within ten lines of each other.
        // The Subject is required, and Date is not a Sent: "Date: Saturday / From: 7pm / To: 11pm"
        // is an event's own description.
        $canonical = [
            'from' => 'from|von|de|da|van|от|מאת|من|kellelt',
            'sent' => 'sent|gesendet|envoyé|enviado|inviato|verzonden|trimis|отправлено|נשלח|تاريخ الإرسال|saadetud',
            'to' => 'to|an|à|para|a|aan|către|кому|אל|إلى|kellele',
            'subject' => 'subject|betreff|objet|asunto|oggetto|onderwerp|assunto|subiect|тема|נושא|الموضوع|teema',
        ];
        $lines = explode("\n", $description);
        $seen = [];
        foreach ($lines as $i => $line) {
            foreach ($canonical as $key => $labels) {
                if (preg_match('~^\s*[>*_\s]*(?:'.$labels.')\s*[:：]\s*[*_]*\s*\S~iu', $line)) {
                    $seen[$key] = $i;
                }
            }

            $recent = array_filter($seen, fn (int $at) => $i - $at < 10);
            if (isset($recent['subject'], $recent['from']) && (isset($recent['to']) || isset($recent['sent']))) {
                $marks[] = ['description', trim($lines[min($recent)])];
                break;
            }
        }

        return $marks;
    }
}
