<?php

namespace Tests\Feature;

use App\Utils\RealtimeTracker;
use App\Utils\SignupSource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Where an account came from, read off what its sign-up stored: one classifier for the admin
 * dashboard's breakdown, its list of latest sign-ups and /admin/realtime.
 *
 * The rule most worth holding is the last one. A first visit to an edge-cached marketing page is
 * stored only with marketing consent, so a visitor who declined arrives with nothing but the
 * sign-up page. That is "Not recorded". Call it "Direct" and the Direct channel grows with every
 * declined cookie banner, which is a number that looks like a finding and is not one.
 */
class SignupSourceTest extends TestCase
{
    /**
     * utm source, medium, campaign, referrer, landing page, referred => channel, name
     */
    public static function firstTouches(): array
    {
        return [
            'a referral link beats everything stored beside it' => ['chatgpt.com', null, null, 'https://www.google.com/', 'pricing', true, 'referral', null],
            'a tagged link from an AI assistant' => ['chatgpt.com', null, null, null, 'features/embed-calendar', false, 'ai', 'chatgpt.com'],
            'tags beat the referrer' => ['newsletter', 'email', 'october', 'https://www.google.com/', 'pricing', false, 'email', 'newsletter'],
            'a boost click is paid' => ['boost', 'network', null, null, '/', false, 'paid', 'boost'],
            'a paid medium is paid' => ['meta', 'cpc', null, null, '/', false, 'paid', 'meta'],
            'an unknown tag is a campaign' => ['partner-site', null, 'spring', null, '/', false, 'campaign', 'partner-site'],
            'search, with www stripped' => [null, null, null, 'https://www.google.com/search?q=x', 'luma-alternative', false, 'search', 'google.com'],
            'social' => [null, null, null, 'https://l.facebook.com/l.php', '/', false, 'social', 'l.facebook.com'],
            'a site nobody has a word for' => [null, null, null, 'https://blog.example.org/post', 'pricing', false, 'other', 'blog.example.org'],
            'a referrer with no scheme is still a host' => [null, null, null, 'bing.com/search', 'pricing', false, 'search', 'bing.com'],
            'a real landing page and nothing else' => [null, null, null, null, 'pricing', false, 'direct', null],
            'the home page counts as a landing page' => [null, null, null, null, '/', false, 'direct', null],
        ];
    }

    #[DataProvider('firstTouches')]
    public function test_the_order_the_evidence_is_read_in(?string $source, ?string $medium, ?string $campaign, ?string $referrer, ?string $landing, bool $referred, string $channel, ?string $name): void
    {
        $result = SignupSource::classify($source, $medium, $campaign, $referrer, $landing, $referred);

        $this->assertSame($channel, $result['channel']);
        $this->assertSame($name, $result['name']);
    }

    /** Mutation: make the fallback 'direct' and every one of these turns. */
    public function test_nothing_stored_is_not_recorded_and_never_direct(): void
    {
        $own = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        $nothing = [
            'nothing at all' => [null, null],
            'empty strings' => ['', ''],
            'only the sign-up page' => [null, 'sign_up'],
            'only the sign-in page, with a query string' => [null, 'login?redirect=1'],
            // What a Google sign-up arrives from: the provider handing the visitor back.
            'an OAuth return' => ['https://accounts.google.com/o/oauth2/auth', 'sign_up'],
            'one of our own pages' => ['https://'.$own.'/pricing', 'sign_up'],
        ];

        foreach ($nothing as $case => [$referrer, $landing]) {
            $this->assertSame('unrecorded', SignupSource::classify(null, null, null, $referrer, $landing, false)['channel'], $case);
        }

        // And the list shows nothing for such a row, where it used to print "Direct".
        $this->assertNull(SignupSource::display((object) ['landing_page' => 'sign_up']));
    }

    /**
     * A stored tag lands in the channel a live visit with the same tag would: the two pages read
     * one table. The mediums and hosts are the tracker's own lists, read off the class, so one
     * added there is covered here the day it is added. Mutation: give SignupSource a copy of any
     * of the four lists and leave an entry out.
     */
    public function test_tags_are_read_as_the_live_tracker_reads_them(): void
    {
        $lists = new \ReflectionClass(RealtimeTracker::class);
        $host = fn (string $pattern) => str_replace('.*', '.com', $pattern);

        $tags = [
            ['source' => 'newsletter', 'medium' => null, 'channel' => 'email'],
            ['source' => 'boost', 'medium' => 'network', 'channel' => 'paid'],
            ['source' => 'a-partner', 'medium' => null, 'channel' => 'campaign'],
            ['source' => 'facebook.com', 'medium' => 'social', 'channel' => 'social'],
        ];
        foreach ($lists->getConstant('PAID_MEDIUMS') as $medium) {
            $tags[] = ['source' => 'anything', 'medium' => $medium, 'channel' => 'paid'];
        }
        foreach ($lists->getConstant('EMAIL_MEDIUMS') as $medium) {
            $tags[] = ['source' => 'anything', 'medium' => $medium, 'channel' => 'email'];
        }
        foreach ($lists->getConstant('AI_HOSTS') as $pattern) {
            $tags[] = ['source' => $host($pattern), 'medium' => null, 'channel' => 'ai'];
        }
        foreach ($lists->getConstant('SOCIAL_HOSTS') as $pattern) {
            $tags[] = ['source' => $host($pattern), 'medium' => null, 'channel' => 'social'];
        }

        $this->assertGreaterThan(30, count($tags), 'the lists moved or emptied, so this test proves nothing');

        foreach ($tags as $utm) {
            $live = RealtimeTracker::classifySource(null, $utm, 'navigate', 'wp', Request::create('/'));
            $stored = SignupSource::classify($utm['source'], $utm['medium'], null, null, '/', false);

            $this->assertSame($utm['channel'], $stored['channel'], json_encode($utm));
            $this->assertSame($live['channel'], $stored['channel'], json_encode($utm));
            $this->assertSame($live['name'], $stored['name'], json_encode($utm));
        }
    }

    public function test_a_landing_page_is_shown_as_a_path(): void
    {
        $this->assertSame('/pricing', SignupSource::landingPath('pricing'));
        $this->assertSame('/pricing', SignupSource::landingPath('https://eventschedule.com/pricing?utm_source=x#top'));
        $this->assertSame('/features/embed-tickets', SignupSource::landingPath('/features/embed-tickets/'));
        $this->assertSame('/', SignupSource::landingPath('/'));
        $this->assertNull(SignupSource::landingPath(null));
        $this->assertNull(SignupSource::landingPath(''));
        $this->assertNull(SignupSource::landingPath('sign_up'));
        $this->assertNull(SignupSource::landingPath('/LOGIN'));
    }

    public function test_the_list_names_the_source_and_the_page(): void
    {
        $row = (object) ['utm_source' => 'chatgpt.com', 'utm_medium' => 'referral', 'landing_page' => 'features/embed-calendar'];
        $this->assertSame(
            ['primary' => 'chatgpt.com / referral', 'secondary' => '/features/embed-calendar', 'channel' => 'ai'],
            SignupSource::display($row)
        );

        $referred = (object) ['referred_by_user_id' => 7, 'landing_page' => '/'];
        $this->assertSame(__('messages.realtime_referred_by', ['name' => 'Li Wei']), SignupSource::display($referred, 'Li Wei')['primary']);
        // The home page is where a visit with no other story starts: not worth a second line.
        $this->assertNull(SignupSource::display($referred, 'Li Wei')['secondary']);
    }

    /** Mutation: delete channel_ai from one language file. */
    public function test_every_channel_has_a_label_in_every_language(): void
    {
        // A key missing from one language falls back to English, which would read here as a
        // label. With no language to fall back to, it comes back as the key.
        app('translator')->setFallback('zz');

        foreach (array_keys(config('app.supported_languages')) as $language) {
            app()->setLocale($language);

            foreach (SignupSource::CHANNELS as $channel) {
                $label = SignupSource::label($channel);

                $this->assertNotSame('', trim($label), "$language: $channel");
                $this->assertStringNotContainsString('messages.', $label, "$language: $channel has no translation");
            }
        }
    }
}
