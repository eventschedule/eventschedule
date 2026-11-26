<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Newsletter;
use App\Models\Role;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A line under each event's name in a newsletter's Events block (issue #138): the short
 * description, or one of the schedule's custom fields. A field reaches the mail only as the event
 * page prints it, and only from the sending schedule's own form.
 */
class NewsletterEventDetailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function venue(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC', 'event_custom_fields' => [
            'new_0' => ['name' => 'Room', 'type' => 'string', 'show_on_event' => true, 'index' => 1],
            'new_1' => ['name' => 'Fee paid', 'type' => 'string', 'private' => true, 'show_on_event' => true, 'index' => 2],
            'new_2' => ['name' => 'Contact phone', 'type' => 'string', 'index' => 3],
            'new_3' => ['name' => 'Step-free', 'type' => 'switch', 'show_on_event' => true, 'index' => 4],
        ]]);
    }

    private function event(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'name' => 'Opening Night',
            'starts_at' => now()->addDays(3)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
            'custom_field_values_role_id' => $role->id,
        ]);
    }

    private function newsletter(Role $role, string $detail, string $layout = 'cards', string $template = 'modern'): Newsletter
    {
        $newsletter = new Newsletter([
            'role_id' => $role->id,
            'user_id' => $role->user_id,
            'subject' => 'This week',
            'status' => 'draft',
            'template' => $template,
            'type' => 'schedule',
            'blocks' => [['id' => 'e', 'type' => 'events', 'data' => ['layout' => $layout, 'useAllEvents' => true, 'eventIds' => [], 'detail' => $detail]]],
        ]);
        $newsletter->setRelation('role', $role);

        return $newsletter;
    }

    /** @return list<?string> */
    private function blurbs(Role $role, string $detail): array
    {
        $block = collect(app(NewsletterService::class)->processBlocks($this->newsletter($role, $detail)))->firstWhere('type', 'events');

        return collect($block['data']['resolvedEvents'])->pluck('blurb')->all();
    }

    public function test_the_short_description_is_printed_under_the_name_in_every_design(): void
    {
        $role = $this->venue();
        $this->event($role, ['short_description' => 'An evening of new songs']);

        $this->assertSame(['An evening of new songs'], $this->blurbs($role, 'short_description'));
        $this->assertSame([null], $this->blurbs($role, ''), 'nothing unless it is chosen');

        foreach (['modern', 'classic', 'minimal', 'bold', 'compact'] as $template) {
            foreach (['cards', 'list'] as $layout) {
                $html = app(NewsletterService::class)->renderHtml($this->newsletter($role, 'short_description', $layout, $template));
                $this->assertStringContainsString('An evening of new songs', $html, "$template / $layout");
                $this->assertGreaterThan(strpos($html, 'Opening Night'), strpos($html, 'An evening of new songs'), "under the name in $template / $layout");
            }
        }
    }

    public function test_a_custom_field_is_printed_only_where_the_event_page_prints_it(): void
    {
        $role = $this->venue();
        $this->event($role, ['custom_field_values' => ['new_0' => 'Garden', 'new_1' => '40', 'new_2' => '555-0100', 'new_3' => '1']]);

        $this->assertSame(['Garden'], $this->blurbs($role, 'field:new_0'));
        $this->assertSame(['Step-free'], $this->blurbs($role, 'field:new_3'), 'a Yes says the field name');
        $this->assertSame([null], $this->blurbs($role, 'field:new_1'), 'a private field');
        $this->assertSame([null], $this->blurbs($role, 'field:new_2'), 'a field the page does not show');
        $this->assertSame([null], $this->blurbs($role, 'field:<b>'), 'a key that is no key');
    }

    public function test_another_schedules_answers_are_not_read_under_this_schedules_field(): void
    {
        $role = $this->venue();
        $act = $this->createRole($this->createOwner(), 'talent', ['event_custom_fields' => [
            'new_0' => ['name' => 'Set list', 'type' => 'string', 'show_on_event' => true, 'index' => 1],
        ]]);
        $event = $this->event($act, ['custom_field_values_role_id' => $act->id, 'custom_field_values' => ['new_0' => 'Secret songs']]);
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $this->assertSame([null], $this->blurbs($role, 'field:new_0'));
    }

    public function test_the_builder_offers_the_fields_the_event_page_shows(): void
    {
        $role = $this->venue();

        $this->assertSame(
            ['short_description', 'field:new_0', 'field:new_3'],
            array_column(NewsletterService::detailChoices($role), 'value'),
        );

        $this->actingAs($role->user)->get(route('newsletter.create', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee(__('messages.newsletter_events_detail'));
    }
}
