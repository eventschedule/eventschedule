<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Services\EventChangeNotifier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The cancellation email shows the date that is off. For a recurring event that is the occurrence
 * the buyer holds (the sale's event_date), never the series' first date: the email redesign added
 * a date block to this mail, and without the occurrence it named a night months in the past.
 */
class EventCancelledOccurrenceTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    public function test_a_recurring_events_cancellation_names_the_cancelled_occurrence(): void
    {
        Queue::fake();

        $role = $this->createRole($this->createOwner());
        // Hosted schedule mail goes out only on the schedule's own SMTP (EmailService::canSendScheduleMail).
        $role->email_settings = [
            'host' => 'smtp.test.dev', 'port' => 587, 'encryption' => 'tls',
            'username' => 'mailer', 'password' => 'secret',
            'from_address' => 'events@harbour.dev', 'from_name' => 'Harbour',
        ];
        $role->save();
        $first = now()->subWeeks(3)->setTime(16, 0);
        $event = $this->createRecurringEvent($role, ['starts_at' => $first->format('Y-m-d H:i:s')]);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $occurrence = now()->addDays(10)->toDateString();
        $this->createSale($event, $role, ['event_date' => $occurrence, 'email' => 'buyer@gmail.com', 'payment_amount' => 10], $ticket);

        EventChangeNotifier::notifyCancellation($event->fresh(), $role);

        $job = Queue::pushed(SendQueuedEmail::class)->first();
        $this->assertNotNull($job, 'No cancellation email was queued.');

        $mailable = (fn () => $this->mailable)->call($job);
        $html = $mailable->render();

        $tz = $role->timezone;
        $this->assertStringContainsString(e(Carbon::parse($occurrence, $tz)->translatedFormat('l, F j, Y')), $html);
        $this->assertStringNotContainsString(e($first->copy()->setTimezone($tz)->translatedFormat('l, F j, Y')), $html);
    }
}
