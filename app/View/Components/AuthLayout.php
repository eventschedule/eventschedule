<?php

namespace App\View\Components;

use App\Models\Role;
use Illuminate\View\Component;
use Illuminate\View\View;

class AuthLayout extends Component
{
    /**
     * $schedule: the schedule whose own mail led here (its confirm, manage and unsubscribe
     * links). The page then stands under that schedule's name and logo, not the platform's:
     * somebody who follows a venue's newsletter and presses a link in it has never heard of
     * us. Null everywhere else, which is every page the platform itself speaks on.
     */
    public function __construct(public ?Role $schedule = null) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.auth');
    }
}
