<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\EventRequestNotification;
use App\Notifications\RequestDeclinedNotification;
use App\Notifications\RequestAcceptedNotification;
use App\Notifications\DeletedEventNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Utils\ColorUtils;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Models\EventRole;
use App\Models\User;
use App\Models\Ticket;
use App\Utils\UrlUtils;
use App\Utils\GeminiUtils;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Rules\NoFakeEmail;
use App\Repos\EventRepo;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventUpdateRequest;

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

    public function delete(Request $request, $subdomain, $hash)
    {
        $user = $request->user();
        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
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


    public function create(Request $request, $subdomain)
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        
        $venue = $role->isVenue() ? $role : null;
        $schedule = $role->isSchedule() ? $role : null;
        $curator = $role->isCurator() ? $role : null;

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
            $event->tickets = $defaultTickets['tickets'] ?? [new Ticket()];
        } else {
            $event->ticket_currency_code = 'USD';
            $event->payment_method = 'cash';
            $event->tickets = [new Ticket()];
        }

        if ($schedule) {
            $selectedMembers = [$schedule->toData()];
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
            return $item->isSchedule();
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->sortBy('name')->toArray());
        $members = array_values($members->sortBy('name')->toArray());
        
        $currencies = file_get_contents(base_path('storage/currencies.json'));
        $currencies = json_decode($currencies);

        return view('event/edit', [
            'role' => $role,
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
            if ($event->venue && $user->isMember($event->venue->subdomain)) {
                $subdomain = $event->venue->subdomain;
            }
        }

        return redirect(route('event.edit', ['subdomain' => $subdomain, 'hash' => $hash]));
    }

    public function edit(Request $request, $subdomain, $hash)
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);
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

        $role = Role::subdomain($subdomain)->firstOrFail();
        $venue = $event->venue;
        $selectedMembers = [];
        foreach ($event->roles as $each) {
            if ($each->isSchedule()) {
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
            return $item->isSchedule();
        })->map(function ($item) {
            return $item->toData();
        });

        $venues = array_values($venues->sortBy('name')->toArray());
        $members = array_values($members->sortBy('name')->toArray());
    
        $currencies = file_get_contents(base_path('storage/currencies.json'));
        $currencies = json_decode($currencies);

        $eventUrlData = $event->getGuestUrlData($subdomain, false);
        $matchingEvent = $this->eventRepo->getEvent($eventUrlData['subdomain'], $eventUrlData['slug'], false);        
        $isUnique = ! $matchingEvent || $matchingEvent->id == $event->id ? true : false;

        return view('event/edit', [
            'role' => $role,
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
        ]);
    }

    public function update(EventUpdateRequest $request, $subdomain, $hash)
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);

        if (! $request->user()->canEditEvent($event)) {
            return redirect()->back();
        }

        $this->eventRepo->saveEvent($request, $event);

        if ($request->has('save_default_tickets')) {
            $role = Role::subdomain($subdomain)->firstOrFail();
            $defaultTickets = [
                'currency_code' => $event->ticket_currency_code,
                'payment_method' => $event->payment_method,
                'payment_instructions' => $event->payment_instructions,
                'expire_unpaid_tickets' => $event->expire_unpaid_tickets,
                'ticket_notes' => $event->ticket_notes,
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
        $event = Event::findOrFail($event_id);        

        if ($user->isMember($event->venue->subdomain)) {
            $event->is_accepted = true;
            $event->save();
        }

        foreach ($event->roles as $role) {
            if ($user->isMember($role->subdomain)) {
                $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
            }
        }

        //$emails = $event->role->members()->pluck('email');
        //Notification::route('mail', $emails)->notify(new RequestAcceptedNotification($event));
        
        return redirect('/' . $subdomain . '/requests')
                    ->with('message', __('messages.request_accepted'));
    }

    public function decline(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $event_id = UrlUtils::decodeId($hash);
        $event = Event::findOrFail($event_id);        
        $event->is_accepted = false;
        $event->save();

        //$emails = $event->role->members()->pluck('email');
        //Notification::route('mail', $emails)->notify(new RequestDeclinedNotification($event));

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
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $curatorId = $role->isCurator() ? $role->id : null;
        $event = $this->eventRepo->saveEvent($request, null, $curatorId);

        if ($request->has('save_default_tickets')) {
            $role = Role::subdomain($subdomain)->firstOrFail();
            $defaultTickets = [
                'currency_code' => $event->ticket_currency_code,
                'payment_method' => $event->payment_method,
                'payment_instructions' => $event->payment_instructions,
                'expire_unpaid_tickets' => $event->expire_unpaid_tickets,
                'ticket_notes' => $event->ticket_notes,
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

        session()->forget('pending_venue');

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
        $role->events()->attach($event->id);
    
        return back()->with('message', __('messages.curate_event'));
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

        return view('event.import', ['role' => $role]);
    }

    public function parse(Request $request, $subdomain)
    {
        $details = request()->input('event_details');
        $parsed = GeminiUtils::parseEvent($details);

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($role->isVenue()) {
            $parsed['venue_id'] = UrlUtils::encodeId($role->id);
            $parsed['event_address'] = $role->address1;
        } elseif ($parsed['venue_name'] && $parsed['event_address']) {
            $venue = Role::where('name', $parsed['venue_name'])
                        ->where('address1', $parsed['event_address'])
                        ->where('type', 'venue')
                        ->first();
            if ($venue) {
                $parsed['venue_id'] = UrlUtils::encodeId($venue->id);
            }
        }

        if ($role->isSchedule()) {
            $parsed['talent_id'] = UrlUtils::encodeId($role->id);
        } elseif ($parsed['performer_name']) {
            $talent = Role::where('name', $parsed['performer_name'])
                        ->where('type', 'schedule')
                        ->first();
            if ($talent) {
                $parsed['talent_id'] = UrlUtils::encodeId($talent->id);
            }
        }

        if (! empty($parsed['event_date_time'])) {
            $eventDate = Carbon::parse($parsed['event_date_time']);
            if ($eventDate->isPast() && $eventDate->diffInMonths(now()) > 6) {
                $eventDate->setYear(now()->year);
                $parsed['event_date_time'] = $eventDate->format('Y-m-d H:i:s');
            }
        }

        if ($parsed['registration_url']) {
            try {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $parsed['registration_url'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5
                ]);
                $html = curl_exec($ch);
                curl_close($ch);
                
                // Look for Open Graph image meta tag
                if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']*)["\']/', $html, $matches) ||
                    preg_match('/<meta[^>]*content=["\']([^"\']*)["\'][^>]*property=["\']og:image["\']/', $html, $matches)) {
                    
                    if ($imageUrl = $matches[1]) {
                        $imageContents = file_get_contents($imageUrl);
                        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $filename = '/tmp/event_' . uniqid() . '.' . $extension;
                        
                        file_put_contents($filename, $imageContents);
                        $parsed['social_image'] = $filename;
                    }
                }
            } catch (\Exception $e) {
                // do nothing 
            }
        }

        // Check if the event is already imported
        $eventUrl = null;
        $event = Event::where('registration_url', $parsed['registration_url'])->first();
        if ($event) {
            $eventUrl = $event->getGuestUrl();
        }

        return response()->json(['message' => 'Imported event', 'parsed' => $parsed, 'event_url' => $eventUrl]);
    }

    public function import(Request $request, $subdomain)
    {
        //\Log::info($request->all());
        //return redirect()->back();
        
        $role = Role::subdomain($subdomain)->firstOrFail();
        $curatorId = $role->isCurator() ? $role->id : null;
        
        $event = $this->eventRepo->saveEvent($request, null, $curatorId);

        if ($request->social_image) {
            $file = new \Illuminate\Http\UploadedFile($request->social_image, basename($request->social_image));
            $filename = strtolower('flyer_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);
            unlink($request->social_image);

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
}
