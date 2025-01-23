<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use App\Mail\SupportEmail;
use Mail;

class HomeController extends Controller
{
    public function landing()
    {
        return redirect(route('home'));
    }

    public function home(Request $request)
    {
        if (session('pending_venue') && auth()->user()->countRoles() == 0) {
            $role = Role::whereSubdomain(session('pending_venue'))->firstOrFail();
            if ($role->accept_requests) {
                return redirect()->route('new', ['type' => 'schedule']);
            }
        } else if ($subdomain = session('pending_follow')) {
            $role = Role::whereSubdomain(session('pending_follow'))->firstOrFail();
            
            $user = auth()->user();
            $user->language_code = $role->language_code;
            $user->save();

            return redirect()->route('role.follow', ['subdomain' => $subdomain]);
        }

        $events = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';

        if (! $month) {
            $month = now()->month;
        }
        if (! $year) {
            $year = now()->year;
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $user = $request->user();
        $roleIds = $user->roles()->pluck('roles.id');
        
        $events = Event::with(['roles', 'venue'])
            ->where(function ($query) use ($roleIds, $user) {
                $query->where(function ($query) use ($roleIds) {
                    $query->whereIn('venue_id', $roleIds)
                          ->where('is_accepted', true);
                })->orWhere(function ($query) use ($roleIds) {
                    $query->whereIn('id', function ($query) use ($roleIds) {
                            $query->select('event_id')
                                ->from('event_role')
                                ->whereIn('role_id', $roleIds)
                                ->where('is_accepted', true);
                    });
                })->orWhere(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                    ->orWhereNotNull('days_of_week');
            })
            ->orderBy('starts_at')
            ->get();

        return view('home', compact(
            'events',
            'month',
            'year',
            'startOfMonth',
            'endOfMonth',
        ));
    }

    public function privacy() 
    {
        return view('privacy');
    }

    public function terms() 
    {
        return view('terms');
    }

    public function sitemap()
    {
        $roles = Role::whereNotNull('email')
                    ->orWhereNotNull('phone')
                    ->orderBy(request()->sort_order == 'id' ? 'id' : 'subdomain', request()->sort_order == 'id' ? 'desc' : 'asc')
                    ->get();

        $content = view('sitemap', [
            'roles' => $roles,
            'lastmod' => now()->toIso8601String()
        ]);
        
        return response($content)
            ->header('Content-Type', 'application/xml');
    }
}