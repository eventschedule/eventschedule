<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\Setting;
use App\Services\BlockedAccounts;
use Throwable;

/**
 * The quotes on the marketing homepage: up to three things schedules on the platform have said,
 * which the operator types in at /admin/settings#home-quotes.
 *
 * WHAT IS STORED: a schedule's id, the quote, a short line about who said it ("Owner, Jazz club
 * in Austin") and, optionally, the name of the person who said it, as JSON in one Setting. The
 * schedule's own name, picture and page are read live from the schedule, so a rename carries over.
 * Erasing an account forgets the quotes on every schedule it owned, the name with them
 * (AccountDeletionService::prepare() calls forget()).
 * A person's name is printed only because the operator typed it in with their permission, and it
 * lives in this Setting alone: never in the repo (it is public), a fixture or an audit row. Asked
 * to take it down, the operator clears the row.
 *
 * WHAT IS SHOWN: only quotes whose schedule still has a page (claimed, not deleted) and whose owner
 * is not blocked. A schedule taken down takes its quote with it, without anybody editing the list.
 *
 * The homepage is edge-cached on its URL (docs/CACHING.md), so this is the same for every visitor;
 * a change shows within the edge's ten minutes.
 */
final class HomeQuotes
{
    public const SETTING = 'home_quotes';

    public const MAX = 3;

    /** A customer's own words are printed as they wrote them, so there is room for a paragraph. */
    public const QUOTE_MAX = 600;

    public const NAME_MAX = 80;

    public const TITLE_MAX = 80;

    /**
     * What is stored, tolerant of anything malformed: a quote that cannot be read is left out
     * rather than taking the homepage down with it.
     *
     * @return list<array{role_id: int, quote: string, title: string, name: string}>
     */
    public static function stored(): array
    {
        $decoded = json_decode((string) Setting::get(self::SETTING, ''), true);

        if (! is_array($decoded)) {
            return [];
        }

        $quotes = [];
        foreach ($decoded as $row) {
            if (! is_array($row) || ! is_numeric($row['role_id'] ?? null) || ! is_string($row['quote'] ?? null) || trim($row['quote']) === '') {
                continue;
            }

            $quotes[] = [
                'role_id' => (int) $row['role_id'],
                'quote' => $row['quote'],
                'title' => is_string($row['title'] ?? null) ? $row['title'] : '',
                'name' => is_string($row['name'] ?? null) ? $row['name'] : '',
            ];

            if (count($quotes) === self::MAX) {
                break;
            }
        }

        return $quotes;
    }

    /**
     * The quotes the homepage prints, each with its schedule's own name, picture and address.
     *
     * @return list<array{quote: string, title: string, person: string, name: string, image: ?string, url: string}>
     */
    public static function forPage(): array
    {
        try {
            $stored = self::stored();

            if ($stored === []) {
                return [];
            }

            $blocked = array_flip((new BlockedAccounts)->scheduleIds());
            $roles = Role::query()
                ->whereIn('id', array_column($stored, 'role_id'))
                ->where('is_deleted', false)
                ->get()
                ->keyBy('id');

            $quotes = [];
            foreach ($stored as $row) {
                $role = $roles->get($row['role_id']);

                if (! $role || isset($blocked[$role->id]) || ! $role->hasRealOwner()) {
                    continue;
                }

                $url = $role->getGuestUrl();
                if (! $url) {
                    continue;
                }

                $image = $role->profile_image_url ? $role->getProfileImageUrl(160) : null;

                $quotes[] = [
                    'quote' => $row['quote'],
                    'title' => $row['title'],
                    'person' => $row['name'],
                    'name' => (string) $role->name,
                    'image' => $image ?: null,
                    'url' => $url,
                ];
            }

            return $quotes;
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Drop the quotes on these schedules, for an account that is being erased.
     *
     * @param  array<int|string>  $roleIds
     */
    public static function forget(array $roleIds): void
    {
        $roleIds = array_map('intval', $roleIds);
        $stored = self::stored();
        $kept = array_values(array_filter($stored, fn ($quote) => ! in_array($quote['role_id'], $roleIds, true)));

        if (count($kept) !== count($stored)) {
            self::save($kept);
        }
    }

    /**
     * @param  list<array{role_id: int, quote: string, title: string, name: string}>  $quotes
     */
    public static function save(array $quotes): void
    {
        Setting::set(self::SETTING, $quotes === [] ? null : json_encode(array_values($quotes)));
    }
}
