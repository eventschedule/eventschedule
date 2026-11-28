<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\DB;

class ParticipantDirectory
{
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

    public static function page(Role $schedule, User $user, $selected): array
    {
        $rows = DB::table('backstage_entries as e')->join('roles as p', 'p.id', '=', 'e.participant_id')
            ->where('e.schedule_id', $schedule->id)->select('e.id', 'p.name')->orderBy('p.name')->orderBy('e.id')->paginate(30);
        $choices = self::choices($schedule, $user)->whereNotIn('id', DB::table('backstage_entries')->select('participant_id')->where('schedule_id', $schedule->id))->orderBy('name')->get(['id', 'name']);
        $entry = null;
        $details = [];
        if ($selected !== null) {
            $entry = DB::table('backstage_entries as e')->join('roles as p', 'p.id', '=', 'e.participant_id')->where('e.schedule_id', $schedule->id)->where('e.id', UrlUtils::decodeIdOrFail($selected))->select('e.id', 'e.promotion', 'p.name')->firstOrFail();
            foreach (['contacts', 'links', 'files'] as $kind) {
                $details[$kind] = DB::table('backstage_'.$kind)->where('entry_id', $entry->id)->orderBy('id')->get();
            }
        }

        return compact('rows', 'choices', 'entry', 'details');
    }
}
