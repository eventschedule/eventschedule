<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts" :showMobileBackground="true">

  <main class="pb-20 sm:pb-0">
    @php
    $eventRole = $event->roles->where('id', $role->id)->first();
    $eventIsAccepted = $eventRole->pivot->is_accepted;
    @endphp

    @php
    $accentColor = (isset($otherRole) && $otherRole && $otherRole->isClaimed())
        ? ($otherRole->accent_color ?? '#4E81FA')
        : ((isset($selectedGroup) && $selectedGroup && $selectedGroup->role)
            ? ($selectedGroup->role->accent_color ?? '#4E81FA')
            : ($role->accent_color ?? '#4E81FA'));
    $contrastColor = accent_contrast_color($accentColor);
    @endphp

  {{-- Status alerts (full width, fixed at top) --}}
  @if ($eventIsAccepted === null)
  <div class="fixed top-0 left-0 w-full bg-amber-50 dark:bg-amber-950 border-b border-amber-200 dark:border-amber-800 py-6 z-[60]">
    <div class="container mx-auto px-5">
      <div class="flex items-center justify-center text-amber-800 dark:text-amber-200">
        <span class="text-xl font-medium">{{ __('messages.event_pending_review') }}</span>
      </div>
    </div>
  </div>
  @elseif (! $eventIsAccepted)
  <div class="fixed top-0 left-0 w-full bg-red-50 dark:bg-red-950 border-b border-red-200 dark:border-red-800 py-6 z-[60]">
    <div class="container mx-auto px-5">
      <div class="flex items-center justify-center text-red-800 dark:text-red-200">
        <span class="text-xl font-medium">{{ __('messages.event_rejected') }}</span>
      </div>
    </div>
  </div>
  @endif
  {{-- Spacer for fixed banner --}}
  @if ($eventIsAccepted === null || ! $eventIsAccepted)
  <div class="h-[68px]"></div>
  @endif

  <div class="container mx-auto max-w-5xl px-0 sm:px-5 pt-4 pb-20 sm:pb-8">
    <div class="flex flex-col gap-4 lg:grid lg:grid-cols-[380px_minmax(0,1fr)] lg:gap-10">

      {{-- LEFT COLUMN --}}
      <div class="order-2 lg:order-1 pb-4">
        <div class="flex flex-col gap-4">

        {{-- Talent/performer cards --}}
        @php
          $talentMembers = $event->members()->filter(fn($m) => $m->isClaimed() || $m->getFirstVideoUrl());
          $hasTalentImage = $talentMembers->contains(fn($m) => $m->profile_image_url);
        @endphp

        @if (!$hasTalentImage)
        @php
          $fallbackImage = null;
          $anyTalentWithImage = $event->roles->first(fn($r) => $r->isTalent() && $r->profile_image_url);
          if ($anyTalentWithImage) {
              $fallbackImage = $anyTalentWithImage->profile_image_url;
          } elseif ($event->venue && $event->venue->profile_image_url) {
              $fallbackImage = $event->venue->profile_image_url;
          } elseif ($role->profile_image_url) {
              $fallbackImage = $role->profile_image_url;
          }
        @endphp
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl overflow-hidden">
          @if ($fallbackImage)
            <img src="{{ $fallbackImage }}"
                 alt="{{ $event->translatedName() }}"
                 class="w-full aspect-square object-cover"
                 fetchpriority="high"/>
          @else
            <img src="{{ url('/images/logo.webp') }}"
                 alt="{{ config('app.name') }}"
                 class="w-full aspect-square object-contain bg-gray-50 dark:bg-gray-800 p-4"
                 fetchpriority="high"/>
          @endif
        </div>
        @endif

        @foreach ($talentMembers as $talentIndex => $each)
        @php
          $hasTalentHeader = ($each->header_image && $each->header_image !== 'none') || $each->header_image_url;
        @endphp
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl overflow-hidden">
          @if ($hasTalentHeader)
            {{-- Header banner --}}
            @if ($each->header_image && $each->header_image !== 'none')
              <picture>
                <source srcset="{{ asset('images/headers') }}/{{ $each->header_image }}.webp" type="image/webp">
                <img class="block max-h-40 w-full object-cover" src="{{ asset('images/headers') }}/{{ $each->header_image }}.png" alt="{{ $each->translatedName() }}"/>
              </picture>
            @elseif ($each->header_image_url)
              <img class="block max-h-40 w-full object-cover" src="{{ $each->header_image_url }}" alt="{{ $each->translatedName() }}"/>
            @endif
            <div class="px-5 pb-5">
              {{-- Profile image overlapping header --}}
              @if ($each->profile_image_url)
                <div class="rounded-xl w-[100px] h-[100px] -mt-[60px] bg-white/95 dark:bg-gray-900/95 p-[5px] mb-3">
                  @if ($each->isClaimed())
                    @php
                      $talentUrl = route('role.view_guest', ['subdomain' => $each->subdomain]);
                      $tQueryParams = [];
                      if (request('category')) $tQueryParams['category'] = request('category');
                      if (request('schedule')) $tQueryParams['schedule'] = request('schedule');
                      if (request('date')) {
                        $tDate = request('date');
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tDate)) {
                          $tDateParts = explode('-', $tDate);
                          $tQueryParams['month'] = (int)$tDateParts[1];
                          $tQueryParams['year'] = (int)$tDateParts[0];
                        }
                      } else {
                        if (request('month')) $tQueryParams['month'] = request('month');
                        if (request('year')) $tQueryParams['year'] = request('year');
                      }
                      if (!empty($tQueryParams)) {
                        $talentUrl .= '?' . http_build_query($tQueryParams);
                      }
                    @endphp
                    <a href="{{ $talentUrl }}">
                      <img src="{{ $each->profile_image_url }}" alt="{{ $each->translatedName() }}" class="rounded-lg w-full h-full object-cover"/>
                    </a>
                  @else
                    <img src="{{ $each->profile_image_url }}" alt="{{ $each->translatedName() }}" class="rounded-lg w-full h-full object-cover"/>
                  @endif
                </div>
              @else
                <div class="pt-4"></div>
              @endif
          @else
            <div class="p-5">
              {{-- Profile image (no header) --}}
              @if ($each->profile_image_url)
                @if ($each->isClaimed())
                  @php
                    $talentUrl = route('role.view_guest', ['subdomain' => $each->subdomain]);
                    $tQueryParams = [];
                    if (request('category')) $tQueryParams['category'] = request('category');
                    if (request('schedule')) $tQueryParams['schedule'] = request('schedule');
                    if (request('date')) {
                      $tDate = request('date');
                      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tDate)) {
                        $tDateParts = explode('-', $tDate);
                        $tQueryParams['month'] = (int)$tDateParts[1];
                        $tQueryParams['year'] = (int)$tDateParts[0];
                      }
                    } else {
                      if (request('month')) $tQueryParams['month'] = request('month');
                      if (request('year')) $tQueryParams['year'] = request('year');
                    }
                    if (!empty($tQueryParams)) {
                      $talentUrl .= '?' . http_build_query($tQueryParams);
                    }
                  @endphp
                  <a href="{{ $talentUrl }}">
                    <img src="{{ $each->profile_image_url }}" alt="{{ $each->translatedName() }}" class="w-full aspect-square object-cover rounded-xl mb-4"/>
                  </a>
                @else
                  <img src="{{ $each->profile_image_url }}" alt="{{ $each->translatedName() }}" class="w-full aspect-square object-cover rounded-xl mb-4"/>
                @endif
              @endif
          @endif

              <div class="flex flex-col gap-4">
                {{-- Name + follow/manage button --}}
                <div class="flex items-center justify-between gap-4">
                  <div class="min-w-0">
                    @if ($each->isClaimed())
                      @if (!isset($talentUrl))
                        @php
                          $talentUrl = route('role.view_guest', ['subdomain' => $each->subdomain]);
                          $tQueryParams = [];
                          if (request('category')) $tQueryParams['category'] = request('category');
                          if (request('schedule')) $tQueryParams['schedule'] = request('schedule');
                          if (request('date')) {
                            $tDate = request('date');
                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tDate)) {
                              $tDateParts = explode('-', $tDate);
                              $tQueryParams['month'] = (int)$tDateParts[1];
                              $tQueryParams['year'] = (int)$tDateParts[0];
                            }
                          } else {
                            if (request('month')) $tQueryParams['month'] = request('month');
                            if (request('year')) $tQueryParams['year'] = request('year');
                          }
                          if (!empty($tQueryParams)) {
                            $talentUrl .= '?' . http_build_query($tQueryParams);
                          }
                        @endphp
                      @endif
                      <a href="{{ $talentUrl }}" class="group inline {{ $role->isRtl() ? 'rtl' : '' }}">
                        <h2 class="inline text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:underline" style="font-family: '{{ $each->font_family }}', sans-serif;">
                          {{ $each->translatedName() }}
                          <svg class="inline-block w-5 h-5 {{ $role->isRtl() ? 'ms-1 scale-x-[-1]' : 'ms-1' }} align-text-bottom fill-gray-900 dark:fill-gray-100 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                          </svg>
                        </h2>
                      </a>
                    @else
                      <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100" style="font-family: '{{ $otherRole->font_family ?? 'sans-serif' }}', sans-serif;">
                        {{ $each->translatedName() }}
                      </h2>
                    @endif
                  </div>

                  @if ($each->isClaimed() && (config('app.hosted') || config('app.is_testing')) && ! is_demo_mode())
                    @if (auth()->user() && auth()->user()->isMember($each->subdomain))
                      <a href="{{ config('app.url') . route('role.view_admin', ['subdomain' => $each->subdomain, 'tab' => 'schedule'], false) }}"
                        class="inline-flex items-center justify-center">
                        <button type="button" name="follow"
                          style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                          class="inline-flex items-center rounded-md px-4 py-2 text-xs font-semibold border-2 border-transparent shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-md">
                          {{ __('messages.manage') }}
                        </button>
                      </a>
                    @else
                      <a href="{{ config('app.url') . route('role.follow', ['subdomain' => $each->subdomain], false) }}"
                        class="inline-flex items-center justify-center">
                        <button type="button" name="follow"
                          style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                          class="inline-flex items-center rounded-md px-4 py-2 transition-all duration-200 hover:scale-105 hover:shadow-md text-xs font-semibold shadow-sm">
                          {{ __('messages.follow') }}
                        </button>
                      </a>
                    @endif
                  @elseif (auth()->user() && auth()->user()->id === $event->user_id && $each->youtube_links)
                    <button type="button"
                      class="clear-videos-btn inline-flex items-center rounded-md px-3 py-1.5 text-xs font-semibold text-red-600 bg-white dark:bg-gray-800 border border-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 shadow-sm"
                      data-clear-url="{{ route('event.clear_videos', ['subdomain' => $role->subdomain, 'event_hash' => App\Utils\UrlUtils::encodeId($event->id), 'role_hash' => App\Utils\UrlUtils::encodeId($each->id)]) }}">
                      {{ __('messages.clear_videos') }}
                    </button>
                  @endif
                </div>

                {{-- Description with expand/collapse --}}
                @if ($each->description_html)
                  @if(str_word_count(strip_tags($each->description_html)) > 5)
                    <div x-data="{ expanded: false }" class="text-sm text-gray-700 dark:text-gray-300">
                      <span x-show="!expanded" class="description-collapsed">
                        {{ Str::words(html_entity_decode(strip_tags($each->description_html)), 5, '') }}...
                        <button :aria-expanded="expanded" @click="expanded = true" class="font-medium hover:underline whitespace-nowrap text-blue-600 dark:text-blue-400">
                          {{ __('messages.show_more') }}
                        </button>
                      </span>
                      <div x-show="expanded" x-cloak class="description-expanded">
                        <div class="custom-content [&>*:first-child]:mt-0 {{ $role->isRtl() ? 'rtl' : '' }}">
                          {!! \App\Utils\UrlUtils::convertUrlsToLinks($each->description_html) !!}
                        </div>
                        <button :aria-expanded="expanded" @click="expanded = false" class="font-medium hover:underline whitespace-nowrap mt-1 text-blue-600 dark:text-blue-400">
                          {{ __('messages.show_less') }}
                        </button>
                      </div>
                    </div>
                  @else
                    <div class="text-sm text-gray-700 dark:text-gray-300 custom-content [&>*:first-child]:mt-0 {{ $role->isRtl() ? 'rtl' : '' }}">
                      {!! \App\Utils\UrlUtils::convertUrlsToLinks($each->description_html) !!}
                    </div>
                  @endif
                @endif

                {{-- YouTube videos --}}
                @if ($each->youtube_links)
                  @php
                    $sidebarVideoLinks = json_decode($each->youtube_links);
                  @endphp
                  @foreach ($sidebarVideoLinks as $sLink)
                    @if ($sLink)
                      <div class="rounded-lg overflow-hidden">
                        <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($sLink->url) }}" title="{{ $each->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                      </div>
                    @endif
                  @endforeach
                @endif
              </div>
            </div>
        </div>
        @php unset($talentUrl); @endphp
        @endforeach

        {{-- Venue card --}}
        @if ($event->venue && $event->venue->name)
        @php
          $hasVenueHeader = ($event->venue->header_image && $event->venue->header_image !== 'none') || $event->venue->header_image_url;
        @endphp
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl z-20 relative {{ $role->isRtl() ? 'rtl' : '' }}">
          @if ($hasVenueHeader)
            <div class="overflow-hidden rounded-t-2xl">
              @if ($event->venue->header_image && $event->venue->header_image !== 'none')
                <picture>
                  <source srcset="{{ asset('images/headers') }}/{{ $event->venue->header_image }}.webp" type="image/webp">
                  <img class="block max-h-40 w-full object-cover" src="{{ asset('images/headers') }}/{{ $event->venue->header_image }}.png" alt="{{ $event->venue->translatedName() }}" loading="lazy" decoding="async"/>
                </picture>
              @elseif ($event->venue->header_image_url)
                <img class="block max-h-40 w-full object-cover" src="{{ $event->venue->header_image_url }}" alt="{{ $event->venue->translatedName() }}" loading="lazy" decoding="async"/>
              @endif
            </div>
          @endif
          <div class="p-5 relative z-10">
            @if ($event->venue->profile_image_url && $hasVenueHeader)
              <div class="rounded-lg w-[100px] h-[100px] -mt-[60px] bg-white/95 dark:bg-gray-900/95 flex items-center justify-center mb-3">
                <img class="rounded-md w-[90px] h-[90px] object-cover" src="{{ $event->venue->profile_image_url }}" alt="{{ $event->venue->translatedName() }}" loading="lazy" decoding="async"/>
              </div>
            @elseif ($event->venue->profile_image_url)
              <img class="w-full aspect-square object-cover rounded-xl mb-3" src="{{ $event->venue->profile_image_url }}" alt="{{ $event->venue->translatedName() }}" loading="lazy" decoding="async"/>
            @endif
            <div class="flex flex-col gap-2">
              @if ($event->venue->isClaimed())
                @php
                  $venuePanelUrl = route('role.view_guest', ['subdomain' => $event->venue->subdomain]);
                @endphp
                <a href="{{ $venuePanelUrl }}" class="group inline-flex items-center gap-2 w-fit hover:opacity-80 transition-opacity duration-200 {{ $role->isRtl() ? 'rtl' : '' }}">
                  <h2 class="text-base leading-snug font-semibold text-gray-900 dark:text-gray-100 group-hover:underline" style="font-family: '{{ $event->venue->font_family }}', sans-serif;">
                    {{ $event->venue->translatedName() }}
                  </h2>
                  <svg class="w-5 h-5 fill-gray-900 dark:fill-gray-100 opacity-70 group-hover:opacity-100 transition-opacity {{ $role->isRtl() ? 'scale-x-[-1]' : '' }}" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                  </svg>
                </a>
              @else
                <h2 class="text-base leading-snug font-semibold text-gray-900 dark:text-gray-100" style="font-family: '{{ $event->venue->font_family }}', sans-serif;">
                  {{ $event->venue->translatedName() }}
                </h2>
              @endif
              @if ($event->venue->translatedDescription())
                <div x-data="{ expanded: false, needsExpand: false }" x-init="$nextTick(() => { let el = $refs.collapsed; needsExpand = el.scrollHeight > el.clientHeight })">
                  <div x-show="!expanded" x-ref="collapsed" class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3 custom-content {{ $role->isRtl() ? 'rtl' : '' }}">
                    {!! \App\Utils\UrlUtils::convertUrlsToLinks($event->venue->translatedDescription()) !!}
                  </div>
                  <div x-show="expanded" x-cloak class="text-sm text-gray-700 dark:text-gray-300 custom-content {{ $role->isRtl() ? 'rtl' : '' }}">
                    {!! \App\Utils\UrlUtils::convertUrlsToLinks($event->venue->translatedDescription()) !!}
                  </div>
                  <button x-show="!expanded && needsExpand" :aria-expanded="expanded" @click="expanded = true" class="text-sm font-medium hover:underline mt-1 text-blue-600 dark:text-blue-400">
                    {{ __('messages.read_more') }}
                  </button>
                  <button x-show="expanded" x-cloak :aria-expanded="expanded" @click="expanded = false" class="text-sm font-medium hover:underline mt-1 text-blue-600 dark:text-blue-400">
                    {{ __('messages.show_less') }}
                  </button>
                </div>
              @endif
              <div class="flex flex-col gap-4 mt-2">
                @if ($event->venue->email && $event->venue->show_email)
                <div class="flex flex-row gap-2 items-center relative duration-300 text-gray-700 dark:text-gray-300 fill-gray-700 dark:fill-gray-300 {{ $role->isRtl() ? 'rtl' : '' }}">
                  <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z" />
                  </svg>
                  <a href="mailto:{{ $role->email }}" class="text-sm hover:underline">{{ $event->venue->email }}</a>
                </div>
                @endif
                @if ($event->venue->website)
                <div class="flex flex-row gap-2 items-center relative duration-300 text-gray-700 dark:text-gray-300 fill-gray-700 dark:fill-gray-300 {{ $role->isRtl() ? 'rtl' : '' }}">
                  <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.02783 11.25C2.41136 6.07745 6.72957 2 12.0001 2C11.1693 2 10.4295 2.36421 9.82093 2.92113C9.21541 3.47525 8.70371 4.24878 8.28983 5.16315C7.87352 6.08292 7.55013 7.15868 7.33126 8.32611C7.1558 9.26194 7.04903 10.2485 7.01344 11.25H2.02783ZM2.02783 12.75H7.01344C7.04903 13.7515 7.1558 14.7381 7.33126 15.6739C7.55013 16.8413 7.87351 17.9171 8.28983 18.8368C8.70371 19.7512 9.21541 20.5247 9.82093 21.0789C10.4295 21.6358 11.1693 22 12.0001 22C6.72957 22 2.41136 17.9226 2.02783 12.75Z" />
                    <path d="M12.0001 3.39535C11.7251 3.39535 11.3699 3.51236 10.9567 3.89042C10.5406 4.27126 10.1239 4.86815 9.75585 5.68137C9.3902 6.4892 9.09329 7.46441 8.88897 8.55419C8.72806 9.41242 8.62824 10.3222 8.59321 11.25H15.4071C15.372 10.3222 15.2722 9.41242 15.1113 8.5542C14.907 7.46441 14.6101 6.48921 14.2444 5.68137C13.8763 4.86815 13.4597 4.27126 13.0435 3.89042C12.6304 3.51236 12.2751 3.39535 12.0001 3.39535Z" />
                    <path d="M8.88897 15.4458C9.09329 16.5356 9.3902 17.5108 9.75585 18.3186C10.1239 19.1319 10.5406 19.7287 10.9567 20.1096C11.3698 20.4876 11.7251 20.6047 12.0001 20.6047C12.2751 20.6047 12.6304 20.4876 13.0435 20.1096C13.4597 19.7287 13.8763 19.1319 14.2444 18.3186C14.6101 17.5108 14.907 16.5356 15.1113 15.4458C15.2722 14.5876 15.372 13.6778 15.4071 12.75H8.59321C8.62824 13.6778 8.72806 14.5876 8.88897 15.4458Z" />
                    <path d="M12.0001 2C12.831 2 13.5708 2.36421 14.1793 2.92113C14.7849 3.47525 15.2966 4.24878 15.7104 5.16315C16.1267 6.08292 16.4501 7.15868 16.669 8.32612C16.8445 9.26194 16.9512 10.2485 16.9868 11.25H21.9724C21.5889 6.07745 17.2707 2 12.0001 2Z" />
                    <path d="M16.669 15.6739C16.4501 16.8413 16.1267 17.9171 15.7104 18.8368C15.2966 19.7512 14.7849 20.5247 14.1793 21.0789C13.5708 21.6358 12.831 22 12.0001 22C17.2707 22 21.5889 17.9226 21.9724 12.75H16.9868C16.9512 13.7515 16.8445 14.7381 16.669 15.6739Z" />
                  </svg>
                  <x-link href="{{ $event->venue->website }}" target="_blank" :nofollow="true" class="text-sm text-gray-700 dark:text-gray-300">
                    {{ App\Utils\UrlUtils::clean($event->venue->website) }}
                  </x-link>
                </div>
                @endif
              </div>

              @if ($event->venue->social_links)
              <div class="flex flex-row gap-3 items-center mt-2 {{ $role->isRtl() ? 'rtl' : '' }}">
                @foreach (json_decode($event->venue->social_links) as $link)
                  @if ($link)
                  <a
                    href="{{ $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
                    class="w-10 h-10 rounded-full flex justify-center items-center bg-gray-100 dark:bg-gray-700 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                    title="{{ App\Utils\UrlUtils::clean($link->url) }}"
                    >
                    <x-url-icon class="w-5 h-5" color="#6B7280">
                      {{ \App\Utils\UrlUtils::clean($link->url) }}
                    </x-url-icon>
                  </a>
                  @endif
                @endforeach
              </div>
              @endif

              {{-- Calendar links dropdown (inside venue card) --}}
              @if ($event->tickets_enabled && $event->isPro())
              <div class="relative mt-4">
                <button type="button"
                    class="calendar-card-toggle inline-flex justify-center gap-x-1.5 rounded-xl px-6 py-3 text-base font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};"
                    aria-expanded="true" aria-haspopup="true">
                  {{ __('messages.add_to_calendar') }}
                  <svg class="-me-1 h-5 w-5" style="color: {{ $contrastColor }};" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                  </svg>
                </button>

                {{-- Calendar dropdown menu --}}
                <div id="calendar-card-dropdown" class="pop-up-menu hidden absolute top-full {{ $role->isRtl() ? 'end-0 origin-top-right' : 'start-0 origin-top-left' }} z-50 mt-2 w-56 divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" tabindex="-1">
                  <div class="py-1 stop-propagation" role="none">
                    <a href="{{ $event->getGoogleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1">
                      <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                      </svg>
                      Google Calendar
                      <svg class="ms-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ $event->getAppleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1">
                      <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
                      </svg>
                      Apple Calendar
                      <svg class="ms-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ $event->getMicrosoftCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1">
                      <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M2,3H11V12H2V3M11,22H2V13H11V22M21,3V12H12V3H21M21,22H12V13H21V22Z" />
                      </svg>
                      Microsoft Outlook
                      <svg class="ms-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
        @endif

        {{-- Calendar links dropdown (fallback when no venue) --}}
        @if (!($event->venue && $event->venue->name) && $event->tickets_enabled && $event->isPro())
        <div class="relative {{ $role->isRtl() ? 'rtl' : '' }}">
          <button type="button"
              class="calendar-card-toggle inline-flex justify-center gap-x-1.5 rounded-xl px-6 py-3 text-base font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
              style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};"
              aria-expanded="true" aria-haspopup="true">
            {{ __('messages.add_to_calendar') }}
            <svg class="-me-1 h-5 w-5" style="color: {{ $contrastColor }};" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>

          {{-- Calendar dropdown menu --}}
          <div id="calendar-card-dropdown" class="pop-up-menu hidden absolute top-full {{ $role->isRtl() ? 'end-0 origin-top-right' : 'start-0 origin-top-left' }} z-50 mt-2 w-56 divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" tabindex="-1">
            <div class="py-1 stop-propagation" role="none">
              <a href="{{ $event->getGoogleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1">
                <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                </svg>
                Google Calendar
                <svg class="ms-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
              <a href="{{ $event->getAppleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1">
                <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
                </svg>
                Apple Calendar
                <svg class="ms-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
              <a href="{{ $event->getMicrosoftCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1">
                <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M2,3H11V12H2V3M11,22H2V13H11V22M21,3V12H12V3H21M21,22H12V13H21V22Z" />
                </svg>
                Microsoft Outlook
                <svg class="ms-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
            </div>
          </div>
        </div>
        @endif

        {{-- Create your own card --}}
        @if (config('app.hosted') && ! $event->isPro())
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-5 flex flex-col gap-6 {{ $role->isRtl() ? 'rtl' : '' }}">
          <h2 class="text-base leading-snug font-semibold text-gray-900 dark:text-gray-100">
            {{ __('messages.create_your_own_event_schedule') }}
          </h2>
          <a href="{{ marketing_url() }}" target="_blank" rel="noopener noreferrer">
            <button
              type="button"
              name="login"
              class="accent-hover-btn inline-flex items-center justify-center rounded-xl text-base duration-300 bg-transparent border-[1px] py-4 px-8 hover:scale-105 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-90 text-gray-900 dark:text-white"
              style="border-color: {{ $accentColor }};"
              data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
            >
              {{ __('messages.create_schedule') }}
            </button>
          </a>
        </div>
        @endif

        {{-- Calendar widget --}}
        @if(count($events) > 0)
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-5 flex flex-col gap-6 {{ $role->isRtl() ? 'rtl' : '' }}">
          <div class="w-full">
            @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule'), 'force_mobile' => true, 'max_events' => 20, 'hide_past_events' => true])
          </div>
        </div>
        @endif

        </div>
      </div>

      {{-- RIGHT COLUMN --}}
      <div class="order-1 lg:order-2 flex flex-col gap-4 lg:gap-6">
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8 flex flex-col gap-6 z-10">

        {{-- Breadcrumb --}}
        @php
          $backUrl = route('role.view_guest', ['subdomain' => $role->subdomain]);
          $queryParams = [];
          if (request('category')) $queryParams['category'] = request('category');
          if (request('schedule')) $queryParams['schedule'] = request('schedule');
          if (request('date')) {
            $date = request('date');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
              $dateParts = explode('-', $date);
              $queryParams['month'] = (int)$dateParts[1];
              $queryParams['year'] = (int)$dateParts[0];
            }
          } else {
            if (request('month')) $queryParams['month'] = request('month');
            if (request('year')) $queryParams['year'] = request('year');
          }
          if (!empty($queryParams)) {
            $backUrl .= '?' . http_build_query($queryParams);
          }
        @endphp
        <nav aria-label="Breadcrumb" class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400 {{ $role->isRtl() ? 'rtl' : '' }}">
          <a href="{{ $backUrl }}" class="px-3 py-2 -mx-3 hover:underline hover:text-gray-700 dark:hover:text-gray-200">
            {{ $role->isRtl() ? '→' : '←' }} {{ __('messages.back_to_schedule') }}
          </a>
          @if (auth()->user() && auth()->user()->canEditEvent($event))
            <span>|</span>
            <a href="{{ config('app.url') . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)], false) }}" class="px-3 py-2 -mx-3 hover:underline hover:text-gray-700 dark:hover:text-gray-200">
              {{ __('messages.edit_event') }}
            </a>
          @endif
        </nav>

        {{-- Event title --}}
        <h1
          class="text-gray-900 dark:text-gray-100 text-[28px] sm:text-[36px] lg:text-[44px] leading-snug font-bold {{ $role->isRtl() ? 'rtl text-right' : '' }}"
        >
          {{ $translation ? $translation->name_translated : $event->translatedName() }}
        </h1>

        {{-- Short description --}}
        @if ($event->short_description)
        <p class="text-gray-600 dark:text-gray-400 text-lg mt-2 {{ $role->isRtl() ? 'rtl text-right' : '' }}">
          {{ $translation && $translation->short_description_translated ? $translation->short_description_translated : $event->translatedShortDescription() }}
        </p>
        @endif

        {{-- Calendar date badge + time --}}
        <div class="flex items-center gap-4 {{ $role->isRtl() ? 'rtl' : '' }}">
          <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700
                      bg-white dark:bg-gray-900 flex flex-col items-center justify-center shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider leading-none pt-1"
                  style="color: {{ $accentColor }};">
              {{ $event->getStartDateTime($date, true)->format('M') }}
            </span>
            <span class="text-2xl font-bold text-gray-900 dark:text-white leading-none">
              {{ $event->getStartDateTime($date, true)->format('j') }}
            </span>
          </div>
          <div class="flex flex-col">
            <span class="text-lg font-semibold text-gray-900 dark:text-white">
              @if ($event->isMultiDay())
                <time datetime="{{ $event->getStartDateTime($date, true)->format('Y-m-d\TH:i:sP') }}">
                  {{ $event->getStartDateTime($date, true)->format($event->getDateTimeFormat(true)) }} - {{ $event->getStartDateTime($date, true)->addHours($event->duration)->format($event->getDateTimeFormat()) }}
                </time>
              @else
                <time datetime="{{ $event->getStartDateTime($date, true)->format('Y-m-d\TH:i:sP') }}">
                  {{ $event->getStartDateTime($date, true)->format('l, F j') }}
                </time>
              @endif
            </span>
            @if (!$event->isMultiDay())
            <span class="text-sm text-gray-500 dark:text-gray-400">
              <time datetime="{{ $event->getStartDateTime($date, true)->format('Y-m-d\TH:i:sP') }}">{{ $event->getStartEndTime($date, get_use_24_hour_time($role)) }}</time>
            </span>
            @endif
          </div>
        </div>

        {{-- Location icon badge --}}
        @if (($event->venue && $event->venue->name) || $event->getEventUrlDomain())
        <div class="flex items-center gap-4 {{ $role->isRtl() ? 'rtl' : '' }}">
          <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900
                      flex items-center justify-center shadow-sm">
            @if ($event->venue && $event->venue->profile_image_url)
              <img src="{{ $event->venue->profile_image_url }}" alt="{{ $event->venue->translatedName() }}" class="w-11 h-11 rounded-lg object-cover" loading="lazy" decoding="async">
            @else
              <svg width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
              </svg>
            @endif
          </div>
          <div class="flex flex-col">
            <span>
              @if ($event->venue && $event->venue->translatedName())
                @if ($event->venue->isClaimed())
                  @php
                    $venueUrl = route('role.view_guest', ['subdomain' => $event->venue->subdomain]);
                    $queryParams = [];
                    if (request('category')) $queryParams['category'] = request('category');
                    if (request('schedule')) $queryParams['schedule'] = request('schedule');
                    if (request('date')) {
                      $date = request('date');
                      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        $dateParts = explode('-', $date);
                        $queryParams['month'] = (int)$dateParts[1];
                        $queryParams['year'] = (int)$dateParts[0];
                      }
                    } else {
                      if (request('month')) $queryParams['month'] = request('month');
                      if (request('year')) $queryParams['year'] = request('year');
                    }
                    if (!empty($queryParams)) {
                      $venueUrl .= '?' . http_build_query($queryParams);
                    }
                  @endphp
                  <a href="{{ $venueUrl }}" class="group inline-flex items-center gap-1 w-fit">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white group-hover:underline">{{ $event->venue->translatedName() }}</span>
                    <svg class="w-5 h-5 fill-gray-900 dark:fill-gray-100 opacity-70 group-hover:opacity-100 transition-opacity {{ $role->isRtl() ? 'scale-x-[-1]' : '' }}" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                    </svg>
                  </a>
                @else
                  <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $event->venue->translatedName() }}</span>
                @endif
              @else
                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $event->getEventUrlDomain() }}</span>
              @endif
            </span>
            @if ($event->venue && $event->venue->shortAddress())
            <span class="text-sm text-gray-500 dark:text-gray-400">
              <x-link href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->venue->bestAddress()) }}" target="_blank" :nofollow="true">
                {{ $event->venue->shortAddress() }}
              </x-link>
            </span>
            @endif
          </div>
        </div>
        @endif

        {{-- CTA buttons --}}
        <div style="font-family: sans-serif" class="relative inline-block text-left hidden sm:block self-start {{ $role->isRtl() ? 'rtl' : '' }}">
        @if ($event->canSellTickets($date) || $event->registration_url)
          @if (request()->get('tickets') !== 'true')
            <a href="{{ $event->registration_url ? $event->registration_url : request()->fullUrlWithQuery(['tickets' => 'true']) }}" {{ $event->registration_url ? 'target="_blank" rel="noopener noreferrer nofollow"' : '' }}
              @if ($event->payment_method === 'payment_url' && $event->user && $event->user->paymentUrlMobileOnly() && ! is_mobile())
                class="payment-mobile-only-link"
                data-mobile-msg="{{ __('messages.payment_url_mobile_only') }}"
              @endif
            >
                <button type="button"
                      class="min-w-[180px] inline-flex justify-center gap-x-1.5 rounded-xl px-6 py-3 text-lg font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
                      style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                  {{ $event->registration_url ? __('messages.view_event') : ($event->areTicketsFree() ? __('messages.get_tickets') : __('messages.buy_tickets')) }}
              </button>
            </a>
          @endif
        @else
              <button type="button"
                  class="calendar-popup-toggle inline-flex justify-center gap-x-1.5 rounded-xl px-6 py-3 text-lg font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
                  style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};"
                  id="menu-button" aria-expanded="true" aria-haspopup="true">
              {{ __('messages.add_to_calendar') }}
              <svg class="-me-1 h-5 w-5" style="color: {{ $contrastColor }};" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
              </button>

            {{-- Desktop calendar dropdown --}}
            <div id="calendar-pop-up-menu" class="pop-up-menu hidden absolute top-full end-0 z-50 mt-2 w-56 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                <div class="py-1 stop-propagation" role="none">
                    <a href="{{ $event->getGoogleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1" id="menu-item-0">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                        </svg>
                        Google Calendar
                        <svg class="ml-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ $event->getAppleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1" id="menu-item-1">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
                        </svg>
                        Apple Calendar
                        <svg class="ml-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                    <a href="{{ $event->getMicrosoftCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="group flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200" role="menuitem" tabindex="-1" id="menu-item-2">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M2,3H11V12H2V3M11,22H2V13H11V22M21,3V12H12V3H21M21,22H12V13H21V22Z" />
                        </svg>
                        Microsoft Outlook
                        <svg class="ml-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>
            </div>

        @endif
        </div>
        </div>

        {{-- Mobile calendar bottom sheet (outside hidden sm:block container so it's visible on mobile) --}}
        @if (!($event->canSellTickets($date) || $event->registration_url))
        <div id="calendar-mobile-sheet" class="hidden fixed inset-0 z-50 sm:hidden">
          <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" id="calendar-mobile-overlay"></div>
          <div class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl">
            <div class="flex justify-center py-3">
              <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>
            <div class="px-6 pb-6 space-y-1 {{ $role->isRtl() ? 'rtl' : '' }}">
              <a href="{{ $event->getGoogleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                </svg>
                Google Calendar
                <svg class="ml-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
              <a href="{{ $event->getAppleCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
                </svg>
                Apple Calendar
                <svg class="ml-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
              <a href="{{ $event->getMicrosoftCalendarUrl($date) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M2,3H11V12H2V3M11,22H2V13H11V22M21,3V12H12V3H21M21,22H12V13H21V22Z" />
                </svg>
                Microsoft Outlook
                <svg class="ml-auto h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
              </a>
            </div>
          </div>
        </div>
        @endif

        {{-- Flyer image --}}
        @if ($event->flyer_image_url && request()->get('tickets') !== 'true')
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl overflow-hidden">
          <img src="{{ $event->flyer_image_url }}" alt="{{ $translation ? $translation->name_translated : $event->translatedName() }} - {{ __('messages.flyer') }}" class="w-full" loading="lazy" decoding="async"/>
        </div>
        @endif

        {{-- Ticket section OR Description/Content --}}
        @if (request()->get('tickets') === 'true' && $event->isPro())
        <div class="flex flex-col xl:flex-row gap-10 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 px-5 py-6 sm:p-8">
          <div class="flex-1">
            <div class="flex flex-col gap-4">
              <h2 class="text-[28px] leading-snug text-gray-900 dark:text-gray-100">
                {{ $event->areTicketsFree() ? __('messages.get_tickets') : __('messages.buy_tickets') }}
              </h2>
              <p class="text-base text-gray-700 dark:text-gray-300">
                @include('event.tickets', ['event' => $event, 'subdomain' => $subdomain])
              </p>
            </div>
          </div>
          <div class="flex-1">
            @if ($event->flyer_image_url)
              <img src="{{ $event->flyer_image_url }}" alt="{{ $translation ? $translation->name_translated : $event->translatedName() }} - {{ __('messages.flyer') }}" class="block rounded-lg" loading="lazy" decoding="async"/>
            @endif
          </div>
        </div>
        @else

        {{-- Description --}}
        @if ($translation ? $translation->description_translated : $event->translatedDescription())
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">
            {{ __('messages.about') }}
          </h2>
          <div class="{{ $role->isRtl() || ($translation && $translation->role->isRtl()) ? 'rtl' : '' }}">
            <div class="text-gray-700 dark:text-gray-300 text-base custom-content [&>*:first-child]:mt-0">
              {!! \App\Utils\UrlUtils::convertUrlsToLinks($translation ? $translation->description_translated : $event->translatedDescription()) !!}
            </div>
          </div>
        </div>
        @endif

        {{-- Event parts --}}
        @if ($event->parts->count() > 0)
        <div id="agenda" class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8 {{ $role->isRtl() ? 'rtl' : '' }}">
          @php
            $hasTimes = $event->parts->contains(fn($part) => !empty($part->start_time));
          @endphp
          @if ($hasTimes)
            {{-- Timed agenda: vertical timeline --}}
            <div class="relative {{ $role->isRtl() ? 'pr-8' : 'pl-8' }}">
              <div class="absolute {{ $role->isRtl() ? 'right-[11px]' : 'left-[11px]' }} top-2 bottom-2 w-[3px] rounded-full" style="background-color: {{ $accentColor }}33;"></div>
              @foreach ($event->parts as $part)
              <div class="relative mb-6 last:mb-0">
                <div class="absolute {{ $role->isRtl() ? '-right-8' : '-left-8' }} top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-900" style="background-color: {{ $accentColor }}; box-shadow: 0 0 0 2px {{ $accentColor }}33;"></div>
                <div class="flex flex-col bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                  @if ($part->start_time)
                  <span class="text-xs font-medium mb-1 inline-flex {{ $role->isRtl() ? 'self-end' : 'self-start' }} rounded-full px-2.5 py-0.5" style="color: {{ $accentColor }}; background-color: {{ $accentColor }}10;">
                    {{ $part->start_time }}@if ($part->end_time) - {{ $part->end_time }}@endif
                  </span>
                  @endif
                  <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $part->translatedName() }}</span>
                  @if ($part->translatedDescription())
                  <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 prose prose-sm dark:prose-invert max-w-none">{!! $part->translatedDescription() !!}</div>
                  @endif
                  @if (!is_demo_role($role))
                  @php
                    $partVideos = $part->approvedVideos;
                    $partComments = $part->approvedComments;
                    if ($event->days_of_week && $date) {
                      $partVideos = $partVideos->filter(fn($v) => $v->event_date === $date || $v->event_date === null);
                      $partComments = $partComments->filter(fn($c) => $c->event_date === $date || $c->event_date === null);
                    }
                  @endphp
                  @if ($partVideos->count() > 0)
                  <div class="mt-2 space-y-2">
                    @foreach ($partVideos as $video)
                    <div class="rounded-lg overflow-hidden">
                      <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" title="{{ $part->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                    </div>
                    @endforeach
                  </div>
                  @endif
                  @if ($partComments->count() > 0)
                  <div class="mt-2 space-y-1">
                    @foreach ($partComments as $comment)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user->first_name ?? $comment->user->name }}</span>: {{ $comment->comment }}
                    </div>
                    @endforeach
                  </div>
                  @endif
                  @php
                    $myPartPendingVideos = $myPendingVideos->where('event_part_id', $part->id);
                    $myPartPendingComments = $myPendingComments->where('event_part_id', $part->id);
                    if ($event->days_of_week && $date) {
                      $myPartPendingVideos = $myPartPendingVideos->filter(fn($v) => $v->event_date === $date || $v->event_date === null);
                      $myPartPendingComments = $myPartPendingComments->filter(fn($c) => $c->event_date === $date || $c->event_date === null);
                    }
                  @endphp
                  @if ($myPartPendingVideos->count() > 0)
                  <div class="mt-2 space-y-2 opacity-60">
                    @foreach ($myPartPendingVideos as $video)
                    <div id="pending-video-{{ $video->id }}" class="rounded-lg overflow-hidden relative">
                      <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" title="{{ $part->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                      <span class="absolute top-2 {{ $role->isRtl() ? 'left-2' : 'right-2' }} inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
                    </div>
                    @endforeach
                  </div>
                  @endif
                  @if ($myPartPendingComments->count() > 0)
                  <div class="mt-2 space-y-1 opacity-60">
                    @foreach ($myPartPendingComments as $comment)
                    <div id="pending-comment-{{ $comment->id }}" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                      <span><span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user->first_name ?? $comment->user->name }}</span>: {{ $comment->comment }}</span>
                      <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
                    </div>
                    @endforeach
                  </div>
                  @endif
                  <div class="mt-2 flex gap-3" x-data="{ showVideo: false, showComment: false }">
                    <button @click="showVideo = !showVideo; showComment = false; if (showVideo) setTimeout(() => $refs.videoInput.focus(), 50)" class="accent-hover-btn inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white rounded-lg border transition-all duration-200 hover:scale-105 hover:shadow-md" style="border-color: {{ $accentColor }};" data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                      {{ __('messages.add_video') }}
                    </button>
                    <button @click="showComment = !showComment; showVideo = false; if (showComment) setTimeout(() => $refs.commentInput.focus(), 50)" class="accent-hover-btn inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white rounded-lg border transition-all duration-200 hover:scale-105 hover:shadow-md" style="border-color: {{ $accentColor }};" data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                      {{ __('messages.add_comment') }}
                    </button>
                    <div x-show="showVideo" x-cloak class="mt-2 w-full">
                      <form method="POST" action="{{ route('event.submit_video', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="event_part_id" value="{{ \App\Utils\UrlUtils::encodeId($part->id) }}">
                        @if ($event->days_of_week && $date)
                        <input type="hidden" name="event_date" value="{{ $date }}">
                        @endif
                        <input x-ref="videoInput" type="url" name="youtube_url" pattern="https?://(www\.)?((m\.)?youtube\.com|youtu\.be)/.+" title="{{ __('messages.invalid_youtube_url') }}" placeholder="{{ __('messages.paste_youtube_url') }}" class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                        <button type="submit" class="self-start font-semibold text-sm px-4 py-2 rounded transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.submit') }}</button>
                      </form>
                    </div>
                    <div x-show="showComment" x-cloak class="mt-2 w-full">
                      <form method="POST" action="{{ route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="event_part_id" value="{{ \App\Utils\UrlUtils::encodeId($part->id) }}">
                        @if ($event->days_of_week && $date)
                        <input type="hidden" name="event_date" value="{{ $date }}">
                        @endif
                        <textarea x-ref="commentInput" name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000" class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                        <button type="submit" class="font-semibold text-sm px-4 py-2 rounded self-start transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.submit') }}</button>
                      </form>
                    </div>
                  </div>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
          @else
            {{-- Untimed setlist: numbered list --}}
            <div class="space-y-3">
              @foreach ($event->parts as $index => $part)
              <div class="flex items-start gap-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 {{ $role->isRtl() ? 'rtl' : '' }}">
                <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background-color: {{ $accentColor }};">{{ $index + 1 }}</span>
                <div class="flex-1">
                  <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $part->translatedName() }}</span>
                  @if ($part->translatedDescription())
                  <div class="text-sm text-gray-500 dark:text-gray-400 block mt-0.5 prose prose-sm dark:prose-invert max-w-none">{!! $part->translatedDescription() !!}</div>
                  @endif
                  @if (!is_demo_role($role))
                  @php
                    $partVideos = $part->approvedVideos;
                    $partComments = $part->approvedComments;
                    if ($event->days_of_week && $date) {
                      $partVideos = $partVideos->filter(fn($v) => $v->event_date === $date || $v->event_date === null);
                      $partComments = $partComments->filter(fn($c) => $c->event_date === $date || $c->event_date === null);
                    }
                  @endphp
                  @if ($partVideos->count() > 0)
                  <div class="mt-2 space-y-2">
                    @foreach ($partVideos as $video)
                    <div class="rounded-lg overflow-hidden">
                      <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" title="{{ $part->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                    </div>
                    @endforeach
                  </div>
                  @endif
                  @if ($partComments->count() > 0)
                  <div class="mt-2 space-y-1">
                    @foreach ($partComments as $comment)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user->first_name ?? $comment->user->name }}</span>: {{ $comment->comment }}
                    </div>
                    @endforeach
                  </div>
                  @endif
                  @php
                    $myPartPendingVideos = $myPendingVideos->where('event_part_id', $part->id);
                    $myPartPendingComments = $myPendingComments->where('event_part_id', $part->id);
                    if ($event->days_of_week && $date) {
                      $myPartPendingVideos = $myPartPendingVideos->filter(fn($v) => $v->event_date === $date || $v->event_date === null);
                      $myPartPendingComments = $myPartPendingComments->filter(fn($c) => $c->event_date === $date || $c->event_date === null);
                    }
                  @endphp
                  @if ($myPartPendingVideos->count() > 0)
                  <div class="mt-2 space-y-2 opacity-60">
                    @foreach ($myPartPendingVideos as $video)
                    <div id="pending-video-{{ $video->id }}" class="rounded-lg overflow-hidden relative">
                      <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" title="{{ $part->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                      <span class="absolute top-2 {{ $role->isRtl() ? 'left-2' : 'right-2' }} inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
                    </div>
                    @endforeach
                  </div>
                  @endif
                  @if ($myPartPendingComments->count() > 0)
                  <div class="mt-2 space-y-1 opacity-60">
                    @foreach ($myPartPendingComments as $comment)
                    <div id="pending-comment-{{ $comment->id }}" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
                      <span><span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user->first_name ?? $comment->user->name }}</span>: {{ $comment->comment }}</span>
                      <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
                    </div>
                    @endforeach
                  </div>
                  @endif
                  <div class="mt-2 flex flex-wrap gap-3" x-data="{ showVideo: false, showComment: false }">
                    <button @click="showVideo = !showVideo; showComment = false; if (showVideo) setTimeout(() => $refs.videoInput.focus(), 50)" class="accent-hover-btn inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white rounded-lg border transition-all duration-200 hover:scale-105 hover:shadow-md" style="border-color: {{ $accentColor }};" data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                      {{ __('messages.add_video') }}
                    </button>
                    <button @click="showComment = !showComment; showVideo = false; if (showComment) setTimeout(() => $refs.commentInput.focus(), 50)" class="accent-hover-btn inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white rounded-lg border transition-all duration-200 hover:scale-105 hover:shadow-md" style="border-color: {{ $accentColor }};" data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                      {{ __('messages.add_comment') }}
                    </button>
                    <div x-show="showVideo" x-cloak class="w-full">
                      <form method="POST" action="{{ route('event.submit_video', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="event_part_id" value="{{ \App\Utils\UrlUtils::encodeId($part->id) }}">
                        @if ($event->days_of_week && $date)
                        <input type="hidden" name="event_date" value="{{ $date }}">
                        @endif
                        <input x-ref="videoInput" type="url" name="youtube_url" pattern="https?://(www\.)?((m\.)?youtube\.com|youtu\.be)/.+" title="{{ __('messages.invalid_youtube_url') }}" placeholder="{{ __('messages.paste_youtube_url') }}" class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                        <button type="submit" class="self-start font-semibold text-sm px-4 py-2 rounded transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.submit') }}</button>
                      </form>
                    </div>
                    <div x-show="showComment" x-cloak class="w-full">
                      <form method="POST" action="{{ route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="event_part_id" value="{{ \App\Utils\UrlUtils::encodeId($part->id) }}">
                        @if ($event->days_of_week && $date)
                        <input type="hidden" name="event_date" value="{{ $date }}">
                        @endif
                        <textarea x-ref="commentInput" name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000" class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                        <button type="submit" class="font-semibold text-sm px-4 py-2 rounded self-start transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.submit') }}</button>
                      </form>
                    </div>
                  </div>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
          @endif
        </div>
        @endif

        {{-- Fan content --}}
        @php
          $eventLevelVideos = $event->approvedVideos->whereNull('event_part_id');
          $eventLevelComments = $event->approvedComments->whereNull('event_part_id');
          $myEventLevelPendingVideos = $myPendingVideos->whereNull('event_part_id');
          $myEventLevelPendingComments = $myPendingComments->whereNull('event_part_id');
          if ($event->days_of_week && $date) {
            $eventLevelVideos = $eventLevelVideos->filter(fn($v) => $v->event_date === $date || $v->event_date === null);
            $eventLevelComments = $eventLevelComments->filter(fn($c) => $c->event_date === $date || $c->event_date === null);
            $myEventLevelPendingVideos = $myEventLevelPendingVideos->filter(fn($v) => $v->event_date === $date || $v->event_date === null);
            $myEventLevelPendingComments = $myEventLevelPendingComments->filter(fn($c) => $c->event_date === $date || $c->event_date === null);
          }
        @endphp
        @if (!is_demo_role($role) && ($eventLevelVideos->count() > 0 || $eventLevelComments->count() > 0 || $myEventLevelPendingVideos->count() > 0 || $myEventLevelPendingComments->count() > 0 || $event->parts->count() == 0))
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8 {{ $role->isRtl() ? 'rtl' : '' }}">
          @if ($eventLevelVideos->count() > 0)
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            @foreach ($eventLevelVideos as $video)
            <div class="rounded-lg overflow-hidden">
              <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" title="{{ $event->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
            </div>
            @endforeach
          </div>
          @endif
          @if ($eventLevelComments->count() > 0)
          <div class="space-y-2 mb-4">
            @foreach ($eventLevelComments as $comment)
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user->first_name ?? $comment->user->name }}</span>: {{ $comment->comment }}
            </div>
            @endforeach
          </div>
          @endif
          @if ($myEventLevelPendingVideos->count() > 0)
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 opacity-60">
            @foreach ($myEventLevelPendingVideos as $video)
            <div id="pending-video-{{ $video->id }}" class="rounded-lg overflow-hidden relative">
              <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" title="{{ $event->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
              <span class="absolute top-2 {{ $role->isRtl() ? 'left-2' : 'right-2' }} inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
            </div>
            @endforeach
          </div>
          @endif
          @if ($myEventLevelPendingComments->count() > 0)
          <div class="space-y-2 mb-4 opacity-60">
            @foreach ($myEventLevelPendingComments as $comment)
            <div id="pending-comment-{{ $comment->id }}" class="text-sm text-gray-600 dark:text-gray-400 flex items-start gap-2">
              <span><span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user->first_name ?? $comment->user->name }}</span>: {{ $comment->comment }}</span>
              <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
            </div>
            @endforeach
          </div>
          @endif
          @if ($event->parts->count() == 0)
          <div class="flex flex-wrap gap-3" x-data="{ showVideo: false, showComment: false }">
            <button @click="showVideo = !showVideo; showComment = false; if (showVideo) setTimeout(() => $refs.videoInput.focus(), 50)" class="accent-hover-btn inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white rounded-lg border transition-all duration-200 hover:scale-105 hover:shadow-md" style="border-color: {{ $accentColor }};" data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
              {{ __('messages.add_video') }}
            </button>
            <button @click="showComment = !showComment; showVideo = false; if (showComment) setTimeout(() => $refs.commentInput.focus(), 50)" class="accent-hover-btn inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-900 dark:text-white rounded-lg border transition-all duration-200 hover:scale-105 hover:shadow-md" style="border-color: {{ $accentColor }};" data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
              {{ __('messages.add_comment') }}
            </button>
            <div x-show="showVideo" x-cloak class="w-full mt-2">
              <form method="POST" action="{{ route('event.submit_video', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="flex flex-col gap-2">
                @csrf
                @if ($event->days_of_week && $date)
                <input type="hidden" name="event_date" value="{{ $date }}">
                @endif
                <input x-ref="videoInput" type="url" name="youtube_url" pattern="https?://(www\.)?((m\.)?youtube\.com|youtu\.be)/.+" title="{{ __('messages.invalid_youtube_url') }}" placeholder="{{ __('messages.paste_youtube_url') }}" class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                <button type="submit" class="self-start font-semibold text-sm px-4 py-2 rounded transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.submit') }}</button>
              </form>
            </div>
            <div x-show="showComment" x-cloak class="w-full mt-2">
              <form method="POST" action="{{ route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="flex flex-col gap-2">
                @csrf
                @if ($event->days_of_week && $date)
                <input type="hidden" name="event_date" value="{{ $date }}">
                @endif
                <textarea x-ref="commentInput" name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000" class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                <button type="submit" class="font-semibold text-sm px-4 py-2 rounded self-start transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.submit') }}</button>
              </form>
            </div>
          </div>
          @endif
        </div>
        @endif

        @endif
        {{-- End of tickets/content conditional --}}

      </div>
      {{-- End RIGHT COLUMN --}}

    </div>
  </div>
  </main>

  {{-- Sticky mobile CTA --}}
  <div class="fixed bottom-0 inset-x-0 z-40 sm:hidden bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-5 py-3 shadow-lg" style="font-family: sans-serif; padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
    @if ($event->canSellTickets($date) || $event->registration_url)
      @if (request()->get('tickets') !== 'true')
        <a href="{{ $event->registration_url ? $event->registration_url : request()->fullUrlWithQuery(['tickets' => 'true']) }}" {{ $event->registration_url ? 'target="_blank" rel="noopener noreferrer nofollow"' : '' }}
          @if ($event->payment_method === 'payment_url' && $event->user && $event->user->paymentUrlMobileOnly() && ! is_mobile())
            class="payment-mobile-only-link"
            data-mobile-msg="{{ __('messages.payment_url_mobile_only') }}"
          @endif
        >
          <button type="button"
                class="w-full justify-center rounded-xl px-6 py-3 text-lg font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
                style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
            {{ $event->registration_url ? __('messages.view_event') : ($event->areTicketsFree() ? __('messages.get_tickets') : __('messages.buy_tickets')) }}
          </button>
        </a>
      @endif
    @else
      <button type="button"
          id="mobile-calendar-cta"
          class="w-full justify-center rounded-xl px-6 py-3 text-lg font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg"
          style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
        {{ __('messages.add_to_calendar') }}
      </button>
    @endif
  </div>

  <script {!! nonce_attr() !!}>
    function clearVideos(url) {
      if (confirm('{{ __("messages.are_you_sure_clear_videos") }}')) {
        window.location.href = url;
      }
    }

    // Accent hover buttons (onmouseover/onmouseout replacement)
    document.querySelectorAll('.accent-hover-btn').forEach(function(btn) {
      var accent = btn.getAttribute('data-accent');
      var contrast = btn.getAttribute('data-contrast');
      btn.addEventListener('mouseover', function() {
        this.style.backgroundColor = accent;
        this.style.color = contrast;
      });
      btn.addEventListener('mouseout', function() {
        this.style.backgroundColor = 'transparent';
        this.style.color = '';
      });
    });

    // Clear videos buttons
    document.querySelectorAll('.clear-videos-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        clearVideos(this.getAttribute('data-clear-url'));
      });
    });

    // Calendar card dropdown toggle buttons
    document.querySelectorAll('.calendar-card-toggle').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var m = document.getElementById('calendar-card-dropdown');
        m.classList.toggle('hidden');
      });
    });

    // Calendar popup toggle button (desktop)
    var calendarPopupToggle = document.querySelector('.calendar-popup-toggle');
    if (calendarPopupToggle) {
      calendarPopupToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        var m = document.getElementById('calendar-pop-up-menu');
        m.classList.toggle('hidden');
      });
    }

    // Stop propagation on dropdown menu contents
    document.querySelectorAll('.stop-propagation').forEach(function(el) {
      el.addEventListener('click', function(e) {
        e.stopPropagation();
      });
    });

    // Payment mobile-only links
    document.querySelectorAll('.payment-mobile-only-link').forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        alert(this.getAttribute('data-mobile-msg'));
      });
    });

    // Mobile calendar sheet overlay (close on click)
    var mobileOverlay = document.getElementById('calendar-mobile-overlay');
    if (mobileOverlay) {
      mobileOverlay.addEventListener('click', function() {
        document.getElementById('calendar-mobile-sheet').classList.add('hidden');
      });
    }

    // Mobile calendar CTA button (open sheet)
    var mobileCta = document.getElementById('mobile-calendar-cta');
    if (mobileCta) {
      mobileCta.addEventListener('click', function() {
        document.getElementById('calendar-mobile-sheet').classList.remove('hidden');
      });
    }

    // Close calendar pop-up menu on document click
    document.addEventListener('click', function() {
      var m = document.getElementById('calendar-pop-up-menu');
      if (m && !m.classList.contains('hidden')) {
        m.classList.add('hidden');
      }
    });

    if (window.location.hash === '#agenda') {
        const el = document.getElementById('agenda');
        if (el) {
            setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
        }
    }

    @if (session('scroll_to'))
    var scrollTarget = document.getElementById('{{ session('scroll_to') }}');
    if (scrollTarget) {
        setTimeout(() => scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' }), 300);
    }
    @endif
  </script>

</x-app-guest-layout>
