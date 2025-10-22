<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Image extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'disk',
        'directory',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'variants',
        'checksum',
        'reference_count',
    ];

    protected $casts = [
        'variants' => 'array',
    ];

    protected $attributes = [
        'reference_count' => 0,
    ];

    protected static function booted(): void
    {
        static::creating(function (Image $image): void {
            if (empty($image->uuid)) {
                $image->uuid = (string) Str::uuid();
            }

            if (empty($image->disk)) {
                $image->disk = config('filesystems.image_storage.disk', 'images');
            }
        });
    }

    public function incrementReferenceCount(int $amount = 1): void
    {
        $this->reference_count += max(1, $amount);
        $this->save();
    }

    public function decrementReferenceCount(int $amount = 1): void
    {
        $this->reference_count = max(0, $this->reference_count - max(1, $amount));
        $this->save();
    }

    public function getVariant(string $name): ?array
    {
        return $this->variants[$name] ?? null;
    }
}
