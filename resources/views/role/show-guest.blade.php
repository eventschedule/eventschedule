<x-app-guest-layout :role="$role" :fonts="$fonts" :has-inline-lang-toggle="$role->headerStyle() !== 'banner'">

  @php
   $isRtl = is_rtl();
   $accentColor = (isset($selectedGroup) && $selectedGroup && $selectedGroup->role)
       ? ($selectedGroup->role->accent_color ?? '#4E81FA')
       : ($role->accent_color ?? '#4E81FA');
   $contrastColor = accent_contrast_color($accentColor);
  @endphp

  @php
    $hasHeaderImage = ($role->header_image && $role->header_image !== 'none') || ($role->header_image_url && $role->header_image !== 'none');
    $headerStyle = $role->headerStyle();
  @endphp

  @if ($role->profile_image_url && !$hasHeaderImage && $headerStyle === 'banner')
  <div class="pt-8"></div>
  @endif

  <script {!! nonce_attr() !!}>
  (function() {
      var serverDefault = '{{ $role->event_layout ?? "calendar" }}';
      try {
          var saved = localStorage.getItem('es_view_{{ $role->subdomain }}');
          if (saved && saved !== serverDefault && (saved === 'calendar' || saved === 'list')) {
              document.documentElement.dataset.esView = saved;
          }
      } catch (e) {}
  })();
  </script>
  <style {!! nonce_attr() !!}>
  html[data-es-view] [data-view-width] { transition: none !important; }
  html[data-es-view="calendar"] [data-view-width] { max-width: 200rem !important; }
  html[data-es-view="list"] [data-view-width] { max-width: 56rem !important; }
  html[data-es-view="list"] #toggle-calendar-btn { background-color: {{ $accentColor }} !important; color: {{ $contrastColor }} !important; }
  html[data-es-view="list"] #toggle-list-btn { background-color: transparent !important; color: inherit !important; }
  html[data-es-view="calendar"] #toggle-list-btn { background-color: {{ $accentColor }} !important; color: {{ $contrastColor }} !important; }
  html[data-es-view="calendar"] #toggle-calendar-btn { background-color: transparent !important; color: inherit !important; }
  html[data-es-view="list"] #month-year-title { display: none !important; }
html[data-es-view="list"] #month-nav-controls { display: none !important; }
html[data-es-view="list"] #calendar-panel-wrapper {
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
    @include('role.partials.guest-banner')
    {{-- Compact renders as a full-width bar at the very top, outside the content container --}}
    @if ($headerStyle !== 'banner')
        @include('role.partials.headers.' . $headerStyle)
    @endif
    <div>
      <div class="container mx-auto pt-3 md:pt-4 pb-3 md:pb-10 px-5 md:mt-0 relative z-10"
      >
        {{-- Mobile background wrapper - covers header and carousel only --}}
        @php
            $mobileBannerUrl = null;
            if ($role->background == 'image' && !request()->embed) {
                $mobileBannerUrl = $role->background_image
                    ? asset('images/backgrounds/' . $role->background_image . '.webp')
                    : $role->background_image_url;
            }
        @endphp
        @if ($mobileBannerUrl)
        <div class="relative {{ $headerStyle === 'banner' ? '-mt-10 pt-10' : '' }} md:m-0 md:p-0">
            <div class="absolute -top-40 -bottom-3 left-1/2 -translate-x-1/2 w-screen bg-cover bg-no-repeat bg-top md:hidden -z-10"
                 style="background-image: url('{{ $mobileBannerUrl }}');"></div>
        @endif
        @if ($headerStyle === 'banner')
        @include('role.partials.headers.banner')
        @endif

        @if (! $role->isTalent() && ! $role->hide_videos)
        @php
          // Filter events for upcoming events with videos (only on main role page, not event pages)
          $upcomingEventsWithVideos = collect();
          if (!$event) {
            $upcomingEvents = $events->filter(function($event) {
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
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl px-6 lg:px-16 py-6 flex flex-col gap-6 mt-6 transition-[max-width] duration-300 ease-in-out mx-auto" data-view-width style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}">
          <!-- Carousel Container -->
          <div class="relative group">
            <!-- Carousel Track -->
            <div id="events-carousel" class="flex overflow-x-auto scrollbar-hide gap-6 pb-4 pt-4 {{ $isRtl ? 'rtl' : '' }}">
              @foreach($upcomingEventsWithVideos as $eventData)
                @foreach($eventData['video_roles'] as $videoRole)
                  <div class="carousel-item flex-shrink-0 w-full sm:w-80 bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden group/card">
                    <!-- Video iframe -->
                    <iframe
                      class="w-full h-48 object-cover"
                      src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($videoRole->getFirstVideoUrl()) }}"
                      title="{{ $videoRole->translatedName() }} - YouTube video"
                      frameborder="0"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                      referrerpolicy="strict-origin-when-cross-origin"
                      allowfullscreen
                      loading="lazy">
                    </iframe>
                    
                    <!-- Event details below video -->
                    <div class="p-4">
                      <a href="{{ $eventData['event']->getGuestUrl($role->subdomain) }}" class="block">
                        <h2 class="text-gray-900 dark:text-gray-100 font-semibold text-lg mb-2 line-clamp-1 group-hover/card:text-blue-600 transition-colors duration-200">
                          {{ $eventData['event']->translatedName() }}
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1 group-hover/card:text-gray-700 dark:group-hover/card:text-gray-300 transition-colors duration-200">
                          {{ $eventData['event']->getVenueDisplayName() }}
                        </p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs group-hover/card:text-gray-600 dark:group-hover/card:text-gray-300 transition-colors duration-200">
                          {{ $eventData['event']->localStartsAt(true, request()->date) }}
                        </p>
                      </a>
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

      @php $sponsorLogos = $role->getSponsorLogos(); @endphp

      @if (!empty($sponsorLogos))
      <div class="mt-2 md:mt-6 mb-6">
          <x-sponsor-grid
              :sponsors="$sponsorLogos"
              :title="$role->translatedSponsorSectionTitle()"
              :maxWidth="($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem'" />
      </div>
      @endif

      <section aria-label="{{ $role->customLabel('events') }}">
      <div
        class="calendar-panel-border {{ empty($sponsorLogos) ? 'mt-2 md:mt-6' : '' }} mb-6 px-0 md:px-6 lg:px-16 pt-0 md:pt-4 pb-0 md:pb-6 transition-[max-width] duration-300 ease-in-out mx-auto"
        id="calendar-panel-wrapper"
        data-view-width
        style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}"
      >
        @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule'), 'eventLayout' => $role->event_layout ?? 'calendar', 'pastEvents' => $pastEvents ?? collect(), 'hide_past_events' => $role->hide_past_events])
      </div>
      </section>

      @if ($role->youtube_links && $role->youtube_links != '[]')
        @php
          $videoLinks = $role->decodeLinks('youtube_links');
          $videoCount = count($videoLinks);
          $gridCols = min($videoCount, $role->getVideoColumns());
        @endphp
        @if ($videoCount > 0)
          <div
              class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl px-6 lg:px-16 py-6 flex flex-col gap-6 mb-6 transition-[max-width] duration-300 ease-in-out mx-auto" data-view-width style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}"
            >
              <div class="grid grid-cols-1 md:grid-cols-{{ $gridCols }} gap-8">
              @foreach ($videoLinks as $link)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                  <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" title="{{ $role->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
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
  background-color: #252526 !important;
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
            if (window.calendarVueApp && window.calendarVueApp.dynamicFilterCount > 0) {
                window.calendarVueApp.showDesktopFiltersModal = true;
            }
        });
    }

    // Hero filters button (mobile)
    var heroFiltersBtnMobile = document.getElementById('hero-filters-btn-mobile');
    if (heroFiltersBtnMobile) {
        heroFiltersBtnMobile.addEventListener('click', function() {
            if (window.calendarVueApp && window.calendarVueApp.dynamicFilterCount > 0) {
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

</x-app-guest-layout>
