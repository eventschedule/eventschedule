@php
    $headerTemplates = $headerTemplates ?? collect();
    // This page draws a calendar: the layout prints the month's stylesheet in its head
    // (partials/month-kit-styles), where an owner's custom CSS still comes after it. Said on the
    // request, which the layout reads when its own turn comes: a page's body is rendered first.
    request()->attributes->set('month_kit', true);
@endphp
<style>
    [v-cloak] { display: none !important; }
    .hover-accent:not(:disabled):hover {
        background-color: var(--es-accent) !important;
        color: var(--es-contrast) !important;
    }
    /* GP Month Navigation */
    .gp-month-nav { background-color: #f3f4f6; }
    .gp-month-separator { background-color: rgba(0,0,0,0.08); }
    .gp-month-btn:hover { background-color: rgba(0,0,0,0.04); }
    .dark .gp-month-nav { background: linear-gradient(135deg, rgb(var(--ap-border)), rgb(var(--ap-surface))); border: 1px solid rgba(255,255,255,0.06); }
    .dark .gp-month-nav .gp-month-btn { color: rgb(var(--ap-ink-3)); }
    .dark .gp-month-nav .gp-month-btn:hover { background-color: rgba(255,255,255,0.1); color: rgb(var(--ap-ink-2)); }
    .dark .gp-month-separator { background-color: rgba(255,255,255,0.08); }
    .es-date-month { color: var(--es-date-month); }
    .dark .es-date-month { color: var(--es-date-month-dark); }
</style>
<div class="flex h-full flex-col" id="calendar-app">
@php
    $role = $role ?? null;
    // A custom label is the schedule owner's own text, and this whole partial is one Vue mount:
    // Vue compiles a mustache it finds in a text node or an option's label, and would run one for
    // every visitor of the public page. Some fifty places below print a label, so the pair of
    // braces is broken once, here, where no new place can forget it.
    $label = fn($key) => $role ? str_replace('{'.'{', '{ {', $role->customLabel($key)) : __('messages.' . $key);
    $isAdminRoute = $route == 'admin';
    $alwaysShowFilters = in_array($route ?? '', ['guest', 'admin']);
    $stickyBleedClass = ($route === 'guest' && !(isset($embed) && $embed)) ? '-mx-5 px-5' : '-mx-4 px-4';
    // Whether this is a guest PAGE (or embed): its list is in the page once, the cards from a
    // tablet up and the rows on a phone (the name is from when it was rows at every width), and
    // it has the chips above the list, the ticket line on a card and the month on a phone.
    // Not ?graphic=1: that renders a picture to share, and keeps both lists as they always were.
    $guestRows = ($route ?? '') === 'guest' && ! request()->graphic;
    $guestEmbed = (bool) (isset($embed) && $embed);
    // The path this schedule's own page lives at, with no trailing slash: '' when the schedule
    // owns the whole host (hosted subdomains, custom domains) and '/{subdomain}' under selfhost's
    // path-based routing. Built here rather than inline in the Vue data because Blade's @json
    // splits its expression on commas. Sub-schedule URLs are derived from it so they do not
    // hardcode one deployment's shape - '/{subdomain}/{group}' 404s on hosted.
    $guestBasePath = $role && $role->subdomain
        ? rtrim(parse_url(route('role.view_guest', ['subdomain' => $role->subdomain]), PHP_URL_PATH) ?? '', '/')
        : '';
    $firstDay = $role?->first_day_of_week ?? 0;
    // The owner's event animation (resources/css/list-reveal.css), or ?list_animation= when an
    // owner is previewing an unsaved choice. Only on the schedule's own guest page: never in the
    // admin views or the home dashboard.
    $listAnimation = ($route === 'guest' && $role) ? $role->activeListAnimation() : 'none';
    $lastDay = ($firstDay + 6) % 7;
    $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek($firstDay);
    $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek($lastDay);
    $currentDate = $startOfMonth->copy();
    $unavailable = [];
    
    // The zone this whole calendar reasons in. Events are placed by their own schedule's
    // calendar date, so anything that decides which day a date belongs to - the "today"
    // highlight, and the past-event filters in the Vue app below - has to agree with it, or an
    // event is highlighted on one day and filtered out as though it were on another.
    //
    // The dashboard (home.blade.php includes this with route => 'home') passes no $role: it
    // aggregates many schedules, so there is no schedule clock and the viewer's own day is the
    // right answer there.
    $calendarTimezone = ($role ?? null)?->timezone ?: (auth()->user()?->timezone ?: config('app.timezone'));
    $today = Carbon\Carbon::now($calendarTimezone)->startOfDay();

    $subdomain = $subdomain ?? null;

    if (request()->graphic) {
        // Keep event processing for graphic mode (calendar-graphic.blade.php uses $events directly)
        $eventGroupIds = [];
        $eventCategoryIds = [];

        // Eager-load creator role so direction and category resolution avoid N+1 queries.
        if (method_exists($events, 'loadMissing')) {
            $events->loadMissing('creatorRole');
        }
        if (isset($pastEvents) && method_exists($pastEvents, 'loadMissing')) {
            $pastEvents->loadMissing('creatorRole');
        }

        $eventsMap = [];
        foreach ($events as $event) {
            $checkDate = $startOfMonth->copy();
            if (isset($event->group_id)) {
                $eventGroupIds[] = $event->group_id;
            }
            if (isset($event->category_id)) {
                $eventCategoryIds[] = $event->category_id;
            }
            while ($checkDate->lte($endOfMonth)) {
                if ($event->matchesDate($checkDate)) {
                    $dateStr = $checkDate->format('Y-m-d');
                    if (!isset($eventsMap[$dateStr])) {
                        $eventsMap[$dateStr] = [];
                    }
                    $eventsMap[$dateStr][] = $event;
                }
                $checkDate->addDay();
            }
        }

        $uniqueCategoryIds = array_unique($eventCategoryIds);
        $hasOnlineEvents = collect($events)->contains(fn($event) => !empty($event->event_url));

        $displayLang = isset($role) ? $role->displayLanguageCode() : 'en';

        $eventToVueArray = function($event) use ($role, $subdomain, $route, $displayLang) {
            $groupId = isset($role) ? $event->getGroupIdForSubdomain($role->subdomain) : null;
            $eventName = $event->nameInLanguage($displayLang, $role ?? null);
            $shortDescription = $event->shortDescriptionInLanguage($displayLang, $role ?? null);
            $venueName = $event->getVenueDisplayName(true, $displayLang);
            // See calendarEventToVueArray(): direction defers to whoever owns each string, so an
            // aggregated event is not measured against the viewing schedule's language.
            $dirLang = $event->creatorRole?->language_code ?: $displayLang;
            return [
                'id' => \App\Utils\UrlUtils::encodeId($event->id),
                'group_id' => $groupId ? \App\Utils\UrlUtils::encodeId($groupId) : null,
                'category_id' => $event->category_id,
                'category_color' => $event->resolveCategoryColor(),
                'name' => $eventName,
                'dir' => content_dir_for_language($eventName, $dirLang),
                'short_description' => $shortDescription,
                'description_dir' => content_dir_for_language($shortDescription, $dirLang),
                'venue_name' => $venueName,
                'venue_dir' => content_dir_for_language($venueName, $event->venue?->language_code ?: $dirLang),
                'venue_subdomain' => $event->venue?->subdomain ?: null,
                'is_free' => $event->isFree(),
                'starts_at' => $event->starts_at,
                'days_of_week' => $event->days_of_week,
                'local_starts_at' => $event->localStartsAt(),
                'local_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
                'utc_date' => $event->starts_at ? $event->getStartDateTime(null, false)->format('Y-m-d') : null,
                'guest_url' => $event->getGuestUrl(isset($subdomain) ? $subdomain : '', ''),
                // The same image fields the month payload carries (Event::cardImageFields()).
                ...$event->cardImageFields(),
                'can_edit' => auth()->user() && auth()->user()->canEditEvent($event),
                'edit_url' => auth()->user() && auth()->user()->canEditEvent($event)
                    ? (isset($role) ? app_url(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)], false)) : app_url(route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($event->id)], false)))
                    : null,
                'recurring_end_type' => $event->recurring_end_type ?? 'never',
                'recurring_end_value' => $event->recurring_end_value,
                'recurring_frequency' => $event->recurring_frequency,
                'recurring_interval' => $event->recurring_interval,
                'recurring_include_dates' => $event->recurring_include_dates ?? [],
                'recurring_exclude_dates' => $event->recurring_exclude_dates ?? [],
                'start_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
                'is_online' => !empty($event->event_url),
                'registration_url' => $event->registrationHref(),
                'ticket_price' => $event->ticket_price,
                'ticket_currency_code' => $event->ticket_currency_code,
                'coupon_code' => $event->coupon_code,
                'coupon_discount_label' => $event->couponDiscountLabel(),
                'duration' => $event->duration,
                'is_multi_day' => $event->duration >= 24,
                'local_end_date' => $event->duration >= 24
                    ? $event->getEndDateTime(null, true)->format('Y-m-d')
                    : null,
                'parts' => $event->parts->map(fn($part) => [
                    'id' => \App\Utils\UrlUtils::encodeId($part->id),
                    'name' => $part->nameInLanguage($displayLang, $event->getTranslationLanguageCode()),
                    'start_time' => $part->start_time,
                    'end_time' => $part->end_time,
                ])->values()->toArray(),
                'video_count' => $event->approved_videos_count ?? 0,
                'comment_count' => $event->approved_comments_count ?? 0,
                'venue_guest_url' => ($event->venue && isset($role) && $event->venue->subdomain === $role->subdomain) ? null : ($event->venue?->getGuestUrl() ?: null),
                'talent' => $event->roles->filter(fn($r) => $r->type === 'talent' && ($route !== 'guest' || $r->isClaimed()))->map(fn($r) => [
                    'name' => $r->name,
                    'dir' => content_dir_for_language($r->name, $r->language_code ?: $dirLang),
                    'profile_image' => $r->getProfileImageUrl(\App\Utils\ImageUtils::VARIANT_WIDTH) ?: null,
                    'header_image' => $r->headerImageUrl(960),
                    'guest_url' => (isset($role) && $r->subdomain === $role->subdomain) ? null : ($r->getGuestUrl() ?: null),
                ])->values()->toArray(),
                'videos' => $event->relationLoaded('approvedVideos') ? $event->approvedVideos->take(3)->map(fn($v) => [
                    'youtube_url' => $v->youtube_url,
                    'thumbnail_url' => \App\Utils\UrlUtils::getYouTubeThumbnail($v->youtube_url),
                    'embed_url' => \App\Utils\UrlUtils::getYouTubeEmbed($v->youtube_url) ?: null,
                ])->values()->toArray() : [],
                'recent_comments' => $event->relationLoaded('approvedComments') ? $event->approvedComments->take(2)->map(fn($c) => [
                    'author' => $c->submitterName(),
                    'text' => Str::limit($c->comment, 80),
                ])->values()->toArray() : [],
                'photos' => $event->relationLoaded('approvedPhotos') ? $event->approvedPhotos->take(4)->map(fn($p) => [
                    'url' => $p->photo_url,
                ])->values()->toArray() : [],
                'photo_count' => $event->approved_photos_count ?? 0,
                'occurrenceDate' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
                'uniqueKey' => \App\Utils\UrlUtils::encodeId($event->id),
                'submit_video_url' => isset($role) ? route('event.submit_video', ['subdomain' => $role->subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : null,
                'submit_comment_url' => isset($role) ? route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : null,
                'submit_photo_url' => isset($role) ? route('event.submit_photo', ['subdomain' => $role->subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : null,
                'polls' => (isset($role) && $role->isPro() && $event->relationLoaded('polls')) ? $event->polls->map(fn($poll) => [
                    'id' => \App\Utils\UrlUtils::encodeId($poll->id),
                    'question' => $poll->question,
                    'options' => $poll->options,
                    'total_votes' => $poll->votes_count ?? $poll->votes()->count(),
                    'results' => $poll->getResults(),
                    'user_vote' => auth()->check() ? $poll->getUserVote(auth()->id()) : null,
                    'is_active' => $poll->is_active,
                ])->values()->toArray() : [],
                'poll_count' => (isset($role) && $role->isPro()) ? ($event->polls_count ?? 0) : 0,
                'vote_poll_url' => (isset($role) && $role->isPro()) ? route('event.vote_poll', ['subdomain' => $role->subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($event->id), 'poll_hash' => 'POLL_HASH']) : null,
                // Read only against the schedule being viewed - see publicCustomFieldValuesFor().
                'custom_field_values' => $event->publicCustomFieldValuesFor($role ?? null),
                'fan_comments_enabled' => $event->isFanCommentsEnabled(),
                'fan_photos_enabled' => $event->isFanPhotosEnabled(),
                'fan_videos_enabled' => $event->isFanVideosEnabled(),
                // Always false here: the ?graphic=1 queries leave password-protected events out.
                // Kept so this payload has the key the Ajax builders' payloads carry.
                'is_password_protected' => $event->isPasswordProtected(),
            ];
        };

        $eventsForVue = [];
        foreach ($events as $event) {
            $eventsForVue[] = $eventToVueArray($event);
        }

        $pastEventsForVue = [];
        foreach (($pastEvents ?? collect()) as $event) {
            $pastEventsForVue[] = $eventToVueArray($event);
        }

        $eventsMapForVue = [];
        foreach ($eventsMap as $date => $eventsForDate) {
            $eventsMapForVue[$date] = array_map(function($event) {
                return \App\Utils\UrlUtils::encodeId($event->id);
            }, $eventsForDate);
        }
    } else {
        // Ajax mode - event data will be loaded via fetch
        $eventsForVue = [];
        $eventsMapForVue = [];
        $pastEventsForVue = [];
        $uniqueCategoryIds = [];
        $hasOnlineEvents = false;
    }

    // Prepare groups data for Vue
    $groupsForVue = [];
    if (isset($role) && $role->groups) {
        foreach ($role->groups as $group) {
            $groupsForVue[] = [
                'id' => \App\Utils\UrlUtils::encodeId($group->id),
                'slug' => $group->slug,
                'name' => $group->translatedName(),
                'color' => $group->color,
            ];
        }
    }

    // Custom fields for the Vue filters and search. Pro only: custom fields are a Pro feature,
    // and the payload's custom_field_values are empty for a schedule that is not
    // (Event::publicCustomFieldValuesFor()), so a filter here would only ever offer nothing.
    //
    // $filterCustomFields are the "Show as filter" fields (Role::isEventCustomFieldFilter()).
    // `index` is the stable {custom_N} number and names the ?custom_N= URL param; a field saved
    // before indices existed has none and simply gets no URL param - never its position, which
    // shifts when fields are reordered and would silently repoint a printed link.
    //
    // $searchableCustomFields are every public text or option field, filter or not: the search
    // box matches their values (and translated option labels). Switch and date values ("1",
    // "2026-09-29") are left out, or searching "1" would match every switched-on event.
    //
    // $initialCustomFilters seeds the selection from ?custom_N=, so a shared "Room A" link opens
    // already filtered. Built here, not in the Vue data, because Blade's @json splits on commas.
    $filterCustomFields = [];
    $searchableCustomFields = [];
    $initialCustomFilters = [];
    $initialCustomFilterLabels = [];
    // The PHP twin of the Vue normKey(): collapse whitespace, trim, lowercase. Collapse FIRST:
    // trim() only strips ASCII whitespace, so a non-breaking space at the edge (options pasted
    // from a document) would survive it and never match the JS key, which trims Unicode spaces.
    // A malformed UTF-8 value makes preg_replace() return null, hence the fallback.
    $normCustomFilter = fn ($value) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $value) ?? ''));
    $urlCustomFilters = \App\Utils\CustomFieldUtils::filterParams(request()->query());
    if (isset($role) && $role->event_custom_fields && $role->isPro()) {
        $showFieldTranslation = ($isAdminRoute && auth()->check()) ? (app()->getLocale() === 'en') : showing_translation($role ?? null);
        foreach ($role->getEventCustomFields() as $key => $field) {
            $type = $field['type'] ?? 'string';
            if (!empty($field['private']) || !in_array($type, ['string', 'multiline_string', 'dropdown', 'multiselect'], true)) {
                continue;
            }

            $originalOptions = \App\Models\Role::customFieldOptions($field);
            $optionsMap = new \stdClass();
            if ($showFieldTranslation && !empty($field['options_en'])) {
                $translatedOptions = array_values(array_filter(array_map('trim', explode(',', $field['options_en']))));
                if (count($originalOptions) === count($translatedOptions)) {
                    $optionsMap = (object) array_combine($originalOptions, $translatedOptions);
                }
            }

            $searchableCustomFields[(string) $key] = ['optionsMap' => $optionsMap];

            if (! \App\Models\Role::isEventCustomFieldFilter($field)) {
                continue;
            }

            $index = isset($field['index']) && (int) $field['index'] >= 1 && (int) $field['index'] <= 10 ? (int) $field['index'] : null;
            $filterCustomFields[] = [
                'key' => (string) $key,
                'name' => ($showFieldTranslation && !empty($field['name_en'])) ? $field['name_en'] : ($field['name'] ?? ''),
                'type' => $type,
                'index' => $index,
                'options' => $type === 'string' ? [] : $originalOptions,
                'optionsMap' => $optionsMap,
            ];

            // filterParams(): strings only, invalid UTF-8 scrubbed (it would make @json print
            // nothing and take the whole script down), capped at the text field's own max.
            $initial = $index ? ($urlCustomFilters['custom_' . $index] ?? '') : '';
            if ($initial !== '') {
                if ($type !== 'string') {
                    // An option list only accepts one of its own options. Compared without case,
                    // then replaced with the stored spelling, so ?custom_1=room+a still selects
                    // "Room A" while a stale or hand-edited value selects nothing.
                    $match = collect($originalOptions)->first(fn ($option) => $normCustomFilter($option) === $normCustomFilter($initial));
                    $initial = $match ?? '';
                }
            }
            // The label keeps the spelling; the selection is the same normalization as the Vue
            // normKey() (collapse whitespace, trim, lowercase), so no watcher fires on load and
            // rewrites the address the visitor arrived at.
            $initialCustomFilterLabels[(string) $key] = $initial;
            $initialCustomFilters[(string) $key] = $normCustomFilter($initial);
        }
    }
    $initialCustomFilters = (object) $initialCustomFilters;
    $initialCustomFilterLabels = (object) $initialCustomFilterLabels;
    $searchableCustomFields = (object) $searchableCustomFields;

    // The base of the link "Copy link" builds: the page the visitor is on for the guest view
    // (built in JS from location.origin + guestBasePath, so a custom domain stays the domain),
    // and the schedule's own guest page for the admin view, where the address bar is the AP's.
    // getCanonicalUrl(): the custom domain when the page is served on it, else the subdomain.
    $filterShareBaseUrl = ($isAdminRoute && $role && $role->subdomain)
        ? rtrim($role->getCanonicalUrl() ?: route('role.view_guest', ['subdomain' => $role->subdomain]), '/')
        : '';

    // Labels for the active-filter chips, resolved through customLabel() like the panel's own.
    $filterChipLabels = [
        'schedule' => $label('schedule'),
        'category' => $label('category'),
        'venue' => $label('venue'),
        'free_entry' => $label('free_entry'),
        'online' => $label('online'),
        'remove' => __('messages.remove'),
    ];

    $accentColor = $accentColor ?? (isset($role) && $role->accent_color ? $role->accent_color : '#4E81FA');
    $contrastColor = accent_contrast_color($accentColor);

    if ($isAdminRoute) {
        $accentColor = '#4E81FA';
        $contrastColor = '#ffffff';
    }

    $dateMonthColorLight = \App\Utils\ColorUtils::readableAccentColor($accentColor, '#ffffff', '#111827');
    $dateMonthColorDark = \App\Utils\ColorUtils::readableAccentColor($accentColor, '#1e1e1e', '#ffffff');
@endphp

{{-- Panel wrapper --}}
<div style="--es-accent: {{ $accentColor }}; --es-contrast: {{ $contrastColor }}; --es-date-month: {{ $dateMonthColorLight }}; --es-date-month-dark: {{ $dateMonthColorDark }}">

@if (! request()->graphic)
<header class="{{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}"
    @if ($route == 'guest')
        :class="currentView === 'list' ? 'pt-0 pb-0' : 'pt-2 pb-4'"
    @else
        :class="currentView === 'list' ? (hasDesktopFilters ? 'pt-2 pb-4' : 'pt-0 pb-0') : 'pt-2 pb-4'"
    @endif
>
    {{-- Main container: Stacks content on mobile, aligns in a row on desktop. --}}
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-center md:justify-between gap-4">

        {{-- Month and Year Title: Always visible and positioned first (hidden in list view).

             h2 by default, because on most including pages something else is the page's subject -
             the dashboard's title, the schedule name on show-admin, the guest banner/compact
             headers - and an h1 here was a second one announcing a month. But show-guest-embed
             has no header partial at all, so there the calendar IS the document and it passes
             h1. Do not assume every caller supplies its own h1; two of them did not, which is
             what a previous version of this comment got wrong.

             The #month-year-title id is unchanged; it is a documented custom-CSS hook
             (marketing/custom-css.blade.php) and show-guest.blade.php targets it by id. --}}
        @php $calendarHeadingTag = ($calendarHeadingTag ?? 'h2') === 'h1' ? 'h1' : 'h2'; @endphp
        <{{ $calendarHeadingTag }} id="month-year-title" v-show="currentView === 'calendar'" class="text-2xl font-semibold leading-6 flex-shrink-0 {{ ($tab ?? '') == 'availability' ? '' : 'hidden md:block' }} text-gray-900 dark:text-gray-100" {!! ($eventLayout ?? 'calendar') === 'list' ? 'style="display:none"' : '' !!}>
            @if ($route === 'guest' && !request()->graphic)
            <time :datetime="monthYearDatetime" v-text="monthYearLabel"></time>
            @else
            <time datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->locale($isAdminRoute && auth()->check() ? app()->getLocale() : (session()->has('translate') ? (isset($role) && $role->translation_language_code ? $role->translation_language_code : 'en') : (isset($role) && $role->language_code ? $role->language_code : 'en')))->translatedFormat('F Y') }}</time>
            @endif
        </{{ $calendarHeadingTag }}>


        {{-- All Controls Wrapper: Groups all interactive elements. Stacks on mobile, row on desktop. --}}
        <div class="flex {{ ($tab ?? '') == 'availability' ? 'flex-row flex-wrap items-center' : 'flex-col' }} md:flex-row md:flex-nowrap md:items-center md:ms-auto gap-3">

            {{-- Month Navigation Controls --}}
            <div id="month-nav-controls" v-show="currentView === 'calendar'" class="flex items-center shadow-sm {{ $route === 'guest' ? 'gk-phone-off gp-month-nav rounded-xl' : 'bg-white/95 dark:bg-gray-900/95 rounded-md' }} {{ ($tab ?? '') == 'availability' ? '' : 'hidden md:flex' }}" {!! ($eventLayout ?? 'calendar') === 'list' ? 'style="display:none"' : '' !!}>
                @if ($route === 'guest' && !request()->graphic)
                <button @click="navigateMonth(-1)" class="flex h-11 w-14 items-center justify-center rounded-s-xl border-transparent pe-1 text-gray-400 hover:text-gray-500 focus:relative md:w-11 md:pe-0 transition-all duration-200 gp-month-btn">
                    <span class="sr-only">{{ __('messages.previous_month') }}</span>
                    <svg class="h-6 w-6 {{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div class="gp-month-separator w-px h-5 flex-shrink-0"></div>
                <button @click="navigateMonth(0)" class="flex h-11 items-center justify-center px-4 text-base font-semibold text-gray-900 dark:text-gray-100 focus:relative transition-all duration-200 gp-month-btn">
                    <span class="h-6 flex items-center">{{ __('messages.this_month') }}</span>
                </button>
                <div class="gp-month-separator w-px h-5 flex-shrink-0"></div>
                <button @click="navigateMonth(1)" class="flex h-11 w-14 items-center justify-center rounded-e-xl border-transparent ps-1 text-gray-400 hover:text-gray-500 focus:relative md:w-11 md:ps-0 transition-all duration-200 gp-month-btn">
                    <span class="sr-only">{{ __('messages.next_month') }}</span>
                    <svg class="h-6 w-6 {{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" clip-rule="evenodd" />
                    </svg>
                </button>
                @else
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}" class="flex h-11 w-14 items-center justify-center rounded-s-md border-s border-y border-gray-300 dark:border-gray-600 pe-1 text-gray-400 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:relative md:w-11 md:pe-0 md:hover:bg-gray-50 dark:md:hover:bg-gray-700" rel="nofollow">
                    <span class="sr-only">{{ __('messages.previous_month') }}</span>
                    <svg class="h-6 w-6 {{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ $route == 'home' ? route('home') : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => now()->year, 'month' => now()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => now()->year, 'month' => now()->month]) }}" class="flex h-11 items-center justify-center border-y border-gray-300 dark:border-gray-600 px-4 text-base font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 focus:relative">
                    <span class="h-6 flex items-center">{{ __('messages.this_month') }}</span>
                </a>
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}" class="flex h-11 w-14 items-center justify-center rounded-e-md border-e border-y border-gray-300 dark:border-gray-600 ps-1 text-gray-400 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:relative md:w-11 md:ps-0 md:hover:bg-gray-50 dark:md:hover:bg-gray-700" rel="nofollow">
                    <span class="sr-only">{{ __('messages.next_month') }}</span>
                    <svg class="h-6 w-6 {{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" clip-rule="evenodd" />
                    </svg>
                </a>
                @endif
            </div>

            {{-- Save Button --}}
            @if ($route == 'admin' && $role->email_verified_at && !(auth()->check() && auth()->user()->isViewer($role->subdomain)))
                @if ($tab == 'availability')
                    <x-brand-button id="saveButton" :disabled="true" class="flex-grow md:flex-grow-0">
                        <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.5 20Q4.22 20 2.61 18.43 1 16.85 1 14.58 1 12.63 2.17 11.1 3.35 9.57 5.25 9.15 5.88 6.85 7.75 5.43 9.63 4 12 4 14.93 4 16.96 6.04 19 8.07 19 11 20.73 11.2 21.86 12.5 23 13.78 23 15.5 23 17.38 21.69 18.69 20.38 20 18.5 20H13Q12.18 20 11.59 19.41 11 18.83 11 18V12.85L9.4 14.4L8 13L12 9L16 13L14.6 14.4L13 12.85V18H18.5Q19.55 18 20.27 17.27 21 16.55 21 15.5 21 14.45 20.27 13.73 19.55 13 18.5 13H17V11Q17 8.93 15.54 7.46 14.08 6 12 6 9.93 6 8.46 7.46 7 8.93 7 11H6.5Q5.05 11 4.03 12.03 3 13.05 3 14.5 3 15.95 4.03 17 5.05 18 6.5 18H9V20M12 13Z" />
                        </svg>
                        {{ __('messages.save') }}
                    </x-brand-button>
                @endif
            @endif

            {{-- Mobile: Filters + Add Event buttons side-by-side (not shown on guest route - hero version used instead) --}}
            @if ($route != 'guest' && ($tab ?? '') != 'availability')
            <div class="md:hidden flex flex-row gap-2 w-full mb-3 calendar-phone-actions">
                {{-- Mobile Filters Button (always shown when filters exist) --}}
                <template v-if="{!! $alwaysShowFilters ? 'true' : 'dynamicFilterCount > 0' !!}">
                    <button @click="showFiltersDrawer = true"
                            :style="activeFilterCount > 0 ? 'background-color: var(--brand-button-bg); color: #fff; border-color: var(--brand-button-bg);' : ''"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5
                                   border border-gray-300 dark:border-gray-600 rounded-md transition-all duration-200
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                   text-base font-semibold {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                        </svg>
                        {{ $label('filters') }}
                        <span v-if="activeFilterCount > 0"
                              class="ms-1 px-1.5 py-0.5 text-xs bg-white rounded-full"
                              style="color: var(--brand-button-bg);">
                            @{{ activeFilterCount }}
                        </span>
                    </button>
                </template>
                @php
                    $headerTemplates = ($route == 'admin' && $tab == 'schedule' && $role->email_verified_at && $role->isPro() && ! (auth()->check() && auth()->user()->isViewer($role->subdomain)))
                        ? $role->eventTemplates
                        : collect();
                @endphp
                {{-- Mobile Add Event Button --}}
                @if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule' && !(auth()->check() && auth()->user()->isViewer($role->subdomain)))
                    <x-brand-link href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}" class="flex-1">
                        <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('messages.add_event') }}
                    </x-brand-link>
                    @if ($headerTemplates->isNotEmpty())
                    <button type="button" class="js-template-picker-open md:hidden inline-flex items-center justify-center gap-1.5 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-base font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                        {{ __('messages.use_a_template') }}
                    </button>
                    @endif
                @endif
            </div>
            @endif

            {{-- Desktop: Filters Button with label - AP only --}}
            @if ($route == 'admin' && ($tab ?? '') != 'availability')
            <template v-if="{!! $alwaysShowFilters ? 'true' : 'dynamicFilterCount > 0' !!}">
                <button @click="showDesktopFiltersModal = true"
                        :class="currentView === 'list' ? 'md:!inline-flex' : ''"
                        :style="activeFilterCount > 0 ? 'background-color: var(--brand-button-bg); color: #fff; border-color: var(--brand-button-bg);' : ''"
                        class="hidden md:inline-flex items-center justify-center gap-2 px-4 py-2.5
                               border border-gray-300 dark:border-gray-600 rounded-md transition-all duration-200
                               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                               text-base font-semibold hover:bg-gray-50 dark:hover:bg-gray-700
                               relative">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                    </svg>
                    {{ $label('filters') }}
                    {{-- Active filter count badge --}}
                    <span v-if="activeFilterCount > 0"
                          class="ms-1 px-1.5 py-0.5 text-xs bg-white rounded-full"
                          style="color: var(--brand-button-bg);">
                        @{{ activeFilterCount }}
                    </span>
                </button>
            </template>
            @endif

            {{-- Desktop Add Event Button --}}
            @if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule' && !(auth()->check() && auth()->user()->isViewer($role->subdomain)))
                @if ($headerTemplates->isNotEmpty())
                <button type="button" class="js-template-picker-open hidden md:inline-flex items-center justify-center gap-1.5 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-base font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                    {{ __('messages.use_a_template') }}
                </button>
                @endif
                <x-brand-link href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}" class="hidden md:inline-flex w-auto">
                    <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    {{ __('messages.add_event') }}
                </x-brand-link>
            @endif
        </div>
    </div>

    @if ($headerTemplates->isNotEmpty())
    {{-- Template picker modal (v-pre: this partial renders inside the calendar Vue app, so guard the user-controlled names from the template compiler) --}}
    <div id="template-picker-modal" v-pre class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="template-picker-title">
        <div class="js-template-picker-close fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-start shadow-xl dark:shadow-gray-900/50 transition-all sm:my-8 sm:w-full sm:max-w-md sm:p-6">
                    <div class="absolute end-0 top-0 pe-4 pt-4">
                        <button type="button" class="js-template-picker-close rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                            <span class="sr-only">{{ __('messages.cancel') }}</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-4" id="template-picker-title">{{ __('messages.use_a_template') }}</h3>
                    <div class="max-h-80 overflow-y-auto -mx-2">
                        @foreach ($headerTemplates as $template)
                            <a href="{{ route('event_template.apply', ['subdomain' => $role->subdomain, 'hash' => $template->encodeId()]) }}"
                               class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                                <span class="break-words">{{ $template->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script {!! nonce_attr() !!}>
        function openTemplatePickerModal() {
            var modal = document.getElementById('template-picker-modal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeTemplatePickerModal() {
            var modal = document.getElementById('template-picker-modal');
            if (modal) modal.classList.add('hidden');
        }
        // Capture-phase delegation (replaces inline onclick; the buttons render inside the calendar Vue app).
        document.addEventListener('click', function (e) {
            if (! e.target.closest) return;
            if (e.target.closest('.js-template-picker-open')) { openTemplatePickerModal(); }
            else if (e.target.closest('.js-template-picker-close')) { closeTemplatePickerModal(); }
        }, true);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeTemplatePickerModal();
        });
    </script>
    @endif
</header>
@endif

@if (! request()->graphic && ! (isset($embed) && $embed))
{{-- Active filters: what is narrowing the view, each with its own remove button. Without it a
     visitor who opens a shared "Room A" link (or scans one on a door) sees a partial schedule
     with nothing but a badge on the Filters button to explain it. --}}
{{-- narrowingFilterCount, not activeFilterCount: a sub-schedule page's own sub-schedule is the
     page, not a filter, so a bare /schedule/kids shows no row. Owner-customizable labels sit in
     v-pre spans: this is inside the Vue mount, and a label is owner-authored text. --}}
<div v-cloak v-if="narrowingFilterCount > 0" id="active-filter-chips" class="mb-4 rounded-xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm px-3 py-2 flex flex-wrap items-center gap-2 {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
    <span v-for="chip in activeFilterChips" :key="chip.id"
          class="inline-flex items-center gap-1 rounded-full border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 ps-3 pe-1 text-sm text-gray-800 dark:text-gray-200 max-w-full">
        <span class="truncate" dir="auto" v-text="chip.text"></span>
        <button type="button" @click="removeFilterChip(chip)"
                :aria-label="filterChipLabels.remove + ': ' + chip.text"
                class="flex-shrink-0 inline-flex items-center justify-center h-11 w-11 md:h-7 md:w-7 -my-2 md:my-0 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-200 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </span>
    <span class="text-sm text-gray-500 dark:text-gray-400 ms-1"
          :aria-live="(showFiltersDrawer || showDesktopFiltersModal) ? 'off' : 'polite'"
          v-text="filteredCountLabel"></span>
    <button type="button" @click="clearFilters(); focusFiltersOpener()"
            class="ms-auto inline-flex items-center min-h-11 md:min-h-0 text-sm font-medium text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] px-2 py-1 rounded focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
        <span v-pre>{{ $label('clear_filters') }}</span>
    </button>
</div>
@endif

    {{-- A load that failed. It used to end in "No scheduled events", which is a statement about
         the schedule and was only ever true of the connection. Above both views, and above the
         rows the cache may still have drawn. --}}
    <div v-cloak v-if="loadFailed" data-load-failed role="alert"
         class="mb-4 flex flex-wrap items-center gap-3 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
        <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <span class="flex-1 text-sm font-medium text-amber-900 dark:text-amber-100">{{ __('messages.error_loading') }}</span>
        <button type="button" @click="retryLoad" :disabled="isLoadingEvents"
                class="inline-flex items-center justify-center rounded-lg border border-amber-300 dark:border-amber-600 bg-white dark:bg-gray-900 px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 transition-all duration-200 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
            {{ __('messages.try_again') }}
        </button>
    </div>

    @if ($guestRows && ! $guestEmbed)
    {{-- The one filter most visitors want, one press away: the schedule's sub-schedules where
         it has them, its categories otherwise. Everything else stays behind Filters. The names
         are the owner's text, drawn by Vue from data (v-text), never compiled. --}}
    <div v-if="quickChips.length > 1" v-cloak class="gk-pills {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}" role="group" aria-label="{{ $label('filters') }}">
        <button type="button" class="gk-pill" :class="{ 'gk-pill-on': !quickChipValue }" :aria-pressed="!quickChipValue ? 'true' : 'false'" @click="pickQuickChip('', $event)">{{ $label('show_all') }}</button>
        <button v-for="chip in quickChips" :key="chip.value" type="button" class="gk-pill" :class="{ 'gk-pill-on': quickChipValue === chip.value }"
                :aria-pressed="quickChipValue === chip.value ? 'true' : 'false'" @click="pickQuickChip(chip.value, $event)">
            <i v-if="chip.color" class="gk-row-dot" :style="{ backgroundColor: chip.color }"></i><span v-text="chip.name"></span>
        </button>
    </div>
    @endif

    <div v-show="currentView === 'calendar'" class="{{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">

        @if (request()->graphic)
            @include('role.partials.calendar-graphic')
        @else
        <div v-if="isLoadingEvents">
            {{-- The month while it loads: its own frame, its weekday row and as many weeks as the
                 month that is coming has, so the page keeps its shape when the month arrives. --}}
            <div class="gk-cal gk-cal-wait {{ ($tab ?? '') == 'availability' ? 'gk-cal-pick' : 'hidden md:block' }} animate-pulse" aria-hidden="true">
                <div class="gk-cal-head">
                    @for ($i = 0; $i < 7; $i++)
                    <div class="gk-cal-wd"><i class="gk-cal-wait-bar gk-cal-wait-wd"></i></div>
                    @endfor
                </div>
                <div class="gk-cal-weeks">
                    <div v-for="w in Math.ceil(calendarDays.length / 7)" :key="w" class="gk-cal-week">
                        <div v-for="d in 7" :key="d" class="gk-cal-day">
                            <i class="gk-cal-wait-bar gk-cal-wait-num"></i>
                            @if (($tab ?? '') != 'availability')
                            <i class="gk-cal-wait-bar gk-cal-wait-line"></i>
                            <i class="gk-cal-wait-bar gk-cal-wait-line gk-cal-wait-short"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (($tab ?? '') != 'availability')
        {{-- The month grid has nothing for the active filters. Offers the two ways out that keep
             them: the list (every upcoming event, not just this month) and the next month. --}}
        <div v-cloak v-if="!isLoadingEvents && !loadFailed && narrowingFilterCount > 0 && monthMatchCount === 0"
             class="{{ ($guestRows && ! $guestEmbed) ? 'flex' : 'hidden md:flex' }} mb-4 flex-wrap items-center justify-between gap-3 rounded-xl bg-white/95 dark:bg-gray-900/95 border border-gray-200 dark:border-gray-700 px-4 py-3">
            <span v-pre class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label('no_events_found') }}</span>
            @if ($route === 'guest' && ! (isset($embed) && $embed))
            <div class="flex items-center gap-2">
                <button type="button" @click="toggleView('list')"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                    {{ __('messages.list') }}
                </button>
                <button type="button" @click="navigateMonth(1)"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                    {{ __('messages.next_month') }}
                </button>
                <button type="button" @click="clearFilters"
                        class="px-3 py-1.5 text-sm font-medium text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                    <span v-pre>{{ $label('clear_filters') }}</span>
                </button>
            </div>
            @elseif (! (isset($embed) && $embed))
            <button type="button" @click="clearFilters"
                    class="px-3 py-1.5 text-sm font-medium text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                <span v-pre>{{ $label('clear_filters') }}</span>
            </button>
            @endif
        </div>
        @endif
        @if (($tab ?? '') !== 'availability')
        {{-- The month, on every page that shows one (a guest's page, the embed, the admin's
             Schedule tab, the dashboard): role/partials/month, with its card and its day's
             panel (month-peek) and the script that writes each day (month-script). --}}
        @include('role.partials.month')
        @else
        {{-- The Availability tab: the same month with nothing on it but its days, where a day is
             the thing that is pressed. The server draws it (the days and what is marked are known
             when the page is made) on the month kit's own classes (partials/month-kit-styles,
             .gk-cal-pick). A day keeps the names its script (role/show-admin) and the browser
             tests know it by: .day-element, data-date, and the .day-x that marks it. It is shown
             on a phone too, so the root carries no hidden class. --}}
        @php
            $pickDayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            $pickDayKeys = array_merge(array_slice($pickDayKeys, $firstDay), array_slice($pickDayKeys, 0, $firstDay));
            $pickToday = $today->format('Y-m-d');
            $pickTodayCol = ($today->year == $year && $today->month == $month) ? ($today->dayOfWeek - $firstDay + 7) % 7 : -1;
            // A day can be pressed where the script that marks it is on the page, and only there
            // (role/show-admin gives it to everyone but a viewer: keep the two conditions one).
            $pickMarks = $route == 'admin' && $role->email_verified_at && ! ($isViewer ?? false);
            $pickMarked = is_array($datesUnavailable) ? $datesUnavailable : [];
            $pickCol = 0;
        @endphp
        <div v-show="!isLoadingEvents" class="gk-cal gk-cal-pick" data-availability-grid role="group"
             aria-label="{{ \Carbon\Carbon::create($year, $month, 1)->locale(app()->getLocale())->translatedFormat('F Y') }}">
            <div class="gk-cal-head" aria-hidden="true">
                @foreach ($pickDayKeys as $i => $pickKey)
                <div class="gk-cal-wd {{ $i === $pickTodayCol ? 'gk-cal-wd-now' : '' }}">{{ __('messages.' . $pickKey) }}</div>
                @endforeach
            </div>
            <div class="gk-cal-weeks">
                @while ($currentDate->lte($endOfMonth))
                @php
                    $pickDate = $currentDate->format('Y-m-d');
                    $pickIsMarked = in_array($pickDate, $pickMarked);
                    $pickClass = 'gk-cal-day'
                        . ($currentDate->month == $month ? '' : ' gk-cal-day-out')
                        . ($pickDate < $pickToday ? ' gk-cal-day-past' : '')
                        . ($pickDate === $pickToday ? ' gk-cal-day-today' : '')
                        . ($pickMarks ? ' day-element' : '');
                @endphp
                @if ($pickCol % 7 === 0)
                <div class="gk-cal-week">
                @endif
                    <div class="{{ $pickClass }}" data-date="{{ $pickDate }}"
                         @if ($pickMarks) role="button" tabindex="0" aria-pressed="{{ $pickIsMarked ? 'true' : 'false' }}" @endif
                         aria-label="{{ $currentDate->copy()->locale(app()->getLocale())->translatedFormat('l, F j') }}"
                         @if ($pickDate === $pickToday) aria-current="date" @endif>
                        <div class="gk-cal-dayhead">
                            <span class="gk-cal-num"><time datetime="{{ $pickDate }}">{{ $currentDate->day }}</time></span>
                            @if ($pickDate === $pickToday)
                            <span class="gk-cal-word">{{ __('messages.today') }}</span>
                            @endif
                        </div>
                        @if ($pickMarks && $pickIsMarked)
                        <div class="day-x" data-label="{{ __('messages.unavailable') }}"></div>
                        @endif
                    </div>
                @if ($pickCol % 7 === 6)
                </div>
                @endif
                @php $currentDate->addDay(); $pickCol++; @endphp
                @endwhile
            </div>
        </div>
        @endif
        @endif


        @if (($tab ?? '') != 'availability')
        {{-- Mobile calendar skeleton --}}
        <div v-show="currentView === 'calendar' && isLoadingEvents" class="md:hidden">
            <div class="space-y-3 px-1 py-4 animate-pulse">
                @for ($i = 0; $i < 5; $i++)
                <div class="flex items-center bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-1/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                    <div class="w-24 h-16 bg-gray-200 dark:bg-gray-700 rounded ml-3"></div>
                </div>
                @endfor
            </div>
        </div>
        @if ($guestRows)
        {{-- The month on a phone. A laptop's grid does not fit one, and what stood here was the
             month's events as a plain list of cards, with nothing to jump to a date by. A small
             month with a count under each day; picking a day folds it to one line and brings
             that day's rows up. The rows are the list's own (role/partials/guest-row).

             It reads the month the way the laptop's grid does (getEventsForDate(), from the
             server's map of the month), so it has the days that are over, the months before
             this one, and a series as far ahead as the grid shows it; and the filters count
             the same month it draws (filterScopeIsMonth).

             An embed in a narrow frame is not a phone: it keeps what it always had there, every
             upcoming event day by day with no month to page through, now as rows.

             v-if, so a laptop and the list view do not carry these rows as well. --}}
        <div v-if="isNarrow && currentView === 'calendar' && !isLoadingEvents" class="gk-list" data-phone-month>
            @if (! $guestEmbed)
            <div class="gk-panel gk-month bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">
                <button v-if="phoneDay" type="button" class="gk-month-fold" ref="phoneFold" @click="unfoldPhoneMonth" aria-expanded="false">
                    <span v-text="formatDateHeader(phoneDay)"></span><span>{{ __('messages.calendar') }}</span>
                </button>
                <template v-else>
                    {{-- Which month this is, and the way to the one before and after: the page's
                         own month buttons say "This month" and are put away on a phone. --}}
                    <div class="gk-month-title">
                        <button type="button" class="gk-month-nav" @click="navigateMonth(-1)" aria-label="{{ __('messages.previous_month') }}">
                            <svg class="{{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" /></svg>
                        </button>
                        <span v-text="phoneMonthTitle" aria-live="polite"></span>
                        <button type="button" class="gk-month-nav" @click="navigateMonth(1)" aria-label="{{ __('messages.next_month') }}">
                            <svg class="{{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" /></svg>
                        </button>
                    </div>
                    <div class="gk-month-head" aria-hidden="true"><span v-for="(name, index) in phoneWeekdays" :key="'wd-' + index" v-text="name"></span></div>
                    <div class="gk-month-grid">
                        <template v-for="cell in phoneMonthCells" :key="cell.key">
                            <span v-if="!cell.date"></span>
                            <button v-else type="button" class="gk-month-day" :disabled="cell.count === 0" :data-day="cell.date"
                                    :class="{ 'gk-month-has': cell.count > 0, 'gk-month-today': cell.today, 'gk-month-past': cell.past }"
                                    :aria-current="cell.today ? 'date' : null"
                                    :aria-label="formatDateHeader(cell.date) + (cell.count ? ' (' + cell.count + ')' : '')"
                                    @click="pickPhoneDay(cell.date)">
                                <b v-text="cell.day"></b><i v-if="cell.count" v-text="cell.count"></i>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
            <button v-if="phoneHasEarlierDays && !phoneShowPast" type="button" class="gk-month-earlier" @click="phoneShowPast = true">{{ $label('show_past_events') }}</button>
            @endif
            <div v-if="phoneGroups.length" class="gk-days" :data-list-anim="activeListAnimation !== 'none' ? activeListAnimation : null" :data-list-rtl="isRtl ? '' : null" style="--es-accent: {{ $accentColor }}">
                <section v-for="group in phoneGroups" :key="'pm-' + group.date" :id="'gk-day-' + group.date" class="gk-panel gk-day bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm" :class="{ 'gk-day-past': group.past }">
                    <div class="gk-dayhead">
                        <span v-if="dayWord(group.date)" class="gk-dayhead-word" v-text="dayWord(group.date)"></span>
                        <h2 class="gk-dayhead-title" v-text="formatDateHeader(group.date)" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></h2>
                    </div>
                    <ul class="gk-rows">
                        <template v-for="event in group.events" :key="'pm-' + event.uniqueKey">
                            @include('role/partials/guest-row')
                        </template>
                    </ul>
                </section>
            </div>
            {{-- Nothing on in THIS month is not an empty schedule: say which, and offer the next.
                 (With a filter on, the notice above the month says so and offers the ways out.) --}}
            <div v-else-if="!isLoadingEvents && !loadFailed && (!phoneMonth || narrowingFilterCount === 0)" class="gk-panel gk-pad gk-month-none bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">
                @if ($guestEmbed)
                <span v-pre>{{ $label('no_scheduled_events') }}</span>
                @else
                <span>{{ __('messages.no_events') }}</span>
                <button type="button" class="gk-pill" @click="navigateMonth(1)">{{ __('messages.next_month') }}</button>
                @endif
            </div>
            @if ($guestEmbed)
            {{-- The embed's agenda is a cut of every upcoming day, so it has a way on. --}}
            @include('role/partials/list-more')
            @endif
        </div>
        @else
        <div v-show="currentView === 'calendar' && !isLoadingEvents" class="md:hidden">
            <div v-if="mobileEventsList.length">
                <button id="showPastEventsBtn" class="text-[var(--brand-blue)] font-medium hidden mb-4 w-full text-center">
                    {{ $label('show_past_events') }}
                </button>
                <div id="mobileEventsList" :data-list-anim="activeListAnimation !== 'none' ? activeListAnimation : null" :data-list-rtl="isRtl ? '' : null" style="--es-accent: {{ $accentColor }}" class="space-y-6">
                    <template v-for="(group, groupIndex) in eventsGroupedByDate" :key="'date-' + group.date">
                        {{-- Date Header --}}
                        <div class="sticky top-0 z-10 {{ $stickyBleedClass }} bg-white/95 backdrop-blur-sm dark:bg-gray-900/95"
                            :class="isPastEvent(group.date) ? 'past-event hidden' : ''">
                            <div class="pb-5 pt-3 px-4 flex items-center gap-4">
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100 text-center" v-text="formatDateHeader(group.date)" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></div>
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                            </div>
                        </div>
                        {{-- Events for this date --}}
                        <div class="space-y-6" :class="isPastEvent(group.date) ? 'past-event hidden' : ''">
                            <template v-for="event in group.events" :key="'mobile-' + event.uniqueKey">
                                <div v-if="isEventVisible(event)"
                                     v-list-reveal:a="event.uniqueKey"
                                     @click="navigateToEvent(event, $event)"
                                     class="block cursor-pointer">
                                    <div class="event-item bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-lg hover:bg-gray-50/95 dark:hover:bg-gray-800/95"
                                        :class="isEventPast(event) ? 'past-event hidden' : ''">
                                        <div class="flex" :class="isRtl ? 'flex-row-reverse' : ''">
                                            @include('role/partials/mobile-event-card')
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                @include('role/partials/list-more')
            </div>
            <div v-else-if="!isLoadingEvents && !loadFailed && narrowingFilterCount > 0" class="pb-4 text-center">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div v-pre class="text-xl text-gray-500 dark:text-gray-400">
                        {{ $label('no_events_found') }}
                    </div>
                    @if (! (isset($embed) && $embed))
                    <button type="button" @click="clearFilters"
                            class="mt-4 inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <span v-pre>{{ $label('clear_filters') }}</span>
                    </button>
                    @endif
                </div>
            </div>
            <div v-else-if="!isLoadingEvents && !loadFailed && {{ $tab != 'availability' ? 'true' : 'false' }}" class="pb-4 text-center">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div class="text-xl text-gray-500 dark:text-gray-400">
                        {{ $label('no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif
    </div>

{{-- The list from a tablet up: a card for each event, under a date set between two lines.

     A guest page used to carry BOTH lists whatever the width, this one and the phone's further
     down, one of them hidden by CSS: every event was in the page twice, and a busy schedule's
     page was 62,000 elements. There it is now v-if on the width ($guestRows): the cards from a
     tablet up, the rows on a phone, never both. (For a short while in October 2026 the rows
     were the list at every width. They read as a table on a wide screen and lost what the cards
     have: the big title, the date tile, the performers and the fan buttons. The cards came
     back; what the rows had learnt to say, the price and whether any are left, came with them
     as role/partials/card-ticket-badge.) The admin and the dashboard keep both lists and the
     CSS switch, as before. --}}
{{-- List View Skeleton (Desktop) --}}
        <div v-if="currentView === 'list' && isLoadingEvents" class="hidden md:block space-y-4 animate-pulse">
            {{-- Date Header Skeleton (matches the real header's centered translucent pill) --}}
            <div class="flex items-center gap-4">
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                <div class="rounded-xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm px-5 py-2.5">
                    <div class="h-6 w-48 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
            </div>
            @for ($i = 0; $i < 4; $i++)
            <div class="rounded-2xl shadow-sm overflow-hidden bg-white/95 dark:bg-gray-900/95">
                <div class="flex flex-col md:flex-row">
                    {{-- Details Column --}}
                    <div class="md:flex-1 md:min-w-0 px-5 py-6 md:px-8 lg:px-16 md:py-8 flex flex-col gap-5">
                        {{-- Title --}}
                        <div class="h-8 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        {{-- Short description --}}
                        <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        {{-- Date Badge --}}
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"></div>
                            <div class="flex flex-col gap-2">
                                <div class="h-5 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        {{-- Venue Badge --}}
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"></div>
                            <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                        {{-- Talent Avatars --}}
                        <div class="flex items-center gap-2">
                            <div class="flex items-center -space-x-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-700"></div>
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-700"></div>
                            </div>
                            <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                    </div>
                    {{-- Image Column --}}
                    <div class="flex-shrink-0 w-80 lg:w-96 h-64 md:h-auto bg-gray-200 dark:bg-gray-700"></div>
                </div>
            </div>
            @endfor
        </div>

{{-- List View (Desktop) --}}
        <div {!! $guestRows ? 'v-if="currentView === \'list\' && !isLoadingEvents && !isNarrow"' : 'v-show="currentView === \'list\' && !isLoadingEvents"' !!} :data-list-anim="activeListAnimation !== 'none' ? activeListAnimation : null" :data-list-rtl="isRtl ? '' : null" style="--es-accent: {{ $accentColor }}" class="hidden md:block {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
            {{-- Upcoming Events --}}
            <div v-if="allListGroups.length" class="space-y-8">
                <template v-for="(group, groupIndex) in allListGroups" :key="'list-d-' + group.date">
                    {{-- The end of the upcoming rows, which is above the past ones. --}}
                    <template v-if="groupIndex === firstPastGroupIndex">
                        @include('role/partials/list-more')
                    </template>
                    {{-- Past Events Divider (once, before the first all-past group). On a guest
                         page it is the kit's marker (.gk-past), which reads on whatever the owner
                         put behind the list; the admin's list is on the portal's own surface and
                         keeps its hairlines. --}}
                    @if ($guestRows)
                    <div v-if="group.events.every(e => e._isPast) && (groupIndex === 0 || !allListGroups[groupIndex - 1].events.every(e => e._isPast))"
                         class="gk-past" role="heading" aria-level="2">
                        <span class="gk-past-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1"/><path d="M3.5 4.5v4h4"/><path d="M12 7.5V12l3 2"/></svg><span>{{ $label('past_events') }}</span></span>
                    </div>
                    @else
                    <div v-if="group.events.every(e => e._isPast) && (groupIndex === 0 || !allListGroups[groupIndex - 1].events.every(e => e._isPast))"
                         class="py-4 flex items-center gap-4">
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-1 bg-white dark:bg-gray-900">
                            {{ $label('past_events') }}
                        </span>
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                    </div>
                    @endif
                    {{-- One date: header + its cards --}}
                    <div class="space-y-4">
                        {{-- Date Header: translucent card-style backing keeps the date + hairlines
                             readable on ANY guest background (solid/gradient/image) in light + dark.
                             Guard the dateless group so it never shows "Invalid Date". --}}
                        <div v-if="group.date && group.date !== 'no-date'"
                             class="flex items-center gap-4"
                             role="heading" aria-level="2">
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                            <div class="rounded-xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm px-5 py-2.5 text-center" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}>
                                @if ($guestRows)
                                <span v-if="dayWord(group.date)" class="me-2 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400" v-text="dayWord(group.date)"></span>
                                @endif
                                <span class="font-semibold text-xl text-gray-900 dark:text-gray-100" v-text="formatDateHeader(group.date)"></span>
                                <span class="ms-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                                    &middot; <span v-text="group.events.length"></span>&nbsp;<span v-if="group.events.length === 1">{{ __('messages.event') }}</span><span v-else>{{ __('messages.events') }}</span>
                                </span>
                            </div>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                        </div>
                        {{-- Cards for this date --}}
                        <template v-for="event in group.events" :key="'list-d-' + event.uniqueKey">
                    <div v-list-reveal:d="event.uniqueKey" @click="navigateToEvent(event, $event)" class="block cursor-pointer">
                        <div class="rounded-2xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">
                            {{-- Side-by-side layout when flyer image exists --}}
                            <template v-if="event.flyer_url">
                                <div class="flex flex-col md:flex-row" :class="isRtl ? 'md:flex-row-reverse' : ''">
                                    {{-- Details Column --}}
                                    <div data-reveal-body class="md:flex-1 md:min-w-0 px-5 py-6 md:px-8 lg:px-16 md:py-8 flex flex-col gap-5">
                                        {{-- Event Title --}}
                                        <div data-reveal-title class="flex items-start gap-2">
                                            <span v-if="getEventDotColor(event)" class="inline-block w-3 h-3 rounded-full flex-shrink-0 mt-2" :style="{ backgroundColor: getEventDotColor(event) }"></span>
                                            <h2 class="font-bold text-2xl md:text-3xl leading-snug line-clamp-2 text-gray-900 dark:text-gray-100" :dir="event.dir || 'auto'">
                                                <a :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" v-html="commaBreak(event.name)"></a>
                                                <svg v-if="event.is_password_protected" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-6 h-6 text-gray-400 ms-2 align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                                <span v-if="event.is_internal" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 ms-2 align-middle">{{ __('messages.internal') }}</span><span v-else-if="event.is_draft" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 ms-2 align-middle">{{ __('messages.draft') }}</span>
                                            </h2>
                                        </div>
                                        <p v-if="event.short_description && !event.is_password_protected" class="text-gray-600 dark:text-gray-400 mt-2" :dir="event.description_dir || event.dir || 'auto'" v-text="event.short_description"></p>

                                        {{-- Date Badge --}}
                                        <div v-if="event.occurrenceDate" class="flex items-center gap-4">
                                            <div data-reveal-date class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col items-center justify-center shadow-sm">
                                                <span class="text-[11px] font-bold uppercase tracking-wider leading-none pt-1 es-date-month" v-text="getMonthAbbr(event._originalOccurrenceDate || event.occurrenceDate)"></span>
                                                <span class="text-2xl font-bold text-gray-900 dark:text-white leading-none" v-text="getDayNum(event._originalOccurrenceDate || event.occurrenceDate)"></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span v-if="event.is_multi_day && event.local_end_date" class="text-lg font-semibold text-gray-900 dark:text-white" v-text="getEventTime(event)"></span>
                                                <span v-else class="text-lg font-semibold text-gray-900 dark:text-white" v-text="formatDayName(event.occurrenceDate)"></span>
                                                <span v-if="!(event.is_multi_day && event.local_end_date)" class="text-sm text-gray-500 dark:text-gray-400">
                                                    <span v-text="getEventTime(event)"></span>
                                                    <span v-if="event.duration" class="ms-1" v-text="'(' + formatDuration(event.duration) + ')'"></span>
                                                </span>
                                                <span v-else-if="event.duration" class="text-sm text-gray-500 dark:text-gray-400" v-text="formatDuration(event.duration)"></span>
                                            </div>
                                        </div>

                                        {{-- Venue Badge --}}
                                        <a v-if="event.venue_name && event.venue_guest_url && !event.is_password_protected" :href="event.venue_guest_url" class="w-fit flex items-center gap-4 min-w-0 hover:opacity-80 transition-opacity">
                                            <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                                <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                                <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                </svg>
                                            </div>
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 hover:underline" v-html="commaBreak(event.venue_name)" :dir="event.venue_dir || 'auto'"></span>
                                            <svg class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                            </svg>
                                        </a>
                                        <div v-else-if="event.venue_name && !event.is_password_protected" class="flex items-center gap-4 min-w-0">
                                            <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                                <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                                <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                </svg>
                                            </div>
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2" v-html="commaBreak(event.venue_name)" :dir="event.venue_dir || 'auto'"></span>
                                        </div>

                                        {{-- RSVP Free Badge --}}
                                        <div v-if="event.rsvp_enabled && !event.is_password_protected" class="flex items-center gap-4">
                                            <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                                <svg width="24" height="24" viewBox="0 0 20 20" fill="{{ $accentColor }}" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l7.5 7.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-7.5-7.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $label('free_entry') }}</span>
                                            </div>
                                        </div>

                                        @includeWhen($guestRows, 'role/partials/card-ticket-badge')

                                        {{-- Ticket Price Badge: the price an owner typed for an event sold somewhere
                                             else. An event that used to be sold elsewhere and is sold here now keeps
                                             that price saved, so on a guest page it stands aside for our own line
                                             above, as it does on a phone's row: one card, one price. --}}
                                        <div v-if="!event.rsvp_enabled && event.registration_url && event.ticket_price != null && !event.is_password_protected{!! $guestRows ? ' && !cardHasTickets(event)' : '' !!}" class="flex items-center gap-4">
                                            <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                                <svg width="24" height="24" viewBox="0 0 20 20" fill="{{ $accentColor }}" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l7.5 7.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-7.5-7.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                                    <span v-if="event.ticket_price == 0">{{ $label('free_entry') }}</span>
                                                    <span v-else v-text="formatPrice(event.ticket_price, event.ticket_currency_code)"></span>
                                                </span>
                                                <span v-if="event.coupon_code || event.coupon_discount_label" class="text-sm text-gray-500 dark:text-gray-400"><span v-if="event.coupon_code">{{ __('messages.coupon_code') }}: <bdi v-text="event.coupon_code"></bdi></span><span v-if="event.coupon_code && event.coupon_discount_label"> &bull; </span><bdi v-if="event.coupon_discount_label" v-text="event.coupon_discount_label"></bdi></span>
                                            </div>
                                        </div>

                                        {{-- Talent Avatars + Names --}}
                                        <div v-if="event.talent && event.talent.length > 0 && !event.is_password_protected" class="flex items-center gap-2 my-2">
                                            <div data-reveal-pop class="flex items-center -space-x-2" :class="isRtl ? 'space-x-reverse' : ''">
                                                <template v-for="(t, tIndex) in event.talent.slice(0, 5)" :key="'ta-' + tIndex">
                                                    <img v-if="t.profile_image" :src="t.profile_image" class="w-8 h-8 rounded-full object-cover border-2 border-white dark:border-gray-700" :alt="t.name" :title="t.name">
                                                    <div v-else class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-700 bg-gray-200 dark:bg-gray-600 flex items-center justify-center" :title="t.name">
                                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400" v-text="t.name.charAt(0).toUpperCase()"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <template v-for="(t, tIndex) in event.talent" :key="'tn-' + tIndex">
                                                <span v-if="tIndex > 0" class="text-base text-gray-600 dark:text-gray-300">, </span>
                                                <a v-if="t.guest_url" :href="t.guest_url" class="text-base text-gray-600 dark:text-gray-300 hover:opacity-80 hover:underline transition-opacity truncate" v-html="commaBreak(t.name)" :dir="t.dir || 'auto'"></a>
                                                <span v-else class="text-base text-gray-600 dark:text-gray-300 truncate" v-html="commaBreak(t.name)" :dir="t.dir || 'auto'"></span>
                                            </template>
                                            <svg v-if="event.talent.some(t => t.guest_url)" class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                            </svg>
                                        </div>

                                        {{-- Video Thumbnails --}}
                                        <div v-if="event.videos && event.videos.length > 0 && !event.is_password_protected && event.fan_videos_enabled" class="mt-3 space-y-2">
                                            {{-- Playing video iframe (full width, above thumbnails) --}}
                                            <div v-if="event.videos.some((v, i) => playingVideo === event.uniqueKey + '-' + i)"
                                                 class="w-full aspect-video rounded-lg overflow-hidden shadow-sm" @click.stop>
                                                <template v-for="(vid, vidIdx) in event.videos" :key="'playing-' + vidIdx">
                                                    <iframe v-if="playingVideo === event.uniqueKey + '-' + vidIdx && vid.embed_url"
                                                            :src="vid.embed_url + '?autoplay=1'"
                                                            class="w-full h-full" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                </template>
                                            </div>
                                            {{-- Thumbnail row --}}
                                            <div class="flex gap-2 overflow-x-auto">
                                                <div v-for="(vid, vidIdx) in event.videos" :key="'vid-' + vidIdx"
                                                     @click.stop="playVideo(event.uniqueKey + '-' + vidIdx)"
                                                     class="relative flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden shadow-sm group/vid cursor-pointer"
                                                     :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'ring-2 ring-blue-500' : ''">
                                                    <img :src="vid.thumbnail_url" class="w-full h-full object-cover" alt="">
                                                    <div class="absolute inset-0 flex items-center justify-center"
                                                         :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'bg-black/50' : 'bg-black/30'">
                                                        <svg v-if="playingVideo !== event.uniqueKey + '-' + vidIdx"
                                                             class="w-8 h-8 text-white opacity-80 group-hover/vid:opacity-100"
                                                             viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M8 5v14l11-7z"/>
                                                        </svg>
                                                        <svg v-else class="w-6 h-6 text-white"
                                                             viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Photo Thumbnails --}}
                                        <div v-if="event.photos && event.photos.length > 0 && !event.is_password_protected && event.fan_photos_enabled" class="mt-3">
                                            <div class="flex gap-2 overflow-x-auto">
                                                <div v-for="(photo, photoIdx) in event.photos" :key="'photo-' + photoIdx"
                                                     class="relative flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden shadow-sm">
                                                    <img :src="photo.url" class="w-full h-full object-cover" alt="" loading="lazy">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Recent Comments --}}
                                        <div v-if="event.recent_comments && event.recent_comments.length > 0 && !event.is_password_protected && event.fan_comments_enabled" class="space-y-1.5" :dir="isRtl ? 'rtl' : 'ltr'">
                                            <div v-for="(comment, cIdx) in event.recent_comments" :key="'c-' + cIdx"
                                                 class="flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="h-4 w-4 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M3.43 2.524A41.29 41.29 0 0110 2c2.236 0 4.43.18 6.57.524 1.437.231 2.43 1.49 2.43 2.902v5.148c0 1.413-.993 2.67-2.43 2.902a41.202 41.202 0 01-5.183.501.78.78 0 00-.528.224l-3.579 3.58A.75.75 0 016 17.25v-3.443a41.033 41.033 0 01-2.57-.33C2.993 13.244 2 11.986 2 10.574V5.426c0-1.413.993-2.67 2.43-2.902z" clip-rule="evenodd" />
                                                </svg>
                                                <span>"<span v-text="comment.text"></span>" - <span class="font-medium" v-text="comment.author"></span></span>
                                            </div>
                                        </div>

                                        {{-- Polls --}}
                                        <div v-if="event.polls && event.polls.length > 0 && !event.is_password_protected" class="space-y-3" @click.stop>
                                            <div v-for="poll in event.polls" :key="poll.id"
                                                 class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                                <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 mb-3"
                                                    v-text="poll.question"></h4>
                                                <div v-if="poll.user_vote === null && poll.is_active">
                                                    <button v-for="(option, idx) in poll.options" :key="idx"
                                                            @click.stop="votePoll(event, poll, idx, $event)"
                                                            :disabled="votingPoll[poll.id] != null"
                                                            class="w-full text-start px-3 py-2.5 mb-1.5 text-sm rounded-lg border transition-all duration-200"
                                                            :class="votingPoll[poll.id] != null && votingPoll[poll.id] !== idx ? 'opacity-40 border-gray-300 dark:border-gray-600' : 'border-gray-300 dark:border-gray-600 hover:border-gray-500'"
                                                            :style="votingPoll[poll.id] === idx ? { borderColor: accentColor, backgroundColor: accentColor + '15' } : {}"
                                                            >
                                                        <span v-text="option" class="dark:text-gray-200" :class="votingPoll[poll.id] === idx ? 'font-medium' : ''"></span>
                                                    </button>
                                                    @guest
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        <a href="{{ app_url('/login') }}" class="underline">{{ __('messages.sign_in_to_vote') }}</a>
                                                    </p>
                                                    @endguest
                                                </div>
                                                <div v-else>
                                                    <div v-for="(option, idx) in poll.options" :key="idx" class="mb-2.5">
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="flex items-center gap-1 text-gray-800 dark:text-gray-200" :class="{ 'font-semibold': getVoteCount(poll, idx) === getMaxVoteCount(poll) && poll.total_votes > 0 }">
                                                                <span v-text="option"></span>
                                                                <svg v-if="idx === poll.user_vote" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 shrink-0" :style="{ color: accentColor }">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                                </svg>
                                                            </span>
                                                            <span class="text-gray-500 dark:text-gray-400 text-xs tabular-nums">
                                                                <span v-text="getVoteCount(poll, idx)"></span> (<span v-text="getVotePercent(poll, idx)"></span>%)
                                                            </span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                            <div class="h-2.5 rounded-full"
                                                                 :style="{
                                                                     width: (pollAnimating[poll.id] ? 0 : Math.max(getVotePercent(poll, idx), poll.total_votes > 0 ? 2 : 0)) + '%',
                                                                     backgroundColor: idx === poll.user_vote ? accentColor : (getVoteCount(poll, idx) === getMaxVoteCount(poll) && poll.total_votes > 0 ? accentColor + '80' : '#9ca3af'),
                                                                     boxShadow: idx === poll.user_vote ? '0 0 8px ' + accentColor + '40' : 'none',
                                                                     transition: 'width 700ms ease-out',
                                                                     transitionDelay: (idx * 120) + 'ms'
                                                                 }"></div>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                                                        <span v-text="poll.total_votes"></span> {{ __('messages.votes') }}
                                                        <span v-if="!poll.is_active" class="ms-1">&middot; {{ __('messages.poll_closed_status') }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mini Timeline for Parts --}}
                                        <div v-if="event.parts && event.parts.length > 0 && !event.is_password_protected" :dir="isRtl ? 'rtl' : 'ltr'">
                                            <div class="relative ps-2">
                                                <div class="absolute top-1 bottom-1 w-0.5 start-0" :style="'background-color: {{ $accentColor }}30'"></div>
                                                <div class="space-y-2">
                                                    <div v-for="(part, partIndex) in event.parts.slice(0, 4)" :key="'p-' + partIndex" class="relative flex items-start gap-2">
                                                        <div class="absolute top-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0 -start-[3px]" :style="'background-color: {{ $accentColor }}'"></div>
                                                        <div class="ps-3">
                                                            <span class="text-sm text-gray-700 dark:text-gray-300" v-html="commaBreak(part.name)"></span>
                                                            <span v-if="part.start_time" class="text-xs ms-1 text-gray-500 dark:text-gray-400" v-text="part.start_time"></span>
                                                        </div>
                                                    </div>
                                                    <div v-if="event.parts.length > 4" class="text-xs text-gray-500 dark:text-gray-400 ps-3">
                                                        +<span v-text="event.parts.length - 4"></span> {{ __('messages.more') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Action buttons row --}}
                                        <div class="flex flex-wrap items-center gap-2">
                                                <button v-if="event.fan_photos_enabled" @click.stop="togglePhotoForm(event, $event)"
                                                        class="hover-accent inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 hover:shadow-md border"
                                                        style="border-color: {{ $accentColor }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
                                                    {{ $label('add_photo') }}
                                                </button>
                                                <button v-if="event.fan_videos_enabled" @click.stop="toggleVideoForm(event, $event)"
                                                        class="hover-accent inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 hover:shadow-md border"
                                                        style="border-color: {{ $accentColor }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                                    {{ $label('add_video') }}
                                                </button>
                                                <button v-if="event.fan_comments_enabled" @click.stop="toggleCommentForm(event, $event)"
                                                        class="hover-accent inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 hover:shadow-md border"
                                                        style="border-color: {{ $accentColor }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                                                    {{ $label('add_comment') }}
                                                </button>
                                        </div>

                                        {{-- Video form --}}
                                        <form v-if="openVideoForm[event.uniqueKey]" @click.stop
                                              method="POST" :action="event.submit_video_url">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                            <div class="flex flex-col gap-2">
                                                <select v-if="event.parts.length > 0" name="event_part_id"
                                                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                    <option value="">{{ __('messages.general') }}</option>
                                                    <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                                </select>
                                                <input type="text" name="youtube_url" placeholder="{{ __('messages.paste_youtube_url') }}"
                                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                                                <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                        style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                            </div>
                                        </form>

                                        {{-- Photo form --}}
                                        <form v-if="openPhotoForm[event.uniqueKey]" @click.stop
                                              method="POST" :action="event.submit_photo_url" enctype="multipart/form-data"
                                              :data-key="event.uniqueKey">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                            <div class="flex flex-col gap-2">
                                                <select v-if="event.parts.length > 0" name="event_part_id"
                                                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                    <option value="">{{ __('messages.general') }}</option>
                                                    <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                                </select>
                                                <div @click="$event.currentTarget.querySelector('input[type=file]').click()"
                                                     @dragover.prevent="$event.currentTarget.style.borderColor = '{{ $accentColor }}'"
                                                     @dragenter.prevent="$event.currentTarget.style.borderColor = '{{ $accentColor }}'"
                                                     @dragleave.prevent="$event.currentTarget.style.borderColor = ''"
                                                     @drop.prevent="const zone = $event.currentTarget; zone.style.borderColor = ''; const f = $event.dataTransfer.files[0]; if (f && f.type.startsWith('image/')) { const inp = zone.querySelector('input[type=file]'); inp.files = $event.dataTransfer.files; const r = new FileReader(); r.onload = ev => { zone.querySelector('.photo-dropzone-preview img').src = ev.target.result; zone.querySelector('.photo-dropzone-placeholder').classList.add('hidden'); zone.querySelector('.photo-dropzone-preview').classList.remove('hidden'); zone.closest('form').querySelector('.photo-submit-btn').classList.remove('hidden'); const camBtn = zone.closest('form').querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.add('hidden'); }; r.readAsDataURL(f); }"
                                                     class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 cursor-pointer transition-colors">
                                                    <div class="photo-dropzone-placeholder flex flex-col items-center justify-center py-6 text-gray-500 dark:text-gray-400">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mb-1" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
                                                        <span class="text-sm hidden sm:inline">{{ __('messages.drag_photo_or_click') }}</span>
                                                        <span class="text-sm sm:hidden">{{ __('messages.choose_from_library') }}</span>
                                                    </div>
                                                    <div class="photo-dropzone-preview hidden relative p-2">
                                                        <img src="" class="rounded-lg max-h-48 mx-auto object-cover">
                                                        <button type="button" @click.stop="const zone = $event.target.closest('.rounded-lg.border-dashed'); zone.querySelector('.photo-dropzone-placeholder').classList.remove('hidden'); zone.querySelector('.photo-dropzone-preview').classList.add('hidden'); zone.querySelector('input[type=file]').value = ''; zone.closest('form').querySelector('.photo-submit-btn').classList.add('hidden'); const camBtn = zone.closest('form').querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.remove('hidden'); const camInp = zone.closest('form').querySelector('.camera-input'); if (camInp) camInp.value = '';" class="absolute top-3 {{ $role?->isRtl() ? 'left-3' : 'right-3' }} bg-black/60 hover:bg-black/80 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm leading-none">&times;</button>
                                                    </div>
                                                    <input type="file" name="photo" accept="image/*" class="hidden"
                                                           @change="if ($event.target.files[0]) { const zone = $event.target.closest('.rounded-lg.border-dashed'); const r = new FileReader(); r.onload = e => { zone.querySelector('.photo-dropzone-preview img').src = e.target.result; zone.querySelector('.photo-dropzone-placeholder').classList.add('hidden'); zone.querySelector('.photo-dropzone-preview').classList.remove('hidden'); zone.closest('form').querySelector('.photo-submit-btn').classList.remove('hidden'); const camBtn = zone.closest('form').querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.add('hidden'); }; r.readAsDataURL($event.target.files[0]); }">
                                                </div>
                                                <button type="button" @click="$event.currentTarget.closest('form').querySelector('.camera-input').click()"
                                                        class="photo-camera-btn sm:hidden w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-lg border-2 transition-colors"
                                                        style="border-color: {{ $accentColor }}; color: {{ $accentColor }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
                                                    {{ __('messages.take_photo') }}
                                                </button>
                                                <input type="file" accept="image/*" capture="environment" class="camera-input hidden"
                                                       @change="if ($event.target.files[0]) { const form = $event.target.closest('form'); const zone = form.querySelector('.rounded-lg.border-dashed'); const inp = form.querySelector('input[name=photo]'); inp.files = $event.target.files; const r = new FileReader(); r.onload = e => { zone.querySelector('.photo-dropzone-preview img').src = e.target.result; zone.querySelector('.photo-dropzone-placeholder').classList.add('hidden'); zone.querySelector('.photo-dropzone-preview').classList.remove('hidden'); form.querySelector('.photo-submit-btn').classList.remove('hidden'); const camBtn = form.querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.add('hidden'); }; r.readAsDataURL($event.target.files[0]); }">
                                                <button type="submit" class="photo-submit-btn hidden self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                        style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.upload_photo') }}</button>
                                            </div>
                                        </form>

                                        {{-- Comment form --}}
                                        <form v-if="openCommentForm[event.uniqueKey]" @click.stop
                                              method="POST" :action="event.submit_comment_url">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                            <div class="flex flex-col gap-2">
                                                <select v-if="event.parts.length > 0" name="event_part_id"
                                                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                    <option value="">{{ __('messages.general') }}</option>
                                                    <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                                </select>
                                                <textarea name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000"
                                                          class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                                                <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                        style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                            </div>
                                        </form>

                                        <a v-if="event.can_edit" :href="event.edit_url"
                                           class="text-sm {{ $route == 'admin' ? 'font-medium text-[var(--brand-blue)] hover:underline' : 'text-gray-500 dark:text-gray-400 hover:underline hover:text-gray-700 dark:hover:text-gray-200' }}"
                                           @click.stop>
                                            {{ __('messages.edit_event') }}
                                        </a>
                                    </div>
                                    {{-- Flyer Image Column: about 320px wide, so the 480 or 960 derivative, with
                                         the original's size to hold the column's shape while it loads. A duplicate of
                                         the title's link, so out of the tab order and the accessibility tree. --}}
                                    <div v-if="!event.is_password_protected" data-reveal-media class="md:w-[35%] md:flex-shrink-0">
                                        <a :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" tabindex="-1" aria-hidden="true" class="block">
                                            <img :src="event.flyer_thumb_url || event.flyer_url" :srcset="event.flyer_srcset || null" sizes="320px" loading="lazy" :width="event.flyer_width || null" :height="event.flyer_height || null" :class="event._isPast ? 'grayscale' : ''" class="w-full" :alt="event.name">
                                        </a>
                                    </div>
                                </div>
                            </template>

                            {{-- Stacked layout when no flyer image --}}
                            <template v-else>
                                {{-- Hero Banner (only when no flyer): the venue's or an act's header at 960
                                     (Event::cardImageFields()). The gradient lets clicks through to the link. --}}
                                <div v-if="getHeaderImage(event) && !event.is_password_protected" data-reveal-media class="h-40 relative overflow-hidden">
                                    <a :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" tabindex="-1" aria-hidden="true" class="block w-full h-full">
                                        <img :src="getHeaderImage(event)" loading="lazy" :class="event._isPast ? 'grayscale' : ''" class="w-full h-full object-cover" :alt="event.name" v-on:error="$event?.target?.closest('.h-40') && ($event.target.closest('.h-40').style.display='none')">
                                    </a>
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                                </div>

                                {{-- Content --}}
                                <div data-reveal-body class="px-5 py-6 md:px-8 lg:px-16 md:py-8 flex flex-col gap-5">
                                    {{-- Event Title --}}
                                    <div data-reveal-title class="flex items-start gap-2">
                                        <span v-if="getEventDotColor(event)" class="inline-block w-3 h-3 rounded-full flex-shrink-0 mt-2" :style="{ backgroundColor: getEventDotColor(event) }"></span>
                                        <h2 class="font-bold text-2xl md:text-3xl leading-snug line-clamp-2 text-gray-900 dark:text-gray-100" :dir="event.dir || 'auto'">
                                            <a :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" v-html="commaBreak(event.name)"></a>
                                            <svg v-if="event.is_password_protected" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-6 h-6 text-gray-400 ms-2 align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                            <span v-if="event.is_internal" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 ms-2 align-middle">{{ __('messages.internal') }}</span><span v-else-if="event.is_draft" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 ms-2 align-middle">{{ __('messages.draft') }}</span>
                                        </h2>
                                    </div>
                                    <p v-if="event.short_description && !event.is_password_protected" class="text-gray-600 dark:text-gray-400 mt-2" :dir="event.description_dir || event.dir || 'auto'" v-text="event.short_description"></p>

                                    {{-- Date Badge --}}
                                    <div v-if="event.occurrenceDate" class="flex items-center gap-4">
                                        <div data-reveal-date class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col items-center justify-center shadow-sm">
                                            <span class="text-[11px] font-bold uppercase tracking-wider leading-none pt-1 es-date-month" v-text="getMonthAbbr(event._originalOccurrenceDate || event.occurrenceDate)"></span>
                                            <span class="text-2xl font-bold text-gray-900 dark:text-white leading-none" v-text="getDayNum(event._originalOccurrenceDate || event.occurrenceDate)"></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span v-if="event.is_multi_day && event.local_end_date" class="text-lg font-semibold text-gray-900 dark:text-white" v-text="getEventTime(event)"></span>
                                            <span v-else class="text-lg font-semibold text-gray-900 dark:text-white" v-text="formatDayName(event.occurrenceDate)"></span>
                                            <span v-if="!(event.is_multi_day && event.local_end_date)" class="text-sm text-gray-500 dark:text-gray-400">
                                                <span v-text="getEventTime(event)"></span>
                                                <span v-if="event.duration" class="ms-1" v-text="'(' + formatDuration(event.duration) + ')'"></span>
                                            </span>
                                            <span v-else-if="event.duration" class="text-sm text-gray-500 dark:text-gray-400" v-text="formatDuration(event.duration)"></span>
                                        </div>
                                    </div>

                                    {{-- Venue Badge --}}
                                    <a v-if="event.venue_name && event.venue_guest_url && !event.is_password_protected" :href="event.venue_guest_url" class="w-fit flex items-center gap-4 hover:opacity-80 transition-opacity">
                                        <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                            <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                            </svg>
                                        </div>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white truncate hover:underline" v-html="commaBreak(event.venue_name)" :dir="event.venue_dir || 'auto'"></span>
                                        <svg class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                        </svg>
                                    </a>
                                    <div v-else-if="event.venue_name && !event.is_password_protected" class="flex items-center gap-4">
                                        <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                            <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                            </svg>
                                        </div>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white truncate" v-html="commaBreak(event.venue_name)" :dir="event.venue_dir || 'auto'"></span>
                                    </div>

                                    {{-- RSVP Free Badge --}}
                                    <div v-if="event.rsvp_enabled && !event.is_password_protected" class="flex items-center gap-4">
                                        <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                            <svg width="24" height="24" viewBox="0 0 20 20" fill="{{ $accentColor }}" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l7.5 7.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-7.5-7.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $label('free_entry') }}</span>
                                        </div>
                                    </div>

                                    @includeWhen($guestRows, 'role/partials/card-ticket-badge')

                                    {{-- Ticket Price Badge --}}
                                    <div v-if="!event.rsvp_enabled && event.registration_url && event.ticket_price != null && !event.is_password_protected{!! $guestRows ? ' && !cardHasTickets(event)' : '' !!}" class="flex items-center gap-4">
                                        <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                            <svg width="24" height="24" viewBox="0 0 20 20" fill="{{ $accentColor }}" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l7.5 7.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-7.5-7.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <span v-if="event.ticket_price == 0">{{ $label('free_entry') }}</span>
                                                <span v-else v-text="formatPrice(event.ticket_price, event.ticket_currency_code)"></span>
                                            </span>
                                            <span v-if="event.coupon_code || event.coupon_discount_label" class="text-sm text-gray-500 dark:text-gray-400"><span v-if="event.coupon_code">{{ __('messages.coupon_code') }}: <bdi v-text="event.coupon_code"></bdi></span><span v-if="event.coupon_code && event.coupon_discount_label"> &bull; </span><bdi v-if="event.coupon_discount_label" v-text="event.coupon_discount_label"></bdi></span>
                                        </div>
                                    </div>

                                    {{-- Talent Avatars + Names --}}
                                    <div v-if="event.talent && event.talent.length > 0 && !event.is_password_protected" class="flex items-center gap-2 my-2">
                                        <div data-reveal-pop class="flex items-center -space-x-2" :class="isRtl ? 'space-x-reverse' : ''">
                                            <template v-for="(t, tIndex) in event.talent.slice(0, 5)" :key="'ta-' + tIndex">
                                                <img v-if="t.profile_image" :src="t.profile_image" class="w-8 h-8 rounded-full object-cover border-2 border-white dark:border-gray-700" :alt="t.name" :title="t.name">
                                                <div v-else class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-700 bg-gray-200 dark:bg-gray-600 flex items-center justify-center" :title="t.name">
                                                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400" v-text="t.name.charAt(0).toUpperCase()"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <template v-for="(t, tIndex) in event.talent" :key="'tn2-' + tIndex">
                                            <span v-if="tIndex > 0" class="text-base text-gray-600 dark:text-gray-300">, </span>
                                            <a v-if="t.guest_url" :href="t.guest_url" class="text-base text-gray-600 dark:text-gray-300 hover:opacity-80 hover:underline transition-opacity truncate" v-html="commaBreak(t.name)" :dir="t.dir || 'auto'"></a>
                                            <span v-else class="text-base text-gray-600 dark:text-gray-300 truncate" v-html="commaBreak(t.name)" :dir="t.dir || 'auto'"></span>
                                        </template>
                                        <svg v-if="event.talent.some(t => t.guest_url)" class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                        </svg>
                                    </div>

                                    {{-- Video Thumbnails --}}
                                    <div v-if="event.videos && event.videos.length > 0 && !event.is_password_protected && event.fan_videos_enabled" class="mt-3 space-y-2">
                                        {{-- Playing video iframe (full width, above thumbnails) --}}
                                        <div v-if="event.videos.some((v, i) => playingVideo === event.uniqueKey + '-' + i)"
                                             class="w-full aspect-video rounded-lg overflow-hidden shadow-sm" @click.stop>
                                            <template v-for="(vid, vidIdx) in event.videos" :key="'playing-' + vidIdx">
                                                <iframe v-if="playingVideo === event.uniqueKey + '-' + vidIdx && vid.embed_url"
                                                        :src="vid.embed_url + '?autoplay=1'"
                                                        class="w-full h-full" frameborder="0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen></iframe>
                                            </template>
                                        </div>
                                        {{-- Thumbnail row --}}
                                        <div class="flex gap-2 overflow-x-auto">
                                            <div v-for="(vid, vidIdx) in event.videos" :key="'vid-' + vidIdx"
                                                 @click.stop="playVideo(event.uniqueKey + '-' + vidIdx)"
                                                 class="relative flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden shadow-sm group/vid cursor-pointer"
                                                 :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'ring-2 ring-blue-500' : ''">
                                                <img :src="vid.thumbnail_url" class="w-full h-full object-cover" alt="">
                                                <div class="absolute inset-0 flex items-center justify-center"
                                                     :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'bg-black/50' : 'bg-black/30'">
                                                    <svg v-if="playingVideo !== event.uniqueKey + '-' + vidIdx"
                                                         class="w-8 h-8 text-white opacity-80 group-hover/vid:opacity-100"
                                                         viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                    <svg v-else class="w-6 h-6 text-white"
                                                         viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Photo Thumbnails --}}
                                    <div v-if="event.photos && event.photos.length > 0 && !event.is_password_protected && event.fan_photos_enabled" class="mt-3">
                                        <div class="flex gap-2 overflow-x-auto">
                                            <div v-for="(photo, photoIdx) in event.photos" :key="'photo2-' + photoIdx"
                                                 class="relative flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden shadow-sm">
                                                <img :src="photo.url" class="w-full h-full object-cover" alt="" loading="lazy">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Recent Comments --}}
                                    <div v-if="event.recent_comments && event.recent_comments.length > 0 && !event.is_password_protected && event.fan_comments_enabled" class="space-y-1.5" :dir="isRtl ? 'rtl' : 'ltr'">
                                        <div v-for="(comment, cIdx) in event.recent_comments" :key="'c-' + cIdx"
                                             class="flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M3.43 2.524A41.29 41.29 0 0110 2c2.236 0 4.43.18 6.57.524 1.437.231 2.43 1.49 2.43 2.902v5.148c0 1.413-.993 2.67-2.43 2.902a41.202 41.202 0 01-5.183.501.78.78 0 00-.528.224l-3.579 3.58A.75.75 0 016 17.25v-3.443a41.033 41.033 0 01-2.57-.33C2.993 13.244 2 11.986 2 10.574V5.426c0-1.413.993-2.67 2.43-2.902z" clip-rule="evenodd" />
                                            </svg>
                                            <span>"<span v-text="comment.text"></span>" - <span class="font-medium" v-text="comment.author"></span></span>
                                        </div>
                                    </div>

                                    {{-- Polls --}}
                                    <div v-if="event.polls && event.polls.length > 0 && !event.is_password_protected" class="space-y-3" @click.stop>
                                        <div v-for="poll in event.polls" :key="poll.id"
                                             class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                            <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 mb-3"
                                                v-text="poll.question"></h4>
                                            <div v-if="poll.user_vote === null && poll.is_active">
                                                <button v-for="(option, idx) in poll.options" :key="idx"
                                                        @click.stop="votePoll(event, poll, idx, $event)"
                                                        :disabled="votingPoll[poll.id] != null"
                                                        class="w-full text-start px-3 py-2.5 mb-1.5 text-sm rounded-lg border transition-all duration-200"
                                                        :class="votingPoll[poll.id] != null && votingPoll[poll.id] !== idx ? 'opacity-40 border-gray-300 dark:border-gray-600' : 'border-gray-300 dark:border-gray-600 hover:border-gray-500'"
                                                        :style="votingPoll[poll.id] === idx ? { borderColor: accentColor, backgroundColor: accentColor + '15' } : {}"
                                                        >
                                                    <span v-text="option" class="dark:text-gray-200" :class="votingPoll[poll.id] === idx ? 'font-medium' : ''"></span>
                                                </button>
                                                @guest
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <a href="{{ app_url('/login') }}" class="underline">{{ __('messages.sign_in_to_vote') }}</a>
                                                </p>
                                                @endguest
                                            </div>
                                            <div v-else>
                                                <div v-for="(option, idx) in poll.options" :key="idx" class="mb-2.5">
                                                    <div class="flex justify-between text-sm mb-1">
                                                        <span class="flex items-center gap-1 text-gray-800 dark:text-gray-200" :class="{ 'font-semibold': getVoteCount(poll, idx) === getMaxVoteCount(poll) && poll.total_votes > 0 }">
                                                            <span v-text="option"></span>
                                                            <svg v-if="idx === poll.user_vote" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 shrink-0" :style="{ color: accentColor }">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                            </svg>
                                                        </span>
                                                        <span class="text-gray-500 dark:text-gray-400 text-xs tabular-nums">
                                                            <span v-text="getVoteCount(poll, idx)"></span> (<span v-text="getVotePercent(poll, idx)"></span>%)
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                        <div class="h-2.5 rounded-full"
                                                             :style="{
                                                                 width: (pollAnimating[poll.id] ? 0 : Math.max(getVotePercent(poll, idx), poll.total_votes > 0 ? 2 : 0)) + '%',
                                                                 backgroundColor: idx === poll.user_vote ? accentColor : (getVoteCount(poll, idx) === getMaxVoteCount(poll) && poll.total_votes > 0 ? accentColor + '80' : '#9ca3af'),
                                                                 boxShadow: idx === poll.user_vote ? '0 0 8px ' + accentColor + '40' : 'none',
                                                                 transition: 'width 700ms ease-out',
                                                                 transitionDelay: (idx * 120) + 'ms'
                                                             }"></div>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                                                    <span v-text="poll.total_votes"></span> {{ __('messages.votes') }}
                                                    <span v-if="!poll.is_active" class="ms-1">&middot; {{ __('messages.poll_closed_status') }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mini Timeline for Parts --}}
                                    <div v-if="event.parts && event.parts.length > 0 && !event.is_password_protected" :dir="isRtl ? 'rtl' : 'ltr'">
                                        <div class="relative ps-2">
                                            <div class="absolute top-1 bottom-1 w-0.5 start-0" :style="'background-color: {{ $accentColor }}30'"></div>
                                            <div class="space-y-2">
                                                <div v-for="(part, partIndex) in event.parts.slice(0, 4)" :key="'p-' + partIndex" class="relative flex items-start gap-2">
                                                    <div class="absolute top-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0 -start-[3px]" :style="'background-color: {{ $accentColor }}'"></div>
                                                    <div class="ps-3">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300" v-html="commaBreak(part.name)"></span>
                                                        <span v-if="part.start_time" class="text-xs ms-1 text-gray-500 dark:text-gray-400" v-text="part.start_time"></span>
                                                    </div>
                                                </div>
                                                <div v-if="event.parts.length > 4" class="text-xs text-gray-500 dark:text-gray-400 ps-3">
                                                    +<span v-text="event.parts.length - 4"></span> {{ __('messages.more') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action buttons row --}}
                                    <div class="flex flex-wrap items-center gap-2">
                                            <button v-if="event.fan_photos_enabled" @click.stop="togglePhotoForm(event, $event)"
                                                    class="hover-accent inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 border"
                                                    style="border-color: {{ $accentColor }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
                                                {{ $label('add_photo') }}
                                            </button>
                                            <button v-if="event.fan_videos_enabled" @click.stop="toggleVideoForm(event, $event)"
                                                    class="hover-accent inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 border"
                                                    style="border-color: {{ $accentColor }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                                {{ $label('add_video') }}
                                            </button>
                                            <button v-if="event.fan_comments_enabled" @click.stop="toggleCommentForm(event, $event)"
                                                    class="hover-accent inline-flex items-center gap-1.5 whitespace-nowrap px-3 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 border"
                                                    style="border-color: {{ $accentColor }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                                                {{ $label('add_comment') }}
                                            </button>
                                    </div>

                                    {{-- Video form --}}
                                    <form v-if="openVideoForm[event.uniqueKey]" @click.stop
                                          method="POST" :action="event.submit_video_url">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                        <div class="flex flex-col gap-2">
                                            <select v-if="event.parts.length > 0" name="event_part_id"
                                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                <option value="">{{ __('messages.general') }}</option>
                                                <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                            </select>
                                            <input type="text" name="youtube_url" placeholder="{{ __('messages.paste_youtube_url') }}"
                                                   class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                                            <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                        </div>
                                    </form>

                                    {{-- Photo form --}}
                                    <form v-if="openPhotoForm[event.uniqueKey]" @click.stop
                                          method="POST" :action="event.submit_photo_url" enctype="multipart/form-data"
                                          :data-key="event.uniqueKey">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                        <div class="flex flex-col gap-2">
                                            <select v-if="event.parts.length > 0" name="event_part_id"
                                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                <option value="">{{ __('messages.general') }}</option>
                                                <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                            </select>
                                            <div @click="$event.currentTarget.querySelector('input[type=file]').click()"
                                                 @dragover.prevent="$event.currentTarget.style.borderColor = '{{ $accentColor }}'"
                                                 @dragenter.prevent="$event.currentTarget.style.borderColor = '{{ $accentColor }}'"
                                                 @dragleave.prevent="$event.currentTarget.style.borderColor = ''"
                                                 @drop.prevent="const zone = $event.currentTarget; zone.style.borderColor = ''; const f = $event.dataTransfer.files[0]; if (f && f.type.startsWith('image/')) { const inp = zone.querySelector('input[type=file]'); inp.files = $event.dataTransfer.files; const r = new FileReader(); r.onload = ev => { zone.querySelector('.photo-dropzone-preview img').src = ev.target.result; zone.querySelector('.photo-dropzone-placeholder').classList.add('hidden'); zone.querySelector('.photo-dropzone-preview').classList.remove('hidden'); zone.closest('form').querySelector('.photo-submit-btn').classList.remove('hidden'); const camBtn = zone.closest('form').querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.add('hidden'); }; r.readAsDataURL(f); }"
                                                 class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 cursor-pointer transition-colors">
                                                <div class="photo-dropzone-placeholder flex flex-col items-center justify-center py-6 text-gray-500 dark:text-gray-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mb-1" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
                                                    <span class="text-sm hidden sm:inline">{{ __('messages.drag_photo_or_click') }}</span>
                                                    <span class="text-sm sm:hidden">{{ __('messages.choose_from_library') }}</span>
                                                </div>
                                                <div class="photo-dropzone-preview hidden relative p-2">
                                                    <img src="" class="rounded-lg max-h-48 mx-auto object-cover">
                                                    <button type="button" @click.stop="const zone = $event.target.closest('.rounded-lg.border-dashed'); zone.querySelector('.photo-dropzone-placeholder').classList.remove('hidden'); zone.querySelector('.photo-dropzone-preview').classList.add('hidden'); zone.querySelector('input[type=file]').value = ''; zone.closest('form').querySelector('.photo-submit-btn').classList.add('hidden'); const camBtn = zone.closest('form').querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.remove('hidden'); const camInp = zone.closest('form').querySelector('.camera-input'); if (camInp) camInp.value = '';" class="absolute top-3 {{ $role?->isRtl() ? 'left-3' : 'right-3' }} bg-black/60 hover:bg-black/80 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm leading-none">&times;</button>
                                                </div>
                                                <input type="file" name="photo" accept="image/*" class="hidden"
                                                       @change="if ($event.target.files[0]) { const zone = $event.target.closest('.rounded-lg.border-dashed'); const r = new FileReader(); r.onload = e => { zone.querySelector('.photo-dropzone-preview img').src = e.target.result; zone.querySelector('.photo-dropzone-placeholder').classList.add('hidden'); zone.querySelector('.photo-dropzone-preview').classList.remove('hidden'); zone.closest('form').querySelector('.photo-submit-btn').classList.remove('hidden'); const camBtn = zone.closest('form').querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.add('hidden'); }; r.readAsDataURL($event.target.files[0]); }">
                                            </div>
                                            <button type="button" @click="$event.currentTarget.closest('form').querySelector('.camera-input').click()"
                                                    class="photo-camera-btn sm:hidden w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-lg border-2 transition-colors"
                                                    style="border-color: {{ $accentColor }}; color: {{ $accentColor }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
                                                {{ __('messages.take_photo') }}
                                            </button>
                                            <input type="file" accept="image/*" capture="environment" class="camera-input hidden"
                                                   @change="if ($event.target.files[0]) { const form = $event.target.closest('form'); const zone = form.querySelector('.rounded-lg.border-dashed'); const inp = form.querySelector('input[name=photo]'); inp.files = $event.target.files; const r = new FileReader(); r.onload = e => { zone.querySelector('.photo-dropzone-preview img').src = e.target.result; zone.querySelector('.photo-dropzone-placeholder').classList.add('hidden'); zone.querySelector('.photo-dropzone-preview').classList.remove('hidden'); form.querySelector('.photo-submit-btn').classList.remove('hidden'); const camBtn = form.querySelector('.photo-camera-btn'); if (camBtn) camBtn.classList.add('hidden'); }; r.readAsDataURL($event.target.files[0]); }">
                                            <button type="submit" class="photo-submit-btn hidden self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.upload_photo') }}</button>
                                        </div>
                                    </form>

                                    {{-- Comment form --}}
                                    <form v-if="openCommentForm[event.uniqueKey]" @click.stop
                                          method="POST" :action="event.submit_comment_url">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                        <div class="flex flex-col gap-2">
                                            <select v-if="event.parts.length > 0" name="event_part_id"
                                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                <option value="">{{ __('messages.general') }}</option>
                                                <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                            </select>
                                            <textarea name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000"
                                                      class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                                            <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                        </div>
                                    </form>

                                    <a v-if="event.can_edit" :href="event.edit_url"
                                       class="text-sm {{ $route == 'admin' ? 'font-medium text-[var(--brand-blue)] hover:underline' : 'text-gray-500 dark:text-gray-400 hover:underline hover:text-gray-700 dark:hover:text-gray-200' }}"
                                       @click.stop>
                                        {{ __('messages.edit_event') }}
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- No past rows drawn: the end of the upcoming rows is the end of the list. --}}
            <template v-if="firstPastGroupIndex === -1">
                @include('role/partials/list-more')
            </template>

            {{-- Load More Button --}}
            <div v-if="!hidePastEvents && hasMorePastEvents && activeFilterCount === 0" class="mt-6 text-center">
                <button @click.stop="loadMorePastEvents()"
                        :disabled="loadingPastEvents"
                        class="inline-flex items-center px-6 py-2.5 text-sm font-semibold rounded-xl border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
                        style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}">
                    <svg v-if="loadingPastEvents" class="animate-spin -ms-1 me-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $label('load_more') }}
                </button>
                <div v-if="pastLoadFailed" role="alert" class="mt-3">
                    <span class="inline-block rounded-lg bg-white/95 dark:bg-gray-900/95 px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.error_loading') }}</span>
                </div>
            </div>


            {{-- Empty State. With a filter active, flatPastEvents is always empty, so the raw
                 pastEvents test below would leave a filter that matches nothing on a blank page. --}}
            <div v-if="!isLoadingEvents && !loadFailed && narrowingFilterCount > 0 && allListGroups.length === 0" class="pb-4 text-center">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div v-pre class="text-xl text-gray-500 dark:text-gray-400">
                        {{ $label('no_events_found') }}
                    </div>
                    @if (! (isset($embed) && $embed))
                    <button type="button" @click="clearFilters"
                            class="mt-4 inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <span v-pre>{{ $label('clear_filters') }}</span>
                    </button>
                    @endif
                </div>
            </div>
            <div v-else-if="!isLoadingEvents && !loadFailed && flatUpcomingEvents.length === 0 && pastEvents.length === 0" class="pb-4 text-center">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div class="text-xl text-gray-500 dark:text-gray-400">
                        {{ $label('no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- List View Skeleton (Mobile) --}}
        <div v-if="currentView === 'list' && isLoadingEvents" class="{{ $guestRows ? 'gk-list md:hidden' : 'md:hidden' }} animate-pulse">
            {{-- Date Header Skeleton --}}
            <div class="sticky top-0 z-10 {{ $stickyBleedClass }} bg-white dark:bg-gray-800">
                <div class="px-4 pb-5 pt-3 flex items-center gap-4">
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                    <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                </div>
            </div>
            {{-- Card Skeletons --}}
            <div class="space-y-3">
                @for ($i = 0; $i < 5; $i++)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex">
                        <div class="flex-1 py-3 px-4 flex flex-col min-w-0 gap-2">
                            {{-- Title --}}
                            <div class="h-5 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            {{-- Short description --}}
                            <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            {{-- Venue --}}
                            <div class="flex items-center gap-2">
                                <div class="h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                            {{-- Time --}}
                            <div class="flex items-center gap-2">
                                <div class="h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        {{-- Image Thumbnail --}}
                        <div class="flex-shrink-0 w-24 h-28 bg-gray-200 dark:bg-gray-700"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        {{-- List View (Mobile) --}}
        {{-- v-if on a guest page, and only on a phone: a list that is not the one on screen is
             not in the page at all (the cards above are the list from a tablet up). --}}
        <div {!! $guestRows ? 'v-if="currentView === \'list\' && !isLoadingEvents && isNarrow"' : 'v-show="currentView === \'list\' && !isLoadingEvents"' !!} :data-list-anim="activeListAnimation !== 'none' ? activeListAnimation : null" :data-list-rtl="isRtl ? '' : null" style="--es-accent: {{ $accentColor }}" class="{{ $guestRows ? 'gk-list' : 'md:hidden' }} {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
            @if ($guestRows)
            {{-- The guest list on a phone: a panel for each day, a row for each event
                 (partials/guest-kit-styles: .gk-day, .gk-row).

                 A row says when, what and where, and nothing that needs a second request or a
                 second look: performers, the agenda, polls and the fan buttons are on the
                 cards a wider screen gets, and on the event's own page. data-reveal-* are the
                 hooks the schedule's list animation uses (resources/css/list-reveal.css). --}}
            <div v-if="allListGroups.length > 0" class="gk-days">
                <template v-for="(group, groupIndex) in allListGroups" :key="'list-m-' + group.date">
                    {{-- The end of the upcoming rows, which is above the past ones. --}}
                    <template v-if="groupIndex === firstPastGroupIndex">
                        @include('role/partials/list-more')
                    </template>
                    {{-- Past Events Divider --}}
                    <div v-if="group.events.every(e => e._isPast) && (groupIndex === 0 || !allListGroups[groupIndex - 1].events.every(e => e._isPast))" class="gk-past" role="heading" aria-level="2">
                        <span class="gk-past-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1"/><path d="M3.5 4.5v4h4"/><path d="M12 7.5V12l3 2"/></svg><span>{{ $label('past_events') }}</span></span>
                    </div>
                    <section v-if="group.events.some(e => isEventVisible(e))" class="gk-panel gk-day bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm" :class="{ 'gk-day-past': group.events.every(e => e._isPast) }">
                        {{-- The dateless group has no heading, so it never says "Invalid Date". --}}
                        <div v-if="group.date && group.date !== 'no-date'" class="gk-dayhead">
                            {{-- "Today" and "Tomorrow" by the schedule's clock, not the visitor's:
                                 a day is a day because of where it happens. --}}
                            <span v-if="dayWord(group.date)" class="gk-dayhead-word" v-text="dayWord(group.date)"></span>
                            {{-- A heading, as the day is over the cards a wider screen gets. --}}
                            <h2 class="gk-dayhead-title" v-text="formatDateHeader(group.date)" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></h2>
                        </div>
                        <ul class="gk-rows">
                            <template v-for="event in group.events" :key="'list-mob-' + event.uniqueKey">
                                @include('role/partials/guest-row')
                            </template>
                        </ul>
                    </section>
                </template>
            </div>
            @else
            {{-- All events grouped by date --}}
            <div v-if="allListGroups.length > 0" class="space-y-6">
                <template v-for="(group, groupIndex) in allListGroups" :key="'list-m-' + group.date">
                    {{-- The end of the upcoming rows, which is above the past ones. --}}
                    <template v-if="groupIndex === firstPastGroupIndex">
                        @include('role/partials/list-more')
                    </template>
                    {{-- Past Events Divider --}}
                    <div v-if="group.events.every(e => e._isPast) && (groupIndex === 0 || !allListGroups[groupIndex - 1].events.every(e => e._isPast))"
                         class="py-1 flex items-center gap-4">
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-1 bg-white dark:bg-gray-900">
                            {{ $label('past_events') }}
                        </span>
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                    </div>
                    {{-- Date Header (guard the dateless group so it never shows "Invalid Date") --}}
                    <div v-if="group.date && group.date !== 'no-date'" class="sticky top-0 z-10 {{ $stickyBleedClass }} bg-white dark:bg-gray-800">
                        <div class="pb-5 pt-3 px-4 flex items-center gap-4">
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100 text-center" v-text="formatDateHeader(group.date)" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></div>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                        </div>
                    </div>
                    {{-- Compact cards --}}
                    <div class="space-y-3">
                        <template v-for="event in group.events" :key="'list-mob-' + event.uniqueKey">
                            <div v-if="isEventVisible(event)" v-list-reveal:m="event.uniqueKey" @click="navigateToEvent(event, $event)" class="block cursor-pointer">
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div class="flex">
                                        @include('role/partials/mobile-event-card')
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            @endif

            {{-- No past rows drawn: the end of the upcoming rows is the end of the list. --}}
            <template v-if="firstPastGroupIndex === -1">
                @include('role/partials/list-more')
            </template>

            {{-- Load More Button --}}
            <div v-if="!hidePastEvents && hasMorePastEvents && activeFilterCount === 0" class="mt-6 text-center">
                <button @click.stop="loadMorePastEvents()"
                        :disabled="loadingPastEvents"
                        class="inline-flex items-center px-6 py-2.5 text-sm font-semibold rounded-xl border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
                        style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}">
                    <svg v-if="loadingPastEvents" class="animate-spin -ms-1 me-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ $label('load_more') }}
                </button>
                <div v-if="pastLoadFailed" role="alert" class="mt-3">
                    <span class="inline-block rounded-lg bg-white/95 dark:bg-gray-900/95 px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.error_loading') }}</span>
                </div>
            </div>

            {{-- Empty State (see the desktop list's note on filters) --}}
            <div v-if="!isLoadingEvents && !loadFailed && narrowingFilterCount > 0 && allListGroups.length === 0 && {{ ($tab ?? '') != 'availability' ? 'true' : 'false' }}" class="pb-4 text-center">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div v-pre class="text-xl text-gray-500 dark:text-gray-400">
                        {{ $label('no_events_found') }}
                    </div>
                    @if (! (isset($embed) && $embed))
                    <button type="button" @click="clearFilters"
                            class="mt-4 inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        <span v-pre>{{ $label('clear_filters') }}</span>
                    </button>
                    @endif
                </div>
            </div>
            <div v-else-if="!isLoadingEvents && !loadFailed && flatUpcomingEvents.length === 0 && pastEvents.length === 0 && {{ ($tab ?? '') != 'availability' ? 'true' : 'false' }}" class="pb-4 text-center">
                <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div class="text-xl text-gray-500 dark:text-gray-400">
                        {{ $label('no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Mobile Filters Bottom Sheet Drawer - Teleported to body to escape stacking context --}}
<Teleport to="body">
<div v-cloak v-if="showFiltersDrawer" class="md:hidden fixed inset-0 z-50">
    {{-- Backdrop --}}
    <div @click="showFiltersDrawer = false"
         class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>

    {{-- Bottom sheet panel --}}
    <div ref="mobileFilterPanel" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="filters-drawer-title"
         class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl max-h-[80vh] overflow-y-auto focus:outline-none {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
        {{-- Header --}}
        <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <div>
                <h3 id="filters-drawer-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $label('filters') }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" aria-live="polite" v-text="filteredCountLabel"></p>
            </div>
            <button v-if="activeFilterCount > 0"
                    type="button"
                    @click="clearFilters"
                    class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] font-medium">
                {{ $label('clear_filters') }}
            </button>
        </div>

        {{-- Search. Matches the name, description, venue, category, talent, agenda parts and
             public custom field values of the events the filters look at. --}}
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400 dark:text-gray-500">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                {{-- dir only once there is text: an empty dir="auto" box lays the placeholder out
                     left-to-right under a right-aligned icon on a Hebrew schedule. --}}
                <input type="search" id="filter-search-m" v-model="searchInput" ref="mobileFilterSearch"
                       :dir="searchInput ? 'auto' : null"
                       enterkeyhint="search" autocomplete="off"
                       aria-label="{{ $label('search_events') }}"
                       placeholder="{{ $label('search_events') }}"
                       @keydown.enter.prevent="onSearchEnter($event)"
                       @keydown.escape.stop.prevent="onSearchEscape"
                       style="font-family: sans-serif"
                       class="w-full py-2.5 ps-9 pe-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            </div>
            <p v-if="filterScopeIsMonth" class="mt-2 text-xs text-gray-500 dark:text-gray-400"><span v-pre>{{ $label('search_scope_month') }}</span></p>
        </div>

        {{-- Schedule Filter --}}
        @if(isset($role) && $role->groups && $role->groups->count() > 1)
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <label for="filter-group-m" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label('schedule') }}</label>
            <select id="filter-group-m" v-model="selectedGroup" style="font-family: sans-serif"
                    class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
                <option value="">{{ $label('show_all') }}</option>
                <option v-for="group in groups" :key="group.slug" :value="group.slug">
                    @{{ group.name }} (@{{ eventCountByGroup[group.slug] || 0 }})
                </option>
            </select>
        </div>
        @endif

        {{-- Category Filter --}}
        <div v-if="availableCategories.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <label for="filter-category-m" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label('category') }}</label>
            <div class="relative">
                <span v-if="selectedCategoryColor"
                      class="absolute start-3 top-1/2 -translate-y-1/2 inline-block w-2.5 h-2.5 rounded-full pointer-events-none z-10"
                      :style="{ backgroundColor: selectedCategoryColor }"></span>
                <select id="filter-category-m" v-model="selectedCategory" style="font-family: sans-serif"
                        :class="['w-full py-2.5 pe-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm', selectedCategoryColor ? 'ps-8' : 'ps-3']">
                    <option value="">{{ $label('show_all') }}</option>
                    <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                        @{{ category.name }} (@{{ eventCountByCategory[category.id] || 0 }})
                    </option>
                </select>
            </div>
        </div>

        {{-- Venue Filter --}}
        <div v-if="uniqueVenues.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <label for="filter-venue-m" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label('venue') }}</label>
            <select id="filter-venue-m" v-model="selectedVenue" style="font-family: sans-serif"
                    class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                <option value="">{{ $label('show_all') }}</option>
                <option v-for="venue in uniqueVenues" :key="venue.subdomain" :value="venue.subdomain">
                    @{{ venue.name }} (@{{ eventCountByVenue[venue.subdomain] || 0 }})
                </option>
            </select>
        </div>

        {{-- Custom Field Filters ("Show as filter" fields). Shown while there is a choice to make, and
             always while one is selected, so a filter that arrived in a shared link can be seen and
             cleared even when it matches nothing in view. --}}
        <template v-for="field in filterCustomFields" :key="field.key">
            <div v-if="(availableCustomFieldOptions[field.key] || []).length > 1 || selectedCustomFields[field.key]"
                 class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label :for="'filter-cf-m-' + field.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @{{ field.name }}
                </label>
                <select :id="'filter-cf-m-' + field.key" v-model="selectedCustomFields[field.key]" style="font-family: sans-serif"
                        class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">{{ $label('show_all') }}</option>
                    <option v-for="opt in availableCustomFieldOptions[field.key]" :key="opt.key" :value="opt.key">
                        @{{ opt.label }} (@{{ (eventCountByCustomField[field.key] || {})[opt.key] || 0 }})
                    </option>
                </select>
            </div>
        </template>

        {{-- Price Filter --}}
        <div v-if="hasFreeEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div @click="showFreeOnly = !showFreeOnly" class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label('free_entry') }}</span>
                <button role="switch" :aria-checked="showFreeOnly.toString()"
                        class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                        :class="showFreeOnly ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                          :class="showFreeOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        {{-- Online Filter --}}
        <div v-if="hasOnlineEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div @click="showOnlineOnly = !showOnlineOnly" class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label('online') }}</span>
                <button role="switch" :aria-checked="showOnlineOnly.toString()"
                        class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                        :class="showOnlineOnly ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                          :class="showOnlineOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        {{-- Footer. Copy link hands out the filtered view (category and custom fields, the params a
             visitor could not otherwise type); Done, the forward action, stays last. --}}
        <div class="px-6 py-4 flex gap-3">
            @if (! (isset($embed) && $embed))
            <button v-if="hasShareableFilter && (route === 'admin' || route === 'guest')"
                    type="button" @click="copyFilterLink"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                </svg>
                <span aria-live="polite"><span v-if="linkCopied">{{ __('messages.copied') }}</span><span v-else>{{ __('messages.copy_link') }}</span></span>
            </button>
            @endif
            <x-brand-button @click="showFiltersDrawer = false" class="flex-1">
                {{ $label('done') }}
            </x-brand-button>
        </div>

    </div>
</div>
</Teleport>

{{-- Desktop Filters Modal - Teleported to body to escape stacking context --}}
<Teleport to="body">
<div v-cloak v-if="showDesktopFiltersModal" class="hidden md:block fixed inset-0 z-[100]">
    {{-- Backdrop --}}
    <div @click="showDesktopFiltersModal = false"
         class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity z-[100]"></div>

    {{-- Modal panel --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 z-[101] pointer-events-none">
        <div ref="desktopFilterPanel" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="filters-modal-title"
             class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md max-h-[80vh] overflow-y-auto pointer-events-auto focus:outline-none {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
            {{-- Header --}}
            <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                <div>
                    <h3 id="filters-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $label('filters') }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" aria-live="polite" v-text="filteredCountLabel"></p>
                </div>
                <button v-if="activeFilterCount > 0"
                        type="button"
                        @click="clearFilters"
                        class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] font-medium">
                    {{ $label('clear_filters') }}
                </button>
            </div>

            {{-- Search. Matches the name, description, venue, category, talent, agenda parts and
                 public custom field values of the events the filters look at. --}}
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400 dark:text-gray-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    {{-- dir only once there is text: an empty dir="auto" box lays the placeholder out
                         left-to-right under a right-aligned icon on a Hebrew schedule. --}}
                    <input type="search" id="filter-search-d" v-model="searchInput" ref="desktopFilterSearch"
                           :dir="searchInput ? 'auto' : null"
                           enterkeyhint="search" autocomplete="off"
                           aria-label="{{ $label('search_events') }}"
                           placeholder="{{ $label('search_events') }}"
                           @keydown.enter.prevent="onSearchEnter($event)"
                           @keydown.escape.stop.prevent="onSearchEscape"
                           style="font-family: sans-serif"
                           class="w-full py-2.5 ps-9 pe-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                </div>
                <p v-if="filterScopeIsMonth" class="mt-2 text-xs text-gray-500 dark:text-gray-400"><span v-pre>{{ $label('search_scope_month') }}</span></p>
            </div>

            {{-- Schedule Filter --}}
            @if(isset($role) && $role->groups && $role->groups->count() > 1)
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label for="filter-group-d" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label('schedule') }}</label>
                <select id="filter-group-d" v-model="selectedGroup" style="font-family: sans-serif"
                        class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
                    <option value="">{{ $label('show_all') }}</option>
                    <option v-for="group in groups" :key="group.slug" :value="group.slug">
                        @{{ group.name }} (@{{ eventCountByGroup[group.slug] || 0 }})
                    </option>
                </select>
            </div>
            @endif

            {{-- Category Filter --}}
            <div v-if="availableCategories.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label for="filter-category-d" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label('category') }}</label>
                <div class="relative">
                    <span v-if="selectedCategoryColor"
                          class="absolute start-3 top-1/2 -translate-y-1/2 inline-block w-2.5 h-2.5 rounded-full pointer-events-none z-10"
                          :style="{ backgroundColor: selectedCategoryColor }"></span>
                    <select id="filter-category-d" v-model="selectedCategory" style="font-family: sans-serif"
                            :class="['w-full py-2.5 pe-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm', selectedCategoryColor ? 'ps-8' : 'ps-3']">
                        <option value="">{{ $label('show_all') }}</option>
                        <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                            @{{ category.name }} (@{{ eventCountByCategory[category.id] || 0 }})
                        </option>
                    </select>
                </div>
            </div>

            {{-- Venue Filter --}}
            <div v-if="uniqueVenues.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label for="filter-venue-d" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label('venue') }}</label>
                <select id="filter-venue-d" v-model="selectedVenue" style="font-family: sans-serif"
                        class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">{{ $label('show_all') }}</option>
                    <option v-for="venue in uniqueVenues" :key="venue.subdomain" :value="venue.subdomain">
                        @{{ venue.name }} (@{{ eventCountByVenue[venue.subdomain] || 0 }})
                    </option>
                </select>
            </div>

            {{-- Custom Field Filters ("Show as filter" fields). Shown while there is a choice to make, and
                 always while one is selected, so a filter that arrived in a shared link can be seen and
                 cleared even when it matches nothing in view. --}}
            <template v-for="field in filterCustomFields" :key="field.key">
                <div v-if="(availableCustomFieldOptions[field.key] || []).length > 1 || selectedCustomFields[field.key]"
                     class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <label :for="'filter-cf-d-' + field.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @{{ field.name }}
                    </label>
                    <select :id="'filter-cf-d-' + field.key" v-model="selectedCustomFields[field.key]" style="font-family: sans-serif"
                            class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                                   bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                        <option value="">{{ $label('show_all') }}</option>
                        <option v-for="opt in availableCustomFieldOptions[field.key]" :key="opt.key" :value="opt.key">
                            @{{ opt.label }} (@{{ (eventCountByCustomField[field.key] || {})[opt.key] || 0 }})
                        </option>
                    </select>
                </div>
            </template>

            {{-- Price Filter --}}
            <div v-if="hasFreeEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div @click="showFreeOnly = !showFreeOnly" class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label('free_entry') }}</span>
                    <button role="switch" :aria-checked="showFreeOnly.toString()"
                            class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                            :class="showFreeOnly ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                              :class="showFreeOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            {{-- Online Filter --}}
            <div v-if="hasOnlineEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div @click="showOnlineOnly = !showOnlineOnly" class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label('online') }}</span>
                    <button role="switch" :aria-checked="showOnlineOnly.toString()"
                            class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                            :class="showOnlineOnly ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                              :class="showOnlineOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            {{-- Footer. Copy link hands out the filtered view (category and custom fields, the params a
                 visitor could not otherwise type); Done, the forward action, stays last. --}}
            <div class="px-6 py-4 flex gap-3">
                @if (! (isset($embed) && $embed))
                <button v-if="hasShareableFilter && (route === 'admin' || route === 'guest')"
                        type="button" @click="copyFilterLink"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                    <span aria-live="polite"><span v-if="linkCopied">{{ __('messages.copied') }}</span><span v-else>{{ __('messages.copy_link') }}</span></span>
                </button>
                @endif
                <x-brand-button @click="showDesktopFiltersModal = false" class="flex-1">
                    {{ $label('done') }}
                </x-brand-button>
            </div>

        </div>
    </div>
</div>
</Teleport>

@if (! request()->graphic && ($tab ?? '') !== 'availability')
@include('role.partials.month-peek')
@endif

{{-- A schedule page passes $hasActivePolls: its $events are only the next 50 public ones, while the
     list's cards take votes on whatever the calendar fetches. Other callers (?graphic=1, the
     admin, the dashboard) pass none and keep asking their own $events. --}}
@if (isset($role) && $role->isPro() && ($hasActivePolls ?? $events->contains(fn($e) => ($e->polls_count ?? 0) > 0)))
<script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!}></script>
<script src="{{ asset('js/poll-confetti.js') }}" {!! nonce_attr() !!}></script>
@endif
<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
@include('role.partials.month-script')
<script {!! nonce_attr() !!}>
if (typeof Vue !== 'undefined') {
const { createApp } = Vue;

// Event animations: the entries of the event lists (the wide list's cards, a phone's rows, the
// rows under a phone's month; on the admin and the dashboard, the mobile list and agenda)
// animate in as they scroll into view, styled by resources/css/list-reveal.css. The CSS only hides
// a card under a root that carries data-list-anim, which Vue binds from activeListAnimation, so a
// script that never runs leaves every card visible. The reveal state lives in data-list-revealed
// (an attribute, not a class, so Vue's class patching can never wipe it):
//   "in"      - playing (transitions and keyframes run, staggered by --reveal-delay)
//   "instant" - shown with no motion (a card the visitor has already scrolled past)
//   "done"    - settled: no transform, filter or pseudo-element is left on the card
const listRevealMotionOk = (() => {
    try {
        const nav = performance.getEntriesByType('navigation')[0];
        return 'IntersectionObserver' in window
            && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
            // The accessibility widget's own switch. Its class lands on <html> only once the
            // widget's module runs, after this script, so read what it persisted instead.
            && localStorage.getItem('es_a11y_reduce_motion') !== '1'
            // Coming back from an event page, the list should already be there.
            && !(nav && nav.type === 'back_forward');
    } catch (e) {
        return false;
    }
})();

const listReveal = (() => {
    // Keys of cards that have already revealed, so a card Vue re-creates (a filter change, a
    // refetch) settles straight away instead of replaying or flashing.
    const revealedKeys = new Set();
    const pending = new Set();
    let observer = null;
    let dealCounter = 0;
    let firstBatch = true;

    const keyFor = (binding) => (binding.arg || '') + ':' + binding.value;
    const isRendered = (el) => el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0;

    // The wave inside a card: each row of the text column gets an offset from a decelerating
    // table (rows start in quick succession and ease into the last ones), each badge tile its
    // row's offset + 40ms, and each talent avatar its row's + 80ms, 40ms apart. The CSS turns
    // --lr-t into a delay on the element itself. Empty rows (an action row with nothing in it)
    // are skipped rather than holding a slot. ListAnimationPicker.vue keeps a copy of this.
    const ROW_OFFSETS = [0, 50, 95, 135, 170, 200, 225, 245];

    function stampOffsets(card) {
        const stamped = [];
        const stamp = (node, ms) => {
            node.style.setProperty('--lr-t', ms + 'ms');
            stamped.push(node);
        };
        card.querySelectorAll('[data-reveal-body]').forEach(body => {
            let i = 0;
            Array.from(body.children).forEach(row => {
                if (!row.children.length && !row.textContent.trim()) return;
                const t = ROW_OFFSETS[Math.min(i++, ROW_OFFSETS.length - 1)];
                stamp(row, t);
                row.querySelectorAll('[data-reveal-date], [data-reveal-tile]').forEach(tile => stamp(tile, t + 40));
                row.querySelectorAll('[data-reveal-pop]').forEach(pop => {
                    Array.from(pop.children).forEach((child, j) => stamp(child, t + 80 + Math.min(j, 4) * 40));
                });
            });
        });
        return stamped;
    }

    function settle(el, state, delay) {
        pending.delete(el);
        if (observer) observer.unobserve(el);
        if (el._listRevealKey) revealedKeys.add(el._listRevealKey);
        if (state === 'instant') {
            el.setAttribute('data-list-revealed', 'instant');
            return;
        }
        // Written in the same frame as "in": no hidden-state rule reads them, so no reflow.
        const stamped = stampOffsets(el);
        el.style.setProperty('--reveal-delay', delay + 'ms');
        el.setAttribute('data-list-revealed', 'in');
        setTimeout(() => {
            el.setAttribute('data-list-revealed', 'done');
            el.style.removeProperty('--reveal-delay');
            stamped.forEach(node => node.style.removeProperty('--lr-t'));
        }, delay + 1500);
    }

    // A lazy flyer that has not arrived yet would make the card animate in empty and the poster
    // pop in afterwards. Give it a moment (never more than 350ms) before playing the batch.
    function whenImagesReady(els) {
        const waits = [];
        els.forEach(el => el.querySelectorAll('[data-reveal-media] img').forEach(img => {
            if (!img.complete) {
                waits.push(new Promise(resolve => {
                    img.addEventListener('load', resolve, { once: true });
                    img.addEventListener('error', resolve, { once: true });
                }));
            }
        }));
        if (!waits.length) return Promise.resolve();
        return Promise.race([Promise.all(waits), new Promise(resolve => setTimeout(resolve, 350))]);
    }

    function onIntersect(entries) {
        const batch = [];
        entries.forEach(entry => {
            if (!entry.isIntersecting || !pending.has(entry.target)) return;
            if (entry.boundingClientRect.top < 0) {
                settle(entry.target, 'instant');
            } else {
                batch.push(entry.target);
            }
        });

        // Anything still waiting ABOVE the viewport was jumped past (End, find-in-page, a restored
        // scroll). Show it as-is: animating it later, from the wrong direction, looks broken.
        // Only the list on screen is scanned (the others are display:none, or not in the page),
        // and every position is read before anything is written, so a long jump costs one layout.
        const rootShown = new Map();
        const passed = [...pending].filter(el => {
            const root = el._lrRoot;
            if (!rootShown.has(root)) rootShown.set(root, !!root && root.getClientRects().length > 0);
            return rootShown.get(root) && isRendered(el) && el.getBoundingClientRect().bottom <= 0;
        });
        passed.forEach(el => settle(el, 'instant'));

        if (!batch.length) return;
        batch.sort((a, b) => (a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING) ? -1 : 1);

        // Slide sends a card's text and image in from their own outer edges, and which side the
        // image is on differs between the lists. Measure every card first, then write, then
        // flush styles once, so the hidden state the transition starts from is already correct.
        // offsetLeft, not getBoundingClientRect(): the hidden state has already shifted the text
        // column, and a wrong guess makes the two columns overlap so neither test could pass.
        const sides = batch.map(el => {
            const body = el.querySelector('[data-reveal-body]');
            const media = el.querySelector('[data-reveal-media]');
            if (!body || !media || body.offsetParent !== media.offsetParent) return null;
            const bl = body.offsetLeft;
            const ml = media.offsetLeft;
            if (ml >= bl + body.offsetWidth - 1) return '1';
            if (ml + media.offsetWidth <= bl + 1) return '-1';
            return null;
        });
        batch.forEach((el, i) => {
            if (sides[i]) el.style.setProperty('--lr-side', sides[i]);
        });
        void batch[0].offsetWidth;

        // The first batch is the entrance someone opening a shared link sees, so it gets a
        // deliberate cascade. Later batches stay quick so fast scrolling never shows blank cards.
        const entrance = firstBatch;
        firstBatch = false;
        const delays = batch.map((el, i) => entrance
            ? 80 + Math.min(i, 5) * 90
            : (batch.length > 4 ? 0 : i * 70));

        whenImagesReady(batch).then(() => {
            batch.forEach((el, i) => {
                if (pending.has(el)) settle(el, 'in', delays[i]);
            });
        });
    }

    return {
        created(el, binding) {
            el.setAttribute('data-list-reveal', '');
            el._listRevealKey = keyFor(binding);
            // Deal tilts alternate cards opposite ways. A running counter rather than
            // :nth-child, which restarts at every date and counts the date header.
            el.style.setProperty('--deal-dir', (dealCounter++ % 2) ? '1' : '-1');
            if (revealedKeys.has(el._listRevealKey)) {
                el.setAttribute('data-list-revealed', 'done');
            }
        },
        mounted(el) {
            el._lrRoot = el.closest('[data-list-anim]');
            if (el.hasAttribute('data-list-revealed') || !el._lrRoot) return;
            if (!observer) {
                // Threshold 0 with a bottom inset rather than a ratio threshold: a card taller
                // than the viewport could never reach a ratio, and would stay hidden.
                observer = new IntersectionObserver(onIntersect, { threshold: 0, rootMargin: '0px 0px -8% 0px' });
            }
            pending.add(el);
            observer.observe(el);
        },
        unmounted(el) {
            pending.delete(el);
            if (observer) observer.unobserve(el);
        },
    };
})();

const calendarApp = createApp({
    // The month, its card and its day's panel: role/partials/month-script.
    mixins: [window.monthMixin],
    data() {
        return {
            selectedGroup: '{{ isset($selectedGroup) ? $selectedGroup->slug : "" }}',
            // JSON-encoded, not echoed inside quotes: HTML escaping leaves a backslash alone, so a
            // shared ?category=%5C would close the string early and take the whole calendar down.
            selectedCategory: @json(is_scalar($category ?? null) ? (string) $category : ''),
            allEvents: @json($eventsForVue),
            eventsMap: @json($eventsMapForVue),
            eventIdsInViewedMonth: [],
            groups: @json($groupsForVue),
            categories: @json(get_translated_categories($role ?? null)),
            startOfMonth: '{{ $startOfMonth->format('Y-m-d') }}',
            endOfMonth: '{{ $endOfMonth->format('Y-m-d') }}',
            use24Hour: {{ get_use_24_hour_time($role ?? null) ? 'true' : 'false' }},
            hidePastEvents: {{ (isset($hide_past_events) && $hide_past_events) ? 'true' : 'false' }},
            // How many upcoming rows the list draws. It was a fixed 200 with nothing after it;
            // "Show more" raises it (role/partials/list-more).
            listRowLimit: 200,
            // The server's row cap cut the payload: later events exist that the page does not hold.
            listTruncated: false,
            // The last load failed. Every empty state is behind this: "No scheduled events" is a
            // statement about the schedule, and used to be what a dropped connection produced.
            loadFailed: false,
            pastLoadFailed: false,
            subdomain: '{{ isset($subdomain) ? $subdomain : '' }}',
            guestBasePath: @json($guestBasePath),
            route: '{{ $route }}',
            tab: '{{ $tab ?? '' }}',
            embed: {{ isset($embed) && $embed ? 'true' : 'false' }},
            // The layout ?layout= asked for, or null. Read from the helper rather than $role
            // because this partial also renders on the home route, which has none. It is the
            // requested value, not currentView, so a link from here to an event carries what
            // the address asked for, and the event's way back returns to that view.
            layoutFromUrl: @json(requested_event_layout()),
            directRegistration: {{ isset($role) && $role->direct_registration ? 'true' : 'false' }},
            {{-- The dashboard is the person's own page, as the admin's are: its direction and its
                 language are theirs. Left to the schedule's (there is none there), the month was
                 forced left to right and wrote its dates in English for every other language. --}}
            isRtl: {{ ($isAdminRoute || $route === 'home') ? (auth()->check() && auth()->user()->isRtl() ? 'true' : 'false') : (isset($role) && $role->isRtl() ? 'true' : 'false') }},
            durationLabels: {
                h: @json(__('messages.duration_hour_short')),
                d: @json(__('messages.duration_day_short')),
                m: @json(__('messages.duration_minute_short')),
            },
            {{-- Also the language forwarded to the guest calendar endpoints, so their payload can no
                 longer disagree with the server-rendered chrome around it. --}}
            languageCode: '{{ ($isAdminRoute || $route === 'home') && auth()->check() ? app()->getLocale() : (isset($role) ? $role->displayLanguageCode() : 'en') }}',
            {{-- The zone the past-event filters resolve "today" in. Must be the same one $today
                 above uses: these compare against occurrenceDate, which is the SCHEDULE's calendar
                 date, so a viewer-anchored today hides an event that is still running. --}}
            userTimezone: '{{ $calendarTimezone }}',
            dayWords: { today: @json(__('messages.today')), tomorrow: @json(__('messages.tomorrow')) },
            leadFilterKeyAtLoad: null,
            // The phone's month: the day that was picked (the month is folded to a line while
            // one is), and whether the days of this month that are over are shown.
            phoneDay: '',
            phoneShowPast: false,
            // A guest page's month on a phone (not the embed, which keeps the agenda there).
            phoneMonth: @json($guestRows && ! $guestEmbed),
            // What the list was left as, when this page is come back to (see the watcher).
            listToRestore: null,
            showFiltersDrawer: false,
            showDesktopFiltersModal: false,
            showOnlineOnly: false,
            selectedVenue: '',
            showFreeOnly: false,
            currentView: '{{ $eventLayout ?? "calendar" }}',
            pastEvents: @json($pastEventsForVue ?? []),
            hasMorePastEvents: {{ isset($hasMorePastEvents) && $hasMorePastEvents ? 'true' : 'false' }},
            loadingPastEvents: false,
            showPastEvents: false,
            isAuthenticated: {{ auth()->check() ? 'true' : 'false' }},
            // A signed-out visitor submitting fan content has to supply a name, an email and
            // (on hosted) a Turnstile challenge. Those live on the event page, whose forms are
            // in the DOM from the start; the ones here are v-if'd in on demand, which Turnstile
            // cannot auto-render into. So send guests to the event page instead of opening a
            // form that could never pass validation.
            fanContentGuestRedirect: {{ (! auth()->check() && isset($role) && $role && ! $role->fan_content_require_account) ? 'true' : 'false' }},
            openVideoForm: {},
            playingVideo: null,
            openCommentForm: {},
            openPhotoForm: {},
            accentColor: '{{ $accentColor ?? "#4E81FA" }}',
            // 'none' also whenever the visitor asks for less motion, on the device or with the
            // accessibility widget (listRevealMotionOk). The CSS forces cards visible either way.
            activeListAnimation: listRevealMotionOk ? @json($listAnimation) : 'none',
            votingPoll: {},
            pollAnimating: {},
            isLoadingEvents: {{ request()->graphic ? 'false' : 'true' }},
            uniqueCategoryIds: @json($uniqueCategoryIds ?? []),
            filterCustomFields: @json($filterCustomFields),
            searchableCustomFields: @json($searchableCustomFields),
            // Normalized keys (normKey()); the raw spellings they came from label an option that
            // no event in scope uses, so a shared link to an empty room still reads "Room A".
            selectedCustomFields: @json($initialCustomFilters),
            initialCustomFilterLabels: @json($initialCustomFilterLabels),
            searchInput: '',
            searchQuery: '',
            searchDebounceTimer: null,
            // Below md there is no month grid: a guest page shows cards or rows by it, and the
            // calendar layout is a small month there (the flat agenda on the admin, the
            // dashboard and in an embed; see filterScopeIsMonth). Kept live by a matchMedia
            // listener in mounted().
            isNarrow: !!(window.matchMedia && window.matchMedia('(max-width: 767.98px)').matches),
            restoringFiltersFromUrl: false,
            filterShareBaseUrl: @json($filterShareBaseUrl),
            filterParamMaxLength: {{ \App\Utils\CustomFieldUtils::FILTER_PARAM_MAX_LENGTH }},
            filterChipLabels: @json($filterChipLabels),
            eventsLabel: @json($label('events')),
            linkCopied: false,
            linkCopiedTimer: null,
            filterPanelOpener: null,
            pageMonth: {{ $month }},
            pageYear: {{ $year }},
            listDataLoaded: false,
            firstDayOfWeek: {{ $firstDay }},
            todayDate: '{{ $today->format('Y-m-d') }}'
        }
    },
    computed: {
        calendarDays() {
            const year = this.pageYear;
            const month = this.pageMonth;
            const firstDay = this.firstDayOfWeek;
            const lastDay = (firstDay + 6) % 7;

            // First day of the month
            const firstOfMonth = new Date(year, month - 1, 1);
            // Last day of the month
            const lastOfMonth = new Date(year, month, 0);

            // Start of calendar grid: go back to the start of the week containing the 1st
            const start = new Date(firstOfMonth);
            while (start.getDay() !== firstDay) {
                start.setDate(start.getDate() - 1);
            }

            // End of calendar grid: go forward to the end of the week containing the last day
            const end = new Date(lastOfMonth);
            while (end.getDay() !== lastDay) {
                end.setDate(end.getDate() + 1);
            }

            const days = [];
            const current = new Date(start);
            while (current <= end) {
                const y = current.getFullYear();
                const m = String(current.getMonth() + 1).padStart(2, '0');
                const d = String(current.getDate()).padStart(2, '0');
                const dateStr = `${y}-${m}-${d}`;
                days.push({
                    date: dateStr,
                    day: current.getDate(),
                    isCurrentMonth: current.getMonth() + 1 === month && current.getFullYear() === year,
                    isToday: dateStr === this.todayDate
                });
                current.setDate(current.getDate() + 1);
            }
            return days;
        },
        monthYearLabel() {
            const date = new Date(this.pageYear, this.pageMonth - 1, 1);
            return date.toLocaleDateString(this.languageCode, { month: 'long', year: 'numeric' });
        },
        monthYearDatetime() {
            return String(this.pageYear).padStart(4, '0') + '-' + String(this.pageMonth).padStart(2, '0');
        },
        hasDesktopFilters() {
            return this.groups.length > 1 || this.availableCategories.length > 1 || this.hasOnlineEvents || this.uniqueVenues.length > 1 || this.hasFreeEvents || this.filterCustomFields.length > 0;
        },
        dynamicFilterCount() {
            let count = 0;
            if (this.groups.length > 1) count++;
            if (this.availableCategories.length > 1) count++;
            if (this.hasOnlineEvents) count++;
            if (this.uniqueVenues.length > 1) count++;
            if (this.hasFreeEvents) count++;
            this.filterCustomFields.forEach(field => {
                if ((this.availableCustomFieldOptions[field.key] || []).length > 1) count++;
            });
            return count;
        },
        activeFilterCount() {
            let count = 0;
            if (this.selectedGroup) count++;
            if (this.selectedCategory) count++;
            if (this.showOnlineOnly) count++;
            if (this.selectedVenue) count++;
            if (this.showFreeOnly) count++;
            Object.values(this.selectedCustomFields).forEach(v => { if (v) count++; });
            if (this.isSearching) count++;
            return count;
        },
        // Everything the visitor can choose that changes which events the list shows.
        leadFilterKey() {
            return [this.selectedGroup, this.selectedCategory, this.showOnlineOnly, this.selectedVenue, this.showFreeOnly,
                this.isSearching, JSON.stringify(this.selectedCustomFields)].join('|');
        },
        // The filters that narrow the page the visitor is on. A sub-schedule is left out: it is
        // the page itself (/schedule/kids), so on its own it gets no chips row and keeps the
        // plain "No scheduled events" message. The hero badge still counts it, as before.
        narrowingFilterCount() {
            return this.activeFilterCount - (this.selectedGroup ? 1 : 0);
        },
        selectedGroupObj() {
            if (!this.selectedGroup) return null;
            return this.groups.find(g => g.slug === this.selectedGroup) || null;
        },
        selectedGroupName() {
            if (!this.selectedGroup) return '';
            const group = this.groups.find(g => g.slug === this.selectedGroup);
            return group ? group.name : this.selectedGroup;
        },
        selectedCategoryName() {
            if (!this.selectedCategory) return '';
            const cat = this.availableCategories.find(c => c.id == this.selectedCategory);
            return cat ? cat.name : '';
        },
        // The search box, folded the way the event text is (see searchFold()) and split into
        // words. Every word has to appear somewhere in an event for it to match.
        searchTokens() {
            return this.searchFold(this.searchQuery).split(/\s+/).filter(Boolean);
        },
        isSearching() {
            return this.searchTokens.length > 0;
        },
        // Each loaded event's searchable text, folded once per payload rather than per keystroke.
        // Depends on isSearching (a boolean) rather than the query, so typing does not rebuild it,
        // and nothing is built at all until someone searches.
        searchHaystacks() {
            const map = {};
            if (!this.isSearching) return map;
            // Upcoming only: any active filter, search included, hides past events
            // (flatPastEvents), so indexing them would be work nobody sees.
            this.allEvents.forEach(event => {
                if (event && !(event.id in map)) {
                    map[event.id] = this.buildSearchHaystack(event);
                }
            });
            return map;
        },
        // Whether the filters look at the one month being shown (true), or at every loaded
        // upcoming event: the list layout, and wherever the calendar layout below md is the
        // flat agenda reaching six months ahead (the admin, the dashboard, a narrow embed).
        // Offering that agenda only this month's rooms would hide a room that is in use next
        // month from the very list it filters.
        filterScopeIsMonth() {
            // A guest page's phone month is a month too (phoneMonth): its chips, counts and
            // "N events" are about the month it draws, as the laptop grid's are.
            return this.currentView === 'calendar' && (!this.isNarrow || this.phoneMonth);
        },
        eventCountByGroup() {
            // Filter by other active filters (except sub-schedule and category)
            const baseEvents = this.eventsForFilters.filter(e => this.passesFilters(e, { group: true, category: true }));

            const counts = { '': baseEvents.length };
            this.groups.forEach(g => {
                counts[g.slug] = baseEvents.filter(e => e.group_id === g.id).length;
            });
            return counts;
        },
        eventCountByCategory() {
            const filteredEvents = this.eventsForFilters.filter(e => this.passesFilters(e, { category: true }));
            const counts = { '': filteredEvents.length };
            this.availableCategories.forEach(c => {
                counts[c.id] = filteredEvents.filter(e => e.category_id == c.id).length;
            });
            return counts;
        },
        eventsForFilters() {
            if (this.currentView === 'list' || !this.filterScopeIsMonth) {
                return this.futureEvents;
            }
            return this.allEvents.filter(e => this.eventIdsInViewedMonth.includes(e.id));
        },
        futureEvents() {
            return this.allEvents.filter(event => {
                if (event.days_of_week && event.days_of_week.length > 0) return true;
                return !this.isEventPast(event);
            });
        },
        filteredEvents() {
            return this.allEvents.filter(event => this.passesFilters(event));
        },
        filteredEventsForView() {
            return this.eventsForFilters.filter(event => this.passesFilters(event));
        },
        // How many events on the viewed month's grid survive the filters. Drives the desktop
        // grid's "nothing matches" notice, which is about that grid whatever eventsForFilters is.
        monthMatchCount() {
            return this.allEvents.filter(e => this.eventIdsInViewedMonth.includes(e.id) && this.passesFilters(e)).length;
        },
        phoneMonthTitle() {
            return new Date(this.pageYear, this.pageMonth - 1, 1).toLocaleDateString(this.languageCode, { month: 'long', year: 'numeric' });
        },
        // The phone's month. Seven narrow weekday names from the schedule's first day.
        phoneWeekdays() {
            return [0, 1, 2, 3, 4, 5, 6].map(step => new Date(2023, 0, 1 + ((this.firstDayOfWeek + step) % 7))
                .toLocaleDateString(this.languageCode, { weekday: 'narrow' }));
        },
        // The month being shown, by day, read as the laptop's grid reads it: the server's map
        // of the month (getEventsForDate), which has the days that are over, any month paged
        // to and a series for as long as it runs. Each occurrence is a row of its own day.
        phoneMonthByDay() {
            if (!this.phoneMonth) { return {}; }
            const prefix = this.pageYear + '-' + String(this.pageMonth).padStart(2, '0') + '-';
            const today = this.scheduleDay(0);
            const days = {};
            Object.keys(this.eventsMap || {}).filter(date => date.startsWith(prefix)).forEach(date => {
                const rows = this.getEventsForDate(date).map(event => {
                    const row = { ...event, occurrenceDate: date, uniqueKey: event.id + '-' + date, _isPast: date < today };
                    // A one-time event over several days is listed on each of them: say which
                    // day this is, and keep the day its tickets are sold under (rowDate).
                    if (event.is_multi_day && event.local_date && event.local_end_date && !(event.days_of_week && event.days_of_week.length)) {
                        const day = 86400000, at = text => { const [y, m, d] = text.split('-').map(Number); return Date.UTC(y, m - 1, d); };
                        row._originalOccurrenceDate = event.local_date;
                        row._multiDayNum = Math.round((at(date) - at(event.local_date)) / day) + 1;
                        row._multiDayTotal = Math.round((at(event.local_end_date) - at(event.local_date)) / day) + 1;
                    }
                    return row;
                });
                if (rows.length) { days[date] = rows; }
            });
            return days;
        },
        phoneMonthCells() {
            const year = this.pageYear, month = this.pageMonth;
            const lead = (new Date(year, month - 1, 1).getDay() - this.firstDayOfWeek + 7) % 7;
            const count = new Date(year, month, 0).getDate();
            const today = this.scheduleDay(0);
            const cells = [];
            for (let gap = 0; gap < lead; gap++) cells.push({ key: 'gap-' + gap });
            for (let day = 1; day <= count; day++) {
                const date = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                cells.push({ key: date, date, day, count: (this.phoneMonthByDay[date] || []).length, today: date === today, past: date < today });
            }
            return cells;
        },
        // Whether the month has days that are over AND days that are not: only then is there
        // something to put away. A month that is wholly past is simply shown.
        phoneHasEarlierDays() {
            const today = this.scheduleDay(0);
            const dates = Object.keys(this.phoneMonthByDay);
            return dates.some(date => date < today) && dates.some(date => date >= today);
        },
        phoneMonthGroups() {
            const today = this.scheduleDay(0);
            const all = this.phoneShowPast || !this.phoneHasEarlierDays;
            return Object.keys(this.phoneMonthByDay).sort()
                .filter(date => all || date >= today)
                .map(date => ({ date, events: this.phoneMonthByDay[date], past: date < today }));
        },
        // What stands under the phone's month: the month's days. In an embed too narrow for a
        // grid there is no month, and it is every upcoming day, as it always was there.
        phoneGroups() {
            if (this.phoneMonth) { return this.phoneMonthGroups; }
            return this.eventsGroupedByDate
                .map(group => ({ date: group.date, past: false, events: group.events.filter(event => this.isEventVisible(event)) }))
                .filter(group => group.events.length);
        },
        // The chips above the guest list: sub-schedules where the schedule has them, its
        // categories otherwise (the same two lists the Filters window offers).
        quickChipKind() {
            if (this.groups && this.groups.length > 1) return 'group';
            return this.availableCategories.length > 1 ? 'category' : '';
        },
        quickChips() {
            if (this.quickChipKind === 'group') {
                return this.groups.map(group => ({ value: String(group.slug), name: group.name, color: group.color || null }));
            }
            if (this.quickChipKind === 'category') {
                // A category's colour is carried by its events (category_color), as its name is.
                return this.availableCategories.map(category => ({
                    value: String(category.id),
                    name: category.name,
                    color: (this.eventsForFilters.find(event => String(event.category_id) === String(category.id)) || {}).category_color || null,
                }));
            }
            return [];
        },
        quickChipValue() {
            return String((this.quickChipKind === 'group' ? this.selectedGroup : this.selectedCategory) || '');
        },
        availableCategories() {
            // Get events filtered only by group (not by category) to show all available categories
            const groupFilteredEvents = this.eventsForFilters.filter(event => {
                if (this.selectedGroupObj && event.group_id !== this.selectedGroupObj.id) {
                    return false;
                }
                return true;
            });

            // Build {id → name}, preferring the per-event resolved category_name (handles
            // cross-schedule foreign categories not in the viewing schedule's own list).
            const nameById = {};
            groupFilteredEvents.forEach(event => {
                if (!event.category_id) return;
                if (!nameById[event.category_id]) {
                    nameById[event.category_id] = event.category_name || this.categories[event.category_id] || `Category ${event.category_id}`;
                }
            });

            return Object.entries(nameById)
                .map(([id, name]) => ({ id: parseInt(id), name }))
                .sort((a, b) => a.name.localeCompare(b.name));
        },
        selectedCategoryColor() {
            if (!this.selectedCategory) return null;
            const ev = this.eventsForFilters.find(e => e.category_id == this.selectedCategory && e.category_color);
            return ev ? ev.category_color : null;
        },
        uniqueVenues() {
            const venuesMap = new Map();
            this.eventsForFilters.forEach(event => {
                if (event.venue_subdomain && event.venue_name) {
                    venuesMap.set(event.venue_subdomain, event.venue_name);
                }
            });
            return Array.from(venuesMap.entries())
                .map(([subdomain, name]) => ({ subdomain, name }))
                .sort((a, b) => a.name.localeCompare(b.name));
        },
        hasFreeEvents() {
            let hasFree = false, hasPaid = false;
            for (const event of this.eventsForFilters) {
                if (event.is_free) hasFree = true;
                else hasPaid = true;
                if (hasFree && hasPaid) return true;
            }
            return false;
        },
        hasOnlineEvents() {
            return this.eventsForFilters.some(e => e.is_online);
        },
        eventCountByVenue() {
            const baseEvents = this.eventsForFilters.filter(e => this.passesFilters(e, { venue: true }));

            const counts = { '': baseEvents.length };
            this.uniqueVenues.forEach(v => {
                counts[v.subdomain] = baseEvents.filter(e => e.venue_subdomain === v.subdomain).length;
            });
            return counts;
        },
        // The options each filter field offers: [{ key, value, label }]. `key` is the normalized
        // form every comparison uses (normKey()), `value` the spelling the URL carries, and
        // `label` what the guest reads. An option list keeps the order the owner defined and only
        // offers options some event in scope uses. A text field offers each distinct value, with
        // "Room A", "room a" and "Room  A " folded into one, labelled with the most common
        // spelling and sorted naturally (Room 2 before Room 10). The selected option is always
        // offered, even when nothing in scope uses it, so an active filter stays visible and
        // clearable - a shared ?custom_1= link can name a room that is empty this month.
        availableCustomFieldOptions() {
            const result = {};
            this.filterCustomFields.forEach(field => {
                const optionsMap = field.optionsMap || {};
                const selected = this.selectedCustomFields[field.key] || '';

                if (field.type !== 'string') {
                    const inUse = new Set();
                    this.eventsForFilters.forEach(e => this.customFieldValuesOf(e, field).forEach(k => inUse.add(k)));
                    const options = [];
                    (field.options || []).forEach(option => {
                        const key = this.normKey(option);
                        if (key && (inUse.has(key) || key === selected) && !options.some(o => o.key === key)) {
                            options.push({ key, value: option, label: optionsMap[option] || option });
                        }
                    });
                    result[field.key] = options;
                    return;
                }

                const spellings = {};
                this.eventsForFilters.forEach(e => {
                    const raw = (e.custom_field_values || {})[field.key];
                    if (raw === null || raw === undefined) return;
                    const value = String(raw).trim().replace(/\s+/g, ' ');
                    const key = this.normKey(value);
                    if (!key) return;
                    if (!spellings[key]) spellings[key] = {};
                    spellings[key][value] = (spellings[key][value] || 0) + 1;
                });

                const options = Object.entries(spellings).map(([key, counts]) => {
                    let best = '';
                    let bestCount = 0;
                    Object.entries(counts).forEach(([spelling, count]) => {
                        if (count > bestCount) {
                            best = spelling;
                            bestCount = count;
                        }
                    });
                    return { key, value: best, label: best };
                });

                if (selected && !spellings[selected]) {
                    const initial = String((this.initialCustomFilterLabels || {})[field.key] || '');
                    const label = this.normKey(initial) === selected ? initial.trim().replace(/\s+/g, ' ') : selected;
                    options.push({ key: selected, value: label, label });
                }

                result[field.key] = options.sort((a, b) => a.label.localeCompare(b.label, undefined, { numeric: true, sensitivity: 'base' }));
            });
            return result;
        },
        eventCountByCustomField() {
            const result = {};
            this.filterCustomFields.forEach(field => {
                const baseEvents = this.eventsForFilters.filter(e => this.passesFilters(e, { customField: field.key }));
                const counts = { '': baseEvents.length };
                (this.availableCustomFieldOptions[field.key] || []).forEach(option => {
                    counts[option.key] = baseEvents.filter(e => this.customFieldValuesOf(e, field).includes(option.key)).length;
                });
                result[field.key] = counts;
            });
            return result;
        },
        // One chip per active filter, for the row above the calendar: what is narrowing the view
        // and a way to drop each piece. A visitor arriving from a shared "Room A" link or a QR
        // code on a door otherwise sees a partial schedule with no explanation.
        activeFilterChips() {
            const labels = this.filterChipLabels || {};
            const chips = [];
            if (this.selectedGroup) {
                chips.push({ id: 'group', text: (labels.schedule ? labels.schedule + ': ' : '') + this.selectedGroupName, clear: () => { this.selectedGroup = ''; } });
            }
            if (this.selectedCategory) {
                const name = this.selectedCategoryName || this.categories[this.selectedCategory] || '';
                chips.push({ id: 'category', text: (labels.category ? labels.category + ': ' : '') + name, clear: () => { this.selectedCategory = ''; } });
            }
            if (this.selectedVenue) {
                const venue = this.uniqueVenues.find(v => v.subdomain === this.selectedVenue);
                chips.push({ id: 'venue', text: (labels.venue ? labels.venue + ': ' : '') + (venue ? venue.name : this.selectedVenue), clear: () => { this.selectedVenue = ''; } });
            }
            this.filterCustomFields.forEach(field => {
                const selected = this.selectedCustomFields[field.key];
                if (!selected) return;
                const option = (this.availableCustomFieldOptions[field.key] || []).find(o => o.key === selected);
                chips.push({
                    id: 'cf-' + field.key,
                    text: field.name + ': ' + (option ? option.label : selected),
                    clear: () => { this.selectedCustomFields = { ...this.selectedCustomFields, [field.key]: '' }; },
                });
            });
            if (this.isSearching) {
                chips.push({ id: 'search', text: '"' + this.searchQuery.trim() + '"', clear: () => { this.clearSearch(); } });
            }
            if (this.showFreeOnly) {
                chips.push({ id: 'free', text: labels.free_entry || '', clear: () => { this.showFreeOnly = false; } });
            }
            if (this.showOnlineOnly) {
                chips.push({ id: 'online', text: labels.online || '', clear: () => { this.showOnlineOnly = false; } });
            }
            return chips;
        },
        // The count shown beside the filters, with the month when the filters only look at one.
        filteredCountLabel() {
            const count = this.filteredEventsForView.length;
            return this.filterScopeIsMonth ? count + ' ' + this.eventsLabel + ' · ' + this.monthYearLabel : count + ' ' + this.eventsLabel;
        },
        // A category or custom field filter someone could hand to another visitor. Sub-schedule
        // alone is not one: its own page URL already is that link.
        hasShareableFilter() {
            return !!this.selectedCategory || this.filterCustomFields.some(f => f.index && this.selectedCustomFields[f.key]);
        },
        // The link "Copy link" copies: the schedule page, the sub-schedule path, and the category
        // and custom_N params - nothing else, so no month, language or layout rides along.
        shareableFilterUrl() {
            const base = this.route === 'admin'
                ? this.filterShareBaseUrl
                : (window.location.origin + this.guestBasePath);
            if (!base) return '';
            const url = base + (this.selectedGroup ? '/' + encodeURIComponent(this.selectedGroup) : '');
            const params = new URLSearchParams();
            if (this.selectedCategory) params.set('category', this.selectedCategory);
            this.customFieldUrlParams().forEach(([name, value]) => params.set(name, value));
            const query = params.toString();
            return query ? url + '?' + query : url;
        },
        // Every upcoming occurrence the loaded payload can project, UNSLICED. Split out of
        // mobileEventsList so "Show more" can tell whether the cut hid anything, and so the
        // phone's month can count a day past it: a computed that has already sliced cannot.
        allMobileOccurrences() {
            // Create a mobile-friendly events list that includes all upcoming occurrences
            const mobileEvents = [];
            
            // Get today's date
            let today = new Date();
            if (this.userTimezone) {
                const userNow = new Date().toLocaleString("en-US", {timeZone: this.userTimezone});
                today = new Date(userNow);
            }
            today.setHours(0, 0, 0, 0);
            
            // Calculate upcoming dates for the next 6 months
            const endDate = new Date(today);
            endDate.setMonth(endDate.getMonth() + 6);
            
            // Helper function to check if a date should be included based on recurring end settings
            const shouldIncludeDate = (event, dateStr) => {
                if (!event.days_of_week || event.days_of_week.length === 0) {
                    return true; // Not a recurring event
                }
                
                const recurringEndType = event.recurring_end_type || 'never';
                
                if (recurringEndType === 'never') {
                    return true;
                }
                
                if (recurringEndType === 'on_date' && event.recurring_end_value) {
                    const endDate = new Date(event.recurring_end_value + 'T00:00:00');
                    const checkDate = new Date(dateStr + 'T00:00:00');
                    return checkDate <= endDate;
                }
                
                if (recurringEndType === 'after_events' && event.recurring_end_value && event.start_date) {
                    const maxOccurrences = parseInt(event.recurring_end_value);
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    const checkDate = new Date(dateStr + 'T00:00:00');

                    const occurrenceCount = this.countOccurrencesForFrequency(event, startDate, checkDate);

                    return occurrenceCount <= maxOccurrences;
                }
                
                return true;
            };
            
            // Process all filtered events
            this.filteredEvents.forEach(event => {
                if (event.days_of_week && event.days_of_week.length > 0) {
                    // Recurring event - generate all occurrences
                    // For multi-day recurring events, look back to catch still-running occurrences
                    const lookBackDays = (event.is_multi_day && event.duration) ? Math.ceil(event.duration / 24) - 1 : 0;
                    const currentDate = new Date(today);
                    if (lookBackDays > 0) {
                        currentDate.setDate(currentDate.getDate() - lookBackDays);
                    }
                    if (event.start_date) {
                        const eventStartDate = new Date(event.start_date + 'T00:00:00');
                        if (currentDate < eventStartDate) {
                            currentDate.setTime(eventStartDate.getTime());
                        }
                    }

                    while (currentDate <= endDate) {
                        if (this.matchesFrequency(event, currentDate)) {
                            const dateStr = currentDate.getFullYear() + '-' +
                                          String(currentDate.getMonth() + 1).padStart(2, '0') + '-' +
                                          String(currentDate.getDate()).padStart(2, '0');

                            // For past occurrences, only include if multi-day and still running
                            const isPastOccurrence = currentDate < today;
                            if (isPastOccurrence) {
                                if (event.is_multi_day && event.duration) {
                                    const occEnd = new Date(currentDate);
                                    occEnd.setHours(occEnd.getHours() + event.duration);
                                    if (occEnd < today) {
                                        currentDate.setDate(currentDate.getDate() + 1);
                                        continue;
                                    }
                                } else {
                                    currentDate.setDate(currentDate.getDate() + 1);
                                    continue;
                                }
                            }

                            // Check if this date should be included based on recurring end settings
                            if (shouldIncludeDate(event, dateStr)) {
                                if (event.is_multi_day && event.duration) {
                                    const totalDays = Math.ceil(event.duration / 24);
                                    const displayDate = (currentDate < today) ? today.getFullYear() + '-' +
                                        String(today.getMonth() + 1).padStart(2, '0') + '-' +
                                        String(today.getDate()).padStart(2, '0') : dateStr;
                                    mobileEvents.push({
                                        ...event,
                                        occurrenceDate: displayDate,
                                        uniqueKey: `${event.id}-${dateStr}`,
                                        _originalOccurrenceDate: dateStr,
                                        _multiDayNum: 1,
                                        _multiDayTotal: totalDays,
                                    });
                                } else {
                                    mobileEvents.push({
                                        ...event,
                                        occurrenceDate: dateStr,
                                        uniqueKey: `${event.id}-${dateStr}`
                                    });
                                }
                            }
                        }

                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                } else if (event.starts_at || event.local_date) {
                    // One-time event
                    const eventDate = event.local_date || event.utc_date;
                    if (eventDate) {
                        const [year, month, day] = eventDate.split('-').map(Number);
                        const startDate = new Date(year, month - 1, day);
                        startDate.setHours(0, 0, 0, 0);

                        if (event.is_multi_day && event.local_end_date) {
                            const [ey, em, ed] = event.local_end_date.split('-').map(Number);
                            const endCheck = new Date(ey, em - 1, ed);
                            endCheck.setHours(0, 0, 0, 0);
                            if (endCheck >= today) {
                                const totalDays = Math.round((endCheck - startDate) / (1000 * 60 * 60 * 24)) + 1;
                                const displayDate = (startDate < today) ?
                                    today.getFullYear() + '-' +
                                    String(today.getMonth() + 1).padStart(2, '0') + '-' +
                                    String(today.getDate()).padStart(2, '0') : eventDate;
                                mobileEvents.push({
                                    ...event,
                                    occurrenceDate: displayDate,
                                    uniqueKey: event.id,
                                    _originalOccurrenceDate: eventDate,
                                    _multiDayNum: 1,
                                    _multiDayTotal: totalDays,
                                });
                            }
                        } else if (startDate >= today) {
                            mobileEvents.push({
                                ...event,
                                occurrenceDate: eventDate,
                                uniqueKey: event.id
                            });
                        }
                    }
                }
            });
            
            // Sort by date, then by time
            return mobileEvents.sort((a, b) => {
                const dateComparison = a.occurrenceDate.localeCompare(b.occurrenceDate);
                if (dateComparison !== 0) return dateComparison;

                return this.compareSameDay(a, b, a.occurrenceDate);
            });
        },
        // How many rows the list draws: the number "Show more" raises.
        listRowCap() {
            return this.listRowLimit;
        },
        mobileEventsList() {
            return this.allMobileOccurrences.slice(0, this.listRowCap);
        },
        // Whether "Show more" has anything to show: a visible occurrence past the cut. The list
        // drawn is by construction a PREFIX of allMobileOccurrences, so "more visible than
        // shown" is "any visible occurrence past the cut", which stops at the first one instead
        // of walking every occurrence twice. Visibility matters because the filters gate each
        // row, and a plain count would offer more when everything past the cut is filtered out.
        hasMoreListRows() {
            return this.allMobileOccurrences.slice(this.listRowLimit).some(e => this.isEventVisible(e));
        },
        // Where the past rows begin in allListGroups (-1: none are drawn). "Show more" belongs at
        // the end of the upcoming rows, which is above the past ones.
        firstPastGroupIndex() {
            return this.allListGroups.findIndex(group => group.events.every(e => e._isPast));
        },
        eventsGroupedByDate() {
            const grouped = {};
            this.mobileEventsList.forEach(event => {
                const date = event.occurrenceDate;
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(event);
            });
            return Object.keys(grouped).sort().map(date => ({
                date: date,
                events: grouped[date]
            }));
        },
        listViewUpcomingGroups() {
            // Filter eventsGroupedByDate to only non-past dates
            return this.eventsGroupedByDate.filter(group => !this.isPastEvent(group.date));
        },
        filteredPastEventsCount() {
            return this.flatPastEvents.length;
        },
        flatUpcomingEvents() {
            const events = [];
            this.eventsGroupedByDate.forEach(group => {
                if (!this.isPastEvent(group.date)) {
                    group.events.forEach(event => {
                        if (this.isEventVisible(event)) {
                            events.push(event);
                        }
                    });
                }
            });
            return events.slice(0, this.listRowCap);
        },
        flatPastEvents() {
            if (this.activeFilterCount > 0) return [];

            let today = new Date();
            if (this.userTimezone) {
                const userNow = new Date().toLocaleString("en-US", {timeZone: this.userTimezone});
                today = new Date(userNow);
            }
            today.setHours(0, 0, 0, 0);

            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            // Start with server-provided past events (one-time), expanding multi-day
            const events = [];
            this.pastEvents.forEach(event => {
                if (!this.isEventVisible(event) || !event.occurrenceDate || !this.isEventPast(event)) return;
                if (event.is_multi_day && event.local_end_date) {
                    const [sy, sm, sd] = event.occurrenceDate.split('-').map(Number);
                    const evStartDate = new Date(sy, sm - 1, sd);
                    evStartDate.setHours(0, 0, 0, 0);
                    const [ey, em, ed] = event.local_end_date.split('-').map(Number);
                    const endCheck = new Date(ey, em - 1, ed);
                    endCheck.setHours(0, 0, 0, 0);
                    const totalDays = Math.round((endCheck - evStartDate) / (1000 * 60 * 60 * 24)) + 1;
                    events.push({
                        ...event,
                        occurrenceDate: event.occurrenceDate,
                        uniqueKey: `${event.id}-past-${event.occurrenceDate}`,
                        _originalOccurrenceDate: event.occurrenceDate,
                        _multiDayNum: 1,
                        _multiDayTotal: totalDays,
                    });
                } else {
                    events.push(event);
                }
            });

            // Also generate past occurrences from recurring events
            this.filteredEvents.forEach(event => {
                if (event.days_of_week && event.days_of_week.length > 0 && event.start_date) {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    // Go back max 90 days from today, but not before the event's start_date
                    const ninetyDaysAgo = new Date(today);
                    ninetyDaysAgo.setDate(ninetyDaysAgo.getDate() - 90);
                    const rangeStart = new Date(Math.max(startDate.getTime(), ninetyDaysAgo.getTime()));

                    const currentDate = new Date(rangeStart);

                    while (currentDate <= yesterday) {
                        if (this.matchesFrequency(event, currentDate)) {
                            const dateStr = currentDate.getFullYear() + '-' +
                                String(currentDate.getMonth() + 1).padStart(2, '0') + '-' +
                                String(currentDate.getDate()).padStart(2, '0');

                            // Check recurring end conditions
                            if (this.shouldIncludePastDate(event, dateStr)) {
                                if (event.is_multi_day && event.duration) {
                                    const totalDays = Math.ceil(event.duration / 24);
                                    events.push({
                                        ...event,
                                        occurrenceDate: dateStr,
                                        uniqueKey: `${event.id}-past-${dateStr}`,
                                        _originalOccurrenceDate: dateStr,
                                        _multiDayNum: 1,
                                        _multiDayTotal: totalDays,
                                    });
                                } else {
                                    events.push({
                                        ...event,
                                        occurrenceDate: dateStr,
                                        uniqueKey: `${event.id}-past-${dateStr}`
                                    });
                                }
                            }
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                }
            });

            // Sort reverse-chronologically, then by time within same date
            return events.sort((a, b) => {
                const dateComparison = (b.occurrenceDate || '').localeCompare(a.occurrenceDate || '');
                if (dateComparison !== 0) return dateComparison;
                return this.compareSameDay(a, b, a.occurrenceDate || '');
            });
        },
        pastEventsGroupedByDate() {
            // Group past events by date, sorted reverse-chronologically
            const grouped = {};
            this.flatPastEvents.forEach(event => {
                if (!event.occurrenceDate) return;
                const date = event.occurrenceDate;
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(event);
            });
            return Object.keys(grouped).sort().reverse().map(date => ({
                date: date,
                events: grouped[date]
            }));
        },
        allListGroups() {
            const groups = {};
            this.flatUpcomingEvents.forEach(event => {
                const date = event.occurrenceDate || 'no-date';
                if (!groups[date]) groups[date] = {date, events: [], hasPast: false};
                groups[date].events.push({...event, _isPast: false});
            });
            if (!this.hidePastEvents) {
                this.flatPastEvents.forEach(event => {
                    const date = event.occurrenceDate || 'no-date';
                    if (!groups[date]) groups[date] = {date, events: [], hasPast: true};
                    groups[date].events.push({...event, _isPast: true});
                    groups[date].hasPast = true;
                });
            }
            return Object.values(groups).sort((a, b) => {
                // upcoming dates first (ascending), then past dates (descending)
                const aIsPast = a.events.every(e => e._isPast);
                const bIsPast = b.events.every(e => e._isPast);
                if (aIsPast !== bIsPast) return aIsPast ? 1 : -1;
                if (aIsPast) return b.date.localeCompare(a.date);
                return a.date.localeCompare(b.date);
            });
        }
    },
    watch: {
        // Back from an event: the list is as it was left. Its rows are fetched after the page
        // has loaded, so the browser's own return to where the visitor had scrolled finds a
        // page too short to scroll and stays at the top; and how far "Show more" had reached
        // and the day picked on a phone's month were simply forgotten. What was left is
        // noted as the page is put away (rememberList(), on pagehide) and put back here, once,
        // when the rows are in.
        isLoadingEvents(loading) {
            if (loading || !this.listToRestore) { return; }
            const left = this.listToRestore;
            this.listToRestore = null;
            this.listRowLimit = Math.max(this.listRowLimit, left.limit || 0);
            this.phoneShowPast = !!left.past;
            this.phoneDay = left.day || '';
            this.$nextTick(() => requestAnimationFrame(() => window.scrollTo(0, left.y || 0)));
        },
        // The next-event card above the month was chosen by the server for the page as it was
        // asked for (its sub-schedule and category included; an address that filters by anything
        // else gets no lead at all, see role/show-guest). Once the visitor changes what the
        // list shows it may not be among it, so it steps aside until the list is back as it
        // loaded: leadFilterKeyAtLoad, read in created() before anything can have changed.
        // Compared by what is chosen, not by how many filters are on: going from one
        // sub-schedule to another leaves the count at one.
        leadFilterKey(key) {
            const lead = this.route === 'guest' ? document.getElementById('gp-next-event') : null;
            // Its wrapper goes with it, or the room it stood in stays.
            if (lead) { (lead.closest('[data-lead-wrap]') || lead).hidden = key !== this.leadFilterKeyAtLoad; }
        },
        selectedGroup(newGroupSlug) {
            // Back/Forward (readFiltersFromUrl) restores the sub-schedule AND the category the
            // address names together: neither re-writes the address nor second-guesses them.
            if (this.restoringFiltersFromUrl) {
                return;
            }
            if (this.route === 'guest' && !this.embed) {
                this.updateUrlWithGroup(newGroupSlug);
            }
            // Reset category selection when group changes, as available categories may change
            if (this.selectedCategory && !this.availableCategories.find(cat => cat.id == this.selectedCategory)) {
                this.selectedCategory = '';
            }
            // updateUrlWithGroup() drops ?category= unconditionally; put back the one that survived.
            this.syncFiltersToUrl();
        },
        selectedCategory() {
            this.syncFiltersToUrl();
        },
        selectedCustomFields: {
            deep: true,
            handler() {
                this.syncFiltersToUrl();
            },
        },
        searchInput(value) {
            clearTimeout(this.searchDebounceTimer);
            if (!value) {
                this.searchQuery = '';
                return;
            }
            this.searchDebounceTimer = setTimeout(() => { this.searchQuery = value; }, 150);
        },
        showFiltersDrawer(open) {
            document.body.style.overflow = open ? 'hidden' : '';
            this.onFilterPanelToggle(open, false);
        },
        showDesktopFiltersModal(open) {
            document.body.style.overflow = open ? 'hidden' : '';
            this.onFilterPanelToggle(open, true);
        },
    },
    methods: {
        commaBreak(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/ , /g, '<br>');
        },
        matchesFrequency(event, date) {
            const dateStr = date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');

            // Exclude dates have highest priority
            if (event.recurring_exclude_dates && event.recurring_exclude_dates.length > 0
                && event.recurring_exclude_dates.includes(dateStr)) {
                return false;
            }

            // Include dates bypass pattern checks
            if (event.recurring_include_dates && event.recurring_include_dates.length > 0
                && event.recurring_include_dates.includes(dateStr)) {
                return true;
            }

            return this.matchesFrequencyPattern(event, date);
        },
        matchesFrequencyPattern(event, date) {
            const frequency = event.recurring_frequency || 'weekly';
            const dayOfWeek = date.getDay();

            switch (frequency) {
                case 'daily':
                    return true;

                case 'weekly':
                    return event.days_of_week && event.days_of_week[dayOfWeek] === '1';

                case 'every_n_weeks': {
                    if (!event.days_of_week || event.days_of_week[dayOfWeek] !== '1') return false;
                    const interval = event.recurring_interval || 2;
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    // Get start of week (Sunday) for both dates
                    const startWeek = new Date(startDate);
                    startWeek.setDate(startWeek.getDate() - startWeek.getDay());
                    const dateWeek = new Date(date);
                    dateWeek.setDate(dateWeek.getDate() - dateWeek.getDay());
                    const daysDiff = Math.round((dateWeek - startWeek) / (1000 * 60 * 60 * 24));
                    const weeksDiff = Math.floor(daysDiff / 7);
                    return weeksDiff % interval === 0;
                }

                case 'monthly_date': {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    return date.getDate() === startDate.getDate();
                }

                case 'monthly_weekday': {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    const nthWeekday = Math.ceil(startDate.getDate() / 7);
                    const targetDayOfWeek = startDate.getDay();
                    const dateNthWeekday = Math.ceil(date.getDate() / 7);
                    return date.getDay() === targetDayOfWeek && dateNthWeekday === nthWeekday;
                }

                case 'yearly': {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    return date.getMonth() === startDate.getMonth() && date.getDate() === startDate.getDate();
                }

                default:
                    return event.days_of_week && event.days_of_week[dayOfWeek] === '1';
            }
        },
        countOccurrencesForFrequency(event, startDate, checkDate) {
            const frequency = event.recurring_frequency || 'weekly';
            let count = 0;

            switch (frequency) {
                case 'daily': {
                    const diffTime = checkDate.getTime() - startDate.getTime();
                    count = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    break;
                }

                case 'monthly_date': {
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        count++;
                        current.setMonth(current.getMonth() + 1);
                    }
                    break;
                }

                case 'monthly_weekday': {
                    const nthWeekday = Math.ceil(startDate.getDate() / 7);
                    const targetDayOfWeek = startDate.getDay();
                    const current = new Date(startDate);
                    current.setDate(1);
                    while (current <= checkDate) {
                        const targetMonth = current.getMonth();
                        let found = 0;
                        const candidate = new Date(current);
                        while (candidate.getMonth() === targetMonth) {
                            if (candidate.getDay() === targetDayOfWeek) {
                                found++;
                                if (found === nthWeekday) {
                                    if (candidate >= startDate && candidate <= checkDate) {
                                        count++;
                                    }
                                    break;
                                }
                            }
                            candidate.setDate(candidate.getDate() + 1);
                        }
                        // Move to next month
                        current.setMonth(current.getMonth() + 1);
                        current.setDate(1);
                    }
                    break;
                }

                case 'yearly': {
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        count++;
                        current.setFullYear(current.getFullYear() + 1);
                    }
                    break;
                }

                case 'every_n_weeks': {
                    const interval = event.recurring_interval || 2;
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        const startWeek = new Date(startDate);
                        startWeek.setDate(startWeek.getDate() - startWeek.getDay());
                        const currentWeek = new Date(current);
                        currentWeek.setDate(currentWeek.getDate() - currentWeek.getDay());
                        const daysDiff = Math.round((currentWeek - startWeek) / (1000 * 60 * 60 * 24));
                        const weeksDiff = Math.floor(daysDiff / 7);
                        if (weeksDiff % interval === 0 && event.days_of_week && event.days_of_week[current.getDay()] === '1') {
                            count++;
                        }
                        current.setDate(current.getDate() + 1);
                    }
                    break;
                }

                case 'weekly':
                default: {
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        if (event.days_of_week && event.days_of_week[current.getDay()] === '1') {
                            count++;
                        }
                        current.setDate(current.getDate() + 1);
                    }
                    break;
                }
            }

            // Adjust count for include/exclude dates
            if (event.recurring_exclude_dates && event.recurring_exclude_dates.length > 0) {
                event.recurring_exclude_dates.forEach(excludeDateStr => {
                    const excludeDate = new Date(excludeDateStr + 'T00:00:00');
                    if (excludeDate >= startDate && excludeDate <= checkDate
                        && this.matchesFrequencyPattern(event, excludeDate)) {
                        count--;
                    }
                });
            }

            if (event.recurring_include_dates && event.recurring_include_dates.length > 0) {
                event.recurring_include_dates.forEach(includeDateStr => {
                    const includeDate = new Date(includeDateStr + 'T00:00:00');
                    if (includeDate >= startDate && includeDate <= checkDate
                        && !this.matchesFrequencyPattern(event, includeDate)) {
                        count++;
                    }
                });
            }

            return Math.max(0, count);
        },
        getEventDotColor(event) {
            if (event.category_color) return event.category_color;
            if (!event.group_id) return null;
            const group = this.groups.find(g => g.id === event.group_id);
            return group && group.color ? group.color : null;
        },
        playVideo(key) {
            this.playingVideo = this.playingVideo === key ? null : key;
        },
        showMoreListRows() {
            this.listRowLimit += 200;
        },
        // Where the list is being left: how far down, how many rows, the phone month's day.
        // Kept for this tab only, under this page's own address (filters are in the address).
        listMemoryKey() {
            return 'es_list_' + window.location.pathname + window.location.search;
        },
        rememberList() {
            try {
                // A venue, Free, Online and a search are not in the address, so Back returns
                // the list WITHOUT them: a place measured in the narrowed list would land
                // somewhere else in the full one. Then nothing is kept and Back opens at the top.
                if (this.selectedVenue || this.showFreeOnly || this.showOnlineOnly || this.isSearching) {
                    sessionStorage.removeItem(this.listMemoryKey());
                    return;
                }
                sessionStorage.setItem(this.listMemoryKey(), JSON.stringify({
                    y: Math.round(window.scrollY), limit: this.listRowLimit, day: this.phoneDay, past: this.phoneShowPast, at: Date.now(),
                }));
            } catch (e) {}
        },
        // Only when the page is come BACK to (the browser's Back or Forward), never on a fresh
        // visit or a reload, and only for half an hour.
        recallList() {
            try {
                const entry = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
                const left = JSON.parse(sessionStorage.getItem(this.listMemoryKey()) || 'null');
                if (entry && entry.type === 'back_forward' && left && Date.now() - left.at < 30 * 60 * 1000) {
                    this.listToRestore = left;
                }
            } catch (e) {}
        },
        // Try the load again: the button on the failed-load notice, and the browser's `online`
        // event. The same choice mounted() makes between the list's payload and the month's.
        retryLoad() {
            if (this.isLoadingEvents) {
                return;
            }
            this.isLoadingEvents = true;
            if (this.currentView === 'list') {
                this.fetchCalendarEvents({ skipMonthFilter: true });
            } else {
                this.fetchCalendarEventsForMonth(this.pageMonth, this.pageYear);
            }
        },
        toggleView(view) {
            this.currentView = view;
            this.updatePanelWrapper(view);
            this.updateOuterContainers(view);
            if (this.subdomain) {
                try {
                    localStorage.setItem('es_view_' + this.subdomain, view);
                } catch (e) {
                    // localStorage not available
                }
            }

            if (view === 'list' && !this.listDataLoaded) {
                // The list needs the wide (row-capped) upcoming set, not just the current month grid.
                this.isLoadingEvents = true;
                this.fetchCalendarEvents({ skipMonthFilter: true });
            } else if (view === 'calendar' && this.listDataLoaded) {
                this.listDataLoaded = false;
                this.isLoadingEvents = true;
                this.fetchCalendarEventsForMonth(this.pageMonth, this.pageYear);
            } else if (this.loadFailed) {
                // Neither branch above loads anything when the LIST's load was the one that
                // failed, so "Failed to load data" used to stay above a month that had loaded
                // fine. Load for the view just chosen; success takes the notice down.
                this.retryLoad();
            }
        },
        updatePanelWrapper(view) {
            const wrapper = document.getElementById('gp-calendar');
            if (wrapper) {
                if (view === 'list') {
                    wrapper.classList.add('calendar-panel-border-transparent');
                    wrapper.classList.remove('calendar-panel-border');
                    wrapper.style.paddingLeft = '0';
                    wrapper.style.paddingRight = '0';
                    wrapper.style.paddingTop = '0';
                    wrapper.style.paddingBottom = '0';
                } else {
                    wrapper.classList.remove('calendar-panel-border-transparent');
                    wrapper.classList.add('calendar-panel-border');
                    wrapper.style.paddingLeft = '';
                    wrapper.style.paddingRight = '';
                    wrapper.style.paddingTop = '';
                    wrapper.style.paddingBottom = '';
                }
            }
        },
        updateOuterContainers(view, animate = true) {
            const maxWidth = view === 'list' ? '56rem' : '200rem';
            // The next-event card (role/show-guest) is told which view is on: it leads the
            // month, and stands aside in the list view at every width.
            const leadWrap = document.querySelector('[data-lead-wrap]');
            if (leadWrap) { leadWrap.dataset.view = view; }
            document.querySelectorAll('[data-view-width]').forEach(el => {
                const prevMaxWidth = el.style.maxWidth;
                el.style.maxWidth = maxWidth;
                if (animate) {
                    const anim = view === 'calendar' ? 'view-toggle-bounce-expand' : 'view-toggle-bounce-shrink';
                    const playBounce = () => {
                        el.style.animation = 'none';
                        el.offsetHeight;
                        el.style.animation = anim + ' 0.4s ease-out';
                        el.addEventListener('animationend', function handler() {
                            el.style.animation = '';
                            el.removeEventListener('animationend', handler);
                        });
                    };
                    if (view === 'calendar' || prevMaxWidth === maxWidth) {
                        playBounce();
                    } else {
                        el.addEventListener('transitionend', function handler(e) {
                            if (e.propertyName !== 'max-width') return;
                            el.removeEventListener('transitionend', handler);
                            playBounce();
                        });
                    }
                }
            });
            // The view switch in the schedule's header says which view is on; how a pressed
            // button looks is the stylesheet's (partials/guest-kit-styles, .gk-head-seg-btn).
            ['list', 'calendar'].forEach((name) => {
                const btn = document.getElementById('toggle-' + name + '-btn');
                if (btn) { btn.setAttribute('aria-pressed', view === name ? 'true' : 'false'); }
            });
        },
        getHeaderImage(event) {
            if (event.venue_header_image) return event.venue_header_image;
            if (event.talent && event.talent.length > 0) {
                for (const t of event.talent) {
                    if (t.header_image) return t.header_image;
                }
            }
            return null;
        },
        formatDuration(hours) {
            if (!hours) return '';
            const labels = this.durationLabels;
            if (hours >= 24) {
                const days = Math.floor(hours / 24);
                const remainingHours = Math.round(hours % 24);
                if (remainingHours > 0) return `${days} ${labels.d} ${remainingHours} ${labels.h}`;
                return `${days} ${labels.d}`;
            }
            const totalMinutes = Math.round(hours * 60);
            const h = Math.floor(totalMinutes / 60);
            const m = totalMinutes % 60;
            if (h > 0 && m > 0) return `${h} ${labels.h} ${m} ${labels.m}`;
            if (h > 0) return `${h} ${labels.h}`;
            return `${m} ${labels.m}`;
        },
        clearFilters() {
            this.selectedGroup = '';
            this.selectedCategory = '';
            this.showOnlineOnly = false;
            this.selectedVenue = '';
            this.showFreeOnly = false;
            this.selectedCustomFields = Object.fromEntries(this.filterCustomFields.map(f => [f.key, '']));
            this.clearSearch();
        },
        // Cancels a pending debounce too, or the text just cleared would come back 150ms later.
        clearSearch() {
            clearTimeout(this.searchDebounceTimer);
            this.searchDebounceTimer = null;
            this.searchInput = '';
            this.searchQuery = '';
        },
        // How every custom field value is compared: trimmed, inner whitespace collapsed, and
        // lowercased, so text typed by hand ("Room A", "room a ") still lands on one option.
        normKey(value) {
            if (value === null || value === undefined) return '';
            return String(value).trim().replace(/\s+/g, ' ').toLowerCase();
        },
        // An event's values for one filter field, normalized. A multiselect is stored as the
        // comma-joined "A, B" (Role::sanitizeCustomFieldValues()), so it is split into its
        // options; a dropdown or text value is kept whole. String() because an AI-parsed value
        // is not guaranteed to be a string, and .trim() on anything else would throw and take
        // the whole calendar down with it.
        customFieldValuesOf(event, field) {
            const raw = (event.custom_field_values || {})[field.key];
            if (raw === null || raw === undefined || raw === '') return [];
            if (field.type === 'multiselect') {
                return String(raw).split(',').map(v => this.normKey(v)).filter(v => v !== '');
            }
            const key = this.normKey(raw);
            return key === '' ? [] : [key];
        },
        matchesCustomFields(event, exceptKey = null) {
            for (const field of this.filterCustomFields) {
                if (field.key === exceptKey) continue;
                const selected = this.selectedCustomFields[field.key];
                if (!selected) continue;
                if (!this.customFieldValuesOf(event, field).includes(selected)) return false;
            }
            return true;
        },
        // Accent- and case-insensitive: "cafe" finds "Café".
        searchFold(text) {
            if (text === null || text === undefined) return '';
            return String(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        },
        buildSearchHaystack(event) {
            const parts = [event.name, event.short_description, event.venue_name, event.category_name || (event.category_id ? this.categories[event.category_id] : '')];
            (event.talent || []).forEach(t => parts.push(t && t.name));
            (event.parts || []).forEach(p => parts.push(p && p.name));
            const values = event.custom_field_values || {};
            Object.entries(this.searchableCustomFields || {}).forEach(([key, conf]) => {
                const raw = values[key];
                if (raw === null || raw === undefined || raw === '') return;
                parts.push(raw);
                // The translated option labels too, so a visitor reading the English version can
                // search for the words they are actually looking at.
                const map = (conf && conf.optionsMap) || {};
                String(raw).split(',').forEach(v => {
                    const translated = map[v.trim()];
                    if (translated) parts.push(translated);
                });
            });
            return this.searchFold(parts.filter(p => p !== null && p !== undefined && p !== '').map(p => String(p)).join('\n'));
        },
        matchesSearch(event) {
            if (!this.isSearching) return true;
            const haystack = this.searchHaystacks[event.id] ?? this.buildSearchHaystack(event);
            return this.searchTokens.every(token => haystack.includes(token));
        },
        // The one filter predicate. Every list, grid cell and count goes through here, so they
        // cannot disagree about what a filter means. `except` leaves one filter out, for the
        // counts beside that filter's own options.
        passesFilters(event, except = {}) {
            if (!except.group && this.selectedGroupObj && event.group_id !== this.selectedGroupObj.id) {
                return false;
            }
            if (!except.category && this.selectedCategory && event.category_id != this.selectedCategory) {
                return false;
            }
            if (this.showOnlineOnly && !event.is_online) {
                return false;
            }
            if (!except.venue && this.selectedVenue && event.venue_subdomain !== this.selectedVenue) {
                return false;
            }
            if (this.showFreeOnly && !event.is_free) {
                return false;
            }
            if (!this.matchesCustomFields(event, except.customField || null)) {
                return false;
            }
            return this.matchesSearch(event);
        },
        // [param, value] pairs for the active custom field filters that have a stable index. The
        // value is the option's own spelling where it is known ("Room A"), not the lowercased key.
        customFieldUrlParams() {
            const pairs = [];
            this.filterCustomFields.forEach(field => {
                const selected = this.selectedCustomFields[field.key];
                if (!field.index || !selected) return;
                const option = (this.availableCustomFieldOptions[field.key] || []).find(o => o.key === selected);
                pairs.push(['custom_' + field.index, option ? option.value : selected]);
            });
            return pairs;
        },
        // Mirrors the category and custom field filters into the address bar, so the page a
        // visitor copies or reloads is the page they are looking at. replaceState, not
        // pushState: a filter change is not a place to go Back to, and passing history.state on
        // keeps navigateMonth()'s { month, year } for the popstate handler. The sub-schedule is
        // the path and is written by updateUrlWithGroup().
        syncFiltersToUrl() {
            if (this.route !== 'guest' || this.embed || this.restoringFiltersFromUrl) {
                return;
            }
            const url = new URL(window.location);
            if (this.selectedCategory) {
                url.searchParams.set('category', this.selectedCategory);
            } else {
                url.searchParams.delete('category');
            }
            for (let i = 1; i <= 10; i++) {
                url.searchParams.delete('custom_' + i);
            }
            this.customFieldUrlParams().forEach(([name, value]) => url.searchParams.set(name, value));
            if (url.toString() !== window.location.href) {
                window.history.replaceState(window.history.state, '', url.toString());
            }
        },
        // The popstate half of syncFiltersToUrl(): Back/Forward lands on a URL whose filters
        // should be the ones shown. The flag keeps the watchers from writing the URL straight back.
        // The sub-schedule is restored too, from the path (or ?schedule=): restoring a category
        // without it can pair the category with a sub-schedule that has none of its events.
        readFiltersFromUrl() {
            const params = new URLSearchParams(window.location.search);
            this.restoringFiltersFromUrl = true;

            const path = window.location.pathname;
            const base = this.guestBasePath || '';
            const rest = path.startsWith(base) ? path.slice(base.length) : '';
            let segment = rest.replace(/^\/+/, '').split('/')[0] || '';
            // decodeURIComponent throws on a malformed "%" sequence; an unknown slug is dropped below anyway.
            try { segment = decodeURIComponent(segment); } catch (e) { segment = ''; }
            let slug = segment || params.get('schedule') || '';
            if (slug && !this.groups.some(g => g.slug === slug)) slug = '';
            this.selectedGroup = slug;

            this.selectedCategory = params.get('category') || '';
            const selection = {};
            this.filterCustomFields.forEach(field => {
                // A field without an index has no URL param, so the address says nothing about
                // it: keep what is selected rather than clearing it on every Back.
                if (!field.index) {
                    selection[field.key] = this.selectedCustomFields[field.key] || '';
                    return;
                }
                const raw = params.get('custom_' + field.index);
                let key = raw ? this.normKey(raw.slice(0, this.filterParamMaxLength)) : '';
                if (key && field.type !== 'string' && !(field.options || []).some(o => this.normKey(o) === key)) {
                    key = '';
                }
                selection[field.key] = key;
            });
            this.selectedCustomFields = selection;
            this.$nextTick(() => { this.restoringFiltersFromUrl = false; });
        },
        // The Clipboard API only exists in a secure context, so a plain-HTTP selfhost install (or
        // a denied permission) falls back to a hidden textarea and execCommand('copy'), the same
        // way the schedule editor's copy buttons do.
        copyFilterLink() {
            const url = this.shareableFilterUrl;
            if (!url) return;
            const done = () => {
                this.linkCopied = true;
                clearTimeout(this.linkCopiedTimer);
                this.linkCopiedTimer = setTimeout(() => { this.linkCopied = false; }, 2000);
            };
            const fallback = () => {
                const textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                try { if (document.execCommand('copy')) done(); } catch (err) {}
                document.body.removeChild(textarea);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(fallback);
            } else {
                fallback();
            }
        },
        // Removing a chip can remove the row it sits in; focus then goes back to the Filters
        // button rather than falling to <body>.
        removeFilterChip(chip) {
            chip.clear();
            this.$nextTick(() => {
                if (this.narrowingFilterCount === 0) this.focusFiltersOpener();
            });
        },
        focusFiltersOpener() {
            this.$nextTick(() => {
                const candidates = ['hero-filters-btn', 'hero-filters-btn-mobile']
                    .map(id => document.getElementById(id))
                    .filter(el => el && el.offsetParent !== null);
                if (candidates.length) candidates[0].focus();
            });
        },
        closeFilterPanels() {
            this.showFiltersDrawer = false;
            this.showDesktopFiltersModal = false;
        },
        // Focus goes into the panel on open and back to whatever opened it on close. The search
        // box takes focus only in the desktop modal: in the phone drawer it would pop the keyboard
        // up over the filters the visitor opened the sheet to see.
        onFilterPanelToggle(open, desktop) {
            if (open) {
                this.filterPanelOpener = document.activeElement && document.activeElement !== document.body ? document.activeElement : null;
                this.$nextTick(() => {
                    // The search box only with a real pointer: a tablet opens the desktop modal too,
                    // and focusing a text box there pops the keyboard over the filters.
                    const finePointer = window.matchMedia && window.matchMedia('(pointer: fine)').matches;
                    const target = desktop
                        ? (finePointer ? this.$refs.desktopFilterSearch : this.$refs.desktopFilterPanel)
                        : this.$refs.mobileFilterPanel;
                    if (target && target.focus) target.focus();
                });
                return;
            }
            const opener = this.filterPanelOpener;
            this.filterPanelOpener = null;
            if (opener && opener.focus && document.body.contains(opener)) {
                opener.focus();
            }
        },
        // Enter applies the search and gets out of the way: on a phone the keyboard plus the
        // sheet otherwise cover every result the search just found.
        onSearchEnter(event) {
            clearTimeout(this.searchDebounceTimer);
            this.searchQuery = this.searchInput;
            if (event && event.target) event.target.blur();
            this.closeFilterPanels();
        },
        // Escape in the box clears it first; a second Escape (or one on an empty box) closes.
        onSearchEscape() {
            if (this.searchInput) {
                this.clearSearch();
                return;
            }
            this.closeFilterPanels();
        },
        getEventsForDate(dateStr) {
            // Use the pre-calculated events map from the backend
            if (this.eventsMap[dateStr]) {
                const eventIds = this.eventsMap[dateStr];
                return this.filteredEvents.filter(event => {
                    return eventIds.includes(event.id);
                }).sort((a, b) => this.compareSameDay(a, b, dateStr));
            }
            return [];
        },
        updateEventIdsInViewedMonth(rawEventsMap) {
            const ids = new Set();
            for (const dateStr in rawEventsMap) {
                if (rawEventsMap[dateStr]) {
                    rawEventsMap[dateStr].forEach(id => ids.add(id));
                }
            }
            this.eventIdsInViewedMonth = Array.from(ids);
        },
        isEventVisible(event) {
            return this.passesFilters(event);
        },
        // A day of the phone's month: fold the month to a line and bring that day's rows up.
        pickPhoneDay(date) {
            if (date < this.scheduleDay(0)) { this.phoneShowPast = true; }
            this.phoneDay = date;
            // The pressed day is gone with the month it was in: focus goes to the line the
            // month folded to, which is what brings the month back.
            this.$nextTick(() => {
                if (this.$refs.phoneFold) { this.$refs.phoneFold.focus({ preventScroll: true }); }
                const panel = document.getElementById('gk-day-' + date);
                if (panel) { panel.scrollIntoView({ block: 'start', behavior: 'smooth' }); }
            });
        },
        // The month again, with focus on the day that had been picked.
        unfoldPhoneMonth() {
            const date = this.phoneDay;
            this.phoneDay = '';
            this.$nextTick(() => {
                const day = date ? document.querySelector('[data-phone-month] [data-day="' + date + '"]') : null;
                if (day) { day.focus({ preventScroll: true }); }
            });
        },
        pickQuickChip(value, clickEvent) {
            if (this.quickChipKind === 'group') {
                this.selectedGroup = value;
            } else {
                this.selectedCategory = value;
            }
            // The chosen chip is brought into view: in a row that scrolls sideways, one picked
            // at its edge was half off the screen.
            const chip = clickEvent && clickEvent.currentTarget;
            if (chip && chip.scrollIntoView) {
                this.$nextTick(() => chip.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' }));
            }
        },
        // Today's date, or a day from it, where the schedule is (userTimezone is the schedule's
        // zone, the one the rows are bucketed into days by), read as the heading is drawn.
        scheduleDay(offset) {
            const there = this.userTimezone ? new Date(new Date().toLocaleString('en-US', { timeZone: this.userTimezone })) : new Date();
            there.setDate(there.getDate() + offset);
            return there.getFullYear() + '-' + String(there.getMonth() + 1).padStart(2, '0') + '-' + String(there.getDate()).padStart(2, '0');
        },
        dayWord(dateStr) {
            if (dateStr === this.scheduleDay(0)) return this.dayWords.today;
            if (dateStr === this.scheduleDay(1)) return this.dayWords.tomorrow;
            return '';
        },
        // The day a row's tickets are sold under. A series: the occurrence, and for one that
        // runs over several days the day it BEGAN, not the day it is listed on while it runs.
        // Anything else has one day, whatever day its row stands under.
        rowDate(event) {
            if (event.days_of_week && event.days_of_week.length) {
                return event._originalOccurrenceDate || event.occurrenceDate || null;
            }
            return event.local_date || null;
        },
        // 'YYYY-MM-DD HH:MM' in a zone: the event's own (event.zone) where a row has one. On a
        // curator's page that is not the page's, and an event's times are in its own: a New
        // York curator's page dropped the price of a Los Angeles 20:00 show at 17:00 there.
        scheduleNow(zone) {
            const where = zone || this.userTimezone;
            const there = where ? new Date(new Date().toLocaleString('en-US', { timeZone: where })) : new Date();
            const two = number => String(number).padStart(2, '0');
            return there.getFullYear() + '-' + two(there.getMonth() + 1) + '-' + two(there.getDate()) + ' ' + two(there.getHours()) + ':' + two(there.getMinutes());
        },
        // Whether our own tickets for this row can no longer be bought because the night has
        // begun, or ended: Event::passesSellingWindow() on the list's clock, branch for branch.
        // An occurrence sells until it STARTS; until it ENDS where the event sells after it
        // starts, and for an event on one date that runs over several days (not for a series
        // of them). Its end is its length, or two hours where it has none (getEndDateTime()).
        // The server already leaves the price off a one-day event that has begun; this is for
        // a series, which has a different answer each day, and for a page left open.
        rowSalesOver(event) {
            const date = this.rowDate(event);
            const time = (event.local_starts_at || '').slice(11, 16);
            if (!date || !time) { return false; }
            const now = this.scheduleNow(event.zone);
            const series = !!(event.days_of_week && event.days_of_week.length);
            if (!event.sells_after_start && (series || !event.is_multi_day)) { return now >= date + ' ' + time; }
            const hours = event.duration > 0 ? event.duration : 2;
            const [y, m, d] = date.split('-').map(Number), [h, min] = time.split(':').map(Number);
            const end = new Date(y, m - 1, d, h, min + Math.round(hours * 60));
            const two = number => String(number).padStart(2, '0');
            return now >= end.getFullYear() + '-' + two(end.getMonth() + 1) + '-' + two(end.getDate()) + ' ' + two(end.getHours()) + ':' + two(end.getMinutes());
        },
        rowSoldOut(event) {
            const date = this.rowDate(event);
            return !!date && !this.rowSalesOver(event) && (event.sold_out_dates || []).includes(date);
        },
        rowLow(event) {
            const date = this.rowDate(event);
            return !!date && !this.rowSalesOver(event) && (event.low_stock_dates || []).includes(date);
        },
        // What our own tickets cost, while they can be bought.
        rowPrice(event) {
            return event.ticket_from && !this.rowSalesOver(event) ? event.ticket_from : null;
        },
        // A card's own ticket line (role/partials/card-ticket-badge): our tickets, never beside
        // the sign-up badge or the owner's typed price, which have lines of their own.
        cardHasTickets(event) {
            if (event._isPast || event.is_password_protected || event.rsvp_enabled) return false;
            return !!(this.rowSoldOut(event) || this.rowPrice(event) || (event.ticket_free && !this.rowSalesOver(event)));
        },
        rowHasChips(event) {
            if (event.is_internal || event.is_draft) return true;
            if (event._isPast) return false;
            return !!(this.rowFree(event) || this.rowPrice(event) || this.rowSoldOut(event) || this.rowSoldElsewhere(event));
        },
        // Free tickets of ours, or a sign-up, which costs nothing by its nature.
        rowFree(event) {
            return !!((event.ticket_free && !this.rowSalesOver(event)) || (event.rsvp_enabled && !event.is_password_protected));
        },
        // Sold somewhere else, at a price the owner typed: not beside a price of our own.
        rowSoldElsewhere(event) {
            return !!(event.registration_url && event.ticket_price != null && !event.is_password_protected
                && !event.ticket_free && !event.rsvp_enabled && !event.ticket_from);
        },
        getEventUrl(event, occurrenceDate = null) {
            let url = event.guest_url;  // Already has /{subdomain}/{slug}/{id}
            let queryParams = [];

            // Check if this is a recurring event
            const isRecurring = event.days_of_week && event.days_of_week.length > 0;

            // Add date to path only for recurring events
            if (isRecurring) {
                // For recurring events, prioritize the occurrence date over the original start date
                const dateStr = occurrenceDate || event.occurrenceDate;
                if (dateStr) {
                    // Parse the date string as UTC to ensure it's always UTC
                    const [year, month, day] = dateStr.split('-').map(Number);
                    const utcDate = new Date(Date.UTC(year, month - 1, day));
                    url += '/' + utcDate.toISOString().split('T')[0];
                }
            }

            // Keep filters as query params (these don't affect social sharing)
            if (this.selectedCategory) {
                queryParams.push('category=' + this.selectedCategory);
            }

            if (this.selectedGroup) {
                queryParams.push('schedule=' + this.selectedGroup);
            }

            // So the event page's back link returns to the same filtered view.
            this.customFieldUrlParams().forEach(([name, value]) => {
                queryParams.push(name + '=' + encodeURIComponent(value));
            });

            // Carry a URL-forced layout through to the event page so the breadcrumb there
            // can hand it back and the visitor returns to the view they left.
            if (this.layoutFromUrl) {
                queryParams.push('layout=' + this.layoutFromUrl);
            }

            if (queryParams.length > 0) {
                url += '?' + queryParams.join('&');
            }

            return url;
        },
        openFanContentOnEventPage(event) {
            if (!this.fanContentGuestRedirect) return false;

            // No event URL to send them to. Returning false would open the inline form, which
            // carries no guest name/email fields and so could never pass validation - better to
            // do nothing than to bounce them off a form that always fails.
            if (!event.guest_url) return true;

            const url = event.guest_url + '#gp-fan-content';

            // This partial is also rendered inside the embed widget. Navigating there would
            // replace the embed with a full event page inside someone else's site, so open a
            // new tab when we are framed.
            if (window.self !== window.top) {
                window.open(url, '_blank', 'noopener');
            } else {
                window.location.href = url;
            }

            return true;
        },
        toggleVideoForm(event, $event) {
            if (this.openFanContentOnEventPage(event)) return;
            const key = event.uniqueKey;
            const btn = $event?.target?.closest('button');
            this.openVideoForm = { ...this.openVideoForm, [key]: !this.openVideoForm[key] };
            if (this.openVideoForm[key]) {
                this.openCommentForm = { ...this.openCommentForm, [key]: false };
                this.openPhotoForm = { ...this.openPhotoForm, [key]: false };
                this.$nextTick(() => {
                    if (!btn) return;
                    const form = btn.closest('div').parentElement.querySelector('form input[name="youtube_url"]');
                    if (form) form.focus();
                });
            }
        },
        togglePhotoForm(event, $event) {
            if (this.openFanContentOnEventPage(event)) return;
            const key = event.uniqueKey;
            this.openPhotoForm = { ...this.openPhotoForm, [key]: !this.openPhotoForm[key] };
            if (this.openPhotoForm[key]) {
                this.openVideoForm = { ...this.openVideoForm, [key]: false };
                this.openCommentForm = { ...this.openCommentForm, [key]: false };
            }
        },
        toggleCommentForm(event, $event) {
            if (this.openFanContentOnEventPage(event)) return;
            const key = event.uniqueKey;
            const btn = $event?.target?.closest('button');
            this.openCommentForm = { ...this.openCommentForm, [key]: !this.openCommentForm[key] };
            if (this.openCommentForm[key]) {
                this.openVideoForm = { ...this.openVideoForm, [key]: false };
                this.openPhotoForm = { ...this.openPhotoForm, [key]: false };
                this.$nextTick(() => {
                    if (!btn) return;
                    const form = btn.closest('div').parentElement.querySelector('form textarea[name="comment"]');
                    if (form) form.focus();
                });
            }
        },
        async votePoll(event, poll, optionIndex, clickEvent) {
            if (this.votingPoll[poll.id] != null) return;
            this.votingPoll = { ...this.votingPoll, [poll.id]: optionIndex };
            try {
                const url = event.vote_poll_url.replace('POLL_HASH', poll.id);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ option_index: optionIndex }),
                });
                if (response.status === 401) {
                    window.location.href = '{{ app_url("/login") }}';
                    return;
                }
                const data = await response.json();
                if (data.success) {
                    poll.results = data.results;
                    poll.total_votes = data.total_votes;
                    const btn = clickEvent?.target?.closest('button');
                    // Loaded only where the page's own events carry a poll (see the script tags
                    // above), and a poll can arrive later with an Ajax month.
                    if (btn && typeof firePollConfetti === 'function') firePollConfetti(btn, this.accentColor);
                    this.pollAnimating = { ...this.pollAnimating, [poll.id]: true };
                    await new Promise(r => setTimeout(r, 600));
                    poll.user_vote = optionIndex;
                    await this.$nextTick();
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            this.pollAnimating = { ...this.pollAnimating, [poll.id]: false };
                        });
                    });
                } else {
                    alert(data.error || '{{ __("messages.an_error_occurred") }}');
                }
            } finally {
                this.votingPoll = { ...this.votingPoll, [poll.id]: null };
            }
        },
        getVoteCount(poll, idx) {
            return (poll.results && poll.results[idx]) || 0;
        },
        getVotePercent(poll, idx) {
            if (!poll.total_votes) return 0;
            return Math.round((this.getVoteCount(poll, idx) / poll.total_votes) * 100);
        },
        getMaxVoteCount(poll) {
            if (!poll.results) return 0;
            return Math.max(...Object.values(poll.results), 0);
        },
        // The target of the event links on the cards: a new tab where a click on the card opens
        // one (navigateToEvent() below) - inside an embed, and in the admin portal.
        eventLinkTarget() {
            return (this.embed || this.route === 'admin') ? '_blank' : null;
        },
        // A card's title and image are real links, for crawlers and for "open in new tab". A
        // plain click keeps the one behaviour a link cannot express on its own: direct
        // registration opens the registration page instead, as a click on the card does. Anything
        // else - a modified or middle click, or no direct registration - is the browser's own
        // navigation to the anchor's href and target.
        //
        // registration_url arrives as Event::registrationHref(), an http(s) link or null. The
        // test is the backstop in the only place it would execute: window.open() runs a
        // javascript: URL on this page.
        onEventLinkClick(event, e) {
            if (!e || e.defaultPrevented || e.button !== 0) return;

            // Opened in a new tab or window: the browser follows the link itself, to the event
            // page. Still a tap into the event, as it is on the month grid's links.
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                this.countListTap();
                return;
            }

            if (this.directRegistration && event.registration_url && /^https?:\/\//i.test(event.registration_url)) {
                e.preventDefault();
                window.open(event.registration_url, '_blank', 'noopener');
                return;
            }

            this.countListTap();
        },
        // One of the guest pages' daily counts (App\Utils\GuestFunnel): a visitor went from a
        // schedule's list or month grid into an event. Only on the schedule's own page, and only
        // where the page printed the beacon (partials/guest-funnel decides whose visits count).
        countListTap() {
            if (this.route === 'guest' && !this.embed && window.esGuestFunnel) {
                window.esGuestFunnel('list_tap');
            }
        },
        navigateToEvent(event, e) {
            // Don't navigate if clicking on the edit link or a form/button
            if (!e?.target || e.target?.closest('a') || e.target?.closest('form') || e.target?.closest('button')) return;

            // Direct registration, on the same terms as onEventLinkClick() above.
            if (this.directRegistration && event.registration_url && /^https?:\/\//i.test(event.registration_url)) {
                window.open(event.registration_url, '_blank', 'noopener');
                return;
            }

            const url = this.getEventUrl(event);
            const openInNewTab = this.embed || this.route === 'admin';

            this.countListTap();

            if (openInNewTab) {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        },
        // Mirrors getEventDisplayName below: that method shows the VENUE name when you are
        // looking at a talent's own schedule, so the cell must not always use the event
        // name's direction.
        getEventDisplayDir(event) {
            if (this.subdomain && this.isRoleAMember(event)) {
                return (event.venue_name ? event.venue_dir : event.dir) || 'auto';
            }
            return event.dir || 'auto';
        },
        getEventDisplayName(event) {
            if (this.subdomain && this.isRoleAMember(event)) {
                return event.venue_name || event.name;
            }
            return event.name;
        },
        // Where an event falls within dateStr: 'HH:MM' in its schedule's clock, or '' (first)
        // for a multi-day event already running when dateStr begins. local_starts_at is a
        // series' ANCHOR (its first date), so only its time of day belongs to the occurrence -
        // comparing the full value put every older series ahead of that night's one-off events.
        sameDaySortKey(event, dateStr) {
            const startDate = event._originalOccurrenceDate || (event.days_of_week ? dateStr : event.local_date);
            if (startDate && startDate < dateStr) return '';
            return (event.local_starts_at || '').slice(11, 16);
        },
        compareSameDay(a, b, dateStr) {
            const ka = this.sameDaySortKey(a, dateStr);
            const kb = this.sameDaySortKey(b, dateStr);
            return ka < kb ? -1 : (ka > kb ? 1 : 0);
        },
        getEventTime(event) {
            if (!event.local_starts_at) return '';
            if (event.is_multi_day && event.local_end_date) {
                const startDate = new Date(event.local_starts_at);
                const [ey, em, ed] = event.local_end_date.split('-').map(Number);
                const endDate = new Date(ey, em - 1, ed);
                const opts = { month: 'short', day: 'numeric' };
                return startDate.toLocaleDateString(this.languageCode, opts) +
                    ' - ' + endDate.toLocaleDateString(this.languageCode, opts);
            }
            const date = new Date(event.local_starts_at);
            if (this.use24Hour) {
                return date.toLocaleTimeString(this.languageCode, { hour: '2-digit', minute: '2-digit', hour12: false });
            } else {
                return date.toLocaleTimeString(this.languageCode, { hour: 'numeric', minute: '2-digit', hour12: true });
            }
        },
        formatPrice(price, currencyCode) {
            const num = Number(price);
            const isWhole = Number.isFinite(num) && num === Math.trunc(num);
            return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                style: 'currency',
                currency: currencyCode,
                currencyDisplay: 'narrowSymbol',
                minimumFractionDigits: isWhole ? 0 : 2,
                maximumFractionDigits: 2,
            }).format(num);
        },
        isRoleAMember(event) {
            // This would need to be determined server-side and passed to the frontend
            // For now, return false as a placeholder
            return false;
        },
        shouldIncludePastDate(event, dateStr) {
            const recurringEndType = event.recurring_end_type || 'never';

            if (recurringEndType === 'never') {
                return true;
            }

            if (recurringEndType === 'on_date' && event.recurring_end_value) {
                const endDate = new Date(event.recurring_end_value + 'T00:00:00');
                const checkDate = new Date(dateStr + 'T00:00:00');
                return checkDate <= endDate;
            }

            if (recurringEndType === 'after_events' && event.recurring_end_value && event.start_date) {
                const maxOccurrences = parseInt(event.recurring_end_value);
                const startDate = new Date(event.start_date + 'T00:00:00');
                const checkDate = new Date(dateStr + 'T00:00:00');

                const occurrenceCount = this.countOccurrencesForFrequency(event, startDate, checkDate);

                return occurrenceCount <= maxOccurrences;
            }

            return true;
        },
        isPastEvent(dateStr) {
            // Parse the date string manually to avoid timezone issues
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day); // month is 0-indexed
            eventDate.setHours(23, 59, 59, 999);

            let today = new Date();

            // If user has a timezone, adjust today's date to their timezone
            if (this.userTimezone) {
                // Create a date in the user's timezone
                const userNow = new Date().toLocaleString("en-US", {timeZone: this.userTimezone});
                today = new Date(userNow);
            }

            today.setHours(0, 0, 0, 0);
            return eventDate < today;
        },
        isEventPast(event) {
            if (event && event.is_multi_day) {
                // For recurring events, compute end date from the occurrence date
                if (event.days_of_week && event.days_of_week.length > 0 && event.occurrenceDate && event.duration) {
                    const [y, m, d] = event.occurrenceDate.split('-').map(Number);
                    const occStart = new Date(y, m - 1, d);
                    const occEnd = new Date(occStart.getTime() + event.duration * 60 * 60 * 1000);
                    const endDateStr = occEnd.getFullYear() + '-' +
                        String(occEnd.getMonth() + 1).padStart(2, '0') + '-' +
                        String(occEnd.getDate()).padStart(2, '0');
                    return this.isPastEvent(endDateStr);
                }
                if (event.local_end_date) {
                    return this.isPastEvent(event.local_end_date);
                }
            }
            return this.isPastEvent(event?.occurrenceDate || event?.local_date);
        },
        formatMobileDate(dateStr) {
            if (!dateStr) return '';

            // Parse the date string manually to avoid timezone issues
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day); // month is 0-indexed
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                              'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const dayNum = eventDate.getDate();
            const suffix = this.getDaySuffix(dayNum);
            const monthName = monthNames[eventDate.getMonth()];

            return `${monthName} ${dayNum}${suffix}`;
        },
        formatDateHeader(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, {
                weekday: 'long',
                month: 'long',
                day: 'numeric'
            });
        },
        formatDateShort(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },
        formatDayName(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, {
                weekday: 'long'
            });
        },
        getMonthAbbr(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, { month: 'short' }).toUpperCase();
        },
        getDayNum(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            return parseInt(parts[2], 10);
        },
        getMultiDayBadge(event) {
            const startDateStr = event._originalOccurrenceDate || event.occurrenceDate;
            if (!startDateStr || !event.local_end_date) return '';
            const startDay = parseInt(startDateStr.split('-')[2], 10);
            const [sy, sm] = startDateStr.split('-').map(Number);
            const [ey, em, ed] = event.local_end_date.split('-').map(Number);
            if (sy === ey && sm === em) {
                return startDay + '-' + ed;
            }
            const endDate = new Date(ey, em - 1, ed);
            const endStr = endDate.toLocaleDateString(this.languageCode, { month: 'short', day: 'numeric' });
            return startDay + ' - ' + endStr;
        },
        getDaySuffix(day) {
            if (day >= 11 && day <= 13) return 'th';
            switch (day % 10) {
                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
                default: return 'th';
            }
        },
        getTalentHeaderImages(event) {
            if (!event.talent) return [];
            return event.talent.filter(t => t.header_image).map(t => ({ name: t.name, image: t.header_image }));
        },
        async loadMorePastEvents() {
            if (this.loadingPastEvents || !this.hasMorePastEvents) return;
            this.loadingPastEvents = true;
            this.pastLoadFailed = false;
            try {
                const oldestEvent = this.pastEvents[this.pastEvents.length - 1];
                if (!oldestEvent || !oldestEvent.starts_at) return;
                const baseUrl = '{{ isset($subdomain) ? route("role.list_past_events", ["subdomain" => $subdomain]) : "" }}';
                let url = baseUrl + '?before=' + encodeURIComponent(oldestEvent.starts_at);
                if (this.route === 'guest') {
                    url += '&lang=' + encodeURIComponent(this.languageCode);
                }
                const response = await fetch(url);
                // An error answered in JSON has no has_more: read as "no more", the button used to
                // vanish as though the past had run out.
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const data = await response.json();
                if (data.events && data.events.length > 0) {
                    this.pastEvents = this.pastEvents.concat(data.events);
                }
                this.hasMorePastEvents = data.has_more;
            } catch (e) {
                console.error('Failed to load more past events:', e);
                this.pastLoadFailed = true;
            } finally {
                this.loadingPastEvents = false;
            }
        },
        onEventsReady() {
            this.$nextTick(() => {
                const showPastEventsBtn = document.getElementById('showPastEventsBtn');
                const pastEventEls = document.querySelectorAll('.past-event');
                if (pastEventEls.length > 0 && showPastEventsBtn) {
                    showPastEventsBtn.classList.remove('hidden');
                    showPastEventsBtn.onclick = function() {
                        pastEventEls.forEach(event => {
                            event.classList.remove('hidden');
                        });
                        showPastEventsBtn.classList.add('hidden');
                    };
                }
            });
        },
        navigateMonth(direction) {
            let newMonth = this.pageMonth;
            let newYear = this.pageYear;

            if (direction === 0) {
                // The schedule's month, not the browser's: around the turn of a month a visitor
                // in another zone was sent to the month the schedule is not in yet, or has left.
                const [todayYear, todayMonth] = this.scheduleDay(0).split('-').map(Number);
                newMonth = todayMonth;
                newYear = todayYear;
            } else {
                newMonth += direction;
                if (newMonth > 12) {
                    newMonth = 1;
                    newYear++;
                } else if (newMonth < 1) {
                    newMonth = 12;
                    newYear--;
                }
            }

            this.pageMonth = newMonth;
            this.pageYear = newYear;
            this.phoneDay = '';
            this.phoneShowPast = false;
            this.listDataLoaded = false;
            this.isLoadingEvents = true;

            this.fetchCalendarEventsForMonth(newMonth, newYear);

            // Update browser URL (skip in embed mode)
            if (!this.embed) {
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('year', newYear);
                currentUrl.searchParams.set('month', newMonth);
                window.history.pushState({ month: newMonth, year: newYear }, '', currentUrl.toString());
            }
        },
        async fetchCalendarEventsForMonth(month, year) {
            if (this.tab === 'availability') {
                this.isLoadingEvents = false;
                return;
            }

            const cacheKeySuffix = month + '_' + year;
            // The language is part of the key: without it, toggling EN/HE repainted the previous
            // language's names from cache before the fetch resolved.
            const cacheKey = `es_cal_${this.route}_${this.subdomain}_${this.languageCode}_${cacheKeySuffix}`;

            // Show cached data immediately if available
            try {
                const cached = sessionStorage.getItem(cacheKey);
                if (cached) {
                    const data = JSON.parse(cached);
                    if (data && data.events && data.eventsMap && data.filterMeta) {
                        this.allEvents = data.events;
                        this.eventsMap = data.eventsMap;
                        this.updateEventIdsInViewedMonth(data.eventsMap);
                        this.pastEvents = data.pastEvents || [];
                        this.hasMorePastEvents = data.hasMorePastEvents || false;
                        this.listTruncated = !!data.truncated;
                        this.uniqueCategoryIds = data.filterMeta.uniqueCategoryIds;
                        this.isLoadingEvents = false;
                        this.onEventsReady();
                    }
                }
            } catch (e) {
                // sessionStorage unavailable
            }

            try {
                let url;
                if (this.route === 'home') {
                    url = '{{ route("home.calendar_events") }}';
                } else if (this.route === 'admin') {
                    url = '{{ isset($subdomain) ? route("role.admin_calendar_events", ["subdomain" => $subdomain]) : "" }}';
                } else {
                    url = '{{ isset($subdomain) ? route("role.calendar_events", ["subdomain" => $subdomain]) : "" }}';
                }
                const separator = url.includes('?') ? '&' : '?';
                url += separator + 'month=' + month + '&year=' + year;
                if (this.route === 'guest') {
                    url += '&lang=' + encodeURIComponent(this.languageCode);
                }

                this.loadFailed = false;
                const response = await fetch(url);
                // fetch() resolves on a 500 or a 404 too, so the status has to be read.
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const data = await response.json();

                this.allEvents = data.events;
                this.eventsMap = data.eventsMap;
                this.updateEventIdsInViewedMonth(data.eventsMap);
                this.pastEvents = data.pastEvents || [];
                this.hasMorePastEvents = data.hasMorePastEvents || false;
                this.listTruncated = !!data.truncated;
                this.uniqueCategoryIds = data.filterMeta.uniqueCategoryIds;

                // Cache for stale-while-revalidate on next visit
                try {
                    sessionStorage.setItem(cacheKey, JSON.stringify(data));
                } catch (e) {
                    // Quota exceeded or unavailable
                }

            } catch (e) {
                console.error('Failed to load calendar events:', e);
                // Said on the page (data-load-failed), above whatever the cache could still draw.
                this.loadFailed = true;
            } finally {
                this.isLoadingEvents = false;
                this.onEventsReady();
            }
        },
        async fetchCalendarEvents(options = {}) {
            if (this.tab === 'availability') {
                this.isLoadingEvents = false;
                return;
            }

            const skipMonthFilter = options.skipMonthFilter || false;

            if (!skipMonthFilter) {
                return this.fetchCalendarEventsForMonth(this.pageMonth, this.pageYear);
            }

            const cacheKey = `es_cal_${this.route}_${this.subdomain}_${this.languageCode}_list`;

            // Show cached data immediately if available
            try {
                const cached = sessionStorage.getItem(cacheKey);
                if (cached) {
                    const data = JSON.parse(cached);
                    if (data && data.events && data.eventsMap && data.filterMeta) {
                        this.allEvents = data.events;
                        this.eventsMap = data.eventsMap;
                        this.updateEventIdsInViewedMonth(data.eventsMap);
                        this.pastEvents = data.pastEvents || [];
                        this.hasMorePastEvents = data.hasMorePastEvents || false;
                        this.listTruncated = !!data.truncated;
                        this.uniqueCategoryIds = data.filterMeta.uniqueCategoryIds;
                        this.isLoadingEvents = false;
                        this.onEventsReady();
                    }
                }
            } catch (e) {
                // sessionStorage unavailable
            }

            try {
                let url;
                if (this.route === 'home') {
                    url = '{{ route("home.calendar_events") }}';
                } else if (this.route === 'admin') {
                    url = '{{ isset($subdomain) ? route("role.admin_calendar_events", ["subdomain" => $subdomain]) : "" }}';
                } else {
                    url = '{{ isset($subdomain) ? route("role.calendar_events", ["subdomain" => $subdomain]) : "" }}';
                }
                const separator = url.includes('?') ? '&' : '?';
                // List layout: omit month/year and request the unbounded (row-capped) upcoming set
                // so future-month events load in one fetch instead of just the current month.
                url += separator + 'list=1';
                if (this.route === 'guest') {
                    url += '&lang=' + encodeURIComponent(this.languageCode);
                }

                this.loadFailed = false;
                const response = await fetch(url);
                // fetch() resolves on a 500 or a 404 too, so the status has to be read.
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const data = await response.json();

                this.allEvents = data.events;
                this.eventsMap = data.eventsMap;
                this.updateEventIdsInViewedMonth(data.eventsMap);
                this.pastEvents = data.pastEvents || [];
                this.hasMorePastEvents = data.hasMorePastEvents || false;
                this.listTruncated = !!data.truncated;
                this.uniqueCategoryIds = data.filterMeta.uniqueCategoryIds;

                this.listDataLoaded = true;

                // Cache for stale-while-revalidate on next visit
                try {
                    sessionStorage.setItem(cacheKey, JSON.stringify(data));
                } catch (e) {
                    // Quota exceeded or unavailable
                }

            } catch (e) {
                console.error('Failed to load calendar events:', e);
                // Said on the page (data-load-failed), above whatever the cache could still draw.
                this.loadFailed = true;
            } finally {
                this.isLoadingEvents = false;
                this.onEventsReady();
            }
        },
        updateUrlWithGroup(newGroupSlug) {
            // Only the path changes here, so carry the rest of the query string over rather
            // than re-deriving a couple of keys - otherwise picking a sub-schedule silently
            // drops ?layout=, ?lang= and ?dark=, and the URL the visitor copies or reloads
            // renders differently from what they are looking at.
            //
            // Two params must NOT survive. ?schedule= is the query form of the very thing the
            // path now expresses, and viewGuest falls back to it when the path resolves no
            // group - leaving it would resurrect a sub-schedule the visitor just cleared.
            // ?category= may have been reset a moment ago by the selectedGroup watcher above
            // (or by clearFilters, which reaches here through it) because the new sub-schedule
            // does not offer that category.
            const currentUrl = new URL(window.location);
            currentUrl.pathname = (this.guestBasePath + (newGroupSlug ? '/' + newGroupSlug : '')) || '/';
            currentUrl.searchParams.delete('schedule');
            currentUrl.searchParams.delete('category');

            window.history.pushState({}, '', currentUrl.toString());
        }
    },
    created() {
        this.leadFilterKeyAtLoad = this.leadFilterKey;
    },
    mounted() {
        if (this.route === 'guest') {
            this.recallList();
            window.addEventListener('pagehide', () => this.rememberList());
        }
        // Check localStorage for saved view preference. Skipped when ?layout= asked for a
        // specific view, and inside an embed: the embedding site chooses the layout, there
        // is no toggle in the frame to change it, and without this a preference the visitor
        // saved on the schedule's own page would silently override every embed of it.
        if (this.subdomain && this.route !== 'admin' && !this.embed && !this.layoutFromUrl) {
            try {
                const saved = localStorage.getItem('es_view_' + this.subdomain);
                if (saved && ['calendar', 'list'].includes(saved)) {
                    this.currentView = saved;
                    this.updateOuterContainers(saved, false);
                }
            } catch (e) {
                // localStorage not available
            }
        }

        // Update panel wrapper for initial view
        this.updatePanelWrapper(this.currentView);

        // Clean up early-load CSS override; Vue/inline styles now have correct values
        document.documentElement.removeAttribute('data-es-view');

        // A load that failed for want of a connection is tried again when there is one.
        window.addEventListener('online', () => {
            if (this.loadFailed) {
                this.retryLoad();
            }
        });

        if (this.isLoadingEvents) {
            // Ajax mode: fetch events, then init popups after data loads. The list layout needs the
            // wide (row-capped) upcoming set on first load, not just the current month grid.
            if (this.currentView === 'list') {
                this.fetchCalendarEvents({ skipMonthFilter: true });
            } else {
                this.fetchCalendarEvents();
            }
        } else {
            // Graphic mode: data already server-rendered
            this.$nextTick(() => {
                const showPastEventsBtn = document.getElementById('showPastEventsBtn');
                const pastEvents = document.querySelectorAll('.past-event');

                if (pastEvents.length > 0) {
                    showPastEventsBtn?.classList.remove('hidden');

                    if (showPastEventsBtn) {
                        showPastEventsBtn.onclick = function() {
                            pastEvents.forEach(event => {
                                event.classList.remove('hidden');
                            });
                            showPastEventsBtn.classList.add('hidden');
                        };
                    }
                }
            });
        }

        // Handle browser back/forward for AJAX month navigation
        @if ($route === 'guest' && !request()->graphic)
        window.addEventListener('popstate', (e) => {
            const params = new URLSearchParams(window.location.search);
            const month = parseInt(params.get('month')) || {{ now()->month }};
            const year = parseInt(params.get('year')) || {{ now()->year }};
            if (month !== this.pageMonth || year !== this.pageYear) {
                this.pageMonth = month;
                this.pageYear = year;
                this.phoneDay = '';
                this.phoneShowPast = false;
                this.listDataLoaded = false;
                this.isLoadingEvents = true;
                this.fetchCalendarEventsForMonth(month, year);
            }
            if (!this.embed) {
                this.readFiltersFromUrl();
            }
        });
        @endif

        // Keep isNarrow in step with the viewport (see filterScopeIsMonth).
        if (window.matchMedia) {
            const narrowQuery = window.matchMedia('(max-width: 767.98px)');
            const onNarrowChange = (e) => { this.isNarrow = e.matches; };
            if (narrowQuery.addEventListener) {
                narrowQuery.addEventListener('change', onNarrowChange);
            } else if (narrowQuery.addListener) {
                narrowQuery.addListener(onNarrowChange);
            }
        }

        // Escape closes the filter panels wherever focus is. The search box handles its own
        // Escape first (clear, then close) and stops it from reaching here.
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && (this.showFiltersDrawer || this.showDesktopFiltersModal)) {
                this.closeFilterPanels();
            }
        });

    }
});

// How the month prints a name (role/partials/month-script).
calendarApp.directive('clamp', window.monthClamp);

if (typeof FileReader !== 'undefined') {
    calendarApp.config.globalProperties.FileReader = FileReader;
}
calendarApp.directive('list-reveal', listReveal);
const calendarAppInstance = calendarApp.mount('#calendar-app');
window.calendarVueApp = calendarAppInstance;

// The filter buttons in the schedule's header (role/partials/headers/tools): a badge on each
// says how many filters are on. The buttons are drawn by the server and shown by the stylesheet.
function updateHeroFiltersButton() {
    if (! window.calendarVueApp) return;
    const count = window.calendarVueApp.activeFilterCount;
    ['hero-filters-badge', 'hero-filters-badge-mobile'].forEach((id) => {
        const badge = document.getElementById(id);
        if (! badge) return;
        badge.textContent = count > 0 ? count : '';
        badge.hidden = ! (count > 0);
    });
}

// Initial update and watch for changes
updateHeroFiltersButton();
calendarAppInstance.$watch('activeFilterCount', updateHeroFiltersButton);
}
</script>

{{-- v-pre is load-bearing, not decoration. This block sits INSIDE #calendar-app, and a browser
     with scripting on parses <noscript> as raw text and serializes it back verbatim into
     innerHTML - which is exactly what Vue's runtime compiler is handed. Vue does not treat
     <noscript> as raw text, so every mustache in here is compiled as a template expression, and
     Blade's {{ }} escaping does not help: it escapes HTML entities and leaves "{" and "}" alone.
     Event names, venue names and descriptions are all written by somebody else - on a curator
     they come from the source schedules, and venues are invented by calendar sync - so without
     this an event named "{{ constructor... }}" runs as JavaScript on the schedule's public page.
     CSP unsafe-eval is on by design (Vue template compilation), so it does not block this.

     The list is EventRepo::upcomingForGuest(): the schedule's next 50 public events, each dated by
     the occurrence it next happens on - the same list its title and description read. It used to
     be the whole month's grid plus every recurring series ever created, with a heavy eager load
     per row, on every schedule page for a list only a crawler reads. --}}
<noscript v-pre>
    @if (! empty($upcoming) && $upcoming->isNotEmpty())
    @php $noscriptLang = isset($role) ? $role->displayLanguageCode() : 'en'; @endphp
    <ul>
        @foreach ($upcoming as $noscriptItem)
        @php
            $noscriptEvent = $noscriptItem['event'];
            $noscriptName = $noscriptEvent->nameInLanguage($noscriptLang, $role ?? null);
            $noscriptShort = $noscriptEvent->shortDescriptionInLanguage($noscriptLang, $role ?? null);
            $noscriptDirLang = $noscriptEvent->creatorRole?->language_code ?: $noscriptLang;
            $noscriptVenue = $noscriptEvent->getVenueDisplayName(true, $noscriptLang);
        @endphp
        {{-- Undated: a series is one page (its canonical), and getGuestUrl() would link it at its
             first date, which may no longer be an occurrence and then only redirects here. --}}
        <li style="margin-bottom: 1rem;">
            <a href="{{ $noscriptEvent->getUndatedGuestUrl($role?->subdomain ?? $noscriptEvent->roles->first()?->subdomain) }}">
                <strong dir="{{ content_dir_for_language($noscriptName, $noscriptDirLang) }}">{{ $noscriptName }}</strong>
            </a>
            <br>
            {{ $noscriptEvent->localStartsAt(true, $noscriptItem['date']) }}
            @if ($noscriptVenue)
                - <span dir="{{ content_dir_for_language($noscriptVenue, $noscriptEvent->venue?->language_code ?: $noscriptDirLang) }}">{{ $noscriptVenue }}</span>
            @endif
            @if ($noscriptShort)
                <br>
                <span dir="{{ content_dir_for_language($noscriptShort, $noscriptDirLang) }}">{{ Str::limit($noscriptShort, 100) }}</span>
            @endif
        </li>
        @endforeach
    </ul>
    @endif
</noscript>
</div>
