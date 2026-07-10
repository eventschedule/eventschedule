<?php

namespace Tests\Feature\Characterization;

use App\Models\BoostCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Pins each Event::boot()/Role::boot() closure behavior ahead of the P16
 * observers move (REFACTOR_PLAN.md rule 5(f)). Every closure body moves
 * verbatim into an observer method; these tests must stay green untouched.
 */
class ModelBootClosureCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_event_markdown_html_fields_derive_on_save(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'description' => 'Some **bold** text',
            'ticket_notes' => 'Bring *ID*',
            'payment_instructions' => 'Pay `cash`',
        ]);

        $this->assertStringContainsString('<strong>bold</strong>', $event->description_html);
        $this->assertStringContainsString('<em>ID</em>', $event->ticket_notes_html);
        $this->assertStringContainsString('<code>cash</code>', $event->payment_instructions_html);
    }

    public function test_event_starts_at_change_rekeys_sold_json_and_sale_event_date(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        // creator_role_id drives the venue-local date derivation
        // (saleEventDateFromStartsAt reads creatorRole->timezone).
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'starts_at' => '2026-08-16 00:00:00',
            'timezone' => 'America/New_York',
            'creator_role_id' => $role->id,
        ]);
        $ticket = $this->createTicket($event);
        // SaleTicket::created seeds the sold JSON: {'2026-08-15': 1}.
        $sale = $this->createSale($event, $role, ['event_date' => '2026-08-15'], $ticket);
        $this->assertSame(
            ['2026-08-15' => 1],
            json_decode($ticket->fresh()->getRawOriginal('sold'), true)
        );

        // Move the occurrence forward five days (new venue-local date 2026-08-20).
        $event->refresh();
        $event->starts_at = '2026-08-21 00:00:00';
        $event->save();

        // sold JSON re-keyed to the NEW venue-local date, quantity preserved.
        $this->assertSame(
            ['2026-08-20' => 1],
            json_decode($ticket->fresh()->getRawOriginal('sold'), true)
        );
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'event_date' => '2026-08-20',
        ]);
    }

    public function test_event_pass_sold_bucket_is_never_rekeyed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'starts_at' => '2026-08-16 00:00:00',
            'timezone' => 'America/New_York',
        ]);
        $pass = $this->createTicket($event, [
            'is_pass' => true,
            'sold' => json_encode(['pass' => 2]),
        ]);

        $event->starts_at = '2026-08-21 00:00:00';
        $event->save();

        // A pass's inventory lives in the 'pass' bucket; re-keying it to a
        // date would zero every sold pass.
        $this->assertSame(
            ['pass' => 2],
            json_decode($pass->fresh()->getRawOriginal('sold'), true)
        );
    }

    public function test_event_name_change_resets_translations(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['name' => 'Original']);
        $event->forceFill(['name_en' => 'Original EN', 'translation_attempts' => 2])->save();

        $event->refresh();
        $event->name = 'Renamed';
        $event->save();

        $fresh = $event->fresh();
        $this->assertNull($fresh->name_en);
        $this->assertSame(0, (int) $fresh->translation_attempts);
    }

    public function test_role_email_is_lowercased_and_normalized_columns_recompute(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'The Grand Hall',
            'city' => 'New York',
            'email' => 'MIXED.Case@Gmail.com',
        ]);

        $this->assertSame('mixed.case@gmail.com', $role->email);
        $this->assertNotNull($role->name_normalized);
        $this->assertNotNull($role->city_normalized);

        $before = $role->name_normalized;
        $role->name = 'The Grander Hall';
        $role->save();

        $this->assertNotSame($before, $role->fresh()->name_normalized);
    }

    public function test_role_email_change_resets_verification_and_sends_notification_on_hosted(): void
    {
        Notification::fake();
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->assertNotNull($role->email_verified_at);

        $role->email = 'changed@gmail.com';
        $role->save();

        $this->assertNull($role->fresh()->email_verified_at);
        Notification::assertSentTo($role, \App\Notifications\VerifyEmail::class);
    }

    public function test_role_name_change_resets_name_en_and_translation_attempts(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->forceFill(['name_en' => 'English Name', 'translation_attempts' => 3])->save();

        $role->refresh();
        $role->name = 'Renamed Schedule';
        $role->save();

        $fresh = $role->fresh();
        $this->assertNull($fresh->name_en);
        $this->assertSame(0, (int) $fresh->translation_attempts);
    }

    public function test_event_deletion_cancels_active_boost_campaigns(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        // Meta unconfigured in tests -> the MetaAdsServiceFake path: no API
        // call, but the campaign status is still cancelled.
        $campaign = BoostCampaign::create([
            'name' => 'Characterized Boost',
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'status' => 'active',
            'user_budget' => 20,
        ]);

        $event->delete();

        $this->assertDatabaseHas('boost_campaigns', [
            'id' => $campaign->id,
            'status' => 'cancelled',
            'meta_status' => null,
        ]);
    }

    public function test_event_deletion_removes_single_event_unregistered_roles(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        // An unclaimed auto-created venue whose ONLY event this is.
        $autoVenue = new \App\Models\Role;
        $autoVenue->name = 'Auto Venue';
        $autoVenue->subdomain = 'autovenue'.strtolower(\Illuminate\Support\Str::random(6));
        $autoVenue->type = 'venue';
        $autoVenue->save();
        $event->roles()->attach($autoVenue->id, ['is_accepted' => true]);

        $event->delete();

        // The orphaned unregistered venue is HARD deleted with its only event
        // (delete(), not the is_deleted flag); the claimed schedule survives.
        $this->assertDatabaseMissing('roles', ['id' => $autoVenue->id]);
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'is_deleted' => 0]);
    }
}
