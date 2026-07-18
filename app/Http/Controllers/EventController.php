<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventCommentSubmitRequest;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventParseRequest;
use App\Http\Requests\EventPartsSaveRequest;
use App\Http\Requests\EventPhotoSubmitRequest;
use App\Http\Requests\EventPollStoreRequest;
use App\Http\Requests\EventPollVoteRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Requests\EventVideoSubmitRequest;
use App\Jobs\NotifyEventCancelled;
use App\Jobs\SendQueuedEmail;
use App\Mail\EventAccepted;
use App\Mail\EventChanged;
use App\Mail\EventDeclined;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventPart;
use App\Models\EventPhoto;
use App\Models\EventPoll;
use App\Models\EventPollVote;
use App\Models\EventVideo;
use App\Models\Group;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\DeletedEventNotification;
use App\Notifications\NewRequestsNotification;
use App\Repos\EventRepo;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\DemoService;
use App\Services\EventChangeNotifier;
use App\Services\MetaAdsService;
use App\Services\OneSignalService;
use App\Services\UsageTrackingService;
use App\Services\WebhookService;
use App\Utils\ColorUtils;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function deleteImage(Request $request, $subdomain)
    {
        $event_id = UrlUtils::decodeId($request->hash);
        $event = Event::findOrFail($event_id);

        if ($request->user()->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($request->image_type == 'flyer') {
            if ($event->flyer_image_url) {
                $path = $event->getAttributes()['flyer_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);

                $event->flyer_image_url = null;
                $event->save();
            }
        }

        if ($request->image_type == 'agenda') {
            if ($event->agenda_image_url) {
                $path = $event->getAttributes()['agenda_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);

                $event->agenda_image_url = null;
                $event->save();
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => __('messages.deleted_image')]);
        }

        return redirect(route('event.edit', ['subdomain' => $subdomain, 'hash' => $request->hash]))
            ->with('message', __('messages.deleted_image'));
    }

    public function clearVideos(Request $request, $subdomain, $eventHash, $roleHash)
    {
        $event_id = UrlUtils::decodeId($eventHash);
        $role_id = UrlUtils::decodeId($roleHash);

        $event = Event::findOrFail($event_id);
        $user = $request->user();

        // Check if user can edit this event
        if ($user->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Clear YouTube videos for unclaimed roles in this event
        foreach ($event->roles as $role) {
            if (! $role->isClaimed() && $role->youtube_links && $role->id == $role_id) {
                $role->youtube_links = null;
                $role->save();
            }
        }

        return redirect()->back()->with('message', __('messages.videos_cleared'));
    }

    public function delete(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if ($request->user()->cannot('delete', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        AuditService::log(AuditService::EVENT_DELETE, $user->id, 'Event', $event->id, null, null, $event->name);

        // Cancel active boost campaigns before deletion (prevents orphaned Meta campaigns)
        $this->cancelActiveBoosts($event);

        // Capture webhook payload before deletion
        if (! $event->is_draft) {
            $webhookPayload = [
                'event' => 'event.deleted',
                'timestamp' => now()->toIso8601String(),
                'data' => $event->toApiData(),
            ];
            WebhookService::dispatch('event.deleted', $event, $webhookPayload);
        }

        $event->delete();

        /*
        $role = $event->role;
        $venue = $event->venue;

        $roleEmails = $role->members()->pluck('email')->toArray();
        $venueEmails = $venue->members()->pluck('email')->toArray();
        $emails = array_unique(array_merge($roleEmails, $venueEmails));

        Notification::route('mail', $emails)->notify(new DeletedEventNotification($event, $user));
        */

        $data = [
            'subdomain' => $subdomain,
            'tab' => 'schedule',
        ];

        return redirect(route('role.view_admin', $data))
            ->with('message', __('messages.event_deleted'));
    }

    /**
     * Soft-cancel an event: retain the row (and its sales / refund trail), mark it cancelled, stop
     * advertising it, remove it from synced calendars, and optionally notify registered attendees.
     */
    public function cancel(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event = Event::findOrFail(UrlUtils::decodeId($hash));

        if ($user->cannot('delete', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($event->is_cancelled) {
            return redirect()->back()->with('message', __('messages.event_cancelled_flash'));
        }

        AuditService::log(AuditService::EVENT_CANCEL, $user->id, 'Event', $event->id, null, null, $event->name);

        // Cancel active boost campaigns (don't keep advertising a cancelled event)
        $this->cancelActiveBoosts($event);

        $event->forceFill([
            'is_cancelled' => true,
            'cancelled_at' => now(),
            // Bump the iCal sequence so subscribed calendars pick up the cancellation (STATUS:CANCELLED).
            'ical_sequence' => (int) $event->ical_sequence + 1,
        ])->save();

        // Remove the event from organizers' / members' synced calendars
        $event->dispatchCalendarSync('delete');

        if (! $event->is_draft) {
            WebhookService::dispatch('event.cancelled', $event);
        }

        // Notify registered attendees (email + push), gated on the schedule having email settings
        if ($request->boolean('notify_attendees')
            && ! $event->is_draft
            && optional($event->getRoleWithEmailSettings())->hasEmailSettings()
            && EventChangeNotifier::hasRecipients($event)) {
            $note = $request->input('notify_message');
            NotifyEventCancelled::dispatch($event->id, $note ? Str::limit($note, 280, '') : null);
            $event->forceFill(['attendees_notified_at' => now()])->saveQuietly();
        }

        return redirect()->back()->with('message', __('messages.event_cancelled_flash'));
    }

    /**
     * Restore a previously cancelled event: clear the cancelled state, re-open sales, and re-add it to
     * synced calendars. Does not re-email attendees (avoids a second surprise blast).
     */
    public function restore(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event = Event::findOrFail(UrlUtils::decodeId($hash));

        // Restore reverses a cancellation, so it requires the same authority as cancelling (delete).
        if ($user->cannot('delete', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! $event->is_cancelled) {
            return redirect()->back();
        }

        AuditService::log(AuditService::EVENT_RESTORE, $user->id, 'Event', $event->id, null, null, $event->name);

        $event->forceFill([
            'is_cancelled' => false,
            'cancelled_at' => null,
            // Bump the iCal sequence so subscribed calendars pick up the restored event.
            'ical_sequence' => (int) $event->ical_sequence + 1,
        ])->save();

        if (! $event->is_draft) {
            $event->dispatchCalendarSync('create');
            WebhookService::dispatch('event.updated', $event);
        }

        return redirect()->back()->with('message', __('messages.event_restored'));
    }

    /**
     * Cancel and refund any active boost campaigns for an event. Shared by delete() and cancel() so a
     * cancelled or deleted event never keeps running paid ads.
     */
    private function cancelActiveBoosts(Event $event): void
    {
        $activeCampaigns = BoostCampaign::where('event_id', $event->id)
            ->whereIn('status', ['active', 'paused', 'pending_payment'])
            ->get();

        foreach ($activeCampaigns as $campaign) {
            try {
                $cancelled = \DB::transaction(function () use ($campaign) {
                    $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
                    if (! $campaign || ! $campaign->canBeCancelled()) {
                        return false;
                    }
                    $campaign->update([
                        'status' => 'cancelled',
                        'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null,
                    ]);

                    return true;
                });

                if ($cancelled) {
                    if ($campaign->meta_campaign_id) {
                        (new MetaAdsService)->deleteCampaign($campaign);
                    }

                    if (config('app.hosted') && ! config('app.is_testing')) {
                        $campaign->refresh();
                        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                            $billingService = new BoostBillingService;
                            if ($campaign->billing_status === 'pending') {
                                if ($campaign->stripe_payment_intent_id) {
                                    $billingService->cancelPaymentIntent($campaign);
                                }
                            } else {
                                $campaign->actual_spend && $campaign->actual_spend > 0
                                    ? $billingService->refundUnspent($campaign)
                                    : $billingService->refundFull($campaign);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to cancel boost campaign', [
                    'campaign_id' => $campaign->id,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Render the attendee change-notification email for the current (unsaved) form values so the
     * organizer can preview what attendees will receive before sending. Editor-only; the note is
     * length-capped and HTML-escaped in the email template.
     */
    public function notifyPreview(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event = Event::with(['roles'])->findOrFail(UrlUtils::decodeId($hash));

        if ($user->cannot('update', $event)) {
            abort(403);
        }

        // A cancelled event never sends change notifications, so there is nothing to preview.
        if ($event->is_cancelled) {
            abort(403);
        }

        $request->validate(['notify_message' => 'nullable|string|max:280', 'venue_id' => 'nullable|string']);

        $role = $event->getRoleWithEmailSettings();
        $note = $request->input('notify_message');
        $tz = $role && $role->timezone ? $role->timezone : $event->getEffectiveTimezone();

        // Build an unsaved "new" event from the submitted form values for a representative preview.
        $new = clone $event;
        if ($request->filled('name')) {
            $new->name = $request->input('name');
        }
        if ($request->filled('event_date') && $request->filled('start_time')) {
            try {
                $new->starts_at = Carbon::parse($request->input('event_date').' '.$request->input('start_time'), $tz)
                    ->utc()->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // Leave the original start time if the submitted values can't be parsed.
            }
        }
        if ($request->filled('duration')) {
            $new->duration = (float) $request->input('duration');
        }
        if ($request->has('event_url')) {
            $new->event_url = $request->input('event_url');
        }

        $oldVenueName = optional($event->venue)->getDisplayName();

        // The preview form submits the encoded venue id (like the main form) so a venue change or an
        // in-person <-> online switch is reflected in the preview. A submitted venue_name with no id
        // means a brand-new, unsaved venue, so use a non-numeric 'new:' proxy that still differs from
        // the old id (detectMaterialChanges treats venue_id as a truthy boolean and a !== operand).
        $newInPerson = $request->filled('venue_name');
        $newVenueName = $newInPerson ? $request->input('venue_name') : null;
        $newVenueId = $newInPerson
            ? ($request->filled('venue_id') ? UrlUtils::decodeId($request->input('venue_id')) : 'new:'.$newVenueName)
            : null;

        $changes = EventRepo::detectMaterialChanges([
            'starts_at' => $event->starts_at,
            'duration' => $event->duration,
            'timezone' => $event->timezone,
            'days_of_week' => $event->days_of_week,
            'event_url' => $event->event_url,
            'venue_id' => optional($event->venue)->id,
            'venue_name' => $oldVenueName,
        ], [
            'starts_at' => $new->starts_at,
            'duration' => $new->duration,
            'days_of_week' => $new->days_of_week,
            'event_url' => $new->event_url,
            'venue_id' => $newVenueId,
            'venue_name' => $newVenueName,
        ]);

        $eventUrl = $event->getGuestUrl(false, null, true);
        $icalUrl = $event->getAppleCalendarUrl();

        $mailable = new EventChanged($new, $role, $changes, $eventUrl, $note, $icalUrl, $user->name);

        return response($mailable->render());
    }

    public function create(Request $request, $subdomain)
    {
        restore_pending_action();

        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();

        $venue = $role->isVenue() ? $role : null;
        $schedule = $role->isTalent() ? $role : null;
        $curator = $role->isCurator() ? $role : null;

        if (! $role->isClaimed()) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        // Onboarding funnel stage 6 ("reached the add-event step"). First-touch stamp.
        // Base query builder + whereNull writes at most once and does not bump users.updated_at
        // (which the admin active-users metric keys off).
        DB::table('users')
            ->where('id', $user->id)
            ->whereNull('event_form_viewed_at')
            ->update(['event_form_viewed_at' => now()]);

        $event = new Event;
        $event->user_id = $user->id;
        $event->setVisibilityState($role->defaultEventVisibility());
        $selectedMembers = [];
        $clonedFlyerImage = null;
        $clonedFlyerImageUrl = null;

        // Check if we're cloning an event
        $clonedData = session('cloned_event');
        if ($clonedData) {
            // Populate event with cloned data
            foreach ($clonedData['event'] as $key => $value) {
                $event->$key = $value;
            }
            $event->user_id = $user->id;
            $event->creator_role_id = $role->id;
            // A clone starts at the schedule's default visibility (same as a fresh event),
            // rather than inheriting the source event's visibility from the cloned payload.
            $event->setVisibilityState($role->defaultEventVisibility());

            // Set cloned tickets
            $event->tickets = collect(array_map(function ($ticketData) {
                $ticket = new Ticket;
                foreach ($ticketData as $key => $value) {
                    $ticket->$key = $value;
                }

                return $ticket;
            }, $clonedData['tickets']));

            // Set cloned add-ons
            if (! empty($clonedData['addons'])) {
                $event->addons = array_map(function ($addonData) {
                    $addon = new Ticket;
                    foreach ($addonData as $key => $value) {
                        $addon->$key = $value;
                    }
                    $addon->is_addon = true;

                    return $addon;
                }, $clonedData['addons']);
            }

            // Set cloned venue
            if ($clonedData['venue_id']) {
                $venueId = UrlUtils::decodeId($clonedData['venue_id']);
                $venue = Role::find($venueId);
            } else {
                $venue = null;
            }

            // Set cloned members
            $selectedMembers = $clonedData['selected_members'] ?? [];

            // Resolve the cloned flyer for preview. The new event keeps a null
            // flyer_image_url; the raw filename rides along in clone_flyer_image and
            // is copied to a fresh file in EventRepo::saveEvent() on submit.
            $clonedFlyerImage = $clonedData['flyer_image_filename'] ?? null;
            $clonedFlyerImageUrl = $clonedFlyerImage ? $event->getFlyerImageUrlAttribute($clonedFlyerImage) : null;

            // Clear cloned data from session
            session()->forget('cloned_event');
        } else {
            // Default behavior for new event
            if ($role->default_tickets) {
                $defaultTickets = json_decode($role->default_tickets, true);
                $event->ticket_currency_code = $defaultTickets['currency_code'] ?? MoneyUtils::getCurrencyForCountry($role->country_code);
                $event->payment_method = $defaultTickets['payment_method'] ?? 'cash';
                $event->payment_instructions = $defaultTickets['payment_instructions'] ?? null;
                $event->expire_unpaid_tickets = $defaultTickets['expire_unpaid_tickets'] ?? false;
                $event->ticket_notes = $defaultTickets['ticket_notes'] ?? null;
                $event->terms_url = $defaultTickets['terms_url'] ?? null;
                $event->total_tickets_mode = $defaultTickets['total_tickets_mode'] ?? 'individual';
                $event->ask_phone = $defaultTickets['ask_phone'] ?? false;
                $event->require_phone = $defaultTickets['require_phone'] ?? false;
                $event->country_code_phone = $defaultTickets['country_code_phone'] ?? false;
                $event->individual_tickets = $defaultTickets['individual_tickets'] ?? false;
                $event->individual_ticket_fields = $defaultTickets['individual_ticket_fields'] ?? false;
                $event->sell_after_start = $defaultTickets['sell_after_start'] ?? false;
                $event->show_unavailable_tickets = $defaultTickets['show_unavailable_tickets'] ?? false;
                $event->custom_fields = $defaultTickets['custom_fields'] ?? null;
                $tickets = array_map(function ($ticketData) {
                    $ticket = new Ticket;
                    foreach ($ticketData as $key => $value) {
                        $ticket->$key = $value;
                    }

                    return $ticket;
                }, $defaultTickets['tickets'] ?? []);
                $event->tickets = collect($tickets ?: [new Ticket]);
                $event->addons = array_map(function ($addonData) {
                    $addon = new Ticket;
                    foreach ($addonData as $key => $value) {
                        $addon->$key = $value;
                    }
                    $addon->is_addon = true;

                    return $addon;
                }, $defaultTickets['addons'] ?? []);
                $defaultPromoCodes = $defaultTickets['promo_codes'] ?? [];
            } else {
                $event->ticket_currency_code = MoneyUtils::getCurrencyForCountry($role->country_code);
                $event->payment_method = 'cash';
                $event->tickets = collect([new Ticket]);
            }

            // Set default category: prefer role's default (only if still enabled),
            // fall back to last event's category.
            if ($defaultCategoryId = EventRepo::resolveDefaultCategoryId($role)) {
                $event->category_id = $defaultCategoryId;
            } else {
                $lastEvent = Event::where('user_id', $user->id)
                    ->whereNotNull('category_id')
                    ->orderBy('id', 'desc')
                    ->first();
                if ($lastEvent) {
                    $event->category_id = $lastEvent->category_id;
                }
            }

            if ($schedule) {
                $selectedMembers = [$schedule->toData()];
            }
        }

        // Pre-populate pending request talent as a member
        if (session('pending_request')) {
            $pendingRole = Role::whereSubdomain(session('pending_request'))->first();
            if ($pendingRole && $pendingRole->isTalent() && ! in_array($pendingRole->id, array_column($selectedMembers, 'id'))) {
                $selectedMembers[] = $pendingRole->toData();
            }
        }

        if ($request->date) {
            // Parse the date in the schedule's timezone and set default time to 20:00 so the
            // default matches how the time will be captured (schedule-anchored), not the editor's
            // personal account timezone.
            $defaultTz = $role->timezone ?? $user->timezone ?? config('app.timezone');
            $event->starts_at = Carbon::createFromFormat('Y-m-d', $request->date, $defaultTz)
                ->setTime(20, 0, 0)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        $roles = $user->roles()->get();

        $venues = $roles->filter(function ($item) {
            if ($item->pivot->level == 'follower' && ! $item->acceptEventRequests()) {
                return false;
            }

            return $item->isVenue();
        })->map(function ($item) {
            return $item->toData();
        });

        $members = $roles->filter(function ($item) {
            return $item->isTalent();
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->sortBy('name')->toArray());
        $members = array_values($members->sortBy('name')->toArray());

        $currencies = file_get_contents(base_path('storage/currencies.json'));
        $currencies = json_decode($currencies);

        // Prepare curator data for cloned event
        $clonedCurators = collect([]);
        $clonedCuratorGroups = [];
        if ($clonedData && isset($clonedData['curators'])) {
            foreach ($clonedData['curators'] as $curatorId) {
                $curatorIdDecoded = UrlUtils::decodeId($curatorId);
                $curator = Role::find($curatorIdDecoded);
                if ($curator) {
                    $clonedCurators->push($curator);
                    if (isset($clonedData['curator_groups'][$curatorId])) {
                        $clonedCuratorGroups[$curatorId] = $clonedData['curator_groups'][$curatorId];
                    }
                }
            }
        }

        // Check if this is a cloned event
        $isCloned = $clonedData !== null;

        // Get cloned parts if available
        $clonedParts = ($clonedData && isset($clonedData['parts'])) ? $clonedData['parts'] : [];

        return view('event/edit', [
            'role' => $role,
            'effectiveRole' => $role,
            'user' => $user,
            'roles' => $roles,
            'event' => $event,
            'subdomain' => $subdomain,
            'title' => __('messages.add_event'),
            'selectedVenue' => $venue,
            'venues' => $venues,
            'selectedMembers' => $selectedMembers,
            'members' => $members,
            'currencies' => $currencies,
            'event_categories' => get_translated_categories($role ?? null),
            'clonedCurators' => $clonedCurators,
            'clonedCuratorGroups' => $clonedCuratorGroups,
            'isCloned' => $isCloned,
            'clonedParts' => $clonedParts,
            'clonedFlyerImage' => $clonedFlyerImage,
            'clonedFlyerImageUrl' => $clonedFlyerImageUrl,
            'polls' => collect(),
            'defaultPromoCodes' => $defaultPromoCodes ?? [],
        ]);
    }

    public function editAdmin(Request $request, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);
        $user = $request->user();
        $subdomain = null;

        foreach ($event->roles as $each) {
            if ($user->isEditor($each->subdomain)) {
                $subdomain = $each->subdomain;
                break;
            }
        }

        if (! $subdomain) {
            abort(403);
        }

        return redirect(route('event.edit', ['subdomain' => $subdomain, 'hash' => $hash]));
    }

    public function clone(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['tickets', 'addons', 'roles', 'creatorRole', 'curators', 'parts'])->findOrFail($event_id);
        $user = $request->user();

        if ($user->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isClaimed()) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        // Build the clone payload (shared with Event Templates) and stash it for the
        // create form to consume.
        session(['cloned_event' => EventRepo::buildClonePayload($event)]);

        return redirect(route('event.create', ['subdomain' => $subdomain]));
    }

    public function edit(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators', 'parts', 'promoCodes', 'addons'])->findOrFail($event_id);
        $user = $request->user();

        if ($user->cannot('update', $event)) {
            return redirect()->back();
        }

        if ($event->tickets->count() == 0) {
            $event->tickets = collect([new Ticket]);
        }

        // Use the creator role's subdomain to determine the correct groups/sub-schedules
        $creatorRole = $event->creatorRole;
        $effectiveSubdomain = $creatorRole ? $creatorRole->subdomain : $subdomain;
        $effectiveRole = Role::subdomain($effectiveSubdomain)->firstOrFail();

        $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $event->venue;
        $selectedMembers = [];
        foreach ($event->roles as $each) {
            if ($each->isTalent()) {
                $selectedMembers[] = $each->toData();
            }
        }

        if (! $role->isClaimed()) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        $title = __('messages.edit_event');

        $roles = $user->roles()->get();

        $venues = $roles->filter(function ($item) {
            if ($item->pivot->level == 'follower' && ! $item->acceptEventRequests()) {
                return false;
            }

            return $item->isVenue();
        })->map(function ($item) {
            return $item->toData();
        });

        $members = $roles->filter(function ($item) {
            return $item->isTalent();
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->sortBy('name')->toArray());
        $members = array_values($members->sortBy('name')->toArray());

        $currencies = file_get_contents(base_path('storage/currencies.json'));
        $currencies = json_decode($currencies);

        $pendingVideos = $event->exists ? $event->pendingVideos()->with(['eventPart', 'user'])->get() : collect();
        $approvedVideos = $event->exists ? $event->approvedVideos()->with(['eventPart', 'user'])->get() : collect();
        $pendingComments = $event->exists ? $event->pendingComments()->with(['eventPart', 'user'])->get() : collect();
        $approvedComments = $event->exists ? $event->approvedComments()->with(['eventPart', 'user'])->get() : collect();
        $pendingPhotos = $event->exists ? $event->pendingPhotos()->with(['eventPart', 'user'])->get() : collect();
        $approvedPhotos = $event->exists ? $event->approvedPhotos()->with(['eventPart', 'user'])->get() : collect();
        $polls = $event->exists ? $event->polls()->withCount('votes')->get() : collect();

        return view('event/edit', [
            'role' => $role,
            'effectiveRole' => $effectiveRole,
            'user' => $user,
            'roles' => $roles,
            'event' => $event,
            'subdomain' => $subdomain,
            'title' => $title,
            'selectedVenue' => $venue,
            'venues' => $venues,
            'selectedMembers' => $selectedMembers,
            'members' => $members,
            'currencies' => $currencies,
            'event_categories' => get_translated_categories($effectiveRole ?? $role),
            'pendingVideos' => $pendingVideos,
            'approvedVideos' => $approvedVideos,
            'pendingComments' => $pendingComments,
            'approvedComments' => $approvedComments,
            'pendingPhotos' => $pendingPhotos,
            'approvedPhotos' => $approvedPhotos,
            'polls' => $polls,
            // Attendee change-notification UX (issue #94): the confirm dialog only appears when the
            // event has registrants and the schedule can actually send email.
            'registrantCount' => EventChangeNotifier::recipientCount($event),
            'scheduleHasEmailSettings' => (bool) optional($event->getRoleWithEmailSettings())->hasEmailSettings(),
            'attendeesNotifiedAt' => optional($event->attendees_notified_at)->toIso8601String(),
        ]);
    }

    public function update(EventUpdateRequest $request, $subdomain, $hash)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);

        if ($request->user()->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->eventRepo->saveEvent($role, $request, $event);

        // Sync polls from form data
        if ($role->isPro() && $request->has('polls')) {
            $maxSort = $event->polls()->max('sort_order') ?? 0;
            foreach (array_slice($request->input('polls', []), 0, 5) as $pollData) {
                $question = trim($pollData['question'] ?? '');
                $options = json_decode($pollData['options'] ?? '[]', true);
                $options = array_values(array_filter(array_map('trim', $options ?: [])));
                $hash = $pollData['hash'] ?? '';

                if (! $question || count($options) < 2) {
                    continue;
                }

                $allowUserOptions = ! empty($pollData['allow_user_options']) && $pollData['allow_user_options'] !== '0';
                $requireOptionApproval = ! empty($pollData['require_option_approval']) && $pollData['require_option_approval'] !== '0';

                if ($hash) {
                    $poll = EventPoll::where('event_id', $event->id)
                        ->find(UrlUtils::decodeId($hash));
                    if ($poll) {
                        $data = [
                            'question' => Str::limit($question, 500),
                            'allow_user_options' => $allowUserOptions,
                            'require_option_approval' => $requireOptionApproval,
                        ];
                        if (! $poll->votes()->exists()) {
                            $data['options'] = array_slice($options, 0, 10);
                        }
                        $poll->update($data);
                    }
                } else {
                    EventPoll::create([
                        'event_id' => $event->id,
                        'question' => Str::limit($question, 500),
                        'options' => array_slice($options, 0, 10),
                        'sort_order' => ++$maxSort,
                        'allow_user_options' => $allowUserOptions,
                        'require_option_approval' => $requireOptionApproval,
                    ]);
                }
            }
        }

        if ($request->has('save_default_tickets')) {
            $role = Role::subdomain($subdomain)->firstOrFail();
            $event->load(['promoCodes', 'addons']);
            $defaultTickets = [
                'currency_code' => $event->ticket_currency_code,
                'payment_method' => $event->payment_method,
                'payment_instructions' => $event->payment_instructions,
                'expire_unpaid_tickets' => $event->expire_unpaid_tickets,
                'ticket_notes' => $event->ticket_notes,
                'terms_url' => $event->terms_url,
                'total_tickets_mode' => $event->total_tickets_mode,
                'ask_phone' => $event->ask_phone,
                'require_phone' => $event->require_phone,
                'country_code_phone' => $event->country_code_phone,
                'individual_tickets' => $event->individual_tickets,
                'individual_ticket_fields' => $event->individual_ticket_fields,
                'sell_after_start' => $event->sell_after_start,
                'show_unavailable_tickets' => $event->show_unavailable_tickets,
                'custom_fields' => $event->custom_fields,
                'tickets' => $event->tickets->map(fn ($ticket) => $ticket->toClonePayload())->toArray(),
                'addons' => $event->addons->map(function ($addon) {
                    return [
                        'type' => $addon->type,
                        'quantity' => $addon->quantity,
                        'price' => $addon->price,
                        'description' => $addon->description,
                    ];
                })->toArray(),
                'promo_codes' => $event->promoCodes->map(function ($pc) {
                    return [
                        'code' => $pc->code,
                        'type' => $pc->type,
                        'value' => $pc->value,
                        'max_uses' => $pc->max_uses,
                        'is_active' => $pc->is_active,
                        'ticket_ids' => $pc->ticket_ids,
                    ];
                })->toArray(),
            ];
            $role->default_tickets = json_encode($defaultTickets);
            $role->save();
        }

        if ($request->has('agenda_ai_prompt')) {
            $event->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $event->save();
        }
        $role->agenda_show_times = $request->boolean('agenda_show_times');
        $role->agenda_show_description = $request->boolean('agenda_show_description');
        $role->agenda_save_image = $request->boolean('save_agenda_image');
        $role->save();

        if ($request->input('save_ai_prompt_default')) {
            $role->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $role->save();
        }

        if ($event->starts_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        } else {
            $date = Carbon::now();
        }

        // A user may be using a different subdomain to edit an event
        // if they clicked on the edit link from the guest view
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect(route('home'));
        }

        $data = [
            'subdomain' => $subdomain,
            'tab' => 'schedule',
            'month' => $date->month,
            'year' => $date->year,
        ];

        // Tell the organizer whether attendees were emailed about the change.
        $message = $this->eventRepo->lastNotifiedCount
            ? __('messages.attendees_notified', ['count' => $this->eventRepo->lastNotifiedCount])
            : ($request->boolean('notify_attendees')
                ? __('messages.saved_without_notifying')
                : __('messages.event_updated'));

        return redirect(route('role.view_admin', $data))
            ->with('message', $message);
    }

    public function accept(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($user->isEditor($subdomain)) {
            $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
            $role->last_notified_request_count = 0;
            $role->save();
        }

        // Send email to the user who submitted the event
        // Only send if they have an email and aren't a member of the accepting role
        // (Guest submissions without accounts have user_id set to venue's user)
        if ($event->user && $event->user->email && ! is_demo_mode()) {
            if (! $event->user->isMember($subdomain)) {
                SendQueuedEmail::dispatch(
                    new EventAccepted($event, $role),
                    $event->user->email,
                    null,
                    $event->user->language_code
                );

                OneSignalService::pushToUser($event->user, [
                    'title_key' => 'messages.push_event_accepted_title',
                    'body_key' => 'messages.push_event_accepted_body',
                    'body_params' => ['event' => $event->name],
                    'url' => $event->getGuestUrl(false, null, true),
                    'options' => ['icon' => $role->profile_image_url],
                ], $role);
            }
        }

        AuditService::log(AuditService::EVENT_ACCEPT, $user->id, 'Event', $event->id, null, null, $role->name);

        return redirect('/'.$subdomain.'/schedule')
            ->with('message', __('messages.request_accepted'));
    }

    public function decline(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($user->isEditor($subdomain)) {
            $event->roles()->updateExistingPivot($role->id, ['is_accepted' => false]);
            $role->last_notified_request_count = 0;
            $role->save();
        }

        // Send email to the user who submitted the event
        // Only send if they have an email and aren't a member of the accepting role
        // (Guest submissions without accounts have user_id set to venue's user)
        if ($event->user && $event->user->email && ! is_demo_mode()) {
            if (! $event->user->isMember($subdomain)) {
                SendQueuedEmail::dispatch(
                    new EventDeclined($event, $role),
                    $event->user->email,
                    null,
                    $event->user->language_code
                );

                OneSignalService::pushToUser($event->user, [
                    'title_key' => 'messages.push_event_declined_title',
                    'body_key' => 'messages.push_event_declined_body',
                    'body_params' => ['event' => $event->name],
                    'url' => $event->getGuestUrl(false, null, true),
                    'options' => ['icon' => $role->profile_image_url],
                ], $role);
            }
        }

        AuditService::log(AuditService::EVENT_DECLINE, $user->id, 'Event', $event->id, null, null, $role->name);

        if ($request->redirect_to == 'schedule') {
            return redirect('/'.$subdomain.'/schedule')
                ->with('message', __('messages.request_declined'));
        } else {
            return redirect('/'.$subdomain.'/requests')
                ->with('message', __('messages.request_declined'));
        }
    }

    public function publish(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with('roles')->findOrFail($event_id);

        if ($user->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Internal events are intentionally never public, so the quick-publish route must not flip one
        // (the UI never offers Publish for them; this also blocks crafted requests). Publishing means
        // "make fully public", so normalize the whole visibility state via the shared helper.
        if (! $event->is_draft || $event->is_internal) {
            return redirect()->back();
        }

        $event->setVisibilityState('public');
        $event->save();

        // Trigger calendar sync now that the event is published
        $role = Role::subdomain($subdomain)->firstOrFail();
        if ($role->syncsToGoogle()) {
            $event->syncToGoogleCalendar('create');
        }
        if ($role->syncsToMicrosoft()) {
            $event->syncToMicrosoftCalendar('create');
        }
        if ($role->syncsToCalDAV()) {
            $event->syncToCalDAV('create');
        }

        // Dispatch webhook
        WebhookService::dispatch('event.created', $event);

        AuditService::log(AuditService::EVENT_PUBLISH, $user->id, 'Event', $event->id, null, null, $event->name);

        return redirect()->back()->with('message', __('messages.event_published'));
    }

    public function acceptAll(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();

        // Get all pending events for this role
        $pendingEvents = Event::with(['creatorRole'])
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)
                    ->whereNull('is_accepted');
            })
            ->get();

        $acceptedCount = 0;

        foreach ($pendingEvents as $event) {
            if ($user->isEditor($subdomain)) {
                $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
                $acceptedCount++;

                // Send email to the user who submitted the event
                // Only send if they have an email and aren't a member of the accepting role
                // (Guest submissions without accounts have user_id set to venue's user)
                if ($event->user && $event->user->email && ! is_demo_mode()) {
                    if (! $event->user->isMember($subdomain)) {
                        SendQueuedEmail::dispatch(
                            new EventAccepted($event, $role),
                            $event->user->email,
                            null,
                            $event->user->language_code
                        );

                        OneSignalService::pushToUser($event->user, [
                            'title_key' => 'messages.push_event_accepted_title',
                            'body_key' => 'messages.push_event_accepted_body',
                            'body_params' => ['event' => $event->name],
                            'url' => $event->getGuestUrl(false, null, true),
                            'options' => ['icon' => $role->profile_image_url],
                        ], $role);
                    }
                }
            }
        }

        $role->last_notified_request_count = 0;
        $role->save();

        return redirect('/'.$subdomain.'/requests')
            ->with('message', __('messages.all_requests_accepted', ['count' => $acceptedCount]));
    }

    public function createDefault()
    {
        $user = auth()->user();

        if ($user->default_role_id) {
            $role = Role::where('id', $user->default_role_id)->where('is_deleted', false)->first();
            if ($role && $user->isEditor($role->subdomain)) {
                return redirect()->route('event.create', ['subdomain' => $role->subdomain]);
            }
        }

        // Fall back to single editor role
        $editorRoles = $user->editor()->get();
        if ($editorRoles->count() === 1) {
            return redirect()->route('event.create', ['subdomain' => $editorRoles->first()->subdomain]);
        }

        return redirect()->route('home')->with('error', __('messages.set_default_schedule'));
    }

    public function store(EventCreateRequest $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $event = $this->eventRepo->saveEvent($role, $request, null);

        // Create polls from form data
        if ($role->isPro() && $request->has('polls')) {
            $sortOrder = 0;
            foreach (array_slice($request->input('polls', []), 0, 5) as $pollData) {
                $question = trim($pollData['question'] ?? '');
                $options = json_decode($pollData['options'] ?? '[]', true);
                $options = array_values(array_filter(array_map('trim', $options ?: [])));
                if ($question && count($options) >= 2) {
                    EventPoll::create([
                        'event_id' => $event->id,
                        'question' => Str::limit($question, 500),
                        'options' => array_slice($options, 0, 10),
                        'sort_order' => $sortOrder++,
                        'allow_user_options' => ! empty($pollData['allow_user_options']) && $pollData['allow_user_options'] !== '0',
                        'require_option_approval' => ! empty($pollData['require_option_approval']) && $pollData['require_option_approval'] !== '0',
                    ]);
                }
            }
        }

        if ($request->has('save_default_tickets')) {
            $role = Role::subdomain($subdomain)->firstOrFail();
            $event->load(['promoCodes', 'addons']);
            $defaultTickets = [
                'currency_code' => $event->ticket_currency_code,
                'payment_method' => $event->payment_method,
                'payment_instructions' => $event->payment_instructions,
                'expire_unpaid_tickets' => $event->expire_unpaid_tickets,
                'ticket_notes' => $event->ticket_notes,
                'terms_url' => $event->terms_url,
                'total_tickets_mode' => $event->total_tickets_mode,
                'ask_phone' => $event->ask_phone,
                'require_phone' => $event->require_phone,
                'country_code_phone' => $event->country_code_phone,
                'individual_tickets' => $event->individual_tickets,
                'individual_ticket_fields' => $event->individual_ticket_fields,
                'sell_after_start' => $event->sell_after_start,
                'show_unavailable_tickets' => $event->show_unavailable_tickets,
                'custom_fields' => $event->custom_fields,
                'tickets' => $event->tickets->map(fn ($ticket) => $ticket->toClonePayload())->toArray(),
                'addons' => $event->addons->map(function ($addon) {
                    return [
                        'type' => $addon->type,
                        'quantity' => $addon->quantity,
                        'price' => $addon->price,
                        'description' => $addon->description,
                    ];
                })->toArray(),
                'promo_codes' => $event->promoCodes->map(function ($pc) {
                    return [
                        'code' => $pc->code,
                        'type' => $pc->type,
                        'value' => $pc->value,
                        'max_uses' => $pc->max_uses,
                        'is_active' => $pc->is_active,
                        'ticket_ids' => $pc->ticket_ids,
                    ];
                })->toArray(),
            ];
            $role->default_tickets = json_encode($defaultTickets);
            $role->save();
        }

        if ($request->input('agenda_ai_prompt')) {
            $event->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $event->save();
        }
        if ($request->input('agenda_image_url')) {
            $agendaUrl = $request->input('agenda_image_url');
            if (preg_match('/^agenda_[a-z0-9]+\.(jpg|jpeg|png|gif|webp)$/', $agendaUrl)) {
                $event->agenda_image_url = $agendaUrl;
                $event->save();
            }
        }
        $role->agenda_show_times = $request->boolean('agenda_show_times');
        $role->agenda_show_description = $request->boolean('agenda_show_description');
        $role->agenda_save_image = $request->boolean('save_agenda_image');
        $role->save();

        if ($request->input('save_ai_prompt_default')) {
            $role->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $role->save();
        }

        $role->autoCurateEvent($event);

        session()->forget('pending_request');
        session()->forget('pending_request_allow_guest');
        session()->forget('pending_request_form');

        if ($event->starts_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        } else {
            $date = Carbon::now();
        }

        $data = [
            'subdomain' => $subdomain,
            'tab' => 'schedule',
            'month' => $date->month,
            'year' => $date->year,
        ];

        return redirect(route('role.view_admin', $data))
            ->with('message', __('messages.event_created'));
    }

    public function curate(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($event->is_draft) {
            $user = auth()->user();
            $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
            if (! $isMemberOrAdmin) {
                abort(404);
            }
        }

        if ($event->is_private) {
            $user = auth()->user();
            $isEventMember = $event->roles->contains(fn ($r) => $user && $user->isMember($r->subdomain));
            if (! $isEventMember && ! $user?->isAdmin()) {
                abort(404);
            }

            // Don't allow curating private events to other schedules
            if (! $event->roles->contains('id', $role->id)) {
                abort(404);
            }
        }

        // Check if the user is authorized to curate events for this role
        if ((! auth()->user() || ! auth()->user()->isEditor($subdomain)) && ! $role->acceptEventRequests()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.not_authorized'),
                ], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Check if the event is already curated by this role
        if ($role->events()->where('event_id', $event->id)->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.event_already_curated'),
                ], 400);
            }

            return redirect()->back()->with('error', __('messages.event_already_curated'));
        }

        // Add the event to the curator's schedule
        $role->events()->attach($event->id, ['is_accepted' => auth()->user() && auth()->user()->isEditor($subdomain) ? true : null]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.event_added_to_schedule'),
                'event_url' => $event->getGuestUrl($subdomain),
            ]);
        }

        return back()->with('message', __('messages.event_added_to_schedule'));
    }

    public function uncurate(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! auth()->user()->isEditor($subdomain)) {
            return back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->events()->detach($event->id);

        return back()->with('message', __('messages.uncurate_event'));
    }

    public function showImportHub(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            abort(403, __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        return view('event.import-hub', [
            'role' => $role,
        ]);
    }

    public function showImport(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            abort(403, __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $venues = [];

        if ($user = $request->user()) {
            $roles = $user->roles()->get();
            $venueRoles = $roles->filter(function ($item) {
                if ($item->pivot->level == 'follower' && ! $item->acceptEventRequests()) {
                    return false;
                }

                return $item->isVenue();
            });

            // Venues connected to this schedule through past events (e.g. created by a
            // previous AI import, by another admin of the schedule, or via calendar sync
            // under another user) have no role_user pivot for this user but are still
            // "existing" venues to the curator. Skipped for venue schedules: the AI
            // parser pins events to the venue itself there. Claimed venues that do not
            // accept requests are excluded, mirroring the pivot-list rule above.
            $connectedVenues = collect();
            if (! $role->isVenue()) {
                $connectedIds = $role->connectedRoleIds('venue')->diff($venueRoles->pluck('id'));
                if ($connectedIds->isNotEmpty()) {
                    $connectedVenues = Role::whereIn('id', $connectedIds)
                        ->get()
                        ->filter(fn ($item) => $item->acceptEventRequests())
                        ->values();
                }
            }

            // Sort venues by most-recently-used in this curator's events first,
            // then alphabetically. The recently-picked venue is far more likely
            // to be the right choice than the alphabetical first.
            $venueIds = $venueRoles->pluck('id')->merge($connectedVenues->pluck('id'));
            $latestEventByVenue = [];
            if ($venueIds->isNotEmpty()) {
                $latestEventByVenue = \DB::table('event_role')
                    ->join('events', 'events.id', '=', 'event_role.event_id')
                    ->whereIn('event_role.role_id', $venueIds)
                    ->groupBy('event_role.role_id')
                    ->selectRaw('event_role.role_id as id, MAX(events.starts_at) as latest_at')
                    ->pluck('latest_at', 'id')
                    ->all();
            }

            $allVenueRoles = $venueRoles->concat($connectedVenues)->sortBy([
                fn ($a, $b) => strcmp($latestEventByVenue[$b->id] ?? '', $latestEventByVenue[$a->id] ?? ''),
                fn ($a, $b) => strcasecmp($a->name ?? '', $b->name ?? ''),
            ]);

            // Only the fields the venue dropdown consumes. Never the full toData(): connected
            // venues have no relationship to this user, so their contact email, phone, and
            // billing columns must not be serialized into the page.
            $venues = array_values($allVenueRoles->map(function ($item) {
                return [
                    'id' => UrlUtils::encodeId($item->id),
                    'name' => $item->name,
                    'address1' => $item->address1,
                    'city' => $item->city,
                    'is_member' => $item->pivot && in_array($item->pivot->level, ['owner', 'admin', 'viewer'], true),
                    'is_connected' => $item->pivot === null,
                ];
            })->toArray());
        }

        $currencies = json_decode(file_get_contents(base_path('storage/currencies.json')));

        return view('event.admin-import', [
            'role' => $role,
            'venues' => $venues,
            'currencies' => $currencies,
            'defaultCurrency' => MoneyUtils::getCurrencyForCountry($role->country_code),
        ]);
    }

    public function showGuestImport(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        // Query params can arrive as arrays (?lang[]=en); is_valid_language_code() is typed
        // ?string, so only honor a string value.
        $lang = is_string($request->lang) ? $request->lang : null;

        // Require-account curators use the structured single-page submission (event.guest_submit),
        // not this AI-import page; bounce them through the request router, which routes there
        // (redirecting to the canonical subdomain on custom domains). This AI page renders below
        // only for curators that do not require an account. Forward a valid ?lang= so shared
        // localized links keep their language across the hop.
        if ($role->require_account) {
            return redirect(route('role.request', array_filter([
                'subdomain' => $subdomain,
                'lang' => $lang && is_valid_language_code($lang) ? $lang : null,
            ])));
        }

        if (! auth()->check()) {
            session()->put('pending_request', $subdomain);
        }

        // Store guest language for auth flow
        if (session()->has('translate')) {
            session()->put('guest_language', $role->translation_language_code);
        } elseif ($lang && is_valid_language_code($lang)) {
            session()->put('guest_language', $lang);
        } elseif (is_valid_language_code($role->language_code)) {
            session()->put('guest_language', $role->language_code);
        }

        if ($lang) {
            // Validate the language code before setting it
            if (is_valid_language_code($lang)) {
                app()->setLocale($lang);

                if ($lang == $role->translation_language_code && $lang != $role->language_code) {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                // If invalid language code, redirect to the same URL without the lang parameter
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale($role->translation_language_code);
        } else {
            // Validate the language code from database before setting it
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }
        }

        $currencies = json_decode(file_get_contents(base_path('storage/currencies.json')));

        return view('event.guest-import', ['role' => $role, 'isGuest' => true, 'venues' => [], 'currencies' => $currencies, 'defaultCurrency' => MoneyUtils::getCurrencyForCountry($role->country_code)]);
    }

    /**
     * Structured single-page submission form for curators that require an account: explicit
     * event fields + an inline account section (email/password/verification code, or login for
     * an existing account) + a schedule name, all on one page. Posts to the same
     * event.guest_import.store endpoint as the AI-import single page.
     */
    public function showGuestSubmit(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        // Not accepting submissions → this page doesn't exist. Abort rather than redirect:
        // request() routes require-account curators here without checking acceptEventRequests(),
        // so redirecting back to it would infinite-loop.
        if (! $role->acceptEventRequests()) {
            abort(404);
        }

        // Non-account curators use a different form; hand back to the request router (which won't
        // route them here, so no loop).
        if (! $role->require_account) {
            return redirect(route('role.request', ['subdomain' => $subdomain]));
        }

        // Booking-form curators collect submissions through the booking form's vetting questions;
        // request() routes them down the bridged path (never here), so bounce direct URL hits back
        // to the router instead of offering a form that skips their required questions.
        if ($role->usesBookingForm()) {
            return redirect(route('role.request', ['subdomain' => $subdomain]));
        }

        // On a custom domain, bounce through the request router, which redirects to this page on
        // the canonical {subdomain}.eventschedule.com so the account+event flow and the post-submit
        // dashboard share the .eventschedule.com cookie (a custom-domain login can't set a cookie
        // the app subdomain reads).
        if ($request->attributes->get('custom_domain_host')) {
            return redirect(route('role.request', ['subdomain' => $subdomain]));
        }

        if (! auth()->check()) {
            session()->put('pending_request', $subdomain);
        }

        // Query params can arrive as arrays (?lang[]=en); is_valid_language_code() is typed
        // ?string, so only honor a string value.
        $lang = is_string($request->lang) ? $request->lang : null;

        // Store guest language for auth flow
        if (session()->has('translate')) {
            session()->put('guest_language', $role->translation_language_code);
        } elseif ($lang && is_valid_language_code($lang)) {
            session()->put('guest_language', $lang);
        } elseif (is_valid_language_code($role->language_code)) {
            session()->put('guest_language', $role->language_code);
        }

        if ($lang) {
            // Validate the language code before setting it
            if (is_valid_language_code($lang)) {
                app()->setLocale($lang);

                if ($lang == $role->translation_language_code && $lang != $role->language_code) {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                // If invalid language code, redirect to the same URL without the lang parameter
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale($role->translation_language_code);
        } else {
            // Validate the language code from database before setting it
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }
        }

        $currencies = json_decode(file_get_contents(base_path('storage/currencies.json')));

        return view('event.guest-submit', ['role' => $role, 'isGuest' => true, 'requireAccount' => true, 'venues' => [], 'currencies' => $currencies, 'defaultCurrency' => MoneyUtils::getCurrencyForCountry($role->country_code)]);
    }

    public function parse(EventParseRequest $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $details = request()->input('event_details');
        $file = null;

        // Handle image data if provided
        if ($request->hasFile('details_image')) {
            $file = $request->file('details_image');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->canMakeAiParseRequest()) {
            return response()->json(['error' => __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiParseDailyLimit()])], 422);
        }

        try {
            $parsed = GeminiUtils::parseEvent($role, $details, $file);

            return response()->json($parsed);
        } catch (\Exception $e) {
            // Log the full error server-side but return generic message to user
            \Log::error('Event parsing failed: '.$e->getMessage(), [
                'role_id' => $role->id,
            ]);

            return response()->json(['error' => __('messages.event_parsing_failed')], 500);
        }
    }

    public function parseEventParts(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $role->canMakeAiAgendaRequest()) {
            return response()->json(['error' => __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiAgendaDailyLimit()])], 422);
        }

        $request->validate([
            'ai_prompt' => 'nullable|string|max:500',
        ]);

        $imageData = null;
        $textDescription = $request->input('parts_text');
        $aiPrompt = $request->input('ai_prompt');

        if ($request->hasFile('parts_image')) {
            $file = $request->file('parts_image');
            ImageUtils::validateUploadedFile($file);
            $imageData = file_get_contents($file->getRealPath());
        }

        if (! $imageData && ! $textDescription) {
            return response()->json(['error' => __('messages.provide_image_or_text')], 422);
        }

        try {

            if ($request->input('save_ai_prompt_default')) {
                $role->agenda_ai_prompt = $aiPrompt;
                $role->agenda_save_image = $request->boolean('save_agenda_image');
                $role->save();
            }

            if ($request->input('event_id')) {
                $event = Event::findOrFail(UrlUtils::decodeId($request->input('event_id')));

                if ($request->user()->cannot('update', $event)) {
                    return response()->json(['error' => __('messages.not_authorized')], 403);
                }

                $event->agenda_ai_prompt = $aiPrompt;
                $event->save();
            }

            $parts = GeminiUtils::parseEventParts($imageData, $textDescription, $aiPrompt, $role->id);

            $agendaImageUrl = null;
            $agendaImageFullUrl = null;

            if ($request->input('save_agenda_image') && $imageData && $request->hasFile('parts_image')) {
                $file = $request->file('parts_image');
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (! in_array($extension, $allowedExtensions)) {
                    return response()->json(['error' => __('messages.invalid_image')], 422);
                }
                $filename = strtolower('agenda_'.Str::random(32).'.'.$extension);

                if (isset($event)) {
                    // Delete old agenda image
                    $oldPath = $event->getAttributes()['agenda_image_url'];
                    if ($oldPath) {
                        $deletePath = $oldPath;
                        if (config('filesystems.default') == 'local') {
                            $deletePath = 'public/'.$deletePath;
                        }
                        Storage::delete($deletePath);
                    }
                }

                $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);
                $agendaImageUrl = $filename;

                if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                    $agendaImageFullUrl = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                    $agendaImageFullUrl = url('/storage/'.$filename);
                } else {
                    $agendaImageFullUrl = $filename;
                }

                if (isset($event)) {
                    $event->agenda_image_url = $filename;
                    $event->save();
                }
            }

            return response()->json([
                'parts' => $parts,
                'agenda_image_url' => $agendaImageUrl,
                'agenda_image_full_url' => $agendaImageFullUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('Event parts parsing failed: '.$e->getMessage());

            return response()->json(['error' => __('messages.event_parsing_failed')], 500);
        }
    }

    public function generateFlyer(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $role->canGenerateAiImage()) {
            return response()->json(['error' => __('messages.ai_daily_limit_reached', ['limit' => $role->aiImageDailyLimit()])], 422);
        }

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! config('services.openai.api_key') && ! config('services.google.gemini_key')) {
            return response()->json(['error' => __('messages.openai_key_required')], 422);
        }

        $request->validate([
            'style_instructions' => 'nullable|string|max:500',
            'custom_prompt' => 'nullable|string|max:5000',
            'name' => 'nullable|string',
            'starts_at' => 'nullable|string',
            'duration' => 'nullable|numeric',
            'short_description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'venue_name' => 'nullable|string',
            'venue_address1' => 'nullable|string',
            'venue_city' => 'nullable|string',
        ]);

        $eventId = UrlUtils::decodeId($request->input('event_id'));

        if ($eventId) {
            $event = Event::with(['roles', 'tickets'])->findOrFail($eventId);

            if ($request->user()->cannot('update', $event)) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
                return response()->json(['error' => __('messages.not_authorized')], 404);
            }
        } else {
            $event = new Event;
            $event->name = $request->input('name');
            $event->starts_at = $request->input('starts_at');
            $event->duration = $request->input('duration');
            $event->short_description = $request->input('short_description');
            $event->category_id = $request->input('category_id');
            $event->setRelation('roles', collect([]));
            $event->setRelation('tickets', collect([]));
        }

        if ($request->input('venue_name') || $request->input('venue_address1')) {
            $venueRole = new Role;
            $venueRole->type = 'venue';
            $venueRole->name = $request->input('venue_name', '');
            $venueRole->address1 = $request->input('venue_address1', '');
            $venueRole->city = $request->input('venue_city', '');
            $roles = $event->relationLoaded('roles') ? $event->roles->filter(fn ($r) => ! $r->isVenue()) : collect([]);
            $event->setRelation('roles', $roles->push($venueRole));
        }

        $requestId = Str::uuid()->toString();
        Cache::put("ai_flyer_{$requestId}", ['status' => 'processing'], 300);

        $styleInstructions = $request->input('style_instructions');
        $customPrompt = $request->input('custom_prompt');
        $roleId = $role->id;

        dispatch(function () use ($requestId, $event, $styleInstructions, $role, $customPrompt, $eventId, $roleId, $subdomain) {
            set_time_limit(120);

            try {
                $imageData = GeminiUtils::generateEventFlyer($event, $styleInstructions, $role, $customPrompt);

                if (! $imageData) {
                    \Log::warning('AI flyer generation returned no image data', [
                        'role_id' => $roleId,
                        'event_id' => $eventId,
                    ]);
                    Cache::put("ai_flyer_{$requestId}", ['status' => 'failed'], 300);

                    return;
                }

                $filename = ImageUtils::saveImageData($imageData, 'generated_flyer.png', 'flyer_');

                UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_FLYER, $roleId);

                if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                    $flyerUrl = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                    $flyerUrl = url('/storage/'.$filename);
                } else {
                    $flyerUrl = $filename;
                }

                $result = [
                    'status' => 'completed',
                    'success' => true,
                    'flyer_image_url' => $flyerUrl,
                    'delete_url' => route('event.delete_image', ['subdomain' => $subdomain]),
                ];

                $result['flyer_image_filename'] = $filename;

                Cache::put("ai_flyer_{$requestId}", $result, 300);
            } catch (\App\Exceptions\ContentModerationException $e) {
                Cache::put("ai_flyer_{$requestId}", ['status' => 'failed', 'error' => __('messages.ai_content_moderation_blocked')], 300);
            } catch (\Exception $e) {
                \Log::error('AI flyer generation failed: '.$e->getMessage(), [
                    'role_id' => $roleId,
                    'event_id' => $eventId,
                    'exception' => get_class($e),
                ]);
                report($e);
                Cache::put("ai_flyer_{$requestId}", ['status' => 'failed'], 300);
            }
        })->afterResponse();

        return response()->json(['request_id' => $requestId]);
    }

    public function pollFlyer($subdomain, $requestId)
    {
        if (! auth()->check() || ! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $data = Cache::get("ai_flyer_{$requestId}");

        if (! $data) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($data);
    }

    public function getEventDetailsPrompt(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:category_id,short_description,description,flyer_image',
            'name' => 'required|string',
            'venue_name' => 'nullable|string',
            'venue_address1' => 'nullable|string',
            'venue_city' => 'nullable|string',
        ]);

        $elements = $request->input('elements');
        $textElements = array_values(array_filter($elements, fn ($el) => $el !== 'flyer_image'));

        $prompt = ! empty($textElements)
            ? GeminiUtils::buildEventDetailsPrompt(
                $request->input('name'),
                $request->input('short_description'),
                $request->input('schedule_name', $role->name),
                $request->input('schedule_type', $role->type),
                $textElements,
                $request->input('description')
            )
            : '';

        $response = ['success' => true, 'prompt' => $prompt];

        if (in_array('flyer_image', $elements)) {
            $event = new Event;
            $event->name = $request->input('name');
            $event->starts_at = $request->input('starts_at');
            $event->duration = $request->input('duration');
            $event->short_description = $request->input('short_description');
            $event->category_id = $request->input('category_id');
            $event->setRelation('roles', collect([]));
            $event->setRelation('tickets', collect([]));

            if ($request->input('venue_name') || $request->input('venue_address1')) {
                $venueRole = new Role;
                $venueRole->type = 'venue';
                $venueRole->name = $request->input('venue_name', '');
                $venueRole->address1 = $request->input('venue_address1', '');
                $venueRole->city = $request->input('venue_city', '');
                $event->setRelation('roles', collect([$venueRole]));
            }

            $styleDescription = GeminiUtils::buildStyleContext($role);
            $flyerPrompt = GeminiUtils::buildFlyerPrompt($event, $request->input('style_instructions'), $styleDescription, $role);
            $response['image_prompts'] = ['flyer_image' => $flyerPrompt];
        }

        return response()->json($response);
    }

    public function generateEventDetails(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $role->canMakeAiContentRequest()) {
            return response()->json(['error' => __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiContentDailyLimit()])], 422);
        }

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:category_id,short_description,description',
            'name' => 'required|string',
            'style_instructions' => 'nullable|string|max:500',
            'custom_prompt' => 'nullable|string|max:5000',
            'save_instructions' => 'nullable|boolean',
        ]);

        $elements = $request->input('elements');

        try {
            $results = GeminiUtils::generateEventDetails(
                $request->input('name'),
                $request->input('short_description'),
                $request->input('schedule_name', $role->name),
                $request->input('schedule_type', $role->type),
                $elements,
                $request->input('description'),
                $request->input('custom_prompt'),
                $request->input('style_instructions')
            );

            if (empty($results)) {
                return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
            }

            UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_EVENT_DETAILS, $role->id);

            if ($request->input('save_instructions')) {
                $role->update(['ai_content_instructions' => $request->input('style_instructions', '')]);
            }

            $results['success'] = true;

            return response()->json($results);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI event details generation failed: '.$e->getMessage(), ['role_id' => $role->id]);

            return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
        }
    }

    public function guestParse(EventParseRequest $request, $subdomain)
    {
        $details = request()->input('event_details');
        $file = null;

        // Handle image data if provided
        if ($request->hasFile('details_image')) {
            $file = $request->file('details_image');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->canMakeAiParseRequest()) {
            return response()->json(['error' => __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiParseDailyLimit()])], 422);
        }

        try {
            $parsed = GeminiUtils::parseEvent($role, $details, $file);

            return response()->json($parsed);
        } catch (\Exception $e) {
            // Log the full error server-side but return generic message to user
            \Log::error('Guest event parsing failed: '.$e->getMessage(), [
                'role_subdomain' => $subdomain,
            ]);

            return response()->json(['error' => __('messages.event_parsing_failed')], 500);
        }
    }

    public function import(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        // An event needs a name; reject empty/null before it reaches the typed
        // SlugPatternUtils::generateSlug(string $eventName) inside saveEvent() (TypeError otherwise).
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        // Attach newly created / safety-net-matched venues to the importing user as
        // follower so they appear in the venue dropdowns on future visits, matching
        // manual create, calendar sync, and the automated curator import.
        $event = $this->eventRepo->saveEvent($role, $request, null, true);

        if ($request->social_image) {
            $tempDir = storage_path('app/temp');
            $imagePath = $tempDir.'/'.basename($request->social_image);
            $realPath = realpath($imagePath);
            if ($realPath && str_starts_with($realPath, $tempDir.DIRECTORY_SEPARATOR) && file_exists($realPath)) {
                $file = new \Illuminate\Http\UploadedFile($realPath, basename($realPath));
                $filename = strtolower('flyer_'.Str::random(32).'.'.$file->getClientOriginalExtension());
                $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                $event->flyer_image_url = $filename;
                $event->save();
            }
        }

        $role->autoCurateEvent($event);

        // Get venue data to return to client for future imports
        $venueData = null;
        $venue = $event->venue;
        if ($venue) {
            $isMember = false;
            $hasPivot = false;
            if ($user = $request->user()) {
                $isMember = $user->member()->where('roles.id', $venue->id)->exists();
                $hasPivot = $isMember || $user->roles()->where('roles.id', $venue->id)->exists();
            }
            // Only the fields the venue dropdown consumes (the client pushes this into the
            // same venues list). Never the full toData(): a connection-only matched venue has
            // no relationship to this user, so its email, phone, and billing columns must not
            // be serialized into the response. Matches the projection in showImport().
            $venueData = [
                'id' => UrlUtils::encodeId($venue->id),
                'name' => $venue->name,
                'address1' => $venue->address1,
                'city' => $venue->city,
                'is_member' => $isMember,
                'is_connected' => ! $hasPivot,
            ];
        }

        return response()->json([
            'success' => true,
            'event' => [
                'view_url' => $event->getGuestUrl($subdomain),
                'edit_url' => route('event.edit', ['subdomain' => $subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            ],
            'venue' => $venueData,
        ]);
    }

    public function guestImport(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->acceptEventRequests()) {
            abort(403, __('messages.not_authorized'));
        }

        // An event needs a name; reject empty/null up front - before guestImportWithAccount()
        // creates the user account + talent schedule - so a null name can't reach the typed
        // SlugPatternUtils::generateSlug(string $eventName) inside saveEvent() (TypeError otherwise).
        // Covers both the non-account save below and the require_account path.
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        // Prevent guests from injecting any visibility state
        $request->request->remove('is_draft');
        $request->request->remove('is_private');
        $request->request->remove('is_internal');

        // Curators that require an account collect the account + schedule + event on this one
        // page and own the event on the submitter's own talent schedule (linked to the curator).
        if ($role->require_account) {
            // The structured page never renders for booking-form curators (their required
            // vetting questions live on the booking form); block direct posts too.
            if ($role->usesBookingForm()) {
                return response()->json(['message' => __('messages.not_authorized')], 403);
            }

            return $this->guestImportWithAccount($request, $role);
        }

        // Handle user creation if requested (optional account; event owned by the curator)
        if ($request->boolean('create_account')) {
            $this->createAndLoginUser($request);
        }

        $event = $this->eventRepo->saveEvent($role, $request, null, false);

        $this->attachGuestFlyerImage($request, $event);

        $role->autoCurateEvent($event);

        // Clear the pending request session
        session()->forget(['pending_request', 'pending_request_allow_guest', 'pending_request_form']);

        return response()->json([
            'success' => true,
            'event' => [
                'view_url' => $event->getGuestUrl($subdomain),
                'message' => __('messages.event_created_guest'),
            ],
        ]);
    }

    /**
     * Single-page submission for curators that require an account: create (or sign in) the
     * submitter, ensure they have a talent schedule, save the event on it, and link it to the
     * curator (pending the curator's approval unless auto-accepted).
     */
    private function guestImportWithAccount(Request $request, Role $role)
    {
        if (is_demo_mode()) {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        // Honeypot
        if ($request->filled('website')) {
            return response()->json(['message' => __('messages.invalid_request')], 422);
        }

        // Server backstop for the curator-configured required submission fields (the form
        // enforces these client-side). Runs before account creation so a rejected submission
        // doesn't mint an account. group_id arrives as curator_group_id in the guest flow.
        $requestKeyByField = [
            'short_description' => 'short_description',
            'description' => 'description',
            'ticket_price' => 'ticket_price',
            'coupon_code' => 'coupon_code',
            'registration_url' => 'registration_url',
            'category_id' => 'category_id',
            'group_id' => 'curator_group_id',
        ];
        $rules = [];
        foreach ($role->import_config['required_fields'] ?? [] as $field => $isRequired) {
            // A stale group_id requirement must not brick submissions: the form hides the
            // sub-schedule field (and skips its client-side check) when the curator has no
            // sub-schedules, so don't demand server-side what the guest cannot provide.
            if ($field === 'group_id' && ! $role->groups()->exists()) {
                continue;
            }

            if ($isRequired && isset($requestKeyByField[$field])) {
                $rules[$requestKeyByField[$field]] = ['required'];
            }
        }
        if ($rules) {
            $request->validate($rules);
        }

        // A required sub-schedule must resolve to a real group for this curator, not merely be
        // present: the rule above is presence-only, and the attach step below silently drops an
        // unresolvable curator_group_id, so a crafted non-empty value would otherwise slip the
        // requirement and file the event uncategorized. Same skip condition as the rule (:2231).
        if (($role->import_config['required_fields']['group_id'] ?? false) && $role->groups()->exists()) {
            $groupId = UrlUtils::decodeId($request->curator_group_id);
            if (! $groupId || ! Group::where('id', $groupId)->where('role_id', $role->id)->exists()) {
                throw ValidationException::withMessages([
                    'curator_group_id' => [__('messages.select_sub_schedule')],
                ]);
            }
        }

        $user = auth()->user();

        if (! $user) {
            if ($request->input('account_mode') === 'login') {
                $request->validate([
                    'account_email' => ['required', 'string', 'email'],
                    'account_password' => ['required', 'string'],
                ]);

                // Mirror the canonical login form's protections: a shared per-email|ip lockout (same
                // throttle key as LoginRequest, so guesses here and on /login draw from one 5-try
                // budget) plus failed-login audit logging. The route's per-IP throttle alone never
                // locks the targeted account.
                $email = strtolower((string) $request->input('account_email'));
                $throttleKey = Str::transliterate(Str::lower((string) $request->input('account_email')).'|'.$request->ip());

                if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                    event(new Lockout($request));
                    $seconds = RateLimiter::availableIn($throttleKey);
                    throw ValidationException::withMessages([
                        'account_password' => [trans('auth.throttle', [
                            'seconds' => $seconds,
                            'minutes' => ceil($seconds / 60),
                        ])],
                    ]);
                }

                if (! Auth::attempt([
                    'email' => $email,
                    'password' => (string) $request->input('account_password'),
                ])) {
                    RateLimiter::hit($throttleKey);
                    AuditService::log(AuditService::AUTH_LOGIN_FAILED, null, null, null, null, null, $email);
                    throw ValidationException::withMessages([
                        'account_password' => [__('messages.incorrect_password')],
                    ]);
                }

                RateLimiter::clear($throttleKey);

                $user = auth()->user();
                AuditService::log(AuditService::AUTH_LOGIN, $user->id);
            } else {
                $user = $this->createAccountWithCode($request, $role);
            }
        }

        // Resolve the submitter's talent schedule (reuse, or auto-create from the page name).
        if ($request->filled('selected_talent_id')) {
            $talent = $user->talents()->where('roles.id', UrlUtils::decodeId($request->selected_talent_id))->first();
        } else {
            $talent = $user->talents()->first();
        }

        if (! $talent) {
            if (config('app.hosted') && $user->owner()->count() >= 50) {
                return response()->json(['message' => __('messages.schedule_limit')], 422);
            }

            $name = trim((string) $request->input('schedule_name')) ?: $user->name;

            // A schedule created solely to post to this curator inherits the curator's timezone, not
            // the guest's browser timezone: its events are entered as curator-local times, and the
            // talent's own guest page renders them in its own timezone (Event::getStartDateTime()
            // falls back to creatorRole->timezone for logged-out viewers). The form warns the guest
            // when their device timezone differs. The user *account* still keeps the browser value.
            $talent = $this->createTalentSchedule($user, $name, $role->timezone, $user->language_code);
        }

        // Auto-follow the curator (the form discloses this). Demo users never follow.
        if (! DemoService::isDemoUser($user) && ! $user->isConnected($role->subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }
        if (! $user->follow_consent_dismissed) {
            $user->follow_consent_dismissed = true;
            $user->saveQuietly();
        }

        // Mirror the AP acceptance rule so require-approval curators get a pending item.
        $isAccepted = ($role->accept_requests && ! $role->require_approval)
            || ($role->approved_subdomains && in_array($talent->subdomain, $role->approved_subdomains));

        try {
            // The event saves onto the submitter's talent schedule, but the time they typed is the
            // event's local time on the curator's page, so anchor the capture to the curator. Without
            // the override an existing talent schedule in another timezone shifts the published time.
            $event = $this->eventRepo->saveEvent($talent, $request, null, false, $role->timezone);

            $this->attachGuestFlyerImage($request, $event);

            // Link the event to the curator. saveEvent()'s sync() only manages the talent's
            // own schedules, so attach here (after the save).
            if (! $event->roles()->where('roles.id', $role->id)->exists()) {
                $pivot = ['is_accepted' => $isAccepted ?: null];

                // Only honor a sub-schedule that actually belongs to this curator.
                if ($request->filled('curator_group_id')) {
                    $groupId = UrlUtils::decodeId($request->curator_group_id);
                    if ($groupId && Group::where('id', $groupId)->where('role_id', $role->id)->exists()) {
                        $pivot['group_id'] = $groupId;
                    }
                }

                $event->roles()->attach($role->id, $pivot);
            }

            // Cascade to the talent's own default curators, matching the other import paths.
            $talent->autoCurateEvent($event);
        } catch (QueryException $e) {
            report($e);

            return response()->json(['message' => __('messages.error_saving_event')], 500);
        }

        session()->forget(['pending_request', 'pending_request_allow_guest', 'pending_request_form']);

        return response()->json([
            'success' => true,
            'event' => [
                'status' => $isAccepted ? 'live' : 'pending',
                'event_name' => $event->name,
                'view_url' => $event->getGuestUrl($talent->subdomain),
                'dashboard_url' => app_url(route('role.view_admin', ['subdomain' => $talent->subdomain, 'tab' => 'schedule'], false)),
            ],
            // The submitter's schedules, so "Submit another" can offer the real picker (a
            // login-mode client has no way to know them otherwise).
            'posted_as' => UrlUtils::encodeId($talent->id),
            'talents' => $user->talents()->get()
                ->map(fn ($t) => ['id' => UrlUtils::encodeId($t->id), 'name' => $t->name])
                ->values(),
        ]);
    }

    /**
     * Validate the 6-digit code (hosted), then create or update (stub) the submitter and log them
     * in. Throws ValidationException (422 JSON) on any failure.
     */
    private function createAccountWithCode(Request $request, Role $role)
    {
        // Turnstile is enforced earlier, at the code-send step (sendVerificationCode), so it is
        // not re-checked here (the emailed code is the proof, and the token is single-use).

        // Selfhost does not allow public self-registration after the first user (mirrors
        // RegisteredUserController::store). Block account creation/claim here too so this guest
        // flow can't be used to mint accounts or take over an invited stub without verification.
        if (! config('app.hosted') && ! config('app.is_testing') && User::count() > 0) {
            throw ValidationException::withMessages([
                'account_email' => [__('messages.account_creation_disabled')],
            ]);
        }

        $email = strtolower((string) $request->input('account_email'));
        $existingUser = User::where('email', $email)->first();
        $isStub = $existingUser ? $existingUser->isStub() : false;

        $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'account_email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                $isStub ? [] : ['unique:users,email'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'account_password' => ['required', 'string', 'min:8'],
        ]);

        // Verify the emailed 6-digit code (hosted only, matching registration).
        if (config('app.hosted') && ! config('app.is_testing')) {
            $request->validate(['verification_code' => ['required', 'string', 'size:6']]);

            $originalEmail = Cache::pull('signup_code_email_'.$request->verification_code);
            if (! $originalEmail || strtolower($originalEmail) !== $email) {
                throw ValidationException::withMessages([
                    'verification_code' => [__('messages.code_invalid')],
                ]);
            }
        }

        $utmParams = session('utm_params', []);
        if (empty($utmParams) && $request->cookie('utm_params')) {
            $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
        }

        $languageCode = (session()->has('guest_language') && is_valid_language_code(session('guest_language')))
            ? session('guest_language')
            : 'en';

        $attributes = [
            'name' => $request->input('account_name'),
            'password' => Hash::make($request->input('account_password')),
            'timezone' => $request->input('timezone') ?: ($role->timezone ?: 'UTC'),
            'language_code' => $languageCode,
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
            'utm_content' => $utmParams['utm_content'] ?? null,
            'utm_term' => $utmParams['utm_term'] ?? null,
            'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
            'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
        ];

        // The guest-submit flow is an event request on someone else's schedule;
        // stubs keep their original acquisition context (team invite, subscriber)
        if ($isStub) {
            $attributes['signup_intent'] = $existingUser->signup_intent ?? 'request';
            $existingUser->update($attributes);
            $user = $existingUser;
        } else {
            $attributes['signup_intent'] = 'request';
            $user = User::create(array_merge($attributes, ['email' => $email]));
        }

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language']);

        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);

        return $user;
    }

    /**
     * Create a minimal talent schedule owned by the user. Contact fields are intentionally left
     * null so the auto-created page stays noindex until the owner adds details.
     */
    private function createTalentSchedule(User $user, string $name, $timezone = null, $languageCode = null): Role
    {
        $talent = new Role;
        $talent->type = 'talent';
        $talent->user_id = $user->id;
        $talent->name = $name;

        // generateSubdomain already handles uniqueness, but retry on the off chance two
        // same-named talents are created at once (mirrors the venue auto-create in
        // ConvertsLocationToVenue) so a race can't surface as a raw UNIQUE-index 500.
        $subdomain = Role::generateSubdomain($name);
        $attempts = 0;
        while (Role::where('subdomain', $subdomain)->exists() && $attempts < 10) {
            $subdomain = Role::generateSubdomain($name.'-'.++$attempts);
        }
        $talent->subdomain = $subdomain;

        $talent->timezone = $timezone ?: 'UTC';
        $talent->language_code = is_valid_language_code($languageCode) ? $languageCode : 'en';

        // Mark verified (without an email/phone) so the page is "claimed" and viewable by the
        // owner (RoleController::viewGuest redirects unclaimed schedules). Leaving email/phone
        // null keeps the auto-created page noindex and out of the sitemap.
        $talent->email_verified_at = now();

        if (config('app.is_testing')) {
            $talent->plan_type = 'enterprise';
            $talent->plan_expires = '2099-01-01';
        }

        $talent->save();

        $user->roles()->attach($talent->id, [
            'level' => 'owner',
            'created_at' => now(),
        ]);

        AuditService::log(AuditService::SCHEDULE_CREATE, $user->id, 'Role', $talent->id, null, null, $talent->name);

        return $talent;
    }

    private function attachGuestFlyerImage(Request $request, Event $event): void
    {
        if (! $request->social_image) {
            return;
        }

        $tempDir = storage_path('app/temp');
        $imagePath = $tempDir.'/'.basename($request->social_image);
        $realPath = realpath($imagePath);
        if ($realPath && str_starts_with($realPath, $tempDir.DIRECTORY_SEPARATOR) && file_exists($realPath)) {
            $file = new \Illuminate\Http\UploadedFile($realPath, basename($realPath));
            $filename = strtolower('flyer_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $event->flyer_image_url = $filename;
            $event->save();
        }
    }

    /**
     * Check whether an account already exists for the given email (drives the single-page
     * on-blur register/login switch). Returns whether it exists and whether it is a stub.
     */
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        $user = User::where('email', strtolower($request->email))->first();

        return response()->json([
            'exists' => (bool) $user,
            'stub' => $user ? $user->isStub() : false,
        ]);
    }

    /**
     * Create a new user and log them in (optional account on curators that do NOT require one).
     */
    private function createAndLoginUser(Request $request)
    {
        // The form posts account fields under account_* to avoid colliding with the event's own
        // name/email fields in the same payload (older callers may still send name/email/password).
        $name = $request->input('account_name', $request->input('name'));
        $email = strtolower((string) $request->input('account_email', $request->input('email')));
        $password = $request->input('account_password', $request->input('password'));

        $request->merge(['account_name' => $name, 'account_email' => $email, 'account_password' => $password]);

        // Selfhost does not allow public self-registration after the first user (mirrors
        // RegisteredUserController::store). Block this optional-account path too.
        if (! config('app.hosted') && ! config('app.is_testing') && User::count() > 0) {
            throw ValidationException::withMessages([
                'account_email' => [__('messages.account_creation_disabled')],
            ]);
        }

        $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'account_email' => array_merge(
                ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'account_password' => ['required', 'string', 'min:8'],
        ]);

        $utmParams = session('utm_params', []);
        if (empty($utmParams) && $request->cookie('utm_params')) {
            $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'timezone' => 'America/New_York',
            'language_code' => (session()->has('guest_language') && is_valid_language_code(session('guest_language')))
                ? session('guest_language')
                : 'en',
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
            'utm_content' => $utmParams['utm_content'] ?? null,
            'utm_term' => $utmParams['utm_term'] ?? null,
            'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
            'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
            'signup_intent' => 'request',
        ]);

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language']);

        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);

        return $user;
    }

    public function showBookingRequest(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->usesBookingForm() || ! $role->acceptEventRequests()) {
            abort(404);
        }

        if (! auth()->check()) {
            session()->put('pending_request', $subdomain);
            session()->put('pending_request_form', 'booking');
        }

        // Store guest language for auth flow
        if (session()->has('translate')) {
            session()->put('guest_language', $role->translation_language_code);
        } elseif ($request->lang && is_valid_language_code($request->lang)) {
            session()->put('guest_language', $request->lang);
        } elseif (is_valid_language_code($role->language_code)) {
            session()->put('guest_language', $role->language_code);
        }

        if ($request->lang) {
            if (is_valid_language_code($request->lang)) {
                app()->setLocale($request->lang);

                if ($request->lang == $role->translation_language_code && $request->lang != $role->language_code) {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale($role->translation_language_code);
        } else {
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }
        }

        return view('event.booking-request', ['role' => $role]);
    }

    public function bookingRequest(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->usesBookingForm() || ! $role->acceptEventRequests()) {
            abort(403, __('messages.not_authorized'));
        }

        // Validate form input
        $rules = [
            'event_name' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_online' => ['nullable'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_country_code' => ['nullable', 'string', 'max:2'],
            'venue_address1' => ['nullable', 'string', 'max:255'],
            'venue_city' => ['nullable', 'string', 'max:255'],
            'venue_state' => ['nullable', 'string', 'max:255'],
            'venue_postal_code' => ['nullable', 'string', 'max:20'],
            'event_url' => ['nullable', 'url', 'max:500'],
        ];

        if (! auth()->check()) {
            $rules['contact_name'] = ['required', 'string', 'max:255'];
            $rules['contact_email'] = ['required', 'string', 'email', 'max:255'];
        }

        $request->validate($rules);

        // Handle user creation if requested
        // Map contact fields to account fields for createAndLoginUser
        if ($request->input('create_account') && ! auth()->check()) {
            if (! $request->has('name') || ! $request->input('name')) {
                $request->merge(['name' => $request->input('contact_name')]);
            }
            if (! $request->has('email') || ! $request->input('email')) {
                $request->merge(['email' => $request->input('contact_email')]);
            }
            $this->createAndLoginUser($request);
        }

        $user = auth()->user();

        // Create venue Role with location info
        $venue = null;
        $isOnline = $request->input('is_online');

        if ($role->isVenue()) {
            // Venue schedule: use the schedule itself as the venue
            $venue = $role;
        } elseif ($request->venue_name || $request->venue_address1 || $request->venue_city) {
            $venue = new Role;
            $venue->name = $request->venue_name ?? null;
            $venue->subdomain = Role::generateSubdomain($request->venue_name);
            $venue->type = 'venue';
            $venue->address1 = $request->venue_address1;
            $venue->city = $request->venue_city;
            $venue->state = $request->venue_state;
            $venue->postal_code = $request->venue_postal_code;
            $countryCode = $request->venue_country_code ?: $role->country_code;
            $venue->country_code = $countryCode ? strtolower($countryCode) : null;
            $venue->language_code = $role->language_code;
            $venue->timezone = $role->timezone;
            $venue->background_colors = ColorUtils::randomGradient();
            $venue->background_rotation = rand(0, 359);
            $venue->font_color = '#ffffff';
            $venue->save();
            $venue->refresh();

            if ($user) {
                $venue->user_id = $user->id;
                $venue->email_verified_at = $user->email_verified_at;
                $venue->save();

                $user->roles()->attach($venue->id, ['level' => 'owner', 'created_at' => now()]);

                if (! $user->default_role_id) {
                    $user->default_role_id = $venue->id;
                    $user->save();
                }
            } elseif ($request->contact_email) {
                $matchingUser = User::whereEmail($request->contact_email)->first();
                if ($matchingUser) {
                    $venue->user_id = $matchingUser->id;
                    $venue->email_verified_at = $matchingUser->email_verified_at;
                    $venue->save();
                    $matchingUser->roles()->attach($venue->id, ['level' => 'owner', 'created_at' => now()]);

                    if (! $matchingUser->default_role_id) {
                        $matchingUser->default_role_id = $venue->id;
                        $matchingUser->save();
                    }
                }
            }
        }

        // Build starts_at from date + time, anchored to the schedule's timezone (not the
        // submitting user's personal account timezone) so the saved time matches the schedule.
        $startsAt = null;
        $startsAtTimezone = null;
        if ($request->date && $request->start_time) {
            $startsAtTimezone = $role->timezone ?? $user?->timezone ?? config('app.timezone');
            $startsAt = Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->start_time, $startsAtTimezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        // Create the event
        $event = new Event;
        $event->name = $request->event_name ?: __('messages.booking_request');
        $event->description = $request->description;
        $event->starts_at = $startsAt;
        $event->timezone = $startsAtTimezone;
        $event->event_url = $isOnline ? ($request->event_url ?: 'online') : null;

        $user = $user ?: $role->user;
        $event->user_id = $user->id;

        $eventName = $request->event_name ?: __('messages.booking_request');
        $event->slug = Str::slug($eventName).'-'.strtolower(Str::random(6));
        $event->save();

        // Attach talent role
        $isAccepted = $role->require_approval ? null : true;
        $event->roles()->attach($role->id, ['is_accepted' => $isAccepted]);

        // Attach venue role if created (skip if venue is the schedule itself)
        if ($venue && $venue->id !== $role->id) {
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        if ($isAccepted === null && $role->accept_requests && $role->require_approval) {
            $pendingCount = Event::whereHas('roles', function ($query) use ($role) {
                $query->where('event_role.role_id', $role->id)
                    ->whereNull('event_role.is_accepted');
            })->count();

            $editors = $role->getEditorsWantingNotification('new_request');
            foreach ($editors as $editor) {
                $editor->notify(new NewRequestsNotification($role, $pendingCount));
            }

            if ($editors->isNotEmpty()) {
                $role->last_notified_request_count = $pendingCount;
                $role->save();
            }
        }

        // Auto-curate event
        $role->autoCurateEvent($event);

        // Clear the pending request session
        session()->forget('pending_request');
        session()->forget('pending_request_allow_guest');
        session()->forget('pending_request_form');

        return response()->json([
            'success' => true,
            'message' => __('messages.booking_request_submitted'),
            'redirect_url' => $role->getGuestUrl(),
        ]);
    }

    public function uploadImage(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $file = $request->file('image');

        if (! $file) {
            return response()->json(['success' => false, 'error' => 'No file uploaded'], 400);
        }

        // Validate file extension (whitelist only safe image extensions)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions)) {
            return response()->json(['success' => false, 'error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp'], 400);
        }

        // Validate MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, $allowedMimeTypes)) {
            return response()->json(['success' => false, 'error' => 'Invalid file type'], 400);
        }

        // Validate that it's actually an image
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            return response()->json(['success' => false, 'error' => 'File is not a valid image'], 400);
        }

        // Use Laravel's storage directory instead of /tmp for security
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0700, true);
        }

        $basename = 'event_'.strtolower(Str::random(32)).'.'.$extension;
        $filename = $tempDir.'/'.$basename;
        move_uploaded_file($file->getPathname(), $filename);

        return response()->json(['success' => true, 'filename' => $basename]);
    }

    public function guestUploadImage(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $file = $request->file('image');

        if (! $file) {
            return response()->json(['success' => false, 'error' => 'No file uploaded'], 400);
        }

        // Validate file extension (whitelist only safe image extensions)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions)) {
            return response()->json(['success' => false, 'error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp'], 400);
        }

        // Validate MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();
        if (! in_array($mimeType, $allowedMimeTypes)) {
            return response()->json(['success' => false, 'error' => 'Invalid file type'], 400);
        }

        // Validate that it's actually an image
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            return response()->json(['success' => false, 'error' => 'File is not a valid image'], 400);
        }

        // Use Laravel's storage directory instead of /tmp for security
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0700, true);
        }

        $basename = 'event_'.strtolower(Str::random(32)).'.'.$extension;
        $filename = $tempDir.'/'.$basename;
        move_uploaded_file($file->getPathname(), $filename);

        return response()->json(['success' => true, 'filename' => $basename]);
    }

    /**
     * Clear the pending_request session and redirect back to the current URL
     */
    public function clearPendingRequest(Request $request)
    {
        // Clear the pending request session
        session()->forget('pending_request');
        session()->forget('pending_request_allow_guest');
        session()->forget('pending_request_form');

        // Get the redirect URL from the form, or fall back to the current URL
        $redirectUrl = $request->input('redirect_url', url()->current());

        // Validate redirect URL to prevent open redirect attacks
        $parsedUrl = parse_url($redirectUrl);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        // Only allow redirects to same host or relative paths
        if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $appHost) {
            // External redirect not allowed - use home page instead
            $redirectUrl = url('/');
        }

        // Block javascript: and data: URLs
        $lowerUrl = strtolower(trim($redirectUrl));
        if (str_starts_with($lowerUrl, 'javascript:') || str_starts_with($lowerUrl, 'data:')) {
            $redirectUrl = url('/');
        }

        return redirect($redirectUrl);
    }

    public function submitVideo(EventVideoSubmitRequest $request, $subdomain, $event_hash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($event_hash);
        $event = Event::with('parts', 'roles')->findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            abort(404);
        }

        if (! $event->isFanVideosEnabled()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if ($event->is_draft && ! $isMemberOrAdmin) {
            abort(404);
        }
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        $youtubeUrl = trim($request->input('youtube_url'));
        $embedUrl = UrlUtils::getYouTubeEmbed($youtubeUrl);

        if (! $embedUrl) {
            return redirect()->back()->with('error', __('messages.invalid_youtube_url'));
        }

        // Store only the canonical watch URL so no guest-controlled query string is persisted
        $youtubeUrl = UrlUtils::getCanonicalYouTubeUrl($youtubeUrl);

        if (! auth()->check()) {
            return redirect_with_pending_action(
                app_url(route('sign_up', [], false)),
                ['pending_fan_content' => [
                    'type' => 'video',
                    'subdomain' => $subdomain,
                    'event_hash' => $event_hash,
                    'youtube_url' => $youtubeUrl,
                    'event_part_id' => $request->input('event_part_id'),
                    'event_date' => $request->input('event_date'),
                    'return_url' => url()->previous(),
                ]]
            );
        }

        $eventPartId = $request->input('event_part_id');
        if ($eventPartId) {
            $eventPartId = UrlUtils::decodeId($eventPartId);
            $part = $event->parts->firstWhere('id', $eventPartId);
            if (! $part) {
                return redirect()->back()->with('error', __('messages.not_authorized'));
            }
        }

        $eventDate = $event->days_of_week ? $request->input('event_date') : null;

        // Check for duplicate by video ID (normalizes different URL formats)
        $submittedVideoId = basename(parse_url($embedUrl, PHP_URL_PATH));
        $query = EventVideo::where('event_id', $event->id);
        if ($eventPartId) {
            $query->where('event_part_id', $eventPartId);
        } else {
            $query->whereNull('event_part_id');
        }
        if ($eventDate) {
            $query->where('event_date', $eventDate);
        }
        $exists = $query->get()->contains(function ($video) use ($submittedVideoId) {
            $existingEmbed = UrlUtils::getYouTubeEmbed($video->youtube_url);

            return $existingEmbed && basename(parse_url($existingEmbed, PHP_URL_PATH)) === $submittedVideoId;
        });

        if ($exists) {
            return redirect()->back()->with('error', __('messages.video_already_submitted'));
        }

        // Auto-approve if user is a member of any schedule associated with this event
        $isApproved = $request->user()->canEditEvent($event);

        $video = EventVideo::create([
            'event_id' => $event->id,
            'event_part_id' => $eventPartId ?: null,
            'event_date' => $eventDate,
            'user_id' => $request->user()->id,
            'youtube_url' => $youtubeUrl,
            'is_approved' => $isApproved,
        ]);

        if (! $request->user()->isConnected($role->subdomain)) {
            $request->user()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        $message = $isApproved
            ? __('messages.video_submitted_approved')
            : __('messages.video_submitted');

        $redirect = redirect()->to($event->getGuestUrl($subdomain))->with('message', $message);

        if ($isApproved) {
            $redirect = $redirect->with('scroll_to', 'event-media-section');
        } else {
            $redirect = $redirect->with('scroll_to', 'pending-video-'.$video->id);
        }

        return $redirect;
    }

    public function submitComment(EventCommentSubmitRequest $request, $subdomain, $event_hash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($event_hash);
        $event = Event::with('parts', 'roles')->findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            abort(404);
        }

        if (! $event->isFanCommentsEnabled()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if ($event->is_draft && ! $isMemberOrAdmin) {
            abort(404);
        }
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        $comment = strip_tags(trim($request->input('comment')));

        if (! auth()->check()) {
            return redirect_with_pending_action(
                app_url(route('sign_up', [], false)),
                ['pending_fan_content' => [
                    'type' => 'comment',
                    'subdomain' => $subdomain,
                    'event_hash' => $event_hash,
                    'comment' => $comment,
                    'event_part_id' => $request->input('event_part_id'),
                    'event_date' => $request->input('event_date'),
                    'return_url' => url()->previous(),
                ]]
            );
        }

        $eventPartId = $request->input('event_part_id');
        if ($eventPartId) {
            $eventPartId = UrlUtils::decodeId($eventPartId);
            $part = $event->parts->firstWhere('id', $eventPartId);
            if (! $part) {
                return redirect()->back()->with('error', __('messages.not_authorized'));
            }
        }

        $eventDate = $event->days_of_week ? $request->input('event_date') : null;

        // Auto-approve if user is a member of any schedule associated with this event
        $isApproved = $request->user()->canEditEvent($event);

        $eventComment = EventComment::create([
            'event_id' => $event->id,
            'event_part_id' => $eventPartId ?: null,
            'event_date' => $eventDate,
            'user_id' => $request->user()->id,
            'comment' => $comment,
            'is_approved' => $isApproved,
        ]);

        if (! $request->user()->isConnected($role->subdomain)) {
            $request->user()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        $message = $isApproved
            ? __('messages.comment_submitted_approved')
            : __('messages.comment_submitted');

        $redirect = redirect()->to($event->getGuestUrl($subdomain))->with('message', $message);

        if ($isApproved) {
            $redirect = $redirect->with('scroll_to', 'event-media-section');
        } else {
            $redirect = $redirect->with('scroll_to', 'pending-comment-'.$eventComment->id);
        }

        return $redirect;
    }

    public function approveVideo(Request $request, $subdomain, $hash)
    {
        $id = UrlUtils::decodeId($hash);
        $video = EventVideo::with('event')->findOrFail($id);

        if ($request->user()->cannot('update', $video->event)) {
            return redirect()->to(url()->previous().'#section-fan-content')->with('error', __('messages.not_authorized'));
        }

        $video->is_approved = true;
        $video->save();

        return redirect()->to(url()->previous().'#section-fan-content')->with('message', __('messages.video_approved'));
    }

    public function rejectVideo(Request $request, $subdomain, $hash)
    {
        $id = UrlUtils::decodeId($hash);
        $video = EventVideo::with('event')->findOrFail($id);

        if ($request->user()->cannot('update', $video->event)) {
            return redirect()->to(url()->previous().'#section-fan-content')->with('error', __('messages.not_authorized'));
        }

        $video->delete();

        return redirect()->to(url()->previous().'#section-fan-content')->with('message', __('messages.video_rejected'));
    }

    public function approveComment(Request $request, $subdomain, $hash)
    {
        $id = UrlUtils::decodeId($hash);
        $comment = EventComment::with('event')->findOrFail($id);

        if ($request->user()->cannot('update', $comment->event)) {
            return redirect()->to(url()->previous().'#section-fan-content')->with('error', __('messages.not_authorized'));
        }

        $comment->is_approved = true;
        $comment->save();

        return redirect()->to(url()->previous().'#section-fan-content')->with('message', __('messages.comment_approved'));
    }

    public function rejectComment(Request $request, $subdomain, $hash)
    {
        $id = UrlUtils::decodeId($hash);
        $comment = EventComment::with('event')->findOrFail($id);

        if ($request->user()->cannot('update', $comment->event)) {
            return redirect()->to(url()->previous().'#section-fan-content')->with('error', __('messages.not_authorized'));
        }

        $comment->delete();

        return redirect()->to(url()->previous().'#section-fan-content')->with('message', __('messages.comment_rejected'));
    }

    public function submitPhoto(EventPhotoSubmitRequest $request, $subdomain, $event_hash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($event_hash);
        $event = Event::with('parts', 'roles')->findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            abort(404);
        }

        if (! $event->isFanPhotosEnabled()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! $role->canUploadPhoto()) {
            return redirect()->back()->with('error', __('messages.photo_limit_reached'));
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if ($event->is_draft && ! $isMemberOrAdmin) {
            abort(404);
        }
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        $file = $request->file('photo');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', __('messages.invalid_photo'));
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            return redirect()->back()->with('error', __('messages.invalid_photo'));
        }

        if (! @getimagesize($file->getPathname())) {
            return redirect()->back()->with('error', __('messages.invalid_photo'));
        }

        if (! auth()->check()) {
            $tempFilename = 'photo_'.Str::random(32).'.'.$extension;
            $file->storeAs('temp', $tempFilename);

            return redirect_with_pending_action(
                app_url(route('sign_up', [], false)),
                ['pending_fan_content' => [
                    'type' => 'photo',
                    'subdomain' => $subdomain,
                    'event_hash' => $event_hash,
                    'temp_filename' => $tempFilename,
                    'extension' => $extension,
                    'event_part_id' => $request->input('event_part_id'),
                    'event_date' => $request->input('event_date'),
                    'return_url' => url()->previous(),
                    'return_to' => $request->input('return_to'),
                ]]
            );
        }

        $eventPartId = $request->input('event_part_id');
        if ($eventPartId) {
            $eventPartId = UrlUtils::decodeId($eventPartId);
            $part = $event->parts->firstWhere('id', $eventPartId);
            if (! $part) {
                return redirect()->back()->with('error', __('messages.not_authorized'));
            }
        }

        $eventDate = $event->days_of_week ? $request->input('event_date') : null;

        $filename = 'photo_'.Str::random(32).'.'.$extension;
        if (config('filesystems.default') == 'local') {
            $file->storeAs('public', $filename);
        } else {
            $file->storeAs('/', $filename);
        }

        $isApproved = $request->user()->canEditEvent($event);

        $photo = EventPhoto::create([
            'event_id' => $event->id,
            'event_part_id' => $eventPartId ?: null,
            'event_date' => $eventDate,
            'user_id' => $request->user()->id,
            'photo_url' => $filename,
            'is_approved' => $isApproved,
        ]);

        if (! $request->user()->isConnected($role->subdomain)) {
            $request->user()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        $message = $isApproved
            ? __('messages.photo_submitted_approved')
            : __('messages.photo_submitted');

        if ($request->input('return_to') === 'gallery') {
            $redirect = redirect()->to($event->getPhotoGalleryUrl($subdomain))->with('message', $message);
        } else {
            $redirect = redirect()->to($event->getGuestUrl($subdomain))->with('message', $message);

            if ($isApproved) {
                $redirect = $redirect->with('scroll_to', 'event-media-section');
            } else {
                $redirect = $redirect->with('scroll_to', 'pending-photo-'.$photo->id);
            }
        }

        return $redirect;
    }

    public function approvePhoto(Request $request, $subdomain, $hash)
    {
        $id = UrlUtils::decodeId($hash);
        $photo = EventPhoto::with('event')->findOrFail($id);

        if ($request->user()->cannot('update', $photo->event)) {
            return redirect()->to(url()->previous().'#section-fan-content')->with('error', __('messages.not_authorized'));
        }

        $photo->is_approved = true;
        $photo->save();

        return redirect()->to(url()->previous().'#section-fan-content')->with('message', __('messages.photo_approved'));
    }

    public function rejectPhoto(Request $request, $subdomain, $hash)
    {
        $id = UrlUtils::decodeId($hash);
        $photo = EventPhoto::with('event')->findOrFail($id);

        if ($request->user()->cannot('update', $photo->event)) {
            return redirect()->to(url()->previous().'#section-fan-content')->with('error', __('messages.not_authorized'));
        }

        $photo->delete();

        return redirect()->to(url()->previous().'#section-fan-content')->with('message', __('messages.photo_rejected'));
    }

    public function downloadPhotos(Request $request, $subdomain, $event_hash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();

        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($event_hash);
        $event = Event::findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            abort(404);
        }

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $photos = EventPhoto::where('event_id', $event->id)
            ->where('is_approved', true)
            ->get();

        if ($photos->isEmpty()) {
            return redirect()->back()->with('error', __('messages.no_photos_to_download'));
        }

        $zipFilename = 'photos-'.Str::slug($event->name).'-'.now()->format('Y-m-d').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFilename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', __('messages.something_went_wrong'));
        }

        foreach ($photos as $index => $photo) {
            $rawPath = $photo->getAttributes()['photo_url'];
            if (! $rawPath) {
                continue;
            }

            $storagePath = config('filesystems.default') == 'local' ? 'public/'.$rawPath : $rawPath;

            try {
                $contents = Storage::get($storagePath);
                if ($contents) {
                    $extension = pathinfo($rawPath, PATHINFO_EXTENSION) ?: 'jpg';
                    $zip->addFromString(($index + 1).'-'.Str::slug($event->name).'.'.$extension, $contents);
                }
            } catch (\Exception $e) {
                report($e);

                continue;
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);
    }

    public function scanAgenda(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Auto-select event: most recent event in past month with no parts
        $selectedEvent = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('role_id', $role->id);
        })
            ->where('starts_at', '>=', now()->subMonth())
            ->where('starts_at', '<=', now())
            ->whereDoesntHave('parts')
            ->orderBy('starts_at', 'desc')
            ->first();

        // Fallback: next upcoming event with no parts
        if (! $selectedEvent) {
            $selectedEvent = Event::whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id);
            })
                ->where('starts_at', '>', now())
                ->whereDoesntHave('parts')
                ->orderBy('starts_at', 'asc')
                ->first();
        }

        // Load all recent events for manual selection
        $events = Event::whereHas('roles', function ($query) use ($role) {
            $query->where('role_id', $role->id);
        })
            ->whereDoesntHave('parts')
            ->orderBy('starts_at', 'desc')
            ->limit(50)
            ->get();

        $eventsData = $events->map(function ($e) use ($subdomain) {
            return [
                'id' => UrlUtils::encodeId($e->id),
                'name' => $e->name,
                'starts_at' => $e->starts_at ? $e->getShortDateRangeDisplay('D, M j, Y') : null,
                'image_url' => $e->getImageUrl(),
                'view_url' => route('event.view_guest_with_id', ['subdomain' => $subdomain, 'slug' => $e->slug, 'id' => UrlUtils::encodeId($e->id)]),
            ];
        });

        return view('event.scan-agenda', [
            'role' => $role,
            'subdomain' => $subdomain,
            'selectedEventId' => $selectedEvent ? UrlUtils::encodeId($selectedEvent->id) : null,
            'eventsData' => $eventsData,
            'aiPrompt' => $selectedEvent?->agenda_ai_prompt ?? $role->agenda_ai_prompt ?? '',
        ]);
    }

    public function saveEventParts(EventPartsSaveRequest $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $eventId = UrlUtils::decodeId($request->input('event_id'));
        $event = Event::findOrFail($eventId);

        if ($request->user()->cannot('update', $event)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        // Delete existing parts
        $event->parts()->delete();

        // Create new parts
        foreach ($request->input('parts') as $index => $partData) {
            EventPart::create([
                'event_id' => $event->id,
                'name' => $partData['name'],
                'description' => $partData['description'] ?? null,
                'start_time' => $partData['start_time'] ?? null,
                'end_time' => $partData['end_time'] ?? null,
                'sort_order' => $index,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.agenda_saved'),
            'edit_url' => route('event.edit', ['subdomain' => $subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            'view_url' => route('event.view_guest_with_id', ['subdomain' => $subdomain, 'slug' => $event->slug, 'id' => UrlUtils::encodeId($event->id)]),
        ]);
    }

    public function photoGallery(Request $request, $subdomain, $slug = '', $id = null, $date = null)
    {
        $role = Role::subdomain($subdomain)->first();

        if (! $role || ! $role->isClaimed()) {
            return redirect(app_url());
        }

        // Locale handling
        if ($request->lang) {
            if (is_valid_language_code($request->lang)) {
                app()->setLocale($request->lang);

                if ($request->lang == $role->translation_language_code && $request->lang != $role->language_code) {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale($role->translation_language_code);
        } elseif (is_valid_language_code($role->language_code)) {
            app()->setLocale($role->language_code);
        }

        $eventIdParam = $id ? UrlUtils::decodeId($id) : null;
        $eventRepo = app(EventRepo::class);
        $event = $slug ? $eventRepo->getEvent($subdomain, $slug, $date, $eventIdParam, $role) : null;

        if (! $event) {
            return redirect($role->getGuestUrl());
        }

        // Privacy check
        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());

        // Draft and internal events (both is_draft=true) are members-only - a non-member must not
        // reach the fan-photo gallery by direct link, matching the guest event page guard.
        if ($event->is_draft && ! $isMemberOrAdmin) {
            return redirect($role->getGuestUrl());
        }

        // Unlisted (is_private, no password) events are reachable by direct link, so their fan-photo
        // gallery is too. Password-protected ones still hit the password gate below.

        // Password gate
        $bypassPassword = $isMemberOrAdmin || session()->has('event_password_'.$event->id);
        if ($event->isPasswordProtected() && ! $bypassPassword) {
            return redirect($event->getGuestUrl($subdomain, $date));
        }

        // Redirect if fan photos are disabled
        if (! $event->isFanPhotosEnabled()) {
            return redirect($event->getGuestUrl($subdomain, $date));
        }

        // Resolve next date for recurring events
        if (! $date && $event->days_of_week) {
            $nextDate = now();
            $daysOfWeek = str_split($event->days_of_week);
            while (true) {
                if ($daysOfWeek[$nextDate->dayOfWeek] == '1' && $nextDate >= now()->format('Y-m-d')) {
                    break;
                }
                $nextDate->addDay();
            }
            $date = $nextDate->format('Y-m-d');
        }

        // Load photos
        $event->loadMissing(['approvedPhotos.user', 'parts.approvedPhotos.user']);

        // Determine otherRole (same logic as viewGuest)
        $otherRole = null;
        if ($role->isCurator()) {
            $talentRoles = $event->roles->filter(fn ($r) => $r->type === 'talent');
            $claimedTalent = $talentRoles->first(fn ($r) => $r->isClaimed());
            if ($claimedTalent) {
                $otherRole = $claimedTalent;
            } elseif ($event->venue && $event->venue->isClaimed()) {
                $otherRole = $event->venue;
            } else {
                $otherRole = $role;
            }
        } elseif ($event->venue) {
            if ($event->venue->subdomain == $subdomain) {
                $talentRoles = $event->roles->filter(fn ($r) => $r->type === 'talent');
                $otherRole = $talentRoles->count() > 0
                    ? ($talentRoles->firstWhere('subdomain', $slug) ?? $talentRoles->first())
                    : $role;
            } else {
                $otherRole = $event->venue;
            }
        } else {
            $otherRole = $role;
        }

        // Collect all approved photos
        $allPhotos = collect();
        foreach ($event->parts as $part) {
            $partPhotos = $part->approvedPhotos;
            if ($event->days_of_week && $date) {
                $partPhotos = $partPhotos->filter(fn ($p) => $p->event_date === $date || $p->event_date === null);
            }
            foreach ($partPhotos as $photo) {
                $allPhotos->push($photo);
            }
        }
        $eventPhotos = $event->approvedPhotos->whereNull('event_part_id');
        if ($event->days_of_week && $date) {
            $eventPhotos = $eventPhotos->filter(fn ($p) => $p->event_date === $date || $p->event_date === null);
        }
        foreach ($eventPhotos as $photo) {
            $allPhotos->push($photo);
        }

        // User's pending photos
        $myPendingPhotos = collect();
        if (auth()->check()) {
            $myPendingPhotos = EventPhoto::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->where('is_approved', false)
                ->get();
        }

        $photoLimitReached = ! $role->canUploadPhoto();

        // Fonts
        $fonts = [];
        if ($event->venue) {
            $fonts[] = $event->venue->font_family;
        }
        foreach ($event->roles as $each) {
            if ($each->isClaimed() && $each->isTalent()) {
                $fonts[] = $each->font_family;
            }
        }
        $fonts = array_unique($fonts);

        return response()->view('event/photo-gallery', compact(
            'subdomain',
            'role',
            'otherRole',
            'event',
            'date',
            'fonts',
            'allPhotos',
            'myPendingPhotos',
            'photoLimitReached',
        ));
    }

    public function downloadIcal($subdomain, $slug, $id, $date = null)
    {
        $event = Event::whereHas('roles', fn ($q) => $q->where('subdomain', $subdomain))
            ->find(UrlUtils::decodeId($id));

        if (! $event) {
            abort(404);
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());

        if ($event->is_draft && ! $isMemberOrAdmin) {
            abort(404);
        }
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        if ($event->isPasswordProtected() && ! $isMemberOrAdmin && ! session()->has('event_password_'.$event->id)) {
            abort(403);
        }

        $title = $event->getTitle();
        $description = $event->description_html ? strip_tags($event->description_html) : ($event->role() ? strip_tags($event->role()->description_html) : '');
        $location = $event->venue ? $event->venue->bestAddress() : '';
        $duration = $event->duration > 0 ? $event->duration : 2;
        // UTC stamp: rebuild a dated occurrence from the venue's time-of-day so this .ics agrees
        // with the subscription feed it shares a UID with (FeedController::buildVevent).
        $startAt = $date ? $event->occurrenceStartUtc($date) : $event->getStartDateTime();
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->addMinutes(Event::durationHoursToMinutes($duration))->format('Ymd\THis\Z');

        // Stable UID shared with the subscription feed (FeedController) so calendar clients update the
        // existing entry rather than creating a duplicate; SEQUENCE bumps whenever a material detail
        // changes (EventRepo), and STATUS:CANCELLED marks a cancelled occurrence.
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'eventschedule.com';
        $uid = $date ? "event-{$event->id}-{$date}@{$domain}" : "event-{$event->id}@{$domain}";

        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Event Schedule//EN\r\nMETHOD:PUBLISH\r\nBEGIN:VEVENT\r\n";
        $ical .= 'UID:'.$uid."\r\n";
        $ical .= 'SEQUENCE:'.((int) $event->ical_sequence)."\r\n";
        $ical .= 'SUMMARY:'.$this->escapeIcalText($title)."\r\n";
        $ical .= 'DESCRIPTION:'.$this->escapeIcalText($description)."\r\n";
        $ical .= 'DTSTART:'.$startDate."\r\n";
        $ical .= 'DTEND:'.$endDate."\r\n";
        $ical .= 'LOCATION:'.$this->escapeIcalText($location)."\r\n";
        if ($event->is_cancelled) {
            $ical .= "STATUS:CANCELLED\r\n";
        }
        $ical .= "END:VEVENT\r\nEND:VCALENDAR";

        return response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="event.ics"',
        ]);
    }

    private function escapeIcalText(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\r", "\n"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'],
            $text
        );
    }

    public function storePoll(EventPollStoreRequest $request, $subdomain, $eventHash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }
        if (! $role->isPro()) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($eventHash));
        if (! $request->user()->canEditEvent($event)) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $pollCount = $event->polls()->count();
        if ($pollCount >= 5) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.max_polls_reached')], 422);
            }

            return redirect()->to(url()->previous().'#section-polls')->with('error', __('messages.max_polls_reached'));
        }

        $maxSort = $event->polls()->max('sort_order') ?? 0;

        $poll = EventPoll::create([
            'event_id' => $event->id,
            'question' => $request->input('question'),
            'options' => $request->input('options'),
            'sort_order' => $maxSort + 1,
            'allow_user_options' => $request->boolean('allow_user_options'),
            'require_option_approval' => $request->boolean('require_option_approval'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'hash' => UrlUtils::encodeId($poll->id),
                'question' => $poll->question,
                'options' => $poll->options,
                'is_active' => $poll->is_active,
                'allow_user_options' => $poll->allow_user_options,
                'require_option_approval' => $poll->require_option_approval,
                'pending_options' => $poll->pending_options ?? [],
                'votes_count' => 0,
                'results' => [],
                'poll_count' => $pollCount + 1,
            ]);
        }

        return redirect()->to(url()->previous().'#section-polls')->with('message', __('messages.poll_created'));
    }

    public function updatePoll(EventPollStoreRequest $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }
        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($eventHash));
        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $poll = EventPoll::where('event_id', $event->id)->findOrFail(UrlUtils::decodeId($pollHash));

        DB::transaction(function () use ($poll, $request) {
            $poll = EventPoll::lockForUpdate()->find($poll->id);

            if (! $poll) {
                return;
            }

            $data = [
                'question' => $request->input('question'),
                'allow_user_options' => $request->boolean('allow_user_options'),
                'require_option_approval' => $request->boolean('require_option_approval'),
            ];

            if (! $poll->votes()->exists()) {
                $data['options'] = $request->input('options');
            }

            $poll->update($data);
        });

        return redirect()->to(url()->previous().'#section-polls')->with('message', __('messages.poll_updated'));
    }

    public function deletePoll(Request $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }
        if (! $role->isPro()) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($eventHash));
        if (! $request->user()->canEditEvent($event)) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $poll = EventPoll::where('event_id', $event->id)->findOrFail(UrlUtils::decodeId($pollHash));
        $poll->delete();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('messages.poll_deleted'),
                'poll_count' => $event->polls()->count(),
            ]);
        }

        return redirect()->to(url()->previous().'#section-polls')->with('message', __('messages.poll_deleted'));
    }

    public function togglePoll(Request $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }
        if (! $role->isPro()) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($eventHash));
        if (! $request->user()->canEditEvent($event)) {
            if ($request->ajax()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $poll = EventPoll::where('event_id', $event->id)->findOrFail(UrlUtils::decodeId($pollHash));
        $poll->update(['is_active' => ! $poll->is_active]);

        $message = $poll->is_active ? __('messages.poll_reopened') : __('messages.poll_closed');

        if ($request->ajax()) {
            return response()->json([
                'is_active' => $poll->is_active,
                'message' => $message,
            ]);
        }

        return redirect()->to(url()->previous().'#section-polls')->with('message', $message);
    }

    public function votePoll(EventPollVoteRequest $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $event = Event::findOrFail(UrlUtils::decodeId($eventHash));

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            return response()->json(['error' => __('messages.not_authorized')], 404);
        }

        if ($event->is_draft || $event->is_private) {
            $user = auth()->user();
            $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
            if (! $isMemberOrAdmin) {
                return response()->json(['error' => __('messages.not_authorized')], 404);
            }
        }

        if (! auth()->check()) {
            return response()->json(['error' => __('messages.sign_in_to_vote')], 401);
        }

        $poll = EventPoll::where('event_id', $event->id)
            ->where('is_active', true)
            ->findOrFail(UrlUtils::decodeId($pollHash));

        $optionIndex = $request->input('option_index');
        $eventDate = $event->days_of_week ? ($request->input('event_date') ?: '') : '';

        $result = DB::transaction(function () use ($poll, $optionIndex, $eventDate) {
            $poll = EventPoll::lockForUpdate()->find($poll->id);

            if (! $poll || ! $poll->is_active) {
                return ['error' => __('messages.poll_closed'), 'status' => 422];
            }

            if ($optionIndex >= count($poll->options)) {
                return ['error' => __('messages.invalid_option'), 'status' => 422];
            }

            try {
                EventPollVote::create([
                    'event_poll_id' => $poll->id,
                    'user_id' => auth()->id(),
                    'option_index' => $optionIndex,
                    'event_date' => $eventDate,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] !== 1062) {
                    throw $e;
                }
                // Duplicate vote - return current results
            }

            return null;
        });

        if ($result) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        $results = $poll->getResults($eventDate);
        $totalVotes = $eventDate !== ''
            ? $poll->votes()->where('event_date', $eventDate)->count()
            : $poll->votes()->count();

        return response()->json([
            'success' => true,
            'options' => $poll->options,
            'results' => $results,
            'total_votes' => $totalVotes,
        ]);
    }

    public function suggestPollOption(Request $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! auth()->check()) {
            return response()->json(['error' => __('messages.sign_in_to_vote')], 401);
        }

        $request->validate([
            'label' => ['required', 'string', 'max:200'],
        ]);

        $event = Event::findOrFail(UrlUtils::decodeId($eventHash));

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            return response()->json(['error' => __('messages.not_authorized')], 404);
        }

        if ($event->is_draft) {
            $user = auth()->user();
            $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
            if (! $isMemberOrAdmin) {
                return response()->json(['error' => __('messages.not_authorized')], 404);
            }
        }

        if ($event->is_private) {
            $user = auth()->user();
            $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
            if (! $isMemberOrAdmin) {
                return response()->json(['error' => __('messages.not_authorized')], 404);
            }
        }

        $poll = EventPoll::where('event_id', $event->id)
            ->where('is_active', true)
            ->where('allow_user_options', true)
            ->findOrFail(UrlUtils::decodeId($pollHash));

        $label = trim($request->input('label'));
        $eventDate = $event->days_of_week ? ($request->input('event_date') ?: '') : '';

        $result = DB::transaction(function () use ($poll, $label, $eventDate) {
            $poll = EventPoll::lockForUpdate()->find($poll->id);

            if (! $poll || ! $poll->is_active || ! $poll->allow_user_options) {
                return ['error' => __('messages.not_authorized'), 'status' => 403];
            }

            $options = $poll->options ?? [];
            $pendingOptions = $poll->pending_options ?? [];

            // Check total options limit (existing + pending)
            if (count($options) + count($pendingOptions) >= 10) {
                return ['error' => __('messages.max_options_reached'), 'status' => 422];
            }

            // Check for duplicate in existing options
            $existingLabels = array_map('strtolower', $options);
            if (in_array(strtolower($label), $existingLabels)) {
                return ['error' => __('messages.duplicate_option'), 'status' => 422];
            }

            // Check for duplicate in pending options
            $pendingLabels = array_map(function ($p) {
                return strtolower($p['label']);
            }, $pendingOptions);
            if (in_array(strtolower($label), $pendingLabels)) {
                return ['error' => __('messages.duplicate_option'), 'status' => 422];
            }

            if (! $poll->require_option_approval) {
                // Add directly to options
                $options[] = $label;
                $poll->update(['options' => $options]);

                // Auto-vote for the suggested option
                $optionIndex = count($options) - 1;
                try {
                    EventPollVote::create([
                        'event_poll_id' => $poll->id,
                        'user_id' => auth()->id(),
                        'option_index' => $optionIndex,
                        'event_date' => $eventDate,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] !== 1062) {
                        throw $e;
                    }
                }

                return ['success' => true, 'options' => $options, 'message' => __('messages.option_suggested')];
            }

            // Add to pending
            $pendingOptions[] = [
                'label' => $label,
                'user_id' => auth()->id(),
                'event_date' => $eventDate,
                'created_at' => now()->toIso8601String(),
            ];
            $poll->update(['pending_options' => $pendingOptions]);

            return ['success' => true, 'message' => __('messages.option_suggested_approval')];
        });

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json($result);
    }

    public function approvePollOption(Request $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }
        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($eventHash));
        if (! $request->user()->canEditEvent($event)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'option_index' => ['required', 'integer', 'min:0'],
        ]);

        $poll = EventPoll::where('event_id', $event->id)->findOrFail(UrlUtils::decodeId($pollHash));
        $optionIndex = $request->input('option_index');

        $result = DB::transaction(function () use ($poll, $optionIndex) {
            $poll = EventPoll::lockForUpdate()->find($poll->id);
            if (! $poll) {
                return ['error' => __('messages.not_authorized'), 'status' => 403];
            }

            $pendingOptions = $poll->pending_options ?? [];
            if (! isset($pendingOptions[$optionIndex])) {
                return ['error' => __('messages.invalid_option'), 'status' => 422];
            }

            $options = $poll->options ?? [];
            if (count($options) >= 10) {
                return ['error' => __('messages.max_options_reached'), 'status' => 422];
            }

            $approved = $pendingOptions[$optionIndex];
            $options[] = $approved['label'];
            array_splice($pendingOptions, $optionIndex, 1);

            $poll->update([
                'options' => $options,
                'pending_options' => empty($pendingOptions) ? null : array_values($pendingOptions),
            ]);

            // Auto-vote for the user who suggested this option
            if (! empty($approved['user_id'])) {
                $newOptionIndex = count($options) - 1;
                $approvedEventDate = $approved['event_date'] ?? '';
                try {
                    EventPollVote::create([
                        'event_poll_id' => $poll->id,
                        'user_id' => $approved['user_id'],
                        'option_index' => $newOptionIndex,
                        'event_date' => $approvedEventDate,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] !== 1062) {
                        throw $e;
                    }
                }
            }

            return [
                'success' => true,
                'message' => __('messages.option_approved'),
                'options' => $options,
                'pending_options' => array_values($pendingOptions),
            ];
        });

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json($result);
    }

    public function rejectPollOption(Request $request, $subdomain, $eventHash, $pollHash)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (is_demo_role($role)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }
        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($eventHash));
        if (! $request->user()->canEditEvent($event)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'option_index' => ['required', 'integer', 'min:0'],
        ]);

        $poll = EventPoll::where('event_id', $event->id)->findOrFail(UrlUtils::decodeId($pollHash));
        $optionIndex = $request->input('option_index');

        $result = DB::transaction(function () use ($poll, $optionIndex) {
            $poll = EventPoll::lockForUpdate()->find($poll->id);
            if (! $poll) {
                return ['error' => __('messages.not_authorized'), 'status' => 403];
            }

            $pendingOptions = $poll->pending_options ?? [];
            if (! isset($pendingOptions[$optionIndex])) {
                return ['error' => __('messages.invalid_option'), 'status' => 422];
            }

            array_splice($pendingOptions, $optionIndex, 1);

            $poll->update([
                'pending_options' => empty($pendingOptions) ? null : array_values($pendingOptions),
            ]);

            return [
                'success' => true,
                'message' => __('messages.option_rejected'),
                'pending_options' => array_values($pendingOptions),
            ];
        });

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json($result);
    }
}
