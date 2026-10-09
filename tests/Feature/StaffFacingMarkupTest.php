<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Text one person wrote, shown to the people who run a schedule.
 *
 * The schedule's settings page (role/edit) is opened by every editor and the owner, and three
 * things on it took a value a team member had saved and treated it as script:
 *
 *   - `custom_domain` was printed inside an Alpine x-data expression. Alpine evaluates that
 *     attribute, the `url` rule accepts an address with a quote in its path, and the model's
 *     trimming is skipped when the address does not parse. The value rides in a data attribute
 *     now, and an address that does not parse to a plain host is refused;
 *   - `background`, `background_colors`, `font_family` and `language_code` were echoed into
 *     single-quoted strings in the page's script. Blade's escaping leaves a backslash alone, so a
 *     value could end the string. They are written with @json from a bare variable;
 *   - the Test Import dialog inserted the import's console text, scraped names and addresses
 *     included, as HTML.
 *
 * And the seating box-office report wrote a buyer's name, email and note to CSV without the
 * guard the other exports use.
 */
class StaffFacingMarkupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_custom_domain_that_is_not_a_plain_host_is_refused(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->from(route('role.edit', ['subdomain' => $role->subdomain]))->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name, 'email' => $role->email, 'timezone' => $role->timezone, 'new_subdomain' => $role->subdomain,
            'custom_domain' => "https://tickets.example.org:99999/it's", 'custom_domain_mode' => 'redirect',
        ])->assertSessionHasErrors('custom_domain');

        $this->assertNull($role->fresh()->custom_domain);
    }

    public function test_the_settings_page_does_not_write_saved_values_into_script(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        Role::where('id', $role->id)->update(['font_family' => 'x\\', 'custom_domain' => "https://a.example.org/it's"]);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        // Written as JSON from a bare variable (double quotes, every backslash doubled), never
        // inside a single-quoted string that Blade's escaping cannot close safely.
        foreach (['background', 'background_colors', 'font_family', 'language_code'] as $id) {
            $this->assertStringContainsString("$('#".$id."').val(\"", $html, $id);
            $this->assertStringNotContainsString("$('#".$id."').val('", $html, $id);
        }
        $this->assertStringNotContainsString("var storedBgColors = '", $html);
        $this->assertSame(1, preg_match('/<div[^>]*id="subdomain-edit"[^>]*>/', $html, $tag));
        $this->assertStringContainsString('$el.dataset.domain', $tag[0]);
        $this->assertStringNotContainsString("domain: '", $tag[0], 'the saved address is not part of the expression');
    }

    public function test_the_import_dialog_and_the_seating_report_escape_what_they_are_given(): void
    {
        $page = file_get_contents(resource_path('views/role/edit.blade.php'));
        $this->assertStringNotContainsString('whitespace-pre-wrap">${output}</pre>', $page);
        $this->assertStringContainsString('${escapeHtml(output)}', $page);

        $report = file_get_contents(app_path('Http/Controllers/BoxOfficeController.php'));
        $this->assertStringContainsString("CsvUtils::sanitizeCell(\$row['name'])", $report);
        $this->assertStringContainsString("CsvUtils::sanitizeCell(\$row['note'])", $report);
    }
}
