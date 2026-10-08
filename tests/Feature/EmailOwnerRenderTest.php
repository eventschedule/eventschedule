<?php

namespace Tests\Feature;

use App\Mail\EmailSettingsFailedMail;
use App\Mail\FeedbackNotification;
use App\Mail\GiftCardSaleNotification;
use App\Mail\NotificationEmailConfirmation;
use App\Mail\ScheduleTransferDeclined;
use App\Mail\ScheduleTransferInvite;
use App\Models\EventFeedback;
use App\Models\GiftCard;
use App\Models\RoleTransfer;
use App\Notifications\NewFanContentNotification;
use App\Notifications\NewPollOptionsNotification;
use App\Notifications\NewRequestsNotification;
use App\Services\RequestNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The owner-facing mails no other test renders. A queued mail that throws while rendering fails
 * silently in the queue, so each one is rendered here, in English and in Hebrew, and held to the
 * shape every <x-email.layout> mail shares: one <h1>, no untranslated key, and small enough that
 * Gmail does not clip it (102KB) and hide the footer's unsubscribe link.
 */
class EmailOwnerRenderTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function assertRendersWell(callable $render, string $label): void
    {
        foreach (['en', 'he'] as $locale) {
            app()->setLocale($locale);
            $html = (string) $render();

            $this->assertSame(1, substr_count($html, '<h1'), "{$label} ({$locale}): exactly one <h1>");
            $this->assertStringNotContainsString('messages.', $html, "{$label} ({$locale}): untranslated key");
            $this->assertLessThan(80 * 1024, strlen($html), "{$label} ({$locale}): too large");
            $this->assertStringContainsString('lang="'.$locale.'"', $html, "{$label} ({$locale}): not built on the email layout");
        }
    }

    private function transfer($role, $owner): RoleTransfer
    {
        $transfer = new RoleTransfer;
        $transfer->role_id = $role->id;
        $transfer->from_user_id = $owner->id;
        $transfer->to_email = 'newowner@gmail.com';
        $transfer->save();

        return $transfer->fresh();
    }

    public function test_the_owner_mails_render(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Blue Note']);
        $event = $this->createEvent($role, ['name' => 'Jazz Night']);
        $sale = $this->createSale($event, $role, ['name' => 'Alex Attendee', 'email' => 'alex@example.com'], $this->createTicket($event));

        $card = GiftCard::create([
            'role_id' => $role->id,
            'code' => GiftCard::generateCode(),
            'secret' => Str::random(32),
            'amount' => 50,
            'remaining_amount' => 50,
            'currency_code' => 'USD',
            'status' => 'active',
            'payment_method' => 'stripe',
            'purchaser_name' => 'Gifter',
            'purchaser_email' => 'gifter@gmail.com',
            'recipient_name' => 'Wine Buyer',
            'recipient_email' => 'buyer@gmail.com',
        ]);

        $feedback = EventFeedback::create([
            'event_id' => $event->id,
            'sale_id' => $sale->id,
            'event_date' => $sale->event_date,
            'rating' => 4,
            'comment' => 'Best night out all year',
        ]);

        $mails = [
            'GiftCardSaleNotification' => fn () => (new GiftCardSaleNotification($card, $role, $owner))->render(),
            'FeedbackNotification' => fn () => (new FeedbackNotification($feedback, $sale, $event, $role, $owner))->render(),
            'NewPollOptionsNotification' => fn () => (new NewPollOptionsNotification($role, 2))->toMail($owner)->render(),
            'NewFanContentNotification' => fn () => (new NewFanContentNotification($event, 3, $role->subdomain))->toMail($owner)->render(),
            'EmailSettingsFailedMail' => fn () => (new EmailSettingsFailedMail($role, $owner, '535 Username and Password not accepted', now()))->render(),
            'NotificationEmailConfirmation' => fn () => (new NotificationEmailConfirmation($role, url('/ne/confirm/abc'), 'Hillel'))->render(),
            'ScheduleTransferInvite' => fn () => (new ScheduleTransferInvite($this->transfer($role, $owner)))->render(),
            'ScheduleTransferDeclined' => fn () => (new ScheduleTransferDeclined($this->transfer($role, $owner)))->render(),
        ];

        foreach ($mails as $label => $render) {
            $this->assertRendersWell($render, $label);
        }
    }

    /**
     * The request mail at its longest: one request, and as many as RequestNotifier gives a mail
     * (the ones it spells out, each with ten answers and a message at their limits, and the ones
     * it names in a line each), in text that is two bytes a character and full of marks that
     * grow when escaped. What does not fit is left for the next mail.
     */
    public function test_the_request_mail_renders_at_its_longest(): void
    {
        Notification::fake();
        $owner = $this->createOwner();
        $fields = [];
        foreach (range(0, 9) as $number) {
            $fields['new_'.$number] = ['name' => 'שאלה ארוכה מאוד מספר '.$number, 'type' => 'multiline_string', 'show_on_request' => true, 'index' => $number + 1];
        }
        $role = $this->createRole($owner, 'venue', ['name' => 'The Blue Note', 'accept_requests' => true, 'require_approval' => true, 'event_custom_fields' => $fields]);
        $long = fn (int $characters) => mb_substr(str_repeat('תשובה "ארוכה" & מאוד <כאן>. ', 400), 0, $characters);
        $total = RequestNotifier::LISTED + RequestNotifier::NAMED + 2;

        $requests = collect(range(1, $total))->map(function ($number) use ($role, $fields, $long) {
            $event = $this->createEvent($role, [
                'name' => $long(250).$number,
                'creator_role_id' => $role->id,
                'custom_field_values_role_id' => $role->id,
                'is_guest_submission' => true,
                'contact_name' => $long(250),
                'contact_email' => 'somebody.with.a.long.address'.$number.'@gmail.com',
                'contact_phone' => '+972 50 555 0100',
                'description' => $long(5000),
                // 700 characters each: past the 600 a mail prints, and what a TEXT column holds
                // of ten answers once json_encode has written each letter as six bytes.
                'custom_field_values' => array_fill_keys(array_keys($fields), $long(700)),
            ]);
            DB::table('event_role')->where('event_id', $event->id)->update(['is_accepted' => null]);

            return $event->fresh();
        });

        $this->assertRendersWell(fn () => (new NewRequestsNotification($role, $total, $requests->take(1), $total))->toMail($owner)->render(), 'one request');

        app(RequestNotifier::class)->announce($role);
        $listed = 0;
        $named = 0;
        Notification::assertSentTo($owner, NewRequestsNotification::class, function ($notification) use ($owner, &$listed, &$named) {
            $this->assertRendersWell(fn () => $notification->toMail($owner)->render(), 'as many as fit');
            $listed = count($notification->toMail($owner)->viewData['requests']);
            $named = count($notification->toMail($owner)->viewData['also']);

            return true;
        });
        $this->assertGreaterThan(1, $listed, 'the budget left room for one request only');
        $this->assertLessThanOrEqual(RequestNotifier::LISTED, $listed);
        $this->assertGreaterThan(10, $named, 'the requests spelled out left the lines after them no room');
        // Only what the mail named is marked as told.
        $this->assertSame($listed + $named, DB::table('event_role')->where('role_id', $role->id)->whereNotNull('request_notified_at')->count());
    }

    /**
     * Every owner notification names the schedule it is about. The fan-content mail is a
     * MailMessage, whose text() call re-runs view() with its own data array, so a schedule passed
     * to view() alone never reached the HTML part.
     */
    public function test_the_fan_content_mail_names_its_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Harbour Room']);
        $event = $this->createEvent($role, ['name' => 'Late Set']);

        $html = (string) (new NewFanContentNotification($event, 3, $role->subdomain))->toMail($owner)->render();

        $this->assertStringContainsString('The Harbour Room', $html);
    }

    public function test_the_settings_failure_greeting_is_not_doubly_punctuated(): void
    {
        // The greeting string carries its own comma ("Hi :name,"), and the view used to add another.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $mail = new EmailSettingsFailedMail($role, $owner, 'error', now());

        $this->assertStringNotContainsString($owner->name.',,', html_entity_decode($mail->render()));
        $content = $mail->content();
        $this->assertStringNotContainsString($owner->name.',,', view($content->text, $content->with)->render());
    }
}
