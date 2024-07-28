<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class EventController extends Controller
{
    public function view(Request $request, $subdomain, $hash)
    {
        dd('here');
    }

    public function edit(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        $venueSubdomain = $event->venue->subdomain;
        $roleSubdomain = $event->role->subdomain;

        $data = [
            'event' => $event,
            'subdomain' => $subdomain,
            'subdomain2' => $subdomain == $venueSubdomain ? $roleSubdomain : $venueSubdomain,
            'venue' => $event->venue,
            'talent' => $event->role->type == 'talent' ? $event->role : false,
            'vendor' => $event->role->type == 'vendor' ? $event->role : false,
            'title' => __('Edit Event'),
        ];

        return view('event/edit', $data);
    }

    public function create(Request $request, $subdomain, $subdomain2 = '')
    {
        $role1 = Role::subdomain($subdomain)->firstOrFail();
        $role2 = $subdomain2 ? Role::subdomain($subdomain2)->firstOrFail() : false;

        $venue = $this->getVenue($role1, $role2);
        $vendor = $this->getVendor($role1, $role2);
        $talent = $this->getTalent($role1, $role2);
        
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event = new Event;
        if ($request->date) {
            $defaultTime = Carbon::now(auth()->user()->timezone)->setTime(20, 0, 0);
            $utcTime = $defaultTime->setTimezone('UTC');
            $event->starts_at = $request->date . $utcTime->format('H:i:s');
        }

        $title = __('Add Event');
        if (strpos($request->url(), '/sign_up') > 0) {
            $title = __('Sign Up');
        }

        $data = [
            'subdomain' => $subdomain,
            'subdomain2' => $subdomain2,
            'event' => $event,
            'venue' => $venue,
            'talent' => $talent,
            'vendor' => $vendor,
            'title' => $title,
        ];

        return view('event/edit', $data);
    }

    public function update(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);
        $event->fill($request->all());

        $timezone = auth()->user()->timezone;        
        $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at, $timezone)
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:S');
        
        $event->save();

        $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
            'month' => $date->month,
            'year' => $date->year,
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('Successfully updated event'));
    }

    public function accept(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = true;
        $event->save();
        
        return redirect('/' . $subdomain . '/requests')
                    ->with('message', __('Request has been accepted'));
    }

    public function decline(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = false;
        $event->save();
        
        if ($request->redirect_to == 'schedule') {
            return redirect('/' . $subdomain . '/schedule')
                ->with('message', __('Request has been declined'));
        } else {
            return redirect('/' . $subdomain . '/requests')
                ->with('message', __('Request has been declined'));
        }

        
    }

    /*
    public function delete(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->delete();
        
        return redirect('/' . $subdomain . '/schedule');
    }
    */

    public function store(Request $request, $subdomain, $subdomain2 = '')
    {
        $role1 = Role::subdomain($subdomain)->firstOrFail();
        $role2 = $subdomain2 ? Role::subdomain($subdomain2)->firstOrFail() : false;

        $venue = $this->getVenue($role1, $role2);
        $vendor = $this->getVendor($role1, $role2);
        $talent = $this->getTalent($role1, $role2);
        
        if (! $venue) {
            $venue = Role::whereEmail($request->venue_email)->first();

            if ($venue && ! $venue->isVenue()) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['venue_email' => __('Email is not associated with a venue')]);
            }
        }

        if ($venue && ! auth()->user()->hasRole($venue->subdomain) && ! $venue->acceptRequests()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['venue_email' => __('Venue is not accepting requests')]);
        }

        if (! $venue) {
            $venue = new Role;
            $venue->name = $request->venue_name;
            $venue->subdomain = Role::generateSubdomain($request->venue_name);
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

            $role = Role::whereEmail($request->role_email)->first();

            if ($role && $role->isVenue()) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['venue_email' => __('Email is associated with a venue')]);
            } elseif (! $role) {
                $role = new Role;
                $role->name = $request->role_name;
                $role->subdomain = Role::generateSubdomain($request->role_name);
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
            $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, $timezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        if (auth()->user()->hasRole($venue->subdomain)) {
            $event->is_accepted = true;
        }

        $event->save();

        $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
            'month' => $date->month,
            'year' => $date->year,
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('Successfully created event'));
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