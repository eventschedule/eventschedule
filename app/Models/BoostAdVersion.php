<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One version of a partner boost's ad: what the partner approved (or is asked to approve) and
 * what a launch or a re-approval sends to Meta.
 */
class BoostAdVersion extends Model
{
    public const UPDATED_AT = null;

    /** The fields whose change needs a new approval. */
    public const CREATIVE_FIELDS = ['headline', 'primary_text', 'description', 'call_to_action', 'image_path', 'destination_url'];

    protected $fillable = [
        'boost_campaign_id',
        'version',
        'headline',
        'primary_text',
        'description',
        'call_to_action',
        'image_path',
        'destination_url',
        'budget',
        'end_date',
        'targeting',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'end_date' => 'datetime',
            'targeting' => 'array',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(BoostCampaign::class, 'boost_campaign_id');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        // The same resolution Event::getFlyerImageUrlAttribute() uses for an uploaded file.
        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$this->image_path;
        }

        if (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$this->image_path);
        }

        return $this->image_path;
    }

    /**
     * The creative fields that differ from another version, for "Changed: headline, picture".
     */
    public function changedFrom(?self $other): array
    {
        if (! $other) {
            return [];
        }

        $changed = [];
        foreach (self::CREATIVE_FIELDS as $field) {
            if ((string) $this->{$field} !== (string) $other->{$field}) {
                $changed[] = $field;
            }
        }

        if ((float) $this->budget !== (float) $other->budget) {
            $changed[] = 'budget';
        }

        return $changed;
    }
}
