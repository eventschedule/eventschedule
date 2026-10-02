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
use Illuminate\Foundation\Testing\RefreshDatabase;
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
