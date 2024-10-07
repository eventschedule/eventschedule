<?php

namespace App\Repos;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\ColorUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventRepo
{
    public function saveEvent($request, $subdomain, $event = null)
    {
        $user = $request->user();
        $subdomainRole = $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $role->isVenue() ? $role : null;
        $talent = $role->isTalent() ? $role : null;
        $vendor = $role->isVendor() ? $role : null;
        $curator = $role->isCurator() ? $role : null;

        if (! $venue && $request->venue_id) {
            $venue = Role::findOrFail(UrlUtils::decodeId($request->venue_id));
        }

        /*
        if ($venue && ! $curator && ! auth()->user()->isMember($venue->subdomain) && ! $venue->acceptRequests()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['venue_email' => __('messages.venue_not_accepting_requests')]);
        }
        */

        if (! $venue) {
            $venue = new Role;
            $venue->name = $request->venue_name ?? null;
            $venue->email = $request->venue_email ?? null;
            $venue->subdomain = Role::generateSubdomain($request->venue_email ? $request->venue_email : null);
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
                $role->email = isset($member['email']) && $member['email'] !== '' ? $member['email'] : null;
                $role->subdomain = Role::generateSubdomain($role->email ? $role->email : null);
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

        if (! $event) {
            $event = new Event;       
        }

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
        $selectedCurators = array_map(function($id) {
            return UrlUtils::decodeId($id);
        }, $selectedCurators);

        $roleIds = array_merge($roleIds, $selectedCurators);

        $event->save();        
        //\Log::info('Role IDs before sync: ' . json_encode($event->roles()->pluck('roles.id')->toArray()));
        $event->roles()->sync($roleIds);
        //\Log::info('Role IDs after sync: ' . json_encode($event->roles()->pluck('roles.id')->toArray()));

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

        if ($venue->wasRecentlyCreated && ! $venue->isClaimed() && $venue->is_subscribed && $venue->email) {
            Mail::to($venue->email)->send(new ClaimVenue($event));
        }

        if ($role->wasRecentlyCreated && ! $role->isClaimed() && $role->is_subscribed && $role->email) {
            Mail::to($role->email)->send(new ClaimRole($event));
        }

        return $event;
    }
}
