<?php

namespace App\Http\Controllers;

use App\Jobs\PublishCalendarHolds;
use App\Models\CalendarImportHold;
use App\Models\Event;
use App\Models\Role;
use App\Services\CalendarArrival;
use App\Services\EventLifecycleService;
use App\Utils\CalendarPrivacy;
use App\Utils\UrlUtils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Events a calendar sync is keeping off a schedule's public page (CalendarArrival), for the
 * schedule's owner and admins to publish or keep hidden. Three lists: what arrived as a draft
 * and waits, what was taken off the page (it was public, and the calendar or its text now says
 * it should not be), and what the owner chose to keep hidden.
 *
 * There is no Delete: deleting the copy here does nothing to the entry in the owner's calendar,
 * and the next full read would bring it back. Repeated meetings are one row: a weekly standup is
 * fifty-two entries, and nobody should press Publish fifty-two times.
 */
class CalendarReviewController extends Controller
{
    /** How many held events one page reads. More than that is said, not listed. */
    private const LIMIT = 500;

    public const SHOWS = ['waiting', 'taken', 'hidden'];

    public function index(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $show = in_array($request->query('show'), self::SHOWS, true) ? $request->query('show') : 'waiting';
        $search = trim((string) $request->query('q', ''));

        $holds = self::query($role, $show)
            ->when($search !== '', fn ($q) => $q->whereHas('event', fn ($e) => $e->where('name', 'like', '%'.addcslashes($search, '\\%_').'%')))
            ->with('event.roles')
            ->join('events', 'events.id', '=', 'calendar_import_holds.event_id')
            ->orderBy('events.starts_at')
            ->select('calendar_import_holds.*')
            ->limit(self::LIMIT + 1)
            ->get()
            ->filter(fn (CalendarImportHold $hold) => $hold->event !== null);

        $more = $holds->count() > self::LIMIT;
        $holds = $holds->take(self::LIMIT);

        $publishing = [];
        foreach (CalendarArrival::PROVIDERS as $provider) {
            $publishing[$provider] = CalendarArrival::publishes($role, $provider);
        }

        return view('role.calendar-review', [
            'role' => $role,
            'show' => $show,
            'search' => $search,
            'groups' => self::groups($holds),
            'more' => $more,
            'counts' => collect(self::SHOWS)->mapWithKeys(fn ($s) => [$s => self::query($role, $s)->count()])->all(),
            'reviewCount' => self::query($role, 'waiting')->where('reason', CalendarArrival::HOLD_REVIEW)->count(),
            'publishing' => $publishing,
        ]);
    }

    /** Publish, or keep hidden, the events ticked or the one pressed. */
    public function decide(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $one = $request->input('publish_one') ?? $request->input('hide_one');
        $action = $one !== null
            ? ($request->filled('publish_one') ? 'publish' : 'hide')
            : $request->input('action');

        $ids = collect($one !== null ? [$one] : (array) $request->input('events', []))
            ->flatMap(fn ($value) => explode(',', (string) $value))
            ->map(fn ($hash) => UrlUtils::decodeId(trim($hash)))
            ->filter()
            ->unique()
            ->take(self::LIMIT);

        $back = redirect()->route('role.calendar_review', ['subdomain' => $role->subdomain, 'show' => $request->input('show'), 'q' => $request->input('q') ?: null]);

        if (! in_array($action, ['publish', 'hide'], true) || $ids->isEmpty()) {
            return $back;
        }

        $done = 0;
        $holds = CalendarImportHold::where('role_id', $role->id)->whereIn('event_id', $ids)->with('event.roles')->get();

        foreach ($holds as $hold) {
            $event = $hold->event;
            if (! $event || (int) $event->creator_role_id !== (int) $role->id) {
                continue;
            }

            if ($action === 'publish') {
                if (app(EventLifecycleService::class)->publish($event, $request->user()->id, $role) === EventLifecycleService::PUBLISHED) {
                    $done++;
                }
            } elseif ($hold->isOpen()) {
                CalendarArrival::decide($event, $request->user()->id, CalendarArrival::DECISION_HIDDEN);
                $done++;
            }
        }

        return $back->with('success', trans_choice(
            $action === 'publish' ? 'messages.calendar_review_published' : 'messages.calendar_review_kept_hidden_flash',
            $done,
            ['count' => number_format($done)]
        ));
    }

    /**
     * Publishes every event that arrived as a draft and waits for nothing but a look: not the
     * ones with meeting details or an email thread, which are published one at a time. On the
     * queue, in chunks: each publish is a push to calendars and a webhook.
     */
    public function publishAll(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $count = self::query($role, 'waiting')->where('reason', CalendarArrival::HOLD_REVIEW)->count();
        if ($count > 0) {
            PublishCalendarHolds::dispatch($role->id, $request->user()->id);
        }

        return redirect()->route('role.calendar_review', ['subdomain' => $role->subdomain])
            ->with('success', trans_choice('messages.calendar_publishing_now', $count, ['count' => number_format($count)]));
    }

    /**
     * "Keep publishing", asked once of a sync carried over from before drafts that publishes the
     * account's main calendar: the owner's own word for that calendar, as the switch gives it.
     * The owner's, since it is their calendar.
     */
    public function keepPublishing(Request $request, string $subdomain, string $provider)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        if ((int) $request->user()->id !== (int) $role->user_id || ! in_array($provider, CalendarArrival::readingProviders($role), true)) {
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])->with('error', __('messages.not_authorized'));
        }

        CalendarArrival::setPublishing($role, $provider, true);

        return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
    }

    /**
     * The holds of one list, on events the schedule owns that are still off the page.
     *
     * waiting: arrived as a draft (nothing to put back); taken: it was public; hidden: the owner
     * chose to keep it hidden.
     */
    public static function query(Role $role, string $show)
    {
        $query = CalendarImportHold::where('calendar_import_holds.role_id', $role->id)
            ->whereHas('event', fn ($q) => $q->where('creator_role_id', $role->id)->where('is_draft', true)->where('is_cancelled', false));

        return match ($show) {
            'taken' => $query->whereNull('calendar_import_holds.decided_at')->whereNotNull('calendar_import_holds.was_visibility'),
            'hidden' => $query->where('calendar_import_holds.decision', CalendarArrival::DECISION_HIDDEN),
            default => $query->whereNull('calendar_import_holds.decided_at')->whereNull('calendar_import_holds.was_visibility'),
        };
    }

    /**
     * Holds grouped into rows: one per event, and one for every occurrence of a repeated meeting
     * (same name, same reason).
     *
     * @return Collection<int, array{holds: Collection, event: Event, reason: string, ids: string, snippet: ?string}>
     */
    private static function groups(Collection $holds): Collection
    {
        return $holds
            ->groupBy(fn (CalendarImportHold $hold) => mb_strtolower(trim($hold->event->name)).'|'.$hold->reason)
            ->map(function (Collection $group) {
                $first = $group->first();
                $event = $first->event;
                $findings = CalendarPrivacy::findings($event->name, $event->description, null);
                $match = collect(CalendarPrivacy::FLAGS)->map(fn ($kind) => $findings[$kind][0] ?? null)->filter()->first();

                return [
                    'holds' => $group,
                    'event' => $event,
                    'reason' => $first->reason,
                    'ids' => $group->map(fn ($hold) => UrlUtils::encodeId($hold->event_id))->implode(','),
                    'snippet' => $match ? (string) CalendarPrivacy::highlight($match['field'] === 'name' ? $event->name : (string) $event->description, $match['match'], 140) : null,
                ];
            })
            ->values();
    }

    private function schedule(string $subdomain): Role|RedirectResponse
    {
        $role = Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        abort_unless(CalendarArrival::tablesReady(), 404);

        return $role;
    }
}
