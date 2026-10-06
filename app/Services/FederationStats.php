<?php

namespace App\Services;

use App\Models\FederatedEvent;
use Illuminate\Support\Facades\DB;

/**
 * The hub's numbers about federation: the selfhost installs that registered here, what they list,
 * and how many visitors were sent on to them. Nexus only - the tables are empty anywhere else.
 *
 * One class for the two readers (the admin dashboard and the growth payload), so the page and the
 * export cannot count installs differently.
 *
 * What it can know is what was pushed to us: the nexus never learns an install's users, schedules
 * or sales, and CheckVersion talks to GitHub, not here - so every count is a floor on selfhost
 * installs, never a census of them.
 */
class FederationStats
{
    /**
     * Installs by status, how many approved ones were seen in the last 30 days, and their app
     * versions. Never the site URL, name or contact: this is what the growth payload exports.
     *
     * @return array{by_status: array<string, int>, active_30d: int, by_version: array<string, int>}
     */
    public static function instances(): array
    {
        $instances = DB::table('federated_instances');

        return [
            'by_status' => (clone $instances)->selectRaw('status, COUNT(*) as c')->groupBy('status')
                ->pluck('c', 'status')->map(fn ($n) => (int) $n)->all(),
            'active_30d' => (clone $instances)->where('status', 'approved')
                ->where('last_seen_at', '>=', now()->copy()->subDays(30))->count(),
            'by_version' => (clone $instances)->where('status', 'approved')
                ->selectRaw("COALESCE(app_version, 'unknown') as v, COUNT(*) as c")
                ->groupBy(DB::raw("COALESCE(app_version, 'unknown')"))->orderByDesc('c')->limit(10)
                ->pluck('c', 'v')->map(fn ($n) => (int) $n)->all(),
        ];
    }

    /**
     * What is listed publicly right now (FederatedEvent::listable(): an approved install, not
     * blocked, with an image and a date still ahead) and how many schedules it comes from.
     *
     * A schedule is its page on one install: two installs may each have a "Jazz Club". Online and
     * hybrid follow FederatedEvent::isOnline() / isHybrid(); the rest is in person.
     *
     * @return array{live: int, schedules: int, online: int, hybrid: int, in_person: int}
     */
    public static function listings(): array
    {
        $row = FederatedEvent::query()->listable()->toBase()->selectRaw(
            "COUNT(*) as live,
            COUNT(DISTINCT CASE WHEN schedule_url IS NOT NULL AND schedule_url <> '' THEN CONCAT(federated_instance_id, '|', schedule_url) END) as schedules,
            COALESCE(SUM(is_online = 1 AND COALESCE(venue_name, '') = ''), 0) as online,
            COALESCE(SUM(is_online = 1 AND COALESCE(venue_name, '') <> ''), 0) as hybrid"
        )->first();

        $live = (int) ($row->live ?? 0);
        $online = (int) ($row->online ?? 0);
        $hybrid = (int) ($row->hybrid ?? 0);

        return [
            'live' => $live,
            'schedules' => (int) ($row->schedules ?? 0),
            'online' => $online,
            'hybrid' => $hybrid,
            'in_person' => $live - $online - $hybrid,
        ];
    }

    /**
     * Visitors sent from the hub out to installs over the last $days days (today included), against
     * the $days before, and the installs that received most. federation_clicks_daily is one row
     * per install per day, so this reads at most installs x days rows.
     *
     * `change` is null when there is no earlier figure to compare with.
     *
     * @return array{total: int, previous: int, change: ?float, top: array<int, array{id: int, name: string, host: ?string, listings: int, clicks: int}>}
     */
    public static function clicks(int $days = 30, int $top = 5): array
    {
        $from = now()->copy()->startOfDay()->subDays($days - 1);

        $rows = DB::table('federation_clicks_daily as c')
            ->join('federated_instances as i', 'i.id', '=', 'c.federated_instance_id')
            ->where('c.date', '>=', $from->toDateString())
            ->groupBy('i.id', 'i.name', 'i.site_url')
            ->selectRaw('i.id, i.name, i.site_url, SUM(c.clicks) as clicks')
            ->orderByRaw('SUM(c.clicks) DESC')
            ->get();

        $total = (int) $rows->sum('clicks');
        $previous = (int) DB::table('federation_clicks_daily')
            ->where('date', '>=', $from->copy()->subDays($days)->toDateString())
            ->where('date', '<', $from->toDateString())
            ->sum('clicks');

        $leaders = $rows->take($top);
        $listings = $leaders->isEmpty() ? collect() : FederatedEvent::query()->live()->toBase()
            ->whereIn('federated_instance_id', $leaders->pluck('id')->all())
            ->groupBy('federated_instance_id')
            ->selectRaw('federated_instance_id, COUNT(*) as c')
            ->pluck('c', 'federated_instance_id');

        return [
            'total' => $total,
            'previous' => $previous,
            'change' => $previous > 0 ? round((($total - $previous) / $previous) * 100, 1) : null,
            'top' => $leaders->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'host' => parse_url((string) $row->site_url, PHP_URL_HOST) ?: null,
                'listings' => (int) ($listings[$row->id] ?? 0),
                'clicks' => (int) $row->clicks,
            ])->values()->all(),
        ];
    }
}
