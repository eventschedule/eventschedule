<?php

namespace App\View\Components;

use App\Models\Event;
use App\Models\Role;
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
         * The leading segment of the <title>, before the schedule name.
         *
         * Defaults to the event name when the page has an event. Pass it explicitly to name a page
         * that would otherwise share the schedule's own title (submit, gift card, feedback), or to
         * REPLACE the event name - the password gate passes a generic label so the tab does not
         * print the very name og:title is hiding.
         */
        public ?string $pageTitle = null,
    ) {}

    /**
     * The guest page <title>.
     *
     * Deliberately carries no platform suffix. On a custom domain the schedule is the site, and the
     * string is not a link, so it earns no link equity anywhere - attribution lives on the credit
     * chip and the free-tier footer strip instead. See docs/BRANDING_MATRIX.md.
     *
     * Uses translatedName() so the title follows the language the page is rendered in, matching
     * og:title, twitter:title and the JSON-LD. Both hreflang variants are indexed, so each needs a
     * title in its own language.
     */
    public function guestTitle(): string
    {
        $segment = $this->pageTitle
            ?: (($this->event && $this->event->exists) ? $this->event->translatedName() : null);

        $name = $this->role->translatedName() ?: config('app.name');

        return $segment ? $segment.' | '.$name : $name;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app-guest');
    }
}
