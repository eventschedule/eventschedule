<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\EventRequestNotification;
use App\Notifications\RequestDeclinedNotification;
use App\Notifications\RequestAcceptedNotification;
use App\Notifications\DeletedEventNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use Illuminate\Support\Facades\Storage;
use App\Utils\ColorUtils;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Models\EventRole;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Rules\NoFakeEmail;

class EventController extends Controller
{
    public function deleteImage(Request $request, $subdomain)
    {
        $event_id = UrlUtils::decodeId($request->hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect('/');
        }

        if ($request->image_type == 'flyer') {
            if ($event->flyer_image_url) {
                $path = $event->getAttributes()['flyer_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);

                $event->flyer_image_url = null;
                $event->save();
            }    
        }

        return redirect(route('event.edit', ['subdomain' => $subdomain, 'hash' => $request->hash]))
                ->with('message', __('messages.deleted_image'));
    }

    public function delete(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect('/');
        }

        $event->delete();

        /*
        $role = $event->role;
        $venue = $event->venue;

        $roleEmails = $role->members()->pluck('email')->toArray();
        $venueEmails = $venue->members()->pluck('email')->toArray();
        $emails = array_unique(array_merge($roleEmails, $venueEmails));

        Notification::route('mail', $emails)->notify(new DeletedEventNotification($event, $user));
        */

        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'schedule',
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('messages.event_deleted'));
    }


    public function create(Request $request, $subdomain)
    {
        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        
        $venue = $role->isVenue() ? $role : null;
        $talent = $role->isTalent() ? $role : null;
        $vendor = $role->isVendor() ? $role : null;
        $curator = $role->isCurator() ? $role : null;

        if (! $role->email_verified_at) {
            return redirect('/');
        }

        $event = new Event;

        if ($venue) {
            $event->venue_id = $venue->id;
        } else if ($talent) {
            $event->members = [$talent->toData()];
        } else if ($vendor) {
            $event->members = [$vendor->toData()];
        }

        if ($request->date) {
            $defaultTime = Carbon::now($user->timezone)->setTime(20, 0, 0);
            $utcTime = $defaultTime->setTimezone('UTC');
            $event->starts_at = $request->date . $utcTime->format('H:i:s');
        }

        $title = __('messages.add_event');
        if (strpos($request->url(), '/sign_up') > 0) {
            $title = __('messages.sign_up');
        }
        
        $roles = $user->roles()->get();
    
        $venues = $roles->filter(function($item) {
            return $item->isVenue() && $item->name;
        })->map(function ($venue) {
            return $venue->toData();
        });
    
        $members = $roles->filter(function($item) {
            return ($item->isTalent() || $item->isVendor()) && $item->name;
        })->map(function ($member) {
            return $member->toData();
        });

        $venues = array_values($venues->toArray());
        $members = array_values($members->toArray());
        
        return view('event/edit', [
            'role' => $role,
            'user' => $user,
            'roles' => $roles,
            'event' => $event,
            'subdomain' => $subdomain,
            'title' => $title,
            'selectedVenue' => $venue,
            'venues' => $venues,
            'selectedMembers' => $members,
        ]);
    }

    public function edit(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);
        $user = $request->user();

        if (! $user->canEditEvent($event)) {
            return redirect()->back();
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $event->venue;
        $selectedMembers = [];
        foreach ($event->roles as $role) {
            $selectedMembers[] = $role->toData();
        }

        if (! $role->email_verified_at) {
            return redirect('/');
        }

        $title = __('messages.edit_event');
        
        $roles = $user->roles()->get();
    
        $venues = $roles->filter(function($item) {
            return $item->isVenue() && $item->name;
        })->map(function ($item) {
            return $item->toData();
        });
    
        $members = $roles->filter(function($item) {
            return ($item->isTalent() || $item->isVendor()) && $item->name;
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->toArray());
        $members = array_values($members->toArray());
    

        return view('event/edit', [
            'role' => $role,
            'user' => $user,
            'roles' => $roles,
            'event' => $event,
            'subdomain' => $subdomain,
            'title' => $title,
            'selectedVenue' => $venue,
            'venues' => $venues,
            'selectedMembers' => $selectedMembers,
            'members' => $members,
        ]);
    }

    public function update(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back();
        }

        $event->fill($request->all());
    
        $days_of_week = '';
        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        foreach ($days as $index => $day) {
            $days_of_week .= request()->has('days_of_week_' . $index) ? '1' : '0';
        }
        $event->days_of_week = request()->schedule_type == 'recurring' ? $days_of_week : null;

        if ($request->starts_at) {
            $timezone = auth()->user()->timezone;        
            $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at, $timezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        $event->save();

        // Handle curator selections
        $selectedCurators = $request->input('curators', []);
        $event->curators()->sync($selectedCurators);

        if ($request->hasFile('flyer_image')) {
            if ($event->flyer_image_url) {
                $path = $event->getAttributes()['flyer_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('flyer_image');
            $filename = strtolower('flyer_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $event->flyer_image_url = $filename;
            $event->save();
        }

        $venue = $event->venue;
        $role = $event->role;

        if (! $venue->user_id) {
            if ($request->venue_name) {
                $venue->name = $request->venue_name;
            }

            $venue->address1 = $request->venue_address1;
            $venue->address2 = $request->venue_address2;
            $venue->city = $request->venue_city;
            $venue->state = $request->venue_state;
            $venue->postal_code = $request->venue_postal_code;
            $venue->country_code = $request->venue_country_code;
            
            if ($request->formatted_address) {
                $venue->formatted_address = $request->formatted_address;
                $venue->google_place_id = $request->google_place_id;
                $venue->geo_address = $request->geo_address;
                $venue->geo_lat = $request->geo_lat;
                $venue->geo_lon = $request->geo_lon;
            }

            $venue->save();
        }

        if (! $role->isClaimed()) {
            $links = [];
            if ($request->first_video_url) {
                $links[] = UrlUtils::getUrlDetails($request->first_video_url);
            }
            if ($request->second_video_url) {
                $links[] = UrlUtils::getUrlDetails($request->second_video_url);
            }
            $role->youtube_links = json_encode($links);
            $role->save();
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

        //$emails = $event->role->members()->pluck('email');
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

        //$emails = $event->role->members()->pluck('email');
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
        //dd($request->all());

        $user = $request->user();
        $subdomainRole = $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $role->isVenue() ? $role : null;
        $talent = $role->isTalent() ? $role : null;
        $vendor = $role->isVendor() ? $role : null;
        $curator = $role->isCurator() ? $role : null;

        if (! $venue && $request->venue_id) {
            $venue = Role::findOrFail(UrlUtils::decodeId($request->venue_id));
        }

        if ($venue && ! $curator && ! auth()->user()->isMember($venue->subdomain) && ! $venue->acceptRequests()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['venue_email' => __('messages.venue_not_accepting_requests')]);
        }

        if (! $venue) {
            $venue = new Role;
            $venue->name = $request->venue_name ?? null;
            $venue->email = $request->venue_email ?? null;
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
            $venue->background_colors = ColorUtils::randomGradient();
            $venue->background_rotation = rand(0, 359);
            $venue->font_color = '#ffffff';
            $venue->save();
            $venue->refresh();
            
            $matchingUser = false;
            
            if ($venue->email && $matchingUser = User::whereEmail($venue->email)->first()) {
                $venue->user_id = $matchingUser->id;
                $venue->email_verified_at = $matchingUser->email_verified_at;
                $venue->save();
                
                $matchingUser->roles()->attach($venue->id, ['level' => 'owner', 'created_at' => now()]);
            }

            if (! $matchingUser || $matchingUser->id != $user->id) { 
                $user->roles()->attach($venue->id, ['level' => 'follower', 'created_at' => now()]);
            }
        }

        $roleIds = [];
        foreach ($request->members as $memberId => $member) {
            if (strpos($memberId, 'new_') === 0) {

                $role = new Role;
                $role->name = $member['name'];
                $role->subdomain = Role::generateSubdomain($member['name']);
                $role->email = isset($member['email']) && $member['email'] !== '' ? $member['email'] : null;
                $role->type = $request->role_type ? $request->role_type : 'talent';
                $role->timezone = $user->timezone;
                $role->language_code = $user->language_code;
                $role->background_colors = ColorUtils::randomGradient();
                $role->background_rotation = rand(0, 359);
                $role->font_color = '#ffffff';

                $links = [];
                if ($member['youtube_url']) {
                    $links[] = UrlUtils::getUrlDetails($member['youtube_url']);
                }
                $role->youtube_links = json_encode($links);

                $role->save();
                $role->refresh();

                $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);

                if ($matchingUser = User::whereEmail($role->email)->first()) {
                    $role->user_id = $matchingUser->id;
                    $role->email_verified_at = $matchingUser->email_verified_at;
                    $role->save();
                    $matchingUser->roles()->attach($role->id, ['level' => 'owner', 'created_at' => now()]);
                }

                $roleIds[] = $role->id;
            } else {
                $roleId = UrlUtils::decodeId($memberId);
                $role = Role::findOrFail($roleId);

                if (!$role->isClaimed()) {
                    $role->name = $member['name'];

                    $links = [];
                    if ($member['youtube_url']) {
                        $links[] = UrlUtils::getUrlDetails($member['youtube_url']);
                    }
                    $role->youtube_links = json_encode($links);    

                    $role->save();
                }

                $roleIds[] = $role->id;
            }
        }

        $event = false;

        /*
        if ($subdomainRole->isCurator()) {
            $date = explode(' ', $request->starts_at)[0];
            $event = Event::whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id);
                    })
                    ->where('venue_id', $venue->id)
                    ->where('starts_at', 'like', $date . '%')
                    ->first();
        }
        */

        if ($event) {
            $message = __('messages.event_added');
        } else {
            $event = new Event;       
            $event->fill($request->all());
            $event->user_id = auth()->user()->id;
            $event->venue_id = $venue->id;

            $days_of_week = '';
            $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            foreach ($days as $index => $day) {
                $days_of_week .= request()->has('days_of_week_' . $index) ? '1' : '0';
            }
            $event->days_of_week = request()->schedule_type == 'recurring' ? $days_of_week : null;

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
                //$subdomain = $role->subdomain;
                $message = __('messages.event_requested');

                $emails = $venue->members()->pluck('email');
                //Notification::route('mail', $emails)->notify(new EventRequestNotification($venue, $role));
            }

            if ($subdomainRole->isCurator()) {                
                $event->curator_id = $subdomainRole->id;
            }

            $selectedCurators = $request->input('curators', []);

            $event->save();
            $event->roles()->sync($roleIds + $selectedCurators);

            if ($request->hasFile('flyer_image_url')) {
                if ($event->flyer_image_url) {
                    $path = $event->getAttributes()['flyer_image_url'];
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/' . $path;
                    }
                    Storage::delete($path);
                }

                $file = $request->file('flyer_image_url');
                $filename = strtolower('flyer_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
                $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                $event->flyer_image_url = $filename;
                $event->save();
            }

            if (! $venue->isClaimed() && $venue->is_subscribed && $venue->email) {
                Mail::to($venue->email)->send(new ClaimVenue($event));
            }

            if (! $role->isClaimed() && $role->is_subscribed && $role->email) {
                Mail::to($role->email)->send(new ClaimRole($event));
            }
        }

        if ($subdomainRole->isCurator() && ! $subdomainRole->events()->where('event_id', $event->id)->exists()) {
            $subdomainRole->events()->attach($event->id);
        }

        session()->forget('pending_venue');

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

    public function curate(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->events()->attach($event->id);
    
        return back()->with('message', __('messages.curate_event'));
    }

    public function uncurate(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->events()->detach($event->id);

        return back()->with('message', __('messages.uncurate_event'));
    }
}