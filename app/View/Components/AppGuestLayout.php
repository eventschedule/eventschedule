<?php

namespace App\View\Components;

use App\Models\Event;
use App\Models\Role;
use App\Utils\GuestSeo;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppGuestLayout extends Component
{
    public function __construct(
        public Role $role,
        public ?Event $event = null,
        public ?string $date = null,
        public array $fonts = [],
        public bool $showMobileBackground = false,
        public bool $passwordGate = false,
        public ?Role $otherRole = null,
        public bool $galleryMode = false,
        public bool $noIndex = false,
        public bool $hasInlineLangToggle = false,
        /**
         * Whether this page may carry an ad or paid promotion.
         *
         * Opt-in, and defaults to false, because seventeen views render through this layout -
         * including checkout, appointment booking, gift-card purchase, feedback and the guest
         * submission forms. Serving a competitor's ad on a checkout page would undercut the
         * schedule owner whose free tier is being monetized, so only the schedule and event
         * pages set this. Guarding instead of allow-listing would mean every future guest view
         * silently inherits ads.
         */
        public bool $adSlot = false,
        /**
         * Whether this page renders the schedule owner's announcement bar at the very top.
         *
         * Opt-in, and defaults to false, because seventeen views render through this layout.
         * The bar is a page-level notice for the schedule and event pages only - it has no
         * business sitting above a checkout, gift-card or feedback form.
         */
        public bool $bannerBar = false,
        /**
         * Whether this page carries the guest cart (floating button, panel and checkout form).
         *
         * Opt-in, and defaults to false, for the same reason as the two above: seventeen views
         * render through this layout, and most of them are task pages - checkout, appointment
         * booking and rescheduling, gift-card purchase, feedback, guest submission - where a
         * shopping cart is noise. It belongs on the browsing surfaces, the schedule and event
         * pages, which are also the only pages a refused checkout can bounce back to.
         *
         * Not merely cosmetic: the cart renders a Turnstile widget when Turnstile is active, and
         * the appointment reschedule page deliberately forces Turnstile off on its secret-bearing
         * URL.
         */
        public bool $cart = false,
        /**
         * The leading segment of the <title>, before the schedule name.
         *
         * Defaults to the event name when the page has an event. Pass it explicitly to name a page
         * that would otherwise share the schedule's own title (submit, gift card, feedback), or to
         * REPLACE the event name - the password gate passes a generic label so the tab does not
         * print the very name og:title is hiding.
         */
        public ?string $pageTitle = null,
        /**
         * The occurrence of a recurring event the URL asked for (Y-m-d), by path or by a ?date=
         * that survived viewGuest()'s guard. Never the next occurrence an undated page fills in
         * for itself: that page is the series.
         *
         * Only og:url reads it. Every dated page canonicalizes to the undated series URL, but a
         * share of next Friday's page should still open next Friday, and og:url is what Facebook
         * and the chat apps take as the share target - Google ignores it for canonicalization.
         * $date cannot stand in for it, because the undated page backfills $date too.
         */
        public ?string $occurrenceDate = null,
        /**
         * The schedule page's upcoming events, EventRepo::upcomingForGuest(): whether there are
         * any picks its title ("Upcoming Events" or "Events"), and the first few are named in its
         * meta description. Null on every page that did not look them up, which then keeps the
         * bare schedule name as its title.
         *
         * @var \Illuminate\Support\Collection<int, array{event: Event, date: string}>|null
         */
        public ?Collection $upcoming = null,
    ) {}

    /**
     * The guest page <title>.
     *
     * Deliberately carries no platform suffix. On a custom domain the schedule is the site, and the
     * string is not a link, so it earns no link equity anywhere - attribution lives on the credit
     * chip and the free-tier footer strip instead. See docs/BRANDING_MATRIX.md.
     *
     * Follows the language the page is rendered in, matching og:title, twitter:title and the
     * JSON-LD. Both hreflang variants are indexed, so each needs a title in its own language.
     *
     * A page that names itself ($pageTitle: the password gate, the gallery, the forms) keeps
     * "{page} | {schedule}". An event page and the schedule home are built by GuestSeo, which adds
     * the date and place, or the "Upcoming Events" label, while they fit.
     */
    public function guestTitle(): string
    {
        $name = $this->role->translatedName() ?: config('app.name');

        if ($this->pageTitle) {
            return $this->pageTitle.' | '.$name;
        }

        if ($this->event && $this->event->exists) {
            return GuestSeo::eventTitle($this->event, $this->role);
        }

        if ($this->upcoming !== null) {
            return GuestSeo::scheduleTitle($this->role, null, $this->upcoming->isNotEmpty());
        }

        return $name;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app-guest');
    }
}
