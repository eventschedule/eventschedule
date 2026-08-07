<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The API accepts a category by NAME and resolves it to an id.
 *
 * It used to compare `Str::slug()` on both sides, and Str::slug returns "" for anything it
 * cannot transliterate. On a schedule with Hebrew or CJK categories that made every name
 * compare equal to every other, so the loop's first iteration always won: the request
 * succeeded with 201 and the event was filed under a category the caller never named, while
 * every category after the first became permanently unreachable.
 */
class ApiEventCategoryMatchTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    /** @return array{0: Role, 1: string} */
    private function scheduleWithCategories(array $names): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $role->event_categories = array_values(array_map(
            fn ($name, $i) => ['id' => 900 + $i, 'name' => $name, 'is_custom' => true, 'color' => null, 'name_en' => null],
            $names,
            array_keys($names)
        ));
        $role->save();

        return [$role, $this->apiKey($owner)];
    }

    private function postEvent(Role $role, string $key, string $category)
    {
        return $this->postJson('/api/events/'.$role->subdomain, [
            'name' => 'API Event',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'duration' => 2,
            'category' => $category,
        ], ['X-API-Key' => $key]);
    }

    public function test_a_non_latin_category_resolves_to_the_one_that_was_named(): void
    {
        // Sorted by name, so 'הופעות' is not first - which is what the old code always picked.
        [$role, $key] = $this->scheduleWithCategories(['הופעות', 'מופעים', 'סדנאות ילדים']);

        $this->postEvent($role, $key, 'סדנאות ילדים')->assertSuccessful();

        $event = Event::query()->latest('id')->firstOrFail();
        $this->assertSame(902, $event->category_id, 'the event landed on a category nobody asked for');
    }

    public function test_every_non_latin_category_is_reachable_not_just_the_first(): void
    {
        [$role, $key] = $this->scheduleWithCategories(['הופעות', 'מופעים', 'סדנאות ילדים']);

        $seen = [];
        foreach (['הופעות' => 900, 'מופעים' => 901, 'סדנאות ילדים' => 902] as $name => $expectedId) {
            $this->postEvent($role, $key, $name)->assertSuccessful();
            $seen[] = Event::query()->latest('id')->firstOrFail()->category_id;
            $this->assertSame($expectedId, end($seen), "\"{$name}\" resolved to the wrong category");
        }

        $this->assertCount(3, array_unique($seen), 'all three collapsed onto one category');
    }

    public function test_an_unknown_category_is_rejected_rather_than_matching_the_first(): void
    {
        [$role, $key] = $this->scheduleWithCategories(['הופעות', 'מופעים']);

        // Slugs to "" too, so the old code matched 'הופעות' and returned 201.
        $this->postEvent($role, $key, '日本語')
            ->assertStatus(422)
            ->assertJsonFragment(['error' => 'Category not found']);

        // As does punctuation-only junk.
        $this->postEvent($role, $key, '???')->assertStatus(422);

        $this->assertSame(0, Event::query()->count());
    }

    public function test_latin_categories_still_match_case_insensitively(): void
    {
        [$role, $key] = $this->scheduleWithCategories(['Live Music', 'Comedy']);

        $this->postEvent($role, $key, 'live music')->assertSuccessful();
        $this->assertSame(900, Event::query()->latest('id')->firstOrFail()->category_id);

        $this->postEvent($role, $key, 'Comedy')->assertSuccessful();
        $this->assertSame(901, Event::query()->latest('id')->firstOrFail()->category_id);

        $this->postEvent($role, $key, 'Jazz')->assertStatus(422);
    }
}
