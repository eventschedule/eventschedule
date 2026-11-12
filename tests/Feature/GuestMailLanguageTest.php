<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\TicketPurchase;
use App\Mail\TicketReminder;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\EmailService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A guest's mails follow the language they read the event in (sales.language_code).
 *
 * A Portuguese visitor on an English schedule translated into European Portuguese reads the event
 * page in pt-PT, and used to be mailed everything in English under the English name: nothing on the
 * sale said which language they had read, the checkout POST is not where SetUserLanguage reads
 * ?lang, and the reminder and feedback mails used the schedule's language. Now the event page's
 * forms post the language, the sale keeps it, and the confirmation, the reminder, the calendar
 * invite and their links follow it. A sale without one is mailed exactly as before.
 */
class GuestMailLanguageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A ?lang= in a link, plain, HTML-escaped or inside an encoded calendar link. */
    private const LANG_PARAM = '/(\?|&|&amp;)lang=|lang%3D/';

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        // Off hosted, a ticket mail goes out only on a real transport; Queue::fake() keeps it here.
        config(['mail.default' => 'smtp']);

        $this->role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Harbour Hall',
            'timezone' => 'UTC',
            'language_code' => 'en',
            'translation_language_code' => 'pt-PT',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function event(array $attrs = []): Event
    {
        return $this->createEvent($this->role, $attrs + [
            'name' => 'Summer Concert',
            'name_en' => 'Concerto de Verão',
            'creator_role_id' => $this->role->id,
        ]);
    }

    private function prop(object $object, string $name): mixed
    {
        return (new \ReflectionProperty($object, $name))->getValue($object);
    }

    /** @return SendQueuedEmail the one queued job carrying a $class mailable */
    private function queued(string $class): SendQueuedEmail
    {
        $jobs = collect(Queue::pushed(SendQueuedEmail::class))->filter(fn ($job) => $this->prop($job, 'mailable') instanceof $class)->values();
        $this->assertCount(1, $jobs, $class.' queued once');

        return $jobs->first();
    }

    /** The mail as the queue would render it: in the job's locale. */
    private function render(SendQueuedEmail $job): array
    {
        /** @var Mailable $mail */
        $mail = $this->prop($job, 'mailable');
        $original = app()->getLocale();
        try {
            app()->setLocale($this->prop($job, 'locale') ?: $original);

            return ['subject' => $mail->envelope()->subject, 'html' => $mail->render(), 'mail' => $mail];
        } finally {
            app()->setLocale($original);
        }
    }

    private function rsvp(Event $event, array $extra = [])
    {
        return $this->post(route('event.rsvp', ['subdomain' => $this->role->subdomain]), $extra + [
            'name' => 'Rita',
            'email' => 'rita'.random_int(1000, 9999).'@gmail.com',
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
        ]);
    }

    public function test_a_guest_who_read_the_translation_registers_and_is_mailed_in_it(): void
    {
        $event = $this->event(['rsvp_enabled' => true]);

        $this->rsvp($event, ['lang' => 'pt-PT'])->assertRedirect();

        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $this->assertSame('pt-PT', $sale->language_code);

        $job = $this->queued(TicketPurchase::class);
        $this->assertSame('pt-PT', $this->prop($job, 'locale'));

        $mail = $this->render($job);
        $this->assertStringContainsString('Concerto de Verão', $mail['subject']);
        $this->assertSame(__('messages.ticket_reservation_confirmation', [], 'pt-PT').' - Concerto de Verão', $mail['subject']);
        $this->assertStringContainsString(e(__('messages.registration_confirmation', [], 'pt-PT')), $mail['html']);
        $this->assertStringContainsString('Concerto de Verão', $mail['html']);
        $this->assertStringNotContainsString('Summer Concert', $mail['html']);
        // The ticket and the event page open in the language too.
        $this->assertStringContainsString('/ticket/view/'.UrlUtils::encodeId($event->id).'/'.$sale->secret.'?lang=pt-PT', $mail['html']);
        $this->assertStringContainsString(e('?lang=pt-PT'), $mail['html']);
    }

    public function test_a_ticket_buyer_who_read_the_translation_is_mailed_in_it(): void
    {
        $event = $this->event(['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0]);

        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Rita', 'email' => 'rita@gmail.com', 'lang' => 'pt-PT',
            'event_id' => UrlUtils::encodeId($event->id), 'event_date' => $event->saleEventDateFromStartsAt(),
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ])->assertRedirect();

        $this->assertSame('pt-PT', Sale::where('event_id', $event->id)->value('language_code'));
        $this->assertSame('pt-PT', $this->prop($this->queued(TicketPurchase::class), 'locale'));
    }

    public function test_a_paid_sale_is_confirmed_in_the_language_it_was_bought_in(): void
    {
        // What a payment webhook calls once the money lands, in a request with no language at all.
        $event = $this->event(['tickets_enabled' => true]);
        $sale = $this->createSale($event, $this->role, [
            'email' => 'rita@gmail.com', 'payment_amount' => 20, 'language_code' => 'pt-PT',
        ], $this->createTicket($event, ['price' => 20]));

        (new EmailService)->sendSaleConfirmationEmails($sale);

        $job = $this->queued(TicketPurchase::class);
        $this->assertSame('pt-PT', $this->prop($job, 'locale'));
        $mail = $this->render($job);
        $this->assertSame(__('messages.ticket_purchase_confirmation', [], 'pt-PT').' - Concerto de Verão', $mail['subject']);
    }

    public function test_the_calendar_invite_is_named_in_the_guests_language(): void
    {
        $event = $this->event();
        $sale = $this->createSale($event, $this->role, ['email' => 'rita@gmail.com', 'language_code' => 'pt-PT'], $this->createTicket($event));

        $mail = new TicketPurchase($sale, $event, $this->role);
        $invite = collect($mail->attachments())->first(fn (Attachment $a) => str_starts_with((string) $a->mime, 'text/calendar'));
        $this->assertNotNull($invite);
        $ics = str_replace("\r\n ", '', $invite->attachWith(fn () => '', fn ($data) => $data()));

        $this->assertStringContainsString("SUMMARY:Concerto de Verão\r\n", $ics);
        $this->assertStringContainsString('?lang=pt-PT', $ics, 'its ticket link opens in the language too');
    }

    public function test_an_english_guest_is_mailed_as_before(): void
    {
        $event = $this->event(['rsvp_enabled' => true]);

        $this->rsvp($event, ['lang' => 'en'])->assertRedirect();

        $sale = Sale::where('event_id', $event->id)->firstOrFail();
        $this->assertSame('en', $sale->language_code);

        $job = $this->queued(TicketPurchase::class);
        $this->assertSame('en', $this->prop($job, 'locale'));
        $mail = $this->render($job);
        $this->assertStringContainsString('Summer Concert', $mail['subject']);
        $this->assertStringNotContainsString('Concerto de Verão', $mail['html']);
        $this->assertDoesNotMatchRegularExpression(self::LANG_PARAM, $mail['html'], 'the schedule\'s own language needs no ?lang=');
    }

    public function test_a_language_the_schedule_does_not_offer_is_ignored(): void
    {
        $event = $this->event(['rsvp_enabled' => true]);

        $this->rsvp($event, ['lang' => 'de'])->assertRedirect();
        $this->rsvp($event, ['lang' => 'xx-evil'])->assertRedirect();

        $this->assertSame([null, null], Sale::where('event_id', $event->id)->orderBy('id')->pluck('language_code')->all());
    }

    public function test_a_sale_with_no_language_is_mailed_as_it_always_was(): void
    {
        $event = $this->event();
        $sale = $this->createSale($event, $this->role, ['email' => 'old@gmail.com'], $this->createTicket($event));
        $this->assertNull($sale->language_code);

        app()->setLocale('fr');
        try {
            (new EmailService)->sendTicketEmail($sale, $this->role);
        } finally {
            app()->setLocale('en');
        }

        $job = $this->queued(TicketPurchase::class);
        $this->assertSame('fr', $this->prop($job, 'locale'), 'the request\'s locale, as before');
        $mail = $this->render($job);
        $this->assertStringContainsString('Summer Concert', $mail['subject']);
        $this->assertDoesNotMatchRegularExpression(self::LANG_PARAM, $mail['html']);
    }

    public function test_a_reminder_goes_out_in_the_language_the_ticket_was_bought_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00', 'UTC'));
        $event = $this->event(['starts_at' => now()->addHours(20)->format('Y-m-d H:i:s')]);
        $sale = $this->createSale($event, $this->role, [
            'email' => 'rita-'.Str::random(6).'@guests.org', 'language_code' => 'pt-PT',
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
        ]);
        Sale::whereKey($sale->id)->update(['created_at' => now()->subDays(5)]);

        $this->artisan('app:send-ticket-reminders', ['--apply' => true])->assertSuccessful();

        $job = $this->queued(TicketReminder::class);
        $this->assertSame('pt-PT', $this->prop($job, 'locale'));
        $mail = $this->render($job);
        $this->assertStringContainsString('Concerto de Verão', $mail['subject']);
        $this->assertStringNotContainsString('Summer Concert', $mail['html']);
        $this->assertStringContainsString('?lang=pt-PT', $mail['html']);
    }

    public function test_the_event_page_forms_post_the_language_it_is_read_in(): void
    {
        $event = $this->event(['rsvp_enabled' => true]);
        $url = $event->getGuestUrl($this->role->subdomain);

        $this->get($url.(str_contains($url, '?') ? '&' : '?').'lang=pt-PT')
            ->assertOk()
            ->assertSee('name="lang" value="pt-PT"', false);
    }

    public function test_the_ticket_page_opens_in_the_language_the_ticket_was_bought_in(): void
    {
        $event = $this->event();
        $sale = $this->createSale($event, $this->role, ['language_code' => 'pt-PT'], $this->createTicket($event));

        $this->get(route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]))
            ->assertOk()
            ->assertSee('Concerto de Verão')
            ->assertSee('lang="pt-PT"', false);
    }
}
