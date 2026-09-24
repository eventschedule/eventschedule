<?php

namespace App\Jobs;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

/**
 * Build the resized WebP derivatives of one of a schedule's uploaded images: its profile photo,
 * or the header or background it uploaded (Role::imageVariantSlots()). The work itself, and its
 * failure handling, is in GenerateImageVariants.
 *
 * The photo matters beyond the schedule's own page because it is the card image of every event
 * that has no flyer (Event::getImageUrl()), and the homepage wall admits those events - so an
 * un-resized 2MB profile PNG used to be served into a 96px slot once per event wearing it. The
 * background is the schedule page's LCP image on a phone.
 *
 * Dispatched from Role's created/updated hooks, one job per changed image. Restored schedules
 * (BackupService saves them quietly) and images that predate this job are covered by
 * `php artisan images:backfill-variants --roles --slot=all`.
 */
class GenerateRoleImageVariants extends GenerateImageVariants
{
    /**
     * Which image this job builds: 'default' (the profile photo), 'header' or 'background'.
     *
     * A plain property with a class-level default, deliberately NOT promoted in the constructor
     * below. A job already waiting in the queue was serialized before this property existed, and
     * unserialize() never runs a constructor: a promoted property's default belongs to the
     * constructor argument, so the property would come back uninitialized and throw on its first
     * read. A class-level default is applied on unserialize too, so an old job still builds the
     * profile photo it was queued for.
     */
    public string $slot = 'default';

    /**
     * Property names are part of the serialized payload of jobs already sitting in the queue, so
     * $profileImage keeps its name even though it now holds whichever image $slot names.
     *
     * @param  int  $roleId  The schedule whose image to resize.
     * @param  string  $profileImage  The raw stored filename of that image at dispatch time.
     * @param  string  $slot  Which image: see $slot.
     */
    public function __construct(public int $roleId, public string $profileImage, string $slot = 'default')
    {
        $this->slot = $slot;
    }

    protected function findModel(): ?Model
    {
        return Role::find($this->roleId);
    }

    protected function storedName(): string
    {
        return $this->profileImage;
    }

    protected function variantSlot(): string
    {
        return $this->slot;
    }

    protected function subject(): string
    {
        return 'schedule '.$this->roleId.($this->slot === 'default' ? '' : ' ('.$this->slot.')');
    }
}
