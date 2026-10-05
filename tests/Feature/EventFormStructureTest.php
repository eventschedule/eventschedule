<?php

namespace Tests\Feature;

use App\Models\CarpoolOffer;
use App\Models\CarpoolReport;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Two things about the event form's markup that broke real actions without failing anything.
 *
 * A form inside the form. The carpool tab wrote its "remove offer" and "dismiss report" forms
 * inside the event form. A browser drops the first nested <form> tag, so that form's
 * _method=DELETE became a field of the event form, after the event's own _method=put. PHP keeps
 * the last one, the update route only accepts PUT, and every save of an event with a carpool
 * offer answered 405 - while that offer's Remove button submitted the event.
 *
 * A link to an id that is not there. Notification emails, push messages and seventeen redirects
 * linked to #section-fan-content and #section-polls, which name tabs inside Engagement and have
 * no element of their own, so all of them opened the form's first section.
 */
class EventFormStructureTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    /** @return array{0: User, 1: Role, 2: Event, 3: CarpoolOffer, 4: CarpoolReport} */
    private function eventWithCarpool(): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['carpool_enabled' => true]);
        $event = $this->createEvent($venue);
        $driver = $this->createOwner();
        $reporter = $this->createOwner();

        // Two offers, each reported: the browser treats the first nested form differently from the
        // rest, so one of each is not enough to see every button's owner.
        foreach (['Brooklyn', 'Queens'] as $city) {
            $offer = CarpoolOffer::create([
                'event_id' => $event->id, 'user_id' => $driver->id, 'role_id' => $venue->id,
                'event_date' => now()->addDays(7)->format('Y-m-d'), 'direction' => 'to_event', 'city' => $city,
                'departure_time' => '18:30', 'meeting_point' => 'Atlantic Ave', 'total_spots' => 3, 'status' => 'active',
            ]);
            $report = CarpoolReport::create([
                'reporter_user_id' => $reporter->id, 'reported_user_id' => $driver->id,
                'carpool_offer_id' => $offer->id, 'reason' => 'late',
            ]);
        }

        return [$owner, $venue, $event, $offer, $report];
    }

    private function editUrl(Role $role, Event $event): string
    {
        return route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
    }

    /** From the event form's opening tag to the first closing tag after it. */
    private function mainForm(string $html): string
    {
        $start = strpos($html, 'id="edit-form"');
        $this->assertNotFalse($start, 'the event form was rendered');
        $end = strpos($html, '</form>', $start);

        return substr($html, $start, $end - $start);
    }

    public function test_the_event_form_holds_no_other_form(): void
    {
        [$owner, $venue, $event] = $this->eventWithCarpool();

        $html = $this->actingAs($owner)->get($this->editUrl($venue, $event))->assertOk()->getContent();

        $this->assertStringContainsString('carpool/remove-offer', $html, 'sanity check: the carpool actions were rendered');
        $this->assertStringContainsString('carpool/dismiss-report', $html);

        $form = $this->mainForm($html);

        $this->assertStringNotContainsString('<form', $form, 'a form nested in the event form posts its fields with the event');
        $this->assertSame(1, substr_count($form, 'name="_method"'), 'the event form carries its own method override and no other');
        $this->assertStringContainsString('name="starts_at"', $form, 'sanity check: this is the whole event form, not a fragment of it');
    }

    /** A button outside its form reaches it by id. One that names a form that is not there does nothing. */
    public function test_every_button_that_names_a_form_names_one_that_exists(): void
    {
        [$owner, $venue, $event] = $this->eventWithCarpool();

        $html = $this->actingAs($owner)->get($this->editUrl($venue, $event))->assertOk()->getContent();

        preg_match_all('/<button[^>]*\sform="([^"{}]+)"/', $html, $buttons);
        preg_match_all('/<form[^>]*\sid="([^"]+)"/', $html, $forms);

        $this->assertGreaterThanOrEqual(4, count($buttons[1]), 'the four carpool buttons were found');
        $this->assertSame([], array_values(array_diff(array_unique($buttons[1]), $forms[1])));
    }

    public function test_removing_a_carpool_offer_still_works_and_comes_back_to_its_tab(): void
    {
        [$owner, $venue, $event, $offer, $report] = $this->eventWithCarpool();
        $editUrl = $this->editUrl($venue, $event);

        $this->actingAs($owner)->from($editUrl)
            ->delete(route('carpool.admin_dismiss_report', ['subdomain' => $venue->subdomain, 'report_hash' => UrlUtils::encodeId($report->id)]))
            ->assertRedirect($editUrl.'#section-carpool');
        $this->assertNull(CarpoolReport::find($report->id));

        $this->actingAs($owner)->from($editUrl)
            ->delete(route('carpool.admin_remove_offer', ['subdomain' => $venue->subdomain, 'offer_hash' => UrlUtils::encodeId($offer->id)]))
            ->assertRedirect($editUrl.'#section-carpool');
        $this->assertNotSame('active', CarpoolOffer::find($offer->id)->status);
    }

    /** What the server does with the request the old markup produced. Pins why nesting matters. */
    public function test_an_update_carrying_a_delete_override_is_refused(): void
    {
        [$owner, $venue, $event] = $this->eventWithCarpool();

        $this->actingAs($owner)
            ->post(route('event.update', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), ['_method' => 'DELETE', 'name' => 'Renamed'])
            ->assertStatus(405);
    }

    /**
     * Every #section-... fragment the application builds has to land somewhere: an element on one
     * of the three tabbed forms, or a fragment the event form resolves to a tab of its own.
     */
    public function test_every_section_fragment_the_app_builds_exists(): void
    {
        $known = [];
        foreach (['event/edit', 'role/edit', 'profile/edit'] as $view) {
            preg_match_all('/id="(section-[a-z-]+)"/', file_get_contents(resource_path("views/{$view}.blade.php")), $ids);
            $known = array_merge($known, $ids[1]);
        }
        $known = array_merge($known, $this->aliases());

        $built = [];
        $files = Finder::create()->files()->name('*.php')
            ->in([app_path(), resource_path('views')])
            ->notPath('marketing');
        foreach ($files as $file) {
            preg_match_all('/#(section-[a-z-]+)/', $file->getContents(), $found);
            foreach ($found[1] as $fragment) {
                $built[$fragment][] = $file->getRelativePathname();
            }
        }

        $this->assertGreaterThan(20, count($built), 'the scan found the fragments, not nothing');
        $this->assertArrayHasKey('section-fan-content', $built, 'sanity check: the scan sees the fragment this test exists for');

        $dead = array_diff_key($built, array_flip($known));
        $this->assertSame([], array_map(fn ($files) => array_values(array_unique($files)), $dead));
    }

    /** The aliases are JavaScript, so they are run, not read. */
    public function test_the_fragments_that_name_an_engagement_tab_resolve_to_it(): void
    {
        [$owner, $venue, $event] = $this->eventWithCarpool();
        $html = $this->actingAs($owner)->get($this->editUrl($venue, $event))->assertOk()->getContent();

        $this->assertSame(1, preg_match('#// event-section-aliases:start(.*?)// event-section-aliases:end#s', $html, $block));

        $cases = [
            ['#section-fan-content', '', 'section-engagement', 'fan_content'],
            // Approving fan content from a page opened on the polls tab comes back with both.
            ['#section-fan-content', '?engagement=polls', 'section-engagement', 'fan_content'],
            ['#section-polls', '', 'section-engagement', 'polls'],
            ['#section-carpool', '?engagement=feedback', 'section-engagement', 'carpool'],
            ['#section-engagement', '?engagement=carpool', 'section-engagement', 'carpool'],
            ['#section-engagement', '?engagement=nonsense', 'section-engagement', 'fan_content'],
            ['#section-tickets', '', 'section-tickets', 'fan_content'],
            ['', '', '', 'fan_content'],
        ];

        $script = 'const window = {};'.$block[1]
            .'const cases = '.json_encode($cases).';'
            .'console.log(JSON.stringify(cases.map(c => { const r = window.resolveEventSectionHash(c[0], c[1]); return [c[0], c[1], r.section, r.engagementTab]; })));';

        $process = new Process(['node', '-e', $script]);
        $process->setTimeout(30);
        $process->run();

        $this->assertTrue($process->isSuccessful(), "The alias resolver did not run in Node. If node is missing, install it - this test must not be skipped.\n".$process->getErrorOutput());
        $this->assertSame($cases, json_decode($process->getOutput(), true));
    }

    /**
     * The Help button reads the raw fragment, so each alias needs its own entry in the map the
     * layout inlines for this page (HelpUtils::getAnchorMap() answers for the current request).
     */
    public function test_each_alias_has_a_help_page(): void
    {
        [$owner, $venue, $event] = $this->eventWithCarpool();

        $pages = [
            $this->editUrl($venue, $event),
            route('event.create', ['subdomain' => $venue->subdomain]),
        ];

        foreach ($pages as $url) {
            $html = $this->actingAs($owner)->get($url)->assertOk()->getContent();

            foreach ($this->aliases() as $alias) {
                $this->assertMatchesRegularExpression('/"'.preg_quote($alias, '/').'":"[^"]*docs[^"]*#[a-z-]+"/', $html, "{$alias} on {$url}");
            }
        }
    }

    /** @return string[] */
    private function aliases(): array
    {
        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $this->assertSame(1, preg_match('/window\.eventSectionAliases = \{(.*?)\};/s', $view, $block));
        preg_match_all("/'(section-[a-z-]+)':/", $block[1], $keys);
        $this->assertNotEmpty($keys[1]);

        return $keys[1];
    }
}
