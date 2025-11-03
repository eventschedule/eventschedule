<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Models\BlogPost;
use App\Utils\UrlUtils;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\SupportEmail;
use Mail;

class HomeController extends Controller
{
    public function root(Request $request, $slug = null)
    {
        if ($slug) {
            return $this->landing($request, $slug);
        }

        if ($request->user()) {
            return $this->home($request);
        }

        return redirect()->route('landing', $request->query());
    }

    public function landing(Request $request, $slug = null)
    {
        if ($slug && $role = Role::whereSubdomain($slug)->first()) {
            return redirect()->route('role.view_guest', ['subdomain' => $role->subdomain]);
        }

        $user = $request->user();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 1900 || $year > 2100) {
            $year = now()->year;
        }

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $baseQuery = Event::with(['roles', 'creatorRole', 'venue'])
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                    ->orWhereNotNull('days_of_week');
            });

        if ($user) {
            $roleIds = $user->roles()->pluck('roles.id');

            $baseQuery->where(function ($query) use ($roleIds, $user) {
                $query->whereIn('id', function ($subQuery) use ($roleIds) {
                    $subQuery->select('event_id')
                        ->from('event_role')
                        ->whereIn('role_id', $roleIds)
                        ->where('is_accepted', true);
                })
                ->orWhere('user_id', $user->id);
            });
        } else {
            $baseQuery->whereHas('roles', function ($query) {
                $query->where('event_role.is_accepted', true)
                    ->where('roles.is_deleted', false)
                    ->where('roles.is_unlisted', false)
                    ->whereIn('roles.type', ['talent', 'schedule']);
            });
        }

        $optionsQuery = clone $baseQuery;
        $filteredQuery = clone $baseQuery;

        $filters = [
            'category' => $request->filled('category') ? (int) $request->input('category') : null,
            'venue' => UrlUtils::decodeId($request->input('venue')),
            'curator' => UrlUtils::decodeId($request->input('curator')),
            'talent' => UrlUtils::decodeId($request->input('talent')),
        ];

        if ($filters['category']) {
            $filteredQuery->where('category_id', $filters['category']);
        }

        if ($filters['venue']) {
            $filteredQuery->whereHas('roles', function ($query) use ($filters) {
                $query->where('roles.id', $filters['venue'])
                    ->where('roles.type', 'venue')
                    ->where('event_role.is_accepted', true);
            });
        }

        if ($filters['curator']) {
            $filteredQuery->where(function ($query) use ($filters) {
                $query->where('creator_role_id', $filters['curator'])
                    ->orWhereHas('roles', function ($subQuery) use ($filters) {
                        $subQuery->where('roles.id', $filters['curator'])
                            ->where('roles.type', 'curator')
                            ->where('event_role.is_accepted', true);
                    });
            });
        }

        if ($filters['talent']) {
            $filteredQuery->whereHas('roles', function ($query) use ($filters) {
                $query->where('roles.id', $filters['talent'])
                    ->whereIn('roles.type', ['talent', 'schedule'])
                    ->where('event_role.is_accepted', true);
            });
        }

        $optionEvents = $optionsQuery->orderBy('starts_at')->get();
        $calendarEvents = $filteredQuery->orderBy('starts_at')->get();

        $venueOptions = $optionEvents
            ->map->venue
            ->filter(function ($role) use ($user) {
                if (! $role || $role->is_deleted) {
                    return false;
                }

                if (! $user && $role->is_unlisted) {
                    return false;
                }

                return true;
            })
            ->unique('id')
            ->sortBy(function ($role) {
                return Str::lower($role->translatedName());
            })
            ->values();

        $curatorOptions = $optionEvents
            ->flatMap(function ($event) use ($user) {
                $curators = $event->roles->filter(function ($role) use ($user) {
                    if ($role->is_deleted || ($role->is_unlisted && ! $user)) {
                        return false;
                    }

                    return $role->isCurator();
                });

                if ($event->creatorRole
                    && ! $event->creatorRole->is_deleted
                    && ! (! $user && $event->creatorRole->is_unlisted)
                    && $event->creatorRole->isCurator()) {
                    $curators->push($event->creatorRole);
                }

                return $curators;
            })
            ->unique('id')
            ->sortBy(function ($role) {
                return Str::lower($role->translatedName());
            })
            ->values();

        $talentOptions = $optionEvents
            ->flatMap(function ($event) use ($user) {
                return $event->roles->filter(function ($role) use ($user) {
                    if ($role->is_deleted || ! $role->isTalent()) {
                        return false;
                    }

                    if (! $user && $role->is_unlisted) {
                        return false;
                    }

                    return true;
                });
            })
            ->unique('id')
            ->sortBy(function ($role) {
                return Str::lower($role->translatedName());
            })
            ->values();

        $formatRoleOptions = function ($roles) {
            return $roles->map(function ($role) {
                return [
                    'id' => UrlUtils::encodeId($role->id),
                    'name' => $role->translatedName(),
                ];
            })->values()->all();
        };

        $selectedFilters = [
            'category' => $request->input('category'),
            'venue' => $request->input('venue'),
            'curator' => $request->input('curator'),
            'talent' => $request->input('talent'),
        ];

        $calendarQueryParams = collect($selectedFilters)
            ->filter(function ($value) {
                return $value !== null && $value !== '';
            })
            ->all();

        return view('landing', [
            'calendarEvents' => $calendarEvents,
            'month' => $month,
            'year' => $year,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'categories' => get_translated_categories(),
            'venueOptions' => $formatRoleOptions($venueOptions),
            'curatorOptions' => $formatRoleOptions($curatorOptions),
            'talentOptions' => $formatRoleOptions($talentOptions),
            'selectedFilters' => $selectedFilters,
            'calendarQueryParams' => $calendarQueryParams,
            'isAuthenticated' => (bool) $user,
        ]);
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
        
        $baseQuery = Event::with(['roles', 'creatorRole', 'tickets'])
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
            });

        $calendarEvents = (clone $baseQuery)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
                    ->orWhereNotNull('days_of_week');
            })
            ->orderBy('starts_at')
            ->get();

        $events = $baseQuery
            ->orderBy('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('home', compact(
            'events',
            'calendarEvents',
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

        $events = Event::with(['venue', 'roles'])
            ->orderBy(request()->has('events') ? 'id' : 'starts_at', 'desc')
            ->get();

        $blogPosts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->get();

        $content = view('sitemap', [
            'roles' => ! request()->has('events') ? $roles : [],
            'events' => ! request()->has('roles') ? $events : [],
            'blogPosts' => request()->has('events') || request()->has('roles') ? [] : $blogPosts,
            'lastmod' => now()->toIso8601String()
        ]);
        
        return response($content)
            ->header('Content-Type', 'application/xml');
    }
}