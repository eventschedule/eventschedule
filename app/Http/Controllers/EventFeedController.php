<?php

namespace App\Http\Controllers;

use App\Models\EventFeed;
use App\Models\Role;
use App\Services\AuditService;
use App\Services\Feeds\FeedSetup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
    public function __construct(private FeedSetup $setup) {}

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

        $checked = $this->setup->opened($request->input('feed_token'), $role, $request->user());

        // Too long on the page, or not a check of ours: looked at again rather than added blind.
        if (! $checked) {
            return redirect()->route('role.feeds.create', ['subdomain' => $role->subdomain])->with('error', __('messages.feeds_check_again'));
        }

        if ($role->feeds()->where('url_hash', EventFeed::hashOf($checked['url']))->exists()) {
            return redirect($this->tab($role))->with('error', __('messages.feeds_problem_already_added'));
        }

        $request->validate([
            'name' => 'nullable|string|max:120',
            'publish_mode' => 'required|in:'.implode(',', EventFeed::PUBLISH_MODES),
            'left_action' => 'nullable|in:'.implode(',', EventFeed::LEFT_ACTIONS),
            'source_timezone' => 'nullable|timezone',
        ]);

        // Only a sub-schedule and a category that are this schedule's own.
        $groupId = $role->groups()->whereKey(\App\Utils\UrlUtils::decodeId((string) $request->input('group_id')))->value('id');
        $categoryId = collect($role->getEventCategories())->pluck('id')->contains((int) $request->input('category_id')) ? (int) $request->input('category_id') : null;

        $feed = $this->setup->add($role, $request->user(), $checked, [
            'name' => $request->input('name'),
            'publish_mode' => $request->input('publish_mode'),
            'left_action' => $request->input('left_action'),
            'group_id' => $groupId,
            'category_id' => $categoryId,
            'source_timezone' => $request->input('source_timezone'),
        ]);

        // The site and the kind, not the address: the audit log is read by more people than the feed is.
        AuditService::log(AuditService::FEED_ADD, $request->user()->id, 'Role', $role->id, null, null, 'feed:'.$feed->id.' '.$feed->host.' '.$feed->kind);

        return redirect($this->tab($role))->with('message', __($feed->publishes() ? 'messages.feeds_added_publishing' : 'messages.feeds_added_drafts'));
    }
}
