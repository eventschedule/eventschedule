<?php

namespace Tests\Feature;

use App\Mail\CarpoolNotification;
use App\Mail\EventChanged;
use App\Mail\SubscriptionConfirmation;
use App\Models\CarpoolOffer;
use App\Models\CarpoolRequest;
use App\Models\RoleSubscriber;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Renders the guest event and audience mails that no other test renders: the change notice (and
 * the organizer's preview of it), the subscription confirmation, and every carpool notice. Each is
 * otherwise only asserted as "queued", which a template that throws would pass, and a queued mail
 * that throws fails silently in the worker.
 */
class EmailGuestEventRenderTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function assertWellFormedEmail(string $html, string $what): void
    {
        $this->assertSame(1, substr_count($html, '<h1'), "{$what}: exactly one heading");
        $this->assertStringNotContainsString('messages.', $html, "{$what}: a raw translation key leaked");
        // Gmail clips at 102KB and hides everything after the cut, unsubscribe link included.
        $this->assertLessThan(80 * 1024, strlen($html), "{$what}: too large for Gmail");
    }

    private function venueAndEvent(): array
    {
        $owner = $this->createOwner();
        $role = $this->createVenueWithAddress($owner, ['name' => 'The Blue Note']);
        $event = $this->createEvent($role, ['name' => 'Jazz Night', 'duration' => 3, 'creator_role_id' => $role->id]);

        return [$owner, $role, $event->fresh()];
    }

    private function changedMail($role, $event): EventChanged
    {
        $changes = [
            'date' => ['old_starts_at' => now()->addDays(5)->setTime(0, 0)->format('Y-m-d H:i:s'), 'old_duration' => 3, 'old_timezone' => 'America/New_York'],
            'location' => ['variant' => 'venue', 'old_venue' => 'Old Town Hall', 'new_venue' => 'The Blue Note'],
        ];

        return new EventChanged($event, $role, $changes, $event->getGuestUrl($role->subdomain, null, true), 'Moved to the main stage.', $event->getAppleCalendarUrl(), 'Sam');
    }

    public function test_the_change_notice_renders(): void
    {
        [, $role, $event] = $this->venueAndEvent();

        $html = $this->changedMail($role, $event)->render();

        $this->assertWellFormedEmail($html, 'event_changed');
        $this->assertStringContainsString('Jazz Night', $html);
        $this->assertStringContainsString(__('messages.event_changed_previously'), $html);
        $this->assertStringContainsString(__('messages.event_changed_now'), $html);
        $this->assertStringContainsString('Moved to the main stage.', $html);
        $this->assertStringContainsString(__('messages.update_your_calendar_note'), $html);
    }

    /**
     * In an RTL line the bidi algorithm pulled the time into the month's run and printed
     * "PM - 11:00 PM EDT 8:00". The date and the time range are isolated separately in the HTML;
     * the text part keeps the joined string.
     */
    public function test_the_change_notice_isolates_the_time_range_from_the_date(): void
    {
        [, $role, $event] = $this->venueAndEvent();
        app()->setLocale('he');

        $mail = $this->changedMail($role, $event);
        $html = $mail->render();

        $this->assertMatchesRegularExpression('~<bdi>[^<]+</bdi>, <bdi dir="ltr">\d{1,2}:\d{2}[^<]*</bdi>~u', $html);

        $content = $mail->content();
        $text = view($content->text, $content->with)->render();
        $this->assertStringContainsString($content->with['display']['date']['new'], $text);
    }

    public function test_the_organizers_preview_of_the_change_notice_renders(): void
    {
        [$owner, $role, $event] = $this->venueAndEvent();

        $response = $this->actingAs($owner)->post('/'.$role->subdomain.'/notify-preview/'.UrlUtils::encodeId($event->id), [
            'notify_message' => 'Moved one day later.',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '20:00',
            'duration' => 3,
        ]);

        $response->assertOk();
        $this->assertWellFormedEmail($response->getContent(), 'notify preview');
        $this->assertStringContainsString(__('messages.event_changed_now'), $response->getContent());
    }

    public function test_the_subscription_confirmation_renders_with_its_plain_url(): void
    {
        [, $role] = $this->venueAndEvent();
        $subscriber = RoleSubscriber::create(['role_id' => $role->id, 'email' => 'fan@example.com', 'token' => Str::random(40), 'confirm_token' => Str::random(40)]);
        $confirmUrl = url('/sub/c/'.$subscriber->confirm_token);

        $html = (new SubscriptionConfirmation($role, $subscriber, $confirmUrl, url('/sub/u/'.$subscriber->token)))->render();

        $this->assertWellFormedEmail($html, 'subscription_confirmation');
        $this->assertStringContainsString('The Blue Note', $html);
        // The button and the written-out address, for a client that drops the button.
        $this->assertSame(2, substr_count($html, 'href="'.e($confirmUrl).'"'));
    }

    public function test_every_carpool_notice_renders(): void
    {
        [, $role, $event] = $this->venueAndEvent();
        $driver = $this->createOwner();
        $rider = $this->createOwner();
        $offer = CarpoolOffer::create(['event_id' => $event->id, 'user_id' => $driver->id, 'role_id' => $role->id, 'event_date' => now()->addDays(7)->format('Y-m-d'),
            'direction' => 'to_event', 'city' => 'Brooklyn', 'departure_time' => '18:30', 'meeting_point' => 'Atlantic Ave', 'total_spots' => 3, 'status' => 'active']);
        $request = CarpoolRequest::create(['carpool_offer_id' => $offer->id, 'user_id' => $rider->id, 'message' => 'Near the station?', 'status' => 'approved']);

        foreach (['carpool_ride_requested', 'carpool_request_approved', 'carpool_request_declined', 'carpool_offer_cancelled', 'carpool_request_cancelled', 'carpool_reminder'] as $type) {
            $recipient = $type === 'carpool_ride_requested' ? $driver : $rider;
            $html = (new CarpoolNotification($type, $event, $offer->fresh(), $request->fresh(), $role, $recipient))->render();

            $this->assertWellFormedEmail($html, $type);
            $this->assertStringContainsString('Brooklyn', $html, $type);
        }
    }
}
