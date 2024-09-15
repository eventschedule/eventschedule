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
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function venue()
    {
        return $this->belongsTo(Role::class, 'venue_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hashedId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function localStartsAt($pretty = false, $date = null)
    {
        if (! $this->starts_at) {
            return '';
        }

        $timezone = 'UTC';
        $enable24 = false;

        if ($user = auth()->user()) {
            $timezone = $user->timezone;
        } else {
            $timezone = $this->venue->timezone;
            $enable24 = $this->venue->use_24_hour_time;
        }

        $startAt = $this->getStartDateTime($date);

        return $startAt->setTimezone($timezone)
                    ->format($pretty ? ($enable24 ? 'l, F jS • H:i' : 'l, F jS • g:i A') : 'Y-m-d H:i:s');
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
        } elseif ($this->role->profile_image_url) {
            return $this->role->profile_image_url;
        } elseif ($this->venue->profile_image_url) {
            return $this->venue->profile_image_url;
        }
        
        return null;
    }

    public function getGuestUrl($subdomain, $date = null)
    {        
        if ($subdomain == $this->role->subdomain) {
            $subdomain = $this->role->subdomain;
            $other_subdomain = $this->venue->subdomain;
        } else {
            $subdomain = $this->venue->subdomain;
            $other_subdomain = $this->role->subdomain;
        }

        $data = [
            'subdomain' => $subdomain, 
            'other_subdomain' => $other_subdomain, 
            'date' => $date ? $date->format('Y-m-d') : Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC')->format('Y-m-d'),
        ];

        return route('event.view_guest', $data);
    }

    public function getTitle()
    {
        $title = __('messages.event_title');

        return str_replace([':role', ':venue'], [$this->role->name, $this->venue->name], $title);
    }

    public function getMetaDescription($date = null)
    {
        return $this->venue->name . ' | ' . $this->localStartsAt(true, $date);
    }

    public function getGoogleCalendarUrl($date = null)
    {
        $title = $this->getTitle();
        $description = $this->description_html ? strip_tags($this->description_html) : strip_tags($this->role->description_html);
        $location = $this->venue->bestAddress();
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
        $description = $this->description_html ? strip_tags($this->description_html) : strip_tags($this->role->description_html);
        $location = $this->venue->bestAddress();
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
        $description = $this->description_html ? strip_tags($this->description_html) : strip_tags($this->role->description_html);
        $location = $this->venue->bestAddress();
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

    private function getStartDateTime($date = null)
    {
        $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC');
        
        if ($date) {
            $customDate = Carbon::createFromFormat('Y-m-d', $date);
            $startAt->setDate($customDate->year, $customDate->month, $customDate->day);
        }
        
        return $startAt;
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
}