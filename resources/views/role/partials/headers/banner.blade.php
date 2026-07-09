        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl mb-0 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }} transition-[max-width] duration-300 ease-in-out mx-auto"
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
                alt="{{ $role->translatedName() }}"
              />
            </picture>
            @elseif ($role->header_image_url && $role->header_image !== 'none')
            <img
              class="block max-h-72 w-full object-cover"
              src="{{ $role->header_image_url }}"
              alt="{{ $role->translatedName() }}"
            />
            @endif
          </div>
          <header id="schedule-header" class="px-6 lg:px-16 pb-1 md:pb-4 relative z-10 {{ $isRtl ? 'rtl' : '' }}">
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
                $hasPhone = $role->phone && $role->show_phone && $role->phone_verified_at;
                $hasWebsite = $role->website;
                $hasSocial = $role->social_links && $role->social_links != '[]';
                $hasPayment = $role->payment_links && $role->payment_links != '[]';
            @endphp
            {{-- Mobile layout (< sm): stacked, centered --}}
            <div class="flex sm:hidden flex-col items-center gap-3 mb-5">
              {{-- Name/Location (centered) --}}
              <div class="text-center mb-1">
                <h1 class="text-[32px] font-semibold leading-10 text-[#151B26] dark:text-gray-100 mb-2" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
                  {!! str_replace(' , ', '<br>', e($role->translatedName())) !!}
                </h1>
                @if($role->translatedShortDescription())
                <p class="text-sm text-[#33383C] dark:text-gray-300 mb-2">
                  {{ $role->translatedShortDescription() }}
                </p>
                @endif
                @if($role->isVenue())
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
                   target="_blank" rel="noopener noreferrer nofollow"
                   class="inline-flex items-center gap-1.5 text-sm text-[#33383C] dark:text-gray-300 hover:text-[var(--brand-blue)] hover:underline transition-colors duration-200">
                  <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/>
                  </svg>
                  {{ $role->shortAddress() }}
                  <svg class="ml-1 h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                </a>
                @endif
              </div>

              {{-- Icons + Buttons together on same row (centered) --}}
              <div class="flex flex-row flex-wrap items-center justify-center gap-3">
                {{-- Social icons --}}
                @if($hasEmail || $hasPhone || $hasWebsite || $hasSocial || $hasPayment)
                <div class="flex flex-row flex-wrap gap-3 items-center justify-center">
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
                    @if($hasPhone)
                    <a href="tel:{{ $role->phone }}"
                       class="w-10 h-10 rounded-lg flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                       style="background-color: {{ $accentColor }}"
                       data-tooltip="Phone: {{ $role->phone }}">
                        <svg class="w-5 h-5" fill="{{ $contrastColor }}" viewBox="0 0 24 24">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasWebsite)
                    <a href="{{ $role->website }}" target="_blank" rel="noopener noreferrer nofollow"
                       class="w-10 h-10 rounded-lg flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                       style="background-color: {{ $accentColor }}"
                       data-tooltip="Website: {{ App\Utils\UrlUtils::clean($role->website) }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <path fill="{{ $contrastColor }}" fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/>
                        </svg>
                    </a>
                    @endif
                    @if($hasSocial)
                        @foreach ($role->decodeLinks('social_links') as $link)
                        @php $gpLinkPlatform = \App\Utils\UrlUtils::detectPlatform($link->url); @endphp
                        <a href="{{ $gpLinkPlatform !== 'website' ? $role->getGuestUrl() . '/' . $gpLinkPlatform : $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
                           class="w-10 h-10 rounded-lg flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                           style="background-color: {{ $accentColor }}"
                           data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                            <x-url-icon class="w-5 h-5" :color="$contrastColor">
                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                            </x-url-icon>
                        </a>
                        @endforeach
                    @endif
                    @if($hasPayment)
                        @foreach ($role->decodeLinks('payment_links') as $link)
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
                           class="w-10 h-10 rounded-lg flex justify-center items-center shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 social-tooltip"
                           style="background-color: {{ $accentColor }}"
                           data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                            <x-url-icon class="w-5 h-5" :color="$contrastColor">
                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                            </x-url-icon>
                        </a>
                        @endforeach
                    @endif
                </div>
                @endif

                {{-- Action buttons --}}
                @php
                $hasSubmitButton = ($role->isCurator() || $role->isVenue() || $role->isTalent()) && $role->accept_requests;
                @endphp
                @if ($hasSubmitButton || config('app.hosted') || config('app.is_testing'))
                <div class="flex flex-row flex-wrap gap-3 items-center justify-center">
                  @if ($hasSubmitButton)
                  <a
                    href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ $role->isTalent() ? $role->customLabel('request_to_book') : $role->customLabel('submit_event') }}
                    </button>
                  </a>
                  @endif
                  @if (config('app.hosted') || config('app.is_testing'))
                  @if (! is_demo_mode() && (
                      ($hasSubmitButton && auth()->user() && ! auth()->user()->isFollowing($role->subdomain) && ! auth()->user()->isConnected($role->subdomain)) ||
                      (! $hasSubmitButton && (! auth()->user() || ! auth()->user()->isConnected($role->subdomain)))
                  ))
                  <button
                    type="button"
                    data-follow-trigger
                    data-follow-url="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                    data-schedule-name="{{ $role->name }}"
                    data-schedule-image="{{ $role->profile_image_url }}"
                    data-accent-color="{{ $accentColor }}"
                    data-contrast-color="{{ $contrastColor }}"
                    style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                    class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                  >
                    {{ $role->customLabel('follow') }}
                  </button>
                  @endif
                  @if (auth()->user() && auth()->user()->isMember($role->subdomain))
                  <a
                    href="{{ app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false)) }}"
                    class="inline-flex items-center justify-center"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm font-semibold border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.manage') }}
                    </button>
                  </a>
                  @endif
                  @endif
                </div>
                @endif
              </div>

              {{-- Description below --}}
              @if($role->translatedDescription())
              @php $descPreview = \Illuminate\Support\Str::words(html_entity_decode(strip_tags($role->translatedDescription())), 5, '...'); @endphp
              <div class="w-full mt-2">
                <div x-data="{ expanded: false, long: false }"
                     x-init="$nextTick(() => { long = $refs.content.scrollHeight > $refs.content.clientHeight })"
                     class="text-start text-sm text-[#33383C] dark:text-gray-300">
                  <div x-show="long && !expanded" x-cloak>{{ $descPreview }}</div>
                  <div x-ref="content" x-show="!long || expanded" :class="{ 'line-clamp-3': !long }" class="custom-content">
                    {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                  </div>
                  <button x-show="long && !expanded" x-cloak @click="expanded = true" class="text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap mt-1">
                    {{ $role->customLabel('show_more') }}
                  </button>
                  <button x-show="long && expanded" x-cloak @click="expanded = false" class="text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap mt-1">
                    {{ $role->customLabel('show_less') }}
                  </button>
                </div>
              </div>
              @endif
            </div>

            {{-- Desktop layout (>= sm): horizontal with spacer --}}
            <div class="hidden sm:flex flex-col gap-3 mb-3">
              {{-- Row 1: Name (full width) --}}
              <h1 class="text-[32px] font-semibold leading-10 text-[#151B26] dark:text-gray-100" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
                {!! str_replace(' , ', '<br>', e($role->translatedName())) !!}
              </h1>
              {{-- Row 2: Description/Location/Social left, Action buttons right --}}
              <div class="flex items-start gap-3">
                {{-- Description/Location/Social --}}
                <div class="min-w-0 flex-1">
                  @if($role->translatedShortDescription())
                  <p class="text-sm text-[#33383C] dark:text-gray-300 mb-2">
                    {{ $role->translatedShortDescription() }}
                  </p>
                  @endif
                  @if($role->isVenue())
                  <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}"
                     target="_blank" rel="noopener noreferrer nofollow"
                     class="flex items-center gap-1.5 text-sm text-[#33383C] dark:text-gray-300 hover:text-[var(--brand-blue)] hover:underline transition-colors duration-200">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"/>
                    </svg>
                    {{ $role->shortAddress() }}
                    <svg class="ml-1 h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                  </a>
                  @endif
                  {{-- Social icons (desktop - simple monochrome style) --}}
                  @if($hasEmail || $hasPhone || $hasWebsite || $hasSocial || $hasPayment)
                  <div class="flex flex-row flex-wrap gap-4 items-center mt-3">
                      @if($hasEmail)
                      <a href="mailto:{{ $role->email }}"
                         class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                         data-tooltip="Email: {{ $role->email }}">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/>
                          </svg>
                      </a>
                      @endif
                      @if($hasPhone)
                      <a href="tel:{{ $role->phone }}"
                         class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                         data-tooltip="Phone: {{ $role->phone }}">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                          </svg>
                      </a>
                      @endif
                      @if($hasWebsite)
                      <a href="{{ $role->website }}" target="_blank" rel="noopener noreferrer nofollow"
                         class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                         data-tooltip="Website: {{ App\Utils\UrlUtils::clean($role->website) }}">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/>
                          </svg>
                      </a>
                      @endif
                      @if($hasSocial)
                          @foreach ($role->decodeLinks('social_links') as $link)
                          @php $gpLinkPlatform2 = \App\Utils\UrlUtils::detectPlatform($link->url); @endphp
                          <a href="{{ $gpLinkPlatform2 !== 'website' ? $role->getGuestUrl() . '/' . $gpLinkPlatform2 : $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
                             class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                             data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                              <x-url-icon class="w-5 h-5" color="currentColor">
                                  {{ \App\Utils\UrlUtils::clean($link->url) }}
                              </x-url-icon>
                          </a>
                          @endforeach
                      @endif
                      @if($hasPayment)
                          @foreach ($role->decodeLinks('payment_links') as $link)
                          <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer nofollow"
                             class="text-[#33383C] dark:text-gray-400 hover:text-[#151B26] dark:hover:text-gray-200 transition-colors social-tooltip"
                             data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                              <x-url-icon class="w-5 h-5" color="currentColor">
                                  {{ \App\Utils\UrlUtils::clean($link->url) }}
                              </x-url-icon>
                          </a>
                          @endforeach
                      @endif
                  </div>
                  @endif
                </div>

                {{-- Action buttons --}}
                @if ($hasSubmitButton || config('app.hosted') || config('app.is_testing'))
                <div class="flex flex-row flex-wrap gap-3 items-center flex-shrink-0">
                  @if ($hasSubmitButton)
                  <a
                    href="{{ route('role.request', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ $role->isTalent() ? $role->customLabel('request_to_book') : $role->customLabel('submit_event') }}
                    </button>
                  </a>
                  @endif
                  @if (config('app.hosted') || config('app.is_testing'))
                  @if (! is_demo_mode() && (
                      ($hasSubmitButton && auth()->user() && ! auth()->user()->isFollowing($role->subdomain) && ! auth()->user()->isConnected($role->subdomain)) ||
                      (! $hasSubmitButton && (! auth()->user() || ! auth()->user()->isConnected($role->subdomain)))
                  ))
                  <button
                    type="button"
                    data-follow-trigger
                    data-follow-url="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                    data-schedule-name="{{ $role->name }}"
                    data-schedule-image="{{ $role->profile_image_url }}"
                    data-accent-color="{{ $accentColor }}"
                    data-contrast-color="{{ $contrastColor }}"
                    style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                    class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 flex-shrink-0"
                  >
                    {{ $role->customLabel('follow') }}
                  </button>
                  @endif
                  @if (auth()->user() && auth()->user()->isMember($role->subdomain))
                  <a
                    href="{{ app_url(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false)) }}"
                    class="inline-flex items-center justify-center flex-shrink-0"
                  >
                    <button
                      type="button"
                      style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                      class="inline-flex items-center rounded-lg px-5 py-2.5 text-sm font-semibold border-2 transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                      {{ __('messages.manage') }}
                    </button>
                  </a>
                  @endif
                  @endif
                </div>
                @endif

                {{-- Filters Button (desktop only, in hero) - visibility controlled by JS watcher in calendar.blade.php --}}
                @if(!$event)
                <button id="hero-filters-btn"
                        data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                        class="hidden w-11 h-11 items-center justify-center rounded-lg border-2 transition-all duration-200 hover:scale-105 hover:shadow-md flex-shrink-0 relative"
                        style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}; display: none;">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                    </svg>
                    {{-- Active filter count badge --}}
                    <span id="hero-filters-badge"
                          class="absolute -top-1 -end-1 min-w-[18px] h-[18px] items-center justify-center text-xs bg-[var(--brand-button-bg)] text-white rounded-full px-1 hidden"></span>
                </button>
                @endif

                {{-- Calendar/List View Toggle (desktop only) --}}
                @if(!$event)
                <div class="hidden md:flex items-center rounded-md shadow-sm flex-shrink-0">
                    <button id="toggle-list-btn"
                            data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                            class="w-11 h-11 flex items-center justify-center rounded-s-md border-2 transition-all duration-200 {{ ($role->event_layout ?? 'calendar') !== 'list' ? 'hover:scale-105 hover:shadow-md' : 'text-gray-900 dark:text-white' }}"
                            style="border-color: {{ $accentColor }}; {{ ($role->event_layout ?? 'calendar') !== 'list' ? 'background-color: ' . $accentColor . '; color: ' . $contrastColor : '' }}">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3,4H7V8H3V4M9,5V7H21V5H9M3,10H7V14H3V10M9,11V13H21V11H9M3,16H7V20H3V16M9,17V19H21V17H9"/>
                        </svg>
                    </button>
                    <button id="toggle-calendar-btn"
                            data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                            class="w-11 h-11 flex items-center justify-center rounded-e-md border-2 border-s-0 transition-all duration-200 {{ ($role->event_layout ?? 'calendar') !== 'calendar' ? 'hover:scale-105 hover:shadow-md' : 'text-gray-900 dark:text-white' }}"
                            style="border-color: {{ $accentColor }}; {{ ($role->event_layout ?? 'calendar') !== 'calendar' ? 'background-color: ' . $accentColor . '; color: ' . $contrastColor : '' }}">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                        </svg>
                    </button>
                </div>
                @endif
              </div>

              {{-- Description below (full width) --}}
              @if($role->translatedDescription())
              @php $descPreviewDesktop = \Illuminate\Support\Str::words(html_entity_decode(strip_tags($role->translatedDescription())), 5, '...'); @endphp
              <div x-data="{ expanded: false, long: false }"
                   x-init="$nextTick(() => { long = $refs.content.scrollHeight > $refs.content.clientHeight })"
                   class="mt-2 text-sm text-[#33383C] dark:text-gray-300">
                <div x-show="long && !expanded" x-cloak>{{ $descPreviewDesktop }}</div>
                <div x-ref="content" x-show="!long || expanded" :class="{ 'line-clamp-3': !long }" class="custom-content">
                  {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
                </div>
                <button x-show="long && !expanded" x-cloak @click="expanded = true" class="text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap mt-1">
                  {{ $role->customLabel('show_more') }}
                </button>
                <button x-show="long && expanded" x-cloak @click="expanded = false" class="text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap mt-1">
                  {{ $role->customLabel('show_less') }}
                </button>
              </div>
              @endif
            </div>
            <!--
            <div class="flex gap-3 justify-start flex-col sm:flex-row mb-6">
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[var(--brand-blue)]">
                  Personal coach
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[var(--brand-blue)]">
                  Yoga trainer
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[var(--brand-blue)]">
                  Fitness trainer
                </p>
              </div>
            </div>
            -->

          </header>
        </div>
