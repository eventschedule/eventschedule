<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\DB;

class ParticipantDirectory
{
    /** Sync profile contact details until the schedule explicitly edits its private copy. */
    public static function register(int $scheduleId, array $participantIds): void
    {
        $ids = array_values(array_unique($participantIds));
        if (! $ids) {
            return;
        }
        $rows = DB::table('roles')->whereIn('id', $ids)->where('is_deleted', false)
            ->selectRaw('? as schedule_id, id as participant_id, email as participant_email, phone as participant_phone, website as participant_website', [$scheduleId])
            ->get()->map(fn ($row) => (array) $row)->all();
        if ($rows) {
            DB::table('backstage_entries')->insertOrIgnore($rows);
            DB::table('backstage_entries as e')
                ->join('roles as p', 'p.id', '=', 'e.participant_id')
                ->where('e.schedule_id', $scheduleId)
                ->whereIn('e.participant_id', $ids)
                ->whereNull('e.participant_info_updated_at')
                ->update([
                    'e.participant_email' => DB::raw('p.email'),
                    'e.participant_phone' => DB::raw('p.phone'),
                    'e.participant_website' => DB::raw('p.website'),
                ]);
        }
    }

    public static function choices(Role $schedule, User $user)
    {
        return Role::where('type', 'talent')->where('is_deleted', false)->where(function ($q) use ($schedule, $user) {
            $q->whereIn('id', $user->roles()->select('roles.id')->toBase())
                ->orWhereIn('id', DB::table('event_role')->select('role_id')->whereIn('event_id', $schedule->ownEvents()->select('events.id')->toBase()));
        });
    }

    /** The event picker gets identity only, never private directory details. */
    public static function picker(Role $schedule, $members): array
    {
        $extra = Role::whereIn('id', DB::table('backstage_entries')->select('participant_id')->where('schedule_id', $schedule->id))
            ->where('is_deleted', false)->get(['id', 'name', 'user_id'])->map(fn ($p) => [
                'id' => UrlUtils::encodeId($p->id), 'name' => $p->name, 'user_id' => UrlUtils::encodeId($p->user_id),
            ]);

        return $members->concat($extra)->unique('id')->sortBy('name')->values()->all();
    }

    public static function page(Role $schedule, User $user, $selected, string $search): array
    {
        $rows = null;
        $entry = null;
        $details = [];
        if ($selected !== null) {
            $entry = Role::query()->join('backstage_entries as e', 'roles.id', '=', 'e.participant_id')
                ->where('e.schedule_id', $schedule->id)->where('e.id', UrlUtils::decodeIdOrFail($selected))
                ->firstOrFail(['e.id as entry_id', 'e.promotion', 'e.participant_email', 'e.participant_phone', 'e.participant_website', 'roles.id', 'roles.name', 'roles.youtube_links', 'roles.subdomain', 'roles.user_id', 'roles.email_verified_at', 'roles.phone_verified_at']);
            foreach (['contacts', 'links', 'files'] as $kind) {
                $details[$kind] = DB::table('backstage_'.$kind)->where('entry_id', $entry->entry_id)->orderBy('id')->get();
            }
            $video = $entry->getFirstVideoUrl();
            if (is_string($video) && filter_var($video, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($video, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                $details['links']->prepend((object) ['profileVideo' => true, 'url' => $video, 'editable' => $entry->isEditableBy($user)]);
            }
        } else {
            $rows = DB::table('roles as p')->leftJoin('backstage_entries as e', fn ($join) => $join
                ->on('e.participant_id', '=', 'p.id')->where('e.schedule_id', $schedule->id))
                ->where(fn ($q) => $q->whereNotNull('e.id')->orWhereIn('p.id', self::choices($schedule, $user)->select('roles.id')))
                ->when($search !== '', fn ($q) => $q->whereRaw("p.name LIKE ? ESCAPE '!'", ['%'.strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']).'%']))
                ->orderBy('p.name')->orderBy('p.id')->paginate(30, ['p.id as participant_id', 'p.name', 'e.id as entry_id'])->appends(['q' => $search]);
        }

        return compact('rows', 'search', 'entry', 'details');
    }
}
