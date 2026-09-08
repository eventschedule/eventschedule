<?php

namespace Tests\Feature;

use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The invitation EventRepo::saveEvent() already mails to an unclaimed performer or venue.
 */
class ClaimInvitationMailTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function placeholder(string $type, string $name, string $email): Role
    {
        $role = new Role;
        $role->subdomain = strtolower(Str::random(12));
        $role->type = $type;
        $role->name = $name;
        $role->email = $email;
        $role->timezone = 'America/New_York';
        $role->plan_type = 'free';
        $role->save();

        return $role->fresh();
    }

    public function test_each_invited_performer_is_mailed_about_themselves(): void
    {
        // EventRepo dispatches one ClaimRole per invited act, but the mailable used to resolve its
        // own recipient as $event->role() - roles->first(isTalent) - so on a two-act bill the
        // second act was mailed about the first, handed the first's email address in the sign-up
        // link and the first's schedule behind the unsubscribe.
        $organizer = $this->createOwner();
        $venue = $this->createRole($organizer, 'venue');
        $event = $this->createEvent($venue, ['name' => 'Double Bill']);

        $actA = $this->placeholder('talent', 'First On', 'first@gmail.com');
        $actB = $this->placeholder('talent', 'Second On', 'second@gmail.com');
        $event->roles()->attach($actA->id, ['is_accepted' => true]);
        $event->roles()->attach($actB->id, ['is_accepted' => true]);

        $mail = new ClaimRole($event->fresh(), $actB);

        $this->assertStringContainsString('Second On', $mail->envelope()->subject);
        $this->assertStringNotContainsString('First On', $mail->envelope()->subject);

        $html = $mail->render();
        $this->assertStringContainsString(urlencode(base64_encode($actB->email)), $html);
        $this->assertStringNotContainsString(urlencode(base64_encode($actA->email)), $html);

        $unsubscribe = $mail->headers()->text['List-Unsubscribe'];
        $this->assertStringContainsString($actB->subdomain, $unsubscribe);
        $this->assertStringNotContainsString($actA->subdomain, $unsubscribe);
    }

    public function test_the_invitation_links_to_the_page_it_is_about(): void
    {
        // The whole loop: until the page existed the only call to action was a bare sign-up form,
        // which told the recipient nothing about what they were being offered.
        $organizer = $this->createOwner();
        $venue = $this->createRole($organizer, 'venue');
        $event = $this->createEvent($venue, ['name' => 'Double Bill']);

        $act = $this->placeholder('talent', 'Second On', 'second@gmail.com');
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $html = (new ClaimRole($event->fresh(), $act))->render();

        $this->assertNotSame('', $act->getClaimUrl());
        $this->assertStringContainsString($act->getClaimUrl(), $html);
    }

    public function test_a_venue_invitation_survives_an_event_with_no_performers(): void
    {
        // Event::role() is talent-only and returns null for a curator- or venue-created event with
        // no acts on it. Both subject builders dereferenced ->name straight off it, so the mail
        // died inside the queued job: nothing sent, nobody told.
        $organizer = $this->createOwner();
        $curator = $this->createRole($organizer, 'curator', ['name' => 'City Listings']);
        $venue = $this->placeholder('venue', 'The Old Hall', 'hall@gmail.com');

        $event = $this->createEvent($curator, ['name' => 'Quiet Night']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $this->assertNull($event->role(), 'the fixture must have no talent, or it proves nothing');
        $this->assertNotNull($event->venue, 'and it must have the venue this mail is addressed to');

        $mail = new ClaimVenue($event);

        $this->assertStringContainsString('The Old Hall', $mail->envelope()->subject);
        $this->assertStringContainsString(urlencode(base64_encode($venue->email)), $mail->render());
        $this->assertStringContainsString($venue->subdomain, $mail->headers()->text['List-Unsubscribe']);
    }
}
