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

    /**
     * ?id[]=1 reaches decodeId() as an array, and Sqids::decode() is typed string: an uncaught
     * TypeError, so a 500 (Sentry EVENTSCHEDULE-PHP-4G, on a schedule's guest page). The helper
     * has some 340 call sites, many fed straight from the request, so it answers null itself
     * instead of each caller checking - the same root fix as is_valid_language_code() got in
     * ArrayLanguageParamTest.
     */
    public function test_the_helper_rejects_a_non_scalar_instead_of_throwing(): void
    {
        $this->assertNull(UrlUtils::decodeId(['1']));
        $this->assertNull(UrlUtils::decodeId([]));
        $this->assertNull(UrlUtils::decodeId(new \stdClass));
        $this->assertNull(UrlUtils::decodeId(null));
        $this->assertNull(UrlUtils::decodeId(''));
        $this->assertNull(UrlUtils::decodeId('not-a-real-hash'));

        // The other half: the guard must not cost a real id, in either of the two forms.
        $this->assertSame(4242, UrlUtils::decodeId(UrlUtils::encodeId(4242)));
        $this->assertEquals(4242, UrlUtils::decodeId(base64_encode((string) (4242 + 389278))));
    }

    public function test_decode_id_or_fail_404s_on_a_non_scalar(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        UrlUtils::decodeIdOrFail(['1']);
    }
}
