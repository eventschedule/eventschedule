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

    public function localStartsAt($pretty = false)
    {
        if (! $this->starts_at) {
            return '';
        }

        $timezone = 'UTC';
        $enable24 = false;

        if ($user =auth()->user()) {
            $timezone = $user->timezone;
        } else {
            $timezone = $this->venue->timezone;
            $enable24 = $this->venue->use_24_hour_time;
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC')
                    ->setTimezone($timezone)
                    ->format($pretty ? ($enable24 ? 'l, F jS • g:i' : 'l, F jS • g:i A') : 'Y-m-d H:i:s');
    }

    public function getGuestUrl()
    {
        return route('role.view_guest', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->id)]);
    }
}