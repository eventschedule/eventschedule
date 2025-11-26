<x-app-admin-layout>

    <x-slot name="head">
        @if ($tab == 'availability')
        <style>
            .day-x {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(45deg, transparent 45%, #888 45%, #888 55%, transparent 55%),
                            linear-gradient(-45deg, transparent 45%, #888 45%, #888 55%, transparent 55%);
                opacity: 0.6;
                pointer-events: none;
            }
        </style>
        <script {!! nonce_attr() !!}>
        $(document).ready(function () {
            const availableDays = new Set();
            const unavailableDays = new Set({!! json_encode($datesUnavailable) !!});
            const $saveButton = $('#saveButton');
            const $dayElements = $('.day-element');

            $dayElements.on('click', function () {
                const $this = $(this);
                const day = $this.data('date');

                if (unavailableDays.has(day)) {
                    unavailableDays.delete(day);
                    availableDays.add(day);
                    $this.find('.day-x').remove();
                } else {
                    unavailableDays.add(day);
                    if (availableDays.has(day)) {
                        availableDays.delete(day);
                    }
                    $this.append('<div class="day-x"></div>');
                }

                $saveButton.prop('disabled', false);
            });

            $saveButton.on('click', function () {                
                $('#unavailable_days').val(JSON.stringify(Array.from(unavailableDays)));
                $('#available_days').val(JSON.stringify(Array.from(availableDays)));
                $('#availability_form').submit();
            });
        });
        </script>
        @elseif (config('services.google.maps') && $tab == 'profile' && $role->formatted_address)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps') }}&callback=initMap"
            loading="async" defer></script>
        <style>
        .modal-overlay {
            z-index: 50;
        }

        #map {
            height: 400px;
            width: 100%;
            z-index: 1;
        }
        </style>
        @endif

        <script {!! nonce_attr() !!}>
            function onTabChange() {
                var tab = $('#current-tab').find(':selected').val();
                location.href = "{{ url('/') }}" + '/{{ $subdomain }}/' + tab;
            }             
        </script>
    </x-slot>

    <form method="POST" action="{{ route('role.remove_links', ['subdomain' => $role->subdomain]) }}"
        id="remove_link_form">

        <input type="hidden" name="remove_link" id="remove_link" />
        <input type="hidden" name="remove_link_type" id="remove_link_type" />

        @csrf

    </form>

    <div class="pt-2 space-y-4">
        <x-role-breadcrumbs :role="$role" class="text-sm" />
        <div class="flex items-start justify-between">
            @if ($role->profile_image_url)
                <div class="pr-4">
                    <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <h2 class="mt-2 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ $role->name }}</h2>

                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    @if($role->email)
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
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
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
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
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
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
            </div>

            {{-- Desktop buttons (hidden on mobile) --}}
            <div class="mt-2 hidden md:flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3 flex-shrink-0 md:ml-4">
                <span class="block">
                    <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}">
                        <button type="button"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                            </svg>
                            {{ __('messages.edit_' . strtolower($role->type)) }}
                        </button>
                    </a>
                </span>
                <span class="block">
                    <a href="{{ route('role.view_guest', (now()->year == $year && now()->month == $month) ? ['subdomain' => $role->subdomain] : ((now()->year == $year) ? ['subdomain' => $role->subdomain, 'month' => $month] : ['subdomain' => $role->subdomain, 'year' => $year, 'month' => $month])) }}"
                        target="_blank">
                        <button type="button" {{ ! $role->email_verified_at ? 'disabled' : '' }}
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm 
                             ring-1 ring-inset ring-gray-300 hover:bg-gray-50 {{ ! $role->email_verified_at ? 'disabled:cursor-not-allowed' : '' }}">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z" />
                            </svg>
                            {{ __('messages.view_' . strtolower($role->type)) }}
                        </button>
                    </a>
                </span>
            </div>

            {{-- Actions dropdown (always visible) --}}
            <div class="mt-2 md:ml-3">
                <div class="relative inline-block text-left w-full">
                    <button type="button" onclick="onPopUpClick('role-actions-pop-up-menu', event)" class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="role-actions-menu-button" aria-expanded="true" aria-haspopup="true">
                        {{ __('messages.actions') }}
                        <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="role-actions-pop-up-menu" class="pop-up-menu hidden absolute right-0 z-10 mt-2 w-64 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="role-actions-menu-button" tabindex="-1">
                        <div class="py-1" role="none" onclick="onPopUpClick('role-actions-pop-up-menu', event)">
                            {{-- Show edit/view options only when desktop buttons are hidden (mobile) --}}
                            <div class="md:hidden">
                                <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                                    </svg>
                                    <div>
                                        {{ __('messages.edit_' . strtolower($role->type)) }}
                                    </div>
                                </a>
                                <a href="{{ route('role.view_guest', (now()->year == $year && now()->month == $month) ? ['subdomain' => $role->subdomain] : ((now()->year == $year) ? ['subdomain' => $role->subdomain, 'month' => $month] : ['subdomain' => $role->subdomain, 'year' => $year, 'month' => $month])) }}" target="_blank" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z" />
                                    </svg>
                                    <div>
                                        {{ __('messages.view_' . strtolower($role->type)) }}
                                    </div>
                                </a>
                            </div>
                            @if ($tab == 'schedule')
                            <a href="{{ route('event.show_import', ['subdomain' => $role->subdomain]) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M14,12L10,8V11H2V13H10V16M20,18V6C20,4.89 19.1,4 18,4H6A2,2 0 0,0 4,6V9H6V6H18V18H6V15H4V18A2,2 0 0,0 6,20H18A2,2 0 0,0 20,18Z" />
                                </svg>
                                <div>
                                    {{ __('messages.import_events') }}
                                </div>
                            </a>
                            <a href="#" onclick="handleEventsGraphicClick()" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M8.5,13.5L11,16.5L14.5,12L19,18H5L8.5,13.5Z" />
                                </svg>
                                <div>
                                    {{ __('messages.events_graphic') }}
                                </div>
                            </a>
                            <a href="#" onclick="openEmbedModal()" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" />
                                </svg>
                                <div>
                                    {{ __('messages.embed_schedule') }}
                                </div>
                            </a>
                            @endif
                            @if ($role->exists && auth()->user()->hasSystemRoleSlug('superadmin'))
                            <div class="py-1" role="none">
                                <div class="border-t border-gray-100"></div>
                            </div>
                            <a href="#" onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.delete', ['subdomain' => $role->subdomain]) }}'; } return false;" class="group flex items-center px-4 py-2 text-sm text-red-600 hover:text-red-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-red-400 group-hover:text-red-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                                </svg>
                                <div>
                                    {{ __('messages.delete_schedule') }}
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (! $role->email_verified_at)
    <div class="pt-5 pb-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            {{ __('messages.verify_email_address') }} &nbsp;&nbsp;
            <a href="{{ route('role.verification.resend', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    onclick="">
                    {{ __('messages.resend_email') }}
            </a>
        </div>
    </div>
    @endif

    <div class="pt-5">
        <!-- Dropdown menu on small screens -->
        <div class="sm:hidden">
            <label for="current-tab" class="sr-only">{{ __('messages.select_a_tab') }}</label>
            <select id="current-tab" name="current-tab" onchange="onTabChange()"
                class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-inset focus:ring-[#4E81FA]">
                <option value="schedule" {{ $tab == 'schedule' ? 'selected' : '' }}>{{ __('messages.schedule') }}</option>
                @if ($role->isCurator())
                <option value="videos" {{ $tab == 'videos' ? 'selected' : '' }}>
                    {{ __('messages.videos') }}</option>
                @endif
                @if ($role->isTalent())
                <option value="availability" {{ $tab == 'availability' ? 'selected' : '' }}>{{ __('messages.availability') }}</option>
                @endif
                @if (count($requests))
                <option value="requests" {{ $tab == 'requests' ? 'selected' : '' }}>
                    {{ __('messages.requests') }}{{ count($requests) ? ' (' . count($requests) . ')' : '' }}</option>
                @endif
                <option value="profile" {{ $tab == 'profile' ? 'selected' : '' }}>{{ __('messages.profile') }}</option>
                @if (config('app.hosted'))
                <option value="followers" {{ $tab == 'followers' ? 'selected' : '' }}>
                    {{ __('messages.followers') }}{{ count($followers) ? ' (' . count($followers) . ')' : '' }}</option>
                @endif
                <option value="team" {{ $tab == 'team' ? 'selected' : '' }}>
                    {{ __('messages.team') }}{{ count($members) ? ' (' . count($members) . ')' : '' }}</option>
                @if (config('app.hosted'))
                <option value="plan" {{ $tab == 'plan' ? 'selected' : '' }}>
                    {{ __('messages.plan') }}</option>
                @endif
            </select>
        </div>

        <!-- Tabs at small breakpoint and up -->
        <div class="hidden sm:block">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('role.view_admin', ((now()->year == $year && now()->month == $month) || $tab == 'schedule') ? ['subdomain' => $role->subdomain, 'tab' => 'schedule'] : ((now()->year == $year) ? ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'month' => $month] : ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => $year, 'month' => $month])) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'schedule' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.schedule') }}</a>
                @if ($role->isCurator())
                <a href=" {{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'videos']) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'videos' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.videos') }}</a>
                @endif
                @if ($role->isTalent())
                <a href=" {{ route('role.view_admin', ((now()->year == $year && now()->month == $month) || $tab == 'availability') ? ['subdomain' => $role->subdomain, 'tab' => 'availability'] : ((now()->year == $year) ? ['subdomain' => $role->subdomain, 'tab' => 'availability', 'month' => $month] : ['subdomain' => $role->subdomain, 'tab' => 'availability', 'year' => $year, 'month' => $month])) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'availability' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.availability') }}</a>
                @endif
                @if (count($requests))
                <a href=" {{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'requests']) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'requests' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.requests') }}{{ count($requests) ? ' (' . count($requests) . ')' : '' }}</a>
                @endif
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'profile']) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'profile' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.profile') }}</a>
                @if (config('app.hosted'))                    
                <a href=" {{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'followers' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.followers') }}{{ count($followers) ? ' (' . count($followers) . ')' : '' }}</a>
                @endif
                <a href=" {{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'team' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.team') }}{{ count($members) ? ' (' . count($members) . ')' : '' }}</a>
                @if (config('app.hosted'))
                <a href=" {{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']) }}"
                    class="whitespace-nowrap border-b-2 {{ $tab == 'plan' ? 'border-[#4E81FA] px-1 pb-4 text-sm font-medium text-[#4E81FA]' : 'border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">{{ __('messages.plan') }}</a>
                @endif
            </nav>
        </div>

    </div>

    @if ($tab == 'schedule')
    @include('role.show-admin-schedule')
    @elseif ($tab == 'availability')
    @include('role.show-admin-availability')
    @elseif ($tab == 'requests')
    @include('role.show-admin-requests')
    @elseif ($tab == 'profile')
    @include('role.show-admin-profile')
    @elseif ($tab == 'followers')
    @include('role.show-admin-followers')
    @elseif ($tab == 'team')
    @include('role.show-admin-team')
    @elseif ($tab == 'videos')
    @include('role.show-admin-videos')
    @elseif ($tab == 'plan')
    @include('role.show-admin-plan')
    @endif

<script>
function handleEventsGraphicClick() {
    @if (!$role->isPro())
        alert('{{ __("messages.requires_pro_plan") }}');
        return false;
    @else
        window.location.href = '{{ route("event.generate_graphic", ["subdomain" => $role->subdomain]) }}';
    @endif
}
</script>

</x-app-admin-layout>