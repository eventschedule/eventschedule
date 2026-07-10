<?php

namespace Tests\Feature\Characterization;

use App\Models\Newsletter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * 404-parity pins for the UrlUtils::decodeId preamble ahead of the P5
 * finder-trait extraction (REFACTOR_PLAN.md rule 5(e)): decodeId(garbage)
 * returns null and findOrFail(null) already 404s - the trait must preserve
 * exactly this. Two representative routes plus the venue_id body-param site.
 */
class EncodedIdRoutingCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_event_edit_hash_parity_triple(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        // Valid hash -> 200.
        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk();

        // Garbage hash -> decodeId() null -> findOrFail(null) -> 404.
        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => 'not-a-real-hash']))
            ->assertNotFound();

        // Well-formed hash of a nonexistent id -> 404.
        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId(999999)]))
            ->assertNotFound();
    }

    public function test_newsletter_edit_hash_parity_triple(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $newsletter = Newsletter::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'subject' => 'Characterized Newsletter',
            'status' => 'draft',
            'template' => 'modern',
        ]);
        $roleParam = ['role_id' => UrlUtils::encodeId($role->id)];

        $this->actingAs($owner)
            ->get(route('newsletter.edit', ['hash' => UrlUtils::encodeId($newsletter->id)] + $roleParam))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('newsletter.edit', ['hash' => 'not-a-real-hash'] + $roleParam))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('newsletter.edit', ['hash' => UrlUtils::encodeId(999999)] + $roleParam))
            ->assertNotFound();
    }

    public function test_invalid_venue_id_in_event_store_payload_404s(): void
    {
        // Body-param decode site: saveEvent's venue_id preamble is
        // Role::where('is_deleted', false)->findOrFail(decodeId(...)), so an
        // invalid hash 404s the whole store request (it does NOT fall through
        // to the no-venue path). P5 must leave this chained-builder site
        // in place (rule 5(e)), preserving exactly this response.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_id' => 'not-a-real-hash',
        ])->assertNotFound();

        $this->assertSame(0, \App\Models\Event::count());
    }
}
