<?php

namespace App\Services;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\Role;
use App\Models\User;
use App\Utils\ImageUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * What deleting an account does to everything AROUND the user row, before ProfileController::
 * destroy() deletes it and the database cascades take the rest (roles.user_id, events.user_id,
 * events.creator_role_id, event_photos.user_id, ...).
 *
 * Erasure (GDPR Art. 17) has two halves, and the cascades got both wrong:
 *
 *  - It must reach the person's data wherever it is, including what no foreign key leads to:
 *    files on the disk (a cascade never runs a model's deleting hook, so every flyer, sponsor
 *    logo, image variant and fan photo stayed readable at its public URL), rows keyed by email
 *    (interest lists, waitlists, platform newsletter recipients), sessions, and export archives.
 *
 *  - It must NOT destroy other people's records. A teammate's account owns, through
 *    events.user_id, every event they ever created on the schedule they help run, and the
 *    cascade took those events off someone else's schedule together with their sales. Those are
 *    handed to the schedule's owner instead, and so are newsletters and templates written for
 *    someone else's schedule. A buyer's purchases are the organiser's records and stay with the
 *    organiser: sales.user_id is nullOnDelete (2026_10_04_000001), so they are only detached.
 *
 * Two entry points, in this order:
 *
 *  - handOver() re-keys the contributions, in one transaction, and THROWS. ProfileController
 *    runs it before anything irreversible and stops the deletion if it fails: going on would let
 *    the cascades take other people's events with the account.
 *
 *  - prepare() does everything else. Every step there is best effort and reports rather than
 *    throws: the account deletion the person asked for must not fail because a file is already
 *    gone, the backups disk is slow to answer or a busy table times out.
 */
class AccountDeletionService
{
    private const CHUNK = 500;

    /**
     * Events, newsletters and newsletter templates this user made for a schedule someone else
     * owns stay with that schedule, re-keyed to its owner. All or nothing.
     */
    public function handOver(User $user): void
    {
        DB::transaction(fn () => $this->handOverContributions($user));
    }

    public function prepare(User $user): void
    {
        $ownedRoleIds = Role::where('user_id', $user->id)->pluck('id')->all();

        // What the cascades are about to take: the events still keyed to this user after the
        // hand-over, and every event created by a schedule they own.
        $cascadingEventIds = $this->attempt(fn () => Event::where(function ($query) use ($user, $ownedRoleIds) {
            $query->where('user_id', $user->id);
            if ($ownedRoleIds) {
                $query->orWhereIn('creator_role_id', $ownedRoleIds);
            }
        })->pluck('id')->all()) ?? [];

        $this->attempt(fn () => $this->purgeEventFiles($cascadingEventIds));
        $this->attempt(fn () => $this->purgeRoleFiles($ownedRoleIds));
        $this->attempt(fn () => $this->purgePhotos($user, $cascadingEventIds));
        $this->attempt(fn () => $this->forgetAccountRows($user));
        if ($this->addressIsProven($user)) {
            $this->attempt(fn () => $this->forgetAddress($user));
        }
        $this->attempt(fn () => $this->deleteExports($user));

        // No foreign key on sessions.user_id: every other device would keep a live row.
        $this->attempt(fn () => DB::table('sessions')->where('user_id', $user->id)->delete());

        // The calendar grant dies at Google's end too, not only with the row that held it.
        // Microsoft offers an app no way to revoke one grant, so its tokens just go with the row.
        if ($user->google_token || $user->google_refresh_token) {
            $this->attempt(fn () => app(GoogleCalendarService::class)->revoke($user));
        }
    }

    /**
     * Whether this account has shown it owns its address, so its deletion may erase what other
     * people keyed to that address. Hosted sign-up verifies an emailed code; Google and Facebook
     * sign-in vouch for the address. A selfhost sign-up marks the address verified with no proof
     * at all (RegisteredUserController), so there, registering someone else's address and then
     * deleting the account would erase THEIR interest lists, waitlists, sign-ups and chats.
     */
    private function addressIsProven(User $user): bool
    {
        return (bool) config('app.hosted') || $user->google_oauth_id || $user->facebook_id;
    }

    /** Runs one step; a failure is reported and the deletion goes on. */
    private function attempt(\Closure $step): mixed
    {
        try {
            return $step();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Ids by a plain (non-locking) read, then deleted or updated by primary key a chunk at a
     * time. A ranged DELETE over an unindexed column, such as newsletter_recipients.email, would
     * lock every row it scanned for as long as it ran, stalling sends and posts.
     *
     * @param  array<string, mixed>|null  $update  null deletes
     */
    private function byIds(\Illuminate\Database\Query\Builder $query, ?array $update = null): void
    {
        $table = $query->from;

        foreach ($query->pluck($table.'.id')->chunk(self::CHUNK) as $ids) {
            $rows = DB::table($table)->whereIn('id', $ids->all());
            $update === null ? $rows->delete() : $rows->update($update);
        }
    }

    private function handOverContributions(User $user): void
    {
        DB::table('events')
            ->join('roles', 'roles.id', '=', 'events.creator_role_id')
            ->where('events.user_id', $user->id)
            ->whereNotNull('roles.user_id')
            ->where('roles.user_id', '!=', $user->id)
            ->update(['events.user_id' => DB::raw('roles.user_id')]);

        // An event from before creator_role_id existed: it belongs wherever it is listed.
        Event::where('user_id', $user->id)->whereNull('creator_role_id')->get(['id'])
            ->each(function (Event $event) use ($user) {
                $ownerId = DB::table('event_role')
                    ->join('roles', 'roles.id', '=', 'event_role.role_id')
                    ->where('event_role.event_id', $event->id)
                    ->whereNotNull('roles.user_id')
                    ->where('roles.user_id', '!=', $user->id)
                    ->orderByDesc('event_role.is_accepted')
                    ->orderBy('event_role.role_id')
                    ->value('roles.user_id');

                if ($ownerId) {
                    DB::table('events')->where('id', $event->id)->update(['user_id' => $ownerId]);
                }
            });

        foreach (['newsletters', 'newsletter_templates'] as $table) {
            DB::table($table)
                ->join('roles', 'roles.id', '=', $table.'.role_id')
                ->where($table.'.user_id', $user->id)
                ->whereNotNull('roles.user_id')
                ->where('roles.user_id', '!=', $user->id)
                ->update([$table.'.user_id' => DB::raw('roles.user_id')]);
        }
    }

    /**
     * Flyers, agenda images, their WebP variants and sponsor logos of the events about to go.
     * A file another surviving event still points at is left alone: copies of an event can share
     * one upload.
     *
     * @param  array<int, int>  $eventIds
     */
    private function purgeEventFiles(array $eventIds): void
    {
        if (! $eventIds) {
            return;
        }

        Event::whereIn('id', $eventIds)
            ->get(['id', 'flyer_image_url', 'agenda_image_url', 'sponsor_logos'])
            ->each(function (Event $event) use ($eventIds) {
                $attributes = $event->getAttributes();

                foreach (['flyer_image_url', 'agenda_image_url'] as $column) {
                    $raw = $attributes[$column] ?? null;

                    if (! $this->ownUpload($raw)
                        || Event::where($column, $raw)->whereNotIn('id', $eventIds)->exists()) {
                        continue;
                    }

                    $this->deleteFile($raw);

                    if ($column === 'flyer_image_url') {
                        ImageUtils::deleteStoredVariants($raw);
                    }
                }

                foreach (json_decode((string) ($attributes['sponsor_logos'] ?? ''), true) ?: [] as $sponsor) {
                    $logo = is_array($sponsor) ? ($sponsor['logo'] ?? null) : null;

                    if ($this->ownUpload($logo)
                        && ! Event::where('sponsor_logos', 'like', '%'.$logo.'%')->whereNotIn('id', $eventIds)->exists()) {
                        $this->deleteFile($logo);
                    }
                }
            });
    }

    /**
     * The owned schedules' image variants and sponsor logos. ProfileController::destroy() deletes
     * the three original images itself; Role's `deleted` hook, which would take the variants, does
     * not run for a cascaded row.
     *
     * @param  array<int, int>  $roleIds
     */
    private function purgeRoleFiles(array $roleIds): void
    {
        if (! $roleIds) {
            return;
        }

        Role::whereIn('id', $roleIds)->get()->each(function (Role $role) {
            foreach (array_keys($role->imageVariantSlots()) as $slot) {
                $raw = $role->imageVariantSource($slot);

                if ($raw !== null) {
                    ImageUtils::deleteStoredVariants($raw);
                }
            }

            foreach (json_decode((string) ($role->getAttributes()['sponsor_logos'] ?? ''), true) ?: [] as $sponsor) {
                $logo = is_array($sponsor) ? ($sponsor['logo'] ?? null) : null;

                if ($this->ownUpload($logo)) {
                    $this->deleteFile($logo);
                }
            }
        });
    }

    /**
     * Fan photos: the ones this user posted anywhere (event_photos.user_id cascades), and every
     * photo on the events about to go. Deleted through the model so its hook removes the file.
     *
     * @param  array<int, int>  $eventIds
     */
    private function purgePhotos(User $user, array $eventIds): void
    {
        EventPhoto::where('user_id', $user->id)
            ->when($eventIds, fn ($query) => $query->orWhereIn('event_id', $eventIds))
            ->get()
            ->each(function (EventPhoto $photo) {
                try {
                    $photo->delete();
                } catch (\Throwable $e) {
                    report($e);
                }
            });
    }

    /** Rows of this account that no foreign key takes with it. */
    private function forgetAccountRows(User $user): void
    {
        $this->byIds(DB::table('newsletter_segment_users')->where('user_id', $user->id));

        // Recipient rows of the platform's own newsletters. A schedule's newsletter recipients are
        // that organiser's send record and only lose the account link.
        $this->byIds(DB::table('newsletter_recipients')
            ->whereIn('newsletter_id', DB::table('newsletters')->whereNull('role_id')->select('id'))
            ->where('user_id', $user->id));
    }

    /**
     * Rows keyed by the address rather than by the account, which no foreign key reaches. Only for
     * an address the account has proven (addressIsProven()).
     *
     * Kept on purpose: a sale (the organiser's record of a purchase), and a newsletter_unsubscribes
     * row, which is the opt-out itself - deleting it would let a schedule that imports the address
     * again mail it.
     */
    private function forgetAddress(User $user): void
    {
        $email = strtolower((string) $user->email);

        if ($email === '') {
            return;
        }

        // Sign-ups to a schedule's emails, confirmed or not: the account-less audience.
        $this->byIds(DB::table('role_subscribers')->where('email', $email));
        $this->byIds(DB::table('event_interests')->where('email', $email));
        $this->byIds(DB::table('ticket_waitlists')->where('email', $email));
        $this->byIds(DB::table('newsletter_recipients')
            ->whereIn('newsletter_id', DB::table('newsletters')->whereNull('role_id')->select('id'))
            ->where('email', $email));

        // Posted without being signed in, under this address.
        foreach (['event_comments', 'event_photos', 'event_videos'] as $table) {
            $this->byIds(DB::table($table)->where('guest_email', $email), ['guest_email' => null]);
        }

        // Support chats started from the marketing site before signing in (support_messages
        // cascade with their conversation).
        $this->byIds(DB::table('support_conversations')->where('guest_email', $email));
    }

    /** Export archives the user asked for; backup_jobs rows cascade, the files would not. */
    private function deleteExports(User $user): void
    {
        BackupJob::where('user_id', $user->id)->whereNotNull('file_path')->get(['id', 'file_path'])
            ->each(function (BackupJob $job) {
                try {
                    Storage::disk('backups')->delete($job->file_path);
                } catch (\Throwable $e) {
                    report($e);
                }
            });
    }

    private function ownUpload(mixed $raw): bool
    {
        return is_string($raw) && $raw !== ''
            && ! str_starts_with($raw, 'demo_') && ! str_starts_with($raw, 'http');
    }

    private function deleteFile(string $raw): void
    {
        try {
            Storage::delete(ImageUtils::storagePathFor($raw));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
