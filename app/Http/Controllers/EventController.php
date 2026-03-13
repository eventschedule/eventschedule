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
use App\Jobs\SendQueuedEmail;
use App\Mail\EventAccepted;
use App\Mail\EventDeclined;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventPart;
use App\Models\EventPhoto;
use App\Models\EventPoll;
use App\Models\EventPollVote;
use App\Models\EventVideo;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\DeletedEventNotification;
use App\Repos\EventRepo;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Services\UsageTrackingService;
use App\Services\WebhookService;
use App\Utils\ColorUtils;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                \Log::warning('Failed to cancel boost campaign during event deletion', [
                    'campaign_id' => $campaign->id,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Capture webhook payload before deletion
        $webhookPayload = [
            'event' => 'event.deleted',
            'timestamp' => now()->toIso8601String(),
            'data' => $event->toApiData(),
        ];
        WebhookService::dispatch('event.deleted', $event, $webhookPayload);

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

    public function create(Request $request, $subdomain)
    {
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

        $event = new Event;
        $event->user_id = $user->id;
        $selectedMembers = [];

        // Check if we're cloning an event
        $clonedData = session('cloned_event');
        if ($clonedData) {
            // Populate event with cloned data
            foreach ($clonedData['event'] as $key => $value) {
                $event->$key = $value;
            }
            $event->user_id = $user->id;
            $event->creator_role_id = $role->id;

            // Set cloned tickets
            $event->tickets = array_map(function ($ticketData) {
                $ticket = new Ticket;
                foreach ($ticketData as $key => $value) {
                    $ticket->$key = $value;
                }

                return $ticket;
            }, $clonedData['tickets']);

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
                $event->custom_fields = $defaultTickets['custom_fields'] ?? null;
                $event->tickets = $defaultTickets['tickets'] ?? [new Ticket];
                $event->addons = $defaultTickets['addons'] ?? [];
                $defaultPromoCodes = $defaultTickets['promo_codes'] ?? [];
            } else {
                $event->ticket_currency_code = MoneyUtils::getCurrencyForCountry($role->country_code);
                $event->payment_method = 'cash';
                $event->tickets = [new Ticket];
            }

            // Load the last event created by the user with a category set and set its category
            $lastEvent = Event::where('user_id', $user->id)
                ->whereNotNull('category_id')
                ->orderBy('id', 'desc')
                ->first();
            if ($lastEvent) {
                $event->category_id = $lastEvent->category_id;
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
            // Parse the date in the user's timezone and set default time to 20:00
            $event->starts_at = Carbon::createFromFormat('Y-m-d', $request->date, $user->timezone)
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
            'event_categories' => get_translated_categories(),
            'clonedCurators' => $clonedCurators,
            'clonedCuratorGroups' => $clonedCuratorGroups,
            'isCloned' => $isCloned,
            'clonedParts' => $clonedParts,
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

        // Prepare cloned event data (copy fillable fields)
        $clonedEventData = [];
        $fillableFields = $event->getFillable();
        foreach ($fillableFields as $field) {
            if (! in_array($field, ['id', 'slug'])) {
                $clonedEventData[$field] = $event->$field;
            }
        }

        // Reset fields that shouldn't be cloned
        $clonedEventData['flyer_image_url'] = null;

        // Clone tickets (reset sold quantities)
        $clonedTickets = [];
        foreach ($event->tickets as $ticket) {
            $clonedTickets[] = [
                'type' => $ticket->type,
                'quantity' => $ticket->quantity,
                'price' => $ticket->price,
                'description' => $ticket->description,
                // sold is not cloned
            ];
        }
        if (empty($clonedTickets)) {
            $clonedTickets = [new \App\Models\Ticket];
        }

        // Clone add-ons (reset sold quantities)
        $clonedAddons = [];
        foreach ($event->addons as $addon) {
            $clonedAddons[] = [
                'type' => $addon->type,
                'quantity' => $addon->quantity,
                'price' => $addon->price,
                'description' => $addon->description,
            ];
        }

        // Prepare venue and members
        $venue = $event->venue;
        $selectedMembers = [];
        foreach ($event->roles as $each) {
            if ($each->isTalent()) {
                $selectedMembers[] = $each->toData();
            }
        }

        // Prepare curator data
        $curatorIds = [];
        $curatorGroups = [];
        foreach ($event->curators as $curator) {
            $curatorId = UrlUtils::encodeId($curator->id);
            $curatorIds[] = $curatorId;
            $groupId = $event->getGroupIdForSubdomain($curator->subdomain);
            if ($groupId) {
                $curatorGroups[$curatorId] = UrlUtils::encodeId($groupId);
            }
        }

        // Clone event parts
        $clonedParts = [];
        foreach ($event->parts as $part) {
            $clonedParts[] = [
                'name' => $part->name,
                'description' => $part->description,
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
            ];
        }

        // Store cloned data in session
        session([
            'cloned_event' => [
                'event' => $clonedEventData,
                'tickets' => $clonedTickets,
                'addons' => $clonedAddons,
                'venue_id' => $venue ? UrlUtils::encodeId($venue->id) : null,
                'selected_members' => $selectedMembers,
                'curators' => $curatorIds,
                'curator_groups' => $curatorGroups,
                'parts' => $clonedParts,
            ],
        ]);

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
            $event->tickets = [new Ticket];
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
            'event_categories' => get_translated_categories(),
            'pendingVideos' => $pendingVideos,
            'approvedVideos' => $approvedVideos,
            'pendingComments' => $pendingComments,
            'approvedComments' => $approvedComments,
            'pendingPhotos' => $pendingPhotos,
            'approvedPhotos' => $approvedPhotos,
            'polls' => $polls,
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
                'custom_fields' => $event->custom_fields,
                'tickets' => $event->tickets->map(function ($ticket) {
                    return [
                        'type' => $ticket->type,
                        'quantity' => $ticket->quantity,
                        'price' => $ticket->price,
                        'description' => $ticket->description,
                        'custom_fields' => $ticket->custom_fields,
                    ];
                })->toArray(),
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

        AuditService::log(AuditService::EVENT_UPDATE, auth()->id(), 'Event', $event->id, null, null, $event->name);

        return redirect(route('role.view_admin', $data))
            ->with('message', __('messages.event_updated'));
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
                'custom_fields' => $event->custom_fields,
                'tickets' => $event->tickets->map(function ($ticket) {
                    return [
                        'type' => $ticket->type,
                        'quantity' => $ticket->quantity,
                        'price' => $ticket->price,
                        'description' => $ticket->description,
                        'custom_fields' => $ticket->custom_fields,
                    ];
                })->toArray(),
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

        AuditService::log(AuditService::EVENT_CREATE, auth()->id(), 'Event', $event->id, null, null, $event->name);

        return redirect(route('role.view_admin', $data))
            ->with('message', __('messages.event_created'));
    }

    public function curate(Request $request, $subdomain, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        $role = Role::subdomain($subdomain)->firstOrFail();

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
            $venues = $roles->filter(function ($item) {
                if ($item->pivot->level == 'follower' && ! $item->acceptEventRequests()) {
                    return false;
                }

                return $item->isVenue();
            })->map(function ($item) {
                return $item->toData();
            });
            $venues = array_values($venues->sortBy('name')->toArray());
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
        if (! auth()->check()) {
            session()->put('pending_request', $subdomain);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        // Store guest language for auth flow
        if (session()->has('translate')) {
            session()->put('guest_language', 'en');
        } elseif ($request->lang && is_valid_language_code($request->lang)) {
            session()->put('guest_language', $request->lang);
        } elseif (is_valid_language_code($role->language_code)) {
            session()->put('guest_language', $role->language_code);
        }

        if (! auth()->check() && $role->require_account) {
            session(['pending_request' => $subdomain]);

            return redirect(app_url(route('sign_up', [], false)));
        }

        if ($request->lang) {
            // Validate the language code before setting it
            if (is_valid_language_code($request->lang)) {
                app()->setLocale($request->lang);

                if ($request->lang == 'en') {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                // If invalid language code, redirect to the same URL without the lang parameter
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale('en');
        } else {
            // Validate the language code from database before setting it
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }
        }

        $currencies = json_decode(file_get_contents(base_path('storage/currencies.json')));

        return view('event.guest-import', ['role' => $role, 'isGuest' => true, 'venues' => [], 'currencies' => $currencies, 'defaultCurrency' => MoneyUtils::getCurrencyForCountry($role->country_code)]);
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

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
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

            $parts = GeminiUtils::parseEventParts($imageData, $textDescription, $aiPrompt);

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
                } elseif (config('filesystems.default') == 'local') {
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

        try {
            $imageData = GeminiUtils::generateEventFlyer($event, $request->input('style_instructions'), $role, $request->input('custom_prompt'));

            if (! $imageData) {
                return response()->json(['error' => __('messages.ai_flyer_generation_failed')], 500);
            }

            $filename = ImageUtils::saveImageData($imageData, 'generated_flyer.png', 'flyer_');

            if ($eventId) {
                // Delete old flyer from storage if exists
                if ($event->flyer_image_url) {
                    $path = $event->getAttributes()['flyer_image_url'];
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }

                $event->flyer_image_url = $filename;
                $event->save();
            }

            UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_FLYER, $role->id);

            // Build full URL for the flyer
            if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                $flyerUrl = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
            } elseif (config('filesystems.default') == 'local') {
                $flyerUrl = url('/storage/'.$filename);
            } else {
                $flyerUrl = $filename;
            }

            $response = [
                'success' => true,
                'flyer_image_url' => $flyerUrl,
                'delete_url' => route('event.delete_image', ['subdomain' => $subdomain]),
            ];

            if (! $eventId) {
                $response['flyer_image_filename'] = $filename;
            }

            return response()->json($response);
        } catch (\App\Exceptions\ContentModerationException $e) {
            return response()->json(['error' => __('messages.ai_content_moderation_blocked')], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_flyer_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI flyer generation failed: '.$e->getMessage(), [
                'role_id' => $role->id,
            ]);

            return response()->json(['error' => __('messages.ai_flyer_generation_failed')], 500);
        }
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

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
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
        // \Log::info($request->all());
        // return redirect()->back();

        $role = Role::subdomain($subdomain)->firstOrFail();

        $event = $this->eventRepo->saveEvent($role, $request, null, false);

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
            $venueData = $venue->toData();
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
        // Handle user creation if requested
        if ($request->input('create_account')) {
            $this->createAndLoginUser($request);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->acceptEventRequests()) {
            abort(403, __('messages.not_authorized'));
        }

        $event = $this->eventRepo->saveEvent($role, $request, null, false);

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

        // Clear the pending request session
        session()->forget('pending_request');
        session()->forget('pending_request_allow_guest');
        session()->forget('pending_request_form');

        return response()->json([
            'success' => true,
            'event' => [
                'view_url' => $event->getGuestUrl($subdomain),
                'message' => __('messages.event_created_guest'),
            ],
        ]);
    }

    /**
     * Create a new user and log them in
     */
    private function createAndLoginUser(Request $request)
    {
        // Validate user creation data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255', 'unique:users'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Create the user
        $utmParams = session('utm_params', []);

        // Fall back to cookie if session has no UTM data
        if (empty($utmParams) && $request->cookie('utm_params')) {
            $utmParams = json_decode($request->cookie('utm_params'), true) ?? [];
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => 'America/New_York', // Default timezone
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
        ]);

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page', 'guest_language']);

        // Mark email as verified for guest users (they're already using the system)
        $user->email_verified_at = now();
        $user->save();

        // Log the user in
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
            session()->put('guest_language', 'en');
        } elseif ($request->lang && is_valid_language_code($request->lang)) {
            session()->put('guest_language', $request->lang);
        } elseif (is_valid_language_code($role->language_code)) {
            session()->put('guest_language', $role->language_code);
        }

        if ($request->lang) {
            if (is_valid_language_code($request->lang)) {
                app()->setLocale($request->lang);

                if ($request->lang == 'en') {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale('en');
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

        // Build starts_at from date + time
        $startsAt = null;
        if ($request->date && $request->start_time) {
            $timezone = $user ? $user->timezone : ($role->timezone ?? 'America/New_York');
            $startsAt = Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->start_time, $timezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        }

        // Create the event
        $event = new Event;
        $event->name = $request->event_name ?: __('messages.booking_request');
        $event->description = $request->description;
        $event->starts_at = $startsAt;
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
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        $youtubeUrl = trim($request->input('youtube_url'));
        $embedUrl = UrlUtils::getYouTubeEmbed($youtubeUrl);

        if (! $embedUrl) {
            return redirect()->back()->with('error', __('messages.invalid_youtube_url'));
        }

        if (! auth()->check()) {
            session()->put('pending_fan_content', [
                'type' => 'video',
                'subdomain' => $subdomain,
                'event_hash' => $event_hash,
                'youtube_url' => $youtubeUrl,
                'event_part_id' => $request->input('event_part_id'),
                'event_date' => $request->input('event_date'),
                'return_url' => url()->previous(),
            ]);

            return redirect(app_url(route('sign_up', [], false)));
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
        if ($event->is_private && ! $isMemberOrAdmin) {
            abort(404);
        }

        $comment = strip_tags(trim($request->input('comment')));

        if (! auth()->check()) {
            session()->put('pending_fan_content', [
                'type' => 'comment',
                'subdomain' => $subdomain,
                'event_hash' => $event_hash,
                'comment' => $comment,
                'event_part_id' => $request->input('event_part_id'),
                'event_date' => $request->input('event_date'),
                'return_url' => url()->previous(),
            ]);

            return redirect(app_url(route('sign_up', [], false)));
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

            session()->put('pending_fan_content', [
                'type' => 'photo',
                'subdomain' => $subdomain,
                'event_hash' => $event_hash,
                'temp_filename' => $tempFilename,
                'extension' => $extension,
                'event_part_id' => $request->input('event_part_id'),
                'event_date' => $request->input('event_date'),
                'return_url' => url()->previous(),
                'return_to' => $request->input('return_to'),
            ]);

            return redirect(app_url(route('sign_up', [], false)));
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
                'starts_at' => $e->starts_at ? Carbon::parse($e->starts_at)->format('D, M j, Y') : null,
                'image_url' => $e->getImageUrl(),
                'view_url' => route('event.view_guest', ['subdomain' => $subdomain, 'slug' => $e->slug]),
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
            'view_url' => route('event.view_guest', ['subdomain' => $subdomain, 'slug' => $event->slug]),
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

                if ($request->lang == 'en') {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');
                }
            } else {
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale('en');
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

        if ($event->is_private && $role->isEnterprise() && ! $event->isPasswordProtected() && (! $user || (! $user->isMember($subdomain) && ! $user->isAdmin()))) {
            return redirect($role->getGuestUrl());
        }

        // Password gate
        $bypassPassword = $isMemberOrAdmin || session()->has('event_password_'.$event->id);
        if ($event->isPasswordProtected() && $role->isEnterprise() && ! $bypassPassword) {
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
        $startAt = $event->getStartDateTime($date);
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->addSeconds($duration * 3600)->format('Ymd\THis\Z');

        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n";
        $ical .= 'SUMMARY:'.$this->escapeIcalText($title)."\r\n";
        $ical .= 'DESCRIPTION:'.$this->escapeIcalText($description)."\r\n";
        $ical .= 'DTSTART:'.$startDate."\r\n";
        $ical .= 'DTEND:'.$endDate."\r\n";
        $ical .= 'LOCATION:'.$this->escapeIcalText($location)."\r\n";
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

        if ($event->is_private) {
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
