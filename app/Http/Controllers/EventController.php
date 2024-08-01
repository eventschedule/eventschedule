<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class EventController extends Controller
{
    public function delete(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (auth()->user()->id != $event->user_id) {            
            return redirect('/');
        }

        $event->delete();

        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('messages.event_deleted'));
    }

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
            'venue' => $event->venue,
            'talent' => $event->role->type == 'talent' ? $event->role : false,
            'vendor' => $event->role->type == 'vendor' ? $event->role : false,
            'title' => __('messages.edit_event'),
        ];

        return view('event/edit', $data);
    }

    public function create(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $role->isVenue() ? $role : null;
        $talent = $role->isTalent() ? $role : null;
        $vendor = $role->isVendor() ? $role : null;
        
        if ($venue && (! auth()->user()->isMember($subdomain) && ! $venue->acceptRequests())) {
            return redirect('/');
        }

        $event = new Event;
        if ($request->date) {
            $defaultTime = Carbon::now(auth()->user()->timezone)->setTime(20, 0, 0);
            $utcTime = $defaultTime->setTimezone('UTC');
            $event->starts_at = $request->date . $utcTime->format('H:i:s');
        }

        $title = __('messages.add_event');
        if (strpos($request->url(), '/sign_up') > 0) {
            $title = __('messages.sign_up');
        }

        $data = [
            'subdomain' => $subdomain,
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
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);
        $event->fill($request->all());
    
        $timezone = auth()->user()->timezone;        
        $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at, $timezone)
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');

        $event->save();

        $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
            'month' => $date->month,
            'year' => $date->year,
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('messages.event_updated'));
    }

    public function accept(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = true;
        $event->save();
        
        return redirect('/' . $subdomain . '/requests')
                    ->with('message', __('messages.request_accepted'));
    }

    public function decline(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = false;
        $event->save();
        
        if ($request->redirect_to == 'schedule') {
            return redirect('/' . $subdomain . '/schedule')
                ->with('message', __('messages.request_accepted'));
        } else {
            return redirect('/' . $subdomain . '/requests')
                ->with('message', __('messages.request_accepted'));
        }        
    }

    /*
    public function delete(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->delete();
        
        return redirect('/' . $subdomain . '/schedule');
    }
    */

    public function store(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $role->isVenue() ? $role : null;
        $talent = $role->isTalent() ? $role : null;
        $vendor = $role->isVendor() ? $role : null;
        
        if (! $venue) {
            $venue = Role::whereEmail($request->venue_email)->first();

            if ($venue && ! $venue->isVenue()) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['venue_email' => __('messages.email_not_associated_with_venue')]);
            }
        }

        if ($venue && ! auth()->user()->isMember($venue->subdomain) && ! $venue->acceptRequests()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['venue_email' => __('messages.venue_not_accepting_requests')]);
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
                        ->withErrors(['venue_email' => __('messages.email_associated_with_venue')]);
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

        if (auth()->user()->isMember($venue->subdomain)) {
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
                ->with('message', __('messages.event_created'));
    }

}