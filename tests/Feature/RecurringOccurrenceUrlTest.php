<?php

namespace Tests\Feature;

use App\Models\EventComment;
use App\Models\EventPhoto;
use App\Models\EventVideo;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A date in a guest event URL must be a real occurrence of that event.
 *
 * The route only constrains the SHAPE of {date} ('\d{4}-\d{2}-\d{2}'), so before
 * RoleController::viewGuest() guarded with Event::matchesDate() every well-formed date rendered a
 * distinct, self-canonical, index,follow page carrying identical content and a synthesized
 * startDate. 1999-01-03 and 2099-12-25 both returned 200. That is an unbounded duplicate-content
 * space, and it is what Google had crawled its way into: ~164k "Crawled - currently not indexed"
 * URLs against a sitemap advertising ~5k.
 *
 * It redirects rather than 404s. Stored dates build user-facing URLs - Sale::getEventUrl() on the
 * buyer's tickets page and the owner's sales table, the Stripe cancel URL, waitlist mail, the
 * ticket-confirmation push - and they stop matching the moment an owner edits the recurrence, so a
 * 404 would break a paying customer's own confirmation link. Removing the 200 is all the crawl
 * problem needed, and a 302 does that without stranding anyone.
 *
 * It redirects to the UNDATED URL, never to getGuestUrl($subdomain). That one re-adds the series'
 * first date, and when the first date is not an occurrence either (excluded, or off the pattern)
 * the redirect pointed at the request itself: 25 of the 159 dated recurring URLs in the sitemap
 * 302'd to themselves forever. Every fixture here used to start on a real occurrence, which is how
 * this file pinned that target without ever seeing the loop.
 */
class RecurringOccurrenceUrlTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The Sunday two weeks out, at noon, so the UTC and schedule-local calendar dates agree. */
    private function nextSunday(int $addWeeks = 0): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::SUNDAY)->addWeeks(2 + $addWeeks)->setTime(12, 0);
    }

    /** Weekly event that occurs on Sundays only (days_of_week is Carbon-indexed, 0 = Sunday). */
    private function sundayEvent(array $attrs = []): array
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $start = $this->nextSunday();
        $event = $this->createRecurringEvent($role, array_merge([
            'name' => 'Sunday Yin Yoga',
            'days_of_week' => '1000000',
            'recurring_frequency' => 'weekly',
            'starts_at' => $start->format('Y-m-d H:i:s'),
        ], $attrs));

        return [$role, $event, $start];
    }

    /** A non-occurrence must 302 to the same event without the date, never 404 and never render. */
    private function assertBouncesToUndatedEvent($role, $event, string $date): void
    {
        $this->get($this->guestEventUrl($role, $event, $date))
            ->assertStatus(302)
            ->assertRedirect($event->getUndatedGuestUrl($role->subdomain));
    }

    /**
     * The URL every email, ticket and sale link is built from - getGuestUrl($subdomain), which
     * carries the series' first date - must bounce ONCE, to the undated URL, and that must render.
     */
    private function assertFirstDateBouncesOnce($role, $event, string $firstDate): void
    {
        $anchorUrl = $event->getGuestUrl($role->subdomain);
        $this->assertSame($this->guestEventUrl($role, $event, $firstDate), $anchorUrl,
            'fixture: getGuestUrl() carries the first date');
        $this->assertFalse($event->matchesDate($firstDate, $event->scheduleTimezone()),
            'fixture: the first date is not an occurrence');

        $response = $this->get($anchorUrl)->assertStatus(302);

        $this->assertNotSame($anchorUrl, $response->headers->get('Location'), 'the first-date URL redirects to itself');
        $response->assertRedirect($event->getUndatedGuestUrl($role->subdomain));

        $this->get($event->getUndatedGuestUrl($role->subdomain))->assertOk();
    }

    /**
     * Follow a redirect chain by hand, failing on a loop instead of hanging.
     *
     * followingRedirects() keeps going for as long as the response is a redirect, so against a URL
     * that 302s to itself - the loop the first-date tests below pin - it never returns.
     */
    private function followBoundedRedirects(TestResponse $response, int $maxHops = 5): TestResponse
    {
        $chain = [];

        while ($response->isRedirect()) {
            $location = $response->headers->get('Location');

            $this->assertNotContains($location, $chain, 'Redirect loop: '.implode(' -> ', [...$chain, $location]));
            $this->assertLessThan($maxHops, count($chain), 'Redirect chain too long: '.implode(' -> ', $chain));

            $chain[] = $location;
            $response = $this->get($location);
        }

        return $response;
    }

    public function test_real_occurrence_renders(): void
    {
        [$role, $event] = $this->sundayEvent();

        $this->get($this->guestEventUrl($role, $event, $this->nextSunday(2)->format('Y-m-d')))
            ->assertOk();
    }

    public function test_wrong_weekday_bounces(): void
    {
        [$role, $event] = $this->sundayEvent();

        // The Monday after a genuine occurrence: well-formed, adjacent, and not an occurrence.
        $this->assertBouncesToUndatedEvent($role, $event, $this->nextSunday(2)->addDay()->format('Y-m-d'));
    }

    public function test_date_before_the_event_starts_bounces(): void
    {
        [$role, $event] = $this->sundayEvent();

        // A Sunday, so the weekday matches - only the start date rules it out.
        $this->assertBouncesToUndatedEvent($role, $event, $this->nextSunday(-4)->format('Y-m-d'));
    }

    public function test_absurd_past_and_future_dates_bounce(): void
    {
        [$role, $event] = $this->sundayEvent();

        // The two dates that returned 200 in production. 1999-01-03 is a Sunday (so the weekday
        // matches and only the start date rejects it); 2099-12-25 is a Friday.
        $this->assertBouncesToUndatedEvent($role, $event, '1999-01-03');
        $this->assertBouncesToUndatedEvent($role, $event, '2099-12-25');
        // What strtotime() makes of an unparseable ?date=. In the path it is just another date.
        $this->assertBouncesToUndatedEvent($role, $event, '1970-01-01');
    }

    public function test_date_after_the_recurrence_ends_bounces(): void
    {
        $endsAt = $this->nextSunday(3);
        [$role, $event] = $this->sundayEvent([
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => $endsAt->format('Y-m-d'),
        ]);

        // On the end date it still occurs; the Sunday after it does not.
        $this->get($this->guestEventUrl($role, $event, $endsAt->format('Y-m-d')))->assertOk();
        $this->assertBouncesToUndatedEvent($role, $event, $this->nextSunday(4)->format('Y-m-d'));
    }

    public function test_excluded_date_bounces(): void
    {
        $skipped = $this->nextSunday(2)->format('Y-m-d');
        [$role, $event] = $this->sundayEvent(['recurring_exclude_dates' => [$skipped]]);

        $this->assertBouncesToUndatedEvent($role, $event, $skipped);
        // The following Sunday is unaffected, so the exclusion is not just breaking the event.
        $this->get($this->guestEventUrl($role, $event, $this->nextSunday(3)->format('Y-m-d')))
            ->assertOk();
    }

    public function test_non_recurring_event_only_matches_its_own_date(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $start = Carbon::now()->addDays(10)->setTime(12, 0);
        $event = $this->createEvent($role, ['starts_at' => $start->format('Y-m-d H:i:s')]);

        $this->get($this->guestEventUrl($role, $event, $start->format('Y-m-d')))->assertOk();
        $this->assertBouncesToUndatedEvent($role, $event, $start->copy()->addDay()->format('Y-m-d'));
    }

    /**
     * The regression an earlier abort(404) would have shipped: a ticket is sold for an occurrence,
     * the owner then cancels that one date (which is exactly what recurring_exclude_dates is), and
     * the buyer opens the link in their confirmation. Sale::getEventUrl() carries the stored date,
     * so it no longer matches - and it still has to reach the event.
     */
    public function test_a_ticket_holders_link_survives_the_occurrence_being_cancelled(): void
    {
        $sold = $this->nextSunday(2)->format('Y-m-d');
        [$role, $event] = $this->sundayEvent();

        $sale = $this->createSale($event, $role, ['event_date' => $sold]);
        $this->get($sale->getEventUrl())->assertOk();

        // Owner cancels that single occurrence after the sale.
        $event->recurring_exclude_dates = [$sold];
        $event->save();

        $this->get($sale->getEventUrl())
            ->assertStatus(302)
            ->assertRedirect($event->getUndatedGuestUrl($role->subdomain));
    }

    public function test_the_bounce_preserves_the_query_string(): void
    {
        [$role, $event] = $this->sundayEvent();

        // TicketController's Stripe cancel URL appends ?tickets=true to a dated event URL. Losing
        // it would drop an abandoning buyer on the event with the tickets panel closed.
        $this->get($this->guestEventUrl($role, $event, '2099-12-25').'?tickets=true')
            ->assertRedirect($event->getUndatedGuestUrl($role->subdomain).'?tickets=true');
    }

    /** The loop production had, reached by cancelling a series' very first occurrence. */
    public function test_an_excluded_first_date_bounces_once_instead_of_looping(): void
    {
        $first = $this->nextSunday()->format('Y-m-d');
        [$role, $event] = $this->sundayEvent(['recurring_exclude_dates' => [$first]]);

        $this->assertFirstDateBouncesOnce($role, $event, $first);
    }

    /** The same loop, reached by a series whose first date is not one of its weekdays. */
    public function test_a_first_date_off_the_weekly_pattern_bounces_once_instead_of_looping(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        // Starts on a Monday, runs on Sundays only: its first occurrence is the Sunday after.
        $monday = $this->nextSunday()->addDay();
        $event = $this->createRecurringEvent($role, [
            'days_of_week' => '1000000',
            'recurring_frequency' => 'weekly',
            'starts_at' => $monday->format('Y-m-d H:i:s'),
        ]);

        $this->assertFirstDateBouncesOnce($role, $event, $monday->format('Y-m-d'));
    }

    public function test_an_impossible_calendar_date_bounces_instead_of_erroring(): void
    {
        [$role, $event] = $this->sundayEvent();

        // All three pass the route's \d{4}-\d{2}-\d{2} constraint and none is a real day.
        // Carbon::parse() throws on 2026-13-45 (a 500) and silently rolls 2026-02-30 over to
        // March 2nd, which would render a page for a date that does not exist.
        foreach (['2026-13-45', '2026-02-30', '2026-00-10'] as $impossible) {
            $this->get($this->guestEventUrl($role, $event, $impossible).'?tickets=true')
                ->assertStatus(302)
                ->assertRedirect($event->getUndatedGuestUrl($role->subdomain).'?tickets=true');
        }
    }

    /** The lookup both guest controllers share must not parse an impossible date either. */
    public function test_the_event_lookup_ignores_an_impossible_date(): void
    {
        [$role, $event] = $this->sundayEvent();

        // By slug, with no id, so the lookup runs the dated branches that parse the date.
        $found = app(EventRepo::class)->getEvent($role->subdomain, $event->slug, '2026-13-45', null, $role);

        $this->assertSame($event->id, $found?->id);
    }

    /**
     * checkEventPassword() used to send every attempt to getGuestUrl($subdomain), the first-date
     * URL, so on a series whose first date is gone the error was flashed onto a URL that looped.
     */
    public function test_a_wrong_password_still_shows_its_error_when_the_first_date_is_gone(): void
    {
        $first = $this->nextSunday()->format('Y-m-d');
        [$role, $event] = $this->sundayEvent([
            'recurring_exclude_dates' => [$first],
            'event_password' => 'letmein',
        ]);

        $response = $this->post(route('event.check_password', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'password' => 'wrong',
        ]);

        $this->followBoundedRedirects($response)
            ->assertOk()
            ->assertSee(__('messages.incorrect_password'));
    }

    /**
     * The prompt carries the occurrence the visitor opened, and the answer returns them to it -
     * but only while it still IS an occurrence, so a stale or forged date cannot start a bounce.
     */
    public function test_the_password_prompt_returns_the_visitor_to_the_occurrence_they_opened(): void
    {
        [$role, $event] = $this->sundayEvent(['event_password' => 'letmein']);
        $occurrence = $this->nextSunday(2)->format('Y-m-d');
        $occurrenceUrl = $this->guestEventUrl($role, $event, $occurrence);
        $checkUrl = route('event.check_password', ['subdomain' => $role->subdomain]);
        $eventId = UrlUtils::encodeId($event->id);

        $this->get($occurrenceUrl)
            ->assertOk()
            ->assertSee('<input type="hidden" name="date" value="'.$occurrence.'">', false);

        // An undated URL shows the next occurrence, but the visitor did not ask for that one, so
        // the prompt posts no date and they go back to the undated URL. Same for a ?date= that was
        // dropped for not being one: the page falls back to the next occurrence, still unasked.
        foreach (['', '?date=nonsense', '?date=2099-12-25'] as $query) {
            $this->get($this->guestEventUrl($role, $event).$query)
                ->assertOk()
                ->assertDontSee('<input type="hidden" name="date"', false);
        }

        // The legacy ?date= form names an occurrence just as the path does.
        $this->get($this->guestEventUrl($role, $event).'?date='.$occurrence)
            ->assertOk()
            ->assertSee('<input type="hidden" name="date" value="'.$occurrence.'">', false);

        $this->post($checkUrl, ['event_id' => $eventId, 'password' => 'wrong', 'date' => $occurrence])
            ->assertRedirect($occurrenceUrl)
            ->assertSessionHas('password_error', true);

        foreach (['2099-12-25', '2026-13-45', 'nonsense'] as $notAnOccurrence) {
            $this->post($checkUrl, ['event_id' => $eventId, 'password' => 'wrong', 'date' => $notAnOccurrence])
                ->assertRedirect($event->getUndatedGuestUrl($role->subdomain));
        }

        $this->post($checkUrl, ['event_id' => $eventId, 'password' => 'letmein', 'date' => $occurrence])
            ->assertRedirect($occurrenceUrl);

        $this->get($occurrenceUrl)->assertOk()->assertSee('Sunday Yin Yoga');
    }

    /**
     * EventController::submitComment() (and the video and photo siblings) return the poster to the
     * occurrence the form posted. With none, they land on the undated series: never on
     * getGuestUrl($subdomain)'s first date, which on a series whose first date is gone bounced them
     * once more.
     */
    public function test_a_confirmation_posted_without_a_date_lands_on_the_undated_series(): void
    {
        $first = $this->nextSunday()->format('Y-m-d');
        [$role, $event] = $this->sundayEvent([
            'recurring_exclude_dates' => [$first],
            'fan_comments_enabled' => true,
        ]);

        $response = $this->actingAs($this->createOwner())
            ->post(route('event.submit_comment', [
                'subdomain' => $role->subdomain,
                'event_hash' => UrlUtils::encodeId($event->id),
            ]), ['comment' => 'Lovely class']);

        $response->assertRedirect($event->getUndatedGuestUrl($role->subdomain));

        $this->get($event->getUndatedGuestUrl($role->subdomain))
            ->assertOk()
            ->assertSee(__('messages.comment_submitted'));
    }

    /**
     * A flash can still arrive on a dated URL whose date has stopped being an occurrence - a stored
     * link, followed after the owner excluded its date. The bounce keeps it for the page after it,
     * the one that shows it.
     */
    public function test_a_flash_survives_the_bounce_off_a_date_that_is_no_occurrence(): void
    {
        $first = $this->nextSunday()->format('Y-m-d');
        [$role, $event] = $this->sundayEvent(['recurring_exclude_dates' => [$first]]);

        // Flashed by the request before this one, so this is already its last request.
        $this->withSession(['message' => 'Saved for later', '_flash' => ['old' => ['message'], 'new' => []]])
            ->get($this->guestEventUrl($role, $event, $first))
            ->assertRedirect($event->getUndatedGuestUrl($role->subdomain));

        $this->get($event->getUndatedGuestUrl($role->subdomain))
            ->assertOk()
            ->assertSee('Saved for later');
    }

    /**
     * A post goes back to the occurrence it was posted from: it is stored under that date, and the
     * page lists the poster's pending items by date, so the first date the confirmation used to land
     * on did not show what they had just posted.
     */
    public function test_a_post_returns_to_the_occurrence_it_was_posted_from(): void
    {
        Storage::fake(config('filesystems.default'));

        [$role, $event] = $this->sundayEvent([
            'fan_comments_enabled' => true,
            'fan_videos_enabled' => true,
            'fan_photos_enabled' => true,
        ]);
        $occurrence = $this->nextSunday(2)->format('Y-m-d');
        $dated = $this->guestEventUrl($role, $event, $occurrence);
        $this->actingAs($this->createOwner());

        $pending = [];

        foreach ($this->fanPosts() as $type => [$route, $payload, $model]) {
            $response = $this->post(route($route, [
                'subdomain' => $role->subdomain,
                'event_hash' => UrlUtils::encodeId($event->id),
            ]), $payload + ['event_date' => $occurrence]);

            $row = $model::where('event_id', $event->id)->latest('id')->firstOrFail();
            $this->assertSame($occurrence, $row->event_date, "fixture: the {$type} is stored under its occurrence");

            $response->assertRedirect($dated)
                ->assertSessionHas('message')
                ->assertSessionHas('scroll_to', "pending-{$type}-{$row->id}");

            $pending[] = "id=\"pending-{$type}-{$row->id}\"";
        }

        $page = $this->get($dated)->assertOk();

        foreach ($pending as $marker) {
            $page->assertSee($marker, false);
        }
    }

    /** A date the event no longer has, or never had, lands on the undated series. */
    public function test_a_post_from_a_date_that_is_no_occurrence_returns_to_the_undated_series(): void
    {
        $skipped = $this->nextSunday(2)->format('Y-m-d');
        [$role, $event] = $this->sundayEvent([
            'recurring_exclude_dates' => [$skipped],
            'fan_comments_enabled' => true,
        ]);
        $url = route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($event->id)]);
        $this->actingAs($this->createOwner());

        // Excluded, and a Monday of a Sundays-only series.
        foreach ([$skipped, $this->nextSunday(2)->addDay()->format('Y-m-d')] as $date) {
            $this->post($url, ['comment' => 'Lovely class', 'event_date' => $date])
                ->assertRedirect($event->getUndatedGuestUrl($role->subdomain));
        }
    }

    /** A photo posted from a dated gallery goes back to that gallery. */
    public function test_a_gallery_post_returns_to_that_occurrences_gallery(): void
    {
        Storage::fake(config('filesystems.default'));

        [$role, $event] = $this->sundayEvent(['fan_photos_enabled' => true]);
        $occurrence = $this->nextSunday(2)->format('Y-m-d');
        $gallery = $event->getPhotoGalleryUrl($role->subdomain, $occurrence);

        $this->actingAs($this->createOwner())
            ->post(route('event.submit_photo', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($event->id)]), [
                'photo' => UploadedFile::fake()->image('class.jpg', 400, 300),
                'event_date' => $occurrence,
                'return_to' => 'gallery',
            ])
            ->assertRedirect($gallery)
            ->assertSessionHas('message', __('messages.photo_submitted'));

        $this->get($gallery)->assertOk();
    }

    /**
     * A schedule that asks posters to sign in first keeps the post in the session until they have,
     * then HomeController files it and sends them back: to the occurrence they posted from too.
     */
    public function test_the_deferred_flow_lands_on_the_occurrence_it_was_posted_from(): void
    {
        Storage::fake(config('filesystems.default'));

        [$role, $event] = $this->sundayEvent([
            'fan_comments_enabled' => true,
            'fan_videos_enabled' => true,
            'fan_photos_enabled' => true,
        ]);
        $role->update(['fan_content_require_account' => true]);
        $occurrence = $this->nextSunday(2)->format('Y-m-d');
        $poster = $this->createOwner();

        $posts = $this->fanPosts();
        $posts['gallery'] = ['event.submit_photo', ['photo' => UploadedFile::fake()->image('stage.jpg', 400, 300), 'return_to' => 'gallery'], EventPhoto::class];

        foreach ($posts as $type => [$route, $payload, $model]) {
            auth()->logout();

            $this->post(route($route, [
                'subdomain' => $role->subdomain,
                'event_hash' => UrlUtils::encodeId($event->id),
            ]), $payload + ['event_date' => $occurrence])->assertRedirect();
            $this->assertNotNull(session('pending_fan_content'), "fixture: the {$type} waits for an account");

            $expected = $type === 'gallery'
                ? $event->getPhotoGalleryUrl($role->subdomain, $occurrence)
                : $this->guestEventUrl($role, $event, $occurrence);

            $this->actingAs($poster)->get(route('home'))->assertRedirect($expected);
            $this->assertSame($occurrence, $model::where('event_id', $event->id)->latest('id')->value('event_date'), $type);
        }
    }

    /**
     * The schedule page's <noscript> list is what a crawler without JavaScript sees, so it links
     * each series once, at its undated URL - never at a first date that may bounce.
     */
    public function test_the_noscript_list_links_a_series_at_its_undated_url(): void
    {
        $first = $this->nextSunday()->format('Y-m-d');
        [$role, $event] = $this->sundayEvent(['recurring_exclude_dates' => [$first]]);

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertSame(1, preg_match('#<noscript v-pre>(.*?)</noscript>#s', $html, $m),
            'fixture: the schedule page renders its noscript event list');
        $noscript = $m[1];

        $this->assertStringContainsString('Sunday Yin Yoga', $noscript, 'fixture: the series is listed');
        $this->assertStringContainsString('href="'.$event->getUndatedGuestUrl($role->subdomain).'"', $noscript);
        $this->assertStringNotContainsString('href="'.$event->getGuestUrl($role->subdomain).'"', $noscript);
    }

    public function test_query_param_date_is_dropped_rather_than_bounced(): void
    {
        [$role, $event] = $this->sundayEvent();

        // The query form is not a crawlable, self-canonical URL, and a malformed ?date= must not
        // break an otherwise valid event page. So it renders - but the non-occurrence must not
        // survive into the page's URLs: og:url names the occurrence a dated page is about, and
        // would advertise a URL that redirects away.
        $content = $this->get($this->guestEventUrl($role, $event).'?date=2099-12-25')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('2099-12-25', $content);
    }

    public function test_unparseable_query_date_does_not_break_the_event(): void
    {
        [$role, $event] = $this->sundayEvent();

        // date('Y-m-d', strtotime('nonsense')) is '1970-01-01'. That is an artifact of the parse
        // failing, not a date anyone asked for, so the event still renders.
        $this->get($this->guestEventUrl($role, $event).'?date=nonsense')->assertOk();
    }

    /**
     * One post of each kind: the route, what it posts, and the row it makes.
     *
     * @return array<string, array{0: string, 1: array<string, mixed>, 2: class-string}>
     */
    private function fanPosts(): array
    {
        return [
            'comment' => ['event.submit_comment', ['comment' => 'Lovely class'], EventComment::class],
            'video' => ['event.submit_video', ['youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'], EventVideo::class],
            'photo' => ['event.submit_photo', ['photo' => UploadedFile::fake()->image('class.jpg', 400, 300)], EventPhoto::class],
        ];
    }
}
