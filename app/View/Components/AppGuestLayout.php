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
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app-guest');
    }
}
