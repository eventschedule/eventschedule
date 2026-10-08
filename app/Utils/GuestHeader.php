<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\User;

/**
 * What a schedule's public header says and offers, decided once for the page.
 *
 * The header's two styles (role/partials/headers/banner and compact) and the slim bar that
 * follows the banner down the page all draw the same buttons, and the banner used to work the
 * rules out twice, once for each of its two hand-copied bodies. They are here so the three ask
 * one question and a signed-in visitor costs one set of lookups.
 */
final class GuestHeader
{
    /** EventRepo::upcomingForGuest() is asked for this many, so a full list means "at least". */
    public const UPCOMING_CAP = 50;

    /**
     * Which buttons the header offers this visitor, and which one leads.
     *
     * One button is the main one: Book a time where the schedule takes appointments (that is
     * what such a page is for), Follow otherwise. Everything else is secondary.
     *
     * @return array{book: bool, submit: bool, gift: bool, follow: bool, manage: bool, following: bool, main: ?string}
     */
    public static function actions(Role $role, ?User $viewer): array
    {
        // Follow and Manage belong to the platform's accounts, which a selfhosted install
        // without them does not offer.
        $onPlatform = config('app.hosted') || config('app.is_testing');
        $submit = ($role->isCurator() || $role->isVenue() || $role->isTalent()) && (bool) $role->accept_requests;

        // Whether to offer the Follow / subscribe trigger.
        //
        // This used to be a pair of branches that made the trigger depend on the Submit button,
        // and the effect for a SIGNED-OUT visitor was that a schedule accepting event requests
        // showed Submit INSTEAD of Follow. That was invisible while every schedule made through
        // the UI had accept_requests false, so flipping that default would have quietly removed
        // the Follow button from every new schedule's public page. Follow is what mints
        // subscriber accounts.
        //
        // Signed out: always offer it. Signed in: unchanged from before.
        $connected = $viewer ? $viewer->isConnected($role->subdomain) : false;
        $showFollow = ! $viewer
            || ($submit
                ? (! $viewer->isFollowing($role->subdomain) && ! $connected)
                : ! $connected);

        $follow = $onPlatform && ! is_demo_mode() && $showFollow;
        $manage = $onPlatform && $viewer && $viewer->isMember($role->subdomain);
        $book = $role->hasBookableAppointments();

        return [
            'book' => $book,
            'submit' => $submit,
            'gift' => $role->canSellGiftCards(),
            'follow' => $follow,
            'manage' => (bool) $manage,
            // A follower is told so where the button was, instead of meeting a gap.
            'following' => $onPlatform && $viewer && ! $follow && ! $manage && $viewer->isFollowing($role->subdomain),
            'main' => $book ? 'book' : ($follow ? 'follow' : null),
        ];
    }

    /**
     * "12 upcoming events", or null where there are none to count.
     *
     * The count is of the events the page already has, so a list at its cap reads "50+". Where
     * the owner renamed "Events" (classes, shows, screenings) the fact is the number and their
     * word: a sentence built around another word cannot be trusted in twelve languages.
     */
    public static function upcomingFact(Role $role, int $count): ?string
    {
        if ($count < 1) {
            return null;
        }

        $shown = $count >= self::UPCOMING_CAP ? self::UPCOMING_CAP.'+' : (string) $count;
        $renamed = trim((string) (($role->custom_labels ?? [])['events']['value'] ?? ''));

        if ($renamed !== '') {
            return $shown.' '.$role->customLabel('events');
        }

        return trans_choice('messages.upcoming_events_count', $count, ['count' => $shown]);
    }

    /**
     * The class that steps a long name down, so the name never runs to four lines. Decided here
     * and not in the browser, so the name is drawn once at its size.
     */
    public static function nameStep(string $name): string
    {
        $length = mb_strlen(trim($name));

        return $length > 40 ? 'gk-head-name-s' : ($length > 26 ? 'gk-head-name-m' : '');
    }
}
