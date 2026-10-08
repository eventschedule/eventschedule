<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class MarketingLayout extends Component
{
    public function __construct(
        public string $title = 'Event Schedule - The simple way to share your event schedule',
        public string $description = 'The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.',
        /**
         * Documentation pages set this via <x-docs-page>, which pulls in the
         * docs CSS/JS bundles and the motion gate. A constructor prop rather
         * than a @stack because props resolve before anything renders, so the
         * assets land in <head> with no ordering question and no FOUC.
         */
        public bool $docs = false,
        /**
         * Set by errors/404.blade.php. An error page is served at whatever URL was requested, so
         * the tags that name "this page's URL" - the canonical, og:url, twitter:url and the
         * BreadcrumbList's last crumb - would all claim the missing URL as a real page.
         */
        public bool $errorPage = false,
        /**
         * The house style: the homepage's design language (2026-10) for another marketing page.
         * The layout then loads the typeface, wraps the page in <div id="hp"> and prints
         * marketing/partials/hp-kit, whose notes say what that does to the page's markup.
         */
        public bool $hp = false,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.marketing');
    }
}
