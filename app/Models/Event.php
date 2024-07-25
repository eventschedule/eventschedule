<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\MarkdownUtils;
use Carbon\Carbon;

class Event extends Model
{
    protected $fillable = [
        'visibility',
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
    
    public function venue()
    {
        return $this->belongsTo(Role::class, 'venue_id');
    }

    public function getStartTimeAttribute($value)
    {
        if (! $value) {
            return '';
        }

        $timezone = auth()->user()->timezone;

        return Carbon::createFromFormat('Y-m-d H:i', $value, 'UTC')
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i');
    }
}