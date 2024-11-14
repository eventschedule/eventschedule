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
use Illuminate\Support\Facades\Mail;
use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;


class EventRepo
{
    public function saveEvent($request, $event = null, $curatorId = null)
    {
        $user = $request->user();
        $venue = null;

        if ($request->venue_id) {
            $venue = Role::findOrFail(UrlUtils::decodeId($request->venue_id));
        }

        if ($request->venue_address1) {
            if (! $venue) {
                $venue = new Role;
                $venue->name = $request->venue_name ?? null;
                $venue->email = $request->venue_email ?? null;
                $venue->subdomain = Role::generateSubdomain($request->venue_email ? $request->venue_name : null);
                $venue->type = 'venue';
                $venue->timezone = $user->timezone;
                $venue->language_code = $user->language_code;
                $venue->name = $request->venue_name ?? null;
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
            } else if ($venue && ! $venue->isClaimed()) {
                $venue->name = $request->venue_name ?? null;
                $venue->address1 = $request->venue_address1;
                $venue->address2 = $request->venue_address2;
                $venue->city = $request->venue_city;
                $venue->state = $request->venue_state;
                $venue->postal_code = $request->venue_postal_code;
                $venue->country_code = $request->venue_country_code;
                $venue->save();
            }
        }

        $roles = [];
        $roleIds = [];

        if ($request->members) {
            foreach ($request->members as $memberId => $member) {
                if (strpos($memberId, 'new_') === 0) {

                    $role = new Role;
                    $role->name = $member['name'];
                    $role->email = isset($member['email']) && $member['email'] !== '' ? $member['email'] : null;
                    $role->subdomain = Role::generateSubdomain($role->email ? $role->email : null);
                    $role->type = $request->role_type ? $request->role_type : 'schedule';
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

                    $roles[] = $role;
                    $roleIds[] = $role->id;
                }
            }
        }

        $venueId = $venue ? $venue->id : null;

        if ($event) {
            if ($event->venue_id != $venueId) {
                $event->is_accepted = null;
            }
        } else {
            $event = new Event;       
            $event->user_id = auth()->user()->id;
            $event->curator_id = $curatorId;
            $event->is_accepted = $venue && $user->isMember($venue->subdomain) ? true : null;
        }

        $event->fill($request->all());
        $event->venue_id = $venueId;

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

        /*
        if (auth()->user()->isMember($venue->subdomain) || !$venue->user_id) {
            $event->is_accepted = true;
            $message = __('messages.event_created');
        } else {
            //$subdomain = $role->subdomain;
            $message = __('messages.event_requested');

            $emails = $venue->members()->pluck('email');
            //Notification::route('mail', $emails)->notify(new EventRequestNotification($venue, $role));
        }
        */


        $selectedCurators = $request->input('curators', []);
        $selectedCurators = array_map(function($id) {
            return UrlUtils::decodeId($id);
        }, $selectedCurators);

        $roleIds = array_merge($roleIds, $selectedCurators);        

        $event->save();        
        //\Log::info('Role IDs before sync: ' . json_encode($event->roles()->pluck('roles.id')->toArray()));
        $event->roles()->sync($roleIds);
        //\Log::info('Role IDs after sync: ' . json_encode($event->roles()->pluck('roles.id')->toArray()));

        foreach ($event->roles as $role) {
            if ($user->isMember($role->subdomain)) {
                $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
            } else if ($role->isCurator() && $role->is_open) {
                $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
            }
        }
        
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

        if ($event->wasRecentlyCreated && $venue && ! $venue->isClaimed() && $venue->is_subscribed && $venue->email) {
            Mail::to($venue->email)->send(new ClaimVenue($event));
        }

        foreach ($roles as $role) {
            if ($event->wasRecentlyCreated && ! $role->isClaimed() && $role->is_subscribed && $role->email) {
                Mail::to($role->email)->send(new ClaimRole($event));
            }
        }

        return $event;
    }
}
