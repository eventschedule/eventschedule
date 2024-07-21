<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class EventController extends Controller
{
    public function edit(Request $request, $subdomain, $hash)
    {
        $event_id = base64_decode($hash);
        $event = Event::findOrFail($event_id);

        $venueSubdomain = $event->venue->subdomain;
        $roleSubdomain = $event->role->subdomain;

        $data = [
            'event' => $event,
            'subdomain1' => $subdomain,
            'subdomain2' => $subdomain == $venueSubdomain ? $roleSubdomain : $venueSubdomain,
            'venue' => $event->venue,
            'talent' => $event->role->type == 'talent' ? $event->role : false,
            'vendor' => $event->role->type == 'vendor' ? $event->role : false,
        ];

        return view('event/edit', $data);
    }

    public function create(Request $request, $subdomain1, $subdomain2 = '')
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
        if ($request->date) {
            $event->starts_at = $request->date . ' 20:00';
        }

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

    public function update(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = base64_decode($hash);
        $event = Event::findOrFail($event_id);
        $event->fill($request->all());

        $timezone = auth()->user()->timezone;
        $event->starts_at = Carbon::createFromFormat('Y-m-d H:i', $request->starts_at, $timezone)
            ->setTimezone('UTC')
            ->format('Y-m-d H:i');
        
        $event->save();
        
        return redirect('/' . $subdomain . '/schedule');
    }

    public function accept(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = base64_decode($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = true;
        $event->save();
        
        return redirect('/' . $subdomain . '/requests');
    }

    public function decline(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = base64_decode($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = false;
        $event->save();
        
        return redirect('/' . $subdomain . '/requests');
    }

    /*
    public function delete(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = base64_decode($hash);
        $event = Event::findOrFail($event_id);        
        $event->delete();
        
        return redirect('/' . $subdomain . '/schedule');
    }
    */

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
            $venue = Role::whereType('venue')->whereEmail($request->venue_email)->first();
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

            $role = Role::whereType($request->role_type)->whereEmail($request->role_email)->first();

            if (! $role) {
                $role = new Role;
                $role->name = $request->role_name;
                $role->subdomain = UrlUtils::createDomain($request->role_name);
                $role->email = $request->role_email;
                $role->type = $request->role_type;
                $role->save();            
            }
        }

        $event = new Event;       
        $event->fill($request->all());
        $event->user_id = auth()->user()->id;
        $event->venue_id = $venue->id;
        $event->role_id = $role->id;

        if ($event->starts_at) {
            $timezone = auth()->user()->timezone;
            $event->starts_at = Carbon::createFromFormat('Y-m-d H:i', $event->starts_at, $timezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i');
        }

        $event->save();

        return redirect('/' . $subdomain1 . '/schedule');
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