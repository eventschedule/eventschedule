<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UrlUtils;

class Group extends Model
{
    protected $fillable = [
        'name', 
        'name_en', 
        'slug', 
    ];

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function encodeId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function translatedName()
    {
        $value = $this->name;

        if ($this->name_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->name_en;
        }

        return $value;
    }
}