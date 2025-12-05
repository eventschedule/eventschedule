<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Role;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Support\HomePageSettings;
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

        return $this->landing($request);
    }

    public function landing(Request $request, $slug = null)
    {
        if ($slug && $role = Role::whereSubdomain($slug)->first()) {
            return redirect()->route('role.view_guest', ['subdomain' => $role->subdomain]);
        }

        $user = $request->user();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $viewMode = $request->input('view');

        if (! in_array($viewMode, ['calendar', 'list'], true)) {
            $viewMode = 'calendar';
        }

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
                    ->whereIn('roles.type', ['talent', 'schedule', 'venue']);
            });
        }

        $optionsQuery = clone $baseQuery;
        $filteredQuery = clone $baseQuery;

        $filters = [
            'category' => collect((array) $request->input('category'))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'venue' => collect((array) $request->input('venue'))
                ->map(fn ($value) => UrlUtils::decodeId($value))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'curator' => collect((array) $request->input('curator'))
                ->map(fn ($value) => UrlUtils::decodeId($value))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'talent' => collect((array) $request->input('talent'))
                ->map(fn ($value) => UrlUtils::decodeId($value))
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ];

        if ($filters['category']) {
            $filteredQuery->whereIn('category_id', $filters['category']);
        }

        if ($filters['venue']) {
            $filteredQuery->whereHas('roles', function ($query) use ($filters) {
                $query->whereIn('roles.id', $filters['venue'])
                    ->where('roles.type', 'venue')
                    ->where('event_role.is_accepted', true);
            });
        }

        if ($filters['curator']) {
            $filteredQuery->where(function ($query) use ($filters) {
                $query->whereIn('creator_role_id', $filters['curator'])
                    ->orWhereHas('roles', function ($subQuery) use ($filters) {
                        $subQuery->whereIn('roles.id', $filters['curator'])
                            ->where('roles.type', 'curator')
                            ->where('event_role.is_accepted', true);
                    });
            });
        }

        if ($filters['talent']) {
            $filteredQuery->whereHas('roles', function ($query) use ($filters) {
                $query->whereIn('roles.id', $filters['talent'])
                    ->whereIn('roles.type', ['talent', 'schedule'])
                    ->where('event_role.is_accepted', true);
            });
        }

        $optionEvents = $optionsQuery->orderBy('starts_at')->get();
        $calendarEvents = $filteredQuery->orderBy('starts_at')->get();

        if ($viewMode === 'list') {
            $calendarEvents = $calendarEvents
                ->flatMap(function ($event) use ($startOfMonth, $endOfMonth) {
                    if (! $event->days_of_week) {
                        $occursAt = $event->getStartDateTime();

                        if (! $occursAt) {
                            return collect();
                        }

                        return collect([[
                            'event' => $event,
                            'occurs_at' => $occursAt,
                            'occurs_at_display' => $event->localStartsAt(true),
                        ]]);
                    }

                    $occurrences = collect();
                    $currentDate = $startOfMonth->copy();

                    while ($currentDate->lessThanOrEqualTo($endOfMonth)) {
                        if ($event->matchesDate($currentDate)) {
                            $dateString = $currentDate->format('Y-m-d');
                            $occursAt = $event->getStartDateTime($dateString);

                            if ($occursAt) {
                                $occurrences->push([
                                    'event' => $event,
                                    'occurs_at' => $occursAt,
                                    'occurs_at_display' => $event->localStartsAt(true, $dateString),
                                ]);
                            }
                        }

                        $currentDate->addDay();
                    }

                    return $occurrences;
                })
                ->sortBy(function ($occurrence) {
                    return $occurrence['occurs_at'] ? $occurrence['occurs_at']->timestamp : PHP_INT_MAX;
                })
                ->values();
        }

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
            'category' => collect((array) $request->input('category'))
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->map('strval')
                ->unique()
                ->values()
                ->all(),
            'venue' => collect((array) $request->input('venue'))
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->map('strval')
                ->unique()
                ->values()
                ->all(),
            'curator' => collect((array) $request->input('curator'))
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->map('strval')
                ->unique()
                ->values()
                ->all(),
            'talent' => collect((array) $request->input('talent'))
                ->filter(function ($value) {
                    return $value !== null && $value !== '';
                })
                ->map('strval')
                ->unique()
                ->values()
                ->all(),
        ];

        $calendarQueryParams = collect($selectedFilters)
            ->filter(function ($value) {
                if (is_array($value)) {
                    return count(array_filter($value, function ($item) {
                        return $item !== null && $item !== '';
                    })) > 0;
                }

                return $value !== null && $value !== '';
            })
            ->all();

        $activeFilterCount = collect($selectedFilters)->reduce(function ($count, $value) {
            if (is_array($value)) {
                return $count + count(array_filter($value, function ($item) {
                    return $item !== null && $item !== '';
                }));
            }

            return $count + (($value !== null && $value !== '') ? 1 : 0);
        }, 0);

        if ($viewMode !== 'calendar') {
            $calendarQueryParams['view'] = $viewMode;
        }

        $homeContent = $this->buildHomePageContent((bool) $user);

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
            'homeContent' => $homeContent,
            'viewMode' => $viewMode,
            'activeFilterCount' => $activeFilterCount,
            'hasActiveFilters' => $activeFilterCount > 0,
        ]);
    }

    protected function buildHomePageContent(bool $isAuthenticated): array
    {
        $stored = Setting::forGroup('home');

        $layout = HomePageSettings::normalizeLayout($stored['layout'] ?? null);
        $heroAlignment = HomePageSettings::normalizeHeroAlignment($stored['hero_alignment'] ?? null);
        $showDefaultHeroText = HomePageSettings::normalizeBoolean($stored['hero_show_default_text'] ?? true, true);

        $heroTitle = HomePageSettings::clean($stored['hero_title'] ?? null)
            ?? ($isAuthenticated ? __('messages.your_events') : __('messages.upcoming_events'));

        $heroHtml = HomePageSettings::compileHtml(
            $stored['hero_html'] ?? null,
            $stored['hero_markdown'] ?? null,
        );

        if (! $heroHtml && $showDefaultHeroText) {
            $defaultText = $isAuthenticated
                ? __('messages.manage_calendar_intro')
                : __('messages.discover_public_events');

            $heroHtml = '<p>' . e($defaultText) . '</p>';
        }

        $heroCtaLabel = HomePageSettings::clean($stored['hero_cta_label'] ?? null);
        $heroCtaUrl = HomePageSettings::clean($stored['hero_cta_url'] ?? null);

        if ($heroCtaUrl && ! HomePageSettings::isSafeCtaUrl($heroCtaUrl)) {
            $heroCtaUrl = null;
        }

        if (! $heroCtaLabel || ! $heroCtaUrl) {
            $heroCtaLabel = null;
            $heroCtaUrl = null;
        }

        if (! $heroCtaLabel && ! $heroCtaUrl && ! $isAuthenticated) {
            $heroCtaLabel = __('messages.log_in');
            $heroCtaUrl = route('login');
        }

        $heroImage = HomePageSettings::resolveImagePreview(
            isset($stored['hero_image_media_asset_id']) ? (int) $stored['hero_image_media_asset_id'] : null,
            isset($stored['hero_image_media_variant_id']) ? (int) $stored['hero_image_media_variant_id'] : null,
        );

        $heroAlt = HomePageSettings::clean($stored['hero_image_alt'] ?? null);

        $asideTitle = HomePageSettings::clean($stored['aside_title'] ?? null);
        $asideHtml = HomePageSettings::compileHtml(
            $stored['aside_html'] ?? null,
            $stored['aside_markdown'] ?? null,
        );

        $asideAlt = HomePageSettings::clean($stored['aside_image_alt'] ?? null);

        $image = HomePageSettings::resolveImagePreview(
            isset($stored['aside_image_media_asset_id']) ? (int) $stored['aside_image_media_asset_id'] : null,
            isset($stored['aside_image_media_variant_id']) ? (int) $stored['aside_image_media_variant_id'] : null,
        );

        $hasAside = $asideTitle || $asideHtml || ($image['url'] ?? null);

        if (! $hasAside) {
            $layout = HomePageSettings::LAYOUT_FULL;
        }

        return [
            'layout' => $layout,
            'hero' => [
                'title' => $heroTitle,
                'html' => $heroHtml,
                'alignment' => $heroAlignment,
                'show_default_text' => $showDefaultHeroText,
                'cta' => [
                    'label' => $heroCtaLabel,
                    'url' => $heroCtaUrl,
                ],
                'logo' => [
                    'url' => $heroImage['url'] ?? null,
                    'alt' => ($heroImage['url'] ?? null) ? $heroAlt : null,
                ],
            ],
            'aside' => [
                'title' => $asideTitle,
                'html' => $asideHtml,
                'image' => [
                    'url' => $image['url'] ?? null,
                    'alt' => $asideAlt,
                ],
            ],
        ];
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
        $viewMode = $request->input('view');

        if (! in_array($viewMode, ['calendar', 'list'], true)) {
            $viewMode = 'calendar';
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }

        if ($year < 1900 || $year > 2100) {
            $year = now()->year;
        }

        $startOfMonth = null;
        $endOfMonth = null;

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

        if ($viewMode === 'list') {
            $calendarEvents = $calendarEvents
                ->flatMap(function ($event) use ($startOfMonth, $endOfMonth) {
                    if (! $event->days_of_week) {
                        $occursAt = $event->getStartDateTime();

                        if (! $occursAt) {
                            return collect();
                        }

                        return collect([
                            [
                                'event' => $event,
                                'occurs_at' => $occursAt,
                                'occurs_at_display' => $event->localStartsAt(true),
                            ],
                        ]);
                    }

                    $occurrences = collect();
                    $currentDate = $startOfMonth->copy();

                    while ($currentDate->lessThanOrEqualTo($endOfMonth)) {
                        if ($event->matchesDate($currentDate)) {
                            $dateString = $currentDate->format('Y-m-d');
                            $occursAt = $event->getStartDateTime($dateString);

                            if ($occursAt) {
                                $occurrences->push([
                                    'event' => $event,
                                    'occurs_at' => $occursAt,
                                    'occurs_at_display' => $event->localStartsAt(true, $dateString),
                                ]);
                            }
                        }

                        $currentDate->addDay();
                    }

                    return $occurrences;
                })
                ->sortBy(function ($occurrence) {
                    return $occurrence['occurs_at'] ? $occurrence['occurs_at']->timestamp : PHP_INT_MAX;
                })
                ->values();
        }

        $events = $baseQuery
            ->orderBy('starts_at')
            ->paginate(10)
            ->withQueryString();

        $calendarQueryParams = [];

        if ($viewMode !== 'calendar') {
            $calendarQueryParams['view'] = $viewMode;
        }

        return view('home', compact(
            'events',
            'calendarEvents',
            'month',
            'year',
            'startOfMonth',
            'endOfMonth',
            'calendarQueryParams',
            'viewMode',
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