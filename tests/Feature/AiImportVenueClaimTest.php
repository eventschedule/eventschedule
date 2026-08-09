<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The AI import's "I manage this venue, make me the owner" checkbox, driven through the real
 * event.import route.
 *
 * The reported bug: the checkbox was only honoured for a brand-new venue, so when the import
 * matched an existing venue instead - which is the common case once a venue exists - the tick
 * was silently dropped, the user was left a follower, and every event they imported sat
 * pending on a venue they believed was theirs.
 */
class AiImportVenueClaimTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function unclaimedVenue(array $attrs = []): Role
    {
        $venue = new Role;
        $venue->name = 'The Blue Note';
        $venue->subdomain = 'bluenote'.strtolower(Str::random(8));
        $venue->type = 'venue';
        $venue->city = 'Springfield';
        $venue->country_code = 'us';

        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }

        $venue->save();

        return $venue->fresh();
    }

    private function importPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Imported Event',
            'starts_at' => '2026-08-15 20:00:00',
            'duration' => 2,
            'schedule_type' => 'one_time',
        ], $overrides);
    }

    private function import($user, Role $role, array $overrides = [])
    {
        return $this->actingAs($user)->postJson(
            route('event.import', ['subdomain' => $role->subdomain]),
            $this->importPayload($overrides)
        );
    }

    private function pivot(Role $venue): ?object
    {
        return DB::table('event_role')
            ->where('event_id', Event::query()->orderByDesc('id')->firstOrFail()->id)
            ->where('role_id', $venue->id)
            ->first();
    }

    public function test_claim_on_a_dedup_matched_unclaimed_venue_makes_the_user_owner(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $existing = $this->unclaimedVenue();
        $rolesBefore = Role::count();

        // No venue_id: the normalized safety net matches the existing row instead. This is the
        // branch that used to drop the tick on the floor.
        $this->import($owner, $curator, [
            'venue_name' => 'The  BLUE Note',
            'venue_city' => 'Springfield',
            'venue_country_code' => 'us',
            'claim_venue_ownership' => true,
        ])->assertOk();

        $this->assertSame($rolesBefore, Role::count(), 'the dedup should reuse the existing venue');
        $this->assertSame($owner->id, $existing->fresh()->user_id);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $existing->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        $this->assertSame(1, (int) $this->pivot($existing)->is_accepted);
    }

    public function test_claim_promotes_an_existing_follower_row_instead_of_throwing(): void
    {
        // role_user carries unique(user_id, role_id), so the old bare attach() would have hit a
        // duplicate-key QueryException here rather than promoting the row.
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->unclaimedVenue();
        $this->followRole($owner, $venue);

        $this->import($owner, $curator, [
            'venue_id' => UrlUtils::encodeId($venue->id),
            'venue_name' => $venue->name,
            'venue_city' => $venue->city,
            'claim_venue_ownership' => true,
        ])->assertOk();

        $this->assertSame(1, DB::table('role_user')
            ->where('role_id', $venue->id)
            ->where('user_id', $owner->id)
            ->count());

        $this->assertDatabaseHas('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        $this->assertSame($owner->id, $venue->fresh()->user_id);
        $this->assertSame(1, (int) $this->pivot($venue)->is_accepted);
    }

    public function test_claim_survives_a_posted_venue_email(): void
    {
        // Ordering guard: the claim writes the claimer's verified contact with saveQuietly(),
        // so it has to run AFTER the parsed venue fields are persisted. The other way round,
        // writing venue_email would re-dirty email and the Role::updating hook would null the
        // verification the claim just copied in.
        config(['app.hosted' => true]);
        Notification::fake();

        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->unclaimedVenue();

        $this->import($owner, $curator, [
            'venue_id' => UrlUtils::encodeId($venue->id),
            'venue_name' => $venue->name,
            'venue_city' => $venue->city,
            'venue_email' => 'parsed-from-flyer@gmail.com',
            'claim_venue_ownership' => true,
        ])->assertOk();

        $venue->refresh();

        $this->assertSame($owner->email, $venue->email);
        $this->assertNotNull($venue->email_verified_at);
        $this->assertTrue($venue->isClaimed());
    }

    public function test_claim_never_takes_a_venue_that_already_has_an_owner(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);

        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', [
            'name' => 'Someone Elses Hall',
            'city' => 'Springfield',
            'accept_requests' => true,
            'require_approval' => true,
        ]);

        $this->import($owner, $curator, [
            'venue_id' => UrlUtils::encodeId($venue->id),
            'venue_name' => $venue->name,
            'venue_city' => $venue->city,
            'claim_venue_ownership' => true,
        ])->assertOk();

        $this->assertSame($venueOwner->id, $venue->fresh()->user_id);
        $this->assertDatabaseMissing('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        // Still a genuine request the venue owner has to approve.
        $this->assertNull($this->pivot($venue)->is_accepted);
    }

    /** Nothing was claimed: ownership, the owner pivot and the contact are all untouched. */
    private function assertVenueUnclaimed(Role $venue, ?int $userId = null): void
    {
        $fresh = $venue->fresh();

        $this->assertNull($fresh->user_id);
        $this->assertNull($fresh->email, 'the claimer\'s contact must not have been copied in');

        if ($userId) {
            $this->assertDatabaseMissing('role_user', [
                'role_id' => $venue->id,
                'user_id' => $userId,
                'level' => 'owner',
            ]);
        }
    }

    public function test_guest_import_cannot_claim_an_existing_venue(): void
    {
        // Signed IN, so $request->user() is set and the pre-existing $wantsClaim guard does not
        // fire - this reaches the existing-venue gate itself. guestImport() saves onto the
        // curator, which this user has no relationship with.
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, [
            'accept_requests' => true,
            'require_approval' => true,
            'require_account' => false,
        ]);
        $stranger = $this->createOwner();
        $venue = $this->unclaimedVenue();

        $this->actingAs($stranger)->postJson(
            route('event.guest_import.store', ['subdomain' => $curator->subdomain]),
            $this->importPayload([
                'venue_id' => UrlUtils::encodeId($venue->id),
                'venue_name' => $venue->name,
                'venue_city' => $venue->city,
                'claim_venue_ownership' => true,
            ])
        )->assertOk();

        $this->assertVenueUnclaimed($venue, $stranger->id);
    }

    /**
     * The hole the isEditor($currentRole) gate did not close. roles.require_account DEFAULTS to
     * true, so this is the ordinary public submission path: it saves onto the submitter's OWN
     * talent schedule, which createTalentSchedule() hands them owner on, making
     * "is an editor of $currentRole" unconditionally true. Only the caller-granted
     * $allowExistingVenueClaim flag stops it.
     */
    public function test_require_account_submission_cannot_claim_an_existing_venue_by_name(): void
    {
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_approval' => true,
            'require_account' => true,
        ]);
        $attacker = $this->createOwner();
        $target = $this->unclaimedVenue();

        $this->actingAs($attacker)->postJson(
            route('event.guest_import.store', ['subdomain' => $curator->subdomain]),
            $this->importPayload([
                // No venue_id: the normalized dedup finds the target by name alone.
                'venue_name' => 'The  BLUE Note',
                'venue_city' => 'Springfield',
                'claim_venue_ownership' => true,
            ])
        )->assertOk();

        $this->assertVenueUnclaimed($target, $attacker->id);
    }

    public function test_require_account_submission_cannot_claim_an_existing_venue_by_id(): void
    {
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_approval' => true,
            'require_account' => true,
        ]);
        $attacker = $this->createOwner();
        $target = $this->unclaimedVenue();

        $this->actingAs($attacker)->postJson(
            route('event.guest_import.store', ['subdomain' => $curator->subdomain]),
            $this->importPayload([
                'venue_id' => UrlUtils::encodeId($target->id),
                'venue_name' => $target->name,
                'venue_city' => $target->city,
                'claim_venue_ownership' => true,
            ])
        )->assertOk();

        $this->assertVenueUnclaimed($target, $attacker->id);
    }

    public function test_demo_mode_cannot_claim_a_venue(): void
    {
        // DemoAutoLogin signs any anonymous visitor into the shared demo account, so a claim
        // here would let the public rewrite a globally shared roles row that resetDemoData()
        // never touches. Covers a brand-new venue too, not just an existing one.
        $demoUser = $this->createOwner();
        $demoUser->forceFill(['email' => DemoService::DEMO_EMAIL])->saveQuietly();
        $curator = $this->createCurator($demoUser);
        $target = $this->unclaimedVenue();

        $this->actingAs($demoUser)->postJson(
            route('event.import', ['subdomain' => $curator->subdomain]),
            $this->importPayload([
                'venue_id' => UrlUtils::encodeId($target->id),
                'venue_name' => $target->name,
                'venue_city' => $target->city,
                'claim_venue_ownership' => true,
            ])
        )->assertOk();

        $this->assertVenueUnclaimed($target, $demoUser->id);
    }

    public function test_import_page_ships_the_claimable_flag_and_its_gate(): void
    {
        // The page collapses to the setup guide without an AI key configured.
        config(['services.google.gemini_key' => 'test-key']);

        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $unclaimed = $this->unclaimedVenue();
        $this->followRole($owner, $unclaimed);

        $owned = $this->createRole($owner, 'venue', ['name' => 'My Own Hall']);

        $response = $this->actingAs($owner)->get(
            route('event.show_import_ai', ['subdomain' => $curator->subdomain])
        )->assertOk();

        $html = $response->getContent();

        // The checkbox is now driven by canClaimVenue(), outside the create-new fields block.
        $this->assertStringContainsString('v-if="canClaimVenue(idx)"', $html);
        $this->assertStringContainsString('claim_venue_ownership: this.canClaimVenue(idx)', $html);

        // Every dropdown venue carries the flag, and it tracks ownership.
        $venues = collect(json_decode(
            (string) preg_replace('/^.*?venues:\s*(\[.*?\]),\n.*$/s', '$1', $html),
            true
        ) ?: []);

        $this->assertTrue($venues->isNotEmpty(), 'the venue payload should be rendered');
        $this->assertTrue($venues->firstWhere('name', 'The Blue Note')['is_claimable']);
        $this->assertFalse($venues->firstWhere('name', 'My Own Hall')['is_claimable']);
    }

    public function test_import_response_reports_a_just_claimed_venue_as_no_longer_claimable(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->unclaimedVenue();

        $response = $this->import($owner, $curator, [
            'venue_id' => UrlUtils::encodeId($venue->id),
            'venue_name' => $venue->name,
            'venue_city' => $venue->city,
            'claim_venue_ownership' => true,
        ])->assertOk();

        $this->assertFalse($response->json('venue.is_claimable'));
        $this->assertTrue($response->json('venue.is_member'));
    }

    public function test_brand_new_venue_claim_still_works(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $owner->forceFill(['default_role_id' => null])->save();

        $this->import($owner, $curator, [
            'venue_name' => 'Brand New Hall',
            'venue_city' => 'Shelbyville',
            'claim_venue_ownership' => true,
        ])->assertOk();

        $venue = Role::where('type', 'venue')->where('name', 'Brand New Hall')->firstOrFail();

        $this->assertSame($owner->id, $venue->user_id);
        $this->assertSame($venue->id, $owner->fresh()->default_role_id);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);
        $this->assertDatabaseMissing('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'follower',
        ]);
    }
}
