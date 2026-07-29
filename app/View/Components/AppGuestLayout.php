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
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app-guest');
    }
}
