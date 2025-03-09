<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

  <main>
    <div
      class="bg-center bg-cover relative before:bg-[#1B212B80] before:absolute before:inset-0 before:z-0"
      style="background-image: url(./images/yoga.jpeg)"
    >
      <div class="container mx-auto pt-[100px] pb-10 px-5 relative z-10">
        <div class="flex flex-col sm:flex-row justify-between mb-10 py-[7px]">
          <h2
            class="text-white text-[40px] sm:text-{52px} leading-snug font-semibold"
          >
            {{ $event->translatedName() }}
          </h2>
          <!--
          <a
            href="route('role.follow', ['subdomain' => $event->role()->subdomain])"
            class="inline-flex items-center justify-center"
          >
            <button
              type="button"
              name="follow"
              style="background-color: {{ $otherRole->accent_color ?? '#4E81FA' }}"
              class="inline-flex items-center rounded-md px-8 py-4 hover:opacity-90 font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            >
              {{ __('messages.follow') }}
            </button>
          </a>
          -->

          <div style="font-family: sans-serif" class="mt-8 relative inline-block text-left">
          @if ($event->canSellTickets() || $event->registration_url)
            @if (request()->get('tickets') !== 'true')
              <a href="{{ $event->registration_url ? $event->registration_url : request()->fullUrlWithQuery(['tickets' => 'true']) }}">
                  <button type="button" 
                        class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="true" aria-haspopup="true">
                    {{ __('messages.buy_tickets') }}
                </button>            
              </a>
            @endif
          @else
                <button type="button" 
                    onclick="onPopUpClick('calendar-pop-up-menu', event)"
                    class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="true" aria-haspopup="true">
                {{ __('messages.add_to_calendar') }}
                <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
                </button>

              <div id="calendar-pop-up-menu" class="pop-up-menu hidden absolute right-0 z-10 mt-2 w-40 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                  <div class="py-1" role="none" onclick="onPopUpClick('calendar-pop-up-menu', event)">
                      <a href="{{ $event->getGoogleCalendarUrl($date) }}" target="_blank" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-0">
                          <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                          <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
                          </svg>
                          Google
                      </a>
                      <a href="{{ $event->getAppleCalendarUrl($date) }}" target="_blank" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-1">
                          <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                          <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
                          </svg>
                          Apple
                      </a>
                      <a href="{{ $event->getMicrosoftCalendarUrl($date) }}" target="_blank" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-1">
                          <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                          <path d="M2,3H11V12H2V3M11,22H2V13H11V22M21,3V12H12V3H21M21,22H12V13H21V22Z" />
                          </svg>
                          Microsoft
                      </a>
                  </div>
              </div>
          @endif
        </div>

        </div>

        <div class="flex flex-col sm:flex-row gap-4 items-center">
          @if (($event->venue && $event->venue->name) || $event->getEventUrlDomain())
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M8.17 2.76C9.39 2.26 10.69 2 12 2C13.31 2 14.61 2.26 15.83 2.76C17.04 3.26 18.14 4 19.07 4.93C20 5.86 20.74 6.96 21.24 8.17C21.74 9.39 22 10.69 22 12C22 14.65 20.95 17.2 19.07 19.07C17.2 20.95 14.65 22 12 22C10.69 22 9.39 21.74 8.17 21.24C6.96 20.74 5.86 20 4.93 19.07C3.05 17.2 2 14.65 2 12C2 9.35 3.05 6.8 4.93 4.93C5.86 4 6.96 3.26 8.17 2.76M12 17L13.56 13.58L17 12L13.56 10.44L12 7L10.43 10.44L7 12L10.43 13.58L12 17Z"
              />
            </svg>
            @if ($event->venue && $event->venue->name)
              @if ($event->venue->isClaimed())
                <a href="{{ route('role.view_guest', ['subdomain' => $event->venue->subdomain]) }}" class="text-sm hover:underline">
                  {{ $event->venue->name }}
                </a>
              @else
                <p class="text-sm">{{ $event->venue->name }}</p>
              @endif
            @else
              <p class="text-sm">{{ $event->getEventUrlDomain() }}</p>
            @endif
            </div>
          @endif
          @if ($event->venue)
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
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
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->venue->bestAddress()) }}" target="_blank">
              <p class="text-sm hover:underline">
                {{ $event->venue->shortAddress() }}
              </p>
            </a>
          </div>
          @endif
          @if ($event->isMultiDay())
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.75 2.5C7.75 2.08579 7.41421 1.75 7 1.75C6.58579 1.75 6.25 2.08579 6.25 2.5V4.07926C4.81067 4.19451 3.86577 4.47737 3.17157 5.17157C2.47737 5.86577 2.19451 6.81067 2.07926 8.25H21.9207C21.8055 6.81067 21.5226 5.86577 20.8284 5.17157C20.1342 4.47737 19.1893 4.19451 17.75 4.07926V2.5C17.75 2.08579 17.4142 1.75 17 1.75C16.5858 1.75 16.25 2.08579 16.25 2.5V4.0129C15.5847 4 14.839 4 14 4H10C9.16097 4 8.41527 4 7.75 4.0129V2.5Z"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2 12C2 11.161 2 10.4153 2.0129 9.75H21.9871C22 10.4153 22 11.161 22 12V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.8284C2 19.6569 2 17.7712 2 14V12ZM17 14C17.5523 14 18 13.5523 18 13C18 12.4477 17.5523 12 17 12C16.4477 12 16 12.4477 16 13C16 13.5523 16.4477 14 17 14ZM17 18C17.5523 18 18 17.5523 18 17C18 16.4477 17.5523 16 17 16C16.4477 16 16 16.4477 16 17C16 17.5523 16.4477 18 17 18ZM13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.5523 12 13 12.4477 13 13ZM13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17ZM7 14C7.55228 14 8 13.5523 8 13C8 12.4477 7.55228 12 7 12C6.44772 12 6 12.4477 6 13C6 13.5523 6.44772 14 7 14ZM7 18C7.55228 18 8 17.5523 8 17C8 16.4477 7.55228 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z"
              />
            </svg>
            <p class="text-sm">{{ $event->getStartDateTime($date, true)->format($event->getDateTimeFormat(true)) }} - {{ $event->getStartDateTime($date, true)->addHours($event->duration)->format($event->getDateTimeFormat()) }}</p>
          </div>

          @else
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.75 2.5C7.75 2.08579 7.41421 1.75 7 1.75C6.58579 1.75 6.25 2.08579 6.25 2.5V4.07926C4.81067 4.19451 3.86577 4.47737 3.17157 5.17157C2.47737 5.86577 2.19451 6.81067 2.07926 8.25H21.9207C21.8055 6.81067 21.5226 5.86577 20.8284 5.17157C20.1342 4.47737 19.1893 4.19451 17.75 4.07926V2.5C17.75 2.08579 17.4142 1.75 17 1.75C16.5858 1.75 16.25 2.08579 16.25 2.5V4.0129C15.5847 4 14.839 4 14 4H10C9.16097 4 8.41527 4 7.75 4.0129V2.5Z"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2 12C2 11.161 2 10.4153 2.0129 9.75H21.9871C22 10.4153 22 11.161 22 12V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.8284C2 19.6569 2 17.7712 2 14V12ZM17 14C17.5523 14 18 13.5523 18 13C18 12.4477 17.5523 12 17 12C16.4477 12 16 12.4477 16 13C16 13.5523 16.4477 14 17 14ZM17 18C17.5523 18 18 17.5523 18 17C18 16.4477 17.5523 16 17 16C16.4477 16 16 16.4477 16 17C16 17.5523 16.4477 18 17 18ZM13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.5523 12 13 12.4477 13 13ZM13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17ZM7 14C7.55228 14 8 13.5523 8 13C8 12.4477 7.55228 12 7 12C6.44772 12 6 12.4477 6 13C6 13.5523 6.44772 14 7 14ZM7 18C7.55228 18 8 17.5523 8 17C8 16.4477 7.55228 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z"
              />
            </svg>
            <p class="text-sm">{{ $event->getStartDateTime($date, true)->format('F j, Y') }}</p>
          </div>
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4"
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
                d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM12.75 8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V12C11.25 12.1989 11.329 12.3897 11.4697 12.5303L13.9697 15.0303C14.2626 15.3232 14.7374 15.3232 15.0303 15.0303C15.3232 14.7374 15.3232 14.2626 15.0303 13.9697L12.75 11.6893V8Z"
              />
            </svg>
            <p class="text-sm">
              {{ $event->getStartEndTime($date) }}
            </p>
          </div>
          @endif
        </div>
      </div>
    </div>

    <div
      class="container mx-auto flex flex-col sm:grid px-5 py-[80px] lg:gap-[48px] gap-[8px] lg:grid-cols-[minmax(0px,_auto)_minmax(0px,_344px)]"
    >
      <div class="flex flex-col gap-10">
        @if (request()->get('tickets') === 'true' && $event->isPro())
        <div class="flex flex-col xl:flex-row gap-10 bg-[#F5F9FE] rounded-2xl p-10 mb-4">
          <div class="flex-1">
            <div class="flex flex-col gap-4">
              <h4 class="text-[28px] leading-snug text-black">
                {{ __('messages.buy_tickets') }}
              </h4>
              <p class="text-base text-black">
                @include('event.tickets', ['event' => $event, 'subdomain' => $subdomain])
              </p>
            </div>
          </div>
          <div class="flex-1">
            @if ($event->flyer_image_url)
              <img src="{{ $event->flyer_image_url }}" class="block"/>
            @endif
          </div>
        </div>
        @else
        <!--
        <div>
          <h3 class="text-[32px] leading-snug text-black mb-6">
            {{ __('messages.date_and_time') }}
          </h3>
          <div class="flex flex-col gap-3">
            <div
              class="flex flex-row gap-2 items-center relative text-[#33383C] fill-[#33383C]"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M7.75 2.5C7.75 2.08579 7.41421 1.75 7 1.75C6.58579 1.75 6.25 2.08579 6.25 2.5V4.07926C4.81067 4.19451 3.86577 4.47737 3.17157 5.17157C2.47737 5.86577 2.19451 6.81067 2.07926 8.25H21.9207C21.8055 6.81067 21.5226 5.86577 20.8284 5.17157C20.1342 4.47737 19.1893 4.19451 17.75 4.07926V2.5C17.75 2.08579 17.4142 1.75 17 1.75C16.5858 1.75 16.25 2.08579 16.25 2.5V4.0129C15.5847 4 14.839 4 14 4H10C9.16097 4 8.41527 4 7.75 4.0129V2.5Z"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 12C2 11.161 2 10.4153 2.0129 9.75H21.9871C22 10.4153 22 11.161 22 12V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.8284C2 19.6569 2 17.7712 2 14V12ZM17 14C17.5523 14 18 13.5523 18 13C18 12.4477 17.5523 12 17 12C16.4477 12 16 12.4477 16 13C16 13.5523 16.4477 14 17 14ZM17 18C17.5523 18 18 17.5523 18 17C18 16.4477 17.5523 16 17 16C16.4477 16 16 16.4477 16 17C16 17.5523 16.4477 18 17 18ZM13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.5523 12 13 12.4477 13 13ZM13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17ZM7 14C7.55228 14 8 13.5523 8 13C8 12.4477 7.55228 12 7 12C6.44772 12 6 12.4477 6 13C6 13.5523 6.44772 14 7 14ZM7 18C7.55228 18 8 17.5523 8 17C8 16.4477 7.55228 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z"
                />
              </svg>
              <p class="text-sm">{{ $event->getStartDateTime($date, true)->format('F j, Y') }}</p>
            </div>
            <div
              class="flex flex-row gap-2 items-center relative text-[#33383C] fill-[#33383C]"
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
                  d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM12.75 8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V12C11.25 12.1989 11.329 12.3897 11.4697 12.5303L13.9697 15.0303C14.2626 15.3232 14.7374 15.3232 15.0303 15.0303C15.3232 14.7374 15.3232 14.2626 15.0303 13.9697L12.75 11.6893V8Z"
                />
              </svg>
              <p class="text-sm">{{ $event->getStartEndTime($date) }}</p>
            </div>
          </div>
        </div>
        -->
        <div>
        @if ($event->translatedDescription())
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6 flex flex-col gap-4 {{ $role->isRtl() ? 'rtl' : '' }}">
            <h2
              class="text-[#151B26] text-[40px] sm:text-{52px} leading-snug font-semibold"
            >
              {{ __('messages.event_details') }}
            </h2>
            <div class="text-[#33383C] text-base custom-content">
              {!! \App\Utils\UrlUtils::convertUrlsToLinks($event->translatedDescription()) !!}
            </div>
          </div>
          @endif
          @if ($event->flyer_image_url && $event->members()->count() != 1)
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6 flex flex-col gap-4">
            <img src="{{ $event->flyer_image_url }}" class="block"/>
          </div>
          @endif
          @foreach ($event->members() as $each)          
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6 flex flex-col gap-4" 
            style="font-family: {{ $each->isClaimed() ? $each->font_family : $otherRole->font_family }}, sans-serif;"
          >
            <div
              class="flex flex-row justify-between items-center"
            >
              <div class="flex gap-3 flex-row items-center">
                @if ($each->isClaimed())                
                @if ($each->profile_image_url)
                <a
                  href="{{ route('role.view_guest', ['subdomain' => $each->subdomain]) }}"                  
                >                
                  <img
                    src="{{ $each->profile_image_url }}"
                    class="rounded-2xl w-[56px] h-[56px]"
                  />
                </a>
                @endif
                <a
                  href="{{ route('role.view_guest', ['subdomain' => $each->subdomain]) }}"
                  class="text-base text-[#151B26] hover:underline cursor-pointer duration-300"
                >                
                  <h3 class="text-[28px] font-semibold leading-10 text-[#151B26]">
                    {{ $each->name }}
                  </h3>
                </a>
                @else
                @if ($each->profile_image_url)
                <img
                  src="{{ $each->profile_image_url }}"
                  class="rounded-2xl w-[56px] h-[56px]"
                />
                @endif
                <h3 class="text-[28px] font-semibold leading-10 text-[#151B26]">
                  {{ $each->translatedName() }}
                </h3>
                @endif
              </div>
              @if ($each->isClaimed())
              <a
                href="{{ auth()->user() && auth()->user()->isMember($each->subdomain)
                  ? config('app.url') . route('role.view_admin', ['subdomain' => $each->subdomain, 'tab' => 'schedule'], false) 
                  : route('role.follow', ['subdomain' => $each->subdomain]) }}"
                class="inline-flex items-center justify-center"
              >
                <button
                  type="button"
                  name="follow"
                  style="background-color: {{ $otherRole->accent_color ?? '#4E81FA' }}"
                  class="inline-flex items-center rounded-md px-8 py-4 hover:opacity-90 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                >
                  {{ auth()->user() && auth()->user()->isMember($role->subdomain) ? __('messages.manage') : __('messages.follow') }}
                </button>
              </a>
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
            <div class="text-base text-[#33383C] custom-content {{ $role->isRtl() ? 'rtl' : '' }}">
              {!! \App\Utils\UrlUtils::convertUrlsToLinks($each->description_html) !!}
            </div>
            @if ($each->youtube_links)
              <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
              @foreach (json_decode($each->youtube_links) as $link)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                  <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
              @endforeach
              </div> 
            @endif
          </div>

          @endforeach

          @if ($event->flyer_image_url && $event->members()->count() == 1)
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6 flex flex-col gap-4">
            <img src="{{ $event->flyer_image_url }}" class="block"/>
          </div>
          @endif



          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6">
            @include('role/partials/calendar', ['route' => 'guest', 'tab' => ''])
          </div>
        </div>
        @endif
      </div>

      <div class="flex flex-col gap-6 {{ $role->isRtl() ? 'rtl' : '' }}">
        @if ($event->venue && $event->venue->name)
        <div class="p-6 rounded-xl flex flex-col gap-6" style="background-color: {{ $otherRole->accent_color ?? '#4E81FA' }}; font-family: {{ $event->venue->font_family }}, sans-serif;">
          <h4 class="text-white text-[24px] leading-snug font-semibold">
            {{ $event->venue->translatedName() }}
          </h4>
          <div class="flex flex-col gap-4">
            @if ($event->venue->phone)
            <div
              class="flex flex-row gap-2 items-center relative duration-300 text-white fill-white"
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
              <a href="tel:{{ $event->venue->phone }}" class="text-sm hover:underline"
                >{{ $event->venue->phone }}</a
              >
            </div>
            @endif
            @if ($event->venue->email && $event->venue->show_email)
            <div
              class="flex flex-row gap-2 items-center relative duration-300 text-white fill-white"
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
              <a href="mailto:{{ $role->email }}" class="text-sm hover:underline"
                >{{ $event->venue->email }}</a
              >
            </div>
            @endif
            @if ($event->venue->website)
            <div
              class="flex flex-row gap-2 items-center relative duration-300 text-white fill-white"
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
              <a href="{{ $event->venue->website }}" class="text-sm hover:underline" target="_blank">
                {{ App\Utils\UrlUtils::clean($event->venue->website) }}
              </a>
            </div>
            @endif
          </div>

          @if ($event->venue->social_links)
          <div class="flex flex-row gap-4 items-center">
            @foreach (json_decode($event->venue->social_links) as $link)
              <a 
                href="{{ $link->url }}" target="_blank"
                style="background-color: {{ $otherRole->accent_color ?? '#4E81FA' }}"
                class="w-[44px] h-[44px] rounded-full flex justify-center items-center hover:opacity-90 duration-300"
                >
                <x-url-icon>
                  {{ \App\Utils\UrlUtils::clean($link->url) }}
                </x-url-icon>
              </a>
            @endforeach
          </div>
          @endif
        </div>
        @endif

        @if ($event->tickets_enabled && $event->isPro())
        <div class="p-6 rounded-xl flex flex-col gap-6 bg-[#F5F9FE] {{ $role->isRtl() ? 'rtl' : '' }}">
          <h4 class="text-[#151B26] text-[24px] leading-snug font-semibold">
            {{ __('messages.add_to_calendar') }}
          </h4>
          <div class="flex flex-row gap-3">          
          <a href="{{ $event->getGoogleCalendarUrl($date) }}" target="_blank" title="Google">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" />
              </svg>
            </a>
            <a href="{{ $event->getAppleCalendarUrl($date) }}" target="_blank" title="Apple">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M18.71,19.5C17.88,20.74 17,21.95 15.66,21.97C14.32,22 13.89,21.18 12.37,21.18C10.84,21.18 10.37,21.95 9.1,22C7.79,22.05 6.8,20.68 5.96,19.47C4.25,17 2.94,12.45 4.7,9.39C5.57,7.87 7.13,6.91 8.82,6.88C10.1,6.86 11.32,7.75 12.11,7.75C12.89,7.75 14.37,6.68 15.92,6.84C16.57,6.87 18.39,7.1 19.56,8.82C19.47,8.88 17.39,10.1 17.41,12.63C17.44,15.65 20.06,16.66 20.09,16.67C20.06,16.74 19.67,18.11 18.71,19.5M13,3.5C13.73,2.67 14.94,2.04 15.94,2C16.07,3.17 15.6,4.35 14.9,5.19C14.21,6.04 13.07,6.7 11.95,6.61C11.8,5.46 12.36,4.26 13,3.5Z" />
              </svg>
            </a>
            <a href="{{ $event->getMicrosoftCalendarUrl($date) }}" target="_blank" title="Microsoft">
              <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M2,3H11V12H2V3M11,22H2V13H11V22M21,3V12H12V3H21M21,22H12V13H21V22Z" />
              </svg>
            </a>
          </div>
        </div>
        @endif

        <div class="p-6 rounded-xl flex flex-col gap-6 bg-[#F5F9FE] {{ $role->isRtl() ? 'rtl' : '' }}">
          <h4 class="text-[#151B26] text-[24px] leading-snug font-semibold">
            {{ __('messages.create_your_own_event_schedule') }}
          </h4>
          <a href="https://www.eventschedule.com" target="_blank">
            <button
              type="button"
              name="login"
              class="inline-flex items-center justify-center rounded-xl text-base duration-300 bg-transparent border-[1px] border-[#4E81FA] hover:border-[#1A48B3] hover:bg-[#1A48B3] text-[#4E81FA] hover:text-white py-4 px-8 hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-90"
            >
              {{ __('messages.create_schedule') }}
            </button>
          </a>
        </div>
      </div>
    </div>
  </main>

</x-app-guest-layout>