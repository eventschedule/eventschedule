<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;

class EventController extends Controller
{
    public function create($subdomain1, $subdomain2 = '')
    {
        $role1 = Role::subdomain($subdomain1)->firstOrFail();
        $role2 = $subdomain2 ? Role::subdomain($subdomain2)->firstOrFail() : false;

        $venue = false;
        $vendor = false;
        $talent = false;

        if ($role1->isVenue()) {
            $venue = $role1;
        } else if ($role2 && $role2->isVenue()) {
            $venue = $role2;
        }

        if ($role1->isVendor()) {
            $vendor = $role1;
        } else if ($role2 && $role2->isVendor()) {
            $vendor = $role2;
        }

        if ($role1->isTalent()) {
            $talent = $role1;
        } else if ($role2 && $role2->isTalent()) {
            $talent = $role2;
        }
        
        if (! auth()->user()->hasRole($venue->subdomain)) {
            return redirect('/');
        }

        $event = new Event;

        $data = [
            'event' => $event,
            'venue' => $venue,
            'talent' => $talent,
            'vendor' => $vendor,
        ];

        return view('event/edit', $data);
    }

}