<?php

namespace Tests\Feature;

use App\Http\Controllers\AppController;
use App\Http\Controllers\Traits\SanitizesNewsletterContent;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\Newsletter;
use App\Models\Role;
use App\Services\NewsletterService;
use App\Utils\ColorUtils;
use App\Utils\EmailTheme;
use App\Utils\NewsletterTheme;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The newsletter is the one outgoing mail whose colours are the owner's, so it is built on its own
 * system (App\Utils\NewsletterTheme, the emails.newsletter views, the x-newsletter components) and
 * not on the x-email layout. This file holds what that system promises.
 *
 * What a reader is sent: only events the schedule accepted, a recurring series under its NEXT
 * date, dates in the schedule's language. Until 2026-10 the events came from `$role->events()`
 * with `starts_at >= now`: a booking request nobody had answered went out to the schedule's
 * audience as one of its events, and a weekly night dropped out the day after it began.
 *
 * How it is built: every colour derived from the owner's three and readable (no block appends an
 * alpha byte to a hex, which Outlook drops), no stripe down one side of anything, direction on
 * every layout table (Gmail strips it from the html tag), nothing in a comment that is not an
 * Outlook conditional (a comment is sent to the reader).
 */
class NewsletterDesignTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const DESIGNS = ['modern', 'classic', 'minimal', 'bold', 'compact'];

    private const TZ = 'America/New_York';

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        $this->travelTo(Carbon::parse('2026-10-08 15:00:00', 'UTC'));
    }

    private function venue(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + [
            'name' => 'Harbour Hall',
            'timezone' => self::TZ,
            'language_code' => 'en',
            'profile_image_url' => 'demo_profile_jazz.jpg',
            'website' => 'https://harbourhall.example.com',
            'sponsor_logos' => json_encode([
                ['logo' => 'demo_profile_beer.jpg', 'name' => 'Tidewater Brewing', 'url' => 'https://tidewater.example.com', 'tier' => 'gold'],
            ]),
        ]);
    }

    /** An event of $role's own, $days from now at $hour on the schedule's clock. */
    private function event(Role $role, string $name, int $days, int $hour, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'creator_role_id' => $role->id,
            'name' => $name,
            'starts_at' => Carbon::now(self::TZ)->addDays($days)->setTime($hour, 0)->utc()->format('Y-m-d H:i:s'),
            'duration' => 2,
        ]);
    }

    private function newsletter(?Role $role, array $blocks, string $design = 'modern', array $style = []): Newsletter
    {
        $newsletter = new Newsletter([
            'role_id' => $role?->id,
            'subject' => "What's on & where",
            'blocks' => $blocks,
            'template' => $design,
            'style_settings' => array_merge(Newsletter::templateDefaults($design), $style),
        ]);
        $newsletter->setRelation('role', $role);

        return $newsletter;
    }

    private function render(?Role $role, array $blocks, string $design = 'modern', array $style = []): string
    {
        return app(NewsletterService::class)->renderHtml($this->newsletter($role, $blocks, $design, $style));
    }

    private function eventsBlock(array $ids = [], string $layout = 'cards'): array
    {
        return ['id' => 'events', 'type' => 'events', 'data' => ['layout' => $layout, 'useAllEvents' => $ids === [], 'eventIds' => $ids]];
    }

    private function everyBlock(Role $role): array
    {
        return [
            ['id' => 'logo', 'type' => 'profile_image', 'data' => []],
            ['id' => 'h1', 'type' => 'heading', 'data' => ['text' => 'The autumn season', 'level' => 'h1', 'align' => 'center']],
            ['id' => 'text', 'type' => 'text', 'data' => ['content' => "Hello from **the harbour**.\n\n## What is new\n\n- A second bar\n- [Gift cards](https://harbourhall.example.com/gift)\n\n> Best room in the city."]],
            ['id' => 'h2', 'type' => 'heading', 'data' => ['text' => 'Coming up', 'level' => 'h2', 'align' => 'left']],
            $this->eventsBlock(),
            ['id' => 'divider', 'type' => 'divider', 'data' => ['style' => 'solid']],
            ['id' => 'h3', 'type' => 'heading', 'data' => ['text' => 'From last month', 'level' => 'h3', 'align' => 'left']],
            ['id' => 'image', 'type' => 'image', 'data' => ['layout' => 'row', 'images' => [
                ['url' => 'https://cdn.example.com/a.jpg', 'alt' => 'Friday night', 'caption' => 'Friday', 'link' => ''],
                ['url' => 'https://cdn.example.com/b.jpg', 'alt' => '', 'caption' => 'Saturday', 'link' => 'https://harbourhall.example.com/gallery'],
            ]]],
            ['id' => 'quote', 'type' => 'quote', 'data' => ['text' => 'The one we asked to come back to.', 'author' => 'Sara Vance', 'title' => 'The Midnight Owls']],
            ['id' => 'offer', 'type' => 'offer', 'data' => ['title' => 'Two for one', 'description' => 'On Thursdays.', 'originalPrice' => '$24', 'salePrice' => '$12', 'couponCode' => 'JAZZ2FOR1', 'buttonText' => 'Book a Thursday', 'buttonUrl' => 'https://harbourhall.example.com/jazz']],
            ['id' => 'video', 'type' => 'video', 'data' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
            ['id' => 'poll', 'type' => 'poll', 'data' => []],
            ['id' => 'button', 'type' => 'button', 'data' => ['text' => 'See the full calendar', 'url' => 'https://harbourhall.example.com/calendar', 'align' => 'center']],
            ['id' => 'spacer', 'type' => 'spacer', 'data' => ['height' => 20]],
            ['id' => 'sponsors', 'type' => 'sponsors', 'data' => ['source' => 'schedule']],
            ['id' => 'social', 'type' => 'social_links', 'data' => ['links' => Newsletter::buildSocialLinksForRole($role)]],
        ];
    }

    public function test_every_design_renders_every_block_and_keeps_the_rules_of_the_build(): void
    {
        $role = $this->venue();
        $show = $this->event($role, 'The Midnight Owls', 3, 20, ['flyer_image_url' => 'demo_flyer_rock.jpg', 'image_variants' => ['src' => ['w' => 1600, 'h' => 900]], 'tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $this->createTicket($show, ['price' => 25, 'quantity' => 100]);
        $this->event($role, 'Vinyl Market', 6, 11);
        EventPoll::create(['event_id' => $show->id, 'question' => 'Which night next?', 'options' => ['Tuesdays', 'Wednesdays'], 'is_active' => true, 'sort_order' => 0]);

        foreach (self::DESIGNS as $design) {
            foreach (['cards', 'list'] as $layout) {
                $blocks = array_map(fn ($b) => $b['type'] === 'events' ? $this->eventsBlock([], $layout) : $b, $this->everyBlock($role));
                $html = $this->render($role, $blocks, $design);
                $where = "{$design}, {$layout}";

                foreach (['The autumn season', 'Coming up', 'From last month', 'The Midnight Owls', 'Vinyl Market', 'The one we asked to come back to.', 'JAZZ2FOR1', 'Which night next?', 'Tuesdays', 'Tidewater Brewing', 'See the full calendar', 'Best room in the city.'] as $text) {
                    $this->assertStringContainsString($text, $html, "{$where}: {$text}");
                }

                // What an event entry says: its day and time on the venue's clock, and its price.
                $this->assertStringContainsString('8:00 PM', $html, $where);
                $this->assertStringContainsString('$25', $html, $where);
                $this->assertStringContainsString(__('messages.get_tickets'), $html, $where);

                // A venue's own name under each of its own events says nothing, and its own logo
                // is the masthead, never the picture of an event that has no flyer.
                $this->assertSame(1, substr_count($html, 'demo_profile_jazz.jpg'), "{$where}: the schedule's logo is shown once");

                $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{8}\b/', $html, "{$where}: a colour with an alpha byte, which Outlook drops");
                $this->assertDoesNotMatchRegularExpression('/border-(left|right):\s*[3-9]px/', $html, "{$where}: a stripe down one side");
                // The only comments are Outlook's conditionals: "<!--[if mso]>", "<!--[if !mso]><!-->", "<!--<![endif]-->".
                $this->assertDoesNotMatchRegularExpression('/<!--(?!\[if !?mso\]|<!\[endif\]|>)/', $html, "{$where}: a comment that would be sent to the reader");
                $this->assertDoesNotMatchRegularExpression('/messages\.[a-z_]+/', $html, "{$where}: a string with no translation");
                $this->assertStringNotContainsString('<table>', $html, $where);
                $this->assertSame(substr_count($html, '<table'), substr_count($html, 'role="presentation"'), "{$where}: every table is layout");

                // A link inside owner-written text takes the page's own colour, not the browser's blue.
                $this->assertMatchesRegularExpression('/<a href="https:\/\/harbourhall\.example\.com\/gift"[^>]*style="color: #[0-9a-f]{6}; text-decoration: underline;"/', $html, $where);
            }
        }
    }

    /** A block saved before a setting existed has no key for it, and must still render. */
    public function test_a_block_with_no_settings_of_its_own_renders_in_every_design(): void
    {
        $role = $this->venue();
        $this->event($role, 'A Show', 3, 20);

        foreach (self::DESIGNS as $design) {
            $html = $this->render($role, [
                ['id' => 'h', 'type' => 'heading', 'data' => ['text' => 'A bare heading']],
                ['id' => 'b', 'type' => 'button', 'data' => ['text' => 'A bare button']],
                ['id' => 'e', 'type' => 'events', 'data' => []],
                ['id' => 'd', 'type' => 'divider', 'data' => []],
                ['id' => 'i', 'type' => 'image', 'data' => ['url' => 'https://cdn.example.com/old.jpg', 'alt' => 'An old image block']],
                ['id' => 'o', 'type' => 'offer', 'data' => ['title' => 'A bare offer']],
                ['id' => 's', 'type' => 'spacer', 'data' => []],
                ['id' => 'x', 'type' => 'no_such_block', 'data' => []],
                ['id' => 'n'],
            ], $design);

            foreach (['A bare heading', 'A bare button', 'A Show', 'An old image block', 'A bare offer'] as $text) {
                $this->assertStringContainsString($text, $html, "{$design}: {$text}");
            }
        }
    }

    public function test_only_events_the_schedule_accepted_are_mailed(): void
    {
        $role = $this->venue();
        $this->event($role, 'Accepted Show', 3, 20);
        $this->event($role, 'Draft Show', 4, 20, ['is_draft' => true]);
        $request = $this->event($role, 'Unanswered Booking Request', 5, 20);
        $role->events()->updateExistingPivot($request->id, ['is_accepted' => null]);

        foreach (self::DESIGNS as $design) {
            $all = $this->render($role, [$this->eventsBlock()], $design);
            $this->assertStringContainsString('Accepted Show', $all, $design);
            $this->assertStringNotContainsString('Unanswered Booking Request', $all, "{$design}: a request nobody accepted is not one of the schedule's events");
            $this->assertStringNotContainsString('Draft Show', $all, $design);
        }

        // Ticked by hand in the builder, it still is not the schedule's to announce.
        $picked = $this->render($role, [$this->eventsBlock([$request->id])]);
        $this->assertStringNotContainsString('Unanswered Booking Request', $picked);
    }

    public function test_a_recurring_series_is_listed_under_its_next_date(): void
    {
        $role = $this->venue();
        // Every Thursday at ten, begun three weeks ago. 8 October 2026 is a Thursday.
        $first = Carbon::now(self::TZ)->subWeeks(3)->setTime(22, 0);
        $series = $this->createRecurringEvent($role, [
            'creator_role_id' => $role->id,
            'name' => 'Late Night Jazz',
            'starts_at' => $first->copy()->utc()->format('Y-m-d H:i:s'),
            'days_of_week' => '0000100',
        ]);

        foreach ([[], [$series->id]] as $ids) {
            // Minimal prints the date as a line of type; a card may carry it on a tile instead.
            $html = $this->render($role, [$this->eventsBlock($ids)], 'minimal');
            $how = $ids === [] ? 'all upcoming' : 'picked by hand';

            $this->assertStringContainsString('Late Night Jazz', $html, "{$how}: a series that began before today is still running");
            $this->assertStringContainsString('Thu, Oct 8', $html, "{$how}: its next date");
            $this->assertStringNotContainsString('Sep 17', $html, "{$how}: never the date it first ran on");
            $this->assertStringContainsString('/2026-10-08"', $html, "{$how}: the link opens that occurrence");
            $this->assertStringContainsString(__('messages.weekly'), $html, $how);
        }
    }

    public function test_events_picked_by_hand_are_shown_in_date_order(): void
    {
        $role = $this->venue();
        $late = $this->event($role, 'Third Show', 9, 20);
        $early = $this->event($role, 'First Show', 2, 20);
        $middle = $this->event($role, 'Second Show', 5, 20);

        // The builder stores the order they were ticked in.
        $html = $this->render($role, [$this->eventsBlock([$late->id, $early->id, $middle->id], 'list')]);

        $this->assertLessThan(strpos($html, 'Second Show'), strpos($html, 'First Show'));
        $this->assertLessThan(strpos($html, 'Third Show'), strpos($html, 'Second Show'));
    }

    public function test_dates_are_in_the_schedules_language_and_direction_is_on_every_layout_table(): void
    {
        $german = $this->venue(['language_code' => 'de', 'subdomain' => 'hafenhalle']);
        $this->event($german, 'Herbstkonzert', 9, 20);
        $html = $this->render($german, [$this->eventsBlock()]);

        $this->assertStringContainsString('Okt', $html, 'the month in German');
        $this->assertStringNotContainsString('Oct 17', $html);
        $this->assertStringContainsString(__('messages.view_event', [], 'de'), $html);

        $hebrew = $this->venue(['language_code' => 'he', 'subdomain' => 'namal']);
        $this->event($hebrew, 'Evening Show', 9, 20);
        $html = $this->render($hebrew, [
            $this->eventsBlock(),
            ['id' => 'quote', 'type' => 'quote', 'data' => ['text' => 'Quoted words', 'author' => 'Someone', 'title' => '']],
        ], 'classic');

        // Gmail strips dir from the html and body tags, so the layout tables carry it themselves.
        $this->assertGreaterThanOrEqual(4, substr_count($html, '<table role="presentation" dir="rtl"'));
        // A clock time keeps its own order inside a Hebrew line.
        $this->assertStringContainsString('<span dir="ltr">8:00 PM</span>', $html);
        // Hebrew has no italic and no capitals to space: neither is faked.
        $this->assertStringNotContainsString('font-style: italic', $html);
        $this->assertStringNotContainsString('letter-spacing: 0.', $html);
    }

    /** The palettes an owner can pick, the five presets and four meant to break them. */
    public static function palettes(): array
    {
        return [
            'modern' => ['modern', '#ffffff', '#4E81FA', '#333333'],
            'classic' => ['classic', '#faf9f6', '#8B4513', '#2c2c2c'],
            'minimal' => ['minimal', '#ffffff', '#111111', '#333333'],
            'bold' => ['bold', '#1a1a2e', '#e94560', '#eaeaea'],
            'compact' => ['compact', '#f5f5f5', '#2d6a4f', '#333333'],
            'a yellow accent' => ['modern', '#ffffff', '#FFD400', '#333333'],
            'Bold on a white page' => ['bold', '#ffffff', '#e94560', '#222222'],
            'Classic on a dark page' => ['classic', '#101418', '#7dd3fc', '#f1f1f1'],
            'text the owner cannot read' => ['modern', '#ffffff', '#f5f5f5', '#eeeeee'],
        ];
    }

    /** @dataProvider palettes */
    public function test_every_colour_a_block_paints_text_with_reads_on_its_surface(string $design, string $background, string $accent, string $text): void
    {
        $nl = NewsletterTheme::make($design, ['backgroundColor' => $background, 'accentColor' => $accent, 'textColor' => $text], false);

        $pairs = [
            'body on the sheet' => [$nl->ink2, $nl->sheet],
            'body on a panel' => [$nl->ink2, $nl->panel],
            'quiet text on the sheet' => [$nl->ink3, $nl->sheet],
            'quiet text on a panel' => [$nl->ink3, $nl->panel],
            'quiet text on the tint' => [$nl->ink3, $nl->accentTint],
            'a link on the sheet' => [$nl->accentInk, $nl->sheet],
            'a date label on the tint' => [$nl->accentInk, $nl->accentTint],
            'a date label on a panel' => [$nl->accentInk, $nl->panel],
            'the label on a button' => [$nl->onAccent, $nl->accent],
            'the footer on the ground' => [$nl->footInk, $nl->ground],
            'a white icon on its disc' => ['#ffffff', $nl->discFill],
        ];

        foreach ($pairs as $what => [$ink, $surface]) {
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $ink, $what);
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $surface, $what);
            $this->assertGreaterThanOrEqual(4.5, round(ColorUtils::getContrastRatio($ink, $surface), 2), "{$what}: {$ink} on {$surface}");
        }
    }

    public function test_a_light_accent_takes_a_dark_label_and_a_dark_one_a_white_label(): void
    {
        $this->assertSame(EmailTheme::INK, NewsletterTheme::make('modern', ['accentColor' => '#FFD400'], false)->onAccent);
        $this->assertSame('#ffffff', NewsletterTheme::make('modern', ['accentColor' => '#8B4513'], false)->onAccent);
    }

    public function test_the_inbox_preview_is_the_owners_line_or_the_first_text_as_it_reads(): void
    {
        $role = $this->venue();
        $blocks = [['id' => 't', 'type' => 'text', 'data' => ['content' => 'This month brings **The Midnight Owls** back.']]];
        $hidden = fn (string $html) => preg_match('/<div style="display: none;[^"]*">(.*?)<\/div>/s', $html, $m) ? $m[1] : null;

        $default = $hidden($this->render($role, $blocks));
        $this->assertStringStartsWith('This month brings The Midnight Owls back.', $default, 'the text as it reads, with no markdown in it');
        $this->assertStringContainsString('&#847;', $default, 'padded, so the body does not run on after it');

        $written = $hidden($this->render($role, $blocks, 'modern', ['previewText' => 'Owls, a festival & jazz']));
        $this->assertStringStartsWith('Owls, a festival &amp; jazz', $written);

        // No text at all: nothing, never the subject a second time.
        $this->assertNull($hidden($this->render($role, [['id' => 'h', 'type' => 'heading', 'data' => ['text' => 'Hello', 'level' => 'h1']]])));
    }

    public function test_the_preview_text_is_one_line_and_does_not_travel_to_another_newsletter(): void
    {
        $sanitizer = new class
        {
            use SanitizesNewsletterContent;

            public function clean(array $settings): array
            {
                return $this->sanitizeStyleSettings($settings);
            }
        };

        $clean = $sanitizer->clean(['fontFamily' => 'System', 'previewText' => "  <b>One</b>\nline ".str_repeat('x', 300)]);

        $this->assertSame('System', $clean['fontFamily']);
        $this->assertStringStartsWith('One line x', $clean['previewText']);
        $this->assertSame(150, mb_strlen($clean['previewText']));
        $this->assertSame('Arial', $sanitizer->clean(['fontFamily' => 'Comic Sans MS'])['fontFamily']);

        // A new newsletter starts from the last one's settings, and a newsletter can be saved as a
        // template: the look goes, the line written for one mail does not.
        $carried = Newsletter::designSettings(['accentColor' => '#123456', 'footerText' => 'Harbour Hall', 'previewText' => 'Last month']);
        $this->assertSame(['accentColor' => '#123456', 'footerText' => 'Harbour Hall', 'previewText' => ''], $carried);
        $this->assertNull(Newsletter::designSettings(null));

        // Every preset names a face the designs have a stack for.
        foreach (self::DESIGNS as $design) {
            $this->assertArrayHasKey(Newsletter::templateDefaults($design)['fontFamily'], NewsletterTheme::FONTS, $design);
        }
    }

    public function test_the_text_part_says_what_the_designed_part_says(): void
    {
        $role = $this->venue();
        $show = $this->event($role, 'The Midnight Owls', 3, 20, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $this->createTicket($show, ['price' => 25, 'quantity' => 100]);
        EventPoll::create(['event_id' => $show->id, 'question' => 'Which night next?', 'options' => ['Tuesdays', 'Wednesdays'], 'is_active' => true, 'sort_order' => 0]);

        $newsletter = $this->newsletter($role, $this->everyBlock($role));
        $text = view('emails.newsletter_text', [
            'newsletter' => $newsletter,
            'role' => $role,
            'style' => $newsletter->style_settings,
            'blocks' => app(NewsletterService::class)->processBlocks($newsletter),
            'unsubscribeUrl' => 'https://app.example/nl/u/token',
            'manageUrl' => 'https://app.example/sub/m/token',
        ])->render();

        // The quote, the poll, the sponsors and the video used to be left out of this part.
        foreach (["What's on & where", 'The Midnight Owls', '8:00 PM', '$25', 'The one we asked to come back to.', 'Sara Vance', 'Which night next?', 'Tuesdays', 'Tidewater Brewing', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'JAZZ2FOR1', 'https://app.example/nl/u/token'] as $said) {
            $this->assertStringContainsString($said, $text, $said);
        }

        // It is never read as HTML: an apostrophe used to arrive as an entity.
        $this->assertDoesNotMatchRegularExpression('/&(#0?39|amp|gt|lt|quot);/', $text);
    }

    public function test_a_platform_newsletter_with_no_schedule_renders(): void
    {
        foreach (self::DESIGNS as $design) {
            $html = $this->render(null, [
                ['id' => 'logo', 'type' => 'profile_image', 'data' => []],
                ['id' => 'h1', 'type' => 'heading', 'data' => ['text' => 'What is new', 'level' => 'h1', 'align' => 'center']],
                ['id' => 'text', 'type' => 'text', 'data' => ['content' => 'Two things shipped.']],
                $this->eventsBlock(),
                ['id' => 'sponsors', 'type' => 'sponsors', 'data' => ['source' => 'schedule']],
                ['id' => 'poll', 'type' => 'poll', 'data' => []],
            ], $design);

            $this->assertStringContainsString('What is new', $html, $design);
            $this->assertStringContainsString(config('app.name'), $html, $design);
        }
    }

    public function test_a_video_thumbnail_can_be_asked_for_with_a_play_mark_drawn_into_it(): void
    {
        $id = 'PlayMark_01';
        $dir = storage_path('app/'.AppController::YOUTUBE_THUMB_CACHE_DIR);
        $forget = fn () => array_map(fn ($file) => @unlink($file), glob("{$dir}/{$id}_*") ?: []);
        $forget();

        $picture = imagecreatetruecolor(480, 360);
        imagefilledrectangle($picture, 0, 0, 480, 360, imagecolorallocate($picture, 200, 60, 40));
        ob_start();
        imagejpeg($picture, null, 90);
        $jpeg = ob_get_clean();

        Http::fake(['i.ytimg.com/*' => Http::response($jpeg, 200, ['Content-Type' => 'image/jpeg'])]);
        $this->venue(['youtube_links' => json_encode([['url' => "https://www.youtube.com/watch?v={$id}"]])]);

        $plain = $this->get("/yt-thumb/{$id}?q=hq")->assertOk()->getContent();
        $marked = $this->get("/yt-thumb/{$id}?q=hq&play=1")->assertOk()->assertHeader('Content-Type', 'image/jpeg')->getContent();

        $this->assertSame($jpeg, $plain);
        $this->assertNotSame($jpeg, $marked);

        // The mark is in the middle: the centre is no longer the picture's red, the corner still is.
        $read = imagecreatefromstring($marked);
        $centre = imagecolorsforindex($read, imagecolorat($read, 245, 180));
        $corner = imagecolorsforindex($read, imagecolorat($read, 10, 10));
        $this->assertGreaterThan(220, $centre['green'], 'the white triangle');
        $this->assertLessThan(90, $corner['green'], 'the picture itself');

        // Drawn from the cached plain thumbnail: YouTube was asked once.
        Http::assertSentCount(1);
        $this->assertFileExists("{$dir}/{$id}_hq_play.jpg");

        $forget();
    }
}
