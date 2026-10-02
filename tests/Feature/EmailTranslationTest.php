<?php

namespace Tests\Feature;

use App\Mail\ClaimRole;
use App\Mail\SupportMessageNotification;
use App\Notifications\NewFanContentNotification;
use App\Notifications\NewPollOptionsNotification;
use App\Notifications\NewRequestsNotification;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Mails that used to carry hardcoded English: the pending requests / poll options / fan content
 * counts, the support chat mails, the verify-email mail and Laravel's own button hint, and the
 * claim invitation's date. Each is rendered in another language and must carry that language's
 * text and none of the English it used to print.
 */
class EmailTranslationTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function inLocale(string $locale, callable $render): mixed
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            return $render();
        } finally {
            app()->setLocale($previous);
        }
    }

    public function test_the_owner_count_mails_are_translated(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbour']);
        $event = $this->createEvent($role, ['name' => 'Late Set']);

        $mails = [
            'requests' => fn () => (new NewRequestsNotification($role, 3))->toMail($owner)->render(),
            'poll options' => fn () => (new NewPollOptionsNotification($role, 2))->toMail($owner)->render(),
            'fan content' => fn () => (new NewFanContentNotification($event, 4, $role->subdomain))->toMail($owner)->render(),
        ];

        foreach ($mails as $label => $render) {
            $html = $this->inLocale('de', $render);

            $this->assertStringNotContainsString('pending', $html, "$label: English left in the German mail");
            $this->assertStringNotContainsString(' for Harbour', $html, $label);
            $this->assertStringNotContainsString(' for Late Set', $html, $label);
        }

        $this->assertStringContainsString('ausstehende Anfragen für Harbour', $this->inLocale('de', $mails['requests']));
        $this->assertStringContainsString('für Late Set', $this->inLocale('de', $mails['fan content']));
    }

    public function test_the_support_mails_are_translated(): void
    {
        $reply = new SupportMessageNotification(['Hallo'], 'Support', true, 'https://example.test/dashboard');
        $reply->locale('fr');

        $this->assertSame('Nouvelle réponse du support '.config('app.name'), $this->inLocale('fr', fn () => $reply->envelope()->subject));

        $html = $reply->render();
        $this->assertStringContainsString(e(__('messages.support_log_in_to_reply', [], 'fr')), $html);
        $this->assertStringContainsString(e(__('messages.support_or_reply', [], 'fr')), $html);
        $this->assertStringNotContainsString('Log in to reply', $html);
        $this->assertStringNotContainsString('Or just reply to this email.', $html);

        $visitor = (new SupportMessageNotification(['Hi'], 'Marie', false, 'https://example.test/admin', true))->locale('he');
        $html = $visitor->render();
        $this->assertStringContainsString(e(__('messages.support_view_conversation', [], 'he')), $html);
        $this->assertStringNotContainsString('a website visitor', $html);
    }

    public function test_verify_email_and_the_button_hint_are_translated(): void
    {
        $user = $this->createOwner();

        [$subject, $html] = $this->inLocale('es', function () use ($user) {
            $mail = (new VerifyEmail)->toMail($user);

            return [$mail->envelope()->subject, $mail->render()];
        });

        $this->assertSame('¡Bienvenido a '.config('app.name').'!', $subject);
        $this->assertStringContainsString(e(__('messages.verify_email_button', [], 'es')), $html);
        $this->assertStringNotContainsString('Please click the button below', $html);
        $this->assertStringNotContainsString('having trouble clicking', $html);
    }

    public function test_the_claim_invitation_date_is_in_the_mails_language(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($venue, ['name' => 'Double Bill', 'starts_at' => '2026-10-09 18:00:00']);
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Second On']);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $html = (new ClaimRole($event->fresh(), $act))->locale('fr')->render();

        $this->assertStringContainsString('octobre', $html);
        $this->assertStringNotContainsString('Oct 9th', $html);
    }
}
