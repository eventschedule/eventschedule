<?php

namespace App\Http\Controllers;

use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Rules\UsableTimezone;
use App\Services\AuditService;
use App\Services\EventChangeNotifier;
use App\Services\Feeds\FeedActions;
use App\Services\Feeds\FeedSetup;
use App\Utils\UrlUtils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * A schedule's feeds: the addresses it keeps reading for events.
 *
 * The list of them is a tab of the schedule (RoleController::viewAdmin, role/show-admin-feeds).
 * Everything that changes a feed is here, and is for the people who run the schedule: its owner
 * and its admins. A viewer sees the schedule and changes nothing, and the tab is not shown to
 * them either, but the check lives here because an address is not a permission.
 *
 * Adding one is two steps on one page: the address is CHECKED, which reads it once and writes
 * nothing, and what was found is shown beside the two choices that matter; then it is added.
 * The address is posted, never put in a query string: for a private calendar it is the
 * credential, and a query string is in every log between the browser and here.
 */
class EventFeedController extends Controller
{
    /** Drafts shown on a feed's page at a time. */
    private const PER_PAGE = 25;

    public function __construct(private FeedSetup $setup, private FeedActions $actions) {}

    /**
     * The schedule, for someone who runs it, or where to send them instead.
     */
    private function schedule(string $subdomain): Role|RedirectResponse
    {
        $role = Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        return $role;
    }

    private function tab(Role $role): string
    {
        return route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'feeds']);
    }

    /** Why this schedule cannot be given another feed right now, in the reader's words, or null. */
    private function cannotAdd(Role $role): ?string
    {
        return match (true) {
            ! EventFeed::allowedFor($role) => __('messages.feeds_need_enterprise'),
            is_demo_mode() => __('messages.feeds_off_in_demo'),
            $role->feeds()->count() >= EventFeed::PER_SCHEDULE => __('messages.feeds_limit', ['count' => EventFeed::PER_SCHEDULE]),
            default => null,
        };
    }

    /**
     * Hold a post on the Add page to its rules. What is refused goes back to the Add page by
     * name, never "back": the page a checked address is shown on is the answer to a POST, and
     * going back to it is a GET of an address that only takes one, which was a 404. And a field
     * that arrives as a list where text belongs is refused here, not thrown on further in.
     */
    private function heldTo(Request $request, Role $role, array $rules): void
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw (new ValidationException($validator))->redirectTo(route('role.feeds.create', ['subdomain' => $role->subdomain]));
        }
    }

    public function create(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        if ($why = $this->cannotAdd($role)) {
            return redirect($this->tab($role))->with('error', $why);
        }

        return view('feed.create', ['role' => $role, 'address' => '', 'found' => null, 'problem' => null]);
    }

    /** Read the address once and show what is there. Nothing is written. */
    public function check(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        if ($why = $this->cannotAdd($role)) {
            return redirect($this->tab($role))->with('error', $why);
        }

        $this->heldTo($request, $role, [
            'address' => 'nullable|string|max:2048',
            'source_timezone' => ['nullable', new UsableTimezone],
        ]);

        $address = trim((string) $request->input('address'));
        // Typed without its scheme, as an address usually is.
        if ($address !== '' && ! preg_match('#^[a-z][a-z0-9+.-]*://#i', $address)) {
            $address = 'https://'.$address;
        }

        $found = $this->setup->check($role, $request->user(), $address, $request->input('source_timezone'));

        return view('feed.create', [
            'role' => $role,
            'address' => $address,
            'found' => $found['ok'] ? $found : null,
            'problem' => $found['ok'] ? null : $found,
        ]);
    }

    public function store(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        if ($why = $this->cannotAdd($role)) {
            return redirect($this->tab($role))->with('error', $why);
        }

        $this->heldTo($request, $role, [
            'feed_token' => 'nullable|string',
            'name' => 'nullable|string|max:120',
            'publish_mode' => 'required|in:'.implode(',', EventFeed::PUBLISH_MODES),
            'left_action' => 'nullable|in:'.implode(',', EventFeed::LEFT_ACTIONS),
            'source_timezone' => ['nullable', new UsableTimezone],
            'group_id' => 'nullable|string',
            'category_id' => 'nullable|integer',
        ]);

        $checked = $this->setup->opened($request->input('feed_token'), $role, $request->user());

        // Too long on the page, or not a check of ours: looked at again rather than added blind.
        if (! $checked) {
            return redirect()->route('role.feeds.create', ['subdomain' => $role->subdomain])->with('error', __('messages.feeds_check_again'));
        }

        if ($role->feeds()->where('url_hash', EventFeed::hashOf($checked['url']))->exists()) {
            return redirect($this->tab($role))->with('error', __('messages.feeds_problem_already_added'));
        }

        $feed = $this->setup->add($role, $request->user(), $checked, [
            'name' => $request->input('name'),
            'publish_mode' => $request->input('publish_mode'),
            'left_action' => $request->input('left_action'),
            'group_id' => $this->groupOf($role, $request),
            'category_id' => $this->categoryOf($role, $request),
            'source_timezone' => $request->input('source_timezone'),
        ]);

        // The site and the kind, not the address: the audit log is read by more people than the feed is.
        AuditService::log(AuditService::FEED_ADD, $request->user()->id, 'Role', $role->id, null, null, 'feed:'.$feed->id.' '.$feed->host.' '.$feed->kind);

        return redirect($this->tab($role))->with('message', __($feed->publishes() ? 'messages.feeds_added_publishing' : 'messages.feeds_added_drafts'));
    }

    /**
     * One of this schedule's feeds. An id from another schedule opens nothing: the 404 does not
     * say whether such a feed exists.
     */
    private function feed(Role $role, string $hash): EventFeed
    {
        return EventFeed::where('role_id', $role->id)->findOrFail(UrlUtils::decodeId($hash));
    }

    /** Only a sub-schedule that is this schedule's own. */
    private function groupOf(Role $role, Request $request): ?int
    {
        return $role->groups()->whereKey(UrlUtils::decodeId((string) $request->input('group_id')))->value('id');
    }

    /** Only a category this schedule offers. */
    private function categoryOf(Role $role, Request $request): ?int
    {
        $id = (int) $request->input('category_id');

        return collect($role->getEventCategories())->pluck('id')->contains($id) ? $id : null;
    }

    private function page(Role $role, EventFeed $feed): string
    {
        return route('role.feeds.show', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($feed->id)]);
    }

    /** A feed's own page: what waits for somebody first, then what it has been doing. */
    public function show(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        // Counted now: a draft published or deleted from the event's own form leaves the stored
        // numbers behind until the next read.
        $feed = $this->feed($role, $hash)->recount();

        $decisions = $feed->items()
            ->where('state', EventFeedItem::STATE_DECIDE)
            ->whereNotNull('event_id')
            ->with(['event.roles', 'event.creatorRole'])
            ->orderBy('starts_at')
            ->get()
            ->filter(fn (EventFeedItem $item) => $item->event && isset($item->pending['decide']))
            ->values();

        // Soonest first: the one that happens on Saturday is the one to look at today.
        $waiting = $feed->items()
            ->where('state', EventFeedItem::STATE_IMPORTED)
            // A draft the source has since called off is not waiting to be published.
            ->whereHas('event', fn ($query) => $query->where('is_draft', true)->where('is_cancelled', false))
            ->with(['event.roles', 'event.creatorRole'])
            ->orderBy('starts_at')
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // How many signed up for each. A decision is not always about people: an event the
        // owner has worked on is held too, where the feed would otherwise remove it.
        $signedUp = $decisions->mapWithKeys(fn (EventFeedItem $item) => [
            $item->id => $item->event->sales()->count() + \App\Models\EventInterest::where('event_id', $item->event->id)->count(),
        ]);

        return view('feed.show', [
            'role' => $role,
            'feed' => $feed,
            'decisions' => $decisions,
            'signedUp' => $signedUp,
            // Whether anybody would get the email the decision offers to send: a sale with no
            // address, or a guest who has since unsubscribed, is somebody signed up and nobody
            // to write to.
            'canTell' => $decisions->mapWithKeys(fn (EventFeedItem $item) => [$item->id => EventChangeNotifier::hasAnyoneToTell($item->event)]),
            'waiting' => $waiting,
            'eventsCount' => $feed->items()->whereNotNull('event_id')->count(),
            'canUndo' => $this->actions->canUndoFirstRead($feed),
            'allowed' => EventFeed::allowedFor($role),
            'publishAtOnce' => FeedActions::PUBLISH_AT_ONCE,
        ]);
    }

    public function edit(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);

        return view('feed.edit', [
            'role' => $role,
            'feed' => $feed,
            'eventsCount' => $feed->items()->whereNotNull('event_id')->count(),
            // What a change away from "Leave it" would reach at the next read.
            'alreadyGone' => $feed->left_action === EventFeed::LEFT_KEEP ? $this->actions->alreadyGone($feed) : 0,
        ]);
    }

    public function update(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);

        $request->validate([
            'name' => 'required|string|max:120',
            'publish_mode' => 'required|in:'.implode(',', EventFeed::PUBLISH_MODES),
            'left_action' => 'nullable|in:'.implode(',', EventFeed::LEFT_ACTIONS),
            'source_timezone' => ['required', new UsableTimezone],
            'group_id' => 'nullable|string',
            'category_id' => 'nullable|integer',
        ]);

        $clockChanged = $this->actions->edit($feed, [
            'name' => $request->input('name'),
            'publish_mode' => $request->input('publish_mode'),
            'left_action' => $request->input('left_action'),
            'group_id' => $this->groupOf($role, $request),
            'category_id' => $this->categoryOf($role, $request),
            'source_timezone' => $request->input('source_timezone'),
        ]);

        AuditService::log(AuditService::FEED_UPDATE, $request->user()->id, 'Role', $role->id, null, null, 'feed:'.$feed->id.' settings');

        // "Being read again" is only said of a feed that is being read.
        $reading = $clockChanged && ! $feed->isPaused() && EventFeed::allowedFor($role);

        return redirect($this->page($role, $feed))->with('message', __($reading ? 'messages.feeds_saved_clock' : 'messages.feeds_saved'));
    }

    /** Read it on the next run. */
    public function read(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);

        if (! EventFeed::allowedFor($role)) {
            return redirect($this->page($role, $feed))->with('error', __('messages.feeds_need_enterprise'));
        }

        return redirect($this->page($role, $feed))->with(
            ...($this->actions->readNow($feed)
                ? ['message', __('messages.feeds_read_now_done')]
                : ['error', __('messages.feeds_read_now_wait')])
        );
    }

    public function pause(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);
        $this->actions->pause($feed);
        AuditService::log(AuditService::FEED_UPDATE, $request->user()->id, 'Role', $role->id, null, null, 'feed:'.$feed->id.' paused');

        return redirect($this->page($role, $feed))->with('message', __('messages.feeds_paused_done'));
    }

    public function resume(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);

        if (! EventFeed::allowedFor($role)) {
            return redirect($this->page($role, $feed))->with('error', __('messages.feeds_need_enterprise'));
        }

        $this->actions->resume($feed);
        AuditService::log(AuditService::FEED_UPDATE, $request->user()->id, 'Role', $role->id, null, null, 'feed:'.$feed->id.' resumed');

        return redirect($this->page($role, $feed))->with('message', __('messages.feeds_resumed_done'));
    }

    /** Remove the feed. Its events stay unless the form asks for the coming ones to go too. */
    public function destroy(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);
        $trace = 'feed:'.$feed->id.' '.$feed->host.' '.$feed->kind;
        $deleted = $this->actions->remove($feed, $request->user(), $request->input('its_events') === 'delete');
        AuditService::log(AuditService::FEED_REMOVE, $request->user()->id, 'Role', $role->id, null, null, $trace);

        return redirect($this->tab($role))->with('message', $deleted
            ? trans_choice('messages.feeds_removed_with', $deleted, ['count' => number_format($deleted)])
            : __('messages.feeds_removed'));
    }

    /**
     * Publish or skip drafts: the ones ticked, or the one whose own button was pressed.
     */
    public function review(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);

        // A row's own button names the row; the buttons over the list take what is ticked.
        [$action, $hashes] = match (true) {
            $request->filled('publish_one') => ['publish', [$request->input('publish_one')]],
            $request->filled('skip_one') => ['skip', [$request->input('skip_one')]],
            default => [$request->input('action'), (array) $request->input('items', [])],
        };

        $ids = array_values(array_filter(array_map(fn ($item) => is_string($item) ? UrlUtils::decodeId($item) : null, $hashes)));

        if (! in_array($action, ['publish', 'skip'], true) || ! $ids) {
            return redirect($this->page($role, $feed))->with('error', __('messages.feeds_nothing_selected'));
        }

        if ($action === 'publish') {
            $done = $this->actions->publish($feed, $role, $request->user(), $ids);

            return redirect()->back()->with('message', trans_choice('messages.feeds_published_count', $done, ['count' => number_format($done)]));
        }

        $done = $this->actions->skip($feed, $request->user(), $ids);
        $said = trans_choice('messages.feeds_skipped_count', $done['skipped'], ['count' => number_format($done['skipped'])]);

        if ($done['kept'] > 0) {
            $kept = trans_choice('messages.feeds_skipped_kept', $done['kept'], ['count' => number_format($done['kept'])]);

            // Nothing skipped at all is not a success with a footnote.
            return redirect()->back()->with(...($done['skipped'] > 0 ? ['message', $said.' '.$kept] : ['error', $kept]));
        }

        return redirect()->back()->with('message', $said);
    }

    public function publishAll(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);
        $done = $this->actions->publishAll($feed, $role, $request->user());

        // Asked of the next read, or, on a feed that is not being read, done here a page at a time.
        return redirect($this->page($role, $feed).($done['asked'] ? '' : '#waiting'))->with('message', $done['asked']
            ? trans_choice('messages.feeds_publish_all_done', $done['asked'], ['count' => number_format($done['asked'])])
            : trans_choice('messages.feeds_published_count', $done['published'], ['count' => number_format($done['published'])]));
    }

    /** The owner's answer to something the feed would not do on its own. */
    public function decide(Request $request, string $subdomain, string $hash, string $item)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);
        $item = $feed->items()->findOrFail(UrlUtils::decodeId($item));

        $request->validate([
            'answer' => 'required|in:keep,apply',
            'note' => 'nullable|string|max:280',
        ]);

        if ($request->input('answer') === 'keep') {
            $this->actions->keep($feed, $item);

            return redirect($this->page($role, $feed))->with('message', __('messages.feeds_decided_kept'));
        }

        $done = $this->actions->apply($feed, $role, $item, $request->user(), $request->boolean('notify'), $request->input('note'));

        return redirect($this->page($role, $feed))->with('message', match ($done) {
            'cancelled' => __('messages.feeds_decided_cancelled'),
            'moved' => __('messages.feeds_decided_moved'),
            default => __('messages.feeds_decided_kept'),
        });
    }

    public function undo(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $feed = $this->feed($role, $hash);

        if (! $this->actions->canUndoFirstRead($feed)) {
            return redirect($this->page($role, $feed))->with('error', __('messages.feeds_undo_too_late'));
        }

        $result = $this->actions->undoFirstRead($feed, $request->user());
        AuditService::log(AuditService::FEED_UPDATE, $request->user()->id, 'Role', $role->id, null, null, 'feed:'.$feed->id.' first read undone');

        return redirect($this->page($role, $feed))->with('message', __('messages.feeds_undone', [
            'removed' => number_format($result['removed']),
            'kept' => number_format($result['kept']),
        ]));
    }
}
