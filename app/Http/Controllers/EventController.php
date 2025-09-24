<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\EventRequestNotification;
use App\Notifications\RequestDeclinedNotification;
use App\Notifications\RequestAcceptedNotification;
use App\Notifications\DeletedEventNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Utils\ColorUtils;
use App\Utils\NotificationUtils;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\EventRole;
use App\Models\User;
use App\Models\Ticket;
use App\Utils\UrlUtils;
use App\Utils\GeminiUtils;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Repos\EventRepo;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Rules\NoFakeEmail;

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

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($request->image_type == 'flyer') {
            if ($event->flyer_image_url) {
                $path = $event->getAttributes()['flyer_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);

                $event->flyer_image_url = null;
                $event->save();
            }    
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

        // Check if user is the creator of the event
        if ($event->user_id !== $user->id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Clear YouTube videos for unclaimed roles in this event
        foreach ($event->roles as $role) {
            if (!$role->isClaimed() && $role->youtube_links && $role->id == $role_id) {
                $role->youtube_links = null;
                $role->save();
            }
        }

        return redirect()->back()->with('message', __('messages.videos_cleared'));
    }

    protected function handleEventDeletion(Event $event, User $user): void
    {
        $event->loadMissing(['roles.members', 'venue.members', 'creatorRole.members', 'sales']);

        foreach ($event->roles as $roleModel) {
            if ($roleModel->isTalent()) {
                $members = NotificationUtils::roleMembers($roleModel);

                if ($members->isNotEmpty()) {
                    Notification::send($members, new DeletedEventNotification($event, $user, 'talent', $roleModel));
                }
            }
        }

        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        foreach ($organizerRoles as $organizerRole) {
            $members = NotificationUtils::roleMembers($organizerRole);

            if ($members->isNotEmpty()) {
                Notification::send($members, new DeletedEventNotification($event, $user, 'organizer', $organizerRole));
            }
        }

        $purchaserEmails = NotificationUtils::purchaserEmails($event);

        if ($purchaserEmails->isNotEmpty()) {
            Notification::route('mail', $purchaserEmails->all())
                ->notify(new DeletedEventNotification($event, $user, 'purchaser', $event->venue));
        }

        $event->delete();
    }

    public function delete(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $this->handleEventDeletion($event, $user);

        $data = [
            'subdomain' => $subdomain,
            'tab' => 'schedule',
        ];

        return redirect(route('role.view_admin', $data))
                ->with('message', __('messages.event_deleted'));
    }

    public function destroyFromHome(Request $request, $hash)
    {
        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $user->canEditEvent($event)) {
            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        $this->handleEventDeletion($event, $user);

        return redirect()->route('home')->with('message', __('messages.event_deleted'));
    }

    public function view(Request $request, $hash)
    {
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['roles', 'creatorRole', 'tickets', 'user'])->findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        $event->loadMissing('venue');

        $startAt = $event->starts_at ? $event->getStartDateTime(null, true) : null;
        $endAt = null;

        if ($startAt && $event->duration) {
            $endAt = $startAt->copy()->addHours($event->duration);
        }

        $recurringDays = [];

        if ($event->days_of_week) {
            $baseDate = Carbon::now()->startOfWeek(Carbon::SUNDAY);

            foreach (str_split($event->days_of_week) as $index => $value) {
                if ($value === '1') {
                    $recurringDays[] = $baseDate->copy()->addDays($index)
                        ->locale(app()->getLocale())
                        ->translatedFormat('l');
                }
            }
        }

        $categories = get_translated_categories();
        $categoryName = $event->category_id && isset($categories[$event->category_id])
            ? $categories[$event->category_id]
            : null;

        return view('event.view', [
            'event' => $event,
            'venue' => $event->venue,
            'talents' => $event->roles->filter(function ($role) {
                return $role->isTalent();
            }),
            'curators' => $event->roles->filter(function ($role) {
                return $role->isCurator();
            }),
            'startAt' => $startAt,
            'endAt' => $endAt,
            'recurringDays' => $recurringDays,
            'categoryName' => $categoryName,
        ]);
    }

    public function cloneConfirm(Request $request, $hash)
    {
        $eventId = UrlUtils::decodeId($hash);
        $event = Event::with(['roles', 'creatorRole', 'tickets', 'user'])->findOrFail($eventId);
        $user = $request->user();

        if (! $user || (! $user->canEditEvent($event) && ! $user->isAdmin())) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event->loadMissing('venue');

        $startAt = $event->starts_at ? $event->getStartDateTime(null, true) : null;
        $endAt = null;

        if ($startAt && $event->duration) {
            $endAt = $startAt->copy()->addHours($event->duration);
        }

        $recurringDays = [];

        if ($event->days_of_week) {
            $baseDate = Carbon::now()->startOfWeek(Carbon::SUNDAY);

            foreach (str_split($event->days_of_week) as $index => $value) {
                if ($value === '1') {
                    $recurringDays[] = $baseDate->copy()->addDays($index)
                        ->locale(app()->getLocale())
                        ->translatedFormat('l');
                }
            }
        }

        $categories = get_translated_categories();
        $categoryName = $event->category_id && isset($categories[$event->category_id])
            ? $categories[$event->category_id]
            : null;

        return view('event.clone-confirm', [
            'event' => $event,
            'venue' => $event->venue,
            'talents' => $event->roles->filter(function ($role) {
                return $role->isTalent();
            }),
            'curators' => $event->roles->filter(function ($role) {
                return $role->isCurator();
            }),
            'startAt' => $startAt,
            'endAt' => $endAt,
            'recurringDays' => $recurringDays,
            'categoryName' => $categoryName,
        ]);
    }

    public function clone(Request $request, $hash)
    {
        $eventId = UrlUtils::decodeId($hash);
        $event = Event::with(['roles', 'tickets'])->findOrFail($eventId);
        $user = $request->user();

        if (! $user || (! $user->canEditEvent($event) && ! $user->isAdmin())) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $clonedEvent = DB::transaction(function () use ($event) {
            $newEvent = $event->replicate();
            $newEvent->created_at = now();
            $newEvent->updated_at = now();
            $newEvent->google_event_id = null;
            $newEvent->save();

            foreach ($event->roles as $role) {
                $pivotAttributes = collect($role->pivot->getAttributes())
                    ->except(['id', 'event_id', 'role_id']);

                $newEvent->roles()->attach($role->id, $pivotAttributes->toArray());
            }

            foreach ($event->tickets as $ticket) {
                $newTicket = $ticket->replicate();
                $newTicket->event_id = $newEvent->id;
                $newTicket->sold = null;
                $newTicket->created_at = now();
                $newTicket->updated_at = now();
                $newTicket->save();
            }

            return $newEvent;
        });

        return redirect()->route('event.edit_admin', [
            'hash' => UrlUtils::encodeId($clonedEvent->id),
        ])->with('message', __('messages.event_cloned'));
    }


    public function create(Request $request, $subdomain)
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        
        $venue = $role->isVenue() ? $role : null;
        $schedule = $role->isTalent() ? $role : null;
        $curator = $role->isCurator() ? $role : null;
        $preselectedCurators = [];

        if (! $role->email_verified_at) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        $event = new Event;
        $event->user_id = $user->id;
        $selectedMembers = [];
        
        if ($role->default_tickets) {
            $defaultTickets = json_decode($role->default_tickets, true);
            $event->ticket_currency_code = $defaultTickets['currency_code'] ?? 'USD';
            $event->payment_method = $defaultTickets['payment_method'] ?? 'cash';
            $event->payment_instructions = $defaultTickets['payment_instructions'] ?? null;
            $event->expire_unpaid_tickets = $defaultTickets['expire_unpaid_tickets'] ?? false;
            $event->ticket_notes = $defaultTickets['ticket_notes'] ?? null;
            $event->total_tickets_mode = $defaultTickets['total_tickets_mode'] ?? 'individual';
            $event->tickets = $defaultTickets['tickets'] ?? [new Ticket()];
        } else {
            $event->ticket_currency_code = 'USD';
            $event->payment_method = 'cash';
            $event->tickets = [new Ticket()];
        }

        if ($schedule) {
            $selectedMembers = [$schedule->toData()];
        }

        // Allow selecting a venue via query parameter when launching from the events page
        if ($request->filled('venue')) {
            $venueId = UrlUtils::decodeId($request->query('venue'));

            if ($venueId) {
                $userVenue = $user->roles()
                    ->where('roles.id', $venueId)
                    ->where('roles.type', 'venue')
                    ->first();

                if ($userVenue) {
                    $venue = $userVenue;
                }
            }
        }

        // Allow selecting additional talent schedules via query parameters
        $requestedTalents = collect($request->query('talents', []));

        if ($request->filled('talent')) {
            $requestedTalents = $requestedTalents->merge([$request->query('talent')]);
        }

        if ($requestedTalents->isNotEmpty()) {
            $existingIds = collect($selectedMembers)->pluck('id');

            $requestedTalents
                ->filter()
                ->each(function ($encodedId) use ($user, &$selectedMembers, &$existingIds) {
                    $decodedId = UrlUtils::decodeId($encodedId);

                    if (! $decodedId) {
                        return;
                    }

                    $talentRole = $user->roles()
                        ->where('roles.id', $decodedId)
                        ->where('roles.type', 'talent')
                        ->first();

                    if ($talentRole && ! $existingIds->contains($talentRole->encodeId())) {
                        $selectedMembers[] = $talentRole->toData();
                        $existingIds->push($talentRole->encodeId());
                    }
                });
        }

        // Allow pre-selecting curators from the events page
        $requestedCurators = collect($request->query('curators', []));

        if ($request->filled('curator')) {
            $requestedCurators = $requestedCurators->merge([$request->query('curator')]);
        }

        if ($requestedCurators->isNotEmpty()) {
            $requestedCurators
                ->filter()
                ->each(function ($encodedId) use (&$preselectedCurators, $user, &$curator) {
                    $decodedId = UrlUtils::decodeId($encodedId);

                    if (! $decodedId) {
                        return;
                    }

                    $curatorRole = $user->roles()
                        ->where('roles.id', $decodedId)
                        ->where('roles.type', 'curator')
                        ->first();

                    if ($curatorRole) {
                        $preselectedCurators[] = $curatorRole->encodeId();

                        if (! $curator) {
                            $curator = $curatorRole;
                        }
                    }
                });
        }

        if ($request->date) {
            $defaultTime = Carbon::now($user->timezone)->setTime(20, 0, 0);
            $utcTime = $defaultTime->setTimezone('UTC');
            $event->starts_at = $request->date . $utcTime->format('H:i:s');
        }

        $roles = $user->roles()->get();
    
        $venues = $roles->filter(function($item) {
            if ($item->pivot->level == 'follower' && ! $item->acceptEventRequests()) {
                return false;
            }

            return $item->isVenue();
        })->map(function ($item) {
            return $item->toData();
        });
    
        $members = $roles->filter(function($item) {
            return $item->isTalent();
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->sortBy('name')->toArray());
        $members = array_values($members->sortBy('name')->toArray());
        
        $currencies = file_get_contents(base_path('storage/currencies.json'));
        $currencies = json_decode($currencies);

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
            'preselectedCurators' => $preselectedCurators,
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

        return redirect(route('event.edit', ['subdomain' => $subdomain, 'hash' => $hash]));
    }

    public function edit(Request $request, $subdomain, $hash)
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);
        $user = $request->user();

        if (! $user->canEditEvent($event)) {
            return redirect()->back();
        }

        if ($event->tickets->count() == 0) {
            $event->tickets = [
                [
                    new Ticket(),
                ]
            ];
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
    
        $venues = $roles->filter(function($item) {            
            if ($item->pivot->level == 'follower' && ! $item->acceptEventRequests()) {
                return false;
            }

            return $item->isVenue();
        })->map(function ($item) {
            return $item->toData();
        });
    
        $members = $roles->filter(function($item) {
            return $item->isTalent();
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->sortBy('name')->toArray());
        $members = array_values($members->sortBy('name')->toArray());
    
        $currencies = file_get_contents(base_path('storage/currencies.json'));
        $currencies = json_decode($currencies);

        $eventUrlData = $event->getGuestUrlData($subdomain, false, true);
        $matchingEvent = $this->eventRepo->getEvent($eventUrlData['subdomain'], $eventUrlData['slug'], false);        
        $isUnique = ! $matchingEvent || $matchingEvent->id == $event->id ? true : false;

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
            'isUnique' => $isUnique,
            'event_categories' => get_translated_categories(),
        ]);
    }

    public function update(EventUpdateRequest $request, $subdomain, $hash)
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }   

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::with(['creatorRole', 'curators'])->findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
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
                'total_tickets_mode' => $event->total_tickets_mode,
                'tickets' => $event->tickets->map(function($ticket) {
                    return [
                        'type' => $ticket->type,
                        'quantity' => $ticket->quantity,
                        'price' => $ticket->price,
                        'description' => $ticket->description,
                    ];
                })->toArray()
            ];
            $role->default_tickets = json_encode($defaultTickets);
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
        }

        $event->load(['roles.members', 'venue.members', 'creatorRole.members']);

        foreach ($event->roles as $talentRole) {
            if ($talentRole->isTalent()) {
                $members = NotificationUtils::roleMembers($talentRole);

                if ($members->isNotEmpty()) {
                    Notification::send($members, new RequestAcceptedNotification($event, $user, 'talent', $talentRole));
                }
            }
        }

        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        foreach ($organizerRoles as $organizerRole) {
            $members = NotificationUtils::roleMembers($organizerRole);

            if ($members->isNotEmpty()) {
                Notification::send($members, new RequestAcceptedNotification($event, $user, 'organizer', $organizerRole));
            }
        }

        return redirect('/' . $subdomain . '/requests')
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
        }

        $event->load(['roles.members', 'venue.members', 'creatorRole.members']);

        foreach ($event->roles as $talentRole) {
            if ($talentRole->isTalent()) {
                $members = NotificationUtils::roleMembers($talentRole);

                if ($members->isNotEmpty()) {
                    Notification::send($members, new RequestDeclinedNotification($event, $user, 'talent', $talentRole));
                }
            }
        }

        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        foreach ($organizerRoles as $organizerRole) {
            $members = NotificationUtils::roleMembers($organizerRole);

            if ($members->isNotEmpty()) {
                Notification::send($members, new RequestDeclinedNotification($event, $user, 'organizer', $organizerRole));
            }
        }

        if ($request->redirect_to == 'schedule') {
            return redirect('/' . $subdomain . '/schedule')
                ->with('message', __('messages.request_declined'));
        } else {
            return redirect('/' . $subdomain . '/requests')
                ->with('message', __('messages.request_declined'));
        }        
    }

    public function store(EventCreateRequest $request, $subdomain)
    {
        if (! is_hosted_or_admin()) {
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
                'total_tickets_mode' => $event->total_tickets_mode,
                'tickets' => $event->tickets->map(function($ticket) {
                    return [
                        'type' => $ticket->type,
                        'quantity' => $ticket->quantity,
                        'price' => $ticket->price,
                        'description' => $ticket->description,
                    ];
                })->toArray()
            ];
            $role->default_tickets = json_encode($defaultTickets);
            $role->save();
        }

        session()->forget('pending_request');

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
        
        // Check if the user is authorized to curate events for this role
        if ((!auth()->user() || !auth()->user()->isMember($subdomain)) && ! $role->acceptEventRequests()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.not_authorized')
                ], 403);
            }
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Check if the event is already curated by this role
        if ($role->events()->where('event_id', $event->id)->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.event_already_curated')
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

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->events()->detach($event->id);

        return back()->with('message', __('messages.uncurate_event'));
    }

    public function showImport(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        return view('event.admin-import', ['role' => $role]);
    }

    public function showGuestImport(Request $request, $subdomain)
    {
        if (! auth()->check()) {
            session()->put('pending_request', $subdomain);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

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
        } else if (session()->has('translate')) {
            app()->setLocale('en');
        } else {
            // Validate the language code from database before setting it
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }
        }

        return view('event.guest-import', ['role' => $role, 'isGuest' => true]);
    }

    public function parse(Request $request, $subdomain)
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function guestParse(Request $request, $subdomain)
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function import(Request $request, $subdomain)
    {
        //\Log::info($request->all());
        //return redirect()->back();
        
        $role = Role::subdomain($subdomain)->firstOrFail();
                
        $event = $this->eventRepo->saveEvent($role, $request, null);

        if ($request->social_image) {
            $file = new \Illuminate\Http\UploadedFile($request->social_image, basename($request->social_image));
            $filename = strtolower('flyer_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $event->flyer_image_url = $filename;
            $event->save();
        }

        return response()->json([
            'success' => true,
            'event' => [
                'view_url' => $event->getGuestUrl($subdomain),
                'edit_url' => route('event.edit', ['subdomain' => $subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            ]
        ]);
    }

    public function guestImport(Request $request, $subdomain)
    {
        // Handle user creation if requested
        if ($request->input('create_account')) {
            $this->createAndLoginUser($request);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
                
        $event = $this->eventRepo->saveEvent($role, $request, null);

        if ($request->social_image) {
            $file = new \Illuminate\Http\UploadedFile($request->social_image, basename($request->social_image));
            $filename = strtolower('flyer_' . Str::random(32)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $event->flyer_image_url = $filename;
            $event->save();
        }

        // Clear the pending request session
        session()->forget('pending_request');

        return response()->json([
            'success' => true,
            'event' => [
                'view_url' => $event->getGuestUrl($subdomain),
                'message' => __('messages.event_created_guest'),
            ]
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
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'timezone' => 'America/New_York', // Default timezone
            'language_code' => 'en', // Default language
        ]);

        // Mark email as verified for guest users (they're already using the system)
        $user->email_verified_at = now();
        $user->save();

        // Log the user in
        Auth::login($user);

        return $user;
    }

    public function uploadImage(Request $request, $subdomain)
    {
        $file = $request->file('image');
        $filename = '/tmp/event_' . strtolower(Str::random(32)) . '.' . $file->getClientOriginalExtension();
        move_uploaded_file($file->getPathname(), $filename);

        return response()->json(['success' => true, 'filename' => $filename]);
    }

    public function guestUploadImage(Request $request, $subdomain)
    {
        $file = $request->file('image');
        $filename = '/tmp/event_' . strtolower(Str::random(32)) . '.' . $file->getClientOriginalExtension();
        move_uploaded_file($file->getPathname(), $filename);

        return response()->json(['success' => true, 'filename' => $filename]);
    }

    /**
     * Clear the pending_request session and redirect back to the current URL
     */
    public function clearPendingRequest(Request $request)
    {
        // Clear the pending request session
        session()->forget('pending_request');
        
        // Get the redirect URL from the form, or fall back to the current URL
        $redirectUrl = $request->input('redirect_url', url()->current());
        
        return redirect($redirectUrl);
    }
}
