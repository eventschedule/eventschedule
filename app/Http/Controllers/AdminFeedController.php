<?php

namespace App\Http\Controllers;

use App\Models\EventFeed;
use App\Services\AuditService;
use App\Services\Feeds\FeedActions;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

/**
 * Every feed on the install, for the people who run it: which schedule reads what, whether it
 * is being read, and the two things support is asked for ("try it now", "start it again").
 *
 * It shows the site a feed reads and never its address, as the owner's own pages do: for a
 * private calendar the address is the key to it, and an admin has no more need of it than a
 * member has. What a failing feed answered is a reason and a status code, never a message.
 */
class AdminFeedController extends Controller
{
    public function __construct(private FeedActions $actions) {}

    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! EventFeed::tablesReady()) {
            return redirect()->route('admin.dashboard')->with('error', __('messages.feeds_admin_not_ready'));
        }

        $query = EventFeed::query()->with('role')->whereHas('role', fn ($role) => $role->where('is_deleted', false));

        if ($search = trim((string) $request->input('search'))) {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $search).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $like)
                ->orWhere('host', 'like', $like)
                ->orWhereHas('role', fn ($role) => $role->where('name', 'like', $like)->orWhere('subdomain', 'like', $like)));
        }

        match ($request->input('state')) {
            'failing' => $query->whereNull('paused_at')->where('failure_count', '>', 0),
            'paused' => $query->whereNotNull('paused_at'),
            'waiting' => $query->whereRaw('(waiting_count + decide_count) > 0'),
            default => null,
        };

        $feeds = $query
            // What is not being read first, then what was read longest ago.
            ->orderByRaw('(paused_at IS NULL AND failure_count > 0) DESC')
            ->orderByRaw('(paused_at IS NOT NULL) DESC')
            ->orderBy('last_success_at')
            ->orderBy('id')
            ->withCount(['items as events_count' => fn ($items) => $items->whereNotNull('event_id')])
            ->paginate(20)
            ->withQueryString();

        return view('admin.feeds', [
            'feeds' => $feeds,
            'total' => EventFeed::count(),
            'failing' => EventFeed::whereNull('paused_at')->where('failure_count', '>', 0)->count(),
            'paused' => EventFeed::whereNotNull('paused_at')->count(),
            'manyFailing' => EventFeed::manyFailing(),
        ]);
    }

    /** Read it on the next run, whatever its backoff says. */
    public function read(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $feed = EventFeed::findOrFail(UrlUtils::decodeId($hash));

        return redirect()->back()->with(
            ...($this->actions->readNow($feed)
                ? ['message', __('messages.feeds_read_now_done')]
                : ['error', __($feed->isPaused() ? 'messages.feeds_admin_paused_first' : 'messages.feeds_read_now_wait')])
        );
    }

    /** Start a paused feed again. The plan is still asked when it is read. */
    public function resume(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $feed = EventFeed::findOrFail(UrlUtils::decodeId($hash));
        $this->actions->resume($feed);
        AuditService::log(AuditService::FEED_UPDATE, $request->user()->id, 'Role', $feed->role_id, null, null, 'feed:'.$feed->id.' resumed by an admin');

        return redirect()->back()->with('message', __('messages.feeds_resumed_done'));
    }
}
