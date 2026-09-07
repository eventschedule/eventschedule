<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The one definition of what soft-deleting a schedule means.
 *
 * `roles.is_deleted` is a hand-rolled flag (nothing in this app uses Laravel's SoftDeletes), and
 * setting it never used to release the schedule's subdomain - `roles.subdomain` is UNIQUE, and
 * every availability check (Role::scopeSubdomain, Role::generateSubdomain,
 * RoleController::update) ignores the flag. So a junk schedule held a good name forever, and the
 * only way to free one was the owner's hard delete, which cascades roughly twenty child tables
 * and destroys revenue history.
 *
 * Releasing therefore RENAMES the row to {name}-deleted-{id} and remembers the original in
 * subdomain_before_delete, so the name is immediately available while the row, its events, its
 * sales and its analytics all survive and the whole thing is reversible.
 *
 * Callers keep their own teardown (images, webhooks, analytics, boost refunds). This owns only
 * the part that has to be identical everywhere: the rename, the flag, the approve-list prune and
 * the custom-domain cache.
 */
class ScheduleDeletionService
{
    /** MySQL's duplicate-key errno. The unique index is the only real authority on a name race. */
    private const ER_DUP_ENTRY = 1062;

    /**
     * Mark a schedule deleted and release its subdomain.
     *
     * Tolerates a row that is ALREADY deleted and simply releases the name. That is not a
     * convenience: ApiScheduleController::destroy(), RoleController::unfollow(),
     * bulkUnfollow() and performMerge() have all been soft-deleting rows without renaming them,
     * so there is an existing population holding good names that a transition-only action could
     * never reach.
     *
     * Sends no mail, unlike ApiScheduleController::destroy() which notifies $role->members().
     * An admin takedown should not email the owner, and an ownerless auto-created row - the
     * likeliest squatter - has no members to email anyway.
     *
     * @return string the released subdomain
     */
    public function markDeleted(Role $role, ?int $actorUserId = null): string
    {
        $host = $role->custom_domain_host;

        $released = DB::transaction(function () use ($role, $actorUserId) {
            // Re-read under a row lock so a double-clicked button, or a Mark deleted racing a
            // Restore on the same row, serializes instead of interleaving.
            $locked = Role::whereKey($role->id)->lockForUpdate()->firstOrFail();

            $original = $locked->subdomain;
            $wasDeleted = (bool) $locked->is_deleted;
            $released = $locked->releasedSubdomain();

            // subdomain and is_deleted move in ONE save. `subdomain` is a FEDERATION_FIELD, and
            // Role::boot()'s `updated` hook re-queues the schedule's events for a federation push
            // when one changes; a single save means the hook sees is_deleted already true and
            // skips them.
            $locked->subdomain = $released;
            $locked->is_deleted = true;
            $locked->subdomain_before_delete = $original;

            // Clearing the custom domain is deliberately NOT done here. Unlike this rename,
            // DigitalOceanService::removeDomain() cannot be undone by restore(), and a failed
            // removal strands the hostname in the app spec with nothing left in the database to
            // retry from. ResolveCustomDomain filters is_deleted, so the domain stops resolving
            // either way; /admin/domains Remove is the tool for actually giving it up.
            $this->save($locked);

            // The name is changing hands, so nobody's auto-accept list may keep pointing at it.
            // See Role::rewriteApprovedSubdomainReferences.
            Role::rewriteApprovedSubdomainReferences($original, null);

            AuditService::log(
                AuditService::ADMIN_SCHEDULE_DELETE,
                $actorUserId,
                'App\\Models\\Role',
                $locked->id,
                ['subdomain' => $original, 'is_deleted' => $wasDeleted],
                ['subdomain' => $released, 'is_deleted' => true],
                $wasDeleted
                    ? "Released subdomain {$original} from deleted schedule"
                    : "Marked {$original} deleted and released its subdomain",
            );

            $role->setRawAttributes($locked->getAttributes(), true);

            return $released;
        });

        // AFTER the row is written, never before: forgetting first leaves a concurrent request
        // free to re-populate the key with the pre-delete row for another ten minutes.
        if ($host) {
            Cache::forget("custom_domain:{$host}");
        }

        return $released;
    }

    /**
     * Undo a mark-deleted, reclaiming the original subdomain when nothing else has taken it.
     *
     * When it HAS been taken - which is the entire point of releasing it - the schedule stays at
     * its released name and subdomain_before_delete is left in place, so the admin can still see
     * what it used to be called.
     *
     * Deliberately narrow: it does not re-verify email, re-provision a custom domain, or undo the
     * image and webhook teardown an API delete performed before the row got here.
     *
     * No Cache::forget for the custom domain here, unlike markDeleted(). It would be dead code:
     * markDeleted() already cleared the key, and Cache::remember() cannot cache the null the
     * middleware's query returns for a deleted row - a stored null reads back as a miss - so a
     * deleted schedule's domain is re-queried on every request and resolves again the moment the
     * flag clears.
     *
     * @return array{reclaimed: bool, subdomain: string, original: ?string}
     */
    public function restore(Role $role, ?int $actorUserId = null): array
    {
        return DB::transaction(function () use ($role, $actorUserId) {
            $locked = Role::whereKey($role->id)->lockForUpdate()->firstOrFail();

            $original = $locked->subdomain_before_delete;
            $before = $locked->subdomain;

            $reclaimed = $original !== null
                && ! Role::where('subdomain', $original)->where('id', '!=', $locked->id)->exists();

            if ($reclaimed) {
                $locked->subdomain = $original;
                $locked->subdomain_before_delete = null;
            }

            $locked->is_deleted = false;
            $this->save($locked);

            if ($reclaimed) {
                // Reversing markDeleted()'s prune would be wrong: consent to auto-accept was
                // withdrawn when the entry was removed, and silently restoring it would republish
                // to a curator who never asked for it back.
                AuditService::log(
                    AuditService::ADMIN_SCHEDULE_RESTORE,
                    $actorUserId,
                    'App\\Models\\Role',
                    $locked->id,
                    ['subdomain' => $before, 'is_deleted' => true],
                    ['subdomain' => $original, 'is_deleted' => false],
                    "Restored {$original} and reclaimed its subdomain",
                );
            } else {
                AuditService::log(
                    AuditService::ADMIN_SCHEDULE_RESTORE,
                    $actorUserId,
                    'App\\Models\\Role',
                    $locked->id,
                    ['is_deleted' => true],
                    ['is_deleted' => false],
                    $original
                        ? "Restored {$locked->subdomain}; original {$original} is taken"
                        : "Restored {$locked->subdomain}",
                );
            }

            $role->setRawAttributes($locked->getAttributes(), true);

            return [
                'reclaimed' => $reclaimed,
                'subdomain' => $locked->subdomain,
                'original' => $reclaimed ? null : $original,
            ];
        });
    }

    /**
     * Save, turning a lost race for a name into something the caller can show a user.
     *
     * The check-then-write above cannot be made airtight without gap locks, which are a deadlock
     * source and far out of proportion for an action performed a handful of times a month. The
     * unique index is the real guarantee; this is what stops it surfacing as a 500.
     */
    private function save(Role $role): void
    {
        try {
            $role->save();
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === self::ER_DUP_ENTRY) {
                report($e);

                throw new SubdomainUnavailableException;
            }

            throw $e;
        }
    }
}
