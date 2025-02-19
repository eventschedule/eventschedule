<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\AddedMemberNotification;
use App\Http\Requests\RoleEmailVerificationRequest;
use App\Notifications\DeletedRoleNotification;
use Illuminate\Auth\Events\Verified;
use Endroid\QrCode\QrCode;
use App\Repos\EventRepo;
use Endroid\QrCode\Writer\PngWriter;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\MemberAddRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Event;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\EventRole;
use App\Utils\UrlUtils;
use App\Utils\ColorUtils;
use Carbon\Carbon;

class RoleController extends Controller
{
    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function deleteImage(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($request->image_type == 'profile') {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);

                $role->profile_image_url = null;
                $role->save();
            }    
        } else if ($request->image_type == 'background') {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);

                $role->background_image_url = null;
                $role->save();
            }    
        } else if ($request->image_type == 'header') {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);

                $role->header_image_url = null;
                $role->save();
            }
        }

        return redirect(route('role.edit', ['subdomain' => $subdomain]))
                ->with('message', __('messages.deleted_image'));
    }

    public function delete(Request $request, $subdomain)
    {
        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        $type = $role->type;

        if ($user->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($role->profile_image_url) {
            $path = $role->getAttributes()['profile_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/' . $path;
            }
            Storage::delete($path);
        }

        if ($role->header_image_url) {
            $path = $role->getAttributes()['header_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/' . $path;
            }
            Storage::delete($path);
        }

        if ($role->background_image_url) {
            $path = $role->getAttributes()['background_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/' . $path;
            }
            Storage::delete($path);
        }

        $emails = $role->members()->pluck('email');

        // Prevent orphaned events
        if ($role->isSchedule()) {
            $events = $role->events()->get();
            foreach ($events as $event) {
                if ($event->members()->count() == 1) {
                    $event->delete();
                }
            }
        }

        $role->delete();
        
        Notification::route('mail', $emails)->notify(new DeletedRoleNotification($role, $user));

        return redirect(route('home'))
                ->with('message', __('messages.deleted_' . $type));
    }

    public function follow(Request $request, $subdomain)
    {
        if (! auth()->user()) {
            session(['pending_follow' => $subdomain]);
            return redirect()->route('sign_up');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        if (! $user->isConnected($role->subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        session()->forget('pending_follow');

        $mainDomain = config('app.url');
        $redirectUrl = $mainDomain . route('following', [], false);

        return redirect($redirectUrl)
                ->with('message', str_replace(':name', $role->name, __('messages.followed_role')));
    }

    public function unfollow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        if (! $role->email) {
            $role->is_deleted = true;
            $role->save();
        }

        if ($user->isConnected($role->subdomain)) {
            $user->roles()->detach($role->id);
        }

        return redirect(route('following'))
                ->with('message', str_replace(':name', $role->name, __('messages.unfollowed_role')));
    }

    public function viewGuest(Request $request, $subdomain, $slug = '')
    {
        $user = auth()->user();
        $curatorRoles = $user ? $user->editableCurators() : collect();

        $role = Role::subdomain($subdomain)->first();

        if (! $role) {
            return redirect(config('app.url'));
        }

        $otherRole = null;
        $event = null;
        $date = $request->date;

        if ($date) {
            $dateObj = Carbon::parse($date);
            $month = $dateObj->month;
            $year = $dateObj->year;
        } else {
            $month = $request->month;
            $year = $request->year;
        }

        if ($slug) {
            $event = $this->eventRepo->getEvent($subdomain, $slug, $date);

            if ($event) {
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
            } else {
                return redirect($role->getGuestUrl());
            }        
        } 

        if ($event) {
            if ($event->venue) {
                if ($event->venue->subdomain == $subdomain) {
                    if ($event->roles->count() > 0) {
                        $otherRole = $event->roles[0];
                    } else {
                        $otherRole = $role;
                    }
                } else {
                    $otherRole = $event->venue;
                }
            } else {
                $otherRole = $role;
            }

            if ($event->starts_at && ! $date) {
                $startAtDate = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
                $month = $startAtDate->month;
                $year = $startAtDate->year;
            }            
        }

        if (! $month) {
            $month = now()->month;
        }

        if (! $year) {
            $year = now()->year;
        }    

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        if ($role->isCurator()) {
            // TODO this may not be needed
            $events = Event::with(['roles', 'venue'])
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                        ->orWhereNotNull('days_of_week');
                })
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id);
                })
                ->orderBy('starts_at')
                ->get();
        } else {
            $events = Event::with(['roles', 'venue'])
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                        ->orWhereNotNull('days_of_week');
                })
                ->where(function ($query) use ($role) {
                    if ($role->isVenue()) {
                        $query->where('venue_id', $role->id)
                            ->where('is_accepted', true);
                    } else {
                        $query->whereHas('roles', function ($query) use ($role) {
                            $query->where('role_id', $role->id)
                                ->where('is_accepted', true);
                        });
                    }
                })
                ->orderBy('starts_at')
                ->get();
        }

        $embed = request()->embed;
        $view = 'role/show-guest';

        if ($embed) {
            $view = 'role/show-guest-embed';
        } else if ($event) {
            $view = 'event/show-guest';
        }

        $fonts = [];
        /*
        if ($event) {
            $fonts[] = $event->venue->font_family;
            foreach ($event->roles as $each) {
                if ($each->isClaimed() && $each->isSchedule()) {
                    $fonts[] = $each->font_family;
                }
            }
        } else {
            $fonts[] = $role->font_family;
        }

        $fonts = array_unique($fonts);
        */

        $response = response()
            ->view($view, compact(
            'subdomain',
            'events',
            'role',
            'otherRole',
            'month', 
            'year',
            'startOfMonth',
            'endOfMonth',
            'user',
            'event',
            'embed',
            'date',
            'curatorRoles',
            'fonts',
        ));


        if (! $role->isPro()) {
            $response->header('X-Frame-Options', 'DENY');
        }

        return $response;
    }

    public function viewAdmin(Request $request, $subdomain, $tab = 'schedule')
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $followers = $role->followers()->get();
        $members = $role->members()->get();

        $events = [];
        $unscheduled = [];
        $requests = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';
        $datesUnavailable = [];

        $requests = Event::with(['roles', 'venue'])
                        ->where(function ($query) use ($role) {
                            if ($role->isVenue()) {
                                $query->where('venue_id', $role->id)
                                    ->whereNull('is_accepted');
                            } else {
                                $query->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role->id)
                                        ->whereNull('is_accepted');
                                });
                            }
                        })
                        ->orderBy('created_at', 'desc')
                        ->get();

        if ($tab == 'requests' && ! count($requests)) {
            return redirect(route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']));
        }

        if ($tab == 'schedule' || $tab == 'availability') {
            if (! $month) {
                $month = now()->month;
            }
            if (! $year) {
                $year = now()->year;
            }

            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            if ($tab == 'schedule') {
                if ($role->isCurator()) {
                    // TODO this may not be needed
                    $events = Event::with(['roles', 'venue'])
                        ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                            $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                                ->orWhereNotNull('days_of_week');
                        })
                        ->whereIn('id', function ($query) use ($role) {
                            $query->select('event_id')
                                ->from('event_role')
                                ->where('role_id', $role->id);
                        })
                        ->orderBy('starts_at')
                        ->get();   
                } else {
                    $events = Event::with(['roles', 'venue'])
                        ->where(function ($query) use ($role) {
                            if ($role->isVenue()) {
                                $query->where('venue_id', $role->id)
                                    ->where('is_accepted', true);
                            } else {
                                $query->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role->id)
                                        ->where('is_accepted', true);
                                });
                            }                                
                        })
                        ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                            $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                                ->orWhereNotNull('days_of_week');
                        })
                        ->orderBy('starts_at')
                        ->get();

                    $unscheduled = Event::with(['roles', 'venue'])
                        ->where(function ($query) use ($role) {
                            if ($role->isVenue()) {
                                $query->where('venue_id', $role->id)
                                    ->where('is_accepted', true);
                            } else {
                                $query->whereHas('roles', function ($query) use ($role) {
                                    $query->where('role_id', $role->id)
                                        ->where('is_accepted', true);
                                });
                            }
                        })
                        ->whereNull('starts_at')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    foreach ($members as $member) {
                        if ($member->pivot->dates_unavailable) {
                            $datesUnavailable[e($member->name)] = json_decode($member->pivot->dates_unavailable);
                        }
                    }
                }
            } else if ($tab == 'availability') {                                       
                $user = $request->user();
                $roleUser = RoleUser::where('user_id', $user->id)
                                ->where('role_id', $role->id)
                                ->first();

                if ($roleUser && $roleUser->dates_unavailable) {
                    $datesUnavailable = json_decode($roleUser->dates_unavailable);
                }
            }
        }

        return view('role/show-admin', compact(
            'subdomain',
            'role',
            'tab',
            'events',
            'members',
            'followers',
            'requests',
            'month',
            'year',
            'startOfMonth',
            'endOfMonth',
            'unscheduled',
            'datesUnavailable',
        ));
    }

    public function createMember(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
            'title' => __('messages.add_member'),
        ];

        return view('role/add-member', $data);
    }

    public function storeMember(MemberAddRequest $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.upgrade_to_pro'));
        } elseif (! $role->email_verified_at) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        $data = $request->validated();
        $user = User::whereEmail($data['email'])->first();

        if ($user && $user->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.member_already_exists'));
        }

        if (! $user) {
            $user = new User;
            $user->email = $data['email'];
            $user->name = $data['name'];
            $user->password = bcrypt(Str::random(32));
            $user->timezone = $request->user()->timezone;
            $user->language_code = $request->user()->language_code;

            if (! config('app.hosted')) {
                $user->email_verified_at = now();
            }

            $user->save();
        }

        if ($user->isFollowing($subdomain)) {
            $roleUser = RoleUser::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->first();
            
            $roleUser->level = 'admin';
            $roleUser->save();

        } else {
            $user->roles()->attach($role->id, ['level' => 'admin', 'created_at' => now()]);
        }

        Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
                    ->with('message', __('messages.member_added'));
    }

    public function removeMember(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $userId = UrlUtils::decodeId($hash);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($userId == $role->user_id) {
            return redirect()->back()->with('error', __('messages.cannot_remove_owner'));
        }

        $roleUser = RoleUser::where('user_id', $userId)
            ->where('role_id', $role->id)
            ->first();        
        $roleUser->delete();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
                    ->with('message', __('messages.removed_member'));
    }

    public function following()
    {
        $user = auth()->user();
        $roleIds = $user->following()->pluck('roles.id');
        $roles = Role::whereIn('id', $roleIds)
                    ->orderBy('name', 'ASC')
                    ->get();

        $data = [
            'roles' => $roles,
        ];

        return view('role/index', $data);
    }


    public function create($type)
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = new Role;
        $role->type = $type;
        $role->font_family = 'Roboto';
        $role->font_color = '#ffffff';
        $role->accent_color = '#007BFF';
        $role->background = 'gradient';
        $role->background_color = '#888888';
        $role->background_colors = ColorUtils::randomGradient();
        $role->background_image = ColorUtils::randomBackgroundImage();
        $role->background_rotation = rand(0, 359);
        $role->header_image = ColorUtils::randomHeaderImage();
        $role->timezone = auth()->user()->timezone;
        $role->language_code = auth()->user()->language_code;

        // Header images
        $headers = file_get_contents(base_path('storage/headers.json'));
        $headers = json_decode($headers);

        $headerOptions = [];
        foreach ($headers as $header) {
            $headerOptions[$header->name] = str_replace('_', ' ', $header->name);
        }

        asort($headerOptions);

        $headerOptions = [
            '' => __('messages.custom'),
        ] + $headerOptions;

        // Background images
        $backgrounds = file_get_contents(base_path('storage/backgrounds.json'));
        $backgrounds = json_decode($backgrounds);

        $backgroundOptions = [];
        foreach ($backgrounds as $background) {
            $backgroundOptions[$background->name] = str_replace('_', ' ', $background->name);
        }

        asort($backgroundOptions);

        $backgroundOptions = [
            '' => __('messages.custom'),
        ] + $backgroundOptions;

        // Background gradients
        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {            
            $gradientOptions[join(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '' => __('messages.custom'),
        ] + $gradientOptions;

        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'role' => $role,
            'user' => auth()->user(),
            'title' => __('messages.new_' . $role->type),
            'gradients' => $gradientOptions,
            'backgrounds' => $backgroundOptions,
            'headers' => $headerOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function store(RoleCreateRequest $request): RedirectResponse
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();        

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = Role::generateSubdomain($request->name);
        $role->user_id = $user->id;

        // TODO remove this
        if (config('app.hosted')) {
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->plan_type = 'pro';
            $role->plan_term = 'year';
        }

        if (! config('app.hosted')) {
            $role->email_verified_at = now();
        } else if ($role->email == $user->email) {
            $role->email_verified_at = $user->email_verified_at;
        }

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1 . ', ' . $request->custom_color2;
        }

        $role->save();

        $user->roles()->attach($role->id, ['created_at' => now(), 'level' => 'owner']);

        if ($request->hasFile('profile_image')) {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('profile_image');
            $filename = strtolower('profile_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->profile_image_url = $filename;
            $role->save();
        }

        if ($request->hasFile('header_image')) {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('header_image');
            $filename = strtolower('header_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->header_image_url = $filename;
            $role->save();
        }

        if ($role->background == 'image' && $request->background_image) {
            $role->background_image = $request->background_image;
            $role->save();
        } elseif ($role->background == 'image' && $request->hasFile('background_image_url')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('background_image_url');
            $filename = strtolower('background_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->background_image_url = $filename;
            $role->save();
        }

        if (! $role->email_verified_at) {
            $role->sendEmailVerificationNotification();
        }

        $message = __('messages.created_' . $role->type);

        if ($subdomain = session('pending_venue') && $user->countRoles() == 1) {
            $data = [
                'subdomain' => $subdomain,
                'role_email' => $role->email,
            ];
            return redirect(route('event.create', $data))->with('message', $message);
        } else {    
            return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))->with('message', $message);
        }
    }

    public function edit($subdomain)
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        // Header images
        $headers = file_get_contents(base_path('storage/headers.json'));
        $headers = json_decode($headers);

        $headerOptions = [];
        foreach ($headers as $header) {
            $headerOptions[$header->name] = str_replace('_', ' ', $header->name);
        }

        asort($headerOptions);

        $headerOptions = [
            '' => __('messages.custom'),
        ] + $headerOptions;
        

        // Background images
        $backgrounds = file_get_contents(base_path('storage/backgrounds.json'));
        $backgrounds = json_decode($backgrounds);

        $backgroundOptions = [];
        foreach ($backgrounds as $background) {
            $backgroundOptions[$background->name] = str_replace('_', ' ', $background->name);
        }

        asort($backgroundOptions);

        $backgroundOptions = [
            '' => __('messages.custom'),
        ] + $backgroundOptions;


        // Background gradients
        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {
            $gradientOptions[join(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '' => __('messages.custom'),
        ] + $gradientOptions;

        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'user' => auth()->user(),
            'role' => $role,
            'title' => __('messages.edit_' . $role->type),
            'gradients' => $gradientOptions,
            'backgrounds' => $backgroundOptions,
            'headers' => $headerOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function update(RoleUpdateRequest $request, $subdomain): RedirectResponse
    {
        if (! \App\Utils\HostedUtils::isHostedOrAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }        

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->fill($request->all());

        $newSubdomain = Role::cleanSubdomain($request->new_subdomain);
        if ($newSubdomain != $subdomain) {
            if (Role::subdomain($newSubdomain)->first()) {
                return redirect()->back()->withErrors(['new_subdomain' => __('messages.subdomain_taken')]);
            }
            $role->subdomain = $newSubdomain;
        }

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1 . ', ' . $request->custom_color2;
        }

        $role->save();

        if ($request->hasFile('profile_image')) {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }
        
            $file = $request->file('profile_image');
            $filename = strtolower('profile_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->profile_image_url = $filename;
            $role->save();
        }

        if ($request->hasFile('header_image_url')) {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('header_image_url');
            $filename = strtolower('header_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->header_image_url = $filename;
            $role->save();
        }

        if ($role->background == 'image' && $request->background_image) {
            $role->background_image = $request->background_image;
            $role->save();
        } elseif ($role->background == 'image' && $request->hasFile('background_image_url')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('background_image_url');
            $filename = strtolower('background_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->background_image_url = $filename;
            $role->save();
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
                    ->with('message', __('messages.updated_' . $role->type));
    }

    public function updateLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($request->link_type == 'social_links') {
            $links = $role->social_links;
        } else if ($request->link_type == 'payment_links') {
            $links = $role->payment_links;
        } else {
            $links = $role->youtube_links;
        }
        
        if (! $links) {
            $links = '[]';
        }

        $links = json_decode($links);

        foreach(explode(',', $request->link) as $link) {
            $link = trim($link);

            if (! $link) {
                continue;
            }

            $links[] = UrlUtils::getUrlDetails($link);
        }

        $links = json_encode($links);

        if ($request->link_type == 'social_links') {
            $role->social_links = $links;
        } else if ($request->link_type == 'payment_links') {
            $role->payment_links = $links;
        } else {
            $role->youtube_links = $links;
        }
        
        $role->save();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'profile']))
                    ->with('message', __('messages.added_link'));
    }

    public function removeLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();        
        if ($request->remove_link_type == 'social_links') {
            $links = $role->social_links;
        } else if ($request->remove_link_type == 'payment_links') {
            $links = $role->payment_links;
        } else {
            $links = $role->youtube_links;
        }

        if (!$links) {
            $links = '[]';
        }

        $links = json_decode($links);
        $new_links = [];

        foreach ($links as $link) {
            if ($link->url != $request->remove_link) {
                $new_links[] = $link;
            }
        }

        $new_links = json_encode($new_links);

        if ($request->remove_link_type == 'social_links') {
            $role->social_links = $new_links;
        } else if ($request->remove_link_type == 'payment_links') {
            $role->payment_links = $new_links;
        } else {
            $role->youtube_links = $new_links;
        }

        $role->save();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'profile']))
                    ->with('message', __('messages.removed_link'));
    }

    public function qrCode($subdomain)
    {
        $url = route('role.follow', ['subdomain' => $subdomain]);

        $qrCode = QrCode::create($url)            
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        header('Content-Type: ' . $result->getMimeType());
        header('Content-Disposition: attachment; filename="qr-code.png"');
            
        echo $result->getString();

        exit;
    }

    public function verify(RoleEmailVerificationRequest $request, $subdomain)
    {
        $role = Role::whereSubdomain($subdomain)->firstOrFail();

        if ($role->hasVerifiedEmail()) {
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
        }

        if ($role->markEmailAsVerified()) {
            event(new Verified($role));
        }

        return redirect()
                ->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])
                ->with('message', __('messages.confirmed_email'));
    }

    public function resendVerify(Request $request, $subdomain)
    {
        $role = Role::whereSubdomain($subdomain)->firstOrFail();

        if ($role->hasVerifiedEmail()) {
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
        }

        $role->sendEmailVerificationNotification();

        return redirect()
            ->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])
            ->with('message', __('messages.sent_confirmation_email'));
    }

    public function signUp(Request $request, $subdomain)
    {
        $mainDomain = config('app.url');

        if (! auth()->user()) {
            session(['pending_venue' => $subdomain]);
            $redirectUrl = $mainDomain . route('sign_up', [], false);
            return redirect($redirectUrl);
        }
        
        $user = auth()->user();
        
        if ($user->schedules()->count() == 0) {
            session(['pending_venue' => $subdomain]);
            $redirectUrl = $mainDomain . route('new', ['type' => 'schedule'], false);
            return redirect($redirectUrl);
        }

        $redirectUrl = $mainDomain . route('event.create', ['subdomain' => $subdomain], false);
        return redirect($redirectUrl);
    }

    public function validateAddress(Request $request)
    {
        $role = new Role;
        $role->fill($request->all());        
        
        if ($address = $role->fullAddress()) {
            $urlAddress = urlencode($address);
            
            $url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . config('services.google.backend') . "&address={$urlAddress}";
            $response = file_get_contents($url);
            $responseData = json_decode($response, true);

            if ($responseData['status'] == 'OK') {
                $result = $responseData['results'][0];
                $addressComponents = $result['address_components'];

                $addressParts = [
                    'street_number' => '',
                    'route' => '',
                    'sublocality_level_1' => '',
                    'locality' => '',
                    'administrative_area_level_1' => '',
                    'postal_code' => '',
                    'country' => '',
                ];

                foreach ($addressComponents as $component) {
                    foreach ($component['types'] as $type) {
                        if (array_key_exists($type, $addressParts)) {
                            $addressParts[$type] = $type == 'country' ? $component['short_name'] : $component['long_name'];
                        }
                    }
                }

                $address1 = trim($addressParts['street_number'] . ' ' . $addressParts['route']);
                $address2 = $addressParts['sublocality_level_1'];
                $city = $addressParts['locality'] ?: $addressParts['sublocality_level_1'];
                $state = $addressParts['administrative_area_level_1'];
                $postal_code = $addressParts['postal_code'];
                $country = $addressParts['country'];

                return response()->json([
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postal_code,
                    'country_code' => $country,
                    'formatted_address' => $result['formatted_address'],
                ]);
            } else {
                \Log::error('Google Maps Error: ' . json_encode($responseData));
                return response()->json(['error' => 'Unable to validate address'], 400);
            }
        }

        return '';
    }

    public function availability(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();       

        $roleUser = RoleUser::where('user_id', $user->id)
                        ->where('role_id', $role->id)
                        ->first();
    
        $dates = json_decode($roleUser->dates_unavailable);
        $available = json_decode($request->available_days);
        $unavailable = json_decode($request->unavailable_days);

        if ($dates) {
            $dates = array_diff($dates, $available);
        } else {
            $dates = [];
        }

        foreach ($unavailable as $date) {
            $dates[] = $date;
        }
        
        $dates = array_unique($dates);
        asort($dates);
        $dates = array_values($dates);

        $roleUser->dates_unavailable = json_encode($dates);
        $roleUser->save();

        $data = [
            'subdomain' => $subdomain, 
            'tab' => 'availability',
            'month' => $request->month,
            'year' => $request->year,
        ];

        return redirect(route('role.view_admin', $data))
            ->with('message', __('messages.updated_availability'));
    }
 
    public function showUnsubscribe(Request $request)
    {        
        return view('role/unsubscribe');
    }

    public function unsubscribe(Request $request)
    {
        $roles = Role::where('email', base64_decode($request->email))->get();

        foreach ($roles as $role) {
            $role->is_subscribed = false;
            $role->save();
        }

        echo __('messages.unsubscribed');
        exit;
    }

    public function subscribe(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $role->is_subscribed = true;
        $role->save();

        return redirect()
            ->back()
            ->with('message', __('messages.subscribed'));
    }

    public function resendInvite(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }
    
        $role = Role::subdomain($subdomain)->firstOrFail();
        $userId = UrlUtils::decodeId($hash);
        $user = User::findOrFail($userId);
    
        Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));
    
        return redirect()->back()->with('message', __('messages.invite_resent'));
    }

    public function search(Request $request)
    {
        $type = $request->type;
        $search = $request->search;

        $roles = Role::whereIn('type', $type == 'venue' ? ['venue'] : ['schedule'])
            ->where(function($query) use ($search) {
                $query->where('email', '=', $search);
                  //->orWhere('phone', '=', $search)
                  //->orWhere('name', 'like', "%{$search}%");
            })
            ->get([
                'id', 
                'subdomain',
                'name', 
                'address1', 
                'address2', 
                'city', 
                'state', 
                'postal_code', 
                'email',
                'user_id',
                'profile_image_url',
                'country_code',
                'youtube_links',
            ]);

        $roles = $roles->map(function ($role) {
            return $role->toData();
        });

        $roles = array_values($roles->toArray());

        return response()->json($roles);
    }

    public function changePlan($subdomain, $plan_type)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.unauthorized'));
        }

        $role->plan_type = $plan_type;
        $role->plan_expires = null;
        $role->save();

        return redirect()->back()->with('message', __('messages.plan_changed'));
    }
}
