<?php

namespace App\Http\Controllers;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\MemberAddRequest;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Event;
use App\Models\User;
use App\Models\RoleUser;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class RoleController extends Controller
{
    public function follow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        if (! $user->isConnected($role->subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower']);
        }

        return redirect(route($role->getTypePlural()))
                ->with('message', str_replace(':name', $role->name, __('Successfully followed :name')));
    }

    public function viewGuest(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = auth()->user();

        if ($role->visibility == 'private' && (! $user || ! $user->hasRole($subdomain))) {
            return redirect('/');
        }

        $month = $request->month;
        $year = $request->year;

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
            ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
            ->where('visibility', 'public')
            ->orderBy('starts_at')
            ->get();

        return view('role/show-guest', compact(
            'events',
            'role',
            'month', 
            'year',
            'startOfMonth',
            'endOfMonth',
            'user',
        ));
    }

    public function viewAdmin(Request $request, $subdomain, $tab)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $followers = $role->followers()->get();
        $members = $role->members()->get();
        $requests = Event::with(['role'])
            ->whereVenueId($role->id)
            ->whereNull('is_accepted')
            ->orderBy('created_at', 'desc')
            ->get();
        
        
        $events = [];
        $unscheduled = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';

        if ($tab == 'schedule') {
            if (! $month) {
                $month = now()->month;
            }
            if (! $year) {
                $year = now()->year;
            }

            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $events = Event::with(['role'])
                ->whereVenueId($role->id)
                ->whereNotNull('is_accepted')
                ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                ->orderBy('starts_at')
                ->get();

            $unscheduled = Event::with(['role'])
                ->whereVenueId($role->id)
                ->where('is_accepted', true)
                ->whereNull('starts_at')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('role/show-admin', compact(
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
        ));
    }

    public function createMember(Request $request, $subdomain)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
            'title' => __('Add Member'),
        ];

        return view('role/add-member', $data);
    }

    public function storeMember(MemberAddRequest $request, $subdomain)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = $request->validated();
        $user = User::whereEmail($data['email'])->first();

        if ($user && $user->hasRole($subdomain)) {
            return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']));    
        }

        if (! $user) {
            $user = new User;
            $user->email = $data['email'];
            $user->name = $data['name'];
            $user->password = bcrypt(Str::random(32));
            $user->timezone = $request->user()->timezone;
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

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
                    ->with('message', __('Successfully added member'));
    }

    public function removeMember(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->hasRole($subdomain)) {
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
                    ->with('message', __('Successfully removed member '));
    }

    public function viewVenues()
    {
        return view('role/index');
    }

    public function viewTalent()
    {
        return view('role/index');
    }

    public function viewVendors()
    {
        return view('role/index');
    }

    public function create()
    {
        $role = new Role;
        $role->font_color = '#111827';
        $role->accent_color = '#007BFF';
        $role->background = 'default';
        $role->background_color = '#FFFFFF';
        $role->background_colors = '#7468E6, #C44B85';
        $role->background_rotation = 135;
        $role->accept_talent_requests = true;

        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {            
            $gradientOptions[join(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '#7468e6, #c44b85' => 'Default', 
            '' => 'Custom',
        ] + $gradientOptions;

        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'role' => $role,
            'title' => __('Register'),
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

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1 . ', ' . $request->custom_color2;
        }

        $role->save();

        $user->roles()->attach($role->id);

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]))
                    ->with('message', __('Successfully created ' . $role->type));
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
            'role' => $role,
            'title' => __('Edit ' . ucwords($role->type)),
            'gradients' => $gradientOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function update(RoleUpdateRequest $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }        

        $role = Role::subdomain($subdomain)->firstOrFail();
        $role->fill($request->all());

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1 . ', ' . $request->custom_color2;
        }

        $role->save();

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $filename = strtolower('profile_' . Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs('images', $filename, 'public');

            $role->profile_image_url = $path;
            $role->save();
        }

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            $filename = strtolower('background_' . Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs('images', $filename, 'public');

            $role->background_image_url = $path;
            $role->save();
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]))
                    ->with('message', __('Successfully updated ' . $role->type));
    }

    public function updateLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->hasRole($subdomain)) {
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
            $html = @file_get_contents($link);    

            if ($html) {    
                $dom = new \DOMDocument();        
                libxml_use_internal_errors(true);        
                $dom->loadHTML($html);            
                libxml_clear_errors();    
                
                $titleElements = $dom->getElementsByTagName('title');        
                if ($titleElements->length > 0) {
                    $title = $titleElements->item(0)->textContent;
                }        
            }
            
            $obj = new \stdClass;
            $obj->name = $title;
            $obj->url = rtrim($link, '/');
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

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]))
                    ->with('message', __('Successfully added link'));
    }

    public function removeLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->hasRole($subdomain)) {
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

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]))
                    ->with('message', __('Successfully removed link'));
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
}