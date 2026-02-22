<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventCommentSubmitRequest;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventParseRequest;
use App\Http\Requests\EventPartsSaveRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Requests\EventVideoSubmitRequest;
use App\Jobs\SendQueuedEmail;
use App\Mail\EventAccepted;
use App\Mail\EventDeclined;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventPart;
use App\Models\EventVideo;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\DeletedEventNotification;
use App\Repos\EventRepo;
use App\Rules\NoFakeEmail;
use App\Services\AuditService;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();

        $venue = $role->isVenue() ? $role : null;
        $schedule = $role->isTalent() ? $role : null;
        $curator = $role->isCurator() ? $role : null;

        if (! $role->email_verified_at) {
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
                $event->custom_fields = $defaultTickets['custom_fields'] ?? null;
                $event->tickets = $defaultTickets['tickets'] ?? [new Ticket];
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
        ]);
    }

    public function editAdmin(Request $request, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);
        $user = $request->user();
        $subdomain = null;

        foreach ($event->roles as $each) {
            if ($user->isMember($each->subdomain)) {
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
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['tickets', 'roles', 'creatorRole', 'curators', 'parts'])->findOrFail($event_id);
        $user = $request->user();

        if ($user->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->email_verified_at) {
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
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators', 'parts'])->findOrFail($event_id);
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

        if (! $role->email_verified_at) {
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
        ]);
    }

    public function update(EventUpdateRequest $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);

        if ($request->user()->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->eventRepo->saveEvent($role, $request, $event);

        if ($request->has('save_default_tickets')) {
            $role = Role::subdomain($subdomain)->firstOrFail();
            $defaultTickets = [
                'currency_code' => $event->ticket_currency_code,
                'payment_method' => $event->payment_method,
                'payment_instructions' => $event->payment_instructions,
                'expire_unpaid_tickets' => $event->expire_unpaid_tickets,
                'ticket_notes' => $event->ticket_notes,
                'terms_url' => $event->terms_url,
                'total_tickets_mode' => $event->total_tickets_mode,
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
            ];
            $role->default_tickets = json_encode($defaultTickets);
            $role->save();
        }

        if ($request->has('agenda_ai_prompt')) {
            $event->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $event->save();
        }
        if ($request->input('save_ai_prompt_default')) {
            $role->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $role->agenda_show_times = $request->boolean('agenda_show_times');
            $role->agenda_show_description = $request->boolean('agenda_show_description');
            $role->save();
        }

        if ($event->starts_at) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
        } else {
            $date = Carbon::now();
        }

        // A user may be using a different subdomain to edit an event
        // if they clicked on the edit link from the guest view
        if (! auth()->user()->isMember($subdomain)) {
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
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($user->isMember($subdomain)) {
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
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($user->isMember($subdomain)) {
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
        if (! auth()->user()->isMember($subdomain)) {
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
            if ($user->isMember($subdomain)) {
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

    public function store(EventCreateRequest $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $event = $this->eventRepo->saveEvent($role, $request, null);

        if ($request->has('save_default_tickets')) {
            $role = Role::subdomain($subdomain)->firstOrFail();
            $defaultTickets = [
                'currency_code' => $event->ticket_currency_code,
                'payment_method' => $event->payment_method,
                'payment_instructions' => $event->payment_instructions,
                'expire_unpaid_tickets' => $event->expire_unpaid_tickets,
                'ticket_notes' => $event->ticket_notes,
                'terms_url' => $event->terms_url,
                'total_tickets_mode' => $event->total_tickets_mode,
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
            ];
            $role->default_tickets = json_encode($defaultTickets);
            $role->save();
        }

        if ($request->input('agenda_ai_prompt')) {
            $event->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $event->save();
        }
        if ($request->input('save_ai_prompt_default')) {
            $role->agenda_ai_prompt = $request->input('agenda_ai_prompt');
            $role->agenda_show_times = $request->boolean('agenda_show_times');
            $role->agenda_show_description = $request->boolean('agenda_show_description');
            $role->save();
        }

        session()->forget('pending_request');
        session()->forget('pending_request_allow_guest');

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

        // Check if the user is authorized to curate events for this role
        if ((! auth()->user() || ! auth()->user()->isMember($subdomain)) && ! $role->acceptEventRequests()) {
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
        $role->events()->attach($event->id, ['is_accepted' => auth()->user() && auth()->user()->isMember($subdomain) ? true : null]);

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

        if (! auth()->user()->isMember($subdomain)) {
            return back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->events()->detach($event->id);

        return back()->with('message', __('messages.uncurate_event'));
    }

    public function showImport(Request $request, $subdomain)
    {
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

        if (! auth()->check() && $role->require_account) {
            session(['pending_request' => $subdomain]);
            return redirect(route('sign_up'));
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
        $details = request()->input('event_details');
        $file = null;

        // Handle image data if provided
        if ($request->hasFile('details_image')) {
            $file = $request->file('details_image');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

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
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isAdmin() && ! $role->isEnterprise()) {
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

            return response()->json($parts);
        } catch (\Exception $e) {
            \Log::error('Event parts parsing failed: '.$e->getMessage());

            return response()->json(['error' => __('messages.event_parsing_failed')], 500);
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

        // Clear the pending request session
        session()->forget('pending_request');
        session()->forget('pending_request_allow_guest');

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
            'language_code' => 'en', // Default language
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
            'utm_content' => $utmParams['utm_content'] ?? null,
            'utm_term' => $utmParams['utm_term'] ?? null,
            'referrer_url' => session('utm_referrer_url') ?? $request->cookie('utm_referrer_url'),
            'landing_page' => session('utm_landing_page') ?? $request->cookie('utm_landing_page'),
        ]);

        session()->forget(['utm_params', 'utm_referrer_url', 'utm_landing_page']);

        // Mark email as verified for guest users (they're already using the system)
        $user->email_verified_at = now();
        $user->save();

        // Log the user in
        Auth::login($user);

        return $user;
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
        $event = Event::with('parts')->findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
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

            return redirect()->route('sign_up');
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

        if (! $isApproved) {
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
        $event = Event::with('parts')->findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
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

            return redirect()->route('sign_up');
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

        if (! $isApproved) {
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

    public function scanAgenda(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isAdmin() && ! $role->isEnterprise()) {
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

        $eventsData = $events->map(function ($e) {
            return [
                'id' => UrlUtils::encodeId($e->id),
                'name' => $e->name,
                'starts_at' => $e->starts_at ? Carbon::parse($e->starts_at)->format('D, M j, Y') : null,
                'image_url' => $e->getImageUrl(),
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
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isAdmin() && ! $role->isEnterprise()) {
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

    public function downloadIcal($subdomain, $slug, $id, $date = null)
    {
        $event = Event::find(UrlUtils::decodeId($id));

        if (! $event) {
            abort(404);
        }

        $title = $event->getTitle();
        $description = $event->description_html ? strip_tags($event->description_html) : ($event->role() ? strip_tags($event->role()->description_html) : '');
        $location = $event->venue ? $event->venue->bestAddress() : '';
        $duration = $event->duration > 0 ? $event->duration : 2;
        $startAt = $event->getStartDateTime($date);
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->addSeconds($duration * 3600)->format('Ymd\THis\Z');

        $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n";
        $ical .= 'SUMMARY:'.$title."\r\n";
        $ical .= 'DESCRIPTION:'.$description."\r\n";
        $ical .= 'DTSTART:'.$startDate."\r\n";
        $ical .= 'DTEND:'.$endDate."\r\n";
        $ical .= 'LOCATION:'.$location."\r\n";
        $ical .= "END:VEVENT\r\nEND:VCALENDAR";

        return response($ical, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="event.ics"',
        ]);
    }
}
