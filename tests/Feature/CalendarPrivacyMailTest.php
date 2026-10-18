<?php

namespace Tests\Feature;

use App\Mail\CalendarPrivacyMail;
use App\Models\Role;
use App\Models\User;
use App\Services\CalendarArrival;
use App\Utils\CalendarPrivacy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The operator telling an owner that the schedule's page looks like a private calendar. It is a
 * mail about a leak, so it names what was found by kind and never quotes it, says what was done
 * only when it was, and links where signing in does not lose the way.
 */
class CalendarPrivacyMailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    /** @return array{0: User, 1: Role} */
    private function schedule(): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['name' => 'Sam Owner', 'language_code' => 'es'])->save();

        return [$owner, $this->createRole($owner, 'talent', ['name' => 'Sample Schedule'])];
    }

    public function test_it_is_in_the_owners_language_and_names_the_schedule_as_a_schedule(): void
    {
        [$owner, $role] = $this->schedule();
        $mail = new CalendarPrivacyMail($role, $owner, [CalendarPrivacy::ACCESS_CODES], 0, false, CalendarArrival::GOOGLE);

        App::setLocale('es');
        $subject = $mail->envelope()->subject;
        App::setLocale('en');

        $this->assertSame(trans('messages.calendar_privacy_mail_subject', ['name' => 'Sample Schedule'], 'es'), $subject);
        $this->assertStringContainsString('"Sample Schedule"', $subject);
        $this->assertStringContainsString(e(trans('messages.calendar_privacy_mail_heading', [], 'es')), $mail->locale('es')->render());
    }

    public function test_it_names_only_what_was_found_and_never_the_codes_themselves(): void
    {
        [$owner, $role] = $this->schedule();
        $this->createEvent($role, ['name' => 'Ops call', 'description' => 'Code: 583920174#', 'creator_role_id' => $role->id]);

        $html = (new CalendarPrivacyMail($role, $owner, [CalendarPrivacy::ACCESS_CODES], 0, false, CalendarArrival::GOOGLE))->render();

        $this->assertStringContainsString(e(__('messages.calendar_privacy_mail_kind_access_codes')), $html);
        $this->assertStringNotContainsString(e(__('messages.calendar_privacy_mail_kind_email_thread')), $html);
        $this->assertStringNotContainsString(e(__('messages.calendar_privacy_mail_kind_link_password')), $html);
        $this->assertStringNotContainsString('583920174', $html);
        $this->assertStringNotContainsString('Ops call', $html);

        // Nothing was done yet, so it does not say anything was.
        $this->assertStringNotContainsString(e(__('messages.calendar_privacy_mail_done_title')), $html);
    }

    public function test_it_says_what_was_done_and_links_where_signing_in_keeps_the_way(): void
    {
        [$owner, $role] = $this->schedule();

        $mail = new CalendarPrivacyMail($role, $owner, [CalendarPrivacy::EMAIL_THREAD], 3, true, CalendarArrival::MICROSOFT);
        $html = $mail->render();
        $text = view('emails.calendar_privacy_text', $mail->content()->with)->render();

        $this->assertStringContainsString(e(__('messages.calendar_privacy_mail_done_title')), $html);
        $this->assertStringContainsString(e(trans_choice('messages.calendar_privacy_mail_done_hidden', 3, ['count' => 3])), $html);
        $this->assertStringContainsString(e(__('messages.calendar_privacy_mail_done_paused')), $html);

        // A query, not a fragment: a fragment is lost through the sign-in form.
        $this->assertStringContainsString(e(route('role.edit', ['subdomain' => $role->subdomain]).'?integration_tab=outlook'), $html);
        $this->assertStringContainsString(e(route('role.calendar_review', ['subdomain' => $role->subdomain])), $html);
        $this->assertStringContainsString(e($role->getGuestUrl(true)), $html);
        $this->assertStringContainsString('?integration_tab=outlook', $text);
        $this->assertStringContainsString(route('role.calendar_review', ['subdomain' => $role->subdomain]), $text);

        // Replies reach the operator, not the platform's no-reply.
        $this->assertSame(config('app.support_email'), $mail->envelope()->replyTo[0]->address);
    }
}
