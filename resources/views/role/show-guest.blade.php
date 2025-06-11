<x-app-guest-layout :role="$role" :fonts="$fonts">

  <main>
    <div>
      <div class="container mx-auto py-10 px-5">
        <div class="bg-[#F5F9FE] rounded-2xl mb-6">
          <div
            class="relative before:block before:absolute before:bg-[#00000033] before:-inset-0"
          >
            
            @if ($role->header_image)
            <img
              class="block max-h-72 w-full object-cover rounded-t-2xl"
              src="{{ asset('images/headers') }}/{{ $role->header_image }}.png"
            />
            @elseif ($role->header_image_url)
            <img
              class="block max-h-72 w-full object-cover rounded-t-2xl"
              src="{{ $role->header_image_url }}"
            />
            @endif
          </div>
          <div class="px-6 lg:px-16 pb-6 relative z-10">
            @if ($role->profile_image_url)
            <div class="rounded-2xl w-[196px] h-[196px] -mt-[96px] -ml-2 mb-8 bg-[#F5F9FE] flex items-center justify-center">
              <img
                class="rounded-2xl w-[180px] h-[180px] object-cover"
                src="{{ $role->profile_image_url }}"
                alt="person"
              />
            </div>
            @else
            <div style="height: 42px;"></div>
            @endif
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6 mb-5">
              <div class="text-center lg:text-left">
                <h3 class="text-[32px] font-semibold leading-10 text-[#151B26] mb-2">
                  {{ $role->translatedName() }}
                </h3>
                @if (auth()->user() && auth()->user()->isMember($role->subdomain))
                <a 
                  href="{{ config('app.url') . route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'], false) }}"
                  class="text-sm font-medium text-[#4E81FA] hover:underline inline-block"
                >
                  {{ __('messages.edit_schedule') }}
                </a>
                @endif
              </div>
              <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-end items-center">
              
              <!-- Event Search Box -->
              <div class="relative search-container flex-shrink-0">
                <div class="relative">
                  <input 
                    type="text" 
                    id="search-input"
                    placeholder="{{ __('messages.search_events') }}"
                    class="w-full sm:w-64 pl-10 pr-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                  >
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </div>
                  <!-- Loading indicator -->
                  <div id="search-loading" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
                    <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </div>
                </div>
                
                <!-- Search Results Dropdown -->
                <div id="search-results" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-96 overflow-y-auto">
                  <!-- Results will be populated by JavaScript -->
                </div>
                
                <!-- No Results Message -->
                <div id="no-results" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg p-3">
                  <p class="text-sm text-gray-500 text-center">{{ __('messages.no_events_found') }}</p>
                </div>
              </div>
              
              @if ($role->isCurator() && $role->is_open)
              <a
                href="{{ route('role.follow', ['subdomain' => $role->subdomain], ['add_event' => true]) }}"
                class="inline-flex items-center justify-center flex-shrink-0"
              >
              <button
                  type="button"
                  style="background-color: {{ $role->accent_color ?? '#4E81FA' }}"
                  class="inline-flex items-center rounded-md px-6 py-3 hover:opacity-90 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 h-12"
                >
                  {{ __('messages.add_event') }}
                </button>
              </a>
              @endif
              
                @if (!auth()->user() || !auth()->user()->isMember($role->subdomain))
                <a
                  href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}"
                  class="inline-flex items-center justify-center flex-shrink-0 {{ $role->isCurator() && $role->is_open ? 'hidden' : '' }}"
                >
                  <button
                    type="button"
                    style="background-color: {{ $role->accent_color ?? '#4E81FA' }}"
                    class="inline-flex items-center rounded-md px-6 py-3 hover:opacity-90 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 h-12"
                  >
                    {{ __('messages.follow') }}
                  </button>
                </a>
                @endif
              </div>
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
            <div class="flex flex-col sm:flex-row gap-4 items-center">
              @if($role->phone)
              <div              
                class="flex flex-row gap-2 items-center relative duration-300 text-[#33383C] fill-[#33383C] hover:text-[#4E81FA] hover:fill-[#4E81FA] sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-[#33383C]"
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
              @endif
              @if($role->email && $role->show_email)
              <div
                class="flex flex-row gap-2 items-center relative duration-300 text-[#33383C] fill-[#33383C] hover:text-[#4E81FA] hover:fill-[#4E81FA] sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px]"
              >
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"
                  />
                </svg>
                <a href="mailto:{{ $role->email }}" class="text-sm"
                  >{{ $role->email }}</a
                >
              </div>
              @endif
              @if($role->website)
              <div
                class="flex flex-row gap-2 items-center relative duration-300 text-[#33383C] fill-[#33383C] hover:text-[#4E81FA] hover:fill-[#4E81FA] sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px]"
              >
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M2.02783 11.25C2.41136 6.07745 6.72957 2 12.0001 2C11.1693 2 10.4295 2.36421 9.82093 2.92113C9.21541 3.47525 8.70371 4.24878 8.28983 5.16315C7.87352 6.08292 7.55013 7.15868 7.33126 8.32611C7.1558 9.26194 7.04903 10.2485 7.01344 11.25H2.02783ZM2.02783 12.75H7.01344C7.04903 13.7515 7.1558 14.7381 7.33126 15.6739C7.55013 16.8413 7.87351 17.9171 8.28983 18.8368C8.70371 19.7512 9.21541 20.5247 9.82093 21.0789C10.4295 21.6358 11.1693 22 12.0001 22C6.72957 22 2.41136 17.9226 2.02783 12.75Z"
                  />
                  <path
                    d="M12.0001 3.39535C11.7251 3.39535 11.3699 3.51236 10.9567 3.89042C10.5406 4.27126 10.1239 4.86815 9.75585 5.68137C9.3902 6.4892 9.09329 7.46441 8.88897 8.55419C8.72806 9.41242 8.62824 10.3222 8.59321 11.25H15.4071C15.372 10.3222 15.2722 9.41242 15.1113 8.5542C14.907 7.46441 14.6101 6.48921 14.2444 5.68137C13.8763 4.86815 13.4597 4.27126 13.0435 3.89042C12.6304 3.51236 12.2751 3.39535 12.0001 3.39535Z"
                  />
                  <path
                    d="M8.88897 15.4458C9.09329 16.5356 9.3902 17.5108 9.75585 18.3186C10.1239 19.1319 10.5406 19.7287 10.9567 20.1096C11.3698 20.4876 11.7251 20.6047 12.0001 20.6047C12.2751 20.6047 12.6304 20.4876 13.0435 20.1096C13.4597 19.7287 13.8763 19.1319 14.2444 18.3186C14.6101 17.5108 14.907 16.5356 15.1113 15.4458C15.2722 14.5876 15.372 13.6778 15.4071 12.75H8.59321C8.62824 13.6778 8.72806 14.5876 8.88897 15.4458Z"
                  />
                  <path
                    d="M12.0001 2C12.831 2 13.5708 2.36421 14.1793 2.92113C14.7849 3.47525 15.2966 4.24878 15.7104 5.16315C16.1267 6.08292 16.4501 7.15868 16.669 8.32612C16.8445 9.26194 16.9512 10.2485 16.9868 11.25H21.9724C21.5889 6.07745 17.2707 2 12.0001 2Z"
                  />
                  <path
                    d="M16.669 15.6739C16.4501 16.8413 16.1267 17.9171 15.7104 18.8368C15.2966 19.7512 14.7849 20.5247 14.1793 21.0789C13.5708 21.6358 12.831 22 12.0001 22C17.2707 22 21.5889 17.9226 21.9724 12.75H16.9868C16.9512 13.7515 16.8445 14.7381 16.669 15.6739Z"
                  />
                </svg>
                <a href="{{ $role->website }}" class="hover:underline text-sm" target="_blank">{{ App\Utils\UrlUtils::clean($role->website) }}</a>
              </div>
              @endif
              @if($role->isVenue())
              <div
                class="flex flex-row gap-2 items-center relative duration-300 text-[#33383C] fill-[#33383C] hover:text-[#4E81FA] hover:fill-[#4E81FA] sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px]"
              >
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"
                    />
                 </svg>
                 <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}" target="_blank">
                  <p class="text-sm hover:underline">
                    {{ $role->shortAddress() }}
                  </p>
                </a>
              </div>
              @endif
            </div>
          </div>
        </div>

        @if($role->translatedDescription())
        <div
          class="bg-[#F5F9FE] rounded-2xl px-6 lg:px-16 py-10 flex flex-col gap-4 mb-6 {{ $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}"
        >
          <div class="text-[32px] font-semibold leading-10 text-[#151B26]">
            {{ __('messages.about') }}
          </div>
          <div class="text-[#33383C] text-base custom-content">
            {!! \App\Utils\UrlUtils::convertUrlsToLinks($role->translatedDescription()) !!}
          </div>
        </div>
        @endif

      @if ($role->youtube_links && $role->youtube_links != '[]' && $role->isTalent())
        <div
            class="bg-[#F5F9FE] rounded-2xl px-6 lg:px-16 py-10 flex flex-col gap-6 mb-6"
          >
            <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
            @foreach (json_decode($role->youtube_links) as $link)
              <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
              </div>
            @endforeach
          </div>          
        </div>
      @endif

      <div 
        class="bg-[#F5F9FE] rounded-2xl px-6 lg:px-16 py-10 flex flex-col gap-6 mb-6"
      >  
        @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule')])
      </div>

      @if ($role->youtube_links && $role->youtube_links != '[]' && ! $role->isTalent())
        <div
            class="bg-[#F5F9FE] rounded-2xl px-6 lg:px-16 py-10 flex flex-col gap-6 mb-6"
          >
            <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
            @foreach (json_decode($role->youtube_links) as $link)
              <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
              </div>
            @endforeach
          </div>          
        </div>
      @endif

      @if ($role->social_links)
      <div 
        class="bg-[#F5F9FE] rounded-2xl px-6 lg:px-16 py-10 flex flex-col gap-6 mb-6"
      >
        <h3 class="text-[32px] font-semibold leading-10 text-[#151B26] mb-6">
          {{ __('messages.social_media') }}
        </h3>
        <div class="flex flex-row gap-4 items-center">
          @foreach (json_decode($role->social_links) as $link)
          @if ($link)
            <a 
              href="{{ $link->url }}" target="_blank"
              style="background-color: {{ $role->accent_color ?? '#4E81FA' }}"
              class="w-[44px] h-[44px] rounded-full flex justify-center items-center hover:opacity-90 duration-300"
              >
              <x-url-icon>
                {{ \App\Utils\UrlUtils::clean($link->url) }}
              </x-url-icon>
            </a>
          @endif
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </main>

<style>
[v-cloak] {
  display: none !important;
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
});
</script>

</x-app-guest-layout>
