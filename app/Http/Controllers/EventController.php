<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;

class EventController extends Controller
{
    public function view($hash)
    {
        $event_id = base64_decode($hash);
        $event = Event::findOrFail($event_id);

        $data = [
            'event' => $event,
        ];

        return view('events/view', $data);
    }

    public function create($subdomain1, $subdomain2 = '')
    {
        $role1 = Role::subdomain($subdomain1)->firstOrFail();
        $role2 = $subdomain2 ? Role::subdomain($subdomain2)->firstOrFail() : false;

        $venue = $this->getVenue($role1, $role2);
        $vendor = $this->getVendor($role1, $role2);
        $talent = $this->getTalent($role1, $role2);
        
        if (! auth()->user()->hasRole($subdomain1)) {
            return redirect('/');
        }

        $event = new Event;

        $data = [
            'subdomain1' => $subdomain1,
            'subdomain2' => $subdomain2,
            'event' => $event,
            'venue' => $venue,
            'talent' => $talent,
            'vendor' => $vendor,
        ];

        return view('event/edit', $data);
    }

    public function store(Request $request, $subdomain1, $subdomain2 = '')
    {
        $role1 = Role::subdomain($subdomain1)->firstOrFail();
        $role2 = $subdomain2 ? Role::subdomain($subdomain2)->firstOrFail() : false;

        $venue = $this->getVenue($role1, $role2);
        $vendor = $this->getVendor($role1, $role2);
        $talent = $this->getTalent($role1, $role2);
        
        if (! auth()->user()->hasRole($subdomain1)) {
            return redirect('/');
        }

        if (! $venue) {
            $venue = new Role;
            $venue->name = $request->venue_name;
            $venue->subdomain = UrlUtils::createDomain($request->venue_name);
            $venue->email = $request->venue_email;
            $venue->type = 'venue';
            $venue->save();
        }

        $role = false;

        if ($vendor) {
            $role = $vendor;            
        } else if ($talent) {
            $role = $talent;
        } else {
            $role = new Role;
            $role->name = $request->role_name;
            $role->subdomain = UrlUtils::createDomain($request->role_name);
            $role->email = $request->role_email;
            $role->type = $request->role_type;
            $role->save();
        }

        $event = new Event;       
        $event->user_id = auth()->user()->id;
        $event->venue_id = $venue->id;
        $event->role_id = $role->id;
        $event->role = $role->type;        

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