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

        $venue = $this->getVenue($role1, $role2);
        $vendor = $this->getVendor($role1, $role2);
        $talent = $this->getTalent($role1, $role2);
        
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

    public function store($subdomain1, $subdomain2 = '')
    {
        $role1 = Role::subdomain($subdomain1)->firstOrFail();
        $role2 = $subdomain2 ? Role::subdomain($subdomain2)->firstOrFail() : false;

        $venue = $this->getVenue($role1, $role2);
        $vendor = $this->getVendor($role1, $role2);
        $talent = $this->getTalent($role1, $role2);
        
        if (! auth()->user()->hasRole($venue->subdomain)) {
            return redirect('/');
        }

        $event = new Event;       
        $event->save();

       return redirect('/' . $subdomain1 . '/' . '');
    }

    private function getVenue($role1, $role2) 
    {
        if ($role1->isVenue()) {
            return $role1;
        } else if ($role2 && $role2->isVenue()) {
            return $role2;
        }      
    }

    private function getTalent($role1, $role2) 
    {
        if ($role1->isTalent()) {
            return $role1;
        } else if ($role2 && $role2->isTalent()) {
            return $role2;
        }      
    }

    private function getVendor($role1, $role2) 
    {
        if ($role1->isVendor()) {
            return $role1;
        } else if ($role2 && $role2->isVendor()) {
            return $role2;
        }      
    }
}