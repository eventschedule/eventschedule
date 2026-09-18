<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The /admin re-authentication window.
 *
 * Before this existed, EnsureUserIsAdmin gated on the PRESENCE of admin_password_confirmed_at and
 * never read the timestamp it stored, so the only thing ending a confirmed admin state was the
 * session expiring - which made SESSION_LIFETIME an accidental admin timeout. Now there are two
 * clocks: a sliding idle window (auth.admin_reauth_timeout) that any admin request restarts, and
 * an absolute ceiling (auth.admin_reauth_max_lifetime) that only re-entering the password resets.
 *
 * Every travel() here is measured in SECONDS on purpose. phpunit.xml pins SESSION_DRIVER=array,
 * and ArraySessionHandler::read() expires its entries against session.lifetime using Carbon::now(),
 * so travelling past that (120 minutes, also pinned) destroys the SESSION rather than expiring the
 * window - and the assertions below would then pass for entirely the wrong reason.
 */
class AdminReauthTimeoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A lighter admin page than the dashboard; the middleware runs before the controller either way. */
    private function adminUrl(): string
    {
        return route('admin.audit_log');
    }

    private function admin(): User
    {
        return $this->createOwner(true);
    }

    public function test_a_confirmation_inside_the_window_is_let_through(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->admin())
            ->get($this->adminUrl())
            ->assertOk();
    }

    public function test_a_confirmation_older_than_the_window_is_challenged(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp - 61])
            ->actingAs($this->admin())
            ->get($this->adminUrl())
            ->assertRedirect(route('admin.password.confirm.show'));
    }

    /**
     * The point of the whole change: the window is idle time, not time since confirmation. Three
     * hops of 40 seconds each is 120 seconds total against a 60-second window - which only passes
     * if every request restarted the clock.
     */
    public function test_each_admin_request_restarts_the_timer(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);
        $admin = $this->admin();

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        foreach ([40, 40, 40] as $seconds) {
            $this->travel($seconds)->seconds();
            $this->get($this->adminUrl())->assertOk();
        }

        // And it still expires once the sliding window is genuinely exhausted.
        $this->travel(61)->seconds();
        $this->get($this->adminUrl())->assertRedirect(route('admin.password.confirm.show'));
    }

    public function test_the_stored_timestamp_actually_moves(): void
    {
        config(['auth.admin_reauth_timeout' => 600]);
        $start = now()->timestamp;

        $this->withSession(['admin_password_confirmed_at' => $start])
            ->actingAs($this->admin());

        $this->travel(30)->seconds();
        $this->get($this->adminUrl())->assertOk();

        $this->assertSame($start + 30, session('admin_password_confirmed_at'));
    }

    /**
     * The ceiling is what stops /admin/support - which polls every 5 seconds - from renewing admin
     * rights forever on an unattended machine.
     */
    public function test_the_absolute_ceiling_expires_a_continuously_active_session(): void
    {
        config([
            'auth.admin_reauth_timeout' => 600,
            'auth.admin_reauth_max_lifetime' => 120,
        ]);
        $start = now()->timestamp;

        $this->withSession([
            'admin_password_confirmed_at' => $start,
            'admin_password_confirmed_first_at' => $start,
        ])->actingAs($this->admin());

        // Busy the whole time, always well inside the idle window.
        foreach ([50, 50] as $seconds) {
            $this->travel($seconds)->seconds();
            $this->get($this->adminUrl())->assertOk();
        }

        // The slide must never push the ceiling out, or it would never be reached.
        $this->assertSame($start, session('admin_password_confirmed_first_at'));

        $this->travel(50)->seconds();
        $this->get($this->adminUrl())->assertRedirect(route('admin.password.confirm.show'));
    }

    /**
     * Sessions that predate this change - and the ~38 existing tests that seed only the sliding key -
     * carry no ceiling. It has to be written, not merely defaulted: the sliding key is rewritten to
     * now on every request, so a default computed from it would restart the ceiling every time.
     */
    public function test_a_legacy_session_gets_its_ceiling_backfilled_and_persisted(): void
    {
        config(['auth.admin_reauth_timeout' => 600]);
        $start = now()->timestamp;

        $this->withSession(['admin_password_confirmed_at' => $start])
            ->actingAs($this->admin());

        $this->travel(30)->seconds();
        $this->get($this->adminUrl())->assertOk();
        $this->assertSame($start, session('admin_password_confirmed_first_at'));

        // Still pinned to the original confirmation after a second request, not slid forward.
        $this->travel(30)->seconds();
        $this->get($this->adminUrl())->assertOk();
        $this->assertSame($start, session('admin_password_confirmed_first_at'));
    }

    public function test_expiry_clears_the_user_agent_binding_and_both_timestamps(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);
        $start = now()->timestamp;

        $this->withSession([
            'admin_password_confirmed_at' => $start - 61,
            'admin_password_confirmed_first_at' => $start - 61,
            'admin_user_agent' => 'Symfony',
        ])->actingAs($this->admin())
            ->get($this->adminUrl())
            ->assertRedirect(route('admin.password.confirm.show'));

        $this->assertNull(session('admin_password_confirmed_at'));
        $this->assertNull(session('admin_password_confirmed_first_at'));
        $this->assertNull(session('admin_user_agent'));
    }

    /** The JSON branch the support poll relies on. Untested before this file existed. */
    public function test_an_expired_window_answers_json_with_423(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp - 61])
            ->actingAs($this->admin())
            ->getJson($this->adminUrl())
            ->assertStatus(423);
    }

    /** A clock that moved backwards must not read as a window that never ends. */
    public function test_a_future_timestamp_is_rejected(): void
    {
        config(['auth.admin_reauth_timeout' => 60]);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp + 3600])
            ->actingAs($this->admin())
            ->get($this->adminUrl())
            ->assertRedirect(route('admin.password.confirm.show'));
    }

    /**
     * The trap this gate was one `> 0` away from walking into: a missing or empty config value casts
     * to 0, and treating 0 as "no timeout" would have disabled the control silently on any install
     * that upgraded with a cached config. 0 must never widen the window.
     */
    public function test_a_zero_timeout_never_means_unlimited(): void
    {
        config(['auth.admin_reauth_timeout' => 0]);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp - 5])
            ->actingAs($this->admin())
            ->get($this->adminUrl())
            ->assertRedirect(route('admin.password.confirm.show'));
    }

    /**
     * .env.example ships keys present-but-empty and env() returns '' for that, so a default ARGUMENT
     * would never fire - the trap config/app.php documents for platform_currency. Re-evaluate the
     * config file with each hostile value to prove the ?: idiom and the max() floor hold.
     */
    public function test_hostile_env_values_fall_back_to_the_shipped_defaults(): void
    {
        $original = $_SERVER['ADMIN_REAUTH_TIMEOUT'] ?? null;

        try {
            // Positive control FIRST. Without it every case below would read 86400 even in a world
            // where env() were ignored entirely, and the test would prove nothing.
            $_SERVER['ADMIN_REAUTH_TIMEOUT'] = '3600';
            $config = require base_path('config/auth.php');
            $this->assertSame(3600, $config['admin_reauth_timeout'],
                'A valid value must be read, or the hostile cases below are vacuous.');

            // Blank and '0' are FALSY, so the ?: fires and hands back the shipped default. This
            // is the .env.example trap the elvis form exists for: a present-but-empty value
            // returns '', so a default ARGUMENT to env() would never fire.
            foreach (['', '0'] as $falsy) {
                $_SERVER['ADMIN_REAUTH_TIMEOUT'] = $falsy;
                $config = require base_path('config/auth.php');

                $this->assertSame(86400, $config['admin_reauth_timeout'],
                    "ADMIN_REAUTH_TIMEOUT='{$falsy}' must fall back to the shipped default.");
            }

            // A non-numeric string and a negative are both TRUTHY, so they survive the ?: and land
            // on the max() floor instead: 'nonsense' casts to 0, '-1' stays -1, both clamp to 60.
            // A one-minute window is a hostile thing to hand someone for a typo, but it is the
            // fail-closed direction and that is what matters here - what must never happen is a
            // value that opens the gate, or one that makes every comparison true and locks the
            // panel behind a re-auth loop nobody can escape.
            foreach (['nonsense', '-1'] as $truthyButUnusable) {
                $_SERVER['ADMIN_REAUTH_TIMEOUT'] = $truthyButUnusable;
                $config = require base_path('config/auth.php');

                $this->assertSame(60, $config['admin_reauth_timeout'],
                    "ADMIN_REAUTH_TIMEOUT='{$truthyButUnusable}' must clamp to the floor, never "
                    .'disable the window or invert its comparison.');
            }
        } finally {
            if ($original === null) {
                unset($_SERVER['ADMIN_REAUTH_TIMEOUT']);
            } else {
                $_SERVER['ADMIN_REAUTH_TIMEOUT'] = $original;
            }
        }
    }

    /**
     * Confirming the password resets BOTH clocks, and regenerates the session id without losing the
     * intended URL that redirect()->guest() stored.
     */
    public function test_confirming_the_password_resets_both_clocks(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->get($this->adminUrl())->assertRedirect(route('admin.password.confirm.show'));

        $this->post(route('admin.password.confirm'), ['password' => 'password'])
            ->assertRedirect($this->adminUrl());

        $this->assertSame(now()->timestamp, session('admin_password_confirmed_at'));
        $this->assertSame(now()->timestamp, session('admin_password_confirmed_first_at'));
    }
}
