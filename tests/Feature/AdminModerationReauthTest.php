<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The two platform-moderation actions that live OUTSIDE the admin route group.
 *
 * marketing.discovery.toggle and marketing.federation.block are served from the base domain, so
 * EnsureUserIsAdmin never runs on them - they were gated by isAdmin() alone, which knows nothing
 * about the password re-confirmation window. toggleFederatedBlock is a verbatim duplicate of the
 * in-gate AdminFederationController::blockEvent(), so before this they were the one way to
 * moderate the platform without a current confirmation, and the comment above the handler named
 * the gate as the reason they were placed there.
 *
 * They ask AdminReauthUtils directly now. This reaches them because
 * AppServiceProvider::defaultHostedSessionDomain() scopes the session cookie to the base domain.
 */
class AdminModerationReauthTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function discoverableEvent(): Event
    {
        $admin = $this->createOwner(true);
        $role = $this->createRole($admin, 'talent');

        return $this->createEvent($role, ['name' => 'Moderated Session']);
    }

    public function test_a_current_confirmation_may_hide_an_event_from_discovery(): void
    {
        $event = $this->discoverableEvent();
        $admin = $this->createOwner(true);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->post(route('marketing.discovery.toggle', $event->hashedId()))
            ->assertRedirect();

        $this->assertTrue((bool) $event->fresh()->is_hidden_from_discovery);
    }

    public function test_a_lapsed_window_cannot_hide_an_event_from_discovery(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);

        $event = $this->discoverableEvent();
        $admin = $this->createOwner(true);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp - 61])
            ->actingAs($admin)
            ->post(route('marketing.discovery.toggle', $event->hashedId()))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse((bool) $event->fresh()->is_hidden_from_discovery,
            'A lapsed re-auth window must not be able to moderate the discovery wall.');
    }

    /**
     * An admin who never confirmed at all is the commonest shape of this: signed in via the
     * remember-me cookie, which is checked by default and lasts 400 days.
     */
    public function test_an_admin_who_never_confirmed_cannot_hide_an_event(): void
    {
        $event = $this->discoverableEvent();
        $admin = $this->createOwner(true);

        $this->actingAs($admin)
            ->post(route('marketing.discovery.toggle', $event->hashedId()))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse((bool) $event->fresh()->is_hidden_from_discovery);
    }

    /**
     * The gate still comes first: a non-admin is refused outright rather than being told to
     * confirm a password they could never use here.
     */
    public function test_a_non_admin_is_still_forbidden_outright(): void
    {
        $event = $this->discoverableEvent();
        $plain = $this->createOwner();

        $this->actingAs($plain)
            ->post(route('marketing.discovery.toggle', $event->hashedId()))
            ->assertForbidden();

        $this->assertFalse((bool) $event->fresh()->is_hidden_from_discovery);
    }

    /**
     * The ceiling applies here too - the whole reason it exists is an admin surface that keeps
     * itself alive indefinitely.
     */
    public function test_the_absolute_ceiling_also_blocks_moderation(): void
    {
        config([
            'auth.admin_reauth_timeout' => 600,
            'auth.admin_reauth_max_lifetime' => 120,
        ]);

        $event = $this->discoverableEvent();
        $admin = $this->createOwner(true);
        $now = now()->timestamp;

        $this->withSession([
            'admin_password_confirmed_at' => $now,
            'admin_password_confirmed_first_at' => $now - 121,
        ])->actingAs($admin)
            ->post(route('marketing.discovery.toggle', $event->hashedId()))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse((bool) $event->fresh()->is_hidden_from_discovery);
    }
}
