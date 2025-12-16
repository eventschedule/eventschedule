<?php

namespace App\Repos;

use App\Models\Event;
use App\Models\Image;
use App\Models\Role;
use App\Models\User;
use App\Utils\ColorUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use App\Notifications\EventAddedNotification;
use App\Utils\NotificationUtils;
use App\Models\Ticket;
use App\Support\MailConfigManager;
use App\Support\MailTemplateManager;
use App\Models\MediaAsset;
use App\Models\MediaAssetVariant;
use App\Models\MediaAssetUsage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class EventRepo
{
    public function saveEvent($currentRole, $request, $event = null)
    {
        $context = [
            'event_id' => $event?->id,
            'current_role_id' => $currentRole?->id,
            'user_id' => $request->user()?->id,
        ];

        $inputSummary = [
            'name' => $request->input('name'),
            'name_en' => $request->input('name_en'),
            'schedule_type' => $request->input('schedule_type'),
            'starts_at' => $request->input('starts_at'),
            'tickets_enabled' => (bool) $request->input('tickets_enabled'),
            'members_count' => is_array($request->input('members')) ? count($request->input('members')) : 0,
            'curators_count' => is_array($request->input('curators')) ? count($request->input('curators')) : 0,
        ];

        Log::info('EventRepo.saveEvent.start', $context + ['input_summary' => $inputSummary]);

        try {
            $user = $request->user();
            $venue = null;
            $roomId = null;

            // Set creator_role_id to the current role
            $creatorRoleId = $currentRole ? $currentRole->id : null;

            // Check if venue_id is explicitly provided and not null/empty
            // This allows clearing venue_id for online-only events
            $hasVenueId = $request->has('venue_id') && $request->venue_id !== null && $request->venue_id !== '';
            
            if ($hasVenueId) {
                $venue = Role::findOrFail(UrlUtils::decodeId($request->venue_id));
            }

            if (! $user) {
                $user = $currentRole->user;
            }

            // Only process venue address if we have a venue_id (updating existing venue)
            // OR if venue_id was not sent at all (creating new venue)
            // Do NOT process venue address if venue_id was explicitly sent as null/empty (clearing venue for online-only)
            $shouldProcessVenueAddress = $request->filled('venue_address1') && ($hasVenueId || !$request->has('venue_id'));
            
            if ($shouldProcessVenueAddress) {
                if (! $venue) {
                    Log::debug('EventRepo.saveEvent.creatingVenue', $context + ['venue_email' => $request->venue_email]);

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

                    Log::debug('EventRepo.saveEvent.updatedVenue', $context + ['venue_id' => $venue->id]);
                }
            }

            if ($request->filled('venue_room_id')) {
                $roomId = UrlUtils::decodeId($request->venue_room_id);
            }

            if ($roomId && $venue) {
                $roomBelongsToVenue = $venue->rooms()->where('id', $roomId)->exists();

                if (! $roomBelongsToVenue) {
                    Log::warning('EventRepo.saveEvent.invalidRoomForVenue', $context + [
                        'venue_id' => $venue->id,
                        'room_id' => $roomId,
                    ]);

                    $roomId = null;
                }
            } else {
                $roomId = null;
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
                Log::debug('EventRepo.saveEvent.creatingEvent', $context + ['venue_id' => $venueId]);

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

            // Prevent attempting to persist legacy venue_id column; venue linkage is
            // handled via the pivot table instead.
            $input = Arr::except($input, ['venue_id', 'venue_room_id']);

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

            // Handle password input: convert plaintext password to a secure hash
            $privateFlag = array_key_exists('event_private', $input) ? (bool) $input['event_private'] : null;

            if ($privateFlag === false) {
                // User explicitly opted out of privacy: clear existing password
                $event->event_password_hash = null;
            } elseif ($privateFlag === true) {
                if (array_key_exists('event_password', $input)) {
                    if ($input['event_password'] && trim($input['event_password']) !== '') {
                        $event->event_password_hash = \Illuminate\Support\Facades\Hash::make($input['event_password']);
                    } else {
                        // No new password provided, and privacy flag is true: keep existing hash
                    }
                    unset($input['event_password']);
                }
            } else {
                // No explicit privacy flag present in input (e.g., unchecked checkbox does not submit)
                if (array_key_exists('event_password', $input)) {
                    if ($input['event_password'] && trim($input['event_password']) !== '') {
                        $event->event_password_hash = \Illuminate\Support\Facades\Hash::make($input['event_password']);
                    } else {
                        // Empty password provided: clear existing
                        $event->event_password_hash = null;
                    }
                    unset($input['event_password']);
                } else {
                    // No password field present: preserve existing hash (common when event_private unchecked)
                }
            }

            $event->fill($input);

            // Explicitly handle event_url to allow clearing it for in-person only events
            if ($request->has('event_url')) {
                $event->event_url = $request->event_url; // Will be null if sent as null
            }

            $days_of_week = '';
            $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            foreach ($days as $index => $day) {
                $days_of_week .= request()->has('days_of_week_' . $index) ? '1' : '0';
            }
            $event->days_of_week = request()->schedule_type == 'recurring' ? $days_of_week : null;

            $event->timezone = $event->timezone ?: ($user->timezone ?? config('app.timezone', 'UTC'));

            if ($event->starts_at) {
                $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, $event->timezone)
                    ->setTimezone('UTC')
                    ->format('Y-m-d H:i:s');
            }

            if ($request->has('flyer_image_id')) {
                $imageId = $request->input('flyer_image_id');
                if ($imageId) {
                    $image = Image::find($imageId);
                    if ($image) {
                        $event->flyer_image_id = $image->id;
                        $event->flyer_image_url = $image->path;
                    }
                } else {
                    $event->flyer_image_id = null;
                    $event->flyer_image_url = null;
                }
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

            Log::debug('EventRepo.saveEvent.persistingEvent', $context + ['changes' => Arr::except($event->getDirty(), ['updated_at'])]);

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

            $roleIds = array_values(array_unique($roleIds));

            $pivotData = [];
            foreach ($roleIds as $roleId) {
                $pivotData[$roleId] = [];
            }

            if ($venueId && $roomId) {
                $pivotData[$venueId]['room_id'] = $roomId;
            }

            $event->roles()->sync($pivotData);

            $curatorGroups = $request->input('curator_groups', []);

            foreach ($roles as $role) {
                if ((auth()->user() && $user->isMember($role->subdomain)) || ($role->accept_requests && ! $role->require_approval)) {
                    $updateData = ['is_accepted' => true];
                    // Preserve room_id if this is the venue role
                    if ($role->id === $venueId && $roomId) {
                        $updateData['room_id'] = $roomId;
                    }
                    $event->roles()->updateExistingPivot($role->id, $updateData);
                }

                // If this is a curator and curator_groups is provided, add it to the pivot
                if ($role && $role->isCurator()) {
                    $curatorEncodedId = UrlUtils::encodeId($role->id);
                    if (isset($curatorGroups[$curatorEncodedId]) && $curatorGroups[$curatorEncodedId]) {
                        $groupId = UrlUtils::decodeId($curatorGroups[$curatorEncodedId]);
                        $updateData = ['group_id' => $groupId];
                        // Preserve room_id if this is the venue role
                        if ($role->id === $venueId && $roomId) {
                            $updateData['room_id'] = $roomId;
                        }
                        $event->roles()->updateExistingPivot($role->id, $updateData);
                    }
                }

                // If this is the current role and current_role_group_id is provided, add it to the pivot
                if ($role && $role->id === $currentRole->id && $request->has('current_role_group_id') && $request->current_role_group_id) {
                    $groupId = UrlUtils::decodeId($request->current_role_group_id);
                    $updateData = ['group_id' => $groupId];
                    // Preserve room_id if this is the venue role
                    if ($role->id === $venueId && $roomId) {
                        $updateData['room_id'] = $roomId;
                    }
                    $event->roles()->updateExistingPivot($role->id, $updateData);
                }
            }

            if ($request->hasFile('flyer_image')) {
                $disk = storage_public_disk();

                if ($event->flyer_image_url) {
                    $path = storage_normalize_path($event->getAttributes()['flyer_image_url']);
                    if ($path !== '') {
                        Storage::disk($disk)->delete($path);
                    }
                }

                $file = $request->file('flyer_image');
                $filename = strtolower('flyer_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
                storage_put_file_as_public($disk, $file, $filename);

                $event->flyer_image_url = $filename;
                $event->save();

                MediaAssetUsage::clearUsage($event, 'flyer');

                Log::debug('EventRepo.saveEvent.flyerUploaded', $context + ['flyer_path' => $filename]);
            } elseif ($request->filled('flyer_media_asset_id')) {
                $assetId = (int) $request->input('flyer_media_asset_id');
                $asset = MediaAsset::find($assetId);

                if ($asset) {
                    $variant = null;

                    if ($request->filled('flyer_media_variant_id')) {
                        $variantId = (int) $request->input('flyer_media_variant_id');
                        $variant = MediaAssetVariant::where('media_asset_id', $asset->id)
                            ->find($variantId);
                    }

                    $event->flyer_image_url = $variant ? $variant->path : $asset->path;
                    $event->save();

                    MediaAssetUsage::recordUsage($event, 'flyer', $asset, $variant);

                    Log::debug('EventRepo.saveEvent.flyerLinked', $context + ['asset_id' => $asset->id, 'variant_id' => $variant?->id]);
                }
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

                Log::debug('EventRepo.saveEvent.ticketsProcessed', $context + ['ticket_ids' => $ticketIds]);
            } else {
                $event->tickets()->update(['is_deleted' => true]);
                Log::debug('EventRepo.saveEvent.ticketsDisabled', $context);
            }

            $event->load('tickets');

            if ($event->wasRecentlyCreated) {
                $event->loadMissing(['roles.members', 'venue.members']);

                $talentRoles = $event->roles->filter(fn ($roleModel) => $roleModel->isTalent());

                NotificationUtils::uniqueRoleMembersWithContext($talentRoles)->each(function (array $recipient) use ($event, $user) {
                    $recipient['user']->notify(new EventAddedNotification($event, $user, 'talent', $recipient['role']));
                });

                if ($event->venue) {
                    NotificationUtils::uniqueRoleMembersWithContext([$event->venue])->each(function (array $recipient) use ($event, $user) {
                        $recipient['user']->notify(new EventAddedNotification($event, $user, 'purchaser', $recipient['role']));
                    });
                }
            }

            Log::info('EventRepo.saveEvent.success', $context + [
                'event_id' => $event->id,
                'was_recently_created' => $event->wasRecentlyCreated,
                'tickets_enabled' => (bool) $event->tickets_enabled,
                'role_ids' => $roleIds,
                'curator_ids' => array_values($selectedCurators),
            ]);

            return $event;
        } catch (\Throwable $exception) {
            Log::error('EventRepo.saveEvent.failed', $context + [
                'exception_message' => $exception->getMessage(),
                'exception_class' => get_class($exception),
            ]);

            throw $exception;
        }
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
