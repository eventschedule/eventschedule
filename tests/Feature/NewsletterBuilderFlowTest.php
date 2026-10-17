<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Newsletter;
use App\Models\NewsletterAbTest;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\Role;
use App\Models\User;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * How a newsletter is written, checked and sent from the builder (the 2026-10 review of the
 * newsletter screens): it is saved without leaving the page, says who it goes to before it goes,
 * sends a test to an address the app knows, and sends an A/B test as a test. Each test is
 * something an owner could get wrong, or lose, before.
 */
class NewsletterBuilderFlowTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Http::preventStrayRequests();
    }

    private function param(Role $role): array
    {
        return ['role_id' => UrlUtils::encodeId($role->id)];
    }

    private function newsletter(Role $role, User $user, array $attrs = []): Newsletter
    {
        return Newsletter::create($attrs + [
            'role_id' => $role->id,
            'user_id' => $user->id,
            'type' => 'schedule',
            'subject' => 'October news',
            'status' => 'draft',
            'template' => 'modern',
            'blocks' => [],
        ]);
    }

    /** NewsletterService::isTestEmail() leaves example.com out of every send, so readers are at readers.dev. */
    private function follower(Role $role, string $email): User
    {
        $follower = User::factory()->create(['email' => $email, 'is_subscribed' => true, 'email_verified_at' => now()]);
        $role->users()->attach($follower->id, ['level' => 'follower']);

        return $follower;
    }

    private function saveHeaders(): array
    {
        return ['X-Save-Before-Action' => '1', 'Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_a_first_save_stays_on_the_page_and_hands_back_the_new_newsletters_addresses(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $response = $this->actingAs($owner)
            ->withHeaders($this->saveHeaders())
            ->post(route('newsletter.store', $this->param($role)), ['subject' => 'Spring', 'template' => 'modern', 'blocks' => '[]'])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $newsletter = Newsletter::where('subject', 'Spring')->firstOrFail();
        $hash = UrlUtils::encodeId($newsletter->id);

        $this->assertSame($hash, $response->json('hash'));
        $this->assertSame(route('newsletter.update', $this->param($role) + ['hash' => $hash]), $response->json('routes.update'));
        $this->assertSame(route('newsletter.edit', $this->param($role) + ['hash' => $hash]), $response->json('routes.edit'));
        $this->assertSame(route('newsletter.send', $this->param($role) + ['hash' => $hash]), $response->json('routes.send'));

        // A refused save says why, as JSON, and a sent newsletter is not saved over.
        $this->actingAs($owner)->withHeaders($this->saveHeaders())
            ->post(route('newsletter.store', $this->param($role)), ['subject' => '', 'template' => 'modern'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('subject');

        $newsletter->update(['status' => 'sent']);
        $this->actingAs($owner)->withHeaders($this->saveHeaders())
            ->put(route('newsletter.update', $this->param($role) + ['hash' => $hash]), ['subject' => 'Changed', 'template' => 'modern'])
            ->assertStatus(409);
        $this->assertSame('Spring', $newsletter->fresh()->subject);
    }

    public function test_the_audience_is_counted_and_named_before_anything_is_sent(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbour Hall']);
        $this->follower($role, 'one@readers.dev');
        $this->follower($role, 'two@readers.dev');

        // The two followers, and the schedule's own people, who always get a copy.
        $everyone = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)))->assertOk();
        $this->assertSame(3, $everyone->json('count'));
        $this->assertSame(__('messages.newsletter_audience_everyone', ['name' => 'Harbour Hall']), $everyone->json('label'));
        $this->assertNull($everyone->json('refusal'));

        // A chosen segment narrows it, by name; another schedule's segment is not this one's.
        $regulars = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Regulars', 'type' => 'manual']);
        NewsletterSegmentUser::create(['newsletter_segment_id' => $regulars->id, 'email' => 'three@readers.dev', 'name' => 'Three', 'created_at' => now()]);
        $elsewhere = NewsletterSegment::create(['role_id' => $this->createRole($this->createOwner())->id, 'name' => 'Theirs', 'type' => 'manual']);

        $chosen = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)).'&segment_ids[]='.$regulars->id.'&segment_ids[]='.$elsewhere->id)->assertOk();
        $this->assertSame(2, $chosen->json('count'));
        $this->assertSame('Regulars', $chosen->json('label'));

        // Nobody to send to is said before Send is pressed, with the way to add people.
        $owner->forceFill(['is_subscribed' => false])->save();
        $empty = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Nobody yet', 'type' => 'manual']);
        $nobody = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)).'&segment_ids[]='.$empty->id)->assertOk();
        $this->assertSame(__('messages.newsletter_no_recipients'), $nobody->json('refusal.message'));
        $this->assertSame(route('newsletter.import', $this->param($role)), $nobody->json('refusal.links.0.url'));

        // A viewer of the schedule is not one of the people who write its newsletters.
        $viewer = User::factory()->create();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->actingAs($viewer)->getJson(route('newsletter.audience', $this->param($role)))->assertForbidden();
    }

    public function test_the_months_allowance_is_said_and_a_spent_one_refuses_in_advance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->follower($role, 'one@readers.dev');
        $limit = $role->newsletterLimit();
        $this->assertNotNull($limit, 'a free schedule has an allowance');

        $fresh = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)))->assertOk();
        $count = $fresh->json('count');
        $this->assertSame(['used' => 0, 'limit' => $limit, 'after' => $limit - $count], $fresh->json('allowance'));

        $this->newsletter($role, $owner, ['status' => 'sent', 'sent_at' => now(), 'sent_count' => $limit]);

        $spent = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)))->assertOk();
        $this->assertSame(__('messages.newsletter_limit_reached', ['used' => $limit, 'limit' => $limit]), $spent->json('refusal.message'));
    }

    public function test_a_test_goes_to_the_person_writing_it_or_to_the_schedule_and_nowhere_else(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email' => 'hall@example.com']);
        $newsletter = $this->newsletter($role, $owner);
        $url = route('newsletter.test_send', $this->param($role) + ['hash' => UrlUtils::encodeId($newsletter->id)]);

        $this->actingAs($owner)->postJson($url, ['to' => 'me'])
            ->assertOk()
            ->assertJson(['message' => __('messages.newsletter_test_sent_to', ['email' => $owner->email])]);
        $this->actingAs($owner)->postJson($url, ['to' => 'schedule'])->assertOk();
        // Anything else is the person's own address: Send a test mails no address that is typed.
        $this->actingAs($owner)->postJson($url, ['to' => 'someone@elsewhere.example'])->assertOk();

        $this->assertSame(
            [$owner->email, 'hall@example.com', $owner->email],
            NewsletterRecipient::where('newsletter_id', $newsletter->id)->orderBy('id')->pluck('email')->all()
        );
        $this->assertSame(3, NewsletterRecipient::where('newsletter_id', $newsletter->id)->where('status', 'test')->count());

        // The fourth within the minute waits, and says so as JSON.
        $this->actingAs($owner)->postJson($url, ['to' => 'me'])->assertStatus(429);
    }

    public function test_a_time_in_the_past_is_refused_in_the_owners_language(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->newsletter($role, $owner);

        $this->actingAs($owner)
            ->post(route('newsletter.schedule', $this->param($role) + ['hash' => UrlUtils::encodeId($newsletter->id)]), ['scheduled_at' => now()->subDay()->format('Y-m-d H:i')])
            ->assertSessionHasErrors(['scheduled_at' => __('messages.newsletter_schedule_in_past')]);

        $this->assertSame('draft', $newsletter->fresh()->status);
    }

    public function test_one_variant_of_a_test_is_never_sent_on_its_own(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->follower($role, 'one@readers.dev');
        [$variantA, $variantB] = $this->abTest($role, $owner);

        foreach ([$variantA, $variantB] as $variant) {
            $params = $this->param($role) + ['hash' => UrlUtils::encodeId($variant->id)];
            $this->actingAs($owner)->post(route('newsletter.send', $params))->assertSessionHas('error', __('messages.ab_test_send_as_test'));
            $this->actingAs($owner)->post(route('newsletter.schedule', $params), ['scheduled_at' => now()->addDay()->format('Y-m-d H:i')])
                ->assertSessionHas('error', __('messages.ab_test_send_as_test'));
            $this->assertSame('draft', $variant->fresh()->status);
        }
        $this->assertSame(0, NewsletterRecipient::count());
    }

    public function test_an_ab_test_waits_for_a_real_queue_and_splits_variant_as_audience(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        foreach (range(1, 9) as $i) {
            $this->follower($role, "reader{$i}@readers.dev");
        }
        [$variantA, $variantB] = $this->abTest($role, $owner, 20);

        // Variant B once had a segment of its own that reaches nobody: the test goes to A's.
        $empty = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Nobody', 'type' => 'manual']);
        $variantB->update(['segment_ids' => [$empty->id]]);

        $abSend = route('newsletter.ab_send', $this->param($role) + ['hash' => UrlUtils::encodeId($variantB->id)]);

        // On the sync driver the winner would be picked at once, from a sample nobody has opened.
        config(['queue.default' => 'sync']);
        $this->actingAs($owner)->post($abSend)->assertSessionHas('error', __('messages.ab_test_needs_queue'));
        $this->assertSame('pending', $variantA->abTest->fresh()->status);
        $this->assertSame(0, NewsletterRecipient::count());

        $audience = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)).'&newsletter='.UrlUtils::encodeId($variantB->id))->assertOk();
        $this->assertSame(__('messages.ab_test_needs_queue'), $audience->json('refusal.message'));

        Queue::fake();
        config(['queue.default' => 'database']);
        $audience = $this->actingAs($owner)->getJson(route('newsletter.audience', $this->param($role)).'&newsletter='.UrlUtils::encodeId($variantB->id))->assertOk();
        // Nine followers and the owner: variant A's audience, not B's empty segment. 20% of ten.
        $this->assertSame(10, $audience->json('count'), "variant A's audience, not B's empty segment");
        $this->assertSame(['sample_a' => 1, 'sample_b' => 1, 'rest' => 8], array_intersect_key($audience->json('ab'), array_flip(['sample_a', 'sample_b', 'rest'])));

        $this->actingAs($owner)->post($abSend)->assertSessionHas('status', __('messages.ab_test_sending'));
        $this->assertSame(1, NewsletterRecipient::where('newsletter_id', $variantA->id)->count());
        $this->assertSame(1, NewsletterRecipient::where('newsletter_id', $variantB->id)->count());
        $this->assertSame('sending', $variantA->abTest->fresh()->status);
    }

    public function test_the_preview_names_each_block_and_a_sent_mail_never_does(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->newsletter($role, $owner, ['blocks' => [
            ['id' => 'b_heading1', 'type' => 'heading', 'data' => ['text' => 'Hello', 'level' => 'h1', 'align' => 'center']],
            ['id' => 'b_text1', 'type' => 'text', 'data' => ['content' => 'A line']],
        ]]);
        $newsletter->setRelation('role', $role);
        $service = app(NewsletterService::class);

        $preview = $service->renderPreview($newsletter);
        $this->assertSame(1, substr_count($preview, 'data-nl-block="b_heading1"'));
        $this->assertSame(1, substr_count($preview, 'data-nl-block="b_text1"'));

        $this->assertStringNotContainsString('data-nl-block', $service->renderHtml($newsletter));
    }

    public function test_the_statistics_count_who_left_from_this_newsletter_and_show_what_went_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->newsletter($role, $owner, ['status' => 'sent', 'sent_at' => now()->subDay(), 'sent_count' => 2]);
        $recipient = NewsletterRecipient::create([
            'newsletter_id' => $newsletter->id, 'email' => 'leaving@example.com', 'name' => 'Leaving',
            'token' => Str::random(64), 'status' => 'sent', 'sent_at' => now(),
        ]);

        $this->post(route('newsletter.unsubscribe', ['token' => $recipient->token]));
        $this->assertDatabaseHas('newsletter_unsubscribes', ['role_id' => $role->id, 'email' => 'leaving@example.com', 'newsletter_id' => $newsletter->id]);

        $params = $this->param($role) + ['hash' => UrlUtils::encodeId($newsletter->id)];
        $html = $this->actingAs($owner)->get(route('newsletter.stats', $params))->assertOk()->getContent();

        $this->assertStringContainsString(__('messages.newsletter_unsubscribes'), $html);
        $this->assertSame(1, substr_count($html, 'id="news-view-mail" data-url="'.e(route('newsletter.preview', $params)).'"'));
        $this->assertSame(1, substr_count($html, 'action="'.e(route('newsletter.clone', $params)).'"'));
    }

    public function test_a_send_in_progress_says_how_far_it_has_got(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->newsletter($role, $owner, ['status' => 'sending', 'sent_at' => now()]);
        foreach (['sent', 'sent', 'pending', 'test'] as $i => $status) {
            NewsletterRecipient::create(['newsletter_id' => $newsletter->id, 'email' => "r{$i}@example.com", 'token' => Str::random(64), 'status' => $status]);
        }

        $progress = __('messages.newsletter_sending_progress', ['done' => 2, 'total' => 3]);
        $this->assertStringContainsString($progress, $this->actingAs($owner)->get(route('newsletter.index', $this->param($role)))->assertOk()->getContent());
        $this->assertStringContainsString($progress, $this->actingAs($owner)->get(route('newsletter.stats', $this->param($role) + ['hash' => UrlUtils::encodeId($newsletter->id)]))->assertOk()->getContent());
    }

    public function test_the_list_keeps_a_tests_two_variants_together(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        [$variantA, $variantB] = $this->abTest($role, $owner);
        $variantA->update(['subject' => 'Variant A subject']);
        $variantB->update(['subject' => 'Variant B subject']);
        // A newer newsletter would otherwise land between them in the default order.
        $this->newsletter($role, $owner, ['subject' => 'Newer one', 'created_at' => now()->addMinute()]);

        $html = $this->actingAs($owner)->get(route('newsletter.index', $this->param($role)).'&sort_by=created_at&sort_dir=asc')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Variant B subject'));
        $this->assertSame(1, substr_count($html, 'class="news-variant-row"'));
        $a = strpos($html, 'Variant A subject');
        $b = strpos($html, 'Variant B subject');
        $this->assertTrue($a < $b && $b < strpos($html, 'Newer one'), 'B straight after A');
    }

    public function test_renaming_a_segment_keeps_its_event_and_the_event_can_be_changed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $first = $this->createEvent($role, ['name' => 'First night']);
        $second = $this->createEvent($role, ['name' => 'Second night']);
        $theirs = $this->createEvent($this->createRole($this->createOwner()), ['name' => 'Their night']);
        $segment = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Buyers', 'type' => 'ticket_buyers', 'filter_criteria' => ['event_id' => $first->id, 'date_from' => '2026-01-01']]);
        $params = $this->param($role) + ['hash' => UrlUtils::encodeId($segment->id)];

        // The page offers the event, chosen.
        $html = $this->actingAs($owner)->get(route('newsletter.segment.edit', $params))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<option value="'.UrlUtils::encodeId($first->id).'" selected/', $html));

        // A rename that sends no filter keeps it, date and all.
        $this->actingAs($owner)->put(route('newsletter.segment.update', $params), ['name' => 'First night buyers'])->assertSessionHas('status');
        $this->assertSame(['event_id' => $first->id, 'date_from' => '2026-01-01'], $segment->fresh()->filter_criteria);

        $this->actingAs($owner)->put(route('newsletter.segment.update', $params), ['name' => 'Second night buyers', 'filter_criteria' => ['event_id' => UrlUtils::encodeId($second->id)]]);
        $this->assertEqualsCanonicalizing(['date_from' => '2026-01-01', 'event_id' => $second->id], $segment->fresh()->filter_criteria);

        // Another schedule's event is not one this segment can be narrowed to.
        $this->actingAs($owner)->put(route('newsletter.segment.update', $params), ['name' => 'Theirs', 'filter_criteria' => ['event_id' => UrlUtils::encodeId($theirs->id)]])
            ->assertSessionHasErrors('filter_criteria.event_id');
        $this->assertSame($second->id, $segment->fresh()->filter_criteria['event_id']);
    }

    public function test_the_builder_is_given_what_it_needs_to_say_who_and_where(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbour Hall', 'email' => 'hall@example.com']);
        [$variantA, $variantB] = $this->abTest($role, $owner);

        $props = $this->builderProps($this->actingAs($owner)->get(route('newsletter.edit', $this->param($role) + ['hash' => UrlUtils::encodeId($variantB->id)]))->assertOk()->getContent());

        $this->assertSame(__('messages.newsletter_audience_everyone', ['name' => 'Harbour Hall']), $props['everyoneLabel']);
        $this->assertSame(['me', 'schedule'], array_column($props['testRecipients'], 'value'));
        $this->assertSame([$owner->email, 'hall@example.com'], array_column($props['testRecipients'], 'email'));
        $this->assertSame('B', $props['abTest']['variant']);
        $this->assertTrue($props['abTest']['pending']);
        $this->assertSame(route('newsletter.edit', $this->param($role) + ['hash' => UrlUtils::encodeId($variantA->id)]), $props['abTest']['other_url']);
        $this->assertSame(route('newsletter.audience', $this->param($role)), $props['routes']['audience']);
        $this->assertSame(route('newsletter.ab_send', $this->param($role) + ['hash' => UrlUtils::encodeId($variantB->id)]), $props['routes']['ab_send']);

        // The create page's builder saves to store until it has a newsletter of its own.
        $create = $this->builderProps($this->actingAs($owner)->get(route('newsletter.create', $this->param($role)))->assertOk()->getContent());
        $this->assertSame(route('newsletter.store', $this->param($role)), $create['routes']['store']);
        $this->assertArrayNotHasKey('send', $create['routes']);
    }

    /**
     * The builder's stylesheet styles the component's own nb-* classes, on the portal's tokens.
     * It used to dress the component from outside by its Tailwind utilities (.flex.border-b,
     * bg-yellow-500), so any edit to the component could undo the look. A class the sheet styles
     * that the component no longer carries is a rule that silently stopped applying.
     */
    public function test_the_builders_stylesheet_styles_classes_the_component_carries(): void
    {
        $component = file_get_contents(resource_path('js/components/NewsletterBuilder.vue'));
        $sheet = file_get_contents(resource_path('views/newsletter/partials/_builder-styles.blade.php'));

        preg_match_all('/\.(nb-[a-z0-9-]+)/', $sheet, $classes);
        foreach (array_unique($classes[1]) as $class) {
            if ($class === 'nb-sheet-open' || $class === 'nb-mark') {
                continue; // set on <html> and drawn into the preview's frame, by the script
            }
            $this->assertMatchesRegularExpression('/["\s\']'.preg_quote($class, '/').'["\s\']/', $component, "the builder carries {$class}");
        }

        foreach (['.flex.border-b', 'bg-yellow-500', 'bg-green-600', '.mt-4.flex'] as $hook) {
            $this->assertStringNotContainsString($hook, $sheet, "the sheet no longer leans on {$hook}");
        }

        // The kit's own pieces, as they are.
        foreach (['ap-tabs-wrap', 'class="ap-tab"', 'event-save-bar', 'event-save-actions', 'event-link'] as $kit) {
            $this->assertStringContainsString($kit, $component, $kit);
        }

        // Every page that holds the builder loads the sheet.
        foreach (['newsletter/create', 'newsletter/edit', 'newsletter/template-edit', 'admin/newsletters/create', 'admin/newsletters/edit', 'admin/newsletters/template-edit'] as $view) {
            $source = file_get_contents(resource_path("views/{$view}.blade.php"));

            $this->assertStringContainsString("@include('newsletter.partials._builder-styles')", $source, $view);
            $this->assertStringContainsString("@include('newsletter.partials._builder')", $source, $view);
        }
    }

    /** @return array{0: Newsletter, 1: Newsletter} */
    private function abTest(Role $role, User $owner, int $sample = 20): array
    {
        $variantA = $this->newsletter($role, $owner, ['subject' => 'Version A']);
        $test = NewsletterAbTest::create([
            'role_id' => $role->id, 'name' => 'Test', 'test_field' => 'subject', 'sample_percentage' => $sample,
            'winner_criteria' => 'open_rate', 'winner_wait_hours' => 4, 'status' => 'pending',
        ]);
        $variantA->update(['ab_test_id' => $test->id, 'ab_variant' => 'A']);
        $variantB = $this->newsletter($role, $owner, ['subject' => 'Version B', 'ab_test_id' => $test->id, 'ab_variant' => 'B']);

        return [$variantA->fresh(), $variantB->fresh()];
    }

    private function builderProps(string $html): array
    {
        $this->assertSame(1, preg_match('/<div id="newsletter-builder" data-props="([^"]*)"/', $html, $match));

        return json_decode(html_entity_decode($match[1]), true);
    }
}
