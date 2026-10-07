<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Small things the guest pages said wrongly, or kept when they should not have.
 */
class GuestSmallTruthsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The sign-up form saves what was typed in the tab's sessionStorage so a refused submit can
     * put it back. The saved state included the account password, and nothing removed it after a
     * successful sign-up, so it stayed readable by any script on the page for the life of the tab.
     * A password is the one field a person expects to retype.
     */
    public function test_the_sign_up_form_does_not_keep_the_password_in_the_tab(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'rsvp_enabled' => true,
            'creator_role_id' => $role->id,
        ]);

        $html = $this->get($event->fresh()->getGuestUrl($role->subdomain))->assertOk()->getContent();

        $save = strpos($html, 'saveFormState() {');
        $restore = strpos($html, 'restoreFormState() {', $save);
        $this->assertNotFalse($save);
        $this->assertNotFalse($restore);

        $this->assertStringNotContainsString('password', substr($html, $save, $restore - $save), 'what the form writes to sessionStorage');
        $this->assertStringNotContainsString('state.password', substr($html, $restore, 1500), 'and what it reads back');
    }

    /**
     * The compact event card (every phone, and the list on every width from Part 5) printed the
     * words "Coupon code" with no code after them, and wrote "Free entry" from the language file
     * where the laptop card used the wording the owner chose.
     */
    public function test_the_compact_event_card_prints_the_coupon_and_the_owners_free_entry_wording(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'custom_labels' => ['free_entry' => ['value' => 'No cover charge']],
        ]);
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        $html = $this->get('/'.$role->subdomain.'?layout=list')->assertOk()->getContent();

        $card = file_get_contents(resource_path('views/role/partials/mobile-event-card.blade.php'));
        $this->assertStringContainsString('v-text="event.coupon_code"', $card, 'the code itself, bound as text');
        $this->assertStringContainsString("\$label('free_entry')", $card);
        $this->assertStringNotContainsString("__('messages.free_entry')", $card);

        // And as the page is served: the owner's wording reaches the compact card's template.
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'No cover charge'), 'the laptop card and the compact card both say it the owner\'s way');
    }
}
