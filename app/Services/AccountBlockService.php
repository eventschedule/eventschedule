<?php

namespace App\Services;

use App\Exceptions\BillingCancellationException;
use App\Models\AccountBlock;
use App\Models\BlocklistEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shutting an account out, and letting it back in (/admin/blocked).
 *
 * A block is three things. The account cannot be used: users.blocked_at is read on every request
 * it makes (EnsureAccountNotBlocked, ApiAuthentication), so every way in is closed at once, an
 * open session included. Every schedule it owns is taken down exactly as Delete on
 * /admin/schedules does it (ScheduleDeletionService::markDeleted(): the page is gone, the name is
 * released, a Stripe subscription is cancelled, every row is kept). And its address goes on the
 * list that refuses new accounts (Blocklist), with the address it signed up from and its email
 * domain if the operator asks for those.
 *
 * Unblock undoes what the block did and nothing else: it brings back the schedules THIS block
 * took down (account_blocks.role_ids), not ones the owner or an operator had deleted before, and
 * removes the list entries this block added, not ones typed in by hand. A cancelled subscription
 * stays cancelled, and a name somebody took in the meantime is theirs.
 *
 * The flag is written first and in its own transaction; the schedules follow outside it, because
 * taking one down may call Stripe. A schedule that cannot be taken down (Stripe would not cancel)
 * is reported and left up, and the account stays blocked: the operator deletes that one by hand.
 */
class AccountBlockService
{
    public function __construct(private ScheduleDeletionService $deletions) {}

    /**
     * Why this account cannot be blocked by this operator, as the key of the sentence to show,
     * or null when it can.
     */
    public function refusal(User $user, User $actor): ?string
    {
        if ($user->id === $actor->id) {
            return 'messages.block_refused_self';
        }

        if ($user->isAdmin()) {
            return 'messages.block_refused_admin';
        }

        if ($user->email === DemoService::DEMO_EMAIL) {
            return 'messages.demo_mode_settings_disabled';
        }

        return null;
    }

    /** The schedules a block takes down: the ones this account owns that are still up. */
    public function schedules(User $user): Collection
    {
        return Role::where('user_id', $user->id)
            ->owned()
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->reject(fn (Role $role) => is_demo_role($role))
            ->values();
    }

    /**
     * @return array{block: AccountBlock, taken: Collection<int, Role>, failed: Collection<int, Role>, entries: Collection<int, BlocklistEntry>}
     */
    public function block(User $user, User $actor, ?string $note = null, bool $withAddress = false, bool $withDomain = false): array
    {
        if ($reason = $this->refusal($user, $actor)) {
            throw new \DomainException($reason);
        }

        [$block, $entries] = DB::transaction(function () use ($user, $actor, $note, $withAddress, $withDomain) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            // Not through save(): User::boot() lowercases and re-verifies on a save, and nothing
            // about the account itself is changing.
            if (! $locked->blocked_at) {
                DB::table('users')->where('id', $locked->id)->update(['blocked_at' => now()]);
            }

            $block = AccountBlock::firstOrCreate(
                ['user_id' => $locked->id],
                [
                    'blocked_by' => $actor->id,
                    'note' => ($note = trim((string) $note)) !== '' ? mb_substr($note, 0, 255) : null,
                    'role_ids' => [],
                ],
            );

            $entries = collect();
            $wanted = [[Blocklist::EMAIL, $locked->email]];

            if ($withAddress && ($range = Blocklist::visitorRange($locked->signup_ip))) {
                $wanted[] = [Blocklist::IP, $range];
            }

            if ($withDomain && str_contains((string) $locked->email, '@')) {
                $wanted[] = [Blocklist::DOMAIN, substr(strrchr($locked->email, '@'), 1)];
            }

            foreach ($wanted as [$type, $value]) {
                try {
                    $entries->push(Blocklist::add($type, $value, null, $actor->id, $block->id));
                } catch (\InvalidArgumentException $e) {
                    // An address the list cannot hold (an account made before addresses were
                    // validated). The account is blocked all the same.
                }
            }

            return [$block, $entries];
        });

        $user->blocked_at = $user->blocked_at ?: now();
        $user->syncOriginalAttribute('blocked_at');

        $taken = collect();
        $failed = collect();

        foreach ($this->schedules($user) as $role) {
            try {
                $this->deletions->markDeleted($role, $actor->id);
                $taken->push($role);
            } catch (BillingCancellationException|SubdomainUnavailableException $e) {
                report($e);
                $failed->push($role);
            }
        }

        if ($taken->isNotEmpty()) {
            $block->role_ids = array_values(array_unique(array_merge($block->role_ids ?? [], $taken->pluck('id')->all())));
            $block->save();
        }

        AuditService::log(
            AuditService::ADMIN_ACCOUNT_BLOCK,
            $actor->id,
            'App\\Models\\User',
            $user->id,
            null,
            [
                'schedules' => $taken->pluck('id')->all(),
                'not_taken_down' => $failed->pluck('id')->all(),
                'list' => $entries->map(fn (BlocklistEntry $entry) => $entry->type.':'.$entry->value)->all(),
            ],
            'Blocked account '.$user->id,
        );

        return ['block' => $block, 'taken' => $taken, 'failed' => $failed, 'entries' => $entries];
    }

    /**
     * @return array{restored: Collection<int, Role>, renamed: Collection<int, Role>} renamed: the
     *                                                                                ones brought back under their released name, because the original has been taken
     */
    public function unblock(User $user, User $actor): array
    {
        $roleIds = DB::transaction(function () use ($user) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $block = AccountBlock::where('user_id', $locked->id)->first();

            DB::table('users')->where('id', $locked->id)->update(['blocked_at' => null]);

            if (! $block) {
                return [];
            }

            foreach ($block->entries()->get() as $entry) {
                $this->releaseEntry($entry, $locked);
            }

            $roleIds = $block->role_ids ?? [];
            $block->delete();

            return $roleIds;
        });

        $user->blocked_at = null;
        $user->syncOriginalAttribute('blocked_at');

        $restored = collect();
        $renamed = collect();

        // Still this account's, and still down: a schedule an operator has restored or handed
        // to someone else since is not this block's to touch.
        $roles = $roleIds
            ? Role::whereIn('id', $roleIds)->where('user_id', $user->id)->where('is_deleted', true)->orderBy('name')->get()
            : collect();

        foreach ($roles as $role) {
            try {
                $result = $this->deletions->restore($role, $actor->id);
                $restored->push($role);

                if (! $result['reclaimed'] && $result['original']) {
                    $renamed->push($role);
                }
            } catch (SubdomainUnavailableException $e) {
                report($e);
            }
        }

        AuditService::log(
            AuditService::ADMIN_ACCOUNT_UNBLOCK,
            $actor->id,
            'App\\Models\\User',
            $user->id,
            null,
            ['schedules' => $restored->pluck('id')->all()],
            'Unblocked account '.$user->id,
        );

        return ['restored' => $restored, 'renamed' => $renamed];
    }

    /**
     * Take an entry a block added off the list, unless another blocked account stands behind
     * the same one (two accounts of one spammer share an address and a domain): then it passes
     * to that account's block, and letting this one back in does not let the other's next
     * account in with it.
     */
    private function releaseEntry(BlocklistEntry $entry, User $user): void
    {
        $others = User::whereNotNull('blocked_at')->where('id', '!=', $user->id);

        $heir = match ($entry->type) {
            Blocklist::DOMAIN => $others->where('email', 'like', '%@'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $entry->match_key))->first(),
            Blocklist::IP => $others->whereNotNull('signup_ip')->get()
                ->first(fn (User $other) => Blocklist::visitorRange($other->signup_ip) === $entry->match_key),
            default => null,
        };

        $heirBlock = $heir ? AccountBlock::where('user_id', $heir->id)->first() : null;

        if ($heirBlock) {
            $entry->account_block_id = $heirBlock->id;
            $entry->save();

            return;
        }

        $entry->delete();
    }
}
