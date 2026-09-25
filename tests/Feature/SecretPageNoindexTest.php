<?php

namespace Tests\Feature;

use App\Models\EventFeedback;
use App\Models\RoleTransfer;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The pages a secret link opens say noindex themselves, wherever they answer.
 *
 * robots.txt names these links exactly only off a schedule's own host (RobotsTxtTest): on a
 * schedule's host /feedback/{id} is also the page of an event slugged "feedback", which no rule can
 * tell from /feedback/{event_id}/{secret}. So the page's own robots tag is what keeps a forwarded
 * feedback link, a schedule handover or an unsubscribe page out of search results there.
 */
class SecretPageNoindexTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const NOINDEX = '<meta name="robots" content="noindex, nofollow">';

    private function assertNoindex(string $html, string $label): void
    {
        $end = stripos($html, '</head>');
        $this->assertNotFalse($end, $label.': the page has no </head> to cut at');
        $head = substr($html, 0, $end);

        $this->assertStringContainsString(self::NOINDEX, $head, $label);
        $this->assertSame(1, substr_count($head, 'name="robots"'), $label.': one robots tag, and no second one that says index');
    }

    public function test_the_feedback_page_is_noindex_before_and_after_feedback(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'starts_at' => Carbon::now()->subDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'feedback_enabled' => true,
        ]);
        $sale = $this->createSale($event, $role);
        $url = route('feedback.show', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);

        $this->assertNoindex($this->get($url)->assertOk()->assertViewIs('feedback.show')->getContent(), 'the feedback form');

        EventFeedback::create([
            'event_id' => $event->id,
            'sale_id' => $sale->id,
            'event_date' => $sale->event_date,
            'rating' => 5,
        ]);

        $this->assertNoindex($this->get($url)->assertOk()->assertViewIs('feedback.thank-you')->getContent(), 'the thank-you page');
    }

    public function test_the_schedule_transfer_page_is_noindex(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Handover Hall']);

        $transfer = new RoleTransfer;
        $transfer->role_id = $role->id;
        $transfer->from_user_id = $owner->id;
        $transfer->to_email = 'newowner@gmail.com';
        $transfer->save();

        $pending = $this->get(route('role.transfer.show', ['token' => $transfer->fresh()->token]))
            ->assertOk()
            ->assertSee('Handover Hall')
            ->getContent();

        $this->assertNoindex($pending, 'a pending handover');
        $this->assertNoindex($this->get(route('role.transfer.show', ['token' => 'nope']))->assertOk()->getContent(), 'an unknown token');
    }

    /** Where a signed unsubscribe link lands once it has done its work. */
    public function test_the_unsubscribe_page_is_noindex(): void
    {
        $html = $this->get(route('role.show_unsubscribe', ['email' => base64_encode('someone@gmail.com')]))->assertOk()->getContent();

        $this->assertNoindex($html, 'the unsubscribe page');
    }
}
