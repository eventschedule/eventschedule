<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Utils\CssUtils;
use App\Utils\EventTextGenerator;
use App\Utils\SlugPatternUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Four places where somebody's text reached a guest page past its guard.
 *
 *   - the carpool page prints other people's details inside its Vue mount. Three lines (a driver's
 *     email and phone, the name in a review line) lacked the v-pre every other line there carries,
 *     and Vue compiles text it is not told to leave alone;
 *   - CssUtils let an owner's CSS name an outside address through image-set(), which takes its
 *     address as a bare string with no url() around it. Every visitor's browser then fetched it;
 *   - a ticket's description goes to v-html, and a ticket from before description_html has none
 *     stored: the raw text went instead of sanitized HTML;
 *   - {custom_N} in a slug pattern, in generated text and on graphics wrote a PRIVATE answer where
 *     visitors see it. A private field's token stays empty.
 */
class GuestMarkupGuardsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_carpool_page_guards_every_line_that_prints_another_persons_details(): void
    {
        $source = file_get_contents(resource_path('views/carpool/index.blade.php'));

        foreach (['$offer->user->email', '$offer->user->phone', "['name' => \$reviewUser->name]"] as $printed) {
            foreach (explode("\n", $source) as $line) {
                if (str_contains($line, $printed) && str_contains($line, '{{')) {
                    $this->assertStringContainsString('v-pre', $line, $printed);
                }
            }
        }
    }

    public function test_owner_css_cannot_name_an_outside_address(): void
    {
        foreach ([
            'a{background:image-set("https://outside.example/a.png" 1x)}',
            'a{background:-webkit-image-set("https://outside.example/a.png" 1x)}',
            "a{background:image-set('//outside.example/a.png' 1x)}",
        ] as $css) {
            $this->assertStringNotContainsString('outside.example', CssUtils::sanitizeCss($css), $css);
        }
    }

    public function test_ordinary_css_is_left_alone(): void
    {
        $css = '.event-title{color:#123456;font-weight:700;background:linear-gradient(90deg,#fff,#eee)} /* note */ @media (max-width:600px){.x{display:none}}';

        $this->assertSame($css, CssUtils::sanitizeCss($css));
    }

    public function test_a_ticket_description_with_no_stored_html_is_still_sanitized(): void
    {
        $event = $this->createEvent($this->createRole($this->createOwner(), 'venue'));
        $ticket = $this->createTicket($event, ['description' => 'Front rows <form action="https://outside.example/"><input name="q"></form> only']);
        DB::table('tickets')->where('id', $ticket->id)->update(['description_html' => null]);

        $description = (string) Ticket::find($ticket->id)->toData()['description'];

        $this->assertStringNotContainsString('<form', $description);
        $this->assertStringContainsString('Front rows', $description);
    }

    public function test_a_private_answer_is_not_written_into_a_slug_or_generated_text(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $role->event_custom_fields = [
            'fieldprivate' => ['name' => 'Door code', 'type' => 'string', 'index' => 3, 'private' => true],
            'fieldpublic' => ['name' => 'Room', 'type' => 'string', 'index' => 4],
        ];
        $role->save();
        $event = $this->createEvent($role->fresh(), ['name' => 'Night', 'custom_field_values' => ['fieldprivate' => 'zebra-seven', 'fieldpublic' => 'cellar']]);

        $slug = SlugPatternUtils::generateSlug('{custom_3}-{custom_4}', 'Night', null, $event, $role->fresh());
        $text = EventTextGenerator::parseInlineVariables('{custom_3} / {custom_4}', $event, $role->fresh());

        $this->assertStringNotContainsString('zebra', $slug.' '.$text);
        $this->assertStringContainsString('cellar', $slug.' '.$text, 'control: a field that is not private is still written');
    }
}
