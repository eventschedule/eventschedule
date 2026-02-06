<?php

namespace App\Models;

use App\Jobs\SyncEventToCalDAV;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    protected $fillable = [
        'starts_at',
        'duration',
        'description',
        'description_en',
        'short_description',
        'short_description_en',
        'event_url',
        'event_password',
        'name',
        'name_en',
        'slug',
        'tickets_enabled',
        'ticket_currency_code',
        'ticket_notes',
        'terms_url',
        'total_tickets_mode',
        'payment_method',
        'payment_instructions',
        'expire_unpaid_tickets',
        'registration_url',
        'category_id',
        'creator_role_id',
        'recurring_end_type',
        'recurring_end_value',
        'recurring_frequency',
        'recurring_interval',
        'custom_fields',
        'custom_field_values',
        'agenda_ai_prompt',
        'translation_attempts',
        'last_translated_at',
        'last_notified_fan_content_count',
    ];

    protected $casts = [
        'duration' => 'float',
        'custom_fields' => 'array',
        'custom_field_values' => 'array',
        'last_translated_at' => 'datetime',
    ];

    /**
     * Strip query parameters from registration_url before saving
     */
    public function setRegistrationUrlAttribute($value)
    {
        if ($value && is_string($value)) {
            // Remove everything after the first '?' (query parameters)
            $this->attributes['registration_url'] = explode('?', $value)[0];
        } else {
            $this->attributes['registration_url'] = $value;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
            $model->description_html_en = MarkdownUtils::convertToHtml($model->description_en);
            $model->ticket_notes_html = MarkdownUtils::convertToHtml($model->ticket_notes);
            $model->payment_instructions_html = MarkdownUtils::convertToHtml($model->payment_instructions);

            if ($model->isDirty('starts_at') && ! $model->days_of_week) {
                $model->load(['tickets', 'sales']);

                $model->tickets->each(function ($ticket) use ($model) {
                    if ($ticket->sold) {
                        $sold = json_decode($ticket->sold, true);
                        if ($oldDate = array_key_first($sold)) {
                            $quantity = $sold[$oldDate];
                            $newDate = Carbon::parse($model->starts_at)->format('Y-m-d');
                            $sold = [$newDate => $quantity];
                            $ticket->sold = json_encode($sold);
                            $ticket->save();
                        }
                    }
                });

                $model->sales->each(function ($sale) use ($model) {
                    $sale->event_date = Carbon::parse($model->starts_at)->format('Y-m-d');
                    $sale->save();
                });
            }

            if ($model->isDirty('name') && $model->exists) {
                $model->name_en = null;
                $model->translation_attempts = 0;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->name_translated = null;
                    $eventRole->translation_attempts = 0;
                    $eventRole->save();
                }
            }

            if ($model->isDirty('description') && $model->exists) {
                $model->description_en = null;
                $model->description_html_en = null;
                $model->translation_attempts = 0;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->description_translated = null;
                    $eventRole->description_html_translated = null;
                    $eventRole->translation_attempts = 0;
                    $eventRole->save();
                }
            }

            if ($model->isDirty('short_description') && $model->exists) {
                $model->short_description_en = null;
                $model->translation_attempts = 0;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->short_description_translated = null;
                    $eventRole->translation_attempts = 0;
                    $eventRole->save();
                }
            }
        });

        static::deleting(function ($event) {
            // Eager load roles with events count and user relationship
            $event->load(['roles' => function ($query) {
                $query->withCount('events')->with('user');
            }]);

            foreach ($event->roles as $role) {
                if (($role->isTalent() || $role->isVenue()) && ! $role->isRegistered()) {
                    if ($role->events_count == 1) {
                        $role->delete();
                    }
                }
            }

            if ($event->registration_url) {
                DB::table('parsed_event_urls')
                    ->where('url', $event->registration_url)
                    ->delete();
            }

            // Sync deletion to Google Calendar and CalDAV for all roles that have sync enabled
            foreach ($event->roles as $role) {
                if ($role->syncsToGoogle()) {
                    $user = $role->user;
                    if ($user && $user->google_token) {
                        SyncEventToGoogleCalendar::dispatchSync($event, $role, 'delete');
                    }
                }

                if ($role->syncsToCalDAV()) {
                    SyncEventToCalDAV::dispatchSync($event, $role, 'delete');
                }
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)->where('is_deleted', false)->orderBy('price', 'desc');
    }

    public function parts()
    {
        return $this->hasMany(EventPart::class)->orderBy('sort_order');
    }

    public function videos()
    {
        return $this->hasMany(EventVideo::class);
    }

    public function approvedVideos()
    {
        return $this->hasMany(EventVideo::class)->where('is_approved', true);
    }

    public function pendingVideos()
    {
        return $this->hasMany(EventVideo::class)->where('is_approved', false);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(EventComment::class)->where('is_approved', true);
    }

    public function pendingComments()
    {
        return $this->hasMany(EventComment::class)->where('is_approved', false);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function venue()
    {
        // Load venue from event_role table where the role is a venue
        return $this->belongsToMany(Role::class, 'event_role', 'event_id', 'role_id')
            ->where('roles.type', 'venue')
            ->withPivot('id', 'name_translated', 'short_description_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
            ->using(EventRole::class);
    }

    public function getVenueAttribute()
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        foreach ($this->roles as $role) {
            if ($role->isVenue()) {
                return $role;
            }
        }

        return null;
    }

    public function getGroupIdForSubdomain($subdomain)
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $role->pivot->group_id : null;
    }

    public function creatorRole()
    {
        return $this->belongsTo(Role::class, 'creator_role_id');
    }

    public function curator()
    {
        // Return the creator role if it's a curator, otherwise return null
        if ($this->creatorRole && $this->creatorRole->isCurator()) {
            return $this->creatorRole;
        }

        return null;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('id', 'name_translated', 'short_description_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
            ->using(EventRole::class);
    }

    public function curatorBySubdomain($subdomain)
    {
        return $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain && $role->isCurator();
        });
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->where('is_deleted', false);
    }

    public function members()
    {
        return $this->roles->filter(function ($role) {
            return $role->isTalent();
        });
    }

    public function role()
    {
        return $this->roles->first(function ($role) {
            return $role->isTalent();
        });
    }

    /**
     * Get a role that can be used to view this event publicly.
     * Priority: first claimed role, then creatorRole if claimed, then any role.
     */
    public function getViewableRole()
    {
        // First, try to find a claimed role
        $claimed = $this->roles->first(fn ($role) => $role->isClaimed());
        if ($claimed) {
            return $claimed;
        }

        // Fall back to creatorRole (which should be claimed if it's a curator)
        if ($this->creatorRole && $this->creatorRole->isClaimed()) {
            return $this->creatorRole;
        }

        // Last resort: return first role even if unclaimed
        return $this->roles->first();
    }

    public function isPro()
    {
        foreach ($this->roles as $role) {
            if ($role->isPro()) {
                return true;
            }
        }

        return false;
    }

    public function isAtVenue($subdomain)
    {
        return $this->venue && $this->venue->subdomain == $subdomain;
    }

    public function isRoleAMember($subdomain, $includeCurators = false)
    {
        return $this->roles->contains(function ($role) use ($subdomain, $includeCurators) {
            return $role->subdomain == $subdomain && ($role->isTalent() || ($includeCurators && $role->isCurator()));
        });
    }

    public function curators()
    {
        return $this->belongsToMany(Role::class, 'event_role', 'event_id', 'role_id');
    }

    public function hashedId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function localStartsAt($pretty = false, $date = null, $endTime = false)
    {
        if (! $this->starts_at) {
            return '';
        }

        $subdomain = request()->subdomain;
        $role = false;
        $enable24 = false;

        if ($subdomain) {
            $role = $this->roles->first(function ($role) use ($subdomain) {
                return $role->subdomain == $subdomain;
            });

            if ($role) {
                $enable24 = $role->use_24_hour_time;
            }
        }

        if ($user = auth()->user()) {
            if ($user->use_24_hour_time !== null) {
                $enable24 = $user->use_24_hour_time;
            }
        }

        $startAt = $this->getStartDateTime($date, true);

        $format = $pretty ? ($enable24 ? 'D, M jS • H:i' : 'D, M jS • g:i A') : 'Y-m-d H:i:s';

        // Set locale for date translation if pretty is true and role has language_code
        if ($pretty && $role && $role->language_code) {
            $startAt->setLocale($role->language_code);
            $localizedFormat = $enable24 ? 'l, j F • H:i' : 'l, j F • g:i A';
            $value = $startAt->translatedFormat($localizedFormat);
        } else {
            $value = $startAt->format($format);
        }

        if ($endTime && $this->duration > 0) {
            $startDate = $startAt->format('Y-m-d');
            $startAt->addHours($this->duration);
            $endDate = $startAt->format('Y-m-d');

            if ($startDate == $endDate) {
                $value .= ' '.__('messages.to').' '.$startAt->format($enable24 ? 'H:i' : 'g:i A');
            } else {
                if ($pretty && $role && $role->language_code) {
                    $localizedFormat = $enable24 ? 'l, j F • H:i' : 'l, j F • g:i A';
                    $value = $value.'<br/>'.__('messages.to').'<br/>'.$startAt->translatedFormat($localizedFormat);
                } else {
                    $value = $value.'<br/>'.__('messages.to').'<br/>'.$startAt->format($format);
                }
            }
        }

        return $value;
    }

    public function matchesDate($date)
    {
        if (! $this->starts_at) {
            return false;
        }

        if ($this->days_of_week) {
            $startDate = Carbon::parse($this->localStartsAt())->startOfDay();
            $afterStartDate = $startDate->isSameDay($date) || $startDate->lessThanOrEqualTo($date);

            if (! $afterStartDate) {
                return false;
            }

            // Check if date matches the frequency pattern
            $frequency = $this->recurring_frequency ?? 'weekly';

            if (! $this->matchesFrequency($frequency, $date, $startDate)) {
                return false;
            }

            // Check recurring end conditions
            $recurringEndType = $this->recurring_end_type ?? 'never';

            if ($recurringEndType === 'on_date' && $this->recurring_end_value) {
                $endDate = Carbon::createFromFormat('Y-m-d', $this->recurring_end_value)->startOfDay();
                $checkDate = Carbon::parse($date)->startOfDay();
                if ($checkDate->greaterThan($endDate)) {
                    return false;
                }
            } elseif ($recurringEndType === 'after_events' && $this->recurring_end_value) {
                $maxOccurrences = (int) $this->recurring_end_value;
                $checkDate = Carbon::parse($date)->startOfDay();

                $occurrenceCount = $this->countOccurrences($frequency, $startDate, $checkDate);

                if ($occurrenceCount > $maxOccurrences) {
                    return false;
                }
            }

            return true;
        } else {
            return Carbon::parse($this->localStartsAt())->isSameDay($date);
        }
    }

    protected function matchesFrequency(string $frequency, Carbon $date, Carbon $startDate): bool
    {
        switch ($frequency) {
            case 'daily':
                return true;

            case 'weekly':
                return $this->days_of_week[$date->dayOfWeek] === '1';

            case 'every_n_weeks':
                if ($this->days_of_week[$date->dayOfWeek] !== '1') {
                    return false;
                }
                $interval = $this->recurring_interval ?? 2;
                $daysDiff = $startDate->copy()->startOfWeek(Carbon::SUNDAY)->diffInDays($date->copy()->startOfWeek(Carbon::SUNDAY));
                $weeksDiff = (int) floor($daysDiff / 7);

                return $weeksDiff % $interval === 0;

            case 'monthly_date':
                return $date->day === $startDate->day;

            case 'monthly_weekday':
                // Match nth weekday (e.g., 2nd Tuesday)
                $nthWeekday = (int) ceil($startDate->day / 7);
                $targetDayOfWeek = $startDate->dayOfWeek;
                $dateNthWeekday = (int) ceil($date->day / 7);

                return $date->dayOfWeek === $targetDayOfWeek && $dateNthWeekday === $nthWeekday;

            case 'yearly':
                return $date->month === $startDate->month && $date->day === $startDate->day;

            default:
                return $this->days_of_week[$date->dayOfWeek] === '1';
        }
    }

    protected function countOccurrences(string $frequency, Carbon $startDate, Carbon $checkDate): int
    {
        switch ($frequency) {
            case 'daily':
                return $startDate->diffInDays($checkDate) + 1;

            case 'monthly_date':
                $count = 0;
                $current = $startDate->copy();
                while ($current->lte($checkDate)) {
                    $count++;
                    $current->addMonth();
                }

                return $count;

            case 'monthly_weekday':
                $count = 0;
                $nthWeekday = (int) ceil($startDate->day / 7);
                $targetDayOfWeek = $startDate->dayOfWeek;
                $current = $startDate->copy()->startOfMonth();
                while ($current->lte($checkDate)) {
                    // Find the nth target weekday in this month
                    $targetMonth = $current->month;
                    $found = 0;
                    $candidate = $current->copy();
                    while ($candidate->month === $targetMonth) {
                        if ($candidate->dayOfWeek === $targetDayOfWeek) {
                            $found++;
                            if ($found === $nthWeekday) {
                                if ($candidate->gte($startDate) && $candidate->lte($checkDate)) {
                                    $count++;
                                }
                                break;
                            }
                        }
                        $candidate->addDay();
                    }
                    // Move to next month
                    $current->addMonth()->startOfMonth();
                }

                return $count;

            case 'yearly':
                $count = 0;
                $current = $startDate->copy();
                while ($current->lte($checkDate)) {
                    $count++;
                    $current->addYear();
                }

                return $count;

            case 'every_n_weeks':
                $interval = $this->recurring_interval ?? 2;
                $count = 0;
                $currentDate = $startDate->copy();
                while ($currentDate->lte($checkDate)) {
                    $daysDiff = $startDate->copy()->startOfWeek(Carbon::SUNDAY)->diffInDays($currentDate->copy()->startOfWeek(Carbon::SUNDAY));
                    $weeksDiff = (int) floor($daysDiff / 7);
                    if ($weeksDiff % $interval === 0 && $this->days_of_week[$currentDate->dayOfWeek] === '1') {
                        $count++;
                    }
                    $currentDate->addDay();
                }

                return $count;

            case 'weekly':
            default:
                $count = 0;
                $currentDate = $startDate->copy();
                while ($currentDate->lte($checkDate)) {
                    if ($this->days_of_week[$currentDate->dayOfWeek] === '1') {
                        $count++;
                    }
                    $currentDate->addDay();
                }

                return $count;
        }
    }

    public function canSellTickets($date = null)
    {
        // For recurring events, check if the specific occurrence is in the past
        if ($this->days_of_week && $date) {
            $startDateTime = $this->getStartDateTime($date, true);
            if ($startDateTime->isPast()) {
                return false;
            }
        }

        // For non-recurring events, check if the event start time is in the past
        if (! $this->days_of_week && $this->starts_at) {
            if (Carbon::parse($this->starts_at)->isPast()) {
                return false;
            }
        }

        return $this->tickets_enabled && $this->isPro();
    }

    public function areTicketsFree()
    {
        return $this->tickets->every(function ($ticket) {
            return $ticket->price == 0;
        });
    }

    public function getImageUrl()
    {
        if ($this->flyer_image_url) {
            return $this->flyer_image_url;
        } elseif ($this->role() && $this->role()->profile_image_url) {
            return $this->role()->profile_image_url;
        } elseif ($this->venue && $this->venue->profile_image_url) {
            return $this->venue->profile_image_url;
        }

        return null;
    }

    public function getLanguageCode()
    {
        if ($this->venue && $this->venue->language_code) {
            return $this->venue->language_code;
        }

        $lang = 'en';

        foreach ($this->roles as $role) {
            if ($role->isTalent() && $role->language_code) {
                $lang = $role->language_code;
                break;
            }
        }

        return $lang;
    }

    public function getVenueDisplayName($translate = true)
    {
        if ($this->venue) {
            return $this->venue->shortVenue($translate);
        }

        return $this->getEventUrlDomain();
    }

    public function getEventUrlDomain()
    {
        if ($this->event_url) {
            $parsedUrl = parse_url($this->event_url);

            if (isset($parsedUrl['host'])) {
                return $parsedUrl['host'];
            } else {
                return $this->event_url;
            }
        }

        return '';
    }

    public function getGuestUrl($subdomain = false, $date = null, $useCustomDomain = false)
    {
        $data = $this->getGuestUrlData($subdomain, $date);

        if (! $data['subdomain']) {
            \Log::error('No subdomain found for event '.$this->id);

            return '';
        }

        // Select the correct route name based on available data
        $routeName = 'event.view_guest';
        if (isset($data['date'])) {
            $routeName = 'event.view_guest_full';
        } elseif (isset($data['id'])) {
            $routeName = 'event.view_guest_with_id';
        }

        // Check if the role has a custom domain
        $role = $this->roles->first(function ($role) use ($data) {
            return $role->subdomain == $data['subdomain'];
        });

        if ($role && $role->custom_domain && $useCustomDomain) {
            $url = route($routeName, $data, false);
            $url = $role->custom_domain.$url;

            return $url;
        } else {
            return route($routeName, $data);
        }
    }

    public function getGuestUrlData($subdomain = false, $date = null)
    {
        $venueSubdomain = $this->venue && $this->venue->isClaimed() ? $this->venue->subdomain : null;
        $roleSubdomain = $this->role() && $this->role()->isClaimed() ? $this->role()->subdomain : null;

        if (! $subdomain) {
            $subdomain = $roleSubdomain ? $roleSubdomain : $venueSubdomain;
        }

        if (! $subdomain) {
            $subdomain = $this->creatorRole ? $this->creatorRole->subdomain : null;

            // Temp fix - remove once curator_id is corrected
            // Check if the given subdomain matches any of the roles
            if ($subdomain) {
                $matchingRole = $this->roles->first(function ($role) use ($subdomain) {
                    return $role->subdomain == $subdomain;
                });

                // If no matching role, try to find the first claimed role
                if (! $matchingRole) {
                    $claimedRole = $this->roles->first(function ($role) {
                        return $role->isClaimed();
                    });

                    if ($claimedRole) {
                        $subdomain = $claimedRole->subdomain;
                    }
                }
            }
        }

        $slug = $this->slug;

        if ($venueSubdomain && $roleSubdomain) {
            $slug = $venueSubdomain == $subdomain ? $roleSubdomain : $venueSubdomain;
        }

        // TODO supoprt custom_slug

        if ($date === null && $this->starts_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC')->format('Y-m-d');
        }

        $data = [
            'subdomain' => $subdomain,
            'slug' => $slug,
            'id' => UrlUtils::encodeId($this->id),  // Always include ID
        ];

        // Only include date for recurring events
        if ($date && $this->days_of_week) {
            $data['date'] = $date;
        }

        return $data;
    }

    public function getTitle()
    {
        $title = __('messages.event_title');

        return str_replace([':role', ':venue'], [$this->name, $this->venue ? $this->venue->getDisplayName() : $this->getEventUrlDomain()], $title);
    }

    public function getMetaDescription($date = null)
    {
        if ($this->short_description) {
            return \Illuminate\Support\Str::limit($this->translatedShortDescription(), 155);
        }

        $str = $this->translatedName();

        if ($this->venue) {
            $str .= ' at '.$this->venue->getDisplayName();
        } elseif ($this->getEventUrlDomain()) {
            $str .= ' at '.$this->getEventUrlDomain();
        }

        $str .= ' | '.$this->localStartsAt(true, $date);

        return \Illuminate\Support\Str::limit($str, 155);
    }

    public function getGoogleCalendarUrl($date = null)
    {
        $title = $this->getTitle();
        $description = $this->description_html ? strip_tags($this->description_html) : ($this->role() ? strip_tags($this->role()->description_html) : '');
        $location = $this->venue ? $this->venue->bestAddress() : '';
        $duration = $this->duration > 0 ? $this->duration : 2;
        $startAt = $this->getStartDateTime($date);
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->addSeconds($duration * 3600)->format('Ymd\THis\Z');

        $url = 'https://calendar.google.com/calendar/r/eventedit?';
        $url .= 'text='.urlencode($title);
        $url .= '&dates='.$startDate.'/'.$endDate;
        $url .= '&details='.urlencode($description);
        $url .= '&location='.urlencode($location);

        return $url;
    }

    public function getAppleCalendarUrl($date = null)
    {
        $title = $this->getTitle();
        $description = $this->description_html ? strip_tags($this->description_html) : ($this->role() ? strip_tags($this->role()->description_html) : '');
        $location = $this->venue ? $this->venue->bestAddress() : '';
        $duration = $this->duration > 0 ? $this->duration : 2;
        $startAt = $this->getStartDateTime($date);
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->addSeconds($duration * 3600)->format('Ymd\THis\Z');

        $url = "BEGIN:VCALENDAR\nVERSION:2.0\nBEGIN:VEVENT\n";
        $url .= 'SUMMARY:'.$title."\n";
        $url .= 'DESCRIPTION:'.$description."\n";
        $url .= 'DTSTART:'.$startDate."\n";
        $url .= 'DTEND:'.$endDate."\n";
        $url .= 'LOCATION:'.$location."\n";
        $url .= "END:VEVENT\nEND:VCALENDAR";

        return 'data:text/calendar;charset=utf8,'.urlencode($url);
    }

    public function getMicrosoftCalendarUrl($date = null)
    {
        $title = $this->getTitle();
        $description = $this->description_html ? strip_tags($this->description_html) : ($this->role() ? strip_tags($this->role()->description_html) : '');
        $location = $this->venue ? $this->venue->bestAddress() : '';
        $duration = $this->duration > 0 ? $this->duration : 2;
        $startAt = $this->getStartDateTime($date);
        $startDate = $startAt->format('Y-m-d\TH:i:s\Z');
        $endDate = $startAt->addSeconds($duration * 3600)->format('Y-m-d\TH:i:s\Z');

        $url = 'https://outlook.live.com/calendar/0/deeplink/compose?';
        $url .= 'subject='.urlencode($title);
        $url .= '&body='.urlencode($description);
        $url .= '&startdt='.$startDate;
        $url .= '&enddt='.$endDate;
        $url .= '&location='.urlencode($location);
        $url .= '&allday=false';

        return $url;
    }

    public function getStartDateTime($date = null, $locale = false)
    {
        $timezone = 'UTC';

        if ($user = auth()->user()) {
            $timezone = $user->timezone;
        } elseif ($this->creatorRole) {
            $timezone = $this->creatorRole->timezone;
        }

        if (strlen($this->starts_at) === 10) {
            // Date-only format (Y-m-d), assume midnight
            $startAt = Carbon::createFromFormat('Y-m-d', $this->starts_at, 'UTC')->startOfDay();
        } else {
            $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC');
        }

        if ($date) {
            $customDate = Carbon::createFromFormat('Y-m-d', $date);
            $startAt->setDate($customDate->year, $customDate->month, $customDate->day);
        }

        if ($locale) {
            $startAt->setTimezone($timezone);
        }

        return $startAt;
    }

    public function use24HourTime()
    {
        if ($user = auth()->user()) {
            if ($user->use_24_hour_time !== null) {
                return (bool) $user->use_24_hour_time;
            }
        }

        return $this->creatorRole && $this->creatorRole->use_24_hour_time;
    }

    public function getTimeFormat()
    {
        return $this->use24HourTime() ? 'H:i' : 'g:i A';
    }

    public function getDateTimeFormat($includeYear = false)
    {
        $format = $this->getTimeFormat();

        if ($includeYear) {
            return 'F jS, Y '.$format;
        } else {
            return 'F jS '.$format;
        }
    }

    public function isMultiDay()
    {
        return ! $this->getStartDateTime(null, true)->isSameDay($this->getStartDateTime(null, true)->addHours($this->duration));
    }

    public function getStartEndTime($date = null, $use24 = false)
    {
        $date = $this->getStartDateTime($date, true);

        if ($this->duration > 0) {
            $endDate = $date->copy()->addHours($this->duration);

            return $date->format($use24 ? 'H:i' : 'g:i A').' - '.$endDate->format($use24 ? 'H:i' : 'g:i A');
        } else {
            return $date->format($use24 ? 'H:i' : 'g:i A');
        }
    }

    public function getFlyerImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        // Handle demo images in public/images/demo/
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (config('filesystems.default') == 'local') {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public function getOtherRole($subdomain)
    {
        if ($this->role() && $subdomain == $this->role()->subdomain) {
            return $this->venue;
        } else {
            return $this->role();
        }
    }

    public function translatedName()
    {
        $value = $this->name;

        if ($this->name_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->name_en;
        }

        $value = str_ireplace('fuck', 'F@#%', $value);

        return $value;
    }

    public function translatedDescription()
    {
        $value = $this->description_html;

        if ($this->description_html_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->description_html_en;
        }

        return $value;
    }

    public function translatedShortDescription()
    {
        $value = $this->short_description;

        if ($this->short_description_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->short_description_en;
        }

        return $value;
    }

    public function toApiData()
    {
        $data = new \stdClass;

        if (! $this->isPro()) {
            return $data;
        }

        $data->id = UrlUtils::encodeId($this->id);
        $data->url = $this->getGuestUrl();
        $data->name = $this->name;
        $data->short_description = $this->short_description;
        $data->description = $this->description;
        $data->starts_at = $this->starts_at;
        $data->duration = $this->duration;
        $data->venue_id = $this->venue ? UrlUtils::encodeId($this->venue->id) : null;

        $data->members = $this->members()->mapWithKeys(function ($member) {
            return [UrlUtils::encodeId($member->id) => [
                'name' => $member->name,
                'email' => $member->email,
                'youtube_url' => $member->getFirstVideoUrl(),
            ]];
        });

        $data->parts = $this->parts->map(function ($part) {
            return [
                'name' => $part->name,
                'description' => $part->description,
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
            ];
        })->values();

        return $data;
    }

    public function hasSameTicketQuantities()
    {
        $tickets = $this->tickets;
        if ($tickets->count() <= 1) {
            return false;
        }

        $quantities = $tickets->pluck('quantity')->filter(function ($qty) {
            return $qty > 0;
        })->unique();

        return $quantities->count() === 1;
    }

    public function getSameTicketQuantity()
    {
        if (! $this->hasSameTicketQuantities()) {
            return null;
        }

        return $this->tickets->first()->quantity;
    }

    public function getTotalTicketQuantity()
    {
        // For combined mode, the total should be the same as the individual quantity
        if ($this->total_tickets_mode === 'combined' && $this->hasSameTicketQuantities()) {
            return $this->getSameTicketQuantity();
        }

        return $this->tickets->sum('quantity');
    }

    /**
     * Get Google event ID for a specific role
     */
    public function getGoogleEventIdForRole($roleId)
    {
        $eventRole = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        return $eventRole ? $eventRole->pivot->google_event_id : null;
    }

    /**
     * Set Google event ID for a specific role
     *
     * @return bool True if the pivot was updated, false if not found
     */
    public function setGoogleEventIdForRole($roleId, $googleEventId)
    {
        // Check if the pivot exists before updating
        $exists = $this->roles()->where('roles.id', $roleId)->exists();
        if (! $exists) {
            \Log::warning('Cannot set Google event ID: pivot record does not exist', [
                'event_id' => $this->id,
                'role_id' => $roleId,
                'google_event_id' => $googleEventId,
            ]);

            return false;
        }

        $this->roles()->updateExistingPivot($roleId, ['google_event_id' => $googleEventId]);

        return true;
    }

    /**
     * Get Google event ID for the role defined by subdomain
     */
    public function getGoogleEventIdForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $this->getGoogleEventIdForRole($role->id) : null;
    }

    /**
     * Set Google event ID for the role defined by subdomain
     */
    public function setGoogleEventIdForSubdomain($subdomain, $googleEventId)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        if ($role) {
            $this->setGoogleEventIdForRole($role->id, $googleEventId);
        }
    }

    /**
     * Sync this event to Google Calendar for all connected users
     */
    public function syncToGoogleCalendar($action = 'create')
    {
        foreach ($this->roles as $role) {
            if ($role->syncsToGoogle()) {
                $user = $role->user;
                if ($user && $user->google_token) {
                    SyncEventToGoogleCalendar::dispatchSync($this, $role, $action);
                }
            }
        }
    }

    /**
     * Check if this event is synced to Google Calendar for a specific role
     */
    public function isSyncedToGoogleCalendarForRole($roleId)
    {
        return ! is_null($this->getGoogleEventIdForRole($roleId));
    }

    /**
     * Check if this event is synced to Google Calendar for the role defined by subdomain
     */
    public function isSyncedToGoogleCalendarForSubdomain($subdomain)
    {
        return ! is_null($this->getGoogleEventIdForSubdomain($subdomain));
    }

    /**
     * Check if this event is synced to Google Calendar for the role defined by subdomain
     */
    public function canBeSyncedToGoogleCalendarForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role && $role->hasGoogleCalendarIntegration() && $role->syncsToGoogle();
    }

    /**
     * Get Google Calendar sync status for a specific user and role
     */
    public function getGoogleCalendarSyncStatus(User $user, $roleId = null)
    {
        if (! $user->google_token) {
            return 'not_connected';
        }

        if ($roleId && $this->isSyncedToGoogleCalendarForRole($roleId)) {
            return 'synced';
        }

        return 'not_synced';
    }

    /**
     * Get end date/time for the event
     */
    public function getEndDateTime($date = null, $locale = false)
    {
        $startAt = $this->getStartDateTime($date, $locale);
        $duration = $this->duration > 0 ? $this->duration : 2; // Default to 2 hours if no duration

        return $startAt->copy()->addHours($duration);
    }

    /**
     * Get location schema data for JSON-LD
     */
    public function getSchemaLocation()
    {
        // Always return a location object (required by Google)
        // Use venue if available, otherwise fallback to organizer or event name
        if ($this->venue) {
            $venueName = $this->venue->translatedName();
            if (empty($venueName)) {
                $venueName = $this->translatedName(); // Fallback to event name
            }

            $location = [
                '@type' => 'Place',
                'name' => $venueName,
            ];

            // Add address if available
            $address = [];
            if ($this->venue->translatedAddress1()) {
                $address['streetAddress'] = $this->venue->translatedAddress1();
                if ($this->venue->translatedAddress2()) {
                    $address['streetAddress'] .= ', '.$this->venue->translatedAddress2();
                }
            }
            if ($this->venue->translatedCity()) {
                $address['addressLocality'] = $this->venue->translatedCity();
            }
            if ($this->venue->translatedState()) {
                $address['addressRegion'] = $this->venue->translatedState();
            }
            if ($this->venue->postal_code) {
                $address['postalCode'] = $this->venue->postal_code;
            }
            if ($this->venue->country_code) {
                $address['addressCountry'] = $this->venue->country_code;
            }

            // Always include address field (required by Google)
            // If we have address data, use it; otherwise provide minimal address
            if (! empty($address)) {
                $address['@type'] = 'PostalAddress';
                $location['address'] = $address;
            } else {
                // Provide minimal address object to satisfy Google's requirement
                $location['address'] = [
                    '@type' => 'PostalAddress',
                ];
            }

            // Add geo coordinates if available
            if ($this->venue->geo_lat && $this->venue->geo_lon) {
                $location['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float) $this->venue->geo_lat,
                    'longitude' => (float) $this->venue->geo_lon,
                ];
            }

            return $location;
        }

        // Fallback: use organizer name if available
        $organizer = $this->getSchemaOrganizer();
        $locationName = $organizer['name'] ?? $this->translatedName();

        $location = [
            '@type' => 'Place',
            'name' => $locationName,
        ];

        // Try to get address from organizer role if available
        $address = [];
        $organizerRole = null;

        // Check if organizer is a role with address information
        if ($this->role() && $this->role()->isClaimed()) {
            $organizerRole = $this->role();
        } elseif ($this->creatorRole) {
            $organizerRole = $this->creatorRole;
        }

        if ($organizerRole) {
            if ($organizerRole->translatedAddress1()) {
                $address['streetAddress'] = $organizerRole->translatedAddress1();
                if ($organizerRole->translatedAddress2()) {
                    $address['streetAddress'] .= ', '.$organizerRole->translatedAddress2();
                }
            }
            if ($organizerRole->translatedCity()) {
                $address['addressLocality'] = $organizerRole->translatedCity();
            }
            if ($organizerRole->translatedState()) {
                $address['addressRegion'] = $organizerRole->translatedState();
            }
            if ($organizerRole->postal_code) {
                $address['postalCode'] = $organizerRole->postal_code;
            }
            if ($organizerRole->country_code) {
                $address['addressCountry'] = $organizerRole->country_code;
            }
        }

        // Always include address field (required by Google)
        // If we have address data, use it; otherwise provide minimal address
        if (! empty($address)) {
            $address['@type'] = 'PostalAddress';
            $location['address'] = $address;
        } else {
            // Provide minimal address object to satisfy Google's requirement
            $location['address'] = [
                '@type' => 'PostalAddress',
            ];
        }

        return $location;
    }

    /**
     * Get offers schema data for JSON-LD (tickets)
     * Always returns at least a default free offer if no tickets are available
     */
    public function getSchemaOffers()
    {
        $url = $this->getGuestUrl();
        $validFrom = $this->getSchemaStartDate(); // Use event start date as validFrom

        if ($this->tickets_enabled && $this->isPro() && ! $this->tickets->isEmpty()) {
            $offers = [];
            $currency = $this->ticket_currency_code ?: 'USD';

            foreach ($this->tickets as $ticket) {
                $offer = [
                    '@type' => 'Offer',
                    'price' => (float) $ticket->price,
                    'priceCurrency' => $currency,
                    'url' => $url.(strpos($url, '?') !== false ? '&' : '?').'tickets=true',
                    'availability' => 'https://schema.org/InStock',
                    'validFrom' => $validFrom,
                ];

                if ($ticket->name) {
                    $offer['name'] = $ticket->name;
                }

                if ($ticket->quantity > 0) {
                    $offer['inventoryLevel'] = $ticket->quantity;
                }

                $offers[] = $offer;
            }

            return $offers;
        }

        // Return default free offer if no tickets
        return [
            [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
                'url' => $url,
                'availability' => 'https://schema.org/InStock',
                'validFrom' => $validFrom,
            ],
        ];
    }

    /**
     * Get performers schema data for JSON-LD
     */
    public function getSchemaPerformers()
    {
        $performers = [];
        $members = $this->members();

        foreach ($members as $member) {
            $performer = [
                '@type' => 'Person',
                'name' => $member->translatedName(),
            ];

            if ($member->getGuestUrl()) {
                $performer['url'] = $member->getGuestUrl();
            }

            $performers[] = $performer;
        }

        return ! empty($performers) ? $performers : null;
    }

    /**
     * Get event status for JSON-LD
     */
    public function getSchemaEventStatus()
    {
        if (! $this->starts_at) {
            return 'https://schema.org/EventScheduled';
        }

        // For most events, EventScheduled is the appropriate status
        // Only use EventPostponed or EventCancelled if explicitly set
        // Since we don't have explicit status tracking, default to EventScheduled
        return 'https://schema.org/EventScheduled';
    }

    /**
     * Get organizer schema data for JSON-LD
     * Always returns an organizer (with fallback if needed)
     * Ensures both "name" and "url" fields are always present
     */
    public function getSchemaOrganizer()
    {
        $eventUrl = $this->getGuestUrl();
        $eventName = $this->translatedName() ?: 'Event Organizer';

        if ($this->venue && $this->venue->isClaimed()) {
            $name = $this->venue->translatedName();
            $url = $this->venue->getGuestUrl();

            return [
                '@type' => 'Organization',
                'name' => $name ?: $eventName,
                'url' => $url ?: $eventUrl,
            ];
        } elseif ($this->role() && $this->role()->isClaimed()) {
            $name = $this->role()->translatedName();
            $url = $this->role()->getGuestUrl();

            return [
                '@type' => 'Person',
                'name' => $name ?: $eventName,
                'url' => $url ?: $eventUrl,
            ];
        } elseif ($this->creatorRole) {
            // Fallback to creator role
            $name = $this->creatorRole->translatedName();
            $url = $this->creatorRole->getGuestUrl();

            return [
                '@type' => $this->creatorRole->isVenue() ? 'Organization' : 'Person',
                'name' => $name ?: $eventName,
                'url' => $url ?: $eventUrl,
            ];
        }

        // Final fallback - use event name as organizer
        return [
            '@type' => 'Organization',
            'name' => $eventName,
            'url' => $eventUrl,
        ];
    }

    /**
     * Get event attendance mode for JSON-LD
     */
    public function getSchemaAttendanceMode()
    {
        if ($this->event_url && $this->venue) {
            return 'https://schema.org/MixedEventAttendanceMode';
        } elseif ($this->event_url) {
            return 'https://schema.org/OnlineEventAttendanceMode';
        }

        return 'https://schema.org/OfflineEventAttendanceMode';
    }

    /**
     * Get description for JSON-LD
     * Always returns a description (with fallback if needed)
     */
    public function getSchemaDescription()
    {
        $description = $this->translatedDescription();
        $description = trim(strip_tags($description));

        if (empty($description)) {
            // Fallback description
            return $this->translatedName().' - '.__('messages.event');
        }

        return $description;
    }

    /**
     * Get ISO 8601 formatted date string for schema
     */
    public function getSchemaStartDate($date = null)
    {
        $startAt = $this->getStartDateTime($date, true);

        return $startAt->toIso8601String();
    }

    /**
     * Get ISO 8601 formatted end date string for schema
     */
    public function getSchemaEndDate($date = null)
    {
        $endAt = $this->getEndDateTime($date, true);

        return $endAt->toIso8601String();
    }

    /**
     * Get CalDAV event UID for a specific role
     */
    public function getCalDAVEventUidForRole($roleId)
    {
        $eventRole = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        return $eventRole ? $eventRole->pivot->caldav_event_uid : null;
    }

    /**
     * Set CalDAV event UID for a specific role
     *
     * @return bool True if the pivot was updated, false if not found
     */
    public function setCalDAVEventUidForRole($roleId, $uid, $etag = null)
    {
        $pivotData = ['caldav_event_uid' => $uid];
        if ($etag !== null) {
            $pivotData['caldav_event_etag'] = $etag;
        }

        // Check if the pivot exists before updating
        $exists = $this->roles()->where('roles.id', $roleId)->exists();
        if (! $exists) {
            \Log::warning('Cannot set CalDAV UID: pivot record does not exist', [
                'event_id' => $this->id,
                'role_id' => $roleId,
                'uid' => $uid,
            ]);

            return false;
        }

        $this->roles()->updateExistingPivot($roleId, $pivotData);

        return true;
    }

    /**
     * Get CalDAV event UID for the role defined by subdomain
     */
    public function getCalDAVEventUidForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $this->getCalDAVEventUidForRole($role->id) : null;
    }

    /**
     * Set CalDAV event UID for the role defined by subdomain
     */
    public function setCalDAVEventUidForSubdomain($subdomain, $uid, $etag = null)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        if ($role) {
            $this->setCalDAVEventUidForRole($role->id, $uid, $etag);
        }
    }

    /**
     * Sync this event to CalDAV for all connected roles
     */
    public function syncToCalDAV($action = 'create')
    {
        foreach ($this->roles as $role) {
            if ($role->syncsToCalDAV()) {
                SyncEventToCalDAV::dispatchSync($this, $role, $action);
            }
        }
    }

    /**
     * Check if this event is synced to CalDAV for a specific role
     */
    public function isSyncedToCalDAVForRole($roleId)
    {
        return ! is_null($this->getCalDAVEventUidForRole($roleId));
    }

    /**
     * Check if this event is synced to CalDAV for the role defined by subdomain
     */
    public function isSyncedToCalDAVForSubdomain($subdomain)
    {
        return ! is_null($this->getCalDAVEventUidForSubdomain($subdomain));
    }

    /**
     * Check if this event can be synced to CalDAV for the role defined by subdomain
     */
    public function canBeSyncedToCalDAVForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role && $role->hasCalDAVSettings() && $role->syncsToCalDAV();
    }

    /**
     * Get custom field values
     */
    public function getCustomFieldValues(): array
    {
        return $this->custom_field_values ?? [];
    }

    /**
     * Get a specific custom field value by key
     */
    public function getCustomFieldValue(string $key): ?string
    {
        $values = $this->getCustomFieldValues();

        return $values[$key] ?? null;
    }

    /**
     * Set a specific custom field value
     */
    public function setCustomFieldValue(string $key, ?string $value): void
    {
        $values = $this->getCustomFieldValues();
        $values[$key] = $value;
        $this->custom_field_values = $values;
    }
}
