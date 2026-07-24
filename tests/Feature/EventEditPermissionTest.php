<?php

namespace Tests\Feature;

use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest "Edit event" link is shown whenever the user can edit the event via ANY
 * attached schedule, but it targets the currently-viewed schedule's subdomain. When
 * the user doesn't manage that schedule (e.g. viewing on a venue/curator page), the
 * edit route must redirect them to a schedule they DO manage instead of rejecting a
 * legitimately editable event.
 */
class EventEditPermissionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_edit_redirects_to_a_managed_schedule_when_viewing_on_another(): void
    {
        $editor = $this->createOwner();
        $roleA = $this->createRole($editor);          // editor is owner of A

        $other = $this->createOwner();
        $roleB = $this->createRole($other);           // editor is NOT a member of B

        // Editable by $editor only through managing A (created by $other, so the edit
        // right is not the creator shortcut). Attached to both A and B.
        $event = $this->createEvent($roleA, ['user_id' => $other->id]);
        $event->roles()->attach($roleB->id, ['is_accepted' => true]);

        $hash = UrlUtils::encodeId($event->id);

        $this->actingAs($editor)
            ->get(route('event.edit', ['subdomain' => $roleB->subdomain, 'hash' => $hash]))
            ->assertRedirect(route('event.edit', ['subdomain' => $roleA->subdomain, 'hash' => $hash]));
    }

    public function test_edit_loads_directly_when_the_viewed_schedule_is_managed(): void
    {
        $editor = $this->createOwner();
        $roleA = $this->createRole($editor);

        $other = $this->createOwner();
        $roleB = $this->createRole($other);

        $event = $this->createEvent($roleA, ['user_id' => $other->id]);
        $event->roles()->attach($roleB->id, ['is_accepted' => true]);

        // Editor also manages B now, so editing under B must not redirect.
        $this->followRole($editor, $roleB, 'admin');

        $hash = UrlUtils::encodeId($event->id);

        $this->actingAs($editor)
            ->get(route('event.edit', ['subdomain' => $roleB->subdomain, 'hash' => $hash]))
            ->assertOk();
    }

    public function test_edit_denies_a_user_who_manages_no_attached_schedule(): void
    {
        $owner = $this->createOwner();
        $roleA = $this->createRole($owner);

        $other = $this->createOwner();
        $roleB = $this->createRole($other);

        $event = $this->createEvent($roleA);
        $event->roles()->attach($roleB->id, ['is_accepted' => true]);

        $stranger = $this->createOwner();             // no membership on A or B
        $hash = UrlUtils::encodeId($event->id);

        $this->actingAs($stranger)
            ->get(route('event.edit', ['subdomain' => $roleB->subdomain, 'hash' => $hash]))
            ->assertSessionHas('error', __('messages.not_authorized'));
    }
}
