<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use App\Models\Role;
use App\Models\Event;

class AppGuestLayout extends Component
{
    public function __construct(
        public Role $role,
        public ?Event $event = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app-guest');
    }
}
