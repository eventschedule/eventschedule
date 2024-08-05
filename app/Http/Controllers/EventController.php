<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\EventRequestNotification;
use App\Notifications\RequestDeclinedNotification;
use App\Notifications\RequestAcceptedNotification;
use App\Notifications\ClaimVenueNotification;
use App\Notifications\DeletedEventNotification;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class EventController extends Controller
{
    public function delete(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect('/');
        }

        $event->delete();

        $role = $event->role;
        $venue = $event->venue;

        $roleEmails = $role->members()->pluck('email')->toArray();
        $venueEmails = $venue->members()->pluck('email')->toArray();
        $emails = array_unique(array_merge($roleEmails, $venueEmails));

        Notification::route('mail', $emails)->notify(new DeletedEventNotification($event, $user));

        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('messages.event_deleted'));
    }

    public function edit(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        $venueSubdomain = $event->venue->subdomain;
        $roleSubdomain = $event->role->subdomain;

        $data = [
            'event' => $event,
            'user' => $request->user(),
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
        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        $roles = [];

        if (! $role->email_verified_at) {
            return redirect('/');
        }

        $venue = $role->isVenue() ? $role : null;
        $talent = $role->isTalent() ? $role : null;
        $vendor = $role->isVendor() ? $role : null;

        if (! $role->isVenue()) {
            if ($request->has('venue_email')) {
                if ($request->user()->email == $request->venue_email) {
                    return redirect()
                            ->back()
                            ->withInput()
                            ->withErrors(['venue_email' => __('messages.email_not_associated_with_venue')]);
                }

                $venue = Role::whereEmail($request->venue_email)->first();

                if ($venue && ! $venue->isVenue()) {
                    return redirect()
                            ->back()
                            ->withInput()
                            ->withErrors(['venue_email' => __('messages.email_not_associated_with_venue')]);
                }

            } else {
                return view('event/venue_search', ['subdomain' => $subdomain]);
            }
        }

        if ($venue && ! auth()->user()->isMember($venue->subdomain)) {
            if (! $user->isMember($subdomain) && ! $venue->acceptRequests()) {
                return redirect('/');
            }

            foreach ($user->talent()->get() as $each) {
                $roles[] = $each;
            }
            
            foreach ($user->vendors()->get() as $each) {
                $roles[] = $each;
            }

            if (count($roles) == 0) {
                return redirect(route('register', ['type' => $venue->accept_talent_requests ? 'talent' : 'vendor']));
            } elseif (count($roles) == 1) {
                if ($roles[0]->isVendor()) {
                    $vendor = $roles[0];
                } else {
                    $talent = $roles[0];
                }
            }
        }

        $event = new Event;
        if ($request->date) {
            $defaultTime = Carbon::now($user->timezone)->setTime(20, 0, 0);
            $utcTime = $defaultTime->setTimezone('UTC');
            $event->starts_at = $request->date . $utcTime->format('H:i:s');
        }

        $title = __('messages.add_event');
        if (strpos($request->url(), '/sign_up') > 0) {
            $title = __('messages.sign_up');
        }

        $data = [
            'role' => $role,
            'user' => $request->user(),
            'subdomain' => $subdomain,
            'event' => $event,
            'venue' => $venue,
            'talent' => $talent,
            'vendor' => $vendor,
            'title' => $title,
            'roles' => $roles,
        ];

        return view('event/edit', $data);
    }

    public function update(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect('/');
        }

        $event->fill($request->all());
    
        if ($request->starts_at) {
            $timezone = auth()->user()->timezone;        
            $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at, $timezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        $event->save();

        $venue = $event->venue;
        if (! $venue->user_id) {
            $venue->name = $request->venue_name;
            $venue->address1 = $request->venue_address1;
            $venue->address2 = $request->venue_address2;
            $venue->city = $request->venue_city;
            $venue->state = $request->venue_state;
            $venue->postal_code = $request->venue_postal_code;
            $venue->country_code = $request->venue_country_code;
            $venue->save();
        }

        if ($event->starts_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        } else {
            $date = Carbon::now();
        }

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

        $emails = $event->role->members()->pluck('email');
        //Notification::route('mail', $emails)->notify(new RequestAcceptedNotification($event));
        
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

        $emails = $event->role->members()->pluck('email');
        //Notification::route('mail', $emails)->notify(new RequestDeclinedNotification($event));

        if ($request->redirect_to == 'schedule') {
            return redirect('/' . $subdomain . '/schedule')
                ->with('message', __('messages.request_declined'));
        } else {
            return redirect('/' . $subdomain . '/requests')
                ->with('message', __('messages.request_declined'));
        }        
    }

    public function store(Request $request, $subdomain)
    {
        $user = $request->user();
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
            $venue->email = $request->venue_email;
            $venue->subdomain = Role::generateSubdomain($request->venue_name);
            $venue->type = 'venue';
            $venue->timezone = $user->timezone;
            $venue->language_code = $user->language_code;
            $venue->address1 = $request->venue_address1;
            $venue->address2 = $request->venue_address2;
            $venue->city = $request->venue_city;
            $venue->state = $request->venue_state;
            $venue->postal_code = $request->venue_postal_code;
            $venue->country_code = $request->venue_country_code;
            $venue->save();
        }

        $role = false;

        if ($vendor) {
            $role = $vendor;            
        } else if ($talent) {
            $role = $talent;
        } else if ($request->role_id) {

            $roleId = UrlUtils::decodeId($request->role_id);
            $role = Role::findOrFail($roleId);

            if (! auth()->user()->isMember($role->subdomain)) {
                return redirect('/');
            }    
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
                $role->timezone = $user->timezone;
                $role->language_code = $user->language_code;
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

        if (auth()->user()->isMember($venue->subdomain) || !$venue->user_id) {
            $event->is_accepted = true;
            $message = __('messages.event_created');
        } else {
            $subdomain = $role->subdomain;
            $message = __('messages.event_requested');

            $emails = $venue->members()->pluck('email');
            //Notification::route('mail', $emails)->notify(new EventRequestNotification($venue, $role));
        }

        $event->save();

        if ($venue->wasRecentlyCreated) {
            Notification::route('mail', $venue->email)->notify(new ClaimVenueNotification($event));
        }

        if ($event->starts_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        } else {
            $date = Carbon::now();
        }

        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
            'month' => $date->month,
            'year' => $date->year,
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', $message);
    }

}