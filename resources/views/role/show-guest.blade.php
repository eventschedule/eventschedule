<x-app-guest-layout :role="$role" :fonts="$fonts">

  @php
   $isRtl = is_rtl();
   $accentColor = (isset($selectedGroup) && $selectedGroup && $selectedGroup->role)
       ? ($selectedGroup->role->accent_color ?? '#4E81FA')
       : ($role->accent_color ?? '#4E81FA');
   $contrastColor = accent_contrast_color($accentColor);
  @endphp

  @php
    $hasHeaderImage = ($role->header_image && $role->header_image !== 'none') || $role->header_image_url;
  @endphp

  @if ($role->profile_image_url && !$hasHeaderImage)
  <div class="pt-8"></div>
  @endif

  <main>
    <div>
      <div class="container mx-auto pt-6 md:pt-4 pb-6 md:pb-10 px-5 md:mt-0 relative z-10"
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
        <div class="relative -mt-10 pt-10 pb-1 md:m-0 md:p-0">
            <div class="absolute -top-40 bottom-0 left-1/2 -translate-x-1/2 w-screen bg-cover bg-no-repeat bg-top md:hidden -z-10"
                 style="background-image: url('{{ $mobileBannerUrl }}');"></div>
        @endif
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl mb-0 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }} transition-[max-width] duration-300 ease-in-out mx-auto"
          data-view-width
          style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}"
        >
          <div
            class="relative overflow-hidden rounded-t-xl before:block before:absolute before:bg-[#00000033] before:-inset-0 before:rounded-t-xl"
          >

            @if ($role->header_image && $role->header_image !== 'none')
            <picture>
              <source srcset="{{ asset('images/headers') }}/{{ $role->header_image }}.webp" type="image/webp">
              <img
                class="block max-h-72 w-full object-cover"
                src="{{ asset('images/headers') }}/{{ $role->header_image }}.png"
              />
            </picture>
            @elseif ($role->header_image_url)
            <img
              class="block max-h-72 w-full object-cover"
              src="{{ $role->header_image_url }}"
            />
            @endif
          </div>
          <div id="schedule-header" class="px-6 lg:px-16 pb-1 md:pb-4 relative z-10 {{ $isRtl ? 'rtl' : '' }}">
            @if ($role->profile_image_url)
            <div class="rounded-lg w-[130px] h-[130px] -mt-[100px] mx-auto {{ $isRtl ? 'sm:mr-0 sm:ml-auto' : 'sm:mx-0 sm:-ml-2' }} mb-3 sm:mb-6 bg-white dark:bg-gray-900 flex items-center justify-center">
              <img
                class="rounded-md w-[120px] h-[120px] object-cover"
                src="{{ $role->profile_image_url }}"
                alt="{{ $role->translatedName() }}"
              />
            </div>
            @else
            <div class="h-6 sm:h-[42px]"></div>
            @endif
            @php
                $hasEmail = $role->email && $role->show_email;
                $hasWebsite = $role->website;
                $hasSocial = $role->social_links && $role->social_links != '[]';
            @endphp
            {{-- Mobile layout (< sm): stacked, centered --}}
            <div class="flex sm:hidden flex-col items-center gap-3 mb-5">
              {{-- Name/Location (centered) --}}
              <div class="text-center mb-1">
                <h1 class="text-[32px] font-semibold leading-10 text-[#151B26] dark:text-gray-100 mb-2" style="font-family: '{{ $role->font_family }}', sans-serif;">
                  {!! str_replace(' , ', '<br>', e($role->translatedName())) !!}
                </h1>
                @if($role->translatedShortDescription())
                <p class="text-sm text-[#33383C] dark:text-gray-300 mb-2">
                  {{ $role->translatedShortDescription() }}
                </p>
                @endif
                @if($role->isVenue())
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 text-sm text-[#33383C] dark:text-gray-300 hover:text-[#4E81FA] hover:underline transition-colors duration-200">
                  <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/>
                  </svg>
                  {{ $role->shortAddress() }}
                  <svg class="ml-1 h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
                @endif
              </div>

              {{-- Icons + Buttons together on same row (centered) --}}
              <div class="flex flex-wrap justify-center gap-3">
                {{-- Social icons --}}
                @if($hasEmail || $hasWebsite || $hasSocial)
                <div class="flex flex-row gap-3 items-center">
                    @if($hasEmail)
                    <a href="mailto:{{ $role->email }}"
                       class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                       style="background-color: {{ $accentColor }}"
                       data-tooltip="Email: {{ $role->email }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasWebsite)
                    <a href="{{ $role->website }}" target="_blank" rel="noopener noreferrer"
                       class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                       style="background-color: {{ $accentColor }}"
                       data-tooltip="Website: {{ App\Utils\UrlUtils::clean($role->website) }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasSocial)
                        @foreach (json_decode($role->social_links) as $link)
                        @if ($link)
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                           class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                           style="background-color: {{ $accentColor }}"
                           data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                            <x-url-icon class="w-5 h-5" :color="$contrastColor">
                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                            </x-url-icon>
                        </a>
                        @endif
                        @endforeach
                    @endif
                </div>
                @endif

                {{-- Action buttons --}}
                @if (config('app.hosted') || config('app.is_testing'))
                <div class="flex flex-row flex-wrap gap-3 items-center">
                  @if (($role->isCurator() || $role->isVenue()) && $role->accept_requests)
                  <a
                    href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.submit_event') }}
                    </button>
                  </a>
                  @endif
                  @if ((! auth()->user() || ! auth()->user()->isConnected($role->subdomain)) && ! is_demo_mode())
                  <a
                    href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.follow') }}
                    </button>
                  </a>
                  @endif
                  @if (auth()->user() && auth()->user()->isMember($role->subdomain))
                  <a
                    href="{{ config('app.url') . route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false) }}"
                    class="inline-flex items-center justify-center"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.manage') }}
                    </button>
                  </a>
                  @endif
                </div>
                @endif
              </div>

              {{-- Description below --}}
              @if($role->translatedDescription())
              <div class="text-start w-full mt-2">
                @if(str_word_count(strip_tags($role->translatedDescription())) > 5)
                <div x-data="{ expanded: false }" class="text-sm text-[#33383C] dark:text-gray-300">
                  <span x-show="!expanded" class="description-collapsed">
                    {{ Str::words(strip_tags($role->translatedDescription()), 5, '') }}...
                    <button :aria-expanded="expanded" @click="expanded = true" class="text-[#4E81FA] hover:underline whitespace-nowrap">
                      {{ __('messages.show_more') }}
                    </button>
                  </span>
                  <div x-show="expanded" x-cloak class="description-expanded">
                    <div class="custom-content [&>*:first-child]:mt-0">
                      {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                    </div>
                    <button :aria-expanded="expanded" @click="expanded = false; window.scrollTo({ top: 0, behavior: 'smooth' })" class="text-[#4E81FA] hover:underline whitespace-nowrap mt-1">
                      {{ __('messages.show_less') }}
                    </button>
                  </div>
                </div>
                @else
                <div class="text-sm text-[#33383C] dark:text-gray-300 custom-content [&>*:first-child]:mt-0">
                  {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                </div>
                @endif
              </div>
              @endif
            </div>

            {{-- Desktop layout (>= sm): horizontal with spacer --}}
            <div class="hidden sm:flex flex-col gap-3 mb-5">
              {{-- Name/Location left, Social icons + Action buttons right --}}
              <div class="flex flex-wrap items-center gap-3">
                {{-- Name/Location --}}
                <div class="min-w-0">
                  <h1 class="text-[32px] font-semibold leading-10 text-[#151B26] dark:text-gray-100 mb-2" style="font-family: '{{ $role->font_family }}', sans-serif;">
                    {!! str_replace(' , ', '<br>', e($role->translatedName())) !!}
                  </h1>
                  @if($role->translatedShortDescription())
                  <p class="text-sm text-[#33383C] dark:text-gray-300 mb-2">
                    {{ $role->translatedShortDescription() }}
                  </p>
                  @endif
                  @if($role->isVenue())
                  <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
                     target="_blank" rel="noopener noreferrer"
                     class="flex items-center gap-1.5 text-sm text-[#33383C] dark:text-gray-300 hover:text-[#4E81FA] hover:underline transition-colors duration-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/>
                    </svg>
                    {{ $role->shortAddress() }}
                    <svg class="ml-1 h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                  </a>
                  @endif
                  {{-- Social icons (desktop - simple monochrome style) --}}
                  @if($hasEmail || $hasWebsite || $hasSocial)
                  <div class="flex flex-row gap-4 items-center mt-3">
                      @if($hasEmail)
                      <a href="mailto:{{ $role->email }}"
                         class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                         data-tooltip="Email: {{ $role->email }}">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/>
                          </svg>
                      </a>
                      @endif
                      @if($hasWebsite)
                      <a href="{{ $role->website }}" target="_blank" rel="noopener noreferrer"
                         class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                         data-tooltip="Website: {{ App\Utils\UrlUtils::clean($role->website) }}">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/>
                          </svg>
                      </a>
                      @endif
                      @if($hasSocial)
                          @foreach (json_decode($role->social_links) as $link)
                          @if ($link)
                          <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                             class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                             data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                              <x-url-icon class="w-5 h-5">
                                  {{ \App\Utils\UrlUtils::clean($link->url) }}
                              </x-url-icon>
                          </a>
                          @endif
                          @endforeach
                      @endif
                  </div>
                  @endif
                </div>

                {{-- Spacer --}}
                <div class="flex-grow"></div>

                {{-- Action buttons --}}
                @if (config('app.hosted') || config('app.is_testing'))
                <div class="flex flex-row flex-wrap gap-3 items-center flex-shrink-0">
                  @if (($role->isCurator() || $role->isVenue()) && $role->accept_requests)
                  <a
                    href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.submit_event') }}
                    </button>
                  </a>
                  @endif
                  @if ((! auth()->user() || ! auth()->user()->isConnected($role->subdomain)) && ! is_demo_mode())
                  <a
                    href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.follow') }}
                    </button>
                  </a>
                  @endif
                  @if (auth()->user() && auth()->user()->isMember($role->subdomain))
                  <a
                    href="{{ config('app.url') . route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.manage') }}
                    </button>
                  </a>
                  @endif
                </div>
                @endif

                {{-- Filters Button (desktop only, in hero) - visibility controlled by JS watcher in calendar.blade.php --}}
                @if(!$event)
                <button id="hero-filters-btn"
                        data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                        class="hidden w-11 h-11 items-center justify-center rounded-md border-2 transition-all duration-200 hover:scale-105 hover:shadow-md flex-shrink-0 relative"
                        style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}; display: none;">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                    </svg>
                    {{-- Active filter count badge --}}
                    <span id="hero-filters-badge"
                          class="absolute -top-1 -end-1 min-w-[18px] h-[18px] items-center justify-center text-xs bg-[#4E81FA] text-white rounded-full px-1 hidden"></span>
                </button>
                @endif

                {{-- Calendar/List View Toggle (desktop only) --}}
                @if(!$event)
                <div class="hidden md:flex items-center rounded-md shadow-sm flex-shrink-0 transition-all duration-200 hover:scale-105 hover:shadow-md">
                    <button id="toggle-list-btn"
                            data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                            class="w-11 h-11 flex items-center justify-center rounded-s-md border-2 transition-colors {{ ($role->event_layout ?? 'calendar') === 'list' ? '' : 'text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                            style="border-color: {{ $accentColor }}; {{ ($role->event_layout ?? 'calendar') === 'list' ? 'background-color: ' . $accentColor . '; color: ' . $contrastColor : '' }}">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3,4H7V8H3V4M9,5V7H21V5H9M3,10H7V14H3V10M9,11V13H21V11H9M3,16H7V20H3V16M9,17V19H21V17H9"/>
                        </svg>
                    </button>
                    <button id="toggle-calendar-btn"
                            data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                            class="w-11 h-11 flex items-center justify-center rounded-e-md border-2 border-s-0 transition-colors {{ ($role->event_layout ?? 'calendar') === 'calendar' ? '' : 'text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                            style="border-color: {{ $accentColor }}; {{ ($role->event_layout ?? 'calendar') === 'calendar' ? 'background-color: ' . $accentColor . '; color: ' . $contrastColor : '' }}">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                        </svg>
                    </button>
                </div>
                @endif
              </div>

              {{-- Description below (full width) --}}
              @if($role->translatedDescription())
              @if(str_word_count(strip_tags($role->translatedDescription())) > 5)
              <div x-data="{ expanded: false }" class="mt-2 text-sm text-[#33383C] dark:text-gray-300">
                <span x-show="!expanded" class="description-collapsed">
                  {{ Str::words(strip_tags($role->translatedDescription()), 5, '') }}...
                  <button :aria-expanded="expanded" @click="expanded = true" class="text-[#4E81FA] hover:underline whitespace-nowrap">
                    {{ __('messages.show_more') }}
                  </button>
                </span>
                <div x-show="expanded" x-cloak class="description-expanded">
                  <div class="custom-content [&>*:first-child]:mt-0">
                    {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                  </div>
                  <button :aria-expanded="expanded" @click="expanded = false; window.scrollTo({ top: 0, behavior: 'smooth' })" class="text-[#4E81FA] hover:underline whitespace-nowrap mt-1">
                    {{ __('messages.show_less') }}
                  </button>
                </div>
              </div>
              @else
              <div class="mt-2 text-sm text-[#33383C] dark:text-gray-300 custom-content [&>*:first-child]:mt-0">
                {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
              </div>
              @endif
              @endif
            </div>
            <!--
            <div class="flex gap-3 justify-start flex-col sm:flex-row mb-6">
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Personal coach
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Yoga trainer
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Fitness trainer
                </p>
              </div>
            </div>
            -->
          </div>
        </div>

        @if (! $role->isTalent())
        @php
          // Filter events for upcoming events with videos (only on main role page, not event pages)
          $upcomingEventsWithVideos = collect();
          if (!$event) {
            $upcomingEvents = $events->filter(function($event) {
              return $event->starts_at && Carbon\Carbon::parse($event->starts_at)->isAfter(now());
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
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl px-6 lg:px-16 py-6 flex flex-col gap-6 mb-6 transition-[max-width] duration-300 ease-in-out mx-auto" data-view-width style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}">
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
                        <h3 class="text-gray-900 dark:text-gray-100 font-semibold text-lg mb-2 line-clamp-1 group-hover/card:text-blue-600 transition-colors duration-200">
                          {{ $eventData['event']->translatedName() }}
                        </h3>
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
            border-radius: 0 !important;
            box-shadow: none !important;
            border: none !important;
          }
          .dark .calendar-panel-border {
            background: transparent !important;
            border: none !important;
          }
        }
      </style>

      <div
        class="calendar-panel-border mb-6 px-0 md:px-6 lg:px-16 pt-4 pb-0 md:pb-6 transition-[max-width] duration-300 ease-in-out mx-auto"
        id="calendar-panel-wrapper"
        data-view-width
        style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}"
      >
        @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule'), 'eventLayout' => $role->event_layout ?? 'calendar', 'pastEvents' => $pastEvents ?? collect()])
      </div>

      @if ($role->youtube_links && $role->youtube_links != '[]')
        @php
          $videoLinks = json_decode($role->youtube_links);
          $videoCount = 0;
          foreach ($videoLinks as $link) {
            if ($link) $videoCount++;
          }
          $gridCols = min($videoCount, $role->getVideoColumns());
        @endphp
        @if ($videoCount > 0)
          <div
              class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl px-6 lg:px-16 py-6 flex flex-col gap-6 mb-6 transition-[max-width] duration-300 ease-in-out mx-auto" data-view-width style="max-width: {{ ($role->event_layout ?? 'calendar') === 'list' ? '56rem' : '200rem' }}"
            >
              <div class="grid grid-cols-1 md:grid-cols-{{ $gridCols }} gap-8">
              @foreach ($videoLinks as $link)
              @if ($link)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                  <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" title="{{ $role->translatedName() }} - YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                </div>
              @endif
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
[x-cloak] {
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
  background-color: rgb(31 41 55) !important;
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
    // Hero filters button
    var heroFiltersBtn = document.getElementById('hero-filters-btn');
    if (heroFiltersBtn) {
        heroFiltersBtn.addEventListener('click', function() {
            if (window.calendarVueApp && window.calendarVueApp.dynamicFilterCount > 0) {
                window.calendarVueApp.showDesktopFiltersModal = true;
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

</x-app-guest-layout>
