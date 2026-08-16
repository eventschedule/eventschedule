<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\User;

/**
 * Shared rules for the videos stored in `roles.youtube_links`.
 *
 * Both the guest-page remove button and the endpoint behind it go through canRemoveVideo(), so the
 * control cannot appear without the request being authorised, or vice versa.
 */
class VideoUtils
{
    /**
     * Whether $user may remove a video from $target while looking at $viewedRole's guest pages.
     *
     * $target is usually a talent schedule appearing on someone else's page; it is $viewedRole
     * itself for the schedule's own video grid.
     *
     * This deliberately does NOT mirror RoleController::saveVideo()'s contract, and the two should
     * not be "aligned" without thinking about it:
     *
     *  - BROADER in who may act. saveVideo requires the acting schedule to be a curator, but the
     *    carousel renders on venue schedules too (role/show-guest.blade.php gates it on
     *    `! $role->isTalent()`), and the event page renders talent cards whatever type of schedule
     *    the URL was reached through. A venue owner looking at a broken embed on their own page has
     *    exactly the same problem a curator does.
     *  - STRICTLY NARROWER in what may be touched. saveVideo has no isClaimed() check at all; this
     *    does. A claimed schedule owns its own videos, and only its own team may delete them. That
     *    guard is load-bearing - the "shares an accepted event" relation below is cheap to
     *    manufacture, since attaching a role to your own event does not require the role's consent.
     *
     * For a destructive, cross-schedule action that is the right direction to drift in.
     */
    public static function canRemoveVideo(?User $user, ?Role $target, ?Role $viewedRole): bool
    {
        if (! $user || ! $target || ! $viewedRole) {
            return false;
        }

        if (is_demo_mode()) {
            return false;
        }

        if (! $user->isEditor($viewedRole->subdomain)) {
            return false;
        }

        // Parenthesised on purpose: `$a && $b || $c` would drop the editor check above for the
        // second branch.
        return $user->isEditor($target->subdomain)
            || (! $target->isClaimed() && self::sharesAcceptedEvent($viewedRole, $target));
    }

    /**
     * Whether $target appears on any event $viewedRole has accepted.
     *
     * Only the viewing schedule's side is required to be accepted. The talent's own side is not:
     * an unaccepted talent still renders on the event page (Event::members() applies no
     * is_accepted filter), so its broken embed is visible and has to be removable.
     */
    public static function sharesAcceptedEvent(Role $viewedRole, Role $target): bool
    {
        return \DB::table('event_role as viewer_er')
            ->join('event_role as target_er', 'viewer_er.event_id', '=', 'target_er.event_id')
            ->where('viewer_er.role_id', $viewedRole->id)
            ->where('viewer_er.is_accepted', true)
            ->where('target_er.role_id', $target->id)
            ->exists();
    }

    /**
     * Drop every link pointing at the same video as $videoUrl.
     *
     * Matching is on the extracted YouTube id so a youtu.be short link and a watch?v= link to the
     * same video both go, with an exact-string fallback for entries that are not YouTube URLs at
     * all (nothing validated the column's contents until recently). Both ids being null must NOT
     * count as a match, or one unparseable URL would delete another.
     *
     * Survivors are passed through untouched, never rebuilt: the column holds two different shapes
     * - `{url, title, type}` from the video matcher and `{name, url, thumbnail_url}` from the
     * schedule editor - and rebuilding would strip whichever keys this code did not know about.
     *
     * @param  array  $links  decoded links, as returned by Role::decodeLinks()
     * @return array the surviving links, re-indexed
     */
    public static function removeByUrl(array $links, string $videoUrl): array
    {
        $targetId = UrlUtils::extractYouTubeVideoId($videoUrl);

        return array_values(array_filter($links, function ($link) use ($videoUrl, $targetId) {
            $linkUrl = $link->url ?? '';

            if ($linkUrl === $videoUrl) {
                return false;
            }

            $linkId = UrlUtils::extractYouTubeVideoId($linkUrl);

            return ! ($targetId && $linkId === $targetId);
        }));
    }

    /**
     * Drop every link whose YouTube id is in $videoIds. Survivors are passed through untouched,
     * for the same reason as removeByUrl().
     *
     * @param  array  $links  decoded links, as returned by Role::decodeLinks()
     * @param  array  $videoIds  YouTube ids known to be unplayable
     * @return array the surviving links, re-indexed
     */
    public static function removeByVideoIds(array $links, array $videoIds): array
    {
        if (! $videoIds) {
            return array_values($links);
        }

        $videoIds = array_flip($videoIds);

        return array_values(array_filter($links, function ($link) use ($videoIds) {
            $linkId = UrlUtils::extractYouTubeVideoId($link->url ?? '');

            return ! ($linkId && isset($videoIds[$linkId]));
        }));
    }

    /**
     * The value to store in `roles.youtube_links` for a given set of links.
     *
     * An emptied list becomes null, not '[]'. The two are not interchangeable: '[]' is what the
     * matcher's Skip button writes, and RoleController::getTalentRolesWithoutVideos() reads it as
     * "a person decided this act gets no video". Removing a video that turned out to be broken is
     * a different fact, and writing the tombstone for it would blacklist the act from ever being
     * offered a working replacement, with no UI anywhere to undo that.
     */
    public static function encodeLinks(array $links): ?string
    {
        return $links ? json_encode(array_values($links)) : null;
    }
}
