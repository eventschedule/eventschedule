<?php

namespace App\Services\VendorMaps;

use App\Models\Event;
use App\Models\Role;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use Carbon\Carbon;

/**
 * What a schedule's guest page and its map's event page show of the vendor map: the band
 * (resources/views/role/partials/vendor-map-band.blade.php) with a row of faces and "Open map",
 * and on the event page the grid of who is there.
 *
 * Read in a fixed number of queries whatever the size of the market (PageQueryScalingTest):
 * the map, its event, its spots, the approved rows with their pages, and the spots' numbers.
 * Only what is public: approved vendors who are THERE on the date (never a waiting claim, a
 * phone, an account, or a vendor who is not coming), a name only without a picture or a link.
 */
class VendorMapBand
{
    /** Faces in the band's row before "+12". */
    public const FACES = 8;

    /** The schedule's own guest page: its map, if it has one that is on and has spots. */
    public static function forSchedule(Role $role): ?array
    {
        $map = VendorMap::where('role_id', $role->id)->where('enabled', true)->first();

        if (! $map) {
            return null;
        }

        $map->setRelation('role', $role);

        return self::build($role, $map, null);
    }

    /**
     * An event's page, only when it is the event a map serves: another event of the same
     * schedule is not the market. $date is the occurrence the page shows, when it is one.
     */
    public static function forEvent(Event $event, ?string $date = null): ?array
    {
        $map = VendorMap::where('event_id', $event->id)->where('enabled', true)->with('role')->first();

        if (! $map || ! $map->role || $map->role->is_deleted || ! $map->role->isClaimed()) {
            return null;
        }

        $map->setRelation('event', $event);

        return self::build($map->role, $map, $date);
    }

    private static function build(Role $role, VendorMap $map, ?string $date): ?array
    {
        $event = $map->event;

        // As the public map (VendorMapPublicController::show): a season that is not public has
        // no dates or vendors to show a stranger.
        if (! $event || $event->is_draft || $event->isInternal() || $event->isPasswordProtected() || $event->is_private) {
            return null;
        }

        // The event's own schedule decides its days; it is the map's schedule here, so its
        // timezone is read without asking for it again.
        if ((int) $event->creator_role_id === (int) $role->id) {
            $event->setRelation('creatorRole', $role);
        }

        if (! $date || ! VendorMapOccupancy::isOpenOn($map, $date)) {
            $date = VendorMapOccupancy::currentDate($map);
        }

        $state = $date ? VendorMapOccupancy::on($map, $date) : [];

        // At least one spot: a map nobody has drawn yet is not offered to the public.
        if ($state === [] && ! $map->spots()->exists()) {
            return null;
        }

        $present = array_filter($state, fn ($s) => $s['state'] === 'present' && $s['occupant']);
        $labels = $present
            ? VendorMapSpot::whereIn('id', array_keys($present))->pluck('label', 'id')->all()
            : [];

        $kind = $map->kind();
        $categories = VendorMapKinds::categoryLabels($kind);
        $vendors = [];
        foreach ($present as $spotId => $s) {
            /** @var VendorMapOccupant $occ */
            $occ = $s['occupant'];
            $page = $occ->vendor_role_id && $occ->vendor && ! $occ->vendor->is_deleted ? $occ->vendor : null;
            $vendors[] = [
                'name' => $occ->displayName(),
                'label' => (string) ($labels[$spotId] ?? ''),
                'sells' => $occ->sells,
                'categories' => array_values(array_filter(array_map(fn ($c) => $categories[$c] ?? null, (array) $occ->categories))),
                // A name only is plain text: no picture, no link.
                'photo' => $page ? (VendorMapImages::publicUrl($occ->photo_thumb_path) ?: ($page->profile_image_url ?: null)) : null,
                'url' => $page ? ($page->getGuestUrl() ?: null) : null,
                'initials' => self::initials($occ->displayName()),
            ];
        }

        usort($vendors, fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));

        $today = $date === Carbon::now($event->scheduleTimezone())->format('Y-m-d');

        return [
            'kind' => $kind,
            'title' => VendorMapKinds::say($kind, 'this_week'),
            'people' => VendorMapKinds::say($kind, 'people'),
            'spotWord' => fn (string $number) => VendorMapKinds::say($kind, 'spot', ['number' => $number]),
            'date' => $date,
            'dateLabel' => $date ? ($today ? __('messages.vm_public_today_date', ['date' => VendorMapPresenter::longDate($date)]) : VendorMapPresenter::longDate($date)) : null,
            'vendors' => $vendors,
            // Faces with a picture first, then initials, so the row reads as people.
            'faces' => array_slice(array_merge(
                array_values(array_filter($vendors, fn ($v) => $v['photo'])),
                array_values(array_filter($vendors, fn ($v) => ! $v['photo'])),
            ), 0, self::FACES),
            'count' => count($vendors),
            'url' => VendorMapPresenter::publicUrl($role),
            'rtl' => $role->isRtl(),
        ];
    }

    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $out .= mb_substr($word, 0, 1);
        }

        return mb_strtoupper($out);
    }
}
