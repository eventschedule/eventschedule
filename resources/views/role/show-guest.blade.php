<x-app-guest-layout :role="$role" :fonts="$fonts" :has-inline-lang-toggle="$role->headerStyle() !== 'banner'" :ad-slot="true" :banner-bar="true" :cart="true" :page-title="isset($selectedGroup) && $selectedGroup ? $selectedGroup->translatedName() : null" :upcoming="$upcoming ?? null" :schedule-home="! (isset($selectedGroup) && $selectedGroup)" :mobile-banner-image="(request()->embed || request()->graphic) ? null : $role->backgroundImageUrl(960, pageWidth: true)">

  @php
   $isRtl = is_rtl();
   $accentColor = (isset($selectedGroup) && $selectedGroup && $selectedGroup->role)
       ? ($selectedGroup->role->accent_color ?? '#4E81FA')
       : ($role->accent_color ?? '#4E81FA');
   $contrastColor = accent_contrast_color($accentColor);

   // Precondition for the Remove-video control, resolved once. VideoUtils::canRemoveVideo() runs
   // an isEditor() query per call, and the carousel alone can draw dozens of videos, so calling it
   // per video put a query per card on the page for every signed-in visitor. This is a strict
   // subset of that rule - the partial still calls canRemoveVideo() and remains the authority - so
   // the view can only ever be more restrictive, never show a button the endpoint would refuse.
   $canRemoveVideos = auth()->check() && ! is_demo_mode() && auth()->user()->isEditor($role->subdomain);

   // The schedule's own photo gallery, on a paid plan. Not on the image the ?graphic= render
   // captures, where a photo grid would crowd the event list it exists to show.
   $galleryImages = (! request()->graphic && $role->showsGallery()) ? $role->galleryImages : collect();
  @endphp

  @php
    $headerStyle = $role->headerStyle();
    // Fetched here (not in the banner partial) so an empty wall flips $hasHeaderImage
    // false and the page degrades exactly like header_image = 'none'.
    $logoWallRoles = ($headerStyle === 'banner' && $role->header_image === 'logos')
        ? $role->logoWallRoles()
        : collect();
    $hasHeaderImage = $role->header_image === 'logos'
        ? $logoWallRoles->isNotEmpty()
        : (($role->header_image && $role->header_image !== 'none') || ($role->header_image_url && $role->header_image !== 'none'));
  @endphp

  @if ($role->profile_image_url && !$hasHeaderImage && $headerStyle === 'banner')
  <div class="pt-8"></div>
  @endif

  <script {!! nonce_attr() !!}>
  (function() {
      // A ?layout= on the URL is an explicit instruction and outranks whatever this
      // visitor last toggled, so skip the saved preference entirely when one is present.
      @if (! requested_event_layout())
      var serverDefault = '{{ $role->activeEventLayout() }}';
      try {
          var saved = localStorage.getItem('es_view_{{ $role->subdomain }}');
          if (saved && saved !== serverDefault && (saved === 'calendar' || saved === 'list')) {
              document.documentElement.dataset.esView = saved;
          }
      } catch (e) {}
      @endif
  })();
  </script>
  <style {!! nonce_attr() !!}>
  html[data-es-view] [data-view-width] { transition: none !important; }
  html[data-es-view="calendar"] [data-view-width] { max-width: 200rem !important; }
  html[data-es-view="list"] [data-view-width] { max-width: 56rem !important; }
  {{-- The next-event card leads the MONTH, which names no next event (the grid on a wide
       screen, the small month on a phone). In the list view it stands aside: the list
       opens on today and what comes next itself, as large cards from a tablet up and as rows
       on a phone, and the card above it said the same event twice. data-view is the
       server's layout, then whatever the list's app switches to (updateOuterContainers());
       the other two rules are for the moment before that app has started. --}}
  [data-lead-wrap][data-view="list"] { display: none; }
  html[data-es-view="list"] [data-lead-wrap] { display: none; }
  html[data-es-view="calendar"] [data-lead-wrap] { display: block; }
  html[data-es-view="list"] #toggle-calendar-btn { background-color: {{ $accentColor }} !important; color: {{ $contrastColor }} !important; }
  html[data-es-view="list"] #toggle-list-btn { background-color: transparent !important; color: #1e1e1e !important; }
  html[data-es-view="calendar"] #toggle-list-btn { background-color: {{ $accentColor }} !important; color: {{ $contrastColor }} !important; }
  html[data-es-view="calendar"] #toggle-calendar-btn { background-color: transparent !important; color: #1e1e1e !important; }
  html.dark[data-es-view="list"] #toggle-list-btn,
  html.dark[data-es-view="calendar"] #toggle-calendar-btn { color: #ffffff !important; }
  html[data-es-view="list"] #month-year-title { display: none !important; }
html[data-es-view="list"] #month-nav-controls { display: none !important; }
html[data-es-view="list"] #gp-calendar {
      background: transparent !important;
      backdrop-filter: none !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      border: none !important;
      padding: 0 !important;
  }
  html[data-es-view="list"] body { background-attachment: fixed !important; }
  html[data-es-view="calendar"] body { background-attachment: scroll !important; }
  </style>

  <main>
    {{-- The announcement bar renders in layouts/app-guest.blade.php, above the language
         switcher: see AppGuestLayout::$bannerBar --}}
    {{-- Compact renders as a full-width bar at the very top, outside the content container --}}
    @if ($headerStyle !== 'banner')
        @include('role.partials.headers.' . $headerStyle)
    @endif
    <div>
      <div class="container mx-auto pt-3 md:pt-4 pb-3 md:pb-10 px-5 md:mt-0 relative z-10"
      >
        {{-- Mobile background wrapper - covers header and carousel only. It is the page's LCP
             element on a phone, so an upload is painted from its 960 derivative (a built-in
             background is already a phone-sized WebP), and the layout preloads the same URL
             (AppGuestLayout::$mobileBannerImage). An animated upload is its original, which
             moves (pageWidth). --}}
        @php
            $mobileBannerUrl = request()->embed ? null : $role->backgroundImageUrl(960, pageWidth: true);
        @endphp
        @if ($mobileBannerUrl)
        <div class="relative {{ $headerStyle === 'banner' ? '-mt-10 pt-10' : '' }} md:m-0 md:p-0">
            {{-- The upward bleed is banner-only. Banner leaves that strip empty (just the
                 layout's language-switcher row), but compact puts its full-width bar there, and
                 this container's z-10 paints over it - so under compact the image starts at the
                 container's top edge instead: -top-3 cancels the mobile pt-3, landing it flush
                 against the bar's bottom border. --}}
            <div class="absolute {{ $headerStyle === 'banner' ? '-top-40' : '-top-3' }} -bottom-3 left-1/2 -translate-x-1/2 w-screen bg-cover bg-no-repeat bg-top md:hidden -z-10"
                 style="background-image: url('{{ css_url($mobileBannerUrl) }}');"></div>
        @endif
        @if ($headerStyle === 'banner')
        @include('role.partials.headers.banner')
        @endif

        @if (! $role->isTalent() && ! $role->hide_videos)
        @php
          // Filter events for upcoming events with videos (only on main role page, not event pages)
          $upcomingEventsWithVideos = collect();
          if (!$event) {
            // $carouselEvents is a dedicated bounded query (upcoming/ongoing events with talent
            // videos) so this promo strip works across months without loading the full event set.
            $upcomingEvents = ($carouselEvents ?? collect())->filter(function($event) {
              if (!$event->starts_at) return false;
              if (Carbon\Carbon::parse($event->starts_at)->isAfter(now())) return true;
              return $event->duration >= 24 && $event->getEndDateTime()->isAfter(now());
            });
            
            foreach ($upcomingEvents as $upcomingEvent) {
              $videoRoles = $upcomingEvent->roles->filter(function($eventRole) {
                return $eventRole->isTalent() && $eventRole->getFirstVideoUrl();
              });
              
              if ($videoRoles->count() > 0) {
                $upcomingEventsWithVideos->push([
                  'event' => $upcomingEvent,
                  'video_roles' => $videoRoles
                ]);
              }
            }
            
            // Sort events by start date (earliest first)
            $upcomingEventsWithVideos = $upcomingEventsWithVideos->sortBy(function($eventData) {
              return $eventData['event']->starts_at;
            });
          }
        @endphp

        @if($upcomingEventsWithVideos && $upcomingEventsWithVideos->count() > 0)
        @php
          // For RTL, we need to reverse the order of videos to show earliest first
          if (is_rtl()) {
            // For RTL, we want earliest events first, so we don't reverse the collection
            // But we do need to reverse the video roles within each event to show earliest videos first
            $upcomingEventsWithVideos = $upcomingEventsWithVideos->map(function($eventData) {
              $eventData['video_roles'] = $eventData['video_roles']->reverse();
              return $eventData;
            });
          }
        @endphp
        @foreach($upcomingEventsWithVideos as $eventData)
        @endforeach
        <div id="gp-video-carousel" class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl px-6 lg:px-16 py-6 flex flex-col gap-6 mt-6 transition-[max-width] duration-300 ease-in-out mx-auto" data-view-width style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}">
          <!-- Carousel Container -->
          <div class="relative group">
            <!-- Carousel Track -->
            <div id="events-carousel" class="flex overflow-x-auto scrollbar-hide gap-6 pb-4 pt-4 {{ $isRtl ? 'rtl' : '' }}">
              @foreach($upcomingEventsWithVideos as $eventData)
                @foreach($eventData['video_roles'] as $videoRole)
                  @php
                    // The first PLAYABLE video, not simply the first: getFirstVideoUrl() returns
                    // index 0 whatever it holds, so a single unparseable entry would drop the whole
                    // card even when the act has a perfectly good second video.
                    $carouselVideoUrl = null;
                    $carouselEmbedUrl = null;
                    foreach ($videoRole->decodeLinks('youtube_links') as $carouselLink) {
                        $carouselEmbedUrl = \App\Utils\UrlUtils::getYouTubeEmbed($carouselLink->url);
                        if ($carouselEmbedUrl) {
                            $carouselVideoUrl = $carouselLink->url;
                            break;
                        }
                    }
                  @endphp
                  @continue (! $carouselEmbedUrl)
                  @php
                    // Below the @continue on purpose: these depend on the event, not the video
                    // role, and this carousel can draw dozens of video roles that never render.
                    $carouselLang = $role->displayLanguageCode();
                    $carouselName = $eventData['event']->nameInLanguage($carouselLang, $role);
                    $carouselVenue = $eventData['event']->getVenueDisplayName(true, $carouselLang);
                  @endphp
                  <div class="carousel-item flex-shrink-0 w-full sm:w-80 bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden group/card">
                    <!-- Video, loaded once the visitor wants it (components/consent-embed) -->
                    <x-consent-embed :src="$carouselEmbedUrl" :title="$videoRole->translatedName().' - YouTube video'" frame-class="w-full h-48 object-cover" :poster="\App\Utils\UrlUtils::getYouTubeThumbnail($carouselVideoUrl)" />

                    <!-- Event details below video -->
                    <div class="p-4">
                      <a href="{{ $eventData['event']->getGuestUrl($role->subdomain) }}" class="block" data-funnel="list_tap">
                        <h2 class="text-gray-900 dark:text-gray-100 font-semibold text-lg mb-2 line-clamp-1 group-hover/card:text-blue-600 transition-colors duration-200" dir="{{ content_dir_for_language($carouselName, $carouselLang) }}">
                          {{ $carouselName }}
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1 group-hover/card:text-gray-700 dark:group-hover/card:text-gray-300 transition-colors duration-200" dir="{{ content_dir_for_language($carouselVenue, $carouselLang) }}">
                          {{ $carouselVenue }}
                        </p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs group-hover/card:text-gray-600 dark:group-hover/card:text-gray-300 transition-colors duration-200">
                          {{ $eventData['event']->localStartsAt(true, request()->date) }}
                        </p>
                      </a>
                      {{-- Outside the anchor above: a button nested in a link is invalid HTML. Below
                           the iframe rather than over it, because the carousel's nav buttons and
                           their click-blocking overlays own the card's edges. --}}
                      @if ($canRemoveVideos)
                        @include('partials.remove-video-button', [
                          'schedule' => $role,
                          'target' => $videoRole,
                          'videoUrl' => $carouselVideoUrl,
                          'class' => 'mt-2',
                        ])
                      @endif
                    </div>
                  </div>
                @endforeach
              @endforeach
            </div>
            
            <!-- Navigation arrows -->
            <button id="carousel-prev" aria-label="Previous" class="absolute {{ $isRtl ? 'right-2' : 'left-2' }} top-1/2 transform -translate-y-1/2 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 rounded-full p-2 shadow-md transition-all duration-200 opacity-100 z-20">
              <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}"></path>
              </svg>
            </button>
            <button id="carousel-next" aria-label="Next" class="absolute {{ $isRtl ? 'left-2' : 'right-2' }} top-1/2 transform -translate-y-1/2 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-800 rounded-full p-2 shadow-md transition-all duration-200 opacity-100 z-20">
              <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"></path>
              </svg>
            </button>
            
            <!-- Invisible overlays to prevent video clicks when buttons are faded -->
            <div id="carousel-overlay-left" class="absolute {{ $isRtl ? 'right-2' : 'left-2' }} top-1/2 transform -translate-y-1/2 w-10 h-10 bg-transparent z-15 pointer-events-none transition-opacity duration-200 rounded-full"></div>
            <div id="carousel-overlay-right" class="absolute {{ $isRtl ? 'left-2' : 'right-2' }} top-1/2 transform -translate-y-1/2 w-10 h-10 bg-transparent z-15 pointer-events-none transition-opacity duration-200 rounded-full"></div>
          </div>
        </div>
        @endif
        @endif

        {{-- Mobile Filters Button (beneath video carousel) - visibility controlled by JS in calendar.blade.php --}}
        @if(!$event)
        <button id="hero-filters-btn-mobile"
                data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                class="md:hidden mt-3 mb-1 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5
                       border border-gray-300 dark:border-gray-600 rounded-2xl
                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                       text-base font-semibold {{ $isRtl ? 'rtl' : '' }}"
                style="display: none;">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
            </svg>
            {{ $role->customLabel('filters') }}
            <span id="hero-filters-badge-mobile"
                  class="ms-1 px-1.5 py-0.5 text-xs bg-[var(--brand-button-bg)] text-white rounded-full hidden"></span>
        </button>
        @endif

        @if ($mobileBannerUrl)
        </div>
        @endif

      <style {!! nonce_attr() !!}>
        /* Custom animated tooltips for social icons */
        .social-tooltip {
          position: relative;
        }

        .social-tooltip::before,
        .social-tooltip::after {
          position: absolute;
          left: 50%;
          transform: translateX(-50%);
          opacity: 0;
          visibility: hidden;
          transition: all 0.15s ease-out;
          pointer-events: none;
          z-index: 50;
        }

        /* Tooltip text bubble */
        .social-tooltip::before {
          content: attr(data-tooltip);
          bottom: calc(100% + 8px);
          padding: 6px 10px;
          background: rgba(17, 24, 39, 0.95);
          color: #fff;
          font-size: 13px;
          font-weight: 500;
          border-radius: 6px;
          white-space: nowrap;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
          transform: translateX(-50%) translateY(4px);
        }

        /* Arrow/caret pointing down */
        .social-tooltip::after {
          content: '';
          bottom: calc(100% + 2px);
          border: 6px solid transparent;
          border-top-color: rgba(17, 24, 39, 0.95);
          transform: translateX(-50%) translateY(4px);
        }

        /* Show on hover with animation */
        .social-tooltip:hover::before,
        .social-tooltip:hover::after {
          opacity: 1;
          visibility: visible;
          transform: translateX(-50%) translateY(0);
        }

        /* Dark mode adjustments */
        .dark .social-tooltip::before {
          background: rgba(255, 255, 255, 0.95);
          color: #111827;
        }

        .dark .social-tooltip::after {
          border-top-color: rgba(255, 255, 255, 0.95);
        }

        @keyframes view-toggle-bounce-expand {
            0%   { transform: scaleX(1); }
            40%  { transform: scaleX(1.008); }
            70%  { transform: scaleX(0.997); }
            100% { transform: scaleX(1); }
        }
        @keyframes view-toggle-bounce-shrink {
            0%   { transform: scaleX(1); }
            40%  { transform: scaleX(0.992); }
            70%  { transform: scaleX(1.003); }
            100% { transform: scaleX(1); }
        }
        .calendar-panel-border {
          background: rgba(255,255,255,0.95) !important;
          backdrop-filter: blur(4px) !important;
          border-radius: 1rem !important;
          margin-top: 1rem;
        }
        .dark .calendar-panel-border {
          background: rgba(30,30,30,0.95) !important;
        }
        .calendar-panel-border-transparent {
          background: transparent !important;
          border-radius: 0 !important;
          box-shadow: none !important;
          border: none !important;
          margin-top: 1rem;
        }
        @media (max-width: 767px) {
          .calendar-panel-border {
            background: transparent !important;
            backdrop-filter: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border: none !important;
            margin-top: 0 !important;
          }
          .dark .calendar-panel-border {
            background: transparent !important;
            border: none !important;
          }
        }
      </style>

      {{-- shownSponsorLogos(), not getSponsorLogos(): the owner can switch the section off without
           deleting the sponsors (Engagement > Sponsors), and the margin test below reads the same list. --}}
      @php $sponsorLogos = $role->shownSponsorLogos(); @endphp

      @if (!empty($sponsorLogos))
      <div class="mt-2 md:mt-6 mb-6">
          <x-sponsor-grid
              :sponsors="$sponsorLogos"
              :title="$role->translatedSponsorSectionTitle()"
              :background="$role->sponsorBackground()"
              :maxWidth="$role->activeEventLayout() === 'list' ? '56rem' : '200rem'" />
      </div>
      @endif

      {{-- The venue map's band (components/venue-map): after the sponsors and above the events,
           in the same column as both. Only where the owner switched it on, its first pass of
           address lookups is done and two venues have a position (VenueMap::band()), and never
           in an embed, which is the list alone, nor in the picture ?graphic=1 renders. --}}
      @php
        $venueMapBand = (request()->embed || request()->graphic)
            ? null
            : \App\Services\VenueMap::band($role, $selectedGroup ?? null, $upcoming ?? null);
      @endphp
      @if ($venueMapBand)
      <div class="{{ empty($sponsorLogos) ? 'mt-2 md:mt-6' : '' }} mb-6 px-0 md:px-6 lg:px-16 mx-auto transition-[max-width] duration-300 ease-in-out" data-view-width data-map-wrap
           style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}">
          <x-venue-map :role="$role" :band="$venueMapBand" :group="$selectedGroup ?? null" />
      </div>
      @endif

      <section id="gp-events" aria-label="{{ $role->customLabel('events') }}">
      {{-- What is next, said by the server above the MONTH, which names no next event itself
           (in the list view this card stands aside, see the style block at the top): the
           month is fetched by the page's script, and until it arrives the page has a header
           and a grey placeholder.
           The first of the schedule's upcoming events the page already has (EventRepo::
           upcomingForGuest(), which leaves out anything draft, private or behind a password),
           inside the category the address names. Not in an embed, which is the list alone.

           Its picture has a box of its own shape from the start, so nothing moves when it
           arrives, and it does not ask for high priority: the header's picture has that. --}}
      @php
        // ?category[]=x is an array, and a cast of it to a string is an error page.
        $leadCategory = is_scalar(request('category')) ? (string) request('category') : '';
        // The lead is chosen for the sub-schedule and the category of the address. An address
        // that narrows by anything else (a shared "Room B" link, ?custom_1=...) gets none: it
        // would name the schedule's next event whatever room it is in.
        $leadNarrowed = collect(request()->query())->contains(
            fn ($value, $key) => str_starts_with((string) $key, 'custom_') && $value !== null && $value !== '' && $value !== []
        );
        // Not in an embed, which is the list alone, nor in the picture ?graphic=1 renders.
        $leadRow = (request()->embed || request()->graphic || $leadNarrowed)
            ? null
            : app(\App\Repos\EventRepo::class)->leadOf($upcoming ?? collect(), $leadCategory);
      @endphp
      @if ($leadRow)
        @php
          $leadEvent = $leadRow['event'];
          $leadLang = $role->displayLanguageCode();
          $leadZone = $leadEvent->scheduleTimezone();
          $leadStart = $leadEvent->getStartDateTime($leadRow['date'], true, $leadZone);
          $leadToday = \Carbon\Carbon::now($leadZone)->format('Y-m-d');
          $leadDay = $leadStart->format('Y-m-d');
          $leadWord = $leadDay === $leadToday ? __('messages.today')
              : ($leadDay === \Carbon\Carbon::now($leadZone)->addDay()->format('Y-m-d') ? __('messages.tomorrow') : null);
          $leadName = $leadEvent->nameInLanguage($leadLang, $role);
          // The place as the list names it, unless this IS the place's own schedule.
          $leadWhere = ($leadEvent->venue && $leadEvent->venue->id === $role->id) ? null : ($leadEvent->getVenueDisplayName(true, $leadLang) ?: null);
          $leadTime = $leadEvent->getStartEndTime($leadRow['date'], get_use_24_hour_time($role));
          $leadImage = $leadEvent->flyer_image_url ? $leadEvent->getImageUrl(960) : null;
          // ONE query, for this one event's tickets, said here so it is not a side effect of
          // reading them: the fifty events of $upcoming are deliberately loaded without theirs
          // (GuestScheduleSchemaTest holds that the list never costs a query an event).
          $leadEvent->loadMissing('tickets');
          // The address keeps what the page was narrowed by, as a row's does, so the event's
          // way back returns here.
          $leadQuery = array_filter([
              'category' => $leadCategory,
              'schedule' => (isset($selectedGroup) && $selectedGroup) ? $selectedGroup->slug : null,
              'layout' => requested_event_layout(),
          ], fn ($value) => $value !== null && $value !== '');
          $leadUrl = $leadEvent->getGuestUrl($role->subdomain, $leadEvent->days_of_week ? $leadRow['date'] : null);
          $leadUrl .= $leadQuery ? (str_contains($leadUrl, '?') ? '&' : '?').http_build_query($leadQuery) : '';
        @endphp
        <div class="mt-2 md:mt-6 mb-4 px-0 md:px-6 lg:px-16 mx-auto transition-[max-width] duration-300 ease-in-out" data-view-width data-lead-wrap data-view="{{ $role->activeEventLayout() }}"
             style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}">
          <a id="gp-next-event" class="gk-panel gk-lead {{ $leadImage ? '' : 'gk-lead-bare' }} bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm {{ $role->isRtl() ? 'rtl' : '' }}"
             href="{{ $leadUrl }}" data-funnel="list_tap">
            @if ($leadImage)
              {{-- lazy: in the list view this card is display:none, and a picture that is not
                   lazy is fetched all the same. Shown, it is at the top and loads at once. --}}
              <img class="gk-lead-img" src="{{ $leadImage }}" alt="" width="960" height="540" loading="lazy" decoding="async">
            @endif
            <span class="gk-lead-body">
              <span class="gk-lead-when">
                @if ($leadWord)<b>{{ $leadWord }}</b>@endif
                <time datetime="{{ $leadStart->format('Y-m-d\TH:i:sP') }}">{{ \App\Utils\DateUtils::dayLabel($leadStart) }}</time>
              </span>
              <span class="gk-lead-title" dir="{{ content_dir_for_language($leadName, $leadLang) }}">{{ $leadName }}</span>
              <span class="gk-lead-where">
                @if ($leadTime)<bdi dir="ltr">{{ $leadTime }}</bdi>@endif
                @if ($leadWhere)<span>{{ $leadWhere }}</span>@endif
              </span>
              @include('partials.guest-ticket-chips', ['chipEvent' => $leadEvent, 'chipDate' => $leadRow['date'], 'chipRole' => $role])
            </span>
          </a>
        </div>
      @endif
      <div
        class="calendar-panel-border {{ (empty($sponsorLogos) && ! $venueMapBand) ? 'mt-2 md:mt-6' : '' }} mb-6 px-0 md:px-6 lg:px-16 pt-0 md:pt-4 pb-0 md:pb-6 transition-[max-width] duration-300 ease-in-out mx-auto"
        id="gp-calendar"
        data-view-width
        style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}"
      >
        @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule'), 'eventLayout' => $role->activeEventLayout(), 'pastEvents' => $pastEvents ?? collect(), 'hide_past_events' => $role->hide_past_events])
      </div>
      </section>

      {{-- Outside the section and outside #gp-calendar, but opted into the same
           data-view-width system as every other panel in this column (the carousel, the sponsor
           grid, the calendar, the video grid): 56rem in list view, the full container in calendar
           view. The CSS at the top of this file and updateOuterContainers() in
           role/partials/calendar.blade.php both select on that attribute, so the panel follows the
           calendar on a toggle as well as on first paint.

           It used to opt OUT and hard-cap at max-w-4xl, because a 1500px-wide email form looks
           broken. That is still true - the cap just moved INSIDE, onto the form row in
           partials/subscribe-panel.blade.php, so the card edge can line up with the calendar while
           the inputs stay readable. Do not put a width cap back on this wrapper: the panel sits
           directly under the calendar, and a width the calendar does not share reads as a
           misaligned card. RoleSubscriberTest pins both halves.

           The panelClass below tracks the column's INSET for the same reason it tracks its width.
           px-6 lg:px-16 is copied verbatim from the carousel, the video grid, the calendar wrapper
           and the banner header's inner <header>; it used to be p-6 sm:p-8, which put "Stay up to
           date" 32px to the left of "September 2026" on every screen at lg or wider. py-6 sm:py-8
           is the old p-6 sm:p-8 with only the horizontal half changed. --}}
      <div class="mb-6 mx-auto w-full transition-[max-width] duration-300 ease-in-out"
           data-view-width
           style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}">
        @include('partials.subscribe-panel', ['panelClass' => 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl px-6 lg:px-16 py-6 sm:py-8'])
      </div>

      {{-- The schedule's photo gallery: below the calendar and its subscribe panel, because the
           events are what the page is for, and in the same data-view-width column as every other
           panel here (56rem in list view, the full container in calendar view). --}}
      @if ($galleryImages->isNotEmpty())
      <section id="gp-gallery" aria-labelledby="es-gallery-title"
          class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl px-6 lg:px-16 py-6 sm:py-8 mb-6 transition-[max-width] duration-300 ease-in-out mx-auto {{ $role->isRtl() ? 'rtl' : '' }}"
          data-view-width style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}">
        @include('partials.gallery-card', ['galleryImages' => $galleryImages, 'galleryVariant' => 'schedule', 'galleryName' => $role->translatedName(), 'galleryLabel' => $role->customLabel('gallery'), 'galleryPriority' => false, 'accentColor' => $accentColor])
      </section>
      @endif

      @if ($role->youtube_links && $role->youtube_links != '[]')
        @php
          // Filtered BEFORE counting: the column count and the "is there anything to show" test
          // below both have to reflect the cards that actually render, or an entry that cannot be
          // turned into an embed reserves a grid column for nothing - and a list where every entry
          // fails would draw an empty panel.
          $videoLinks = array_values(array_filter(
              $role->decodeLinks('youtube_links'),
              fn ($l) => (bool) \App\Utils\UrlUtils::getYouTubeEmbed($l->url)
          ));
          $videoCount = count($videoLinks);
          $gridCols = min($videoCount, $role->getVideoColumns());
        @endphp
        @if ($videoCount > 0)
          <div
              id="gp-videos"
              class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl px-6 lg:px-16 py-6 flex flex-col gap-6 mb-6 transition-[max-width] duration-300 ease-in-out mx-auto" data-view-width style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}"
            >
              <div class="grid grid-cols-1 md:grid-cols-{{ $gridCols }} gap-8">
              @foreach ($videoLinks as $link)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                  <x-consent-embed :src="\App\Utils\UrlUtils::getYouTubeEmbed($link->url)" :title="$role->translatedName().' - YouTube video'" frame-class="w-full" :frame-style="'height:'.$role->getVideoHeight().'px'" :poster="\App\Utils\UrlUtils::getYouTubeThumbnail($link->url)" />
                  @if ($canRemoveVideos)
                    @include('partials.remove-video-button', [
                      'schedule' => $role,
                      'target' => $role,
                      'videoUrl' => $link->url,
                      'class' => 'px-3 py-2 text-end',
                    ])
                  @endif
                </div>
              @endforeach
            </div>          
          </div>
        @endif
      @endif

    </div>
  </main>

<style {!! nonce_attr() !!}>
[v-cloak] {
  display: none !important;
}
/* Carousel styles */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.carousel-item {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.carousel-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Navigation button styles */
#carousel-prev,
#carousel-next {
  transition: opacity 0.3s ease, background-color 0.2s ease;
  z-index: 10;
}

#carousel-prev:hover,
#carousel-next:hover {
  background-color: white !important;
}
.dark #carousel-prev:hover,
.dark #carousel-next:hover {
  background-color: rgb(var(--ap-surface)) !important;
}

/* Disabled state for navigation buttons */
#carousel-prev[style*="opacity: 0.3"],
#carousel-next[style*="opacity: 0.3"] {
  cursor: not-allowed;
  pointer-events: none;
}
</style>
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
            // Carousel functionality
        const carousel = document.getElementById('events-carousel');
        const prevButton = document.getElementById('carousel-prev');
        const nextButton = document.getElementById('carousel-next');
        
        if (carousel && prevButton && nextButton) {
            const carouselItems = carousel.querySelectorAll('.carousel-item');
            const isRtl = carousel.classList.contains('rtl');
            
            // Only show navigation if there are multiple items
            if (carouselItems.length <= 1) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
                return;
            }
            
            // Make buttons visible by default for multiple items
            prevButton.style.display = 'block';
            nextButton.style.display = 'block';
            
            function updateNavigationButtons() {
                const scrollLeft = carousel.scrollLeft;
                const scrollWidth = carousel.scrollWidth;
                const clientWidth = carousel.clientWidth;
                const maxScroll = scrollWidth - clientWidth;
                
                // Get overlay elements
                const overlayLeft = document.getElementById('carousel-overlay-left');
                const overlayRight = document.getElementById('carousel-overlay-right');
                
                // For RTL, we need to check the opposite conditions
                if (isRtl) {
                    // In RTL, scrollLeft uses negative values!
                    // scrollLeft: 0 = start, scrollLeft: -maxScroll = end
                    const isAtStart = scrollLeft >= -1; // Allow for small rounding errors
                    const isAtEnd = scrollLeft <= -maxScroll + 1; // Allow for small rounding errors
                    
                    // For RTL: when at start (scrollLeft ≈ 0), prev button should fade (can't go back)
                    // For RTL: when at end (scrollLeft ≈ -maxScroll), next button should fade (can't go forward)
                    prevButton.style.opacity = isAtStart ? '0.3' : '1'; // Prev button fades when at start
                    nextButton.style.opacity = isAtEnd ? '0.3' : '1';   // Next button fades when at end
                    
                    // Update overlays for RTL
                    if (overlayLeft) {
                        overlayLeft.style.pointerEvents = isAtStart ? 'auto' : 'none';
                        overlayLeft.style.opacity = isAtStart ? '0.01' : '0'; // Tiny opacity to make it clickable
                    }
                    if (overlayRight) {
                        overlayRight.style.pointerEvents = isAtEnd ? 'auto' : 'none';
                        overlayRight.style.opacity = isAtEnd ? '0.01' : '0'; // Tiny opacity to make it clickable
                    }
                } else {
                    // Standard LTR logic
                    const isAtStart = scrollLeft <= 1; // Allow for small rounding errors
                    const isAtEnd = scrollLeft >= maxScroll - 1; // Allow for small rounding errors
                    
                    prevButton.style.opacity = isAtStart ? '0.3' : '1';
                    nextButton.style.opacity = isAtEnd ? '0.3' : '1';
                    
                    // Update overlays for LTR
                    if (overlayLeft) {
                        overlayLeft.style.pointerEvents = isAtStart ? 'auto' : 'none';
                        overlayLeft.style.opacity = isAtStart ? '0.01' : '0'; // Tiny opacity to make it clickable
                    }
                    if (overlayRight) {
                        overlayRight.style.pointerEvents = isAtEnd ? 'auto' : 'none';
                        overlayRight.style.opacity = isAtEnd ? '0.01' : '0'; // Tiny opacity to make it clickable
                    }
                }
            }
        
        // Consistent scrolling for both RTL and LTR
        function scrollCarousel(direction) {
            // Get the actual width of a carousel item (responsive)
            const firstItem = carousel.querySelector('.carousel-item');
            const itemWidth = firstItem ? firstItem.offsetWidth + 24 : 320; // 24px is the gap (gap-6 = 1.5rem = 24px)
            const scrollAmount = itemWidth * 1; // Scroll by 1.25 item widths
            
            if (isRtl) {
                // For RTL, reverse the direction
                const actualScrollAmount = direction === 'next' ? -scrollAmount : scrollAmount;
                carousel.scrollBy({
                    left: actualScrollAmount,
                    behavior: 'smooth'
                });
            } else {
                // For LTR, use normal direction
                const actualScrollAmount = direction === 'next' ? scrollAmount : -scrollAmount;
                carousel.scrollBy({
                    left: actualScrollAmount,
                    behavior: 'smooth'
                });
            }
        }
        
        prevButton.addEventListener('click', function(e) {
            // Check if button is disabled (faded out)
            if (this.style.opacity === '0.3') {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            
            scrollCarousel('prev');
        });
        
        nextButton.addEventListener('click', function(e) {
            // Check if button is disabled (faded out)
            if (this.style.opacity === '0.3') {
                e.preventDefault();
                e.stopPropagation();
                return;
            }
            
            scrollCarousel('next');
        });
        
        // Update navigation buttons on scroll
        carousel.addEventListener('scroll', updateNavigationButtons);
        
        // Initial button state
        updateNavigationButtons();
        
        // Prevent clicks on faded buttons from propagating to videos
        function preventFadedButtonClicks(e) {
            if (this.style.opacity === '0.3') {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
        }
        
        // Add mousedown and touchstart handlers to prevent video interaction when buttons are faded
        [prevButton, nextButton].forEach(button => {
            button.addEventListener('mousedown', preventFadedButtonClicks);
            button.addEventListener('touchstart', preventFadedButtonClicks);
            button.addEventListener('pointerdown', preventFadedButtonClicks);
        });
        
        // Add event handlers for overlays to prevent video clicks
        const overlayLeft = document.getElementById('carousel-overlay-left');
        const overlayRight = document.getElementById('carousel-overlay-right');
        
        function preventVideoClicks(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
        
        if (overlayLeft) {
            overlayLeft.addEventListener('click', preventVideoClicks);
            overlayLeft.addEventListener('mousedown', preventVideoClicks);
            overlayLeft.addEventListener('touchstart', preventVideoClicks);
            overlayLeft.addEventListener('pointerdown', preventVideoClicks);
        }
        
        if (overlayRight) {
            overlayRight.addEventListener('click', preventVideoClicks);
            overlayRight.addEventListener('mousedown', preventVideoClicks);
            overlayRight.addEventListener('touchstart', preventVideoClicks);
            overlayRight.addEventListener('pointerdown', preventVideoClicks);
        }
        
        // For RTL, we don't need to set initial scroll position since scrollBy will work correctly
    }
});
</script>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // Hero filters button (desktop)
    var heroFiltersBtn = document.getElementById('hero-filters-btn');
    if (heroFiltersBtn) {
        heroFiltersBtn.addEventListener('click', function() {
            if (window.calendarVueApp) {
                window.calendarVueApp.showDesktopFiltersModal = true;
            }
        });
    }

    // Hero filters button (mobile)
    var heroFiltersBtnMobile = document.getElementById('hero-filters-btn-mobile');
    if (heroFiltersBtnMobile) {
        heroFiltersBtnMobile.addEventListener('click', function() {
            if (window.calendarVueApp) {
                window.calendarVueApp.showFiltersDrawer = true;
            }
        });
    }

    // Toggle list view button
    var toggleListBtn = document.getElementById('toggle-list-btn');
    if (toggleListBtn) {
        toggleListBtn.addEventListener('click', function() {
            if (window.calendarVueApp) {
                window.calendarVueApp.toggleView('list');
            }
        });
    }

    // Toggle calendar view button
    var toggleCalendarBtn = document.getElementById('toggle-calendar-btn');
    if (toggleCalendarBtn) {
        toggleCalendarBtn.addEventListener('click', function() {
            if (window.calendarVueApp) {
                window.calendarVueApp.toggleView('calendar');
            }
        });
    }
});
</script>

@if (! is_demo_mode())
    @include('partials.follow-consent-modal')
@endif

@if ($galleryImages->isNotEmpty())
    @include('partials.lightbox', ['rtl' => $role->isRtl()])
@endif

@include('partials.guest-funnel')

</x-app-guest-layout>
