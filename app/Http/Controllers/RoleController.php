<?php

namespace App\Http\Controllers;

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
    public function viewAdmin(Request $request, $subdomain, $tab = 'overview', $year = null, $month = null)
    {
        if (! auth()->user()->hasRole($subdomain)) {
            return redirect('/');
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $events = [];
        
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
                //->whereNotNull('starts_at')
                //->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                ->orderBy('starts_at')
                ->get();

            $unscheduled = Event::with(['role'])
                ->whereVenueId($role->id)
                ->where('is_accepted', true)
                ->whereNull('starts_at')
                ->orderBy('created_at', 'desc')
                ->get();
    
            return view('role/show-admin', compact(
                'role',
                'tab',
                'events',
                'month',
                'year',
                'startOfMonth',
                'endOfMonth',
                'unscheduled',
            ));
        } else if ($tab == 'requests') {
            $events = Event::with(['role'])
                ->whereVenueId($role->id)
                ->whereNull('is_accepted')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return view('role/show-admin', compact(
                'role',
                'tab',
                'events',
            ));
        } else if ($tab == 'team') {
            $members = $role->members()->get();

            return view('role/show-admin', compact(
                'role',
                'tab',
                'members',
            ));

        }

        return view('role/show-admin', compact(
            'role',
            'tab',
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

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']));
    }

    public function removeMember(Request $request, $subdomain, $hash)
    {

    }

    public function viewGuest($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
        ];

        return view('role/show-guest', $data);
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
        $role->accept_talent_requests = true;

        $data = [
            'role' => $role,
            'title' => __('Register'),
        ];

        return view('role/edit', $data);
    }

    public function store(RoleCreateRequest $request): RedirectResponse
    {
        $user = $request->user();        

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = UrlUtils::createDomain($request->name);
        $role->user_id = $user->id;
        $role->save();

        $user->roles()->attach($role->id);

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]));
    }

    public function edit($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
            'title' => __('Edit ' . ucwords($role->type)),
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
        $role->save();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]));
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

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]));
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

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain]));
    }
}