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

  @if ($role->profile_image_url && !$hasHeaderImage && $role->language_code == 'en')
  <div class="pt-8"></div>
  @endif

  <main>
    <div>
      <div class="container mx-auto pt-6 md:pt-4 pb-6 md:pb-10 px-5 md:mt-0 relative z-10">
        {{-- Mobile background wrapper - covers header and carousel only --}}
        @php
            $mobileBannerUrl = null;
            if ($role->background == 'image' && !request()->embed) {
                $mobileBannerUrl = $role->background_image
                    ? asset('images/backgrounds/' . $role->background_image . '.png')
                    : $role->background_image_url;
            }
        @endphp
        @if ($mobileBannerUrl)
        <div class="relative -mt-10 pt-10 pb-6 md:m-0 md:p-0">
            <div class="absolute -top-40 bottom-0 left-1/2 -translate-x-1/2 w-screen bg-cover bg-no-repeat bg-top md:hidden -z-10"
                 style="background-image: url('{{ $mobileBannerUrl }}');"></div>
        @endif
        <div class="bg-[#F5F9FE] dark:bg-gray-800 rounded-xl mb-0 md:mb-6 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }}">
          <div
            class="relative overflow-hidden rounded-t-xl before:block before:absolute before:bg-[#00000033] before:-inset-0 before:rounded-t-xl"
          >

            @if ($role->header_image && $role->header_image !== 'none')
            <img
              class="block max-h-72 w-full object-cover"
              src="{{ asset('images/headers') }}/{{ $role->header_image }}.png"
            />
            @elseif ($role->header_image_url)
            <img
              class="block max-h-72 w-full object-cover"
              src="{{ $role->header_image_url }}"
            />
            @endif
          </div>
          <div id="schedule-header" class="px-6 lg:px-16 pb-4 relative z-10 {{ $isRtl ? 'rtl' : '' }}">
            @if ($role->profile_image_url)
            <div class="rounded-lg w-[130px] h-[130px] -mt-[100px] {{ $isRtl ? '-mr-2 sm:ml-auto sm:mr-0' : '-ml-2' }} mb-3 sm:mb-6 bg-[#F5F9FE] dark:bg-gray-800 flex items-center justify-center">
              <img
                class="rounded-md w-[120px] h-[120px] object-cover"
                src="{{ $role->profile_image_url }}"
                alt="person"
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
                <h3 class="text-[32px] font-semibold leading-10 text-[#151B26] dark:text-gray-100 mb-2" style="font-family: '{{ $role->font_family }}', sans-serif;">
                  {!! str_replace(' , ', '<br>', e($role->translatedName())) !!}
                </h3>
                @if($role->isVenue())
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 text-sm text-[#33383C] dark:text-gray-300 hover:text-[#4E81FA] hover:underline transition-colors duration-200">
                  <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/>
                  </svg>
                  {{ $role->shortAddress() }}
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
                       class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                       style="background-color: {{ $accentColor }}"
                       title="{{ $role->email }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasWebsite)
                    <a href="{{ $role->website }}" target="_blank"
                       class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                       style="background-color: {{ $accentColor }}"
                       title="{{ App\Utils\UrlUtils::clean($role->website) }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasSocial)
                        @foreach (json_decode($role->social_links) as $link)
                        @if ($link)
                        <a href="{{ $link->url }}" target="_blank"
                           class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                           style="background-color: {{ $accentColor }}"
                           title="{{ App\Utils\UrlUtils::clean($link->url) }}">
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
                      style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.submit_event') }}
                    </button>
                  </a>
                  @endif
                  @if (! auth()->user() || ! auth()->user()->isConnected($role->subdomain))
                  <a
                    href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center"
                  >
                    <button
                      type="button"
                      style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
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
                      style="border-color: {{ $role->accent_color ?? '#4E81FA' }}; color: {{ $role->accent_color ?? '#4E81FA' }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold bg-transparent border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.edit_schedule') }}
                    </button>
                  </a>
                  @endif
                </div>
                @endif
              </div>

              {{-- Description below --}}
              @if($role->translatedDescription())
              <div class="text-left w-full mt-2">
                <div x-data="{ expanded: false }" class="text-sm text-[#33383C] dark:text-gray-300">
                  <span x-show="!expanded" class="description-collapsed">
                    {{ Str::words(strip_tags($role->translatedDescription()), 5, '') }}...
                    <button @click="expanded = true" class="text-[#4E81FA] hover:underline whitespace-nowrap">
                      {{ __('messages.show_more') }}
                    </button>
                  </span>
                  <div x-show="expanded" x-cloak class="description-expanded">
                    <div class="custom-content [&>*:first-child]:mt-0">
                      {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                    </div>
                    <button @click="expanded = false; window.scrollTo({ top: 0, behavior: 'smooth' })" class="text-[#4E81FA] hover:underline whitespace-nowrap mt-1">
                      {{ __('messages.show_less') }}
                    </button>
                  </div>
                </div>
              </div>
              @endif
            </div>

            {{-- Desktop layout (>= sm): horizontal with spacer --}}
            <div class="hidden sm:flex flex-col gap-3 mb-5">
              {{-- Top row: Name/Location left, Buttons right --}}
              <div class="flex flex-wrap items-center gap-3">
                {{-- Name/Location --}}
                <div class="min-w-0">
                  <h3 class="text-[32px] font-semibold leading-10 text-[#151B26] dark:text-gray-100 mb-2" style="font-family: '{{ $role->font_family }}', sans-serif;">
                    {!! str_replace(' , ', '<br>', e($role->translatedName())) !!}
                  </h3>
                  @if($role->isVenue())
                  <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
                     target="_blank"
                     class="flex items-center gap-1.5 text-sm text-[#33383C] dark:text-gray-300 hover:text-[#4E81FA] hover:underline transition-colors duration-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/>
                    </svg>
                    {{ $role->shortAddress() }}
                  </a>
                  @endif
                </div>

                {{-- Spacer to push buttons right --}}
                <div class="flex-grow"></div>

                {{-- Social icons --}}
                @if($hasEmail || $hasWebsite || $hasSocial)
                <div class="flex flex-row gap-3 items-center flex-shrink-0">
                    @if($hasEmail)
                    <a href="mailto:{{ $role->email }}"
                       class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                       style="background-color: {{ $accentColor }}"
                       title="{{ $role->email }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasWebsite)
                    <a href="{{ $role->website }}" target="_blank"
                       class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                       style="background-color: {{ $accentColor }}"
                       title="{{ App\Utils\UrlUtils::clean($role->website) }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasSocial)
                        @foreach (json_decode($role->social_links) as $link)
                        @if ($link)
                        <a href="{{ $link->url }}" target="_blank"
                           class="w-10 h-10 rounded-md flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200"
                           style="background-color: {{ $accentColor }}"
                           title="{{ App\Utils\UrlUtils::clean($link->url) }}">
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
                <div class="flex flex-row flex-wrap gap-3 items-center flex-shrink-0">
                  @if (($role->isCurator() || $role->isVenue()) && $role->accept_requests)
                  <a
                    href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.submit_event') }}
                    </button>
                  </a>
                  @endif
                  @if (! auth()->user() || ! auth()->user()->isConnected($role->subdomain))
                  <a
                    href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
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
                      style="border-color: {{ $role->accent_color ?? '#4E81FA' }}; color: {{ $role->accent_color ?? '#4E81FA' }}"
                      class="inline-flex items-center rounded-md px-5 py-2.5 text-sm font-semibold bg-transparent border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.edit_schedule') }}
                    </button>
                  </a>
                  @endif
                </div>
                @endif
              </div>

              {{-- Description below (full width) --}}
              @if($role->translatedDescription())
              <div x-data="{ expanded: false }" class="mt-2 text-sm text-[#33383C] dark:text-gray-300">
                <span x-show="!expanded" class="description-collapsed">
                  {{ Str::words(strip_tags($role->translatedDescription()), 5, '') }}...
                  <button @click="expanded = true" class="text-[#4E81FA] hover:underline whitespace-nowrap">
                    {{ __('messages.show_more') }}
                  </button>
                </span>
                <div x-show="expanded" x-cloak class="description-expanded">
                  <div class="custom-content [&>*:first-child]:mt-0">
                    {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                  </div>
                  <button @click="expanded = false; window.scrollTo({ top: 0, behavior: 'smooth' })" class="text-[#4E81FA] hover:underline whitespace-nowrap mt-1">
                    {{ __('messages.show_less') }}
                  </button>
                </div>
              </div>
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
            @if($role->phone)
            <div class="flex flex-col sm:flex-row gap-4 pb-4 items-center {{ $isRtl ? 'sm:justify-end' : '' }}">
              <div
                class="flex flex-row gap-2 items-center relative duration-300 text-[#33383C] dark:text-gray-300 fill-[#33383C] dark:fill-gray-300 hover:text-[#4E81FA] hover:fill-[#4E81FA]"
              >
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M10.0376 5.31617L10.6866 6.4791C11.2723 7.52858 11.0372 8.90532 10.1147 9.8278C10.1147 9.8278 10.1147 9.8278 10.1147 9.8278C10.1146 9.82792 8.99588 10.9468 11.0245 12.9755C13.0525 15.0035 14.1714 13.8861 14.1722 13.8853C14.1722 13.8853 14.1722 13.8853 14.1722 13.8853C15.0947 12.9628 16.4714 12.7277 17.5209 13.3134L18.6838 13.9624C20.2686 14.8468 20.4557 17.0692 19.0628 18.4622C18.2258 19.2992 17.2004 19.9505 16.0669 19.9934C14.1588 20.0658 10.9183 19.5829 7.6677 16.3323C4.41713 13.0817 3.93421 9.84122 4.00655 7.93309C4.04952 6.7996 4.7008 5.77423 5.53781 4.93723C6.93076 3.54428 9.15317 3.73144 10.0376 5.31617Z"
                  />
                </svg>
                <a href="tel:{{ $role->phone }}" class="text-sm"
                  >{{ $role->phone }}</a
                >
              </div>
            </div>
            @endif
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
        <div class="bg-[#F5F9FE] dark:bg-gray-800 rounded-lg px-6 lg:px-16 py-6 flex flex-col gap-6 mb-6">
          <!-- Carousel Container -->
          <div class="relative group">
            <!-- Carousel Track -->
            <div id="events-carousel" class="flex overflow-x-auto scrollbar-hide gap-6 pb-4 pt-4 {{ $isRtl ? 'rtl' : '' }}">
              @foreach($upcomingEventsWithVideos as $eventData)
                @foreach($eventData['video_roles'] as $videoRole)
                  <div class="carousel-item flex-shrink-0 w-full sm:w-80 bg-white rounded-xl shadow-md overflow-hidden group/card">
                    <!-- Video iframe -->
                    <iframe 
                      class="w-full h-48 object-cover"
                      src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($videoRole->getFirstVideoUrl()) }}" 
                      frameborder="0" 
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                      referrerpolicy="strict-origin-when-cross-origin" 
                      allowfullscreen>
                    </iframe>
                    
                    <!-- Event details below video -->
                    <div class="p-4">
                      <a href="{{ $eventData['event']->getGuestUrl($role->subdomain) }}" class="block">
                        <h3 class="text-gray-900 font-semibold text-lg mb-2 line-clamp-1 group-hover/card:text-blue-600 transition-colors duration-200">
                          {{ $eventData['event']->translatedName() }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-1 group-hover/card:text-gray-700 transition-colors duration-200">
                          {{ $eventData['event']->getVenueDisplayName() }}
                        </p>
                        <p class="text-gray-500 text-xs group-hover/card:text-gray-600 transition-colors duration-200">
                          {{ $eventData['event']->localStartsAt(true, request()->date) }}
                        </p>
                      </a>
                    </div>
                  </div>
                @endforeach
              @endforeach
            </div>
            
            <!-- Navigation arrows -->
            <button id="carousel-prev" class="absolute {{ $isRtl ? 'right-2' : 'left-2' }} top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white rounded-full p-2 shadow-md transition-all duration-200 opacity-100 z-20">
              <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isRtl ? 'M9 5l7 7-7 7' : 'M15 19l-7-7 7-7' }}"></path>
              </svg>
            </button>
            <button id="carousel-next" class="absolute {{ $isRtl ? 'left-2' : 'right-2' }} top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white rounded-full p-2 shadow-md transition-all duration-200 opacity-100 z-20">
              <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

      <div
        class="bg-[#F5F9FE] dark:bg-gray-800 rounded-none lg:rounded-lg px-5 lg:px-16 py-6 flex flex-col gap-6 mb-6 lg:mx-0 max-lg:w-screen max-lg:ml-[calc(-50vw+50%)]"
      >  
        @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule')])
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
              class="bg-[#F5F9FE] dark:bg-gray-800 rounded-lg px-6 lg:px-16 py-6 flex flex-col gap-6 mb-6"
            >
              <div class="grid grid-cols-1 md:grid-cols-{{ $gridCols }} gap-8">
              @foreach ($videoLinks as $link)
              @if ($link)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                  <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
              @endif
              @endforeach
            </div>          
          </div>
        @endif
      @endif

    </div>
  </main>

<style>
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

/* Disabled state for navigation buttons */
#carousel-prev[style*="opacity: 0.3"],
#carousel-next[style*="opacity: 0.3"] {
  cursor: not-allowed;
  pointer-events: none;
}
</style>
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchLoading = document.getElementById('search-loading');
    const searchResults = document.getElementById('search-results');
    const noResults = document.getElementById('no-results');
    let searchTimeout = null;
    let currentFocusIndex = -1;
    let lastResults = [];
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value;
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Hide all dropdowns initially and reset focus
            searchResults.classList.add('hidden');
            noResults.classList.add('hidden');
            currentFocusIndex = -1;
            
            if (query.length < 2) {
                searchLoading.classList.add('hidden');
                return;
            }
            
            searchLoading.classList.remove('hidden');
            
            // Debounce the search
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Add keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            const resultItems = searchResults.querySelectorAll('.search-result-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocusIndex = Math.min(currentFocusIndex + 1, resultItems.length - 1);
                updateFocus(resultItems);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocusIndex = Math.max(currentFocusIndex - 1, -1);
                updateFocus(resultItems);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocusIndex >= 0 && resultItems[currentFocusIndex]) {
                    resultItems[currentFocusIndex].click();
                }
            } else if (e.key === 'Escape') {
                searchInput.value = '';
                searchResults.classList.add('hidden');
                noResults.classList.add('hidden');
                currentFocusIndex = -1;
            }
        });
    }
    
    function performSearch(query) {
        const selectedGroup = getSelectedGroupFromVue();
        let searchUrl = `/search_events/{{ $role->subdomain }}?q=${encodeURIComponent(query)}`;
        
        if (selectedGroup) {
            searchUrl += `&group=${encodeURIComponent(selectedGroup)}`;
        }
        
        fetch(searchUrl)
            .then(response => response.json())
            .then(data => {
                searchLoading.classList.add('hidden');
                lastResults = data;
                currentFocusIndex = -1;
                
                if (data.length > 0) {
                    displaySearchResults(data);
                } else {
                    noResults.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                searchLoading.classList.add('hidden');
                noResults.classList.remove('hidden');
            });
    }
    
    function getSelectedGroupFromVue() {
        // Try to get the selected group from the Vue.js app in the calendar
        try {
            // For Vue 3, we need to access the app instance differently
            const calendarApp = document.getElementById('calendar-app');
            if (calendarApp && window.calendarVueApp) {
                return window.calendarVueApp.selectedGroup || '';
            }
            
            // Fallback: check URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const scheduleParam = urlParams.get('schedule');
            
            // Also check the current path for group slug
            const pathSegments = window.location.pathname.split('/');
            if (pathSegments.length >= 3 && pathSegments[2] !== '') {
                return pathSegments[2];
            }
            
            return scheduleParam || '';
        } catch (e) {
            console.log('Could not access Vue app, using URL fallback');
            // Fallback: check URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const scheduleParam = urlParams.get('schedule');
            
            // Also check the current path for group slug
            const pathSegments = window.location.pathname.split('/');
            if (pathSegments.length >= 3 && pathSegments[2] !== '') {
                return pathSegments[2];
            }
            
            return scheduleParam || '';
        }
    }
    
    function updateFocus(resultItems) {
        // Remove focus from all items
        resultItems.forEach((item, index) => {
            if (index === currentFocusIndex) {
                item.classList.add('bg-blue-50', 'border-l-blue-500');
                item.classList.remove('hover:bg-gray-50', 'border-l-transparent');
            } else {
                item.classList.remove('bg-blue-50', 'border-l-blue-500');
                item.classList.add('hover:bg-gray-50', 'border-l-transparent');
            }
        });
    }
    
    function displaySearchResults(events) {
        const resultsHtml = events.map((event, index) => {
            const imageHtml = event.image_url 
                ? `<img src="${event.image_url}" alt="${event.name}" class="w-12 h-12 object-cover rounded-md">`
                : `<div class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                     <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                     </svg>
                   </div>`;
            
            const timeHtml = event.local_starts_at 
                ? `<p class="text-xs text-gray-400">${formatEventTime(event.local_starts_at)}</p>`
                : '';
            
            return `
                <div class="search-result-item flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 border-l-4 border-l-transparent last:border-b-0" onclick="selectEvent('${event.guest_url}', ${index})">
                    <div class="flex-shrink-0 mr-3">
                        ${imageHtml}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${event.name}</p>
                        <p class="text-sm text-gray-500 truncate">${event.venue_name}</p>
                        ${timeHtml}
                    </div>
                </div>
            `;
        }).join('');
        
        searchResults.innerHTML = resultsHtml;
        searchResults.classList.remove('hidden');
    }
    
    function formatEventTime(datetime) {
        if (!datetime) return '';
        
        const date = new Date(datetime);
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            day: 'numeric',
            month: 'short'
        });
    }
    
    window.selectEvent = function(url, index) {
        // Clear search when event is selected
        searchInput.value = '';
        searchResults.classList.add('hidden');
        noResults.classList.add('hidden');
        currentFocusIndex = -1;
        
        // Add current group parameter to URL to preserve selection
        const selectedGroup = getSelectedGroupFromVue();
        let finalUrl = url;
        
        if (selectedGroup) {
            const urlObj = new URL(url, window.location.origin);
            urlObj.searchParams.set('schedule', selectedGroup);
            finalUrl = urlObj.toString();
        }
        
        // Navigate to the event page
        window.location.href = finalUrl;
    };
    
    // Global keyboard shortcut to focus search
    document.addEventListener('keydown', function(e) {
        // Focus search box with '/' key (like GitHub)
        if (e.key === '/' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Handle escape globally
        if (e.key === 'Escape' && (document.activeElement === searchInput || searchResults.classList.contains('visible'))) {
            searchInput.value = '';
            searchResults.classList.add('hidden');
            noResults.classList.add('hidden');
            currentFocusIndex = -1;
            searchInput.blur();
        }
    });
    
    // Handle clicking outside search dropdown to close it
    document.addEventListener('click', function(e) {
        const searchContainer = document.querySelector('.search-container');
        if (searchContainer && !searchContainer.contains(e.target)) {
            searchResults.classList.add('hidden');
            noResults.classList.add('hidden');
            currentFocusIndex = -1;
        }
    });
    
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
        if (isRtl && carouselItems.length > 1) {
            console.log('RTL carousel initialized');
        }
        
        // Debug logging
        console.log('Carousel initialized:', {
            isRtl: isRtl,
            itemCount: carouselItems.length,
            scrollWidth: carousel.scrollWidth,
            clientWidth: carousel.clientWidth
        });
    }
});
</script>

</x-app-guest-layout>
