<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'starts_at',
        'duration',
        'description',
        'event_url',
        'event_password',
        'venue_id',
        'name',
        'slug',
        'tickets_enabled',
        'ticket_currency_code',
        'ticket_notes',
        'payment_method',
        'payment_instructions',
        'expire_unpaid_tickets',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
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
        });

        static::deleting(function ($event) {
            if ($event->venue && ! $event->venue->email) {
                $event->venue->delete();
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
        return $this->belongsTo(Role::class, 'venue_id');
    }

    public function curator()
    {
        return $this->belongsTo(Role::class, 'curator_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }


    public function sales()
    {
        return $this->hasMany(Sale::class)->where('is_deleted', false);
    }

    public function members()
    {
        return $this->roles->filter(function($role) {
            return $role->isSchedule();
        });
    }

    public function role()
    {
        return $this->roles->first(function($role) {
            return $role->isSchedule();
        });
    }

    public function isPro()
    {
        if ($this->venue && $this->venue->isPro()) {
            return true;
        }

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
            return $role->subdomain == $subdomain && ($role->isSchedule() || ($includeCurators && $role->isCurator()));
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

    public function getVenueDisplayName()
    {
        if ($this->venue) {
            return $this->venue->getDisplayName();
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
        if (! $subdomain) {
            $subdomain = $this->role() ? $this->role()->subdomain : $this->venue->subdomain;
        }

        $data = [
            'subdomain' => $subdomain, 
            'slug' => $this->slug, 
            'date' => $date ? (is_string($date) ? $date : $date->format('Y-m-d')) : Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC')->format('Y-m-d'),
        ];

        return route('event.view_guest', $data);
    }

    public function getTitle()
    {
        $title = __('messages.event_title');

        return str_replace([':role', ':venue'], [$this->name, $this->venue ? $this->venue->getDisplayName() : $this->getEventUrlDomain()], $title);
    }

    public function getUse24HourTime()
    {
        return $this->role() ? $this->role()->use_24_hour_time : $this->venue->use_24_hour_time;
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
        } else if ($this->venue) {
            $timezone = $this->venue->timezone;
        } else if ($this->role()) {
            $timezone = $this->role()->timezone;
        } else if ($this->curator) {
            $timezone = $this->curator->timezone;
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

        if (config('filesystems.default') == 'do_spaces') {
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
}
