<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthorizedUser(): array
    {
        $user = User::factory()->create();
        $user->is_admin = true;
        $user->save();

        $role = new Role;
        $role->subdomain = 'test' . strtolower(\Str::random(8));
        $role->user_id = $user->id;
        $role->type = 'venue';
        $role->name = 'Test Schedule';
        $role->email = 'test@example.com';
        $role->save();

        $role->users()->attach($user->id, ['level' => 'owner']);

        return [$user, $role];
    }

    private function createNewsletter(Role $role, User $user): Newsletter
    {
        return Newsletter::create([
            'role_id' => $role->id,
            'user_id' => $user->id,
            'subject' => 'Test Newsletter',
            'status' => 'draft',
            'template' => 'modern',
        ]);
    }

    // ── Page load tests ──

    public function test_newsletter_index_page_loads(): void
    {
        [$user, $role] = $this->createAuthorizedUser();

        $response = $this
            ->actingAs($user)
            ->get('/newsletters?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertOk();
    }

    public function test_newsletter_create_page_loads(): void
    {
        [$user, $role] = $this->createAuthorizedUser();

        $response = $this
            ->actingAs($user)
            ->get('/newsletters/create?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertOk();
    }

    public function test_newsletter_edit_page_loads(): void
    {
        [$user, $role] = $this->createAuthorizedUser();
        $newsletter = $this->createNewsletter($role, $user);

        $response = $this
            ->actingAs($user)
            ->get('/newsletters/' . UrlUtils::encodeId($newsletter->id) . '/edit?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertOk();
    }

    public function test_newsletter_stats_page_loads(): void
    {
        [$user, $role] = $this->createAuthorizedUser();
        $newsletter = $this->createNewsletter($role, $user);

        $response = $this
            ->actingAs($user)
            ->get('/newsletters/' . UrlUtils::encodeId($newsletter->id) . '/stats?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertOk();
    }

    public function test_newsletter_segments_page_loads(): void
    {
        [$user, $role] = $this->createAuthorizedUser();

        $response = $this
            ->actingAs($user)
            ->get('/newsletter-segments?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertOk();
    }

    public function test_newsletter_import_page_loads(): void
    {
        [$user, $role] = $this->createAuthorizedUser();

        $response = $this
            ->actingAs($user)
            ->get('/newsletter-import?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertOk();
    }

    // ── Auth tests ──

    public function test_newsletter_index_requires_auth(): void
    {
        $response = $this->get('/newsletters');

        $response->assertRedirect('/login');
    }

    public function test_newsletter_index_requires_enterprise_role(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $user = User::factory()->create();

        $role = new Role;
        $role->subdomain = 'noent' . strtolower(\Str::random(8));
        $role->user_id = $user->id;
        $role->type = 'venue';
        $role->name = 'Non-Enterprise Schedule';
        $role->email = 'noent@example.com';
        $role->save();

        $role->users()->attach($user->id, ['level' => 'owner']);

        $response = $this
            ->actingAs($user)
            ->get('/newsletters?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertForbidden();
    }

    // ── CRUD tests ──

    public function test_newsletter_can_be_created(): void
    {
        [$user, $role] = $this->createAuthorizedUser();

        $response = $this
            ->actingAs($user)
            ->post('/newsletters', [
                'subject' => 'My New Newsletter',
                'template' => 'modern',
                'role_id' => UrlUtils::encodeId($role->id),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('newsletters', [
            'subject' => 'My New Newsletter',
            'role_id' => $role->id,
            'status' => 'draft',
        ]);
    }

    public function test_newsletter_can_be_updated(): void
    {
        [$user, $role] = $this->createAuthorizedUser();
        $newsletter = $this->createNewsletter($role, $user);

        $response = $this
            ->actingAs($user)
            ->put('/newsletters/' . UrlUtils::encodeId($newsletter->id), [
                'subject' => 'Updated Subject',
                'template' => 'modern',
                'role_id' => UrlUtils::encodeId($role->id),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('newsletters', [
            'id' => $newsletter->id,
            'subject' => 'Updated Subject',
        ]);
    }

    public function test_newsletter_can_be_deleted(): void
    {
        [$user, $role] = $this->createAuthorizedUser();
        $newsletter = $this->createNewsletter($role, $user);

        $response = $this
            ->actingAs($user)
            ->delete('/newsletters/' . UrlUtils::encodeId($newsletter->id) . '?role_id=' . UrlUtils::encodeId($role->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('newsletters', [
            'id' => $newsletter->id,
            'status' => 'cancelled',
        ]);
    }
}
