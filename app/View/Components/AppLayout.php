<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(
        public string $title = 'Event Schedule',
        /**
         * Opt in to the six theme palettes (Sand/Mist/Paper, Espresso/Midnight/
         * Carbon). Off by default because this layout is the shell for BOTH the
         * admin portal and the guest portal - app-guest.blade.php opens with
         * <x-app-layout> just like app-admin.blade.php. Only the admin portal
         * passes true; guest pages stay on the :root/.dark fallback so the
         * palettes cannot override a schedule owner's configured colours.
         */
        public bool $themeVariants = false,
        /**
         * What the /admin/realtime beacon reports this page as. app-guest passes 'gp' with its
         * schedule and event, app-admin passes 'ap'; a page using this shell directly
         * (getting-started, ticket views, ...) falls back to 'ap' when signed in and 'gp' when not.
         * Untyped on purpose: a component model prop that a view leaves out arrives as an EMPTY
         * model rather than null, and the beacon checks ->exists itself.
         */
        public $realtimeSurface = null,
        public $realtimeRole = null,
        public $realtimeEvent = null,
        /** False on a page that must not carry the beacon at all (the PayFast auto-post). */
        public bool $realtime = true,
        /**
         * 'dark' or 'light': the guest pages are drawn in the mode their schedule's look asks for,
         * whatever the visitor's own setting (the 2026-11 redesign, GuestTheme::pageMode()). An
         * embed's own ?dark= still wins: its owner knows the colours of the site it sits on.
         */
        public ?string $forceMode = null,
        /**
         * The owner picked a palette (not Automatic), so $forceMode is their choice and wins over
         * the light or dark a visitor carries from the first page of the visit (theme-script).
         */
        public bool $modeChosen = false,
        /** Hosts of their own that this guest page links to (GuestTheme::carryHosts()). */
        public array $carryHosts = [],
        /**
         * The page's own icon (a schedule's logo on its guest pages), printed IN PLACE of ours,
         * never after it: a link preview whose og:image it cannot use falls back to the first
         * icon a page declares, which put our logo on a schedule's shared link.
         */
        public ?string $icon = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
