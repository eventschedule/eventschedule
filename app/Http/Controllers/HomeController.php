<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Models\BlogPost;
use Carbon\Carbon;
use App\Mail\SupportEmail;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function landing($slug = null)
    {
        if ($slug && $role = Role::whereSubdomain($slug)->first()) {
            return redirect()->route('role.view_guest', ['subdomain' => $role->subdomain]);
        }

        return redirect(route('home'));
    }

    public function home(Request $request)
    {
        $subdomain = session('pending_follow');
        
        if (! $subdomain) {
            $subdomain = session('pending_request');
        }

        if ($subdomain) {
            $role = Role::whereSubdomain($subdomain)->firstOrFail();
            
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
        
        $events = Event::with('roles')
            ->where(function ($query) use ($roleIds, $user) {
                $query->where(function ($query) use ($roleIds) {
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

    public function sitemap()
    {
        $roles = Role::with('groups')
                ->where(function($query) {
                    $query->where(function($q) {
                        $q->whereNotNull('email')
                          ->whereNotNull('email_verified_at');
                    })->orWhere(function($q) {
                        $q->whereNotNull('phone')
                          ->whereNotNull('phone_verified_at'); 
                    });
                })
                ->where('is_deleted', false)
                ->orderBy(request()->has('roles') ? 'id' : 'subdomain', request()->has('roles') ? 'desc' : 'asc')
                ->get();

        $events = Event::with(['roles'])
            ->orderBy(request()->has('events') ? 'id' : 'starts_at', 'desc')
            ->get();

        $blogPosts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->get();

        $sitemapView = view('sitemap', [
            'roles' => ! request()->has('events') ? $roles : [],
            'events' => ! request()->has('roles') ? $events : [],
            'blogPosts' => request()->has('events') || request()->has('roles') ? [] : $blogPosts,
            'lastmod' => now()->toIso8601String()
        ]);
        
        // Check if the request is for the gzipped version
        $isGzipped = str_ends_with(request()->path(), '.gz');
        
        if ($isGzipped) {
            $content = $sitemapView->render();
            $gzipped = gzencode($content, 9);
            return response($gzipped)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Encoding', 'gzip');
        }
        
        return response($sitemapView)
            ->header('Content-Type', 'application/xml');
    }

    public function submitFeedback(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string|max:5000',
        ]);

        $user = $request->user();
        $feedback = $request->input('feedback');

        try {
            Mail::to('contact@eventschedule.com')->send(new SupportEmail(
                $user->name ?? $user->email,
                $user->email,
                $feedback
            ));

            return response()->json([
                'success' => true,
                'message' => __('messages.feedback_submitted')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.feedback_failed')
            ], 500);
        }
    }
}