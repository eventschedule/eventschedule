<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventVideo;
use App\Models\Role;
use App\Utils\SeoUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The fan-photo gallery (/{slug}/{id}[/{date}]/photos) owes the same URL guarantees as the event
 * page it hangs off - see RecurringOccurrenceUrlTest - and used to give none of them:
 *
 *  - Every well-formed date returned 200 with a self-canonical, so a series had an unbounded
 *    number of gallery URLs, and 2026-13-45 reached Carbon::parse() and 500'd.
 *  - The undated gallery picked its date with a `while (true)` over days_of_week, which never
 *    ends on '0000000' and answers "today" for a monthly event (EventRepo::saveEvent() stores
 *    '1111111' for every non-weekly frequency).
 *  - It printed the event's full Event JSON-LD, so the gallery competed with the event page for
 *    the same rich result.
 *  - Every dated gallery canonicalized to the undated one, which shows the NEXT occurrence's
 *    photos, a different night every week, and named that next night in its description. A dated
 *    gallery is its own canonical now, with its date in its title, and a gallery with no photo to
 *    show is noindex.
 */
class PhotoGalleryUrlTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The Sunday two weeks out, at noon, so the UTC and schedule-local calendar dates agree. */
    private function nextSunday(int $addWeeks = 0): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::SUNDAY)->addWeeks(2 + $addWeeks)->setTime(12, 0);
    }

    /** Weekly event that occurs on Sundays only, with fan photos on. */
    private function sundayEvent(array $attrs = []): array
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createRecurringEvent($role, array_merge([
            'name' => 'Sunday Yin Yoga',
            'days_of_week' => '1000000',
            'recurring_frequency' => 'weekly',
            'starts_at' => $this->nextSunday()->format('Y-m-d H:i:s'),
            'fan_photos_enabled' => true,
            'creator_role_id' => $role->id,
        ], $attrs));

        return [$role, $event];
    }

    /** /{subdomain}/{slug}/{id}[/{date}]/photos - the gallery routes are unnamed. */
    private function galleryUrl($role, $event, ?string $date = null): string
    {
        return $this->guestEventUrl($role, $event, $date).'/photos';
    }

    /** The @type of every JSON-LD block on the page, each one decoded rather than string-matched. */
    private function jsonLdTypes(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $m);

        return array_map(function ($block) {
            $decoded = json_decode(trim($block), true);
            $this->assertIsArray($decoded, 'every JSON-LD block decodes');

            return $decoded['@type'] ?? null;
        }, $m[1]);
    }

    private function linkHref(string $html, string $pattern): ?string
    {
        return preg_match($pattern, $html, $m) ? html_entity_decode($m[1]) : null;
    }

    private function robots(string $html): ?string
    {
        return $this->linkHref($html, '#<meta name="robots" content="([^"]*)">#');
    }

    private function metaContent(string $html, string $attribute, string $name): ?string
    {
        return $this->linkHref($html, '#<meta '.$attribute.'="'.preg_quote($name, '#').'" content="([^"]*)">#');
    }

    /** An approved photo, pinned to one night of a series when $night is given. */
    private function photo(Event $event, ?string $night, string $path, bool $approved = true, ?int $userId = null): EventPhoto
    {
        return EventPhoto::create([
            'event_id' => $event->id,
            'user_id' => $userId ?? $event->user_id,
            'event_date' => $night,
            'photo_url' => $path,
            'is_approved' => $approved,
        ]);
    }

    public function test_a_non_occurrence_date_bounces_to_the_undated_gallery_keeping_the_query(): void
    {
        [$role, $event] = $this->sundayEvent();
        $undatedGallery = $event->getUndatedGuestUrl($role->subdomain).'/photos';

        // A Monday: well-formed, and not an occurrence of a Sundays-only series.
        $monday = $this->nextSunday(2)->addDay()->format('Y-m-d');

        // Already in key order: getQueryString() normalizes, exactly as the event page's bounce does.
        foreach ([$monday, '2099-12-25', '2026-13-45', '2026-02-30'] as $notAnOccurrence) {
            $this->get($this->galleryUrl($role, $event, $notAnOccurrence).'?foo=1&utm_source=newsletter')
                ->assertStatus(302)
                ->assertRedirect($undatedGallery.'?foo=1&utm_source=newsletter');
        }

        $this->get($undatedGallery)->assertOk();
    }

    public function test_a_real_occurrence_renders(): void
    {
        [$role, $event] = $this->sundayEvent();
        $occurrence = $this->nextSunday(2)->format('Y-m-d');

        $this->get($this->galleryUrl($role, $event, $occurrence))
            ->assertOk()
            ->assertViewHas('date', $occurrence)
            ->assertViewHas('occurrenceDate', $occurrence);
    }

    /**
     * Unchecking every day on a weekly event stores '0000000'. The undated gallery used to scan
     * forward for a matching weekday with no bound, so this request never returned.
     */
    public function test_an_undated_gallery_of_a_series_with_no_weekdays_renders(): void
    {
        [$role, $event] = $this->sundayEvent(['days_of_week' => '0000000']);

        // A regression would spin forever; fail the run instead of hanging it.
        set_time_limit(60);

        try {
            $html = $this->get($this->galleryUrl($role, $event))
                ->assertOk()
                ->assertViewHas('date', null)
                ->getContent();
        } finally {
            set_time_limit(0);
        }

        // With no occurrence in view the page is the undated gallery, and links as one: handed
        // null instead of false, getGuestUrl() would fall back to the first date, which a series
        // with no weekdays never occurs on.
        $this->assertSame(
            $event->getUndatedGuestUrl($role->subdomain).'/photos',
            $this->linkHref($html, '#<link rel="canonical" href="([^"]*)"#')
        );
        $this->assertStringContainsString('href="'.$event->getUndatedGuestUrl($role->subdomain).'"', $html,
            'the back link returns to the undated event page');
    }

    /**
     * The password gate and the fan-photos-off redirect send the visitor to the event page. With no
     * date they went to getGuestUrl($subdomain, null) - the first date - which on a series whose
     * first date is gone redirected to itself.
     */
    public function test_the_gallery_gates_send_a_series_to_its_undated_event_page(): void
    {
        $first = $this->nextSunday()->format('Y-m-d');
        $occurrence = $this->nextSunday(2)->format('Y-m-d');

        [$role, $event] = $this->sundayEvent([
            'recurring_exclude_dates' => [$first],
            'event_password' => 'letmein',
        ]);
        $undated = $event->getUndatedGuestUrl($role->subdomain);

        $this->get($this->galleryUrl($role, $event))->assertRedirect($undated);
        // A real occurrence keeps its date; one that is not goes straight to the undated page
        // rather than through a dated URL that would bounce a second time.
        $this->get($this->galleryUrl($role, $event, $occurrence))
            ->assertRedirect($this->guestEventUrl($role, $event, $occurrence));
        $this->get($this->galleryUrl($role, $event, $first))->assertRedirect($undated);

        [$role, $event] = $this->sundayEvent([
            'recurring_exclude_dates' => [$first],
            'fan_photos_enabled' => false,
        ]);

        $this->get($this->galleryUrl($role, $event))->assertRedirect($event->getUndatedGuestUrl($role->subdomain));
    }

    /**
     * The API delete, unfollow and merge paths soft-delete a schedule WITHOUT renaming it, so its
     * subdomain still resolves. The event page stopped serving those rows; the gallery had not.
     */
    public function test_a_deleted_schedules_gallery_is_not_served(): void
    {
        [$role, $event] = $this->sundayEvent();
        $this->get($this->galleryUrl($role, $event))->assertOk();

        Role::whereKey($role->id)->update(['is_deleted' => true]);

        // A 404, as on the event page, not the redirect to the app a crawler files as a soft 404.
        $this->get($this->galleryUrl($role, $event))->assertNotFound();
    }

    /**
     * A monthly event is stored with days_of_week '1111111', so a weekday scan answers TODAY, a day
     * it does not occur on - and that date is what the upload form posts as event_date.
     */
    public function test_a_monthly_series_gallery_opens_on_its_real_next_occurrence(): void
    {
        // Ten days out, so "the next occurrence" and "today" can never coincide.
        $start = Carbon::now()->addDays(10)->setTime(12, 0);
        [$role, $event] = $this->sundayEvent([
            'days_of_week' => '1111111',
            'recurring_frequency' => 'monthly_date',
            'starts_at' => $start->format('Y-m-d H:i:s'),
        ]);

        // The photos are that night's, but the page is the series gallery: the date is filled in,
        // not asked for. A closure, because assertViewHas() handed null only checks the key.
        $this->get($this->galleryUrl($role, $event))
            ->assertOk()
            ->assertViewHas('date', $start->format('Y-m-d'))
            ->assertViewHas('occurrenceDate', fn ($occurrenceDate) => $occurrenceDate === null);
    }

    /**
     * The gallery is ABOUT the event, not the event itself. Two pages carrying the same Event node
     * compete for one rich result, and a VideoObject for the event's videos belongs on the page
     * that plays them. The event page keeps all of it.
     */
    public function test_the_gallery_carries_no_event_schedule_or_video_structured_data(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Gallery Night', 'fan_photos_enabled' => true]);
        EventVideo::create([
            'event_id' => $event->id,
            'user_id' => $role->user_id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_approved' => true,
        ]);

        $eventTypes = $this->jsonLdTypes($this->get($this->guestEventUrl($role, $event))->assertOk()->getContent());
        $this->assertContains('Event', $eventTypes, 'fixture: the event page carries the Event node');
        $this->assertContains('VideoObject', $eventTypes, 'fixture: the event page carries the VideoObject');

        $galleryTypes = $this->jsonLdTypes($this->get($this->galleryUrl($role, $event))->assertOk()->getContent());

        foreach (['Event', 'VideoObject', 'EventVenue', 'Organization', 'Person'] as $type) {
            $this->assertNotContains($type, $galleryTypes, "the gallery must not emit a {$type} node");
        }

        // An event with no date yet fails the layout's Event condition and falls to the schedule
        // branch, which is how a gallery printed the schedule's node (this venue's EventVenue)
        // instead.
        $tba = $this->createEvent($role, ['name' => 'Date TBA', 'fan_photos_enabled' => true]);
        Event::whereKey($tba->id)->update(['starts_at' => null]);

        $tbaTypes = $this->jsonLdTypes($this->get($this->galleryUrl($role, $tba))->assertOk()->getContent());
        $this->assertNotContains('EventVenue', $tbaTypes, 'the gallery must not emit the schedule node');
        $this->assertContains('BreadcrumbList', $tbaTypes, 'fixture: the page still renders its JSON-LD');
    }

    public function test_an_invalid_language_is_dropped_without_losing_the_rest_of_the_query(): void
    {
        [$role, $event] = $this->sundayEvent();
        $gallery = $this->galleryUrl($role, $event);

        $this->get($gallery.'?lang=xx&foo=1')
            ->assertRedirect($gallery.'?foo=1');

        // ?lang[]=x is an array, which is_valid_language_code()'s ?string signature cannot take.
        $this->get($gallery.'?lang[]=en')->assertOk();
    }

    /** The gallery's language alternates and canonical describe the GALLERY, not the event page. */
    public function test_the_gallery_hreflang_and_canonical_point_at_the_gallery(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'language_code' => 'en',
            'translation_language_code' => 'es',
        ]);
        $event = $this->createEvent($role, ['name' => 'Gallery Night', 'fan_photos_enabled' => true]);
        $gallery = $event->getCanonicalPhotoGalleryUrl();

        $this->assertStringEndsWith('/photos', $gallery, 'fixture: a one-off event has one gallery URL');

        $html = $this->get($this->galleryUrl($role, $event).'?lang=es')->assertOk()->getContent();

        $this->assertSame($gallery.'?lang=es', $this->linkHref($html, '#<link rel="canonical" href="([^"]*)"#'));
        $this->assertSame($gallery.'?lang=es', $this->linkHref($html, '#<link rel="alternate" hreflang="es" href="([^"]*)"#'));
        $this->assertSame($gallery, $this->linkHref($html, '#<link rel="alternate" hreflang="en" href="([^"]*)"#'));
        $this->assertSame($gallery, $this->linkHref($html, '#<link rel="alternate" hreflang="x-default" href="([^"]*)"#'));

        // The primary language keeps the clean URL. flushSession(): ?lang=es remembered the
        // translation in the session, which a returning visitor's clean URL would follow.
        $this->flushSession();
        $html = $this->get($this->galleryUrl($role, $event))->assertOk()->getContent();
        $this->assertSame($gallery, $this->linkHref($html, '#<link rel="canonical" href="([^"]*)"#'));
    }

    /**
     * A dated gallery shows its own night's photos, so it is its own canonical and is indexed. The
     * undated gallery fills in the series' next occurrence - nextSunday(0), its first date - which
     * has no photo here, so it shows none and is noindex.
     */
    public function test_a_dated_gallery_is_its_own_indexed_canonical_and_an_empty_one_is_noindex(): void
    {
        [$role, $event] = $this->sundayEvent();
        $earlier = $this->nextSunday(1)->format('Y-m-d');
        $night = $this->nextSunday(2)->format('Y-m-d');
        $this->photo($event, $earlier, 'photos/earlier.jpg');
        $this->photo($event, $night, 'photos/night.jpg');

        $dated = $this->galleryUrl($role, $event, $night);
        $html = $this->get($dated)->assertOk()->getContent();

        $this->assertSame($dated, $this->linkHref($html, '#<link rel="canonical" href="([^"]*)"#'));
        $this->assertSame(SeoUtils::ROBOTS_INDEX, $this->robots($html));
        $this->assertStringContainsString('photos/night.jpg', $html);
        // Not in the grid, and not as the preview image either: og:image used to be the event's
        // first photo of ANY night.
        $this->assertStringNotContainsString('photos/earlier.jpg', $html);
        $this->assertSame(url('/storage/photos/night.jpg'), $this->metaContent($html, 'property', 'og:image'));

        $html = $this->get($this->galleryUrl($role, $event))->assertOk()->getContent();

        $this->assertSame('noindex, nofollow', $this->robots($html));
        $this->assertSame($this->galleryUrl($role, $event), $this->linkHref($html, '#<link rel="canonical" href="([^"]*)"#'));
    }

    /** A pending photo is its poster's alone, so a gallery holding nothing else is still empty. */
    public function test_a_gallery_with_only_a_pending_photo_is_noindex_until_it_is_approved(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Gallery Night', 'fan_photos_enabled' => true]);
        $fan = $this->createOwner();
        $photo = $this->photo($event, null, 'photos/pending.jpg', approved: false, userId: $fan->id);
        $gallery = $this->galleryUrl($role, $event);

        // The poster sees their own pending photo, and the page is still empty to everyone else.
        $html = $this->actingAs($fan)->get($gallery)->assertOk()->getContent();
        $this->assertStringContainsString('photos/pending.jpg', $html, 'fixture: the poster sees it');
        $this->assertSame('noindex, nofollow', $this->robots($html));

        $photo->update(['is_approved' => true]);

        $this->assertSame(SeoUtils::ROBOTS_INDEX, $this->robots($this->get($gallery)->assertOk()->getContent()));
    }

    /**
     * The undated gallery is the series gallery: its description used to name the next occurrence
     * it fills in, a date that changes every week. A dated gallery names its own night, in the
     * description and in the title, which every dated gallery otherwise shared.
     */
    public function test_only_a_dated_gallery_names_a_date(): void
    {
        [$role, $event] = $this->sundayEvent();
        $next = $event->nextOccurrenceFrom();
        $night = $this->nextSunday(2)->format('Y-m-d');
        $day = fn (string $date) => Carbon::parse($date)->locale('en')->isoFormat('ddd, ll');
        $page = 'Sunday Yin Yoga - '.__('messages.photo_gallery');

        $html = $this->get($this->galleryUrl($role, $event))->assertOk()->assertViewHas('date', $next)->getContent();

        foreach ([['property', 'og:description'], ['name', 'twitter:description']] as [$attribute, $name]) {
            $this->assertNotNull($this->metaContent($html, $attribute, $name), "fixture: {$name} is there");
            $this->assertStringNotContainsString($day($next), $this->metaContent($html, $attribute, $name), $name);
        }
        $this->assertSame($page.' | Test Schedule', $this->linkHref($html, '#<title>([^<]*)</title>#'));

        $html = $this->get($this->galleryUrl($role, $event, $night))->assertOk()->getContent();
        $label = Carbon::parse($night)->locale('en')->isoFormat('ll');

        $this->assertStringContainsString($day($night), $this->metaContent($html, 'property', 'og:description'));
        $this->assertSame("{$page} - {$label} | Test Schedule", $this->linkHref($html, '#<title>([^<]*)</title>#'));
        $this->assertSame("{$page} - {$label}", $this->metaContent($html, 'name', 'description'));
        $this->assertSame("{$page} - {$label}", $this->metaContent($html, 'property', 'og:title'));

        // A one-off event has one gallery whatever its URL says, so its own date adds nothing.
        $oneOff = $this->createEvent($role, ['name' => 'Gallery Night', 'fan_photos_enabled' => true]);
        $own = $oneOff->getStartDateTime(null, true)->format('Y-m-d');

        $html = $this->get($this->guestEventUrl($role, $oneOff, $own).'/photos')->assertOk()->assertViewHas('occurrenceDate', $own)->getContent();
        $this->assertSame('Gallery Night - '.__('messages.photo_gallery').' | Test Schedule', $this->linkHref($html, '#<title>([^<]*)</title>#'));
        $this->assertSame($this->galleryUrl($role, $oneOff), $this->linkHref($html, '#<link rel="canonical" href="([^"]*)"#'));
    }

    /**
     * The event page and its gallery link each other by the date the URL asked for. The undated
     * series page linked the next occurrence's gallery, a page of its own that changed every week,
     * and the undated gallery's back link went to that occurrence's page rather than the series.
     */
    public function test_the_event_page_and_the_gallery_link_each_other_by_the_date_asked_for(): void
    {
        [$role, $event] = $this->sundayEvent();
        $next = $event->nextOccurrenceFrom();
        $night = $this->nextSunday(2)->format('Y-m-d');
        // Undated, so it shows on every night and the event page offers its gallery link.
        $this->photo($event, null, 'photos/any-night.jpg');

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.$this->galleryUrl($role, $event).'"', $html);
        $this->assertStringNotContainsString('href="'.$this->galleryUrl($role, $event, $next).'"', $html);

        $html = $this->get($this->guestEventUrl($role, $event, $night))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.$this->galleryUrl($role, $event, $night).'"', $html);

        $html = $this->get($this->galleryUrl($role, $event))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.$this->guestEventUrl($role, $event).'"', $html);
        $this->assertStringNotContainsString('href="'.$this->guestEventUrl($role, $event, $next).'"', $html);

        $html = $this->get($this->galleryUrl($role, $event, $night))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.$this->guestEventUrl($role, $event, $night).'"', $html);
    }
}
