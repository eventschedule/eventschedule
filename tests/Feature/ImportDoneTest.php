<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\ImportRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Where an import ends, and how it is taken back.
 *
 * "N events added" and "Undo this import" both work on one sitting's events, found by the batch
 * the session holds. Neither endpoint takes a count, an id or a batch from the request: what was
 * added is read from the database, and what is removed is only what that sitting added.
 */
class ImportDoneTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.gemini_key' => 'test-key']);
        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'venue');
        $this->actingAs($this->owner);
    }

    private function openImportPage(?Role $role = null)
    {
        return $this->get(route('event.show_import_ai', ['subdomain' => ($role ?? $this->role)->subdomain]))->assertOk();
    }

    private function import(string $name, int $daysAhead = 10, ?Role $role = null): Event
    {
        $this->postJson(route('event.import', ['subdomain' => ($role ?? $this->role)->subdomain]), [
            'name' => $name,
            'starts_at' => now()->addDays($daysAhead)->setTime(19, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
        ])->assertOk();

        return Event::query()->latest('id')->firstOrFail();
    }

    private function done(?Role $role = null)
    {
        return $this->get(route('event.import_done', ['subdomain' => ($role ?? $this->role)->subdomain]));
    }

    private function undo(array $payload = [], ?Role $role = null)
    {
        return $this->post(route('event.import_undo', ['subdomain' => ($role ?? $this->role)->subdomain]), $payload);
    }

    private function schedule(?Role $role = null): string
    {
        return route('role.view_admin', ['subdomain' => ($role ?? $this->role)->subdomain, 'tab' => 'schedule']);
    }

    public function test_done_counts_what_this_sitting_added_and_says_so_on_the_schedule(): void
    {
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'Typed in']);
        $this->openImportPage();
        $this->import('Later', 20);
        $this->import('Sooner', 5);
        $this->import('Middle', 10);
        $this->import('Last', 30);

        $response = $this->done()->assertRedirect($this->schedule());
        // The count is the database's, and the names are the soonest three.
        $response->assertSessionHas('events_imported', ['count' => 4, 'names' => ['Sooner', 'Middle', 'Later']]);

        $page = $this->get($this->schedule())->assertOk();
        $page->assertSee('Events added to your schedule: 4');
        $page->assertSee('and 1 more');
        $page->assertSee('Undo this import');
        $page->assertSee('Add to your website');
        $this->assertNotNull($byHand->fresh());
    }

    public function test_done_with_nothing_added_is_just_the_schedule(): void
    {
        $this->done()->assertRedirect($this->schedule())->assertSessionMissing('events_imported');

        $this->openImportPage();
        $this->done()->assertRedirect($this->schedule())->assertSessionMissing('events_imported');
    }

    public function test_a_few_events_lead_to_adding_more_and_a_calendar_leads_to_the_website(): void
    {
        $this->openImportPage();
        $this->import('Only one');
        $this->done();
        $few = $this->get($this->schedule())->assertOk()->getContent();
        // The forward button, at the end, is the last brand link in the panel.
        $this->assertStringContainsString('Add more events', $few);
        $this->assertStringNotContainsString('Import more', $few);

        $this->openImportPage();
        foreach (range(1, 5) as $n) {
            $this->import('Event '.$n, 10 + $n);
        }
        $this->done();
        $many = $this->get($this->schedule())->assertOk()->getContent();
        $this->assertStringContainsString('Import more', $many);
        $this->assertStringNotContainsString('Add more events', $many);
    }

    public function test_undo_removes_this_sittings_events_and_nothing_else(): void
    {
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'Typed in']);

        // An earlier sitting, finished and left alone.
        $this->openImportPage();
        $earlier = $this->import('Earlier import');
        $this->done();

        $this->openImportPage();
        $first = $this->import('First');
        $second = $this->import('Second');
        $this->done();

        $this->undo()->assertRedirect($this->schedule())
            ->assertSessionHas('import_undone', ['removed' => 2, 'kept' => 0]);

        $this->assertNull($first->fresh());
        $this->assertNull($second->fresh());
        $this->assertNotNull($earlier->fresh(), 'an earlier import is not this one');
        $this->assertNotNull($byHand->fresh(), 'an event typed in by hand is nobody\'s import');

        $this->get($this->schedule())->assertOk()->assertSee('Import undone. Events removed: 2.');

        // It can be undone once.
        $this->undo()->assertRedirect($this->schedule())->assertSessionMissing('import_undone');
        $this->assertNotNull($earlier->fresh());
    }

    public function test_undo_keeps_an_event_somebody_holds_a_ticket_for(): void
    {
        $this->openImportPage();
        $sold = $this->import('Sold out show');
        $unsold = $this->import('Quiet night');
        $this->createSale($sold, $this->role);
        $this->done();

        $this->undo()->assertSessionHas('import_undone', ['removed' => 1, 'kept' => 1]);

        $this->assertNotNull($sold->fresh());
        $this->assertNull($unsold->fresh());
        $this->get($this->schedule())->assertSee('Kept, because someone has a ticket or booking for them: 1.');
    }

    public function test_what_is_undone_is_decided_by_the_session_not_the_request(): void
    {
        $this->openImportPage();
        $mine = $this->import('Mine');
        $batch = $mine->import_batch;
        $this->done();

        // Another editor of the same schedule, with no import of their own.
        $other = $this->createOwner();
        $this->followRole($other, $this->role, 'admin');
        $this->actingAs($other)->withSession([])->flushSession();

        $this->actingAs($other)->undo(['batch' => $batch, 'import_batch' => $batch])
            ->assertRedirect($this->schedule())->assertSessionMissing('import_undone');
        $this->assertNotNull($mine->fresh());

        // Even holding the batch in their own session, they reach only what they added
        // themselves: a batch names a sitting, and a sitting is one person's.
        $held = ['import_last.'.$this->role->id => ['batch' => $batch, 'finished_at' => now()->getTimestamp()]];
        $this->actingAs($other)->withSession($held)->undo()
            ->assertSessionHas('import_undone', ['removed' => 0, 'kept' => 0]);
        $this->assertNotNull($mine->fresh());
        $this->actingAs($other)->withSession($held)->done()->assertSessionMissing('events_imported');

        // And somebody who cannot edit the schedule at all.
        $stranger = $this->createOwner();
        $this->actingAs($stranger)->undo()->assertStatus(403);
        $this->actingAs($stranger)->done()->assertStatus(403);
        $this->assertNotNull($mine->fresh());
    }

    public function test_an_undo_is_offered_for_a_day_and_no_longer(): void
    {
        $this->openImportPage();
        $event = $this->import('Yesterday');
        $this->done();

        $this->travel(ImportRun::UNDO_HOURS + 1)->hours();

        $this->openImportPage()->assertDontSee('Undo this import');
        $this->undo()->assertRedirect($this->schedule())->assertSessionMissing('import_undone');
        $this->assertNotNull($event->fresh());
    }

    public function test_an_import_left_without_finishing_can_still_be_undone_from_the_import_page(): void
    {
        $this->openImportPage();
        $left = $this->import('Left behind');

        // Back on the import page later: the last import is named, with its undo.
        $page = $this->openImportPage();
        $page->assertSee('Your last import added events to this schedule: 1.');
        $page->assertSee('Undo this import');

        // What is added now is a sitting of its own.
        $now = $this->import('This time');
        $this->assertNotSame($left->import_batch, $now->import_batch);

        $this->undo()->assertSessionHas('import_undone', ['removed' => 1, 'kept' => 0]);
        $this->assertNull($left->fresh());
        $this->assertNotNull($now->fresh());
    }

    public function test_one_schedules_import_is_not_undone_from_another(): void
    {
        $other = $this->createRole($this->owner, 'venue');

        $this->openImportPage();
        $here = $this->import('Here');
        $this->done();

        $this->undo([], $other)->assertRedirect($this->schedule($other))->assertSessionMissing('import_undone');
        $this->assertNotNull($here->fresh());

        // Nor when the other schedule's session entry names this schedule's batch.
        $this->withSession(['import_last.'.$other->id => ['batch' => $here->import_batch, 'finished_at' => now()->getTimestamp()]])
            ->undo([], $other)->assertSessionHas('import_undone', ['removed' => 0, 'kept' => 0]);
        $this->assertNotNull($here->fresh());
    }

    public function test_a_reload_half_way_through_keeps_one_sitting_until_events_are_added(): void
    {
        // Opening the page twice with nothing added is still one run.
        $this->openImportPage();
        $this->openImportPage()->assertDontSee('Your last import added');
        $first = $this->import('One');
        $second = $this->import('Two');

        $this->assertNotNull($first->import_batch);
        $this->assertSame($first->import_batch, $second->import_batch);
    }
}
