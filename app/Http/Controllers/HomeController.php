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
        return view('landing');
    }

    public function home(Request $request)
    {
        if (session('pending_venue') && auth()->user()->countRoles() == 0) {
            $role = Role::whereSubdomain(session('pending_venue'))->firstOrFail();
            if ($role->accept_talent_requests) {
                return redirect()->route('new', ['type' => 'talent']);
            } else if ($role->accept_vendor_requests) {
                return redirect()->route('new', ['type' => 'vendor']);
            }
        } else if ($subdomain = session('pending_follow')) {
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
        
        $events = Event::with(['role'])
            ->where(function ($query) use ($roleIds) {
                $query->where(function ($query) use ($roleIds) {
                    $query->whereIn('venue_id', $roleIds)
                        ->orWhereIn('role_id', $roleIds);
                })->orWhere(function ($query) use ($roleIds) {
                    $query->whereIn('id', function ($query) use ($roleIds) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->whereIn('role_id', $roleIds);
                    });
                });
            })
            ->where('is_accepted', true)
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

    public function message(Request $request)
    {
        $name = trim($request->first_name . ' ' . $request->last_name);
        $email = $request->email;
        $message = $request->message;

        $mail = new SupportEmail($name, $email, $message);
        Mail::to(config('mail.from.address'))->send($mail);

        return redirect(route('landing'))->with('message', __('messages.message_sent'));
    }
}