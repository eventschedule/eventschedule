<x-app-guest-layout :role="$role" :event="$event" :date="$date">

    <div class="p-10 max-w-5xl mx-auto px-4">
        <div class="flex items-start justify-between pb-6">
            <a href="{{ $event ? $event->venue->getGuestUrl() : $role->getGuestUrl() }}">
                <div id="venue_title" class="text-4xl font-bold">
                    {{ $event ? $event->venue->name : $role->name }}
                </div>
            </a>
            <div>
                @if (($event && $event->venue->bestAddress()) || $role->bestAddress())
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event ? $event->venue->bestAddress() : $role->bestAddress()) }}" target="_blank" class="pl-2">
                    <button type="button" style="background-color: {{ $event ? $otherRole->accent_color : $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                            </svg>
                        {{ __('messages.map') }}
                    </button>
                </a>
                @endif

                @if ($event)
                @if (! $user || ! $user->isConnected($event->role->subdomain))
                <a href="{{ route('role.follow', ['subdomain' => $event->role->subdomain]) }}" class="pl-2">
                    <button type="button" style="background-color: {{ $otherRole->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M21.61 2.39C21.61 2.39 16.66 .269 11 5.93C8.81 8.12 7.5 10.53 6.65 12.64C6.37 13.39 6.56 14.21 7.11 14.77L9.24 16.89C9.79 17.45 10.61 17.63 11.36 17.35C13.5 16.53 15.88 15.19 18.07 13C23.73 7.34 21.61 2.39 21.61 2.39M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46M8.88 16.53L7.47 15.12L8.88 16.53M6.24 22L9.88 18.36C9.54 18.27 9.21 18.12 8.91 17.91L4.83 22H6.24M2 22H3.41L8.18 17.24L6.76 15.83L2 20.59V22M2 19.17L6.09 15.09C5.88 14.79 5.73 14.47 5.64 14.12L2 17.76V19.17Z" />
                            </svg>
                        {{ __('messages.follow') }}
                    </button>
                </a>
                @endif
                @else
                @if (! $user || ! $user->isConnected($role->subdomain))
                <a href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}" class="pl-2">
                    <button type="button" style="background-color: {{ $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                            aria-hidden="true">
                            <path
                                d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M21.61 2.39C21.61 2.39 16.66 .269 11 5.93C8.81 8.12 7.5 10.53 6.65 12.64C6.37 13.39 6.56 14.21 7.11 14.77L9.24 16.89C9.79 17.45 10.61 17.63 11.36 17.35C13.5 16.53 15.88 15.19 18.07 13C23.73 7.34 21.61 2.39 21.61 2.39M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46M8.88 16.53L7.47 15.12L8.88 16.53M6.24 22L9.88 18.36C9.54 18.27 9.21 18.12 8.91 17.91L4.83 22H6.24M2 22H3.41L8.18 17.24L6.76 15.83L2 20.59V22M2 19.17L6.09 15.09C5.88 14.79 5.73 14.47 5.64 14.12L2 17.76V19.17Z" />
                        </svg>
                        {{ __('messages.follow') }}
                    </button>
                </a>
                @endif
                @endif

                @if($event && $curatorRoles->count() > 0)
                    @php
                        $eventInRole = false;
                        foreach ($curatorRoles as $curatorRole) {
                            if ($curatorRole->events()->where('event_role.event_id', $event->id)->exists()) {
                                $eventInRole = true;
                                break;
                            }
                        }
                    @endphp

                    @if($eventInRole)
                        <form action="{{ route('event.uncurate', ['subdomain' => $curatorRoles->first()->subdomain, 'hash' => $event->hashedId()]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background-color: {{ $role->accent_color }}"
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                    aria-hidden="true">
                                    <path
                                        d="M5.8 21L7.4 14L2 9.2L9.2 8.6L12 2L14.8 8.6L22 9.2L18.8 12H18C14.9 12 12.4 14.3 12 17.3L5.8 21M20.1 14.5L18 16.6L15.9 14.5L14.5 15.9L16.6 18L14.5 20.1L15.9 21.5L18 19.4L20.1 21.5L21.5 20.1L19.4 18L21.5 15.9L20.1 14.5Z" />
                                </svg>
                                {{ __('messages.uncurate') }}
                            </button>
                        </form>
                    @else
                        @if($curatorRoles->count() == 1)
                            <form action="{{ route('event.curate', ['subdomain' => $curatorRoles->first()->subdomain, 'hash' => $event->hashedId()]) }}" method="POST">
                                @csrf
                                <button type="submit" style="background-color: {{ $role->accent_color }}"
                                    class="btn btn-primary inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                        aria-hidden="true">
                                        <path
                                            d="M5.8 21L7.4 14L2 9.2L9.2 8.6L12 2L14.8 8.6L22 9.2L18.8 12H18C14.9 12 12.4 14.3 12 17.3L5.8 21M17 14V17H14V19H17V22H19V19H22V17H19V14H17Z" />
                                    </svg>
                                    {{ __('messages.curate') }}
                                </button>
                            </form>
                        @else
                            <div class="dropdown" style="background-color: {{ $role->accent_color }}">
                                <button class="dropdown-toggle inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" 
                                    type="button" id="curateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                        aria-hidden="true">
                                        <path
                                            d="M5.8 21L7.4 14L2 9.2L9.2 8.6L12 2L14.8 8.6L22 9.2L18.8 12H18C14.9 12 12.4 14.3 12 17.3L5.8 21M17 14V17H14V19H17V22H19V19H22V17H19V14H17Z"/>
                                    </svg>
                                    {{ __('messages.curate') }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="curateDropdown">
                                    @foreach($curatorRoles as $curatorRole)
                                        <li>
                                            <form action="{{ route('event.curate', ['subdomain' => $curatorRole->subdomain, 'hash' => $event->hashedId()]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                                                    {{ $curatorRole->name }}
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                @endif                
            </div>
        </div>

        @if ($event)

            <div id="event_title" class="py-16">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="mt-2 text-6xl font-bold tracking-tight">
                            <a href="{{ $event->role->getGuestUrl() }}">
                                {{ $event->role->name }}
                            </a>
                        </p>
                        <p class="mt-6 text-2xl leading-8">
                            {{ $event->starts_at ? $event->localStartsAt(true, $date) : __('messages.date_to_be_announced') . '...' }}
                        </p>

                        <div style="font-family: sans-serif" class="mt-8 relative inline-block text-left">
                            <div style="letter-spacing: .35em">
                                <button type="button" 
                                    onclick="onPopUpClick(event)"
                                    class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="true" aria-haspopup="true">
                                {{ strtoupper(__('messages.add_to_calendar')) }}
                                <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                                </button>
                            </div>

                            <div id="pop-up-menu" class="hidden absolute right-0 z-10 mt-2 w-40 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                                <div class="py-1" role="none" onclick="onPopUpClick(event)">
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
                        </div>

                    </div>
                </div>
            </div>

            @if ($event->role->youtube_links)
                <div class="container mx-auto py-8">
                    <div class="grid grid-cols-1 md:grid-cols-{{ $event->role->getVideoColumns() }} gap-8">
                        @foreach (json_decode($event->role->youtube_links) as $link)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <iframe class="w-full" style="height:{{ $event->role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($event->description_html)
                <div class="container mx-auto pb-8 max-w-xl text-lg">
                    {!! nl2br($event->description_html) !!}
                </div>
            @endif

            @if ($event->flyer_image_url)
                <div class="container mx-auto pb-8 flex justify-center">
                    <img src="{{ $event->flyer_image_url }}" class="block"/>
                </div>
            @endif
        
        @elseif (! $role->isVenue() && $role->youtube_links)

            @if ($role->youtube_links)
            <div class="container mx-auto py-8">
                <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
                    @foreach (json_decode($role->youtube_links) as $link)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        @endif

        @include('role/partials/calendar', ['route' => 'guest', 'tab' => ''])

        <div class="py-6">
            <div class="flex items-start justify-between pb-6">
                <div>
                    {!! $role->description_html !!}
                </div>
                <div>
                @if ($role->isVenue() && $role->accept_talent_requests && $role->user_id)
                <a href="{{ route('event.sign_up', ['subdomain' => $role->subdomain])}}">
                    <button type="button" style="background-color: {{ $event ? $event->role->accent_color : $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M19.59,3H22V5H20.41L16.17,9.24C15.8,8.68 15.32,8.2 14.76,7.83L19.59,3M12,8A4,4 0 0,1 16,12C16,13.82 14.77,15.42 13,15.87V16A5,5 0 0,1 8,21A5,5 0 0,1 3,16A5,5 0 0,1 8,11H8.13C8.58,9.24 10.17,8 12,8M12,10.5A1.5,1.5 0 0,0 10.5,12A1.5,1.5 0 0,0 12,13.5A1.5,1.5 0 0,0 13.5,12A1.5,1.5 0 0,0 12,10.5M6.94,14.24L6.23,14.94L9.06,17.77L9.77,17.06L6.94,14.24Z" />
                        </svg>
                        {{ __('messages.sign_up_to_perform') }}
                    </button>
                </a>
                @endif

                @if ($role->isVenue() && $role->accept_vendor_requests && $role->user_id)
                <a href="{{ route('event.sign_up', ['subdomain' => $role->subdomain])}}">
                    <button type="button" style="background-color: {{ $event ? $event->role->accent_color : $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M12,13A5,5 0 0,1 7,8H9A3,3 0 0,0 12,11A3,3 0 0,0 15,8H17A5,5 0 0,1 12,13M12,3A3,3 0 0,1 15,6H9A3,3 0 0,1 12,3M19,6H17A5,5 0 0,0 12,1A5,5 0 0,0 7,6H5C3.89,6 3,6.89 3,8V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V8C21,6.89 20.1,6 19,6Z" />
                        </svg>
                        {{ __('messages.sign_up_to_vend') }}
                    </button>
                </a>
                @endif
                </div>
            </div>
        </div>
        
        @if ($role->isVenue() && $role->youtube_links && ! $event)
            <div class="container mx-auto py-8">
                <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
                    @foreach (json_decode($role->youtube_links) as $link)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between pb-16">
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                @if($role->email && $role->show_email)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                    </svg>
                    <div class="mt-1">
                        <a href="mailto:{{ $role->email }}" class="hover:underline">
                            {{ $role->email }}
                        </a>
                    </div>
                </div>
                @endif

                @if($role->phone)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
                    </svg>
                    <div class="mt-1">
                        <a href="tel:{{ $role->phone }}" class="hover:underline">
                            {{ $role->phone }}
                        </a>
                    </div>
                </div>
                @endif

                @if($role->website)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M10.59,13.41C11,13.8 11,14.44 10.59,14.83C10.2,15.22 9.56,15.22 9.17,14.83C7.22,12.88 7.22,9.71 9.17,7.76V7.76L12.71,4.22C14.66,2.27 17.83,2.27 19.78,4.22C21.73,6.17 21.73,9.34 19.78,11.29L18.29,12.78C18.3,11.96 18.17,11.14 17.89,10.36L18.36,9.88C19.54,8.71 19.54,6.81 18.36,5.64C17.19,4.46 15.29,4.46 14.12,5.64L10.59,9.17C9.41,10.34 9.41,12.24 10.59,13.41M13.41,9.17C13.8,8.78 14.44,8.78 14.83,9.17C16.78,11.12 16.78,14.29 14.83,16.24V16.24L11.29,19.78C9.34,21.73 6.17,21.73 4.22,19.78C2.27,17.83 2.27,14.66 4.22,12.71L5.71,11.22C5.7,12.04 5.83,12.86 6.11,13.65L5.64,14.12C4.46,15.29 4.46,17.19 5.64,18.36C6.81,19.54 8.71,19.54 9.88,18.36L13.41,14.83C14.59,13.66 14.59,11.76 13.41,10.59C13,10.2 13,9.56 13.41,9.17Z" />
                    </svg>
                    <div class="mt-1">
                        <a href="{{ $role->website }}" target="_blank" class="hover:underline">
                            {{ \App\Utils\UrlUtils::clean($role->website) }}
                        </a>
                    </div>
                </div>
                @endif

            </div>
            <div class="flex space-x-4">
                @if ($role->social_links)
                @foreach (json_decode($role->social_links) as $link)
                    <a href="{{ $link->url }}" target="_blank">
                        <x-url-icon>
                            {{ \App\Utils\UrlUtils::clean($link->url) }}
                        </x-url-icon>
                    </a>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    <footer class="bottom-0 left-0 right-0 bg-black text-white py-4 w-full">
        <div class="container mx-auto text-center">
            <p>
                {!! str_replace(':link', '<a href="' . url('/') . '" target="_blank" class="hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
                •
                @if (($role->country_code == 'il' && $role->id != 6) || ($event && $event->venue && $event->venue->country_code == 'il' && $event->venue->id != 6))
                {!! str_replace(':link', '<a href="https://myjewishsoulmate.com" target="_blank" class="hover:underline">My Jewish Soulmate</a>',  __('messages.supported_by')) !!}
                @else
                {!! str_replace([':link1', ':link2'], ['<a href="https://invoiceninja.com" target="_blank" class="hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>', '<a href="https://mudeo.app" target="_blank" class="hover:underline" title="Make music together">mudeo</a>'],  __('messages.supported_by_both')) !!}
                @endif
            </p>
        </div>
    </footer>

</x-app-guest-layout>
