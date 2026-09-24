<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Role::isIndexableHost() and Role::indexableHost() are one rule in two languages: the guest
 * layout's robots meta asks the PHP one, both sitemaps ask the SQL one. Any row they disagree on
 * is either a page the sitemap submits and then refuses ("Excluded by noindex"), or an indexable
 * page the sitemap never submits. So this holds them to the same answer for every state that
 * decides it, and holds their parts - hasVerifiedContact() and isDemoContent() - to theirs.
 */
class RoleIndexabilityPredicateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array<string, array{0: Role, 1: bool}> label => [schedule, indexable?] */
    private function matrix(): array
    {
        $owner = fn () => $this->createOwner();
        $demoUser = User::factory()->create([
            'email' => DemoService::DEMO_EMAIL,
            'email_verified_at' => now(),
        ]);

        $states = [
            'verified email' => [$this->createRole($owner()), true],
            'verified date, empty email' => [$this->createRole($owner(), 'venue', ['email' => '']), false],
            'verified date, no email' => [$this->createRole($owner(), 'venue', ['email' => null]), false],
            'unverified email' => [$this->createRole($owner(), 'venue', ['email_verified_at' => null]), false],
            'phone only' => [$this->createRole($owner(), 'talent', [
                'email' => null,
                'email_verified_at' => null,
                'phone' => '+15551234567',
                'phone_verified_at' => now(),
            ]), true],
            'verified date, empty phone' => [$this->createRole($owner(), 'talent', [
                'email' => null,
                'email_verified_at' => null,
                'phone' => '',
                'phone_verified_at' => now(),
            ]), false],
            'unverified email, verified phone' => [$this->createRole($owner(), 'talent', [
                'email_verified_at' => null,
                'phone' => '+15551234568',
                'phone_verified_at' => now(),
            ]), true],
            'deleted' => [$this->createRole($owner(), 'venue', ['is_deleted' => true]), false],
            'showcase contact address' => [$this->createRole($owner(), 'venue', ['email' => DemoService::DEMO_EMAIL]), false],
            'demo owner' => [$this->createRole($demoUser), false],
            'simpsons' => [$this->createRole($owner(), 'curator', ['subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN]), false],
            // generateSubdomain() hands a real "Demo Night" this name. It is not demo content.
            'a real demo-night' => [$this->createRole($owner(), 'venue', ['subdomain' => 'demo-night', 'name' => 'Demo Night']), true],
        ];

        $noOwner = $this->createRole($owner(), 'venue');
        DB::table('roles')->where('id', $noOwner->id)->update(['user_id' => null]);
        $states['no owner'] = [$noOwner->fresh(), false];

        // The column collation is case- and trailing-space-insensitive, so the PHP twin must be.
        // Written around the model, whose saving hook lower-cases the address: rows that predate
        // the hook, or that were written around it, still hold the original.
        $mixedCase = $this->createRole($owner(), 'venue');
        DB::table('roles')->where('id', $mixedCase->id)->update(['email' => 'Contact@EventSchedule.com ']);
        $states['showcase address, other case'] = [$mixedCase->fresh(), false];

        return $states;
    }

    public function test_the_php_and_sql_predicates_agree_on_every_state(): void
    {
        $matrix = $this->matrix();
        $demoOwnerId = (int) User::where('email', DemoService::DEMO_EMAIL)->value('id');

        $indexable = Role::query()->indexableHost()->pluck('id')->all();
        $demo = Role::query()->demoContent()->pluck('id')->all();
        $verified = Role::constrainVerifiedContact(Role::query())->pluck('id')->all();

        foreach ($matrix as $label => [$role, $expected]) {
            $role = $role->fresh();

            $this->assertSame($expected, $role->isIndexableHost(), $label.': isIndexableHost()');
            $this->assertSame($expected, in_array($role->id, $indexable, true), $label.': indexableHost()');
            // The sitemap's form, which compares ids instead of loading each owner.
            $this->assertSame($expected, $role->fresh()->isIndexableHost($demoOwnerId), $label.': isIndexableHost($demoOwnerId)');

            $this->assertSame(in_array($role->id, $demo, true), $role->isDemoContent(), $label.': isDemoContent()');
            $this->assertSame(in_array($role->id, $demo, true), $role->fresh()->isDemoContent($demoOwnerId), $label.': isDemoContent($demoOwnerId)');
            $this->assertSame(in_array($role->id, $verified, true), $role->hasVerifiedContact(), $label.': hasVerifiedContact()');
        }
    }

    /**
     * The sitemap passes 0 when there is no demo user at all, and 0 must match nobody - never
     * fall back to loading each schedule's owner, which is an N+1 over the whole table.
     */
    public function test_a_missing_demo_user_matches_nobody_without_a_query(): void
    {
        $role = $this->createRole($this->createOwner())->fresh();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $answer = $role->isIndexableHost(0);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertTrue($answer);
        $this->assertSame([], $queries);
    }

    /**
     * A selfhost install has no demo at all, so nothing on it is demo content - the gate
     * is_demo_role() has always had. Without it, a selfhosted schedule of its own that happens to
     * be called "simpsons", or that uses the demo contact address, was de-indexed and dropped from
     * the sitemap. Both twins must agree on that too.
     */
    public function test_selfhost_has_no_demo_content(): void
    {
        $matrix = $this->matrix();

        config(['app.hosted' => false, 'app.is_testing' => false]);

        $demo = Role::query()->demoContent()->pluck('id')->all();
        $indexable = Role::query()->indexableHost()->pluck('id')->all();

        $this->assertSame([], $demo);

        foreach (['showcase contact address', 'demo owner', 'simpsons'] as $label) {
            $role = $matrix[$label][0]->fresh();

            $this->assertFalse($role->isDemoContent(), $label.': isDemoContent()');
            $this->assertTrue($role->isIndexableHost(), $label.': isIndexableHost()');
            $this->assertContains($role->id, $indexable, $label.': indexableHost()');
        }
    }

    /**
     * The robots meta is this rule. A showcase schedule - caught only by its contact address, which
     * is_demo_role() never looked at - used to render "index, follow".
     */
    public function test_the_page_robots_meta_follows_the_rule(): void
    {
        foreach ($this->matrix() as $label => [$role, $expected]) {
            if ($role->is_deleted || ! $role->isClaimed()) {
                // Not served at all (a 404, or a claim page): nothing to read a robots meta off.
                continue;
            }

            $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

            preg_match('/<meta name="robots" content="([^"]*)"/', $html, $m);

            if ($expected) {
                $this->assertMatchesRegularExpression('/^index, follow\b/', $m[1] ?? '', $label);
            } else {
                $this->assertSame('noindex, nofollow', $m[1] ?? null, $label);
            }
        }
    }
}
