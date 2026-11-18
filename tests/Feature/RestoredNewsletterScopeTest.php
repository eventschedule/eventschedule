<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\NewsletterSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's newsletter and segment can never be the platform's (2026-11-18). A backup is a
 * file its owner can edit, and restore copied `type` from it: an 'admin' newsletter skipped every
 * check in NewsletterService::send() and went to every user on the install, and an 'all_users'
 * segment resolved to them too. The platform's own are the rows with no schedule.
 */
class RestoredNewsletterScopeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Mutation: let isAdmin() answer on the type alone. */
    public function test_a_schedules_newsletter_is_never_the_platforms(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $schedules = new Newsletter;
        $schedules->forceFill(['role_id' => $role->id, 'type' => 'admin']);
        $this->assertFalse($schedules->isAdmin());

        $platforms = new Newsletter;
        $platforms->forceFill(['role_id' => null, 'type' => 'admin']);
        $this->assertTrue($platforms->isAdmin());
    }

    /** Mutation: drop the role_id guard in resolveRecipients(). */
    public function test_a_schedules_platform_wide_segment_reaches_nobody(): void
    {
        User::factory()->count(3)->create(['email_verified_at' => now()]);
        $role = $this->createRole($this->createOwner(), 'venue');

        foreach (['all_users', 'plan_tier', 'signup_date', 'admins'] as $type) {
            $segment = new NewsletterSegment;
            $segment->forceFill(['role_id' => $role->id, 'name' => $type, 'type' => $type, 'filter_criteria' => []]);
            $this->assertCount(0, $segment->resolveRecipients(), $type);
        }

        $platform = new NewsletterSegment;
        $platform->forceFill(['role_id' => null, 'name' => 'Everyone', 'type' => 'all_users', 'filter_criteria' => []]);
        $this->assertGreaterThan(0, $platform->resolveRecipients()->count());
    }

    /** Mutation: copy the type from the backup again. */
    public function test_restore_never_writes_a_platform_type(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $service = app(\App\Services\BackupService::class);

        $importSegment = new \ReflectionMethod($service, 'importSegment');
        $importSegment->setAccessible(true);
        foreach (['all_users' => 'manual', 'admins' => 'manual', 'all_followers' => 'all_followers'] as $posted => $kept) {
            $segment = $importSegment->invoke($service, ['name' => 'x', 'type' => $posted], $role, ['events' => [], 'groups' => []]);
            $this->assertSame($kept, $segment->type, $posted);
        }

        $source = file_get_contents(app_path('Services/BackupService.php'));
        $this->assertStringContainsString("\$newsletter->type = 'schedule';", $source);
        $this->assertStringNotContainsString("\$newsletter->type = \$data['type']", $source);
    }
}
