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
use Illuminate\Support\Facades\Notification;
use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use App\Notifications\EventAddedNotification;
use App\Utils\NotificationUtils;
use App\Models\Ticket;
use App\Support\MailConfigManager;
use App\Support\MailTemplateManager;


class EventRepo
{
    public function saveEvent($currentRole, $request, $event = null)
    {
        $user = $request->user();
        $venue = null;

        // Set creator_role_id to the current role
        $creatorRoleId = $currentRole ? $currentRole->id : null;

        if ($request->venue_id) {
            $venue = Role::findOrFail(UrlUtils::decodeId($request->venue_id));
        }

        if (! $user) {
            $user = $currentRole->user;
        }

        if ($request->venue_address1) {
            if (! $venue) {
                $venue = new Role;
                $venue->name = $request->venue_name ?? null;
                $venue->name_en = $request->venue_name_en ?? null;
                $venue->email = $request->venue_email ?? null;
                $venue->subdomain = Role::generateSubdomain($request->venue_name);
                $venue->type = 'venue';
                $venue->name = $request->venue_name ?? null;
                $venue->address1 = $request->venue_address1;
                $venue->address2 = $request->venue_address2;
                $venue->city = $request->venue_city;
                $venue->state = $request->venue_state;
                $venue->postal_code = $request->venue_postal_code;
                $venue->country_code = $request->venue_country_code ? $request->venue_country_code : $currentRole->country_code;
                $venue->language_code = $request->venue_language_code ? $request->venue_language_code : $currentRole->language_code;
                $venue->timezone = $currentRole->timezone;
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
                if ($request->venue_email) {
                    $venue->email = $request->venue_email;
                }
                
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
                if (! $memberId || strpos($memberId, 'new_') === 0) {
                    $role = new Role;
                    $role->name = $member['name'];
                    $role->email = isset($member['email']) && $member['email'] !== '' ? $member['email'] : null;
                    $role->subdomain = Role::generateSubdomain($member['name']);
                    $role->type = $request->role_type ? $request->role_type : 'talent';
                    $role->timezone = $currentRole->timezone;
                    $role->language_code = $request->language_code ? $request->language_code : $currentRole->language_code;
                    $role->country_code = $request->country_code ? $request->country_code : $currentRole->country_code;
                    $role->background_colors = ColorUtils::randomGradient();
                    $role->background_rotation = rand(0, 359);
                    $role->font_color = '#ffffff';

                    $links = [];
                    if (! empty($member['youtube_url'])) {
                        $urlInfo = UrlUtils::getUrlInfo($member['youtube_url']);
                        if ($urlInfo !== null) {
                            $links[] = $urlInfo;
                        }
                    }
                    if (count($links)) {
                        $role->youtube_links = json_encode($links);
                    }

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
                } else {
                    $roleId = UrlUtils::decodeId($memberId);
                    $role = Role::findOrFail($roleId);

                    if (! $role->isClaimed()) {
                        if (! empty($member['name'])) {
                            $role->name = $member['name'];
                        }

                        if (! empty($member['email'])) {
                            $role->email = $member['email'];
                        }
                        
                        $links = $role->youtube_links ? json_decode($role->youtube_links, true) : [];

                        if (! empty($member['youtube_url'])) {
                            $urlInfo = UrlUtils::getUrlInfo($member['youtube_url']);
                            if ($urlInfo !== null) {
                                $links = [$urlInfo];
                            }

                            $role->youtube_links = json_encode($links);
                        }

                        $role->save();
                    }
                }

                $roles[] = $role;
                $roleIds[] = $role->id;
            }
        }

        // Ensure current role is included if it's not already in the list
        if ($currentRole && ! in_array($currentRole->id, $roleIds)) {
            $roles[] = $currentRole;
            $roleIds[] = $currentRole->id;
        }

        $venueId = $venue ? $venue->id : null;

        if (! $event) {
            $event = new Event;       
            $event->user_id = $user->id;
            $event->creator_role_id = $creatorRoleId;

            if ($request->name_en) {
                $event->slug = \Str::slug($request->name_en);
            } else {
                $event->slug = \Str::slug($request->name);
            }

            if (! $event->slug) {
                $event->slug = strtolower(\Str::random(5));
            }
        }

        $input = $request->all();

        if (array_key_exists('slug', $input)) {
            $slugValue = $input['slug'];

            if ($slugValue !== null) {
                $slugValue = Str::slug($slugValue);
            }

            if ($slugValue === '') {
                $slugValue = null;
            }

            $input['slug'] = $slugValue;
        }

        $event->fill($input);
        
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
            $timezone = $user->timezone;
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

        $event->save();        

        if ($venue) {
            $roles[] = $venue;
            $roleIds[] = $venue->id;
        }

        $selectedCurators = $request->input('curators', []);
        $selectedCurators = array_map(function($id) {
            return UrlUtils::decodeId($id);
        }, $selectedCurators);

        // If editing an existing event, preserve curators that the current user can't see
        if ($event && $event->exists) {
            $existingCurators = $event->roles()->where('roles.type', 'curator')->pluck('roles.id')->toArray();
            $userCurators = $user->curators()->pluck('roles.id')->toArray();
            
            // Find curators that exist on the event but the user can't edit
            $preservedCurators = array_diff($existingCurators, $userCurators);
            
            // Add preserved curators to the selected curators
            foreach ($preservedCurators as $curatorId) {
                if (!in_array($curatorId, $selectedCurators)) {
                    $selectedCurators[] = $curatorId;
                }
            }
        }

        foreach ($selectedCurators as $curatorId) {
            $curator = Role::find($curatorId);
            if ($curator) {
                $roles[] = $curator;
                $roleIds[] = $curator->id;
            }
        }

        $event->roles()->sync($roleIds);
        
        $curatorGroups = $request->input('curator_groups', []);
        
        foreach ($roles as $role) {
            if ((auth()->user() && $user->isMember($role->subdomain)) || ($role->accept_requests && ! $role->require_approval)) {
                $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);            
            }
                        
            // If this is a curator and curator_groups is provided, add it to the pivot
            if ($role && $role->isCurator()) {
                $curatorEncodedId = UrlUtils::encodeId($role->id);
                if (isset($curatorGroups[$curatorEncodedId]) && $curatorGroups[$curatorEncodedId]) {
                    $groupId = UrlUtils::decodeId($curatorGroups[$curatorEncodedId]);
                    $event->roles()->updateExistingPivot($role->id, ['group_id' => $groupId]);
                }
            }
            
            // If this is the current role and current_role_group_id is provided, add it to the pivot
            if ($role && $role->id === $currentRole->id && $request->has('current_role_group_id') && $request->current_role_group_id) {
                $groupId = UrlUtils::decodeId($request->current_role_group_id);
                $event->roles()->updateExistingPivot($role->id, ['group_id' => $groupId]);
            }            
        }
        
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

        MailConfigManager::applyFromDatabase();

        if (! config('mail.disable_delivery')) {
            $templates = app(MailTemplateManager::class);

            foreach ($roles as $role) {
                if ($event->wasRecentlyCreated && ! $role->isClaimed() && $role->is_subscribed && $role->email) {
                    if ($role->isVenue()) {
                        if ($templates->enabled('claim_venue')) {
                            Mail::to($role->email)->send(new ClaimVenue($event));
                        }
                    } elseif ($role->isTalent()) {
                        if ($templates->enabled('claim_role')) {
                            Mail::to($role->email)->send(new ClaimRole($event));
                        }
                    }
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

        if ($event->wasRecentlyCreated) {
            $event->loadMissing(['roles.members', 'venue.members']);

            foreach ($event->roles as $roleModel) {
                if ($roleModel->isTalent()) {
                    $members = NotificationUtils::roleMembers($roleModel);

                    if ($members->isNotEmpty()) {
                        Notification::send($members, new EventAddedNotification($event, $user, 'talent', $roleModel));
                    }
                }
            }

            if ($event->venue) {
                $purchasers = NotificationUtils::roleMembers($event->venue);

                if ($purchasers->isNotEmpty()) {
                    Notification::send($purchasers, new EventAddedNotification($event, $user, 'purchaser', $event->venue));
                }
            }
        }

        return $event;
    }

    public function getEvent($subdomain, $slug, $date = null)
    {
        $date = $date ?: null;

        $subdomainRole = Role::where('subdomain', $subdomain)->first();
        $slugRole = Role::where('subdomain', $slug)->first();
        $timezone = config('app.timezone', 'UTC');

        $user = auth()->user();
        if ($user && $user->timezone) {
            $timezone = $user->timezone;
        } elseif ($subdomainRole && $subdomainRole->timezone) {
            $timezone = $subdomainRole->timezone;
        }

        $eventDate = null;
        $eventDateContext = null;

        if ($date) {
            try {
                $eventDate = Carbon::createFromFormat('Y-m-d', $date, $timezone);
                $eventDateContext = [
                    'startUtc' => $eventDate->copy()->startOfDay()->setTimezone('UTC'),
                    'endUtc' => $eventDate->copy()->endOfDay()->setTimezone('UTC'),
                    'dayOfWeek' => $eventDate->dayOfWeek,
                ];
            } catch (\Exception $exception) {
                $eventDate = null;
            }
        }

        $eventId = UrlUtils::decodeId($slug);

        if ($subdomainRole && $eventId) {
            $event = Event::with(['venue', 'roles'])
                ->where('id', $eventId)
                ->first();

            if ($event) {
                return $event;
            }
        }

        if ($eventDateContext) {
            $event = $this->findEventBySlugForDate($subdomain, $slug, $eventDateContext);

            if ($event) {
                return $event;
            }
        }

        $event = $this->findEventBySlug($subdomain, $slug);

        if ($event) {
            return $event;
        }

        if ($subdomainRole && $slugRole) {
            $venue = null;
            $role = null;

            if ($subdomainRole->isVenue()) {
                $venue = $subdomainRole;
            } elseif ($slugRole->isVenue()) {
                $venue = $slugRole;
            }

            if ($subdomainRole->isTalent()) {
                $role = $subdomainRole;
            } elseif ($slugRole->isTalent()) {
                $role = $slugRole;
            }

            if ($role && $venue) {
                if ($eventDateContext) {
                    $event = Event::with(['venue', 'roles'])
                        ->whereHas('roles', function ($query) use ($role) {
                            $query->where('role_id', $role->id);
                        })
                        ->whereHas('roles', function ($query) use ($venue) {
                            $query->where('role_id', $venue->id);
                        })
                        ->where(function ($query) use ($eventDateContext) {
                            $this->applyDateContext($query, $eventDateContext);
                        })
                        ->orderBy('starts_at')
                        ->first();
                } else {
                    $event = Event::with(['venue', 'roles'])
                        ->whereHas('roles', function ($query) use ($role) {
                            $query->where('role_id', $role->id);
                        })
                        ->whereHas('roles', function ($query) use ($venue) {
                            $query->where('role_id', $venue->id);
                        })
                        ->where('starts_at', '>=', now()->subDay())
                        ->orderBy('starts_at')
                        ->first();

                    if (! $event) {
                        $event = Event::with(['venue', 'roles'])
                            ->whereHas('roles', function ($query) use ($role) {
                                $query->where('role_id', $role->id);
                            })
                            ->whereHas('roles', function ($query) use ($venue) {
                                $query->where('role_id', $venue->id);
                            })
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

        if ($eventDateContext) {
            $event = Event::with(['venue', 'roles'])
                ->where(function ($query) use ($eventDateContext) {
                    $this->applyDateContext($query, $eventDateContext);
                })
                ->where(function ($query) use ($subdomain) {
                    $query->whereHas('roles', function ($q) use ($subdomain) {
                        $q->where('type', 'venue')
                            ->where('subdomain', $subdomain);
                    })->orWhereHas('roles', function ($q) use ($subdomain) {
                        $q->where('subdomain', $subdomain);
                    });
                })
                ->first();

            if ($event) {
                return $event;
            }
        }

        return null;
    }

    protected function findEventBySlugForDate(string $subdomain, string $slug, array $eventDateContext)
    {
        $query = $this->queryEventBySlug($subdomain, $slug);

        $event = (clone $query)
            ->where(function ($query) use ($eventDateContext) {
                $this->applyDateContext($query, $eventDateContext);
            })
            ->orderBy('starts_at')
            ->first();

        if ($event) {
            return $event;
        }

        return $this->findEventBySlug($subdomain, $slug);
    }

    protected function findEventBySlug(string $subdomain, string $slug)
    {
        $query = $this->queryEventBySlug($subdomain, $slug);

        $event = (clone $query)
            ->where('starts_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->first();

        if ($event) {
            return $event;
        }

        return (clone $query)
            ->orderBy('starts_at', 'desc')
            ->first();
    }

    protected function queryEventBySlug(string $subdomain, string $slug)
    {
        return Event::with(['venue', 'roles'])
            ->where('slug', $slug)
            ->where(function ($query) use ($subdomain) {
                $query->whereHas('roles', function ($q) use ($subdomain) {
                    $q->where('type', 'venue')
                        ->where('subdomain', $subdomain);
                })->orWhereHas('roles', function ($q) use ($subdomain) {
                    $q->where('subdomain', $subdomain);
                });
            });
    }

    protected function applyDateContext($query, array $eventDateContext): void
    {
        $query->whereBetween('starts_at', [$eventDateContext['startUtc'], $eventDateContext['endUtc']])
            ->orWhere(function ($query) use ($eventDateContext) {
                $query->whereNotNull('days_of_week')
                    ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDateContext['dayOfWeek'] + 1])
                    ->where('starts_at', '<=', $eventDateContext['endUtc']);
            });
    }


}
