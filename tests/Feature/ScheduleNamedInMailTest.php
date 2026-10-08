<?php

namespace Tests\Feature;

use App\Notifications\AddedMemberNotification;
use App\Notifications\DeletedRoleNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Mail to an owner or a member says "schedule" beside the schedule's name.
 *
 * A schedule is often named after a person or a place, and its type (talent, venue, curator) is a
 * word of the app's own. "Talent has been deleted. The talent Jane Doe has been deleted by Sam
 * Doe." read as though a person had been, and "You are now the owner of Jane Doe" was no better.
 * Guest mail is left alone on purpose: to a ticket buyer the name is the organizer.
 */
class ScheduleNamedInMailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Every string of an owner's or a member's mail that carries a schedule's name. */
    private const NAMED = [
        'role_has_been_deleted', 'role_has_been_deleted_details',
        'added_to_team', 'added_to_team_detail', 'added_to_team_detail_viewer', 'sms_member_invite',
        'schedule_transfer_invite_subject', 'schedule_transfer_invite_intro',
        'schedule_transfer_declined_subject', 'schedule_transfer_declined_intro',
        'schedule_transfer_received_subject', 'schedule_transfer_received_intro',
        'schedule_transfer_sent_subject', 'schedule_transfer_sent_intro',
        'email_settings_failed_email_subject',
        'gift_card_sale_notification_subject', 'gift_card_sale_notification_intro',
        'ticket_trial_ending_subject', 'ticket_trial_ending_body',
        'activation_nudge_subject_no_event', 'activation_nudge_body_no_event',
        'activation_nudge_subject_no_ticket_type', 'activation_nudge_body_no_ticket_type',
        'activation_nudge_subject_no_ticket_type_free', 'activation_nudge_body_no_ticket_type_free',
        'activation_nudge_body_no_ticket_type_free_no_trial',
        'activation_nudge_subject_no_gateway', 'activation_nudge_body_no_gateway',
        'activation_nudge_body_first_sale',
        'activation_nudge_subject_idle_30', 'activation_nudge_body_idle_30',
        'activation_nudge_subject_idle_60', 'activation_nudge_body_idle_60',
        'owner_digest_subject_one', 'onboarding_nudge_subject_1_named',
        'notification_email_confirm_subject', 'notification_email_confirm_heading',
        'notification_email_confirm_intro', 'notification_email_footer',
        'new_requests_notification_subject', 'new_requests_caption', 'new_requests_line',
        'new_poll_options_notification_subject', 'new_poll_options_caption', 'new_poll_options_line',
    ];

    public function test_a_deleted_schedule_is_named_as_a_schedule_and_not_by_its_type(): void
    {
        $owner = $this->createOwner();
        $owner->name = 'Sam Doe';
        $role = $this->createRole($owner, 'talent', ['name' => 'Jane Doe']);

        $mail = (new DeletedRoleNotification($role, $owner))->toMail($owner);

        $this->assertSame('The "Jane Doe" schedule has been deleted', $mail->subject);
        $this->assertSame(['The "Jane Doe" schedule has been deleted by Sam Doe.'], $mail->introLines);
        $this->assertStringNotContainsStringIgnoringCase('talent', $mail->subject.' '.$mail->introLines[0]);
    }

    public function test_a_viewer_is_not_told_they_were_added_as_an_admin(): void
    {
        $owner = $this->createOwner();
        $owner->name = 'Sam Doe';
        $role = $this->createRole($owner, 'venue', ['name' => 'Blue Room']);

        $lines = [];
        foreach (['admin', 'viewer'] as $level) {
            $member = $this->createOwner();
            $member->roles()->attach($role->id, ['level' => $level, 'created_at' => now()]);

            $mail = (new AddedMemberNotification($role, $member, $owner))->toMail($member);

            $this->assertSame('You\'ve been added to the "Blue Room" schedule', $mail->subject);
            $lines[$level] = $mail->introLines[0];
        }

        $this->assertSame('You\'ve been added to the "Blue Room" schedule as an admin by Sam Doe.', $lines['admin']);
        $this->assertSame('You\'ve been added to the "Blue Room" schedule as a viewer by Sam Doe.', $lines['viewer']);
    }

    /**
     * The name is set off in quotes in all twelve languages, which is what tells it from the words
     * around it. Whether the word beside it means "schedule" is a translator's reading, not a
     * test's; in English it is checked outright.
     */
    public function test_the_name_is_set_off_as_a_name_in_every_language(): void
    {
        $bare = [];

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            foreach (self::NAMED as $key) {
                $line = trans('messages.'.$key, [], $lang);

                $this->assertNotSame('messages.'.$key, $line, "$key is missing in $lang");
                if (! preg_match('/["«] ?:(?:name|schedule) ?["»]/u', $line)) {
                    $bare[] = "$lang: $key";
                }
                if ($lang === 'en' && ! preg_match('/":(?:name|schedule)" schedule\b/', $line)) {
                    $bare[] = "en (no \"schedule\"): $key";
                }
            }
        }

        $this->assertSame([], $bare);
    }
}
