<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Sub-schedule slugs used to come straight from Str::slug(), which returns "" for Hebrew,
 * CJK and anything else it cannot transliterate. That broke three things at once:
 *
 *  - the slug is part of the guest URL, so the sub-schedule was unreachable;
 *  - unique(['slug','role_id']) rejects a second empty one, so a schedule could only ever
 *    have one non-Latin sub-schedule;
 *  - the calendar filter uses the slug as its <option> value, where "" is already taken by
 *    "Show all", so picking a Hebrew sub-schedule silently did nothing.
 */
class GroupSlugTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array<string, array{0: string, 1: string|null}> */
    public static function nonLatinNames(): array
    {
        return [
            'hebrew' => ['הופעות', null],
            'hebrew with geresh' => ['ג׳אז', null],
            'hebrew multiword' => ['סדנאות ילדים', null],
            'cjk' => ['日本語', null],
            'arabic' => ['حفلات', null],
            'cyrillic' => ['Концерты', null],
            'hebrew with english name' => ['הופעות', 'Live Shows'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nonLatinNames')]
    public function test_clean_slug_is_never_empty(string $name, ?string $nameEn): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        $slug = Group::cleanSlug($role->id, $name, $nameEn);

        $this->assertNotSame('', $slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug, 'a slug has to be URL-safe');
    }

    public function test_an_english_name_is_preferred_for_readability(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        $this->assertSame('live-shows', Group::cleanSlug($role->id, 'הופעות', 'Live Shows'));
        // Without one it romanizes rather than falling back to something opaque.
        $this->assertSame('hwpwt', Group::cleanSlug($role->id, 'הופעות'));
    }

    public function test_a_user_typed_slug_wins_but_is_still_cleaned(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        $this->assertSame('my-shows', Group::cleanSlug($role->id, 'הופעות', null, 'My Shows'));
        // A non-Latin slug typed by hand used to be stored verbatim and was unusable.
        $this->assertSame('hwpwt', Group::cleanSlug($role->id, 'הופעות', null, 'הופעות'));
    }

    public function test_collisions_within_a_schedule_are_disambiguated(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $this->createGroup($role, ['name' => 'Shows', 'slug' => 'shows']);

        $this->assertSame('shows-2', Group::cleanSlug($role->id, 'Shows'));

        // A different schedule is free to reuse the slug.
        $other = $this->createRole($owner, 'curator', ['name' => 'Other']);
        $this->assertSame('shows', Group::cleanSlug($other->id, 'Shows'));
    }

    public function test_a_group_keeps_its_own_slug_when_it_is_the_one_being_edited(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $group = $this->createGroup($role, ['name' => 'Shows', 'slug' => 'shows']);

        $this->assertSame('shows', Group::cleanSlug($role->id, 'Shows', null, null, $group->id));
    }

    /** The whole point: two Hebrew sub-schedules on one schedule used to be impossible. */
    public function test_two_non_latin_sub_schedules_can_coexist_and_stay_distinguishable(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        $slugs = [];
        foreach (['הופעות', 'מופעים', 'סדנאות ילדים'] as $name) {
            $slug = Group::cleanSlug($role->id, $name);
            $role->groups()->create(['name' => $name, 'slug' => $slug]);
            $slugs[] = $slug;
        }

        $this->assertCount(3, array_unique($slugs), 'each sub-schedule needs its own slug');
        $this->assertNotContains('', $slugs);
        $this->assertSame(3, $role->groups()->count());
    }

    public function test_saving_hebrew_sub_schedules_through_the_edit_form_produces_usable_slugs(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['language_code' => 'he', 'translation_language_code' => 'he']);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'he',
            'new_subdomain' => $role->subdomain,
            'groups' => [
                'new_0' => ['name' => 'הופעות'],
                'new_1' => ['name' => 'מופעים'],
            ],
        ])->assertSessionHasNoErrors();

        $groups = $role->groups()->orderBy('id')->get();

        $this->assertCount(2, $groups);
        foreach ($groups as $group) {
            $this->assertNotSame('', (string) $group->slug, "{$group->name} has no slug");
        }
        $this->assertNotSame($groups[0]->slug, $groups[1]->slug);
    }

    /**
     * The reported symptom. The filter renders one <option> per sub-schedule keyed on the
     * slug, alongside a "Show all" option whose value is "" - so an empty slug made the two
     * indistinguishable and selecting the sub-schedule did nothing.
     */
    public function test_the_calendar_filter_options_are_distinct_and_never_blank(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['language_code' => 'he']);

        foreach (['הופעות', 'מופעים'] as $name) {
            $role->groups()->create(['name' => $name, 'slug' => Group::cleanSlug($role->id, $name)]);
        }

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        // $groupsForVue is what the <option :value="group.slug"> loop reads.
        $this->assertMatchesRegularExpression('/groups:\s*(\[.*?\]),/s', $html);
        preg_match('/groups:\s*(\[.*?\]),\n/s', $html, $m);
        $groups = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertCount(2, $groups);

        $slugs = array_column($groups, 'slug');
        $this->assertNotContains('', $slugs, 'an empty value collides with the Show all option');
        $this->assertCount(2, array_unique($slugs));
    }

    public function test_the_backfill_repairs_existing_empty_slugs(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        // What the old Str::slug() path wrote.
        $id = DB::table('groups')->insertGetId([
            'role_id' => $role->id,
            'name' => 'הופעות',
            'slug' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_08_07_000002_backfill_empty_group_slugs.php');
        $migration->up();

        $this->assertNotSame('', DB::table('groups')->where('id', $id)->value('slug'));
    }

    /**
     * events.slug has the same history: 2025_01_25_212106_add_quantity backfilled every row
     * with a bare Str::slug() and then made the column non-nullable, so pre-2025 non-Latin
     * event names are still stored as "" and their guest URL is broken.
     */
    public function test_the_event_backfill_repairs_existing_empty_slugs(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['name' => 'הופעה מיוחדת']);

        DB::table('events')->where('id', $event->id)->update(['slug' => '']);

        $migration = require database_path('migrations/2026_08_07_000003_backfill_empty_event_slugs.php');
        $migration->up();

        $slug = DB::table('events')->where('id', $event->id)->value('slug');

        $this->assertNotSame('', $slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);

        // A row that already had a usable slug is left alone.
        $latin = $this->createEvent($role, ['name' => 'Jazz Night', 'slug' => 'jazz-night']);
        $migration->up();
        $this->assertSame('jazz-night', DB::table('events')->where('id', $latin->id)->value('slug'));
    }

    public function test_the_api_produces_a_usable_slug(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['language_code' => 'he', 'translation_language_code' => 'he']);

        $group = $role->groups()->create([
            'name' => 'הופעות',
            'slug' => Group::cleanSlug($role->id, 'הופעות'),
        ]);

        $this->assertNotSame('', $group->slug);
        $this->assertSame(
            $group->id,
            Role::find($role->id)->groups()->where('slug', $group->slug)->value('id'),
            'the sub-schedule must be resolvable by its slug, which is how the guest URL works'
        );
    }
}
