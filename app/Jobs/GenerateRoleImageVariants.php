<?php

namespace App\Jobs;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

/**
 * Build the resized WebP derivatives of a schedule's profile photo. The work itself, and its
 * failure handling, is in GenerateImageVariants.
 *
 * The photo matters beyond the schedule's own page because it is the card image of every event
 * that has no flyer (Event::getImageUrl()), and the homepage wall admits those events - so an
 * un-resized 2MB profile PNG used to be served into a 96px slot once per event wearing it.
 *
 * Dispatched from Role's created/updated hooks. Restored schedules (BackupService saves them
 * quietly) and photos that predate this job are covered by
 * `php artisan images:backfill-variants --roles`.
 */
class GenerateRoleImageVariants extends GenerateImageVariants
{
    /**
     * @param  int  $roleId  The schedule whose profile photo to resize.
     * @param  string  $profileImage  The raw stored filename at dispatch time.
     */
    public function __construct(public int $roleId, public string $profileImage) {}

    protected function findModel(): ?Model
    {
        return Role::find($this->roleId);
    }

    protected function storedName(): string
    {
        return $this->profileImage;
    }

    protected function subject(): string
    {
        return 'schedule '.$this->roleId;
    }
}
