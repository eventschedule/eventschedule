<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventAnnouncement;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Services\TranslationQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A feed's events arrive with nobody at the keyboard, a hundred in one read. Three things that
 * were built for events somebody typed in would otherwise treat that as a hundred things a
 * person just did: the translation queue, the order it works in, and the mail to followers.
 */
class FeedQuietRulesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        // A schedule written in Italian that wants English: its events are queued for translation.
        $this->role = $this->createRole($this->createOwner(), 'venue', ['language_code' => 'it', 'translation_language_code' => 'en']);
    }

    private function event(string $name, array $attrs = []): Event
    {
        $event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => $name]);
        $event->forceFill($attrs)->save();

        return $event->fresh();
    }

    /** A draft a feed made waits for its owner. Translating it is spend on an event that may never be published. */
    public function test_a_feeds_draft_is_not_queued_for_translation_until_it_is_published(): void
    {
        $feedDraft = $this->event('Feed draft', ['is_draft' => true, 'import_source' => Event::IMPORT_FEED]);
        $feedPublished = $this->event('Feed published', ['import_source' => Event::IMPORT_FEED]);
        $handDraft = $this->event('Hand-made draft', ['is_draft' => true]);
        $importedDraft = $this->event('Draft from the import page', ['is_draft' => true, 'import_source' => Event::IMPORT_ICS]);

        $queued = TranslationQueue::events()->pluck('id')->all();

        $this->assertNotContains($feedDraft->id, $queued);
        $this->assertContains($feedPublished->id, $queued);
        // Everything else is queued exactly as it was.
        $this->assertContains($handDraft->id, $queued);
        $this->assertContains($importedDraft->id, $queued);

        $feedDraft->forceFill(['is_draft' => false])->save();
        $this->assertContains($feedDraft->id, TranslationQueue::events()->pluck('id')->all());

        // Naming an event is the operator's way round every gate, and still is.
        $feedDraft->forceFill(['is_draft' => true])->save();
        $this->assertSame([$feedDraft->id], TranslationQueue::events($feedDraft->id)->pluck('id')->all());
    }

    /**
     * The queue takes never-translated rows first, and a first read is a hundred of those. An
     * event typed in that afternoon must not wait behind them.
     */
    public function test_events_somebody_typed_in_are_translated_before_a_feeds(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $fromFeed = [$this->event('Feed one', ['import_source' => Event::IMPORT_FEED]), $this->event('Feed two', ['import_source' => Event::IMPORT_FEED])];
        $byHand = $this->event('Typed in afterwards');
        $imported = $this->event('From the import page', ['import_source' => Event::IMPORT_ICS]);

        Artisan::call('app:translate', ['--dry-run' => true]);
        preg_match_all('/\[dry-run\] events #(\d+)/', Artisan::output(), $matches);

        $this->assertSame([$byHand->id, $imported->id, $fromFeed[0]->id, $fromFeed[1]->id], array_map('intval', $matches[1]));
    }

    /**
     * A first read is the schedule catching up with a calendar that already existed: new to us,
     * and to nobody who follows the schedule. What a feed adds afterwards is news.
     */
    public function test_followers_are_not_mailed_about_a_feeds_first_read_and_are_about_what_it_adds_later(): void
    {
        RoleSubscriber::create(['role_id' => $this->role->id, 'email' => 'fan@fans.test', 'name' => 'A Fan', 'token' => RoleSubscriber::newToken(), 'confirmed_at' => now()]);
        $this->role->forceFill(['last_announced_at' => now()->subDays(30)])->save();

        $this->event('From the first read', ['import_source' => Event::IMPORT_FEED, 'import_batch' => 'abcdef012345']);
        $this->event('Also from the first read', ['import_source' => Event::IMPORT_FEED, 'import_batch' => 'abcdef012345']);
        $this->artisan('app:send-event-announcements', ['--apply' => true])->assertSuccessful();
        $this->assertSame([], $this->announced());

        $this->role->forceFill(['last_announced_at' => now()->subDays(30)])->save();
        $this->event('Added by the feed last night', ['import_source' => Event::IMPORT_FEED]);
        // A run of the import page has a batch too, and is somebody's own doing.
        $this->event('Imported by hand', ['import_source' => Event::IMPORT_ICS, 'import_batch' => 'ffffffffffff']);
        $this->artisan('app:send-event-announcements', ['--apply' => true])->assertSuccessful();

        $this->assertEqualsCanonicalizing(['Added by the feed last night', 'Imported by hand'], $this->announced());
    }

    /** @return list<string> The names of the events in the announcement that was queued, if one was. */
    private function announced(): array
    {
        foreach (Queue::pushed(SendQueuedEmail::class) as $job) {
            $property = new \ReflectionProperty($job, 'mailable');
            $property->setAccessible(true);
            $mailable = $property->getValue($job);

            if ($mailable instanceof EventAnnouncement) {
                $events = (new \ReflectionProperty($mailable, 'events'));
                $events->setAccessible(true);

                return collect($events->getValue($mailable))->pluck('name')->all();
            }
        }

        return [];
    }
}
