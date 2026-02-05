<?php

namespace App\Models;

use App\Utils\MarkdownUtils;
use Illuminate\Database\Eloquent\Model;

class EventPart extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'name_en',
        'description',
        'description_en',
        'description_html',
        'description_html_en',
        'start_time',
        'end_time',
        'sort_order',
        'translation_attempts',
        'last_translated_at',
    ];

    protected $casts = [
        'last_translated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('name') && $model->exists) {
                $model->name_en = null;
                $model->translation_attempts = 0;
            }

            if ($model->isDirty('description') && $model->exists) {
                $model->description_en = null;
                $model->translation_attempts = 0;
            }

            // Convert markdown to HTML
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
            $model->description_html_en = MarkdownUtils::convertToHtml($model->description_en);
        });
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

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function videos()
    {
        return $this->hasMany(EventVideo::class);
    }

    public function approvedVideos()
    {
        return $this->hasMany(EventVideo::class)->where('is_approved', true);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(EventComment::class)->where('is_approved', true);
    }
}
