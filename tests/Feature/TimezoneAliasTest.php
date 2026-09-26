<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Backward-compat timezone aliases, end to end.
 *
 * Chrome reports India as Asia/Calcutta. Sign-up stored it raw, the one-card schedule form posted
 * it as a hidden input, and RoleCreateRequest's plain `timezone` rule (DateTimeZone::ALL) refused
 * it - an error about a field the user could neither see nor change, on a page with no navigation.
 * And the profile page's <select> had no option for it, so any profile save rewrote the timezone
 * to Africa/Abidjan, the first option.
 */
class TimezoneAliasTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function userIn(string $timezone): User
    {
        $user = $this->createOwner();
        // forceFill + raw: the point is a row that already holds the alias, the way sign-up left it.
        DB::table('users')->where('id', $user->id)->update(['timezone' => $timezone]);

        return $user->fresh();
    }

    /** The bug report itself: the first schedule could not be saved. */
    public function test_a_user_stored_with_an_alias_can_save_their_first_schedule(): void
    {
        $user = $this->userIn('Asia/Calcutta');
        $this->actingAs($user);

        $html = $this->get(route('new', ['type' => 'talent']))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="Asia\/Kolkata"\s+selected/', $html,
            'the form must offer the listed name, selected');

        $this->post(route('role.store'), [
            'type' => 'talent',
            'name' => 'Calcutta Band',
            'email' => $user->email,
            'timezone' => 'Asia/Calcutta',
            'language_code' => 'en',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Asia/Kolkata', Role::where('user_id', $user->id)->value('timezone'));
    }

    /** A usable zone with no listed name must still save rather than strand the user. */
    public function test_a_usable_zone_with_no_listed_name_still_saves(): void
    {
        $user = $this->createOwner();
        $this->actingAs($user);

        $this->post(route('role.store'), [
            'type' => 'talent',
            'name' => 'Offset Band',
            'email' => $user->email,
            'timezone' => 'Etc/GMT-3',
            'language_code' => 'en',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Etc/GMT-3', Role::where('user_id', $user->id)->value('timezone'));
    }

    /**
     * The rule accepts exactly what canonicalize() keeps. With the framework's all_with_bc rule an
     * offset such as +05:30 - stored by the API, then rendered by x-timezone-options as its own
     * selected option - was posted straight back by the profile form and refused.
     */
    public function test_an_offset_timezone_round_trips_through_the_profile_form(): void
    {
        $user = $this->userIn('+05:30');
        $this->actingAs($user);

        $this->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => '+05:30',
            'language_code' => 'en',
        ])->assertSessionHasNoErrors();

        $this->assertSame('+05:30', $user->fresh()->timezone);
    }

    public function test_junk_is_still_refused(): void
    {
        $user = $this->createOwner();
        $this->actingAs($user);

        $this->post(route('role.store'), [
            'type' => 'talent',
            'name' => 'Junk Band',
            'email' => $user->email,
            'timezone' => 'Mars/Olympus_Mons',
            'language_code' => 'en',
        ])->assertSessionHasErrors('timezone');
    }

    /** Sign-up stores the listed name, and derives the clock format from it. */
    public function test_sign_up_stores_the_listed_name(): void
    {
        config(['app.hosted' => true]);

        $this->post('/sign_up', [
            'terms' => '1',
            'name' => 'Alias User',
            'email' => 'alias-user@gmail.com',
            'password' => 'password',
            'timezone' => 'US/Eastern',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'alias-user@gmail.com')->firstOrFail();

        $this->assertSame('America/New_York', $user->timezone);
        // detect_24_hour_time() reads the prefix, so US/Eastern would have read as unknown (null).
        $this->assertFalse((bool) $user->use_24_hour_time);
        $this->assertNotNull($user->getRawOriginal('use_24_hour_time'));
    }

    /**
     * The profile select had no option for an alias, so the browser posted the first one and any
     * save - changing only the name - rewrote the timezone to Africa/Abidjan.
     */
    public function test_the_profile_page_keeps_an_alias_users_timezone(): void
    {
        $user = $this->userIn('Asia/Calcutta');

        $html = $this->actingAs($user)->get('/settings')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="Asia\/Kolkata"\s+selected/', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="Africa\/Abidjan"\s+selected/', $html);

        $this->actingAs($user)->patch('/settings', [
            'name' => 'Renamed',
            'email' => $user->email,
            'timezone' => 'Asia/Calcutta',
            'language_code' => 'en',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Asia/Kolkata', $user->fresh()->timezone);
    }

    /** A zone with no listed name gets an option of its own instead of silently becoming another. */
    public function test_the_schedule_settings_select_keeps_an_unlisted_zone(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'talent');
        DB::table('roles')->where('id', $role->id)->update(['timezone' => 'Etc/GMT-3']);

        $html = $this->actingAs($user)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<option value="Etc\/GMT-3"\s+selected/', $html);
    }

    /**
     * The migration rewrites what is already stored, in every table that compares timezones, and
     * does it without touching updated_at (the sitemap reads it as the page's lastmod).
     */
    public function test_the_migration_canonicalizes_stored_aliases(): void
    {
        $user = $this->userIn('Asia/Calcutta');
        $role = $this->createRole($user, 'talent');
        $event = $this->createEvent($role);
        $untouched = $this->createRole($this->createOwner(), 'venue');

        $stamp = '2024-01-02 03:04:05';
        DB::table('users')->where('id', $user->id)->update(['updated_at' => $stamp]);
        DB::table('roles')->where('id', $role->id)->update(['timezone' => 'Europe/Kiev', 'updated_at' => $stamp]);
        DB::table('roles')->where('id', $untouched->id)->update(['timezone' => 'Etc/GMT-3']);
        DB::table('events')->where('id', $event->id)->update(['timezone' => 'Asia/Calcutta', 'updated_at' => $stamp]);

        $migration = require database_path('migrations/2026_09_25_000002_canonicalize_timezone_aliases.php');
        $migration->up();

        $this->assertSame('Asia/Kolkata', DB::table('users')->where('id', $user->id)->value('timezone'));
        $this->assertSame(\App\Utils\TimezoneUtils::canonicalize('Europe/Kiev'), DB::table('roles')->where('id', $role->id)->value('timezone'));
        $this->assertSame('Asia/Kolkata', DB::table('events')->where('id', $event->id)->value('timezone'));
        $this->assertSame('Etc/GMT-3', DB::table('roles')->where('id', $untouched->id)->value('timezone'), 'no listed name, so left alone');

        $this->assertSame($stamp, (string) DB::table('users')->where('id', $user->id)->value('updated_at'));
        $this->assertSame($stamp, (string) DB::table('roles')->where('id', $role->id)->value('updated_at'));
        $this->assertSame($stamp, (string) DB::table('events')->where('id', $event->id)->value('updated_at'));
    }
}
