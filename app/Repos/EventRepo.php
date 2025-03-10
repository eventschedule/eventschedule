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
use App\Models\Ticket;


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
                $venue->name_en = $request->venue_name_en ?? null;
                $venue->email = $request->venue_email ?? null;
                $venue->subdomain = Role::generateSubdomain($request->venue_email ? $request->venue_name : null);
                $venue->type = 'venue';
                $venue->timezone = $user->timezone;
                $venue->language_code = $request->venue_language_code ? $request->venue_language_code : $user->language_code;
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
                $venue->email = $request->venue_email ?? null;
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
                    $role->language_code = $request->language_code ? $request->language_code : $user->language_code;
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

                    if ($matchingUser = User::whereEmail($role->email)->first()) {
                        $role->user_id = $matchingUser->id;
                        $role->email_verified_at = $matchingUser->email_verified_at;
                        $role->save();
                        $matchingUser->roles()->attach($role->id, ['level' => 'owner', 'created_at' => now()]);
                    }

                    if (! $matchingUser || $matchingUser->id != $user->id) { 
                        $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
                    }

                    $roleIds[] = $role->id;
                } else {
                    $roleId = UrlUtils::decodeId($memberId);
                    $role = Role::findOrFail($roleId);

                    if (! $role->isClaimed()) {
                        $role->name = $member['name'];
                        $role->email = $member['email'];
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
                $event->is_accepted = $venue && $user->isMember($venue->subdomain) ? true : null;
            }
        } else {
            $event = new Event;       
            $event->user_id = auth()->user()->id;
            $event->curator_id = $curatorId;
            $event->is_accepted = $venue && $user->isMember($venue->subdomain) ? true : null;

            if ($request->name_en) {
                $event->slug = \Str::slug($request->name_en);
            } else {
                $event->slug = \Str::slug($request->name);
            }

            if (! $event->slug) {
                $event->slug = strtolower(\Str::random(5));
            }
        }

        $event->fill($request->all());
        $event->venue_id = $venueId;
        if (! $request->event_url) {
            $event->event_url = null;
        }

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

        if (config('app.hosted')) {
            if ($event->wasRecentlyCreated && $venue && ! $venue->isClaimed() && $venue->is_subscribed && $venue->email) {
                Mail::to($venue->email)->send(new ClaimVenue($event));
            }

            foreach ($roles as $role) {
                if ($event->wasRecentlyCreated && ! $role->isClaimed() && $role->is_subscribed && $role->email) {
                    Mail::to($role->email)->send(new ClaimRole($event));
                }
            }
        }

        if ($event->tickets_enabled) {
            $ticketData = $request->input('tickets', []);
            $ticketIds = [];

            foreach ($ticketData as $data) {
                if (!empty($data['id'])) {
                    $ticket = Ticket::find($data['id']);
                    $ticketIds[] = $ticket->id;
                    if ($ticket && $ticket->event_id == $event->id) {
                        $ticket->update([
                            'type' => $data['type'] ?? null,
                            'quantity' => $data['quantity'] ?? null,
                            'price' => $data['price'] ?? null,
                            'description' => $data['description'] ?? null
                        ]);
                    }
                } else {
                    $ticket = Ticket::create([
                        'event_id' => $event->id,
                        'type' => $data['type'] ?? null,
                        'quantity' => $data['quantity'] ?? null, 
                        'price' => $data['price'] ?? null,
                        'description' => $data['description'] ?? null
                    ]);
                    $ticketIds[] = $ticket->id;
                }
            }

            $event->tickets()
                ->whereNotIn('id', $ticketIds)
                ->update(['is_deleted' => true]);
        } else {
            $event->tickets()->update(['is_deleted' => true]);
        }

        $event->load('tickets');

        return $event;
    }

    public function getEvent($subdomain, $slug, $date = null)
    {
        $event = null;
        $eventDate = $date ? Carbon::parse($date) : null;
            
        $subdomainRole = Role::where('subdomain', $subdomain)->first();
        $slugRole = Role::where('subdomain', $slug)->first();        
        $timezone = auth()->user() ? auth()->user()->timezone : $subdomainRole->timezone;

        if ($subdomainRole && $slugRole) {
            $venue = null;
            $role = null;

            if ($subdomainRole->isVenue()) {
                $venue = $subdomainRole;
            } elseif ($slugRole->isVenue()) {
                $venue = $slugRole;
            }

            if ($subdomainRole->isSchedule()) {
                $role = $subdomainRole;
            } elseif ($slugRole->isSchedule()) {
                $role = $slugRole;
            }

            if ($role && $venue) {
                if ($eventDate) {
                    $event = Event::with(['venue', 'roles'])
                        ->whereHas('roles', function ($query) use ($role) {
                            $query->where('role_id', $role->id);
                        })
                        ->where('venue_id', $venue->id)
                        ->where(function ($query) use ($eventDate, $timezone) {
                            $query->whereBetween('starts_at', [
                                $eventDate->copy()->startOfDay()->setTimezone($timezone), 
                                $eventDate->copy()->endOfDay()->setTimezone($timezone),
                            ])
                                ->orWhere(function ($query) use ($eventDate) {
                                    $query->whereNotNull('days_of_week')
                                        ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDate->dayOfWeek + 1])
                                        ->where('starts_at', '<=', $eventDate);
                                });
                        })
                        ->orderBy('starts_at')
                        ->first();
                } else {
                    $event = Event::with(['venue', 'roles'])
                        ->whereHas('roles', function ($query) use ($role) {
                            $query->where('role_id', $role->id);
                        })
                        ->where('venue_id', $venue->id)
                        ->where('starts_at', '>=', now()->subDay())
                        ->orderBy('starts_at')
                        ->first();

                    if (!$event) {
                        $event = Event::with(['venue', 'roles'])
                            ->whereHas('roles', function ($query) use ($role) {
                                $query->where('role_id', $role->id);
                            })    
                            ->where('venue_id', $venue->id)
                            ->where('starts_at', '<', now())
                            ->orderBy('starts_at', 'desc')
                            ->first();
                    }
                }

                if ($event) {
                    return $event;
                }
            }
        }

        if ($eventDate) {                
            $event = Event::with(['venue', 'roles'])
                        ->where('slug', $slug)
                        ->where(function ($query) use ($eventDate, $timezone) {
                            // Convert local date to UTC range for comparison
                            $startOfDay = $eventDate->copy()->startOfDay()->setTimezone($timezone);
                            $endOfDay = $eventDate->copy()->endOfDay()->setTimezone($timezone);
                            
                            $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                                ->orWhere(function ($query) use ($eventDate) {
                                    $query->whereNotNull('days_of_week')
                                        ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDate->dayOfWeek + 1])
                                        ->where('starts_at', '<=', $eventDate);
                                });
                        })
                        ->where(function($query) use ($subdomain) {
                            $query->whereHas('venue', function($q) use ($subdomain) {
                                $q->where('subdomain', $subdomain);
                            })->orWhereHas('roles', function($q) use ($subdomain) {
                                $q->where('subdomain', $subdomain);
                            });
                        })
                        ->orderBy('starts_at')
                        ->first();
            
        } else {
            $event = Event::with(['venue', 'roles'])
                        ->where('slug', $slug)
                        ->where('starts_at', '>=', now()->subDay())                    
                        ->where(function($query) use ($subdomain) {
                            $query->whereHas('venue', function($q) use ($subdomain) {
                                $q->where('subdomain', $subdomain);
                            })->orWhereHas('roles', function($q) use ($subdomain) {
                                $q->where('subdomain', $subdomain);
                            });
                        })
                        ->orderBy('starts_at', 'desc')
                        ->first();
            
            if (! $event) {
                $event = Event::with(['venue', 'roles'])
                            ->where('slug', $slug)
                            ->where('starts_at', '<', now())
                            ->where(function($query) use ($subdomain) {
                                $query->whereHas('venue', function($q) use ($subdomain) {
                                    $q->where('subdomain', $subdomain);
                                })->orWhereHas('roles', function($q) use ($subdomain) {
                                    $q->where('subdomain', $subdomain);
                                });
                            })    
                            ->orderBy('starts_at', 'desc')
                            ->first();                    

            }
        }

        if ($event) {
            return $event;
        }

        if ($eventDate) {
            $event = Event::with(['venue', 'roles'])
                        ->where(function ($query) use ($eventDate, $timezone) {
                            // Convert local date to UTC range for comparison
                            $startOfDay = $eventDate->copy()->startOfDay()->setTimezone($timezone);
                            $endOfDay = $eventDate->copy()->endOfDay()->setTimezone($timezone);
                            
                            $query->whereBetween('starts_at', [$startOfDay, $endOfDay]);
                        })
                        ->where(function($query) use ($subdomain) {
                            $query->whereHas('venue', function($q) use ($subdomain) {
                                $q->where('subdomain', $subdomain);
                            })->orWhereHas('roles', function($q) use ($subdomain) {
                                $q->where('subdomain', $subdomain);
                            });
                        })
                        ->first();
        }

        return $event;
    }
}
