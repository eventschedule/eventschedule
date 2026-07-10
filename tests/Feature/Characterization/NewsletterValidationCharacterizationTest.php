<?php

namespace Tests\Feature\Characterization;

use App\Models\Newsletter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Pins NewsletterController's CURRENT validation-failure semantics ahead of
 * the P17 FormRequest wave (REFACTOR_PLAN.md rule 5(a)). The ordering pins
 * matter most: statements that run BEFORE the inline validate() are semantic
 * barriers - a FormRequest would validate first and flip these responses,
 * so those sites must stay inline (or be pinned green after conversion).
 */
class NewsletterValidationCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_store_validation_failure_pins_error_keys(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $response = $this->actingAs($owner)->post(route('newsletter.store', [
            'role_id' => UrlUtils::encodeId($role->id),
        ]), [
            'subject' => '',
            'template' => 'not-a-template',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['subject', 'template']);
        $this->assertSame(0, Newsletter::count());
    }

    public function test_store_missing_role_404s_before_validation(): void
    {
        // ORDERING PIN: getRole() aborts 404 before validate() runs, so an
        // invalid payload with no role_id is a 404, never a 422/redirect-
        // with-errors. Converting store() to a FormRequest would flip this
        // to validation-first - rule 5(a) gate 1 forbids that.
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        $this->actingAs($owner)->post(route('newsletter.store'), [
            'subject' => '',
            'template' => 'not-a-template',
        ])->assertNotFound();
    }

    public function test_update_already_sent_guard_runs_before_validation(): void
    {
        // ORDERING PIN: the sent-status guard precedes validate(), so an
        // invalid payload against a sent newsletter flashes the business
        // error and never reports validation errors.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $newsletter = Newsletter::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'subject' => 'Sent Newsletter',
            'status' => 'sent',
            'template' => 'modern',
        ]);

        $response = $this->actingAs($owner)->put(route('newsletter.update', [
            'hash' => UrlUtils::encodeId($newsletter->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]), [
            'subject' => '',
            'template' => 'not-a-template',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('messages.newsletter_already_sent'));
        $response->assertSessionMissing('errors');
    }

    public function test_update_validation_failure_pins_error_keys(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $newsletter = Newsletter::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'subject' => 'Draft Newsletter',
            'status' => 'draft',
            'template' => 'modern',
        ]);

        $this->actingAs($owner)->put(route('newsletter.update', [
            'hash' => UrlUtils::encodeId($newsletter->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]), [
            'subject' => '',
            'template' => 'modern',
        ])->assertRedirect()
            ->assertSessionHasErrors(['subject']);

        $this->assertSame('Draft Newsletter', $newsletter->fresh()->subject);
    }

    public function test_store_segment_validation_failure_pins_error_keys(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->post(route('newsletter.segment.store', [
            'role_id' => UrlUtils::encodeId($role->id),
        ]), [
            'name' => '',
            'type' => 'bogus-type',
        ])->assertRedirect()
            ->assertSessionHasErrors(['name', 'type']);
    }

    public function test_member_management_authorization_denies_follower_with_403(): void
    {
        // P18 pin: an existing authorize() site (manageMembers) - a follower
        // is denied with a plain 403, no custom redirect or JSON.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $follower = $this->createOwner()->fresh();
        $this->followRole($follower, $role);

        $this->actingAs($follower)->post(route('role.store_member', ['subdomain' => $role->subdomain]), [
            'email' => 'someone@gmail.com',
            'name' => 'Someone',
        ])->assertForbidden();
    }
}
