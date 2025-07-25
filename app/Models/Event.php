<?php

namespace App\Models;

use App\Models\EventRole;
use Illuminate\Database\Eloquent\Model;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    protected $fillable = [
        'starts_at',
        'duration',
        'description',
        'description_en',
        'event_url',
        'event_password',
        'name',
        'name_en',
        'slug',
        'tickets_enabled',
        'ticket_currency_code',
        'ticket_notes',
        'payment_method',
        'payment_instructions',
        'expire_unpaid_tickets',
        'registration_url',
        'category_id',
        'creator_role_id',
    ];

    protected $casts = [
        'duration' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
            $model->description_html_en = MarkdownUtils::convertToHtml($model->description_en);
            $model->ticket_notes_html = MarkdownUtils::convertToHtml($model->ticket_notes);
            $model->payment_instructions_html = MarkdownUtils::convertToHtml($model->payment_instructions);

            if ($model->isDirty('starts_at') && ! $model->days_of_week) {
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

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->name_translated = null;
                    $eventRole->save();
                }
            }

            if ($model->isDirty('description') && $model->exists) {
                $model->description_en = null;
                $model->description_html_en = null;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->description_translated = null;
                    $eventRole->description_html_translated = null;
                    $eventRole->save();
                }                
            }
        });

        static::deleting(function ($event) {
            foreach ($event->roles as $role) {
                if (($role->isTalent() || $role->isVenue()) && ! $role->isRegistered()) {
                    if ($role->events->count() == 1) {
                        $role->delete();
                    }
                }
            }

            if ($event->registration_url) {
                DB::table('parsed_event_urls')
                    ->where('url', $event->registration_url)
                    ->delete();
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)->where('is_deleted', false)->orderBy('price', 'desc');
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
                    ->withPivot('id', 'name_translated', 'description_html_translated', 'is_accepted', 'group_id')
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

        $role = $this->roles->first(function($role) use ($subdomain) {
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
                    ->withPivot('id', 'name_translated', 'description_html_translated', 'is_accepted', 'group_id')
                    ->using(EventRole::class);
    }

    public function curatorBySubdomain($subdomain)
    {
        return $this->roles->first(function($role) use ($subdomain) {
            return $role->subdomain == $subdomain && $role->isCurator();
        });
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->where('is_deleted', false);
    }

    public function members()
    {
        return $this->roles->filter(function($role) {
            return $role->isTalent();
        });
    }

    public function role()
    {
        return $this->roles->first(function($role) {
            return $role->isTalent();
        });
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

        $enable24 = false;

        if ($user = auth()->user()) {
            //    
        } else if ($this->venue) {
            $enable24 = $this->venue->use_24_hour_time;
        }

        $startAt = $this->getStartDateTime($date, true);
        
        $format = $pretty ? ($enable24 ? 'l, F jS • H:i' : 'l, F jS • g:i A') : 'Y-m-d H:i:s';
        $value = $startAt->format($format);
        
        if ($endTime && $this->duration > 0) {
            $startDate = $startAt->format('Y-m-d');
            $startAt->addHours($this->duration);
            $endDate = $startAt->format('Y-m-d');
            
            if ($startDate == $endDate) {
                $value .= ' ' . __('messages.to') . ' ' . $startAt->format($enable24 ? 'H:i' : 'g:i A');
            } else {
                $value = $value . '<br/>' . __('messages.to') . '<br/>' . $startAt->format($format);
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
            $afterStartDate = Carbon::parse($this->localStartsAt())->isSameDay($date) || Carbon::parse($this->localStartsAt())->lessThanOrEqualTo($date);
            $dayOfWeek = $date->dayOfWeek;
            return $afterStartDate && $this->days_of_week[$dayOfWeek];
        } else {
            return Carbon::parse($this->localStartsAt())->isSameDay($date);
        }
    }

    public function canSellTickets()
    {
        if (! $this->days_of_week && $this->starts_at) {
            if (Carbon::parse($this->starts_at)->isPast()) {
                return false;
            }
        }

        return $this->tickets_enabled && $this->isPro();
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
            return $this->venue->getDisplayName($translate);
        }

        return $this->getEventUrlDomain();
    }

    public function getEventUrlDomain()
    {
        if ($this->event_url) {
            $parsedUrl = parse_url($this->event_url);
            return $parsedUrl['host'];
        }

        return '';
    }

    public function getGuestUrl($subdomain = false, $date = null)
    {
        $data = $this->getGuestUrlData($subdomain, $date);

        if (! $data['subdomain']) {
            \Log::error('No subdomain found for event ' . $this->id);
            return '';
        }

        return route('event.view_guest', $data);
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
        ];

        if ($date) {
            $data['date'] = $date;
        }

        return $data;
    }

    public function getTitle()
    {
        $title = __('messages.event_title');

        return str_replace([':role', ':venue'], [$this->name, $this->venue ? $this->venue->getDisplayName() : $this->getEventUrlDomain()], $title);
    }

    public function getUse24HourTime()
    {
        if ($this->role()) {
            return $this->role()->use_24_hour_time;
        } else if ($this->venue) {
            return $this->venue->use_24_hour_time;
        }

        return false;
    }

    public function getMetaDescription($date = null)
    {
        $str = '';

        if ($this->venue) {
            $str .= $this->venue->getDisplayName();
        } else {
            $str .= $this->getEventUrlDomain();
        }

        $str .=  ' | ' . $this->localStartsAt(true, $date);

        return $str;
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

        $url = "https://calendar.google.com/calendar/r/eventedit?";
        $url .= "text=" . urlencode($title);
        $url .= "&dates=" . $startDate . "/" . $endDate;
        $url .= "&details=" . urlencode($description);
        $url .= "&location=" . urlencode($location);

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
        $url .= "SUMMARY:" . $title . "\n";
        $url .= "DESCRIPTION:" . $description . "\n";
        $url .= "DTSTART:" . $startDate . "\n";
        $url .= "DTEND:" . $endDate . "\n";
        $url .= "LOCATION:" . $location . "\n";
        $url .= "END:VEVENT\nEND:VCALENDAR";

        return "data:text/calendar;charset=utf8," . urlencode($url);
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

        $url = "https://outlook.live.com/calendar/0/deeplink/compose?";
        $url .= "subject=" . urlencode($title);
        $url .= "&body=" . urlencode($description);
        $url .= "&startdt=" . $startDate;
        $url .= "&enddt=" . $endDate;
        $url .= "&location=" . urlencode($location);
        $url .= "&allday=false";

        return $url;
    }

    public function getStartDateTime($date = null, $locale = false)
    {
        $timezone = 'UTC';
        
        if ($user = auth()->user()) {
            $timezone = $user->timezone;
        } else if ($this->creatorRole) {
            $timezone = $this->creatorRole->timezone;
        }

        $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC');

        if ($locale) {
            $startAt->setTimezone($timezone);        
        }

        if ($date) {
            $customDate = Carbon::createFromFormat('Y-m-d', $date);
            $startAt->setDate($customDate->year, $customDate->month, $customDate->day);
        }
        
        return $startAt;
    }

    public function use24HourTime()
    {
        if ($this->role() && $this->role()->use_24_hour_time) {
            return true;
        } else if ($this->venue && $this->venue->use_24_hour_time) {
            return true;
        }

        return false;
    }

    public function getTimeFormat()
    {
        return $this->use24HourTime() ? 'H:i' : 'g:i A';
    }

    public function getDateTimeFormat($includeYear = false)
    {
        $format = $this->getTimeFormat();

        if ($includeYear) {
            return 'F jS, Y ' . $format;
        } else {
            return 'F jS ' . $format;
        }
    }

    public function isMultiDay()
    {
        return ! $this->getStartDateTime(null, true)->isSameDay($this->getStartDateTime(null, true)->addHours($this->duration));
    }

    public function getStartEndTime($date = null)
    {
        $date = $this->getStartDateTime($date, true);

        if ($this->duration > 0) {
            $endDate = $date->copy()->addHours($this->duration);
            return $date->format($this->use24HourTime() ? 'H:i' : 'g:i A') . ' - ' . $endDate->format($this->use24HourTime() ? 'H:i' : 'g:i A');
        } else {
            return $date->format($this->use24HourTime() ? 'H:i' : 'g:i A');
        }        
    }

    public function getFlyerImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } else if (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }

    public function getOtherRole($subdomain) {        
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

    public function toApiData()
    {
        $data = new \stdClass;

        if (! $this->isPro()) {
            return $data;
        }

        $data->id = UrlUtils::encodeId($this->id);
        $data->url = $this->getGuestUrl();
        $data->name = $this->name;
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

        return $data;
    }
}
