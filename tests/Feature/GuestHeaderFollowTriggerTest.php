<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Follow / subscribe trigger on a schedule's public header.
 *
 * It used to be gated on `! $hasSubmitButton`, i.e. on the schedule NOT accepting event requests.
 * For a signed-out visitor that meant a schedule which accepts requests offered Submit INSTEAD of
 * Follow - and Follow is the control that mints a subscriber account.
 *
 * Nothing caught it, because every schedule created through the UI had accept_requests = false:
 * the create form's toggle read a null attribute, painted off, and posted its companion hidden 0
 * over the column default of TRUE. So the case where the two differ never occurred in practice,
 * and flipping that default would have silently removed Follow from every new schedule's page.
 *
 * These assertions are about the ANONYMOUS visitor, which is the only one whose behaviour changed
 * and the only one who can become a subscriber.
 */
class GuestHeaderFollowTriggerTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Count the Follow BUTTONS, not the string.
     *
     * A bare assertStringContainsString('data-follow-trigger') is useless here:
     * partials/follow-consent-modal.blade.php renders on every guest page and its JavaScript
     * carries `closest('[data-follow-trigger]')` as a literal, so the raw string is present
     * whether or not any button is. The first version of this test passed with the bug put back.
     */
    private function followButtons(string $html): int
    {
        return preg_match_all('/<button[^>]*data-follow-trigger/s', $html);
    }

    private function guestPage(bool $acceptRequests)
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => $acceptRequests,
        ]);

        // Signed out on purpose - that is the population this is about.
        return [$role, $this->get($role->getGuestUrl())];
    }

    public function test_a_schedule_that_accepts_requests_still_offers_follow(): void
    {
        [$role, $response] = $this->guestPage(true);

        $response->assertOk();

        $this->assertGreaterThan(0, $this->followButtons($response->getContent()),
            'a schedule accepting requests dropped its Follow button for signed-out visitors, '.
            'which is the control that turns a visitor into a subscriber');
    }

    public function test_it_offers_the_submit_button_too_rather_than_one_or_the_other(): void
    {
        [$role, $response] = $this->guestPage(true);

        $this->assertStringContainsString(
            route('role.request', ['subdomain' => $role->subdomain]),
            $response->getContent(),
            'the Submit/Request control should still be there; this is not a swap back'
        );
    }

    /** The case that always worked, kept so a fix that swings the other way cannot pass. */
    public function test_a_schedule_that_does_not_accept_requests_still_offers_follow(): void
    {
        [$role, $response] = $this->guestPage(false);

        $response->assertOk();
        $this->assertGreaterThan(0, $this->followButtons($response->getContent()));
    }
}
