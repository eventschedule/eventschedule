<?php

namespace App\Http\Controllers\Traits;

use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

trait CalendarDataTrait
{
    protected function calendarEventToVueArray(Event $event, ?Role $role, ?string $subdomain, ?array $userAdminRoleIds = null): array
    {
        $groupId = $role ? $event->getGroupIdForSubdomain($role->subdomain) : null;

        $curatorTranslation = null;
        if ($role && $role->isCurator()) {
            $curatorPivot = $event->roles->where('id', $role->id)->first();
            if ($curatorPivot && $curatorPivot->pivot && $curatorPivot->pivot->name_translated) {
                $curatorTranslation = $curatorPivot->pivot;
            }
        }

        $user = auth()->user();
        $canEdit = false;
        if ($user && $userAdminRoleIds !== null) {
            $canEdit = $user->id == $event->user_id
                || $event->roles->contains(fn ($r) => in_array($r->id, $userAdminRoleIds));
        } elseif ($user) {
            $canEdit = $user->canEditEvent($event);
        }

        $data = [
            'id' => UrlUtils::encodeId($event->id),
            'group_id' => $groupId ? UrlUtils::encodeId($groupId) : null,
            'category_id' => $event->category_id,
            'name' => $curatorTranslation ? $curatorTranslation->name_translated : $event->translatedName(),
            'short_description' => $curatorTranslation && $curatorTranslation->short_description_translated ? $curatorTranslation->short_description_translated : $event->translatedShortDescription(),
            'venue_name' => $event->getVenueDisplayName(),
            'venue_subdomain' => $event->venue?->subdomain ?: null,
            'is_free' => $event->isFree(),
            'starts_at' => $event->starts_at,
            'days_of_week' => $event->days_of_week,
            'local_starts_at' => $event->localStartsAt(),
            'local_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'utc_date' => $event->starts_at ? $event->getStartDateTime(null, false)->format('Y-m-d') : null,
            'guest_url' => $event->getGuestUrl($subdomain ?? '', ''),
            'image_url' => $event->getImageUrl(),
            'flyer_url' => $event->flyer_image_url ?: null,
            'can_edit' => $canEdit,
            'edit_url' => $canEdit
                ? ($role ? app_url(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)], false)) : app_url(route('event.edit_admin', ['hash' => UrlUtils::encodeId($event->id)], false)))
                : null,
            'recurring_end_type' => $event->recurring_end_type ?? 'never',
            'recurring_end_value' => $event->recurring_end_value,
            'recurring_frequency' => $event->recurring_frequency,
            'recurring_interval' => $event->recurring_interval,
            'start_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'is_online' => ! empty($event->event_url),
            'registration_url' => $event->registration_url,
            'ticket_price' => $event->ticket_price,
            'ticket_currency_code' => $event->ticket_currency_code,
            'coupon_code' => $event->coupon_code,
            'description_excerpt' => Str::words(strip_tags($curatorTranslation && $curatorTranslation->description_html_translated ? $curatorTranslation->description_html_translated : $event->translatedDescription()), 25, '...'),
            'duration' => $event->duration,
            'parts' => $event->parts->map(fn ($part) => [
                'id' => UrlUtils::encodeId($part->id),
                'name' => $part->translatedName(),
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
            ])->values()->toArray(),
            'video_count' => $event->approved_videos_count ?? 0,
            'comment_count' => $event->approved_comments_count ?? 0,
            'photo_count' => $event->approved_photos_count ?? 0,
            'venue_profile_image' => $event->venue?->profile_image_url ?: null,
            'venue_header_image' => ($event->venue && $event->venue->getAttributes()['header_image'] && $event->venue->getAttributes()['header_image'] !== 'none') ? $event->venue->getHeaderImageUrlAttribute($event->venue->getAttributes()['header_image']) : null,
            'venue_guest_url' => ($event->venue && isset($role) && $event->venue->subdomain === $role->subdomain) ? null : ($event->venue?->getGuestUrl() ?: null),
            'talent' => $event->roles->filter(fn ($r) => $r->type === 'talent')->map(fn ($r) => [
                'name' => $r->name,
                'profile_image' => $r->profile_image_url ?: null,
                'header_image' => ($r->getAttributes()['header_image'] && $r->getAttributes()['header_image'] !== 'none') ? $r->getHeaderImageUrlAttribute($r->getAttributes()['header_image']) : null,
                'guest_url' => (isset($role) && $r->subdomain === $role->subdomain) ? null : ($r->getGuestUrl() ?: null),
            ])->values()->toArray(),
            'videos' => $event->relationLoaded('approvedVideos') ? $event->approvedVideos->take(3)->map(fn ($v) => [
                'youtube_url' => $v->youtube_url,
                'thumbnail_url' => UrlUtils::getYouTubeThumbnail($v->youtube_url),
                'embed_url' => UrlUtils::getYouTubeEmbed($v->youtube_url),
            ])->values()->toArray() : [],
            'recent_comments' => $event->relationLoaded('approvedComments') ? $event->approvedComments->take(2)->map(fn ($c) => [
                'author' => $c->user ? ($c->user->first_name ?: 'User') : 'User',
                'text' => Str::limit($c->comment, 80),
            ])->values()->toArray() : [],
            'photos' => $event->relationLoaded('approvedPhotos') ? $event->approvedPhotos->take(4)->map(fn ($p) => [
                'url' => $p->photo_url,
            ])->values()->toArray() : [],
            'occurrenceDate' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'uniqueKey' => UrlUtils::encodeId($event->id),
            'submit_video_url' => isset($role) ? route('event.submit_video', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($event->id)]) : null,
            'submit_comment_url' => isset($role) ? route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($event->id)]) : null,
            'submit_photo_url' => isset($role) ? route('event.submit_photo', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($event->id)]) : null,
            'polls' => (isset($role) && $role->isPro() && $event->relationLoaded('polls')) ? $event->polls->map(fn ($poll) => [
                'id' => UrlUtils::encodeId($poll->id),
                'question' => $poll->question,
                'options' => $poll->options,
                'total_votes' => $poll->votes_count ?? $poll->votes()->count(),
                'results' => $poll->getResults(),
                'user_vote' => $user ? $poll->getUserVote($user->id) : null,
                'is_active' => $poll->is_active,
            ])->values()->toArray() : [],
            'poll_count' => (isset($role) && $role->isPro()) ? ($event->polls_count ?? 0) : 0,
            'vote_poll_url' => (isset($role) && $role->isPro()) ? route('event.vote_poll', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($event->id), 'poll_hash' => 'POLL_HASH']) : null,
            'custom_field_values' => $event->custom_field_values ?? [],
            'is_password_protected' => $event->isPasswordProtected(),
        ];

        if ($event->isPasswordProtected()) {
            $data['short_description'] = null;
            $data['description_excerpt'] = null;
            $data['venue_name'] = null;
            $data['venue_guest_url'] = null;
            $data['venue_profile_image'] = null;
            $data['venue_header_image'] = null;
            $data['ticket_price'] = null;
            $data['registration_url'] = null;
            $data['coupon_code'] = null;
            $data['talent'] = [];
            $data['videos'] = [];
            $data['recent_comments'] = [];
            $data['photos'] = [];
            $data['polls'] = [];
            $data['parts'] = [];
            $data['image_url'] = null;
            $data['flyer_url'] = null;
            $data['custom_field_values'] = [];
        }

        return $data;
    }

    protected function buildEventsMap($events, Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        $eventsMap = [];
        foreach ($events as $event) {
            $checkDate = $startOfMonth->copy();
            while ($checkDate->lte($endOfMonth)) {
                if ($event->matchesDate($checkDate)) {
                    $dateStr = $checkDate->format('Y-m-d');
                    if (! isset($eventsMap[$dateStr])) {
                        $eventsMap[$dateStr] = [];
                    }
                    $eventsMap[$dateStr][] = UrlUtils::encodeId($event->id);
                }
                $checkDate->addDay();
            }
        }

        return $eventsMap;
    }

    protected function buildFilterMeta($events): array
    {
        $uniqueCategoryIds = [];
        $hasOnlineEvents = false;

        foreach ($events as $event) {
            if (isset($event->category_id) && $event->category_id) {
                $uniqueCategoryIds[$event->category_id] = true;
            }
            if (! empty($event->event_url)) {
                $hasOnlineEvents = true;
            }
        }

        return [
            'uniqueCategoryIds' => array_values(array_keys($uniqueCategoryIds)),
            'hasOnlineEvents' => $hasOnlineEvents,
        ];
    }

    protected function buildCalendarResponse($events, $pastEvents, bool $hasMorePastEvents, ?Role $role, ?string $subdomain, int $month, int $year, string $timezone, int $firstDayOfWeek = 0): JsonResponse
    {
        $lastDay = ($firstDayOfWeek + 6) % 7;
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek($firstDayOfWeek);
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek($lastDay);

        $user = auth()->user();
        $userAdminRoleIds = $user
            ? $user->roles()->wherePivotIn('level', ['owner', 'admin'])->pluck('roles.id')->all()
            : [];

        $eventsForVue = [];
        foreach ($events as $event) {
            $eventsForVue[] = $this->calendarEventToVueArray($event, $role, $subdomain, $userAdminRoleIds);
        }

        $pastEventsForVue = [];
        foreach ($pastEvents as $event) {
            $pastEventsForVue[] = $this->calendarEventToVueArray($event, $role, $subdomain, $userAdminRoleIds);
        }

        $eventsMap = $this->buildEventsMap($events, $startOfMonth, $endOfMonth);
        $filterMeta = $this->buildFilterMeta($events);

        return response()->json([
            'events' => $eventsForVue,
            'eventsMap' => (object) $eventsMap,
            'pastEvents' => $pastEventsForVue,
            'hasMorePastEvents' => $hasMorePastEvents,
            'filterMeta' => $filterMeta,
        ]);
    }
}
