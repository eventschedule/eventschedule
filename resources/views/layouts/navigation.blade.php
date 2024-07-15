<!-- Static sidebar for desktop -->
<div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
    <!-- Sidebar component, swap this element with another sidebar if you like -->
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-6 pb-4">
        <a href="{{ route('landing') }}">
            <div class="flex h-16 shrink-0 items-center">
                <img class="h-8 w-auto" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=500"
                    alt="Your Company">
            </div>
        </a>
        <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <ul role="list" class="-mx-2 space-y-1">
                        <li>
                            <a href="{{ route('home') }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('home') ? 'bg-gray-800 text-white' : '' }}">
                                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24"
                                    fill="{{ request()->is('home') ? '#ccc' : '#666' }}" aria-hidden="true">
                                    <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z" />
                                </svg>
                                {{ __('Home') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('venues') }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('venues') ? 'bg-gray-800 text-white' : '' }}">
                                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24"
                                    fill="{{ request()->is('venues') ? '#ccc' : '#666' }}" aria-hidden="true">
                                    <path
                                        d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                                </svg>
                                {{ __('Venues') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('talent') }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('talent') ? 'bg-gray-800 text-white' : '' }}">
                                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24"
                                    fill="{{ request()->is('talent') ? '#ccc' : '#666' }}" aria-hidden="true">
                                    <path
                                        d="M18.09 11.77L19.56 18.1L14 14.74L8.44 18.1L9.9 11.77L5 7.5L11.47 6.96L14 1L16.53 6.96L23 7.5L18.09 11.77M2 12.43C2.19 12.43 2.38 12.37 2.55 12.26L5.75 10.15L4.18 8.79L1.45 10.59C.989 10.89 .861 11.5 1.16 12C1.36 12.27 1.68 12.43 2 12.43M1.16 21.55C1.36 21.84 1.68 22 2 22C2.19 22 2.38 21.95 2.55 21.84L6.66 19.13L7 17.76L7.31 16.31L1.45 20.16C.989 20.47 .861 21.09 1.16 21.55M1.45 15.38C.989 15.68 .861 16.3 1.16 16.76C1.36 17.06 1.68 17.21 2 17.21C2.19 17.21 2.38 17.16 2.55 17.05L7.97 13.5L8.24 12.31L7.32 11.5L1.45 15.38Z" />
                                </svg>
                                {{ __('Talent') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('vendors') }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('vendors') ? 'bg-gray-800 text-white' : '' }}">
                                <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24"
                                    fill="{{ request()->is('vendors') ? '#ccc' : '#666' }}" aria-hidden="true">
                                    <path
                                        d="M12,13A5,5 0 0,1 7,8H9A3,3 0 0,0 12,11A3,3 0 0,0 15,8H17A5,5 0 0,1 12,13M12,3A3,3 0 0,1 15,6H9A3,3 0 0,1 12,3M19,6H17A5,5 0 0,0 12,1A5,5 0 0,0 7,6H5C3.89,6 3,6.89 3,8V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V8C21,6.89 20.1,6 19,6Z" />
                                </svg>
                                {{ __('Vendors') }}
                            </a>
                        </li>
                    </ul>
                </li>

                <?php
                    $user = auth()->user();
                    $venues = $user->venues()->get();
                    $talent = $user->talent()->get();
                    $vendors = $user->vendors()->get();
                ?>

                @if (count($venues) > 0)
                <li>
                    <div class="text-xs font-semibold leading-6 text-gray-400">{{ __('Your Venues') }}</div>

                    <ul role="list" class="-mx-2 mt-2 space-y-1">

                        @foreach ($venues as $venue)
                        <li>
                            <a href="{{ url('/venue/'. $venue->subdomain) }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is('venue/'. $venue->subdomain) ? 'bg-gray-800 text-white' : 'text-gray-400' }}">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is('venue/'. $venue->subdomain) ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($venue->name, 0, 1)) }}</span>
                                <span class="truncate">{{ $venue->name }}</span>
                            </a>
                        </li>
                        @endforeach

                    </ul>
                </li>
                @endif


                @if (count($talent) > 0)
                <li>
                    <div class="text-xs font-semibold leading-6 text-gray-400">{{ __('Your Talent') }}</div>

                    <ul role="list" class="-mx-2 mt-2 space-y-1">

                        @foreach ($talent as $each)
                        <li>
                            <a href="{{ url('/talent/'. $each->subdomain) }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is('talent/'. $each->subdomain) ? 'bg-gray-800 text-white' : 'text-gray-400' }}">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is('talent/'. $each->subdomain) ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($each->name, 0, 1)) }}</span>
                                <span class="truncate">{{ $each->name }}</span>
                            </a>
                        </li>
                        @endforeach

                    </ul>
                </li>
                @endif

                @if (count($vendors) > 0)
                <li>
                    <div class="text-xs font-semibold leading-6 text-gray-400">{{ __('Your Vendors') }}</div>

                    <ul role="list" class="-mx-2 mt-2 space-y-1">

                        @foreach ($vendors as $vendor)
                        <li>
                            <a href="{{ url('/vendor/'. $vendor->subdomain) }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is('vendor/'. $vendor->subdomain) ? 'bg-gray-800 text-white' : 'text-gray-400' }}">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is('vendor/'. $vendor->subdomain) ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($vendor->name, 0, 1)) }}</span>
                                <span class="truncate">{{ $vendor->name }}</span>
                            </a>
                        </li>
                        @endforeach

                    </ul>
                </li>
                @endif

                <!--
                <li class="mt-auto">
                    <a href="#"
                        class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">
                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>
                </li>
                -->
            </ul>
        </nav>
    </div>
</div>