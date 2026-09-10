<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The venue merge form on the schedule editor must not sit inside #edit-form.
 *
 * A browser ignores a <form> start tag while another form is open, then lets the inner </form>
 * close the OUTER one. So for an unclaimed venue with merge candidates, the merge form did not
 * exist in the DOM (its button's script found nothing to submit), and every field after the merge
 * section had no form owner and silently fell out of the schedule's own save.
 */
class MergeVenueFormNestingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_merge_form_stands_outside_the_schedule_form(): void
    {
        $owner = $this->createOwner();
        // Unclaimed: Role::isClaimed() needs a verified email or phone as well as an owner.
        $unclaimed = $this->createRole($owner, 'venue', ['name' => 'The Blue Room', 'email_verified_at' => null]);
        // Another venue this user edits, which makes it a merge candidate.
        $this->createRole($owner, 'venue', ['name' => 'Blue Room Annex']);
        $this->assertFalse($unclaimed->isClaimed());

        $html = $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $unclaimed->subdomain]))
            ->assertOk()
            ->assertSee('id="merge-venue-button"', false)
            ->getContent();

        // On the markup itself, not on a parser's repair of it: libxml and browsers repair a nested
        // form differently, and the browser's repair is the one that loses the fields. Script and
        // style bodies and comments are text to a browser, not tags, and the layout's inline
        // scripts mention "<form>" in their comments, so they are dropped before counting.
        $markup = preg_replace(['#<script\b[^>]*>.*?</script>#is', '#<style\b[^>]*>.*?</style>#is', '#<!--.*?-->#s'], '', $html);
        $this->assertNotNull($markup, 'the scripts could not be stripped');

        $depth = 0;
        $open = '';
        preg_match_all('#<(/?)form\b[^>]*>#i', $markup, $tags);
        foreach ($tags[0] as $i => $tag) {
            $opens = $tags[1][$i] === '';
            $depth += $opens ? 1 : -1;
            $this->assertLessThanOrEqual(1, $depth, "{$tag} opens inside {$open}");
            $this->assertGreaterThanOrEqual(0, $depth, "{$tag} closes a form that is not open");
            if ($opens) {
                $open = $tag;
            }
        }
        $this->assertSame(0, $depth);

        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        $merge = $xpath->query('//form[@id="merge-venue-form"]');
        $this->assertSame(1, $merge->length, 'the merge form exists as an element');
        for ($node = $merge->item(0)->parentNode; $node; $node = $node->parentNode) {
            $this->assertFalse(
                $node instanceof \DOMElement && $node->getAttribute('id') === 'edit-form',
                'the merge form is nested inside #edit-form'
            );
        }
        $this->assertSame(1, $xpath->query('//form[@id="merge-venue-form"]//input[@name="_token"]')->length, 'it carries its own CSRF token');

        // Its controls stay in the merge section and reach it through the form attribute.
        $this->assertSame('merge-venue-form', $xpath->query('//select[@name="target_subdomain"]')->item(0)?->getAttribute('form'));
        $this->assertSame('merge-venue-form', $xpath->query('//*[@id="merge-venue-button"]')->item(0)?->getAttribute('form'));
    }
}
