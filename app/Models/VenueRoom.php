<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Utils\UrlUtils;

class VenueRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'details',
    ];

    public function venue()
    {
        return $this->belongsTo(Role::class, 'venue_id');
    }

    public function encodeId(): string
    {
        return UrlUtils::encodeId($this->id);
    }
}
