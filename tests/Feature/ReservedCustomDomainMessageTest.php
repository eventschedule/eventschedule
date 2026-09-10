<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * invalid_custom_domain used to read "Cannot use an eventschedule.com domain as a custom domain",
 * untranslated in most locales, and it said so even when the host being refused was the install's
 * OWN base domain - so a white-label platform told its customers about a product they had never
 * heard of. The message now names the host that was refused, and nothing else.
 */
class ReservedCustomDomainMessageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_host_containing_eventschedule_com_is_refused_by_name(): void
    {
        $role = $this->createRole($this->createOwner());

        $this->putCustomDomain($role, 'https://tickets.eventschedule.com')
            ->assertSessionHasErrors([
                'custom_domain' => __('messages.invalid_custom_domain', ['domain' => 'tickets.eventschedule.com']),
            ]);

        $this->assertNull($role->fresh()->custom_domain);
    }

    public function test_the_installs_own_base_domain_is_refused_without_naming_eventschedule_com(): void
    {
        $base = _base_domain();
        $this->assertNotEmpty($base, 'phpunit.xml forces APP_URL, so the base domain is known');

        $host = 'events.'.$base;
        $role = $this->createRole($this->createOwner());

        $response = $this->putCustomDomain($role, 'https://'.$host);

        $response->assertSessionHasErrors([
            'custom_domain' => __('messages.invalid_custom_domain', ['domain' => $host]),
        ]);

        if (! str_contains($base, 'eventschedule.com')) {
            $this->assertStringNotContainsString(
                'eventschedule.com',
                (string) session('errors')->first('custom_domain'),
                'An operator\'s own domain must not be refused in our name.'
            );
        }
    }

    private function putCustomDomain(Role $role, string $customDomain): TestResponse
    {
        return $this->actingAs($role->user)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => $role->language_code ?: 'en',
            'new_subdomain' => $role->subdomain,
            'custom_domain' => $customDomain,
        ]);
    }
}
