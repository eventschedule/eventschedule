<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Notifications\AddedMemberNotification;
use App\Http\Requests\RoleEmailVerificationRequest;
use App\Notifications\DeletedRoleNotification;
use Illuminate\Auth\Events\Verified;
use Endroid\QrCode\QrCode;
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
use App\Utils\UrlUtils;
use App\Utils\ColorUtils;
use Carbon\Carbon;

class RoleController extends Controller
{
    public function deleteImage(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect('/');
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
            return redirect('/');
        }

        if ($role->profile_image_url) {
            $path = $role->getAttributes()['profile_image_url'];
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
            $user->roles()->attach($role->id, ['level' => 'follower']);
        }

        session()->forget('pending_follow');

        $mainDomain = config('app.url');
        $redirectUrl = $mainDomain . route($role->getTypePlural(), [], false);

        return redirect($redirectUrl)
                ->with('message', str_replace(':name', $role->name, __('messages.followed_role')));
    }

    public function unfollow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        if ($user->isConnected($role->subdomain)) {
            $user->roles()->detach($role->id);
        }

        return redirect(route($role->getTypePlural()))
                ->with('message', str_replace(':name', $role->name, __('messages.unfollowed_role')));
    }

    public function viewGuest(Request $request, $subdomain, $hash = '')
    {
        if ($subdomain == 'www') {
            return redirect()->route('landing');
        }

        $user = auth()->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        $otherRole = null;
        $event = null;
        $month = $request->month;
        $year = $request->year;

        if ($hash) {
            $event_id = UrlUtils::decodeId($hash);
            $event = Event::findOrFail($event_id);
            $otherRole = $event->venue->subdomain == $subdomain ? $event->role : $event->venue;

            if ($event->starts_at) {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
                $month = $date->month;
                $year = $date->year;
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

        $events = Event::with(['role'])
            ->where($role->isVenue() ? 'venue_id' : 'role_id', $role->id)
            ->where('is_accepted', true)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                    ->orWhereNotNull('days_of_week');
            })
            ->orderBy('starts_at')
            ->get();

        return view('role/show-guest', compact(
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
        ));
    }

    public function viewAdmin(Request $request, $subdomain, $tab = 'schedule')
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $followers = $role->followers()->get();
        $members = $role->members()->get();

        $requests = Event::with(['role'])
            ->where(function ($query) use ($role) {
                $query->where('venue_id', $role->id)
                    ->orWhere('role_id', $role->id);
            })
            ->whereNull('is_accepted')
            ->orderBy('created_at', 'desc')
            ->get();        
        
        $events = [];
        $unscheduled = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';
        $datesUnavailable = [];

        // TODO remove once supported
        if ($tab == 'requests' && ! $role->isVenue()) {
            $tab = 'schedule';
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
                $events = Event::with(['role'])
                    ->where(function ($query) use ($role) {
                        $query->where('venue_id', $role->id)
                            ->orWhere('role_id', $role->id);
                    })
                    ->whereNotNull('is_accepted')
                    ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                            ->orWhereNotNull('days_of_week');
                    })
                    ->orderBy('starts_at')
                    ->get();

                $unscheduled = Event::with(['role'])
                    ->where(function ($query) use ($role) {
                        $query->where('venue_id', $role->id)
                            ->orWhere('role_id', $role->id);
                    })
                    ->where('is_accepted', true)
                    ->whereNull('starts_at')
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($members as $member) {
                    $datesUnavailable[e($member->name)] = json_decode($member->pivot->dates_unavailable);
                }
            } else if ($tab == 'availability') {                                       
                $user = $request->user();
                $roleUser = RoleUser::where('user_id', $user->id)
                                ->where('role_id', $role->id)
                                ->first();
                $datesUnavailable = json_decode($roleUser->dates_unavailable);
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
            return redirect('/');
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
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = $request->validated();
        $user = User::whereEmail($data['email'])->first();

        if ($user && $user->isMember($subdomain)) {
            return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']));    
        }

        if (! $user) {
            $user = new User;
            $user->email = $data['email'];
            $user->name = $data['name'];
            $user->password = bcrypt(Str::random(32));
            $user->timezone = $request->user()->timezone;
            $user->language_code = $request->user()->language_code;
            $user->save();
        }

        if ($user->isFollowing($subdomain)) {
            $roleUser = RoleUser::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->first();
            
            $roleUser->level = 'admin';
            $roleUser->save();

        } else {
            $user->roles()->attach($role->id, ['level' => 'admin']);
        }

        Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
                    ->with('message', __('messages.member_added'));
    }

    public function removeMember(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $userId = UrlUtils::decodeId($hash);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($userId == $role->user_id) {
            return redirect('/');
        }

        $roleUser = RoleUser::where('user_id', $userId)
            ->where('role_id', $role->id)
            ->first();        
        $roleUser->delete();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
                    ->with('message', __('messages.removed_member'));
    }

    public function viewVenues()
    {
        $user = auth()->user();
        $roleIds = $user->followingVenues()->pluck('roles.id');
        $roles = Role::whereIn('id', $roleIds)
                    ->orderBy('name', 'ASC')
                    ->get();

        $data = [
            'roles' => $roles,
            'type' => 'venue',
        ];

        return view('role/index', $data);
    }

    public function viewTalent()
    {
        $user = auth()->user();
        $roleIds = $user->followingTalent()->pluck('roles.id');
        $roles = Role::whereIn('id', $roleIds)
                    ->orderBy('name', 'ASC')
                    ->get();

        $data = [
            'roles' => $roles,
            'type' => 'talent',
        ];

        return view('role/index', $data);
    }

    public function viewVendors()
    {
        $user = auth()->user();
        $roleIds = $user->followingVendors()->pluck('roles.id');
        $roles = Role::whereIn('id', $roleIds)
                    ->orderBy('name', 'ASC')
                    ->get();

        $data = [
            'roles' => $roles,
            'type' => 'vendor',
        ];

        return view('role/index', $data);
    }

    public function create($type)
    {
        $role = new Role;
        $role->type = $type;
        $role->font_family = 'Roboto';
        $role->font_color = '#ffffff';
        $role->accent_color = '#007BFF';
        $role->background = 'gradient';
        $role->background_colors = ColorUtils::randomGradient();
        $role->background_rotation = rand(0, 359);
        $role->accept_talent_requests = true;
        $role->timezone = auth()->user()->timezone;
        $role->language_code = auth()->user()->language_code;

        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {            
            $gradientOptions[join(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '' => 'Custom',
        ] + $gradientOptions;



        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'role' => $role,
            'user' => auth()->user(),
            'title' => __('messages.register'),
            'gradients' => $gradientOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function store(RoleCreateRequest $request): RedirectResponse
    {
        $user = $request->user();        

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = Role::generateSubdomain($request->name);
        $role->user_id = $user->id;

        if ($role->email == $user->email) {
            $role->email_verified_at = $user->email_verified_at;
        }

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1 . ', ' . $request->custom_color2;
        }

        $role->save();

        $user->roles()->attach($role->id);

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

        if ($request->hasFile('background_image')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('background_image');
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
            return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]))->with('message', $message);
        }
    }

    public function edit($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {
            $gradientOptions[join(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '#7468E6, #C44B85' => 'Default', 
            '' => 'Custom',
        ] + $gradientOptions;

        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'user' => auth()->user(),
            'role' => $role,
            'title' => __('messages.edit_' . $role->type),
            'gradients' => $gradientOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function update(RoleUpdateRequest $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }        

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->fill($request->all());
        $role->subdomain = Role::cleanSubdomain($request->subdomain);

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

        if ($request->hasFile('background_image')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('background_image');
            $filename = strtolower('background_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->background_image_url = $filename;
            $role->save();
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]))
                    ->with('message', __('messages.updated_' . $role->type));
    }

    public function updateLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($request->link_type == 'social_links') {
            $links = $role->social_links;
        } else if ($request->link_type == 'payment_links') {
            $links = $role->payment_links;
        } else {
            $links = $role->youtube_links;
        }
        
        if (!$links) {
            $links = '[]';
        }

        $links = json_decode($links);

        foreach(explode(',', $request->link) as $link) {
            $title = '';
            $thumbnail_url = '';
            $url = 'https://noembed.com/embed?dataType=json&url=' . urlencode($link);

            if ($response = @file_get_contents($url)) {
                $json = json_decode($response);

                if (property_exists($json, 'title')) {
                    $title = $json->title;
                }

                if (property_exists($json, 'thumbnail_url')) {
                    $thumbnail_url = $json->thumbnail_url;
                }
            }
                        
            $obj = new \stdClass;
            $obj->name = $title;
            $obj->url = rtrim($link, '/');
            $obj->thumbnail_url = $thumbnail_url;
            $links[] = $obj;
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
            return redirect('/');
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
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain]);
        }

        if ($role->markEmailAsVerified()) {
            event(new Verified($role));
        }

        return redirect()
                ->route('role.view_admin', ['subdomain' => $role->subdomain])
                ->with('message', __('messages.confirmed_email'));
    }

    public function resendVerify(Request $request, $subdomain)
    {
        $role = Role::whereSubdomain($subdomain)->firstOrFail();

        if ($role->hasVerifiedEmail()) {
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain]);
        }

        $role->sendEmailVerificationNotification();

        return redirect()
            ->route('role.view_admin', ['subdomain' => $role->subdomain])
            ->with('message', __('messages.sent_confirmation_email'));
    }

    public function signUp(Request $request, $subdomain)
    {
        session(['pending_venue' => $subdomain]);

        $mainDomain = config('app.url');
        $redirectUrl = $mainDomain . route('event.create', ['subdomain' => $subdomain], false);

        return redirect($redirectUrl);
    }

    public function validateAddress(Request $request)
    {
        $role = new Role;
        $role->fill($request->all());
        
        $address = implode(', ', $parts);
        $urlAddress = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$urlAddress}&key=" . config('services.google.backend');
        $response = file_get_contents($url);
        $responseData = json_decode($response, true);

        if ($responseData['status'] == 'OK') {
            return response()->json([
                'geo_address' => $address,
                'formatted_address' => $responseData['results'][0]['formatted_address'],
                'google_place_id' => $responseData['results'][0]['place_id'],
                'geo_lat' => $responseData['results'][0]['geometry']['location']['lat'],
                'geo_lon' => $responseData['results'][0]['geometry']['location']['lng'],
            ]);
        } else {
            return '';
        }        
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

        return redirect(route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'availability']))
            ->with('message', __('messages.updated_availability'));
    }
}