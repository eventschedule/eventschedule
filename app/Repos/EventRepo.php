<?php

namespace App\Repos;

use App\Jobs\SendQueuedEmail;
use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use App\Models\Event;
use App\Models\EventPart;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\WebhookService;
use App\Utils\ColorUtils;
use App\Utils\GeminiUtils;
use App\Utils\SlugPatternUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventRepo
{
    /**
     * Get UTC date range for a date in a given timezone
     */
    private function getUtcDateRange(Carbon $date): array
    {
        return [
            $date->copy()->startOfDay()->utc(),
            $date->copy()->endOfDay()->utc(),
        ];
    }

    /**
     * Find event attached to both roles on a specific date
     */
    private function findEventForBothRoles(Role $subdomainRole, Role $slugRole, Carbon $eventDate): ?Event
    {
        [$startOfDay, $endOfDay] = $this->getUtcDateRange($eventDate);

        return Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
            ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
            ->whereHas('roles', fn ($q) => $q->where('role_id', $slugRole->id)->where('is_accepted', true))
            ->where(function ($query) use ($startOfDay, $endOfDay, $eventDate) {
                $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                    ->orWhere(function ($query) use ($eventDate, $endOfDay) {
                        $query->whereNotNull('days_of_week')
                            ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDate->dayOfWeek + 1])
                            ->where('starts_at', '<=', $endOfDay);
                    })
                    ->orWhere(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where('duration', '>=', 24)
                            ->where('starts_at', '<', $endOfDay)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$startOfDay]);
                    });
            })
            ->orderBy('starts_at')
            ->first();
    }

    /**
     * Find event by slug on a specific date
     */
    private function findEventBySlug(string $subdomain, string $slug, Carbon $eventDate): ?Event
    {
        [$startOfDay, $endOfDay] = $this->getUtcDateRange($eventDate);

        return Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
            ->where('slug', $slug)
            ->where(function ($query) use ($startOfDay, $endOfDay, $eventDate) {
                $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                    ->orWhere(function ($query) use ($eventDate) {
                        $query->whereNotNull('days_of_week')
                            ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDate->dayOfWeek + 1]);
                    })
                    ->orWhere(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where('duration', '>=', 24)
                            ->where('starts_at', '<', $endOfDay)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$startOfDay]);
                    });
            })
            ->where(function ($query) use ($subdomain) {
                $query->whereHas('roles', function ($q) use ($subdomain) {
                    $q->where('subdomain', $subdomain)->where('is_accepted', true);
                });
            })
            ->orderBy('starts_at')
            ->first();
    }

    /**
     * Find event by subdomain on a specific date (final fallback)
     */
    private function findEventBySubdomain(string $subdomain, Carbon $eventDate): ?Event
    {
        [$startOfDay, $endOfDay] = $this->getUtcDateRange($eventDate);

        return Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
            ->where(function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                    ->orWhere(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where('duration', '>=', 24)
                            ->where('starts_at', '<', $endOfDay)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$startOfDay]);
                    });
            })
            ->where(function ($query) use ($subdomain) {
                $query->whereHas('roles', function ($q) use ($subdomain) {
                    $q->where('subdomain', $subdomain)->where('is_accepted', true);
                });
            })
            ->first();
    }

    public function saveEvent($currentRole, $request, $event = null, $followNewRoles = true)
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

        if ($request->venue_name || $request->venue_address1 || $request->venue_address2 || $request->venue_city || $request->venue_state || $request->venue_postal_code || $request->venue_email || $request->venue_website) {
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
                $countryCode = $request->venue_country_code ? $request->venue_country_code : $currentRole->country_code;
                $venue->country_code = $countryCode ? strtolower($countryCode) : null;
                $venue->language_code = $request->venue_language_code ? $request->venue_language_code : $currentRole->language_code;
                $venue->timezone = $currentRole->timezone;
                $venue->website = $request->venue_website;
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

                    if (! $matchingUser->default_role_id) {
                        $matchingUser->default_role_id = $venue->id;
                        $matchingUser->save();
                    }
                }

                if ($followNewRoles && (! $matchingUser || $matchingUser->id != $user->id)) {
                    $user->roles()->attach($venue->id, ['level' => 'follower', 'created_at' => now()]);
                }
            } elseif ($venue && ! $venue->isClaimed()) {
                if ($request->venue_email) {
                    $venue->email = $request->venue_email;
                }

                $venue->name = $request->venue_name ?? null;
                $venue->address1 = $request->venue_address1;
                $venue->address2 = $request->venue_address2;
                $venue->city = $request->venue_city;
                $venue->state = $request->venue_state;
                $venue->postal_code = $request->venue_postal_code;
                $venue->country_code = $request->venue_country_code ? strtolower($request->venue_country_code) : null;
                $venue->website = $request->venue_website;
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
                    $countryCode = $request->country_code ? $request->country_code : $currentRole->country_code;
                    $role->country_code = $countryCode ? strtolower($countryCode) : null;
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

                        if (! $matchingUser->default_role_id) {
                            $matchingUser->default_role_id = $role->id;
                            $matchingUser->save();
                        }
                    }

                    if ($followNewRoles && (! $matchingUser || $matchingUser->id != $user->id)) {
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

        $isNewEvent = ! $event;
        if ($isNewEvent) {
            $event = new Event;
            $event->user_id = $user->id;
            $event->creator_role_id = $creatorRoleId;
        }

        // Decode event-level custom_fields from JSON string
        if ($request->has('custom_fields')) {
            $customFields = json_decode($request->input('custom_fields'), true);

            if (! empty($customFields)) {
                foreach ($customFields as $fieldKey => $fieldData) {
                    if (isset($fieldData['options'])) {
                        $customFields[$fieldKey]['options'] = implode(',', array_map('trim', explode(',', $fieldData['options'])));
                    }
                }
            }

            // Handle name_en for event-level custom fields
            if (! empty($customFields) && $currentRole && $currentRole->language_code !== 'en') {
                $existingCustomFields = $event && $event->custom_fields ? $event->custom_fields : [];
                $fieldsNeedingTranslation = [];

                foreach ($customFields as $fieldKey => $fieldData) {
                    if (empty($fieldData['name'])) {
                        continue;
                    }

                    if (! empty($fieldData['name_en'])) {
                        // User provided a manual English name - already set
                    } else {
                        // Check if name changed or name_en is missing
                        $existingField = $existingCustomFields[$fieldKey] ?? null;
                        $existingName = $existingField['name'] ?? null;
                        $existingNameEn = $existingField['name_en'] ?? null;

                        if ($existingNameEn && $existingName === $fieldData['name']) {
                            // Name unchanged, keep existing translation
                            $customFields[$fieldKey]['name_en'] = $existingNameEn;
                        } else {
                            // Name changed or no translation exists - mark for translation
                            $fieldsNeedingTranslation[$fieldKey] = $fieldData['name'];
                        }
                    }
                }

                // Batch translate field names that need translation
                if (! empty($fieldsNeedingTranslation)) {
                    try {
                        $translations = GeminiUtils::translateCustomFieldNames(
                            array_values($fieldsNeedingTranslation),
                            $currentRole->language_code
                        );

                        foreach ($fieldsNeedingTranslation as $fieldKey => $fieldName) {
                            if (isset($translations[$fieldName])) {
                                $customFields[$fieldKey]['name_en'] = $translations[$fieldName];
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to translate event custom field names: '.$e->getMessage());
                    }
                }
            }

            // De-duplicate indices as a safety net
            if (! empty($customFields)) {
                $usedIndices = [];
                foreach ($customFields as $fieldKey => $fieldData) {
                    $index = $fieldData['index'] ?? null;
                    if ($index && ! in_array($index, $usedIndices)) {
                        $usedIndices[] = $index;
                    } elseif ($index) {
                        // Duplicate index - reassign
                        for ($i = 1; $i <= 10; $i++) {
                            if (! in_array($i, $usedIndices)) {
                                $customFields[$fieldKey]['index'] = $i;
                                $usedIndices[] = $i;
                                break;
                            }
                        }
                    }
                }
            }

            $request->merge([
                'custom_fields' => $customFields,
            ]);
        }

        // Handle custom_field_values (event metadata fields defined at schedule level)
        if ($request->has('custom_field_values')) {
            $customFieldValues = $request->input('custom_field_values', []);
            // Filter out empty values
            $customFieldValues = array_filter($customFieldValues, function ($value) {
                return $value !== null && $value !== '';
            });
            // Validate dropdown and multiselect custom field values against allowed options
            $eventCustomFields = $currentRole->getEventCustomFields();
            foreach ($eventCustomFields as $fieldKey => $fieldConfig) {
                $fieldType = $fieldConfig['type'] ?? '';
                if ($fieldType === 'dropdown' && isset($customFieldValues[$fieldKey])) {
                    $allowedOptions = array_map('trim', explode(',', $fieldConfig['options'] ?? ''));
                    if (! in_array($customFieldValues[$fieldKey], $allowedOptions, true)) {
                        unset($customFieldValues[$fieldKey]);
                    }
                } elseif ($fieldType === 'multiselect' && isset($customFieldValues[$fieldKey])) {
                    $allowedOptions = array_map('trim', explode(',', $fieldConfig['options'] ?? ''));
                    $selectedValues = is_array($customFieldValues[$fieldKey])
                        ? array_map('trim', $customFieldValues[$fieldKey])
                        : array_map('trim', explode(',', $customFieldValues[$fieldKey]));
                    $validValues = array_filter($selectedValues, function ($v) use ($allowedOptions) {
                        return in_array($v, $allowedOptions, true);
                    });
                    $customFieldValues[$fieldKey] = ! empty($validValues) ? implode(', ', $validValues) : null;
                }
            }
            $request->merge([
                'custom_field_values' => ! empty($customFieldValues) ? $customFieldValues : null,
            ]);
        }

        $event->fill($request->all());

        if ($currentRole && ! $currentRole->isEnterprise()) {
            $event->is_private = false;
            $event->event_password = null;
        }

        // Handle slug update for existing events
        if (! $isNewEvent) {
            if ($request->filled('slug')) {
                $event->slug = Str::slug($request->slug) ?: $event->getOriginal('slug');
            } elseif ($currentRole?->slug_pattern
                && self::slugPatternFieldsChanged($currentRole->slug_pattern, $event)) {
                $event->slug = SlugPatternUtils::generateSlug(
                    $currentRole->slug_pattern,
                    $request->short_name ?: $request->name,
                    $request->short_name_en ?: $request->name_en,
                    $event,
                    $currentRole,
                    $venue
                );
            } else {
                $event->slug = $event->getOriginal('slug');
            }
        }

        // Generate slug after event data is populated (needs starts_at for date variables)
        if ($isNewEvent) {
            $event->slug = SlugPatternUtils::generateSlug(
                $currentRole?->slug_pattern,
                $request->short_name ?: $request->name,
                $request->short_name_en ?: $request->name_en,
                $event,
                $currentRole,
                $venue  // Pass venue directly since relationship isn't loaded yet
            );
        }

        // Handle recurring frequency and days_of_week
        if (request()->schedule_type == 'recurring') {
            $frequency = $request->input('recurring_frequency', 'weekly');
            $event->recurring_frequency = $frequency;

            if ($frequency === 'every_n_weeks') {
                $event->recurring_interval = max(2, (int) $request->input('recurring_interval', 2));
            } else {
                $event->recurring_interval = null;
            }

            if (in_array($frequency, ['weekly', 'every_n_weeks'])) {
                // Build days_of_week from checkboxes
                $days_of_week = '';
                $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
                foreach ($days as $index => $day) {
                    $days_of_week .= request()->has('days_of_week_'.$index) ? '1' : '0';
                }
                $event->days_of_week = $days_of_week;
            } else {
                // daily, monthly_date, monthly_weekday, yearly - set all days for query compatibility
                $event->days_of_week = '1111111';
            }
        } else {
            $event->days_of_week = null;
            $event->recurring_frequency = null;
            $event->recurring_interval = null;
        }

        // Handle recurring end configuration (only for recurring events)
        if (request()->schedule_type == 'recurring') {
            $event->recurring_end_type = $request->input('recurring_end_type', 'never');
            $event->recurring_end_value = $request->input('recurring_end_value');

            // Validate and clean up recurring_end_value based on type
            if ($event->recurring_end_type === 'never') {
                $event->recurring_end_value = null;
            } elseif ($event->recurring_end_type === 'on_date') {
                // Ensure it's a valid date format (YYYY-MM-DD)
                if ($event->recurring_end_value && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $event->recurring_end_value)) {
                    $event->recurring_end_value = null;
                }
            } elseif ($event->recurring_end_type === 'after_events') {
                // Ensure it's a positive integer
                $event->recurring_end_value = $event->recurring_end_value && is_numeric($event->recurring_end_value) && (int) $event->recurring_end_value > 0
                    ? (string) (int) $event->recurring_end_value
                    : null;
            }
        } else {
            // Clear recurring end fields for non-recurring events
            $event->recurring_end_type = 'never';
            $event->recurring_end_value = null;
        }

        // Handle recurring include/exclude dates
        if (request()->schedule_type == 'recurring') {
            $includeDates = array_filter(
                array_unique($request->input('recurring_include_dates', [])),
                fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)
            );
            sort($includeDates);
            $event->recurring_include_dates = ! empty($includeDates) ? array_values($includeDates) : null;

            $excludeDates = array_filter(
                array_unique($request->input('recurring_exclude_dates', [])),
                fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)
            );
            sort($excludeDates);
            $event->recurring_exclude_dates = ! empty($excludeDates) ? array_values($excludeDates) : null;
        } else {
            $event->recurring_include_dates = null;
            $event->recurring_exclude_dates = null;
        }

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

        // Handle nullable feedback_enabled (empty string = null = use schedule default)
        if ($currentRole && $currentRole->isPro() && $request->has('feedback_enabled')) {
            $val = $request->input('feedback_enabled');
            $event->feedback_enabled = $val === '' || $val === null ? null : (bool) $val;
        }

        // Handle nullable fan content fields (empty string = null = use schedule default)
        foreach (['fan_comments_enabled', 'fan_photos_enabled', 'fan_videos_enabled'] as $fanField) {
            if ($request->has($fanField)) {
                $val = $request->input($fanField);
                $event->$fanField = $val === '' || $val === null ? null : (bool) $val;
            }
        }

        // Handle event sponsor logos (Pro feature)
        if ($currentRole && $currentRole->isPro() && ! is_demo_mode()) {
            $sponsorMode = $request->input('sponsor_mode', 'default');
            $event->sponsor_mode = in_array($sponsorMode, ['default', 'none', 'custom']) ? $sponsorMode : 'default';

            if ($sponsorMode === 'custom') {
                $oldSponsors = json_decode($event->getOriginal('sponsor_logos') ?? '[]', true) ?: [];
                $oldLogoFiles = array_filter(array_column($oldSponsors, 'logo'));

                // Process existing sponsors (reordered via drag-and-drop)
                $existingSponsorsJson = $request->input('existing_event_sponsors', '[]');
                $sponsors = json_decode($existingSponsorsJson, true) ?: [];

                // Process new sponsor uploads
                $newFiles = $request->file('event_sponsor_logos', []);
                $newNames = $request->input('event_sponsor_names', []);
                $newUrls = $request->input('event_sponsor_urls', []);
                $newTiers = $request->input('event_sponsor_tiers', []);

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

                foreach ($newFiles as $index => $file) {
                    if (count($sponsors) >= 12) {
                        break;
                    }

                    $extension = strtolower($file->getClientOriginalExtension());
                    if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                        continue;
                    }

                    $filename = strtolower('sponsor_'.Str::random(32).'.'.$extension);
                    $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                    $sponsors[] = [
                        'name' => $newNames[$index] ?? '',
                        'logo' => $filename,
                        'url' => ! empty($newUrls[$index]) ? $newUrls[$index] : null,
                        'tier' => $newTiers[$index] ?? '',
                    ];
                }

                // Cap at 12
                $sponsors = array_slice($sponsors, 0, 12);

                // Delete orphaned logo files
                $currentLogoFiles = array_filter(array_column($sponsors, 'logo'));
                $orphanedFiles = array_diff($oldLogoFiles, $currentLogoFiles);
                foreach ($orphanedFiles as $orphanedFile) {
                    if (str_starts_with($orphanedFile, 'demo_')) {
                        continue;
                    }
                    $path = $orphanedFile;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }

                $event->sponsor_logos = ! empty($sponsors) ? json_encode(array_values($sponsors)) : null;
            } else {
                // Switching away from custom: clean up old logo files
                $oldSponsors = json_decode($event->getOriginal('sponsor_logos') ?? '[]', true) ?: [];
                foreach ($oldSponsors as $sponsor) {
                    if (! empty($sponsor['logo']) && ! str_starts_with($sponsor['logo'], 'demo_')) {
                        $path = $sponsor['logo'];
                        if (config('filesystems.default') == 'local') {
                            $path = 'public/'.$path;
                        }
                        Storage::delete($path);
                    }
                }
                $event->sponsor_logos = null;
            }
        } else {
            $event->sponsor_mode = null;
            $event->sponsor_logos = null;
        }

        $event->save();

        if ($venue) {
            $roles[] = $venue;
            $roleIds[] = $venue->id;
        }

        $selectedCurators = $request->input('curators', []);
        $selectedCurators = array_map(function ($id) {
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
                if (! in_array($curatorId, $selectedCurators)) {
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
            if ((auth()->user() && $user->isMember($role->subdomain))
                || ($role->accept_requests && ! $role->require_approval)
                || ($currentRole && $role->approved_subdomains
                    && in_array($currentRole->subdomain, $role->approved_subdomains))) {
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
            $file = $request->file('flyer_image');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                throw ValidationException::withMessages([
                    'flyer_image' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp',
                ]);
            }

            if ($event->flyer_image_url) {
                $path = $event->getAttributes()['flyer_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('flyer_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $event->flyer_image_url = $filename;
            $event->save();
        }

        if (! $request->hasFile('flyer_image') && $request->input('ai_flyer_image')) {
            $aiFilename = $request->input('ai_flyer_image');
            if (preg_match('/^flyer_[a-z0-9]+\.png$/', $aiFilename)) {
                if ($event->flyer_image_url) {
                    $path = $event->getAttributes()['flyer_image_url'];
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
                $event->flyer_image_url = $aiFilename;
                $event->save();
            }
        }

        if (config('app.hosted')) {
            $sendEmailToMembers = $request->input('send_email_to_members', []);

            foreach ($roles as $role) {
                if (! $role->isClaimed() && $role->is_subscribed && $role->email) {
                    $shouldSendEmail = false;

                    if ($role->isVenue()) {
                        // Check if send_email_to_venue checkbox is checked
                        // Checkbox values can be "1", "on", or true - all are truthy
                        $shouldSendEmail = ! empty($request->input('send_email_to_venue', false));
                    } elseif ($role->isTalent()) {
                        // Check if this role's email is in the send_email_to_members array
                        // The array uses email addresses as keys
                        // Checkbox values can be "1", "on", or true - all are truthy
                        $shouldSendEmail = ! empty($sendEmailToMembers[$role->email]);
                    }

                    if ($shouldSendEmail) {
                        if ($role->isVenue()) {
                            SendQueuedEmail::dispatch(
                                new ClaimVenue($event),
                                $role->email
                            );
                        } elseif ($role->isTalent()) {
                            SendQueuedEmail::dispatch(
                                new ClaimRole($event),
                                $role->email
                            );
                        }
                    }
                }
            }
        }

        if ($event->tickets_enabled) {
            $ticketData = $request->input('tickets', []);
            $ticketIds = [];

            foreach ($ticketData as $data) {
                // Process custom_fields with name_en translation
                $ticketCustomFields = isset($data['custom_fields']) ? json_decode($data['custom_fields'], true) : null;

                if (! empty($ticketCustomFields)) {
                    foreach ($ticketCustomFields as $fieldKey => $fieldData) {
                        if (isset($fieldData['options'])) {
                            $ticketCustomFields[$fieldKey]['options'] = implode(',', array_map('trim', explode(',', $fieldData['options'])));
                        }
                    }
                }

                if (! empty($ticketCustomFields) && $currentRole && $currentRole->language_code !== 'en') {
                    $existingTicket = ! empty($data['id']) ? Ticket::find($data['id']) : null;
                    $existingCustomFields = $existingTicket && $existingTicket->custom_fields ? $existingTicket->custom_fields : [];
                    $fieldsNeedingTranslation = [];

                    foreach ($ticketCustomFields as $fieldKey => $fieldData) {
                        if (empty($fieldData['name'])) {
                            continue;
                        }

                        if (empty($fieldData['name_en'])) {
                            $existingField = $existingCustomFields[$fieldKey] ?? null;
                            $existingName = $existingField['name'] ?? null;
                            $existingNameEn = $existingField['name_en'] ?? null;

                            if ($existingNameEn && $existingName === $fieldData['name']) {
                                $ticketCustomFields[$fieldKey]['name_en'] = $existingNameEn;
                            } else {
                                $fieldsNeedingTranslation[$fieldKey] = $fieldData['name'];
                            }
                        }
                    }

                    if (! empty($fieldsNeedingTranslation)) {
                        try {
                            $translations = GeminiUtils::translateCustomFieldNames(
                                array_values($fieldsNeedingTranslation),
                                $currentRole->language_code
                            );

                            foreach ($fieldsNeedingTranslation as $fieldKey => $fieldName) {
                                if (isset($translations[$fieldName])) {
                                    $ticketCustomFields[$fieldKey]['name_en'] = $translations[$fieldName];
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to translate ticket custom field names: '.$e->getMessage());
                        }
                    }
                }

                // De-duplicate indices as a safety net
                if (! empty($ticketCustomFields)) {
                    $usedIndices = [];
                    foreach ($ticketCustomFields as $fieldKey => $fieldData) {
                        $index = $fieldData['index'] ?? null;
                        if ($index && ! in_array($index, $usedIndices)) {
                            $usedIndices[] = $index;
                        } elseif ($index) {
                            for ($i = 1; $i <= 10; $i++) {
                                if (! in_array($i, $usedIndices)) {
                                    $ticketCustomFields[$fieldKey]['index'] = $i;
                                    $usedIndices[] = $i;
                                    break;
                                }
                            }
                        }
                    }
                }

                $salesStartAt = ! empty($data['sales_start_at']) ? $data['sales_start_at'] : null;
                $salesEndAt = ! empty($data['sales_end_at']) ? $data['sales_end_at'] : null;

                if (! empty($data['id'])) {
                    $ticket = Ticket::find($data['id']);
                    $ticketIds[] = $ticket->id;
                    if ($ticket && $ticket->event_id == $event->id) {
                        $ticket->update([
                            'type' => $data['type'] ?? null,
                            'quantity' => $data['quantity'] ?? null,
                            'price' => $data['price'] ?? null,
                            'description' => $data['description'] ?? null,
                            'sales_start_at' => $salesStartAt,
                            'sales_end_at' => $salesEndAt,
                            'custom_fields' => $ticketCustomFields,
                        ]);
                    }
                } else {
                    $ticket = Ticket::create([
                        'event_id' => $event->id,
                        'type' => $data['type'] ?? null,
                        'quantity' => $data['quantity'] ?? null,
                        'price' => $data['price'] ?? null,
                        'description' => $data['description'] ?? null,
                        'sales_start_at' => $salesStartAt,
                        'sales_end_at' => $salesEndAt,
                        'custom_fields' => $ticketCustomFields,
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

        // Save add-ons
        if ($event->tickets_enabled) {
            $addonData = $request->input('addons', []);
            $addonImageData = $request->input('addon_image_data', []);
            $addonIds = [];

            foreach ($addonData as $index => $data) {
                if (empty($data['type'])) {
                    continue;
                }

                if (! empty($data['id'])) {
                    $addon = Ticket::find($data['id']);
                    if ($addon && $addon->event_id == $event->id && $addon->is_addon) {
                        $addon->update([
                            'type' => $data['type'] ?? null,
                            'quantity' => $data['quantity'] ?? null,
                            'price' => $data['price'] ?? null,
                            'description' => $data['description'] ?? null,
                            'url' => $data['url'] ?? null,
                        ]);
                        $addonIds[] = $addon->id;
                    }
                } else {
                    $addon = Ticket::create([
                        'event_id' => $event->id,
                        'type' => $data['type'] ?? null,
                        'quantity' => $data['quantity'] ?? null,
                        'price' => $data['price'] ?? null,
                        'description' => $data['description'] ?? null,
                        'url' => $data['url'] ?? null,
                        'is_addon' => true,
                    ]);
                    $addonIds[] = $addon->id;
                }

                // Handle addon image removal
                if (! empty($data['remove_image'])) {
                    $rawImageUrl = $addon->getAttributes()['image_url'] ?? null;
                    if ($rawImageUrl) {
                        $path = $rawImageUrl;
                        if (config('filesystems.default') == 'local') {
                            $path = 'public/'.$path;
                        }
                        Storage::delete($path);
                    }
                    $addon->update(['image_url' => null]);
                }

                // Handle addon image from base64 data URL
                if (isset($addonImageData[$index]) && str_starts_with($addonImageData[$index], 'data:image/')) {
                    $dataUrl = $addonImageData[$index];
                    $commaPos = strpos($dataUrl, ',');

                    if ($commaPos !== false) {
                        $header = substr($dataUrl, 0, $commaPos);
                        $base64Data = substr($dataUrl, $commaPos + 1);

                        if (preg_match('/^data:image\/(jpe?g|png|gif|webp);base64$/', $header, $matches)) {
                            $extension = ($matches[1] === 'jpeg' || $matches[1] === 'jpg') ? 'jpg' : $matches[1];
                            $content = base64_decode($base64Data, true);

                            if ($content !== false) {
                                $imageInfo = @getimagesizefromstring($content);
                                if ($imageInfo !== false) {
                                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    if (in_array($imageInfo['mime'], $allowedMimeTypes)) {
                                        // Delete old image if exists
                                        $rawImageUrl = $addon->getAttributes()['image_url'] ?? null;
                                        if ($rawImageUrl) {
                                            $path = $rawImageUrl;
                                            if (config('filesystems.default') == 'local') {
                                                $path = 'public/'.$path;
                                            }
                                            Storage::delete($path);
                                        }

                                        $filename = strtolower('addon_'.Str::random(32).'.'.$extension);
                                        Storage::put(
                                            config('filesystems.default') == 'local' ? 'public/'.$filename : $filename,
                                            $content
                                        );
                                        $addon->update(['image_url' => $filename]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Clean up images for addons being soft-deleted
            $addonsToDelete = $event->addons()
                ->whereNotIn('id', $addonIds)
                ->whereNotNull('image_url')
                ->get();
            foreach ($addonsToDelete as $addonToDelete) {
                $rawImageUrl = $addonToDelete->getAttributes()['image_url'] ?? null;
                if ($rawImageUrl) {
                    $path = $rawImageUrl;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
            }

            $event->addons()
                ->whereNotIn('id', $addonIds)
                ->update(['is_deleted' => true]);
        } else {
            // Clean up images for all addons before soft-deleting
            $addonsWithImages = $event->addons()
                ->whereNotNull('image_url')
                ->get();
            foreach ($addonsWithImages as $addonToDelete) {
                $rawImageUrl = $addonToDelete->getAttributes()['image_url'] ?? null;
                if ($rawImageUrl) {
                    $path = $rawImageUrl;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
            }

            $event->addons()->update(['is_deleted' => true]);
        }

        // Save promo codes
        if ($event->tickets_enabled) {
            $promoData = $request->input('promo_codes', []);
            $promoIds = [];

            foreach ($promoData as $data) {
                if (empty($data['code'])) {
                    continue;
                }

                $ticketIds = null;
                if (! empty($data['ticket_ids'])) {
                    $decoded = is_string($data['ticket_ids']) ? json_decode($data['ticket_ids'], true) : $data['ticket_ids'];
                    $ticketIds = ! empty($decoded) ? $decoded : null;
                }

                $promoFields = [
                    'event_id' => $event->id,
                    'code' => strtoupper(trim($data['code'])),
                    'type' => $data['type'] ?? 'percentage',
                    'value' => $data['value'] ?? 0,
                    'max_uses' => ! empty($data['max_uses']) ? $data['max_uses'] : null,
                    'expires_at' => ! empty($data['expires_at']) ? $data['expires_at'] : null,
                    'is_active' => ! empty($data['is_active']),
                    'ticket_ids' => $ticketIds,
                ];

                if (! empty($data['id'])) {
                    $promoCode = PromoCode::find($data['id']);
                    if ($promoCode && $promoCode->event_id == $event->id) {
                        $promoCode->update($promoFields);
                        $promoIds[] = $promoCode->id;
                    }
                } else {
                    $promoCode = PromoCode::create($promoFields);
                    $promoIds[] = $promoCode->id;
                }
            }

            PromoCode::where('event_id', $event->id)
                ->whereNotIn('id', $promoIds)
                ->delete();

            // Clear cached IN subscription so it gets recreated with updated promo code
            if ($event->invoiceninja_subscription_id) {
                $event->invoiceninja_subscription_id = null;
                $event->invoiceninja_subscription_url = null;
                $event->save();
            }
        }

        // Save event parts
        $partsData = $request->input('event_parts', []);
        $partIds = [];
        foreach ($partsData as $index => $partData) {
            if (empty($partData['name'])) {
                continue;
            }
            if (! empty($partData['id'])) {
                $part = EventPart::find($partData['id']);
                if ($part && $part->event_id == $event->id) {
                    $part->update([
                        'name' => $partData['name'],
                        'description' => $partData['description'] ?? null,
                        'start_time' => $partData['start_time'] ?? null,
                        'end_time' => $partData['end_time'] ?? null,
                        'sort_order' => $index,
                    ]);
                    $partIds[] = $part->id;
                }
            } else {
                $part = EventPart::create([
                    'event_id' => $event->id,
                    'name' => $partData['name'],
                    'description' => $partData['description'] ?? null,
                    'start_time' => $partData['start_time'] ?? null,
                    'end_time' => $partData['end_time'] ?? null,
                    'sort_order' => $index,
                ]);
                $partIds[] = $part->id;
            }
        }
        // Delete removed parts
        $event->parts()->whereNotIn('id', $partIds)->delete();

        $event->load(['tickets', 'addons', 'roles']);

        // Sync to Google Calendar for the current role
        if ($currentRole && $currentRole->syncsToGoogle()) {
            if ($event->wasRecentlyCreated) {
                $event->syncToGoogleCalendar('create');
            } else {
                $event->syncToGoogleCalendar('update');
            }
        }

        // Sync to CalDAV for the current role
        if ($currentRole && $currentRole->syncsToCalDAV()) {
            if ($event->wasRecentlyCreated) {
                $event->syncToCalDAV('create');
            } else {
                $event->syncToCalDAV('update');
            }
        }

        // Dispatch webhook
        WebhookService::dispatch(
            $event->wasRecentlyCreated ? 'event.created' : 'event.updated',
            $event
        );

        return $event;
    }

    public function getEvent($subdomain, $slug, $date = null, $eventId = null, ?Role $role = null)
    {
        $event = null;

        $subdomainRole = $role ?? Role::where('subdomain', $subdomain)->first();
        // Use explicit event ID if provided, otherwise try to decode from slug
        $lookupEventId = $eventId ?: UrlUtils::decodeId($slug);

        // Parse dates with timezone context - local timezone first, then UTC as fallback
        $roleTimezone = $subdomainRole?->timezone ?? config('app.timezone');
        $validDate = $date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
        $eventDateLocal = $validDate ? Carbon::parse($date, $roleTimezone) : null;
        $eventDateUtc = $validDate ? Carbon::parse($date, 'UTC') : null;

        if ($subdomainRole && $lookupEventId) {
            $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                ->where('id', $lookupEventId)
                ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
                ->first();

            if ($event) {
                return $event;
            }
        }

        // Only load $slugRole when actually needed (ID lookup failed)
        $slugRole = Role::where('subdomain', $slug)->first();

        // Find events attached to both roles (handles all combinations: venue+talent, curator+venue, curator+talent, etc.)
        if ($subdomainRole && $slugRole && $slugRole->isClaimed()) {
            // Try local timezone interpretation first
            if ($eventDateLocal) {
                $event = $this->findEventForBothRoles($subdomainRole, $slugRole, $eventDateLocal);
            }

            // Fallback to UTC interpretation if local didn't find anything
            if (! $event && $eventDateUtc && $eventDateLocal->format('Y-m-d') !== $eventDateUtc->format('Y-m-d')) {
                $event = $this->findEventForBothRoles($subdomainRole, $slugRole, $eventDateUtc);
            }

            // No date provided - find most recent/upcoming
            if (! $event && ! $date) {
                $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $slugRole->id)->where('is_accepted', true))
                    ->where(function ($q) {
                        $q->where('starts_at', '>=', now()->subDay())
                            ->orWhere(function ($q2) {
                                $q2->where('duration', '>=', 24)
                                    ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                            });
                    })
                    ->orderBy('starts_at')
                    ->first();

                if (! $event) {
                    $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                        ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
                        ->whereHas('roles', fn ($q) => $q->where('role_id', $slugRole->id)->where('is_accepted', true))
                        ->where('starts_at', '<', now())
                        ->orderBy('starts_at', 'desc')
                        ->first();
                }
            }

            if ($event) {
                return $event;
            }
        }

        // Try slug-based search with local timezone first
        if ($eventDateLocal) {
            $event = $this->findEventBySlug($subdomain, $slug, $eventDateLocal);

            // Fallback to UTC interpretation if local didn't find anything
            if (! $event && $eventDateLocal->format('Y-m-d') !== $eventDateUtc->format('Y-m-d')) {
                $event = $this->findEventBySlug($subdomain, $slug, $eventDateUtc);
            }
        } else {
            $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                ->where('slug', $slug)
                ->where(function ($q) {
                    $q->where('starts_at', '>=', now()->subDay())
                        ->orWhere(function ($q2) {
                            $q2->where('duration', '>=', 24)
                                ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                        });
                })
                ->where(function ($query) use ($subdomain) {
                    $query->whereHas('roles', function ($q) use ($subdomain) {
                        $q->where('subdomain', $subdomain)->where('is_accepted', true);
                    });
                })
                ->orderBy('starts_at', 'desc')
                ->first();

            if (! $event) {
                $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                    ->where('slug', $slug)
                    ->where('starts_at', '<', now())
                    ->where(function ($query) use ($subdomain) {
                        $query->whereHas('roles', function ($q) use ($subdomain) {
                            $q->where('subdomain', $subdomain)->where('is_accepted', true);
                        });
                    })
                    ->orderBy('starts_at', 'desc')
                    ->first();
            }
        }

        if ($event) {
            return $event;
        }

        // Final fallback: try subdomain-based search with local timezone first
        if ($eventDateLocal) {
            $event = $this->findEventBySubdomain($subdomain, $eventDateLocal);

            // Fallback to UTC interpretation if local didn't find anything
            if (! $event && $eventDateLocal->format('Y-m-d') !== $eventDateUtc->format('Y-m-d')) {
                $event = $this->findEventBySubdomain($subdomain, $eventDateUtc);
            }
        }

        return $event;
    }

    private static function slugPatternFieldsChanged(string $pattern, Event $event): bool
    {
        // Date/time variables -> starts_at/ends_at
        if (preg_match('/\{(day|month|year|day_name|day_short|date_dmy|date_mdy|date_full_dmy|date_full_mdy|month_name|month_short|time|end_time|duration)\}/', $pattern)
            && $event->isDirty(['starts_at', 'ends_at'])) {
            return true;
        }

        // Event name variable -> name/short_name
        if (str_contains($pattern, '{event_name}')
            && $event->isDirty(['name', 'short_name', 'name_en', 'short_name_en'])) {
            return true;
        }

        // Venue variables -> venue_id
        if (preg_match('/\{(venue|city|address|state|country)\}/', $pattern)
            && $event->isDirty('venue_id')) {
            return true;
        }

        // Custom field variables -> custom_field_values
        if (str_contains($pattern, '{custom_')
            && $event->isDirty('custom_field_values')) {
            return true;
        }

        return false;
    }
}
