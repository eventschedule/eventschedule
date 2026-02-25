<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EventPhoto extends Model
{
    protected $fillable = [
        'event_id',
        'event_part_id',
        'event_date',
        'user_id',
        'photo_url',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    protected static function booted()
    {
        static::deleting(function (EventPhoto $photo) {
            $path = $photo->getAttributes()['photo_url'];
            if ($path) {
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }
        });
    }

    public function getPhotoUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } elseif (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventPart()
    {
        return $this->belongsTo(EventPart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
