<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the public "Submit your event" page (event/guest-submit) puts in front of a visitor.
 *
 * Each test is something the page used to get wrong, or a promise its markup makes that its
 * script relies on. GuestSubmitProtectionTest holds the endpoint; this holds the page.
 *
 * The assertions use English literals rather than __(): a missing key makes __() return the key
 * name, the view renders that same key name, and an assertion written with __() passes on
 * completely unwired copy.
 */
class GuestSubmitPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function curator(array $attrs = []): Role
    {
        return $this->createCurator($this->createOwner(), $attrs + [
            'name' => 'Springfield Live',
            'accept_requests' => true,
            'require_account' => true,
            'require_approval' => true,
        ]);
    }

    private function page(Role $role, array $query = []): string
    {
        return $this->get(route('event.guest_submit', ['subdomain' => $role->subdomain] + $query))
            ->assertOk()->getContent();
    }

    // ---- told what is true ----------------------------------------------------------------------

    public function test_the_schedules_own_request_terms_are_on_the_page(): void
    {
        $html = $this->page($this->curator(['request_terms' => "Events inside city limits only.\nNo resale listings."]));

        $this->assertStringContainsString('Request Terms', $html);
        $this->assertStringContainsString("Events inside city limits only.<br />\nNo resale listings.", $html);

        // And a schedule that wrote none shows no empty panel.
        $this->assertStringNotContainsString('class="gs-terms"', $this->page($this->curator()));
    }

    public function test_it_says_what_pressing_submit_leads_to(): void
    {
        $this->assertStringContainsString('Springfield Live reviews each event before it appears.', $this->page($this->curator()));

        $instant = $this->page($this->curator(['require_approval' => false]));
        $this->assertStringContainsString('Your event appears on Springfield Live as soon as you submit.', $instant);
        $this->assertStringNotContainsString('reviews each event before it appears', $instant);
    }

    /**
     * Someone signed in has said who they are, so the rule is asked with their own schedule: one
     * on the approved list was told "reviews each event" and then "You're live". With more than
     * one schedule to post as, "as soon as you submit" is said only when it is true of them all.
     */
    public function test_a_signed_in_visitor_is_told_what_will_happen_to_their_own_schedule(): void
    {
        $member = $this->createOwner();
        $approved = $this->createRole($member, 'talent', ['name' => 'The Nightjars']);
        $curator = $this->curator(['approved_subdomains' => [$approved->subdomain]]);

        $this->assertStringContainsString('reviews each event before it appears', $this->page($curator), 'a guest is not yet anyone');

        $html = $this->actingAs($member)->page($curator);
        $this->assertStringContainsString('Your event appears on Springfield Live as soon as you submit.', $html);

        $this->createRole($member, 'talent', ['name' => 'A Second Act']);
        $html = $this->actingAs($member->fresh())->page($curator);
        $this->assertStringContainsString('Springfield Live reviews each event before it appears.', $html);
    }

    /** Nobody owns it, so nobody could review: the page must not promise that somebody will. */
    public function test_a_schedule_nobody_owns_does_not_promise_a_review(): void
    {
        $placeholder = new Role;
        $placeholder->subdomain = 'placeholder'.random_int(100, 999);
        $placeholder->type = 'venue';
        $placeholder->name = 'The Unclaimed Hall';
        $placeholder->timezone = 'America/New_York';
        $placeholder->save();

        $html = $this->page($placeholder->fresh());

        $this->assertStringContainsString('as soon as you submit', $html);
        $this->assertStringNotContainsString('reviews each event before it appears', $html);
    }

    public function test_the_terms_box_is_sent_with_a_new_account(): void
    {
        $html = $this->page($this->curator());

        $this->assertStringContainsString('v-model="acceptedTerms"', $html);
        $this->assertStringContainsString('body.terms = this.acceptedTerms;', $html);
    }

    /**
     * Submitting follows the schedule and shows it the submitter's name and email. The line that
     * says so used to sit inside the new-account branch only. Its v-if is on a WRAPPER: v-pre,
     * which the schedule's name needs, switches off every directive on its own element.
     */
    public function test_the_follow_line_is_for_everyone_who_will_be_followed(): void
    {
        $curator = $this->curator();
        $html = $this->page($curator);

        $this->assertMatchesRegularExpression(
            '/<div v-if="willFollow">\s*<p v-pre[^>]*>By submitting, Springfield Live will see your name and email/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression('/<p[^>]*v-if="[^"]*"[^>]*v-pre|<p[^>]*v-pre[^>]*v-if=/', $html);
        $this->assertStringContainsString('alreadyConnected: false,', $html);

        // Someone signed in who does not follow yet is told; someone who already does is not.
        $stranger = $this->createOwner();
        $this->assertStringContainsString('alreadyConnected: false,', $this->actingAs($stranger)->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))->getContent());

        $this->followRole($stranger, $curator);
        $this->assertStringContainsString('alreadyConnected: true,', $this->actingAs($stranger)->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))->getContent());
    }

    // ---- what it asks, and how --------------------------------------------------------------------

    /** A show at 7:15 was listed at 7:30: the half-hour list is gone, and a time is typed or picked. */
    public function test_a_time_is_typed_or_picked_to_the_minute(): void
    {
        $html = $this->page($this->curator());

        $this->assertStringNotContainsString('timeOptions', $html);
        $this->assertStringNotContainsString('<select id="submit_event_time"', $html);
        $this->assertMatchesRegularExpression('/<input id="submit_event_time" type="text"[^>]*role="combobox"/', $html);
        $this->assertMatchesRegularExpression('/<input id="submit_event_end_time" type="text"[^>]*role="combobox"/', $html);
        // A time reads left to right in every language.
        $this->assertSame(2, preg_match_all('/dir="ltr" role="combobox"/', $html));
    }

    public function test_one_image_and_three_rows(): void
    {
        $html = $this->page($this->curator());

        // One place takes the picture. The second, hidden under "Additional details", is gone.
        $this->assertSame(1, substr_count($html, 'type="file"'));
        $this->assertStringNotContainsString('additional-details-panel', $html);

        $this->assertStringContainsString('data-row="description"', $html);
        $this->assertStringContainsString('data-row="price"', $html);
        // The third row holds what a schedule chose to ask, so Vue draws it only then.
        $this->assertStringContainsString('<template v-if="hasListingRow">', $html);
        // EasyMDE is wired to the textarea once, by id: its pane hides, it is never removed.
        $this->assertMatchesRegularExpression('/<div id="submit_row_description" v-show=/', $html);
    }

    public function test_the_schedules_date_question_uses_the_date_picker(): void
    {
        $html = $this->page($this->curator(['event_custom_fields' => [
            'cf1' => ['name' => 'On sale from', 'type' => 'date', 'show_on_request' => true],
        ]]));

        $this->assertStringContainsString('data-gs-date', $html);
        $this->assertStringNotContainsString('type="date"', $html);
    }

    public function test_google_comes_before_the_email_field_and_keeps_the_language(): void
    {
        config(['services.google.client_id' => 'test-client']);
        $curator = $this->curator();

        $html = $this->page($curator, ['lang' => 'es']);

        $google = strpos($html, '/guest-submit/google');
        $email = strpos($html, 'id="account_email" type="email" v-model="userEmail" @blur="checkEmailExists" :class=');
        $this->assertNotFalse($google);
        $this->assertNotFalse($email);
        $this->assertLessThan($email, $google, 'Google is offered before the field it replaces');
        $this->assertStringContainsString('/guest-submit/google?lang=es', $html);
    }

    // ---- the emailed code, and the bar ---------------------------------------------------------

    /** The sign-up screen's six boxes, from the one stylesheet both pages include. */
    public function test_the_code_step_is_the_sign_up_screens_six_boxes(): void
    {
        $html = $this->page($this->curator());

        $this->assertMatchesRegularExpression('/<div id="code-boxes"[^>]*dir="ltr"/', $html);
        $this->assertStringContainsString('<template v-for="i in 6" :key="i">', $html);
        $this->assertMatchesRegularExpression('/<input id="verification_code" type="text" class="code-input"[^>]*autocomplete="one-time-code"/', $html);
        // No maxlength: the browser would cut a pasted "Your code is 123456" before the page can read it.
        $this->assertDoesNotMatchRegularExpression('/<input id="verification_code"[^>]*maxlength/', $html);
        $this->assertMatchesRegularExpression('/#code-boxes \.code-input \{[^}]*width: calc\(100% \+ 44px\);/', $html);

        // The same rules, not a copy, on the sign-up page.
        $signUp = $this->get(route('sign_up'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/#code-boxes \.code-input \{[^}]*width: calc\(100% \+ 44px\);/', $signUp);
        $this->assertSame(1, substr_count($signUp, '#code-boxes .code-input {'));
        $this->assertSame(1, substr_count($html, '#code-boxes .code-input {'));

        // One rendered copy each is also what two pages with a copy apiece would show. What makes
        // it one set of rules is that neither view writes them: both include the same file.
        foreach (['auth/register.blade.php', 'event/guest-submit.blade.php'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertStringContainsString("@include('partials.code-boxes-styles')", $source, $view);
            $this->assertStringNotContainsString('#code-boxes .code-input', $source, $view.' writes the rules out again');
        }
        $this->assertStringContainsString('#code-boxes .code-input {', file_get_contents(resource_path('views/partials/code-boxes-styles.blade.php')));
    }

    public function test_the_bar_and_the_honeypot_are_there(): void
    {
        $html = $this->page($this->curator());

        $this->assertStringContainsString('id="submit-bar"', $html);
        $this->assertMatchesRegularExpression('/role="status" aria-live="polite" aria-atomic="true"/', $html);
        $this->assertMatchesRegularExpression('/name="website"[^>]*v-model="honeypot"/', $html);
        $this->assertStringContainsString('website: this.honeypot,', $html);
    }

    // ---- nobody's words become the page's code ---------------------------------------------------

    /**
     * The page is a Vue mount with the runtime compiler, so a schedule's name or terms echoed into
     * it would be compiled as a template. Every place one appears inside the mount is either
     * marked v-pre or arrives as data.
     */
    public function test_a_schedules_name_and_terms_are_never_compiled_as_a_template(): void
    {
        $marker = '{{ 7 * 191 }}';
        $html = $this->page($this->curator(['name' => 'Live '.$marker, 'request_terms' => 'Terms '.$marker]));

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        $xpath = new \DOMXPath($dom);

        $unguarded = [];
        foreach ($xpath->query('//*[@id="event-submit-app"]//text()[contains(., "7 * 191")]') as $text) {
            $guarded = false;
            for ($node = $text->parentNode; $node && $node->nodeType === XML_ELEMENT_NODE; $node = $node->parentNode) {
                if ($node->hasAttribute('v-pre')) {
                    $guarded = true;
                    break;
                }
                if ($node->getAttribute('id') === 'event-submit-app') {
                    break;
                }
            }
            if (! $guarded) {
                $unguarded[] = trim(substr($text->textContent, 0, 80));
            }
        }

        $this->assertSame([], $unguarded, 'owner text inside the Vue mount without v-pre');
        // And it IS on the page: under the title and in the terms, outside the mount.
        $this->assertGreaterThan(0, $xpath->query('//*[@id="gs-page"]//text()[contains(., "7 * 191")]')->length);
    }

    // ---- the page for schedules that do not require an account ----------------------------------

    public function test_the_import_page_sends_its_terms_box_and_can_say_a_request_is_waiting(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $curator = $this->curator(['require_account' => false]);

        $html = $this->get(route('event.guest_import', ['subdomain' => $curator->subdomain]))->assertOk()->getContent();

        // The whole tag, quotes and all. A doubled quote after class= once left it with an empty
        // class and a run of stray attributes, the last of them named `focus:ring-blue-500"`:
        // Safari refuses that name, and the account box this checkbox sits in never drew.
        $this->assertMatchesRegularExpression(
            '/<input type="checkbox" :id="\'terms_\' \+ idx" v-model="acceptedTerms" required\s+class="h-4 w-4 rounded [a-z0-9:\- ]+">/',
            $html
        );
        $this->assertStringContainsString('terms: this.acceptedTerms', $html);
        $this->assertStringContainsString('<div v-if="guestSent" v-cloak', $html);
        $this->assertStringContainsString('v-text="guestSent.message"', $html);
        // It goes to the event only when the event has an address to go to.
        $this->assertStringContainsString('this.guestSent = data.event;', $html);
    }

    public function test_the_import_pages_account_needs_the_terms_where_they_are_recorded(): void
    {
        config(['app.hosted' => true]);
        $curator = $this->curator(['require_account' => false, 'require_approval' => false]);
        $body = [
            'name' => 'Jazz Night',
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 19:00:00',
            'venue_name' => 'The Blue Room',
            'create_account' => true,
            'account_name' => 'New Person',
            'account_email' => 'newperson'.random_int(10000, 99999).'@gmail.com',
            'account_password' => 'password123',
            'website' => '',
        ];
        $url = route('event.guest_import.store', ['subdomain' => $curator->subdomain]);

        $this->postJson($url, $body)->assertStatus(422)->assertJsonValidationErrors('terms');
        $this->assertSame(0, \App\Models\User::where('email', $body['account_email'])->count());

        $this->postJson($url, $body + ['terms' => true])->assertOk()->assertJsonPath('success', true);
        $this->assertNotNull(\App\Models\User::where('email', $body['account_email'])->firstOrFail()->terms_accepted_at);
    }
}
