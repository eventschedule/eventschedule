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
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.marketing');
    }
}
