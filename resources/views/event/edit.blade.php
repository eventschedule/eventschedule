<x-app-admin-layout>

@vite([
    'resources/js/countrySelect.min.js',
    'resources/css/countrySelect.min.css',
])

<!-- Step Indicator for Add Event Flow -->
@if(session('pending_request'))
    <div class="my-6">
        <x-step-indicator :compact="true" />
    </div>
@endif

<x-slot name="head">
  <style>
    form button {
      min-width: 100px;
      min-height: 40px;
    }
    
    /* Hide all sections except the first one on desktop by default */
    @media (min-width: 1024px) {
      .section-content {
        display: none;
      }
      .section-content:first-of-type {
        display: block;
      }
    }

    .section-nav-link.validation-error {
      border-left-color: #dc2626 !important;
    }

    @media (prefers-color-scheme: dark) {
      .section-nav-link.validation-error {
        border-left-color: #ef4444 !important;
      }
    }

    .dark .section-nav-link.validation-error {
      border-left-color: #ef4444 !important;
    }
  </style>
  <script src="{{ asset('js/vue.global.prod.js') }}"></script>
  <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
        var localeConfig = fpLocale ? { locale: fpLocale } : {};

        var f = flatpickr('.datepicker', Object.assign({
            allowInput: true,
            enableTime: true,
            altInput: true,
            time_24hr: {{ $role && $role->use_24_hour_time ? 'true' : 'false' }},
            altFormat: "{{ $role && $role->use_24_hour_time ? 'M j, Y • H:i' : 'M j, Y • h:i K' }}",
            dateFormat: "Y-m-d H:i:S",
        }, localeConfig));
        // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
        f._input.onkeydown = () => false;

        // Initialize recurring end date picker if it exists on page load
        var endDateInput = document.querySelector('.datepicker-end-date');
        if (endDateInput && endDateInput.value) {
            var endDatePicker = flatpickr(endDateInput, Object.assign({
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
            }, localeConfig));
            if (endDatePicker._input) {
                endDatePicker._input.onkeydown = () => false;
            }
        }

        $("#venue_country").countrySelect({
            defaultCountry: "{{ $selectedVenue && $selectedVenue->country ? $selectedVenue->country : ($role && $role->country_code ? $role->country_code : '') }}",
        });
    });

    function onChangeCountry() {
        var selected = $('#venue_country').countrySelect('getSelectedCountryData');
        $('#venue_country_code').val(selected.iso2);
        app.venueCountryCode = selected.iso2;
    }

    function onChangeDateType() {
        var value = $('input[name="schedule_type"]:checked').val();
        if (value == 'one_time') {
            $('#days_of_week_div').hide();
            app.isRecurring = false;
        } else {
            $('#days_of_week_div').show();
            app.isRecurring = true;
        }
    }

    function onValidateClick() {
        $('#address_response').text("{{ __('messages.searching') }}...").show();
        $('#accept_button').hide();
        var country = $('#venue_country').countrySelect('getSelectedCountryData');
        
        $.post({
            url: '{{ route('validate_address') }}',
            data: {
                _token: '{{ csrf_token() }}',
                address1: $('#venue_address1').val(),
                city: $('#venue_city').val(),
                state: $('#venue_state').val(),
                postal_code: $('#venue_postal_code').val(),                    
                country_code: country ? country.iso2 : '',
            },
            success: function(response) {
                if (response) {
                    var address = response['data']['formatted_address'];
                    $('#address_response').text(address);
                    $('#accept_button').show();
                    $('#address_response').data('validated_address', response['data']);
                } else {
                    $('#address_response').text("{{ __('messages.address_not_found') }}");    
                }
            },
            error: function(xhr, status, error) {
                $('#address_response').text("{{ __('messages.an_error_occurred') }}" + ': ' + error);
            }
        });
    }

    function viewMap() {
        var address = [
            $('#venue_address1').val(),
            $('#venue_city').val(),
            $('#venue_state').val(),
            $('#venue_postal_code').val(),
            $('#venue_country').countrySelect('getSelectedCountryData').name
        ].filter(Boolean).join(', ');

        if (address) {
            var url = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address);
            window.open(url, '_blank');
        } else {
            alert("{{ __('messages.please_enter_address') }}");
        }
    }

    function acceptAddress(event) {
        event.preventDefault();
        var validatedAddress = $('#address_response').data('validated_address');
        if (validatedAddress) {
            $('#venue_address1').val(validatedAddress['address1']);
            $('#venue_city').val(validatedAddress['city']);
            $('#venue_state').val(validatedAddress['state']);
            $('#venue_postal_code').val(validatedAddress['postal_code']);
                        
            // Hide the address response and accept button after accepting
            $('#address_response').hide();
            $('#accept_button').hide();
        }
    }

    function previewImage(input) {
        var preview = document.getElementById('preview_img');
        var previewDiv = document.getElementById('image_preview');
        var warningElement = document.getElementById('image_size_warning');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewDiv.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);

            // Check file size
            var fileSize = input.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2.5) {
                warningElement.textContent = "{{ __('messages.image_size_warning') }}";
                warningElement.style.display = 'block';
            } else {
                warningElement.textContent = '';
                warningElement.style.display = 'none';
            }
        } else {
            preview.src = '#';
            previewDiv.style.display = 'none';
            warningElement.textContent = '';
            warningElement.style.display = 'none';
        }
    }

    function copyEventUrl(button) {
        const url = '{{ $event->exists ? $event->getGuestUrl($subdomain, $isUnique ? false : null) : "" }}';
        navigator.clipboard.writeText(url).then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            `;
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        });
    }

    </script>

</x-slot>

<div id="app">

  <div class="pb-4 flex items-center justify-between">
    <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
      {{ $title }}
    </h2>
    
    @if ($event->exists)
    {{-- Actions dropdown --}}
    <div class="mt-2 md:ml-3">
        <div class="relative inline-block text-left">
            <button type="button" onclick="onPopUpClick('event-actions-pop-up-menu', event)" class="inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700" id="event-actions-menu-button" aria-expanded="true" aria-haspopup="true">
                {{ __('messages.actions') }}
                <svg class="-mr-1 ml-2 h-6 w-6 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div id="event-actions-pop-up-menu" class="pop-up-menu hidden absolute right-0 z-10 mt-2 w-64 origin-top-right divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-600 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="event-actions-menu-button" tabindex="-1">
                <div class="py-2" role="none" onclick="onPopUpClick('event-actions-pop-up-menu', event)">
                    <a href="{{ route('event.clone', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                        </svg>
                        <div>
                            {{ __('messages.clone_event') }}
                        </div>
                    </a>
                    @if ($event->user_id == $user->id)
                    <div class="py-2" role="none">
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                    </div>
                    <a href="#" onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('event.delete', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}'; } return false;" class="group flex items-center px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:bg-red-50 dark:focus:bg-red-900/20 focus:text-red-700 dark:focus:text-red-300 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="mr-3 h-5 w-5 text-red-400 group-hover:text-red-500 dark:group-hover:text-red-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                        </svg>
                        <div>
                            {{ __('messages.delete') }}
                        </div>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
  </div>

  <form method="POST"
        @submit="validateForm"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain]) }}"
        enctype="multipart/form-data">

        @csrf

        @if ($event->exists)
        @method('put')
        @endif

        <x-text-input name="venue_name" type="hidden" v-model="venueName" />
        <x-text-input name="venue_email" type="hidden" v-model="venueEmail" />                                                                
        <x-text-input name="venue_address1" type="hidden" v-model="venueAddress1" />                                                                
        <x-text-input name="venue_city" type="hidden" v-model="venueCity" />                                                                
        <x-text-input name="venue_state" type="hidden" v-model="venueState" />                                                                
        <x-text-input name="venue_postal_code" type="hidden" v-model="venuePostalCode" />                                                                
        <x-text-input name="venue_country_code" type="hidden" v-model="venueCountryCode" />                                                                

        <div class="py-5">
            <div class="mx-auto lg:grid lg:grid-cols-12 lg:gap-6">
                <!-- Sidebar Navigation (hidden on small screens, visible on lg+) -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-6">
                        <nav class="space-y-1">
                            @if (! $role->isVenue() || $user->isMember($role->subdomain) || $user->canEditEvent($event))
                            <a href="#section-details" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-details">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                {{ __('messages.details') }}
                            </a>
                            @endif
                            <a href="#section-venue" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-venue">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
                                </svg>
                                {{ __('messages.venue') }}
                            </a>
                            <a href="#section-participants" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-participants">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                {{ __('messages.participants') }}
                            </a>
                            @if (! $role->isCurator())
                            <a href="#section-recurring" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-recurring">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                {{ __('messages.recurring') }}
                            </a>
                            @endif
                            @php
                                $curatorsForNav = $user->allCurators();
                                $curatorsForNav = $curatorsForNav->filter(function($curator) use ($subdomain) {
                                    return $curator->subdomain !== $subdomain;
                                });
                            @endphp
                            @if ($curatorsForNav->count() > 0)
                            <a href="#section-schedules" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-schedules">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                {{ __('messages.schedules') }}
                            </a>
                            @endif
                            @if ($event->user_id == $user->id)
                            <a href="#section-tickets" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-tickets">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                {{ __('messages.tickets') }}
                            </a>
                            @endif
                            @if ($event->exists && $event->canBeSyncedToGoogleCalendarForSubdomain(request()->subdomain))
                            <a href="#section-google-calendar" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-r-md hover:bg-gray-100 dark:hover:bg-gray-700 border-l-4 border-transparent" data-section="section-google-calendar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                {{ __('messages.google_calendar_sync') }}
                            </a>
                            @endif
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9 space-y-6 lg:space-y-0">
                @if (! $role->isVenue() || $user->isMember($role->subdomain) || $user->canEditEvent($event))
                <div id="section-details" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            {{ __('messages.details') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="event_name" :value="__('messages.event_name') . ' *'" />
                            <x-text-input id="event_name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $event->name)"
                                v-model="eventName"
                                autofocus required autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            @if ($event->exists)
                            <div class="text-sm text-gray-500 flex items-center gap-2">
                                <x-link href="{{ $event->getGuestUrl($subdomain, $isUnique ? false : null) }}" target="_blank">
                                    {{ \App\Utils\UrlUtils::clean($event->getGuestUrl($subdomain, $isUnique ? false : null)) }}
                                </x-link>
                                <button type="button" onclick="copyEventUrl(this)" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>

                        <!--
                        <x-input-label for="event_slug" :value="__('messages.url')" />
                            <div class="mt-1 flex">
                                <x-text-input type="text" 
                                    class="block w-1/2 rounded-r-none bg-gray-100 dark:bg-gray-800" 
                                    :value="''"
                                    readonly />
                                <x-text-input id="event_slug" 
                                    name="slug" 
                                    type="text" 
                                    class="block w-1/2 rounded-l-none border-l-0"
                                    :value="old('slug', $event->slug)"
                                    placeholder="{{ __('messages.auto_generated') }}"                                    autocomplete="off" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        -->

                        @if($effectiveRole->groups && count($effectiveRole->groups))
                        <div class="mb-6">
                            <x-input-label for="current_role_group_id" :value="__('messages.schedule')" />
                            <select id="current_role_group_id" name="current_role_group_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach($effectiveRole->groups as $group)
                                    @php
                                        $selectedGroupId = null;
                                        if ($event->exists) {
                                            $selectedGroupId = $event->getGroupIdForSubdomain($effectiveRole->subdomain);
                                            if ($selectedGroupId) {
                                                $selectedGroupId = \App\Utils\UrlUtils::encodeId($selectedGroupId);
                                            }
                                        }
                                    @endphp
                                    <option value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('current_role_group_id', $selectedGroupId) == \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->translatedName() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('current_role_group_id')" />
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="category_id" :value="__('messages.category')" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl') }}">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach(get_translated_categories() as $id => $label)
                                    <option value="{{ $id }}" {{ old('category_id', $event->category_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="starts_at"
                                :value="__('messages.date_and_time') . '*'"/>
                            <x-text-input type="text" id="starts_at" name="starts_at" class="datepicker"
                                :value="old('starts_at', $event->localStartsAt())"
                                required autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="duration" :value="__('messages.duration_in_hours')" />
                            <x-text-input type="number" id="duration" name="duration" step="0.01"
                                :value="old('duration', $event->duration)" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration')" />
                        </div>
                        
                        <div class="mb-6">
                        <x-input-label for="flyer_image" :value="__('messages.flyer_image')" />
                        <input id="flyer_image" name="flyer_image" type="file" class="mt-1 block w-full text-gray-900 dark:text-gray-100" 
                                accept="image/png, image/jpeg" onchange="previewImage(this);" />
                            <x-input-error class="mt-2" :messages="$errors->get('flyer_image')" />
                            <p id="image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                {{ __('messages.image_size_warning') }}
                            </p>

                            <div id="image_preview" class="mt-3" style="display: none;">
                                <img id="preview_img" src="#" alt="Preview" style="max-height:120px" />
                            </div>

                            @if ($event->flyer_image_url)
                            <img src="{{ $event->flyer_image_url }}" style="max-height:120px" class="pt-3" />
                            <a href="#"
                                onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('event.delete_image', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id), 'image_type' => 'flyer']) }}'; }"
                                class="hover:underline text-gray-900 dark:text-gray-100">
                                {{ __('messages.delete_image') }}
                            </a>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.event_details')" />
                            <textarea id="description" name="description"
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                autocomplete="off">{{ old('description', $event->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                    </div>
                </div>
                @endif
                <div id="section-venue" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
                            </svg>
                            {{ __('messages.venue') }}
                        </h2>

                        <div class="mb-6">
                            <fieldset>
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center">
                                        <input id="in_person" name="event_type" type="checkbox" v-model="isInPerson"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            @change="onChangeVenueType('in_person')">
                                        <label for="in_person" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.in_person') }}
                                        </label>
                                    </div>
                                    <div class="flex items-center pl-3">
                                        <input id="online" name="event_type" type="checkbox" v-model="isOnline"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            @change="onChangeVenueType('online')">
                                        <label for="online" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.online') }}
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <x-text-input name="venue_id" v-bind:value="selectedVenue.id" type="hidden" />

                        <div v-if="isInPerson">
                            <div v-if="!selectedVenue || showVenueAddressFields" class="mb-6">
                                <div v-if="!selectedVenue">
                                    <fieldset v-if="Object.keys(venues).length > 0">                                
                                        <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                            <div v-if="Object.keys(venues).length > 0" class="flex items-center">
                                                <input id="use_existing_venue" name="venue_type" type="radio" value="use_existing" v-model="venueType"
                                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                                <label for="use_existing_venue"
                                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="create_new_venue" name="venue_type" type="radio" value="create_new" v-model="venueType"
                                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                                <label for="create_new_venue"
                                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.create_new') }}</label>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <div v-if="venueType === 'use_existing'">
                                        <select id="selected_venue"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl') }}"
                                                v-model="selectedVenue">
                                                <option value="" disabled selected>{{ __('messages.please_select') }}</option>                                
                                                <option v-for="venue in venues" :key="venue.id" :value="venue">
                                                    @{{ venue.name || venue.address1 }} <template v-if="venue.email">(@{{ venue.email }})</template>
                                                </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="showAddressFields()">
                                    <div class="mb-6">
                                        <x-input-label for="venue_name" :value="__('messages.name')" />
                                        <x-text-input id="venue_name" name="venue_name" type="text"
                                            class="mt-1 block w-full" v-model="venueName" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_email" :value="__('messages.email')" />
                                        <div class="flex mt-1">
                                            <x-text-input id="venue_email" name="venue_email" type="email" class="block w-full"
                                                @blur="searchVenues" v-model="venueEmail" autocomplete="off" />
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                                        @if (config('app.hosted'))
                                        <div v-if="(venueType === 'create_new' || (!selectedVenue.user_id && venueEmail)) && venueEmail" class="mt-2">
                                            <div class="flex items-center">
                                                <input id="send_email_to_venue" name="send_email_to_venue" type="checkbox" v-model="sendEmailToVenue"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                <label for="send_email_to_venue" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.send_email_to_notify_them') }}
                                                </label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <div v-if="venueSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="venue in venueSearchResults" :key="venue.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate">
                                                        <a :href="venue.url" target="_blank" class="hover:underline">@{{ venue.name }}</a>:
                                                        @{{ venue.address1 }}
                                                    </span>
                                                </div>
                                                <x-primary-button @click="selectVenue(venue)" type="button">
                                                    {{ __('messages.select') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_address1" :value="__('messages.street_address')" />
                                        <x-text-input id="venue_address1" name="venue_address1" type="text"
                                            class="mt-1 block w-full" v-model="venueAddress1" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_address1')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_city" :value="__('messages.city')" />
                                        <x-text-input id="venue_city" name="venue_city" type="text" class="mt-1 block w-full"
                                            v-model="venueCity" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_city')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_state" :value="__('messages.state_province')" />
                                        <x-text-input id="venue_state" name="venue_state" type="text" class="mt-1 block w-full"
                                            v-model="venueState" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_state')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_postal_code" :value="__('messages.postal_code')" />
                                        <x-text-input id="venue_postal_code" name="venue_postal_code" type="text"
                                            class="mt-1 block w-full" v-model="venuePostalCode" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_postal_code')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_country" :value="__('messages.country')" />
                                        <x-text-input id="venue_country" name="venue_country" type="text" class="mt-1 block w-full"
                                            onchange="onChangeCountry()" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('country')" />
                                        <input type="hidden" id="venue_country_code" name="venue_country_code" 
                                            v-model="venueCountryCode"/>
                                    </div>

                                    <div class="mb-6">
                                        <div class="flex items-center space-x-4">
                                            <x-secondary-button id="view_map_button" onclick="viewMap()">{{ __('messages.view_map') }}</x-secondary-button>
                                            @if (config('services.google.backend'))
                                            <x-secondary-button id="validate_button" onclick="onValidateClick()">{{ __('messages.validate_address') }}</x-secondary-button>
                                            <x-secondary-button id="accept_button" onclick="acceptAddress(event)" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
                                            @endif
                                            <x-primary-button v-if="showVenueAddressFields" type="button" @click="updateSelectedVenue()">{{ __('messages.done') }}</x-primary-button>
                                        </div>
                                    </div>

                                    <div id="address_response" class="mb-6 hidden text-gray-900 dark:text-gray-100"></div>

                                </div>
                            </div>

              
                            <div v-else class="mb-6">
                                <div class="flex justify-between w-full">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            <template v-if="selectedVenue.url">
                                                <a :href="selectedVenue.url" target="_blank" class="hover:underline">@{{ venueName || venueAddress1 }}</a>
                                            </template>
                                            <template v-else>
                                                @{{ venueName || venueAddress1 }}
                                            </template>
                                            <template v-if="venueEmail">
                                                (<a :href="'mailto:' + venueEmail" class="hover:underline">@{{ venueEmail }}</a>)
                                            </template>
                                        </span>
                                    </div>
                                    <div>
                                        <x-secondary-button v-if="!selectedVenue.user_id" @click="editSelectedVenue" type="button" class="mr-2">
                                            {{ __('messages.edit') }}
                                        </x-secondary-button>
                                        <x-secondary-button @click="clearSelectedVenue" type="button">
                                            {{ __('messages.remove') }}
                                        </x-secondary-button>
                                    </div>
                                </div>
                            </div>                        

                        </div>

                        <div v-if="isOnline">
                            <x-input-label for="event_url" :value="__('messages.event_url')" />
                            <x-text-input id="event_url" name="event_url" type="url" class="mt-1 block w-full"
                                v-model="event.event_url" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('event_url')" />
                        </div>
                        <div v-if="!isOnline">
                            <input type="hidden" name="event_url" value="" />
                        </div>
                    </div>
                </div>


                <div id="section-participants" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            {{ __('messages.participants') . ($role->isVenue() ? ' - ' . __('messages.optional') : '') }}
                            <span v-if="selectedMembers.length > 1">(@{{ selectedMembers.length }})</span>
                        </h2>

                        <div>
                            <div v-if="selectedMembers && selectedMembers.length > 0" class="mb-6">
                                <div v-for="member in selectedMembers" :key="member.id" class="flex items-center justify-between mb-2">
                                    <input type="hidden" v-bind:name="'members[' + member.id + '][email]'" v-bind:value="member.email" />
                                    <div v-show="editMemberId === member.id" class="w-full">
                                        <div class="mb-6">
                                            <x-input-label :value="__('messages.name') . ' *'" />
                                            <div class="flex mt-1">
                                                <x-text-input v-bind:id="'edit_member_name_' + member.id"
                                                    v-bind:name="'members[' + member.id + '][name]'" type="text" class="mr-2 block w-full"
                                                    v-model="selectedMembers.find(m => m.id === member.id).name" v-bind:required="editMemberId === member.id"
                                                    @keydown.enter.prevent="editMember()" autocomplete="off" />
                                                <x-primary-button @click="editMember()" type="button">
                                                    {{ __('messages.done') }}
                                                </x-primary-button>
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('member_name')" />
                                        </div>

                                        <div class="mb-6">  
                                            <x-input-label for="edit_member_email" :value="__('messages.email')" />
                                            <x-text-input v-bind:id="'edit_member_email_' + member.id" 
                                                v-bind:name="'members[' + member.id + '][email]'" type="email" class="mr-2 block w-full" 
                                                v-model="selectedMembers.find(m => m.id === member.id).email" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            @if (config('app.hosted'))
                                            <div v-if="selectedMembers.find(m => m.id === member.id).email && !member.user_id" class="mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                        :id="'send_email_to_edit_member_' + member.id" 
                                                        :name="'send_email_to_members[' + selectedMembers.find(m => m.id === member.id).email + ']'" 
                                                        v-model="sendEmailToMembers[selectedMembers.find(m => m.id === member.id).email]"
                                                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                    <label :for="'send_email_to_edit_member_' + member.id" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                        {{ __('messages.send_email_to_notify_them') }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="mb-6">
                                            <x-input-label for="edit_member_youtube_url" :value="__('messages.youtube_video_url')" />
                                            <x-text-input v-bind:id="'edit_member_youtube_url_' + member.id" 
                                                v-bind:name="'members[' + member.id + '][youtube_url]'" type="url" class="mr-2 block w-full" 
                                                v-model="selectedMembers.find(m => m.id === member.id).youtube_url" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                        </div>

                                    </div>
                                    <div v-show="editMemberId !== member.id" class="flex justify-between w-full">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <span class="text-sm text-gray-900 dark:text-gray-100 truncate">
                                                    <template v-if="member.url">
                                                        <a :href="member.url" target="_blank" class="hover:underline">@{{ member.name }}</a>
                                                    </template>
                                                    <template v-else>
                                                        @{{ member.name }}
                                                    </template>
                                                    <template v-if="member.email">
                                                        (<a :href="'mailto:' + member.email" class="hover:underline">@{{ member.email }}</a>)
                                                    </template>
                                                </span>
                                                <a v-if="member.youtube_url" :href="member.youtube_url" target="_blank" class="ml-2">
                                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                            @if (config('app.hosted'))
                                            <div v-if="((member.id && member.id.toString().startsWith('new_')) || (!member.user_id)) && member.email" class="mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                        :id="'send_email_to_member_' + member.id" 
                                                        :name="'send_email_to_members[' + member.email + ']'" 
                                                        v-model="sendEmailToMembers[member.email]"
                                                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                    <label :for="'send_email_to_member_' + member.id" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                        {{ __('messages.send_email_to_notify_them') }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <x-secondary-button v-if="!member.user_id" @click="editMember(member)" type="button" class="mr-2">
                                                {{ __('messages.edit') }}
                                            </x-secondary-button>
                                            <x-secondary-button @click="removeMember(member)" type="button">
                                                {{ __('messages.remove') }}
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="showMemberTypeRadio">
                                <fieldset>                                
                                    <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                        <div v-if="Object.keys(members).length > 0" class="flex items-center">
                                            <input id="use_existing_members" name="member_type" type="radio" value="use_existing" v-model="memberType"
                                                class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <label for="use_existing_members"
                                                class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                        </div>
                                        <div v-if="Object.keys(members).length == 0" class="flex items-center">
                                            <input id="use_existing_members" name="member_type" type="radio" value="use_existing" v-model="memberType"
                                                class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <label for="use_existing_members"
                                                class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.none') }}</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="create_new_members" name="member_type" type="radio" value="create_new" v-model="memberType"
                                                class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <label for="create_new_members"
                                                class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.create_new') }}</label>
                                        </div>
                                    </div>
                                </fieldset>

                                <div v-if="memberType === 'use_existing' && Object.keys(members).length > 0">
                                    <select v-model="selectedMember" @change="addExistingMember" id="selected_member"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                        <option v-for="member in filteredMembers" :key="member.id" :value="member">
                                            @{{ member.name }} <template v-if="member.email">(@{{ member.email }})</template>
                                        </option>
                                    </select>
                                </div>

                                <div v-if="memberType === 'create_new'"> 
                                    <div class="mb-6">
                                        <x-input-label for="member_name" :value="__('messages.name') . ' *'" />
                                        <div class="flex mt-1">
                                            <x-text-input id="member_name" @keydown.enter.prevent="addMember"
                                                v-model="memberName" type="text" class="mr-2 block w-full" required autocomplete="off" />
                                            <x-primary-button @click="addMember" type="button">
                                                {{ __('messages.add') }}
                                            </x-primary-button>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="member_email" :value="__('messages.email')" />
                                        <div class="flex mt-1">
                                            <x-text-input id="member_email" name="member_email" type="email" class="mr-2 block w-full"
                                            @keydown.enter.prevent="addMember" @blur="searchMembers" v-model="memberEmail" autocomplete="off" />
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('member_email')" />
                                        @if (config('app.hosted'))
                                        <div v-if="memberEmail" class="mt-2">
                                            <div class="flex items-center">
                                                <input id="send_email_to_new_member" 
                                                    type="checkbox" 
                                                    v-model="sendEmailToNewMember"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                <label for="send_email_to_new_member" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.send_email_to_notify_them') }}
                                                </label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <div v-if="memberSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="member in memberSearchResults" :key="member.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                                        <a :href="member.url" target="_blank" class="hover:underline">@{{ member.name }}</a>
                                                        <template v-if="member.email">
                                                            (<a :href="'mailto:' + member.email" class="hover:underline">@{{ member.email }}</a>)
                                                        </template>
                                                    </span>
                                                    <a v-if="member.youtube_url" :href="member.youtube_url" target="_blank" class="ml-2">
                                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                                <x-primary-button @click="selectMember(member)" type="button">
                                                    {{ __('messages.select') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="member_youtube_url" :value="__('messages.youtube_video_url')" />
                                        <x-text-input id="member_youtube_url" @keydown.enter.prevent="addMember"
                                            v-model="memberYoutubeUrl" type="url" class="mr-2 block w-full" autocomplete="off" />
                                    </div>
                                
                                </div>
                                
                            </div>

                            <div v-if="!showMemberTypeRadio" class="mt-4 flex justify-end">
                                <x-secondary-button @click="showAddMemberForm" type="button">
                                    {{ __('messages.add') }}
                                </x-secondary-button>
                            </div>
                        </div>
                    </div>
                </div>

                @if (! $role->isCurator())
                <div id="section-recurring" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ __('messages.recurring') }}
                        </h2>

                        <div class="mb-6 sm:flex sm:items-center sm:space-x-10">
                            <div class="flex items-center">
                                <input id="one_time" name="schedule_type" type="radio" value="one_time" onchange="onChangeDateType()" {{ $event->days_of_week ? '' : 'CHECKED' }}
                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                <label for="one_time"
                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.one_time') }}</label>
                            </div>
                            <div class="flex items-center">
                                <input id="recurring" name="schedule_type" type="radio" value="recurring" onchange="onChangeDateType()"  {{ $event->days_of_week ? 'CHECKED' : '' }}
                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                <label for="recurring"
                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.recurring') }}</label>
                            </div>
                        </div>

                        <div id="days_of_week_div" class="mb-6 {{ ! $event || ! $event->days_of_week ? 'hidden' : '' }}">
                            <x-input-label :value="__('messages.days_of_week')" />
                            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $index => $day)
                            <label for="days_of_week_{{ $index }}" class="mr-3 text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">
                                <input type="checkbox" id="days_of_week_{{ $index }}" name="days_of_week_{{ $index }}" class="h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]"
                                    {{ $event && $event->days_of_week && $event->days_of_week[$index] == '1' ? 'checked' : '' }}/> &nbsp;
                                {{ __('messages.' . $day) }}
                            </label>
                            @endforeach
                        </div>

                        <div v-if="isRecurring" id="recurring_end_div" class="mb-6">
                            <x-input-label :value="__('messages.recurring_end')" />
                            <div class="mt-2 space-y-4">
                                <div class="flex items-center">
                                    <input id="recurring_end_never" name="recurring_end_type" type="radio" value="never" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                    <label for="recurring_end_never"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.never') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="recurring_end_on_date" name="recurring_end_type" type="radio" value="on_date" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                    <label for="recurring_end_on_date"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.on_date') }}</label>
                                </div>
                                <div v-if="event.recurring_end_type === 'on_date'" class="ml-7">
                                    <x-text-input type="text" id="recurring_end_date" name="recurring_end_value" class="datepicker-end-date mt-1 block w-full"
                                        value="{{ old('recurring_end_value', $event->recurring_end_value) }}"
                                        autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('recurring_end_value')" />
                                </div>
                                <div class="flex items-center">
                                    <input id="recurring_end_after_events" name="recurring_end_type" type="radio" value="after_events" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                    <label for="recurring_end_after_events"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.after_events') }}</label>
                                </div>
                                <div v-if="event.recurring_end_type === 'after_events'" class="ml-7">
                                    <x-text-input type="number" id="recurring_end_count" name="recurring_end_value" class="mt-1 block w-full"
                                        :value="old('recurring_end_value', $event->recurring_end_value)"
                                        v-model="event.recurring_end_value"
                                        min="1" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('recurring_end_value')" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                @endif

                @php
                    $curators = $user->allCurators();
                    $curators = $curators->filter(function($curator) use ($subdomain) {
                        return $curator->subdomain !== $subdomain;
                    });
                @endphp
                
                @if ($curators->count() > 0)
                <div id="section-schedules" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            {{ __('messages.schedules') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label class="mb-2" for="curators" :value="__(count($curators) > 1 ? 'messages.add_to_schedules' : 'messages.add_to_schedule')" />
                            
                            @foreach($curators as $curator)
                            @php
                                $isClonedCurator = isset($clonedCurators) && $clonedCurators->contains(function($c) use ($curator) { return $c->id == $curator->id; });
                                $isCuratorChecked = (! $event->exists && ($role->subdomain == $curator->subdomain || session('pending_request') == $curator->subdomain)) || $event->curators->contains($curator->id) || $isClonedCurator;
                            @endphp
                            <div class="mb-4">
                                <div class="flex items-center mb-2 h-6">
                                    <input type="checkbox" 
                                           id="curator_{{ $curator->encodeId() }}" 
                                           name="curators[]" 
                                           value="{{ $curator->encodeId() }}"
                                           {{ $isCuratorChecked ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                           @change="toggleCuratorGroupSelection('{{ $curator->encodeId() }}')">
                                    <label for="curator_{{ $curator->encodeId() }}" class="ml-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $curator->name }}
                                    </label>
                                    <div class="ml-2 flex-shrink-0">
                                        @if($curator->accept_requests && $curator->request_terms)
                                        <div class="relative group">
                                            <button type="button" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 focus:outline-none">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-4 py-3 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-[28rem] max-w-lg z-10">
                                                <div class="leading-relaxed" 
                                                     dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
                                                     style="{{ is_rtl() ? 'text-align: right;' : 'text-align: left;' }}">{!! nl2br(e($curator->translatedRequestTerms())) !!}</div>
                                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="w-4 h-4"></div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($curator->groups && count($curator->groups) > 0)
                                <div id="curator_group_{{ $curator->encodeId() }}" class="ml-6 mb-2" style="display: {{ $isCuratorChecked ? 'block' : 'none' }};">
                                    <select id="curator_group_{{ $curator->encodeId() }}" 
                                            name="curator_groups[{{ $curator->encodeId() }}]" 
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach($curator->groups as $group)
                                            @php
                                                $selectedGroupId = null;
                                                if ($event->exists) {
                                                    $selectedGroupId = $event->getGroupIdForSubdomain($curator->subdomain);
                                                    if ($selectedGroupId) {
                                                        $selectedGroupId = \App\Utils\UrlUtils::encodeId($selectedGroupId);
                                                    }
                                                } elseif (isset($clonedCuratorGroups) && isset($clonedCuratorGroups[$curator->encodeId()])) {
                                                    $selectedGroupId = $clonedCuratorGroups[$curator->encodeId()];
                                                }
                                            @endphp
                                            <option value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('curator_groups.' . $curator->encodeId(), $selectedGroupId) == \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->translatedName() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                    @if ($event->user_id == $user->id)
                    <div id="section-tickets" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                        <div class="max-w-xl">                                                
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                {{ __('messages.tickets') }}
                            </h2>

                            <div class="mb-6">
                                <div class="flex items-center">
                                    <input id="tickets_enabled" name="tickets_enabled" type="checkbox" v-model="event.tickets_enabled" :value="1"
                                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                        {{ ! $role->isPro() ? 'disabled' : '' }}>
                                    <input type="hidden" name="tickets_enabled" :value="event.tickets_enabled ? 1 : 0" >
                                    <label for="tickets_enabled" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                        {{ __('messages.enable_tickets') }}
                                        @if (! $role->isPro())
                                        <div class="text-xs pt-1">
                                            <x-link href="{{ route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan']) }}" target="_blank">
                                                {{ __('messages.requires_pro_plan') }}
                                            </x-link>
                                        </div>
                                        @endif
                                    </label>
                                </div>
                            </div>

                            <!-- Registration URL (only visible when tickets are disabled) -->
                            <div class="mb-6" v-show="!event.tickets_enabled">
                                <x-input-label for="registration_url" :value="__('messages.registration_url')" />
                                <x-text-input id="registration_url" name="registration_url" type="url" class="mt-1 block w-full"
                                    v-model="event.registration_url" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.registration_url_help') }}</p>
                            </div>

                            @if ($role->isPro())
                            <div v-show="event.tickets_enabled">

                                <!-- Ticket Section Tabs -->
                                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                                    <nav class="-mb-px flex space-x-6">
                                        <button type="button" @click="activeTicketTab = 'tickets'"
                                            :class="activeTicketTab === 'tickets' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                            class="whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium">
                                            {{ __('messages.general') }}
                                        </button>
                                        <button type="button" @click="activeTicketTab = 'payment'"
                                            :class="activeTicketTab === 'payment' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                            class="whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium">
                                            {{ __('messages.payment') }}
                                        </button>
                                        <button type="button" @click="activeTicketTab = 'options'"
                                            :class="activeTicketTab === 'options' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                            class="whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium">
                                            {{ __('messages.options') }}
                                        </button>
                                    </nav>
                                </div>

                                <!-- Payment Tab -->
                                <div v-show="activeTicketTab === 'payment'">
                                @if ($user->canAcceptStripePayments() || $user->invoiceninja_api_key || $user->payment_url)
                                <div class="mb-6">
                                    <x-input-label for="payment_method" :value="__('messages.payment_method')"/>
                                    <select id="payment_method" name="payment_method" v-model="event.payment_method" :required="event.tickets_enabled"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="cash">Cash</option>
                                        @if ($user->stripe_completed_at)
                                        <option value="stripe">Stripe - {{ $user->stripe_company_name }}</option>
                                        @elseif ($user->canAcceptStripePayments())
                                        <option value="stripe">Stripe</option>
                                        @endif
                                        @if ($user->invoiceninja_api_key)
                                        <option value="invoiceninja">Invoice Ninja - {{ $user->invoiceninja_company_name }}</option>
                                        @endif
                                        @if ($user->payment_url)
                                        <option value="payment_url">{{ __('messages.payment_url') }} - {{ $user->paymentUrlHost() }}</option>
                                        @endif
                                    </select>
                                    <div class="text-xs pt-1">
                                        <x-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </x-link>
                                    </div>
                                </div>
                                @endif

                                <div class="mb-6">
                                    <x-input-label for="ticket_currency_code" :value="__('messages.currency')"/>
                                    <select id="ticket_currency_code" name="ticket_currency_code" v-model="event.ticket_currency_code" :required="event.tickets_enabled"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach ($currencies as $currency)
                                        @if ($loop->index == 2)
                                        <option disabled>──────────</option>
                                        @endif
                                        <option value="{{ $currency->value }}" {{ $event->ticket_currency_code == $currency->value ? 'selected' : '' }}>
                                            {{ $currency->value }} - {{ $currency->label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if (! $user->canAcceptStripePayments() && ! $user->invoiceninja_api_key && ! $user->payment_url)
                                    <div class="text-xs pt-1">
                                        <x-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </x-link>
                                    </div>
                                    @endif
                                </div>

                                <div class="mb-6" v-show="event.payment_method == 'cash'">
                                    <x-input-label for="payment_instructions" :value="__('messages.payment_instructions')" />
                                    <textarea id="payment_instructions" name="payment_instructions" v-model="event.payment_instructions" rows="4"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                </div>
                                </div>

                                <!-- Options Tab -->
                                <div v-show="activeTicketTab === 'options'">
                                <!-- Event-level Custom Fields -->
                                <div class="mb-6">
                                    <x-input-label :value="__('messages.custom_fields') . ' (' . __('messages.per_order') . ')'" class="mb-3" />

                                    <div v-if="eventCustomFields && Object.keys(eventCustomFields).length > 0">
                                        <div v-for="(field, fieldKey) in eventCustomFields" :key="fieldKey" class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-md">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <x-input-label :value="__('messages.field_name') . ' *'" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.name" class="mt-1 block w-full text-sm" v-bind:required="event.tickets_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && !field.name }" />
                                                    <p v-if="formSubmitAttempted && !field.name" class="mt-1 text-xs text-red-600">{{ __('messages.field_name_required') }}</p>
                                                </div>
                                                <div>
                                                    <x-input-label :value="__('messages.field_type')" class="text-xs" />
                                                    <select v-model="field.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                                        <option value="string">{{ __('messages.type_string') }}</option>
                                                        <option value="multiline_string">{{ __('messages.type_multiline_string') }}</option>
                                                        <option value="switch">{{ __('messages.type_switch') }}</option>
                                                        <option value="date">{{ __('messages.type_date') }}</option>
                                                        <option value="dropdown">{{ __('messages.type_dropdown') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-2" v-if="field.type === 'dropdown'">
                                                <x-input-label :value="__('messages.field_options')" class="text-xs" />
                                                <x-text-input type="text" v-model="field.options" class="mt-1 block w-full text-sm" :placeholder="__('messages.options_placeholder')" />
                                            </div>
                                            <div class="mt-2 flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <input type="checkbox" v-model="field.required" :id="`event_field_required_${fieldKey}`" class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                    <label :for="`event_field_required_${fieldKey}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                                </div>
                                                <x-secondary-button @click="removeEventCustomField(fieldKey)" type="button" class="text-xs py-1 px-2">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="custom_fields" :value="JSON.stringify(eventCustomFields || {})">
                                    <x-secondary-button @click="addEventCustomField" type="button" class="mt-2" v-if="getEventCustomFieldCount() < 8">
                                        {{ __('messages.add_field') }}
                                    </x-secondary-button>
                                </div>
                                </div>

                                <!-- Tickets Tab -->
                                <div v-show="activeTicketTab === 'tickets'">
                                <div class="mb-6">
                                    <div v-for="(ticket, index) in tickets" :key="index" 
                                        :class="{'mt-4 p-4 border border-gray-300 dark:border-gray-700 rounded-lg': tickets.length > 1, 'mt-4': tickets.length === 1}">
                                        <input type="hidden" v-bind:name="`tickets[${index}][id]`" v-model="ticket.id">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <x-input-label :value="__('messages.price')" />
                                                <x-text-input type="number" step="0.01" v-bind:name="`tickets[${index}][price]`" 
                                                    v-model="ticket.price" class="mt-1 block w-full" placeholder="{{ __('messages.free') }}" />
                                            </div>
                                            <div>
                                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.quantity') }} @{{ (! isRecurring && ticket.sold && Object.values(JSON.parse(ticket.sold))[0] > 0) ? (' - ' + Object.values(JSON.parse(ticket.sold))[0] + ' ' +soldLabel) : '' }}</label>
                                                <x-text-input type="number" v-bind:name="`tickets[${index}][quantity]`" 
                                                    v-model="ticket.quantity" class="mt-1 block w-full" placeholder="{{ __('messages.unlimited') }}" />
                                            </div>
                                            <div v-if="tickets.length > 1">
                                                <x-input-label :value="__('messages.type') . ' *'" />
                                                <x-text-input v-bind:name="`tickets[${index}][type]`" v-model="ticket.type"
                                                    class="mt-1 block w-full" v-bind:required="event.tickets_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && tickets.length > 1 && !ticket.type }" />
                                                <p v-if="formSubmitAttempted && tickets.length > 1 && !ticket.type" class="mt-1 text-xs text-red-600">{{ __('messages.ticket_type_required') }}</p>
                                            </div>
                                            <div v-if="tickets.length > 1" class="flex items-end gap-2">
                                                <x-secondary-button @click="addTicketCustomField(index)" type="button" class="mt-1" v-if="getTicketCustomFieldCount(index) < 8">
                                                    {{ __('messages.add_field') }}
                                                </x-secondary-button>
                                                <x-secondary-button @click="removeTicket(index)" type="button" class="mt-1">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <x-input-label :value="__('messages.description')" />
                                            <textarea v-bind:name="`tickets[${index}][description]`" v-model="ticket.description" rows="4"
                                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                        </div>

                                        <!-- Ticket-level Custom Fields -->
                                        <div class="mt-4" v-if="ticket.custom_fields && Object.keys(ticket.custom_fields).length > 0">
                                            <x-input-label :value="__('messages.custom_fields') . ' (' . __('messages.per_ticket') . ')'" />
                                            <div v-for="(field, fieldKey) in ticket.custom_fields" :key="fieldKey" class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-md">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <x-input-label :value="__('messages.field_name') . ' *'" class="text-xs" />
                                                        <x-text-input type="text" v-model="field.name" class="mt-1 block w-full text-sm" v-bind:required="event.tickets_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && !field.name }" />
                                                        <p v-if="formSubmitAttempted && !field.name" class="mt-1 text-xs text-red-600">{{ __('messages.field_name_required') }}</p>
                                                    </div>
                                                    <div>
                                                        <x-input-label :value="__('messages.field_type')" class="text-xs" />
                                                        <select v-model="field.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                                            <option value="string">{{ __('messages.type_string') }}</option>
                                                            <option value="multiline_string">{{ __('messages.type_multiline_string') }}</option>
                                                            <option value="switch">{{ __('messages.type_switch') }}</option>
                                                            <option value="date">{{ __('messages.type_date') }}</option>
                                                            <option value="dropdown">{{ __('messages.type_dropdown') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mt-2" v-if="field.type === 'dropdown'">
                                                    <x-input-label :value="__('messages.field_options')" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.options" class="mt-1 block w-full text-sm" :placeholder="__('messages.options_placeholder')" />
                                                </div>
                                                <div class="mt-2 flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" v-model="field.required" :id="`ticket_${index}_field_required_${fieldKey}`" class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                        <label :for="`ticket_${index}_field_required_${fieldKey}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                                    </div>
                                                    <x-secondary-button @click="removeTicketCustomField(index, fieldKey)" type="button" class="text-xs py-1 px-2">
                                                        {{ __('messages.remove') }}
                                                    </x-secondary-button>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" v-bind:name="`tickets[${index}][custom_fields]`" :value="JSON.stringify(ticket.custom_fields || {})">
                                    </div>

                                    <!-- Total Tickets Mode Selection -->
                                    <div v-if="hasSameTicketQuantities && tickets.length > 1" class="mt-6 p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="total_tickets_individual" name="total_tickets_mode" type="radio"
                                                    value="individual" v-model="event.total_tickets_mode"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300">
                                                <label for="total_tickets_individual" class="ml-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.individual_quantities') }} (@{{ getTotalTicketQuantity }} total)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ __('messages.individual_quantities_help') }}
                                                    </p>
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="total_tickets_combined" name="total_tickets_mode" type="radio"
                                                    value="combined" v-model="event.total_tickets_mode"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300">
                                                <label for="total_tickets_combined" class="ml-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.combined_total') }} (@{{ getCombinedTotalQuantity }} total)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ __('messages.combined_total_help') }}
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-4">
                                        <x-secondary-button @click="addTicket" type="button">
                                            {{ __('messages.add_type') }}
                                        </x-secondary-button>
                                    </div>
                                </div>
                                </div>

                                <!-- Options Tab (continued) -->
                                <div v-show="activeTicketTab === 'options'">
                                <div class="mb-6">
                                    <x-input-label for="ticket_notes" :value="__('messages.ticket_notes')" />
                                    <textarea id="ticket_notes" name="ticket_notes" v-model="event.ticket_notes" rows="4"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="terms_url" :value="__('messages.terms_url')" />
                                    <x-text-input id="terms_url" name="terms_url" type="url" class="mt-1 block w-full"
                                        :value="old('terms_url', $event->terms_url)"
                                        v-model="event.terms_url" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('messages.terms_url_help') }}
                                    </p>
                                </div>

                                <div v-if="hasLimitedPaidTickets">
                                    <div class="mb-6">
                                        <div class="flex items-center">
                                            <input id="expire_unpaid_tickets_checkbox" name="expire_unpaid_tickets_checkbox" type="checkbox"
                                                v-model="showExpireUnpaid"
                                                class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                                @change="toggleExpireUnpaid">
                                            <label for="expire_unpaid_tickets_checkbox" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                {{ __('messages.expire_unpaid_tickets') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-6" v-if="showExpireUnpaid">
                                        <x-input-label for="expire_unpaid_tickets" :value="__('messages.after_number_of_hours')" />
                                        <x-text-input id="expire_unpaid_tickets" name="expire_unpaid_tickets" type="number" class="mt-1 block w-full"
                                            :value="old('expire_unpaid_tickets', $event->expire_unpaid_tickets)"
                                            v-model="event.expire_unpaid_tickets"
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('expire_unpaid_tickets')" />
                                    </div>
                                    <div v-else>
                                        <input type="hidden" name="expire_unpaid_tickets" value="0"/>
                                    </div>
                                </div>
                                </div>

                                <!-- Save as Default (outside all tabs) -->
                                @if ($user->isMember($subdomain))
                                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center">
                                        <input id="save_default_tickets" name="save_default_tickets" type="checkbox"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                        <label for="save_default_tickets" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.save_as_default') }}
                                        </label>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

        
            @if ($event->exists && $event->canBeSyncedToGoogleCalendarForSubdomain(request()->subdomain))
            <div id="section-google-calendar" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                <div class="max-w-xl">                                                
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ __('messages.google_calendar_sync') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        {{ __('messages.sync_this_event_description') }}
                    </p>
                
                    <div class="flex items-center space-x-4">
                        @if ($event->isSyncedToGoogleCalendarForSubdomain(request()->subdomain))
                            <div class="flex items-center text-green-600 dark:text-green-400">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">{{ __('messages.synced_to_google_calendar') }}</span>
                            </div>
                            <x-secondary-button type="button" onclick="unsyncEvent('{{ $subdomain }}', {{ $event->id }})">
                                {{ __('messages.remove_from_google_calendar') }}
                            </x-secondary-button>
                        @else
                            <div class="flex items-center text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">{{ __('messages.not_synced_to_google_calendar') }}</span>
                            </div>
                            <x-primary-button type="button" onclick="syncEvent('{{ $subdomain }}', {{ $event->id }})">
                                {{ __('messages.sync_to_google_calendar') }}
                            </x-primary-button>
                        @endif
                    </div>
                
                    <div id="sync-status-{{ $event->id }}" class="hidden mt-3">
                        <div class="flex items-center text-blue-600 dark:text-blue-400">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">{{ __('messages.syncing') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

                </div> <!-- End of main content area -->
            </div> <!-- End of grid container -->

        <div class="mx-auto space-y-6 pt-4">
            <p class="text-base dark:text-gray-400 text-gray-600 pb-2">
                @if ($event->exists)
                    {{ __('messages.event_created_by', ['user' => $event->user->name]) }}
                @else
                    {{ __('messages.note_all_events_are_publicly_listed') }}
                @endif
            </p>

            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>                    
                    <x-cancel-button></x-cancel-button>
                </div>
            </div>
        </div>

    </form>
</div>

<script {!! nonce_attr() !!}>
  const { createApp, ref } = Vue

  app = createApp({
    data() {
      return {
        event: {
          ...@json($event),
          tickets_enabled: {{ $event->tickets_enabled ? 'true' : 'false' }},
          total_tickets_mode: @json($event->total_tickets_mode ?? 'individual'),
          recurring_end_type: @json($event->recurring_end_type ?? 'never'),
          recurring_end_value: @json($event->recurring_end_value ?? null),
        },
        venues: @json($venues),
        members: @json($members ?? []),
        venueType: "{{ count($venues) > 0 ? 'use_existing' : 'create_new' }}",
        memberType: "{{ 'use_existing' }}",
        venueName: @json($selectedVenue ? $selectedVenue->name : ''),
        venueEmail: @json($selectedVenue ? $selectedVenue->email : ''),
        venueAddress1: @json($selectedVenue ? $selectedVenue->address1 : ''),
        venueCity: @json($selectedVenue ? $selectedVenue->city : ''),
        venueState: @json($selectedVenue ? $selectedVenue->state : ''),
        venuePostalCode: @json($selectedVenue ? $selectedVenue->postal_code : ''),
        venueCountryCode: @json($selectedVenue ? $selectedVenue->country_code : ''),
        venueSearchEmail: "",
        venueSearchResults: [],
        selectedVenue: @json($selectedVenue ? $selectedVenue->toData() : ""),
        selectedMembers: @json($selectedMembers ?? []),
        memberSearchResults: [],
        selectedMember: "",
        editMemberId: "",
        memberEmail: "",
        memberName: "",
        memberYoutubeUrl: "",
        showMemberTypeRadio: true,
        showVenueAddressFields: false,
        isInPerson: false,
        isOnline: false,
        eventName: @json($event->name ?? ''),
        tickets: @json($event->tickets ?? [new Ticket()]).map(ticket => ({
          ...ticket,
          custom_fields: ticket.custom_fields || {},
          /*
          price: new Intl.NumberFormat('{{ app()->getLocale() }}', {
            style: 'currency',
            currency: '{{ $event->ticket_currency_code ?? "USD" }}'
          }).format(ticket.price).toString().replace(/[^\d.,]/g, '')
          */
         price: new Intl.NumberFormat('{{ app()->getLocale() }}', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          }).format(ticket.price)
        })),
        eventCustomFields: @json($event->custom_fields ?? []),
        showExpireUnpaid: @json($event->expire_unpaid_tickets > 0),
        activeTicketTab: 'tickets',
        formSubmitAttempted: false,
        soldLabel: "{{ __('messages.sold_reserved') }}",
        isRecurring: @json($event->days_of_week ? true : false),
        sendEmailToVenue: false,
        sendEmailToMembers: {},
        sendEmailToNewMember: false,
      }
    },
    methods: {
      clearSelectedVenue() {
        this.selectedVenue = "";
      },
      editSelectedVenue() {
        this.showVenueAddressFields = true;
        

        this.$nextTick(() => {
            $("#venue_country").countrySelect({
                defaultCountry: this.venueCountryCode,
            });
        });
      },
      updateSelectedVenue() {
        this.showVenueAddressFields = false;
      },
      searchVenues() {
        if (! this.venueEmail) {
          return;
        }

        const emailInput = document.getElementById('venue_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        fetch(`/search_roles?type=venue&search=${encodeURIComponent(this.venueEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          this.venueSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
      },
      selectVenue(venue) {
        this.selectedVenue = venue;
        this.venueName = venue.name;
        this.venueEmail = venue.email;
        this.venueAddress1 = venue.address1;
        this.venueCity = venue.city;
        this.venueState = venue.state;
        this.venuePostalCode = venue.postal_code;
        this.venueCountryCode = venue.country_code;
      },
      setFocusBasedOnVenueType() {
        this.$nextTick(() => {
          if (this.venueType === 'create_new') {
            const venueNameInput = document.getElementById('venue_name');
            if (venueNameInput) {
              venueNameInput.focus();
            }
          }
        });
      },
      showAddressFields() {
        return (this.venueType === 'use_existing' && this.selectedVenue && ! this.selectedVenue.user_id) 
            || this.venueType === 'create_new';
      },
      searchMembers() {
        if (! this.memberEmail) {
          return;
        }

        const emailInput = document.getElementById('member_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        fetch(`/search_roles?type=member&search=${encodeURIComponent(this.memberEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          this.memberSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching members:', error);
        });
      },
      selectMember(member) {
        if (! this.selectedMembers.some(m => m.id === member.id)) {
          this.selectedMembers.push(member);
          // Initialize sendEmailToMembers for selected member if they don't have user_id and have email
          if (!member.user_id && member.email) {
            this.$set(this.sendEmailToMembers, member.email, false);
          }
        }        
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.showMemberTypeRadio = false;
      },
      removeMember(member) {
        this.selectedMembers = this.selectedMembers.filter(m => m.id !== member.id);
        // Remove from sendEmailToMembers
        if (member.email && this.sendEmailToMembers[member.email] !== undefined) {
          delete this.sendEmailToMembers[member.email];
        }
        if (this.selectedMembers.length === 0) {
          this.showMemberTypeRadio = true;
        }
      },
      editMember(member) {
        if (member) {
          this.showMemberTypeRadio = false;
          this.editMemberId = member.id;
          this.$nextTick(() => {
            const memberNameInput = document.getElementById(`edit_member_name_${member.id}`);
            if (memberNameInput) {
              memberNameInput.focus();
            }
          });
        } else {
           const memberNameInput = document.getElementById(`edit_member_name_${this.editMemberId}`);
           if (memberNameInput && !memberNameInput.checkValidity()) {
            memberNameInput.reportValidity();
            return;
          }

          const youtubeInput = document.getElementById(`edit_member_youtube_url_${this.editMemberId}`);
          if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
            youtubeInput.reportValidity();
            return;
          }

          this.editMemberId = "";
        }
      },
      addNewMember() {
        const nameInput = document.getElementById('member_name');    
        if (!nameInput.checkValidity()) {
          nameInput.reportValidity();
          return;
        }

        const youtubeInput = document.getElementById('member_youtube_url');
        if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
          youtubeInput.reportValidity();
          return;
        }

        const newMember = {
          id: 'new_' + Date.now(),
          name: this.memberName,
          email: this.memberEmail,
          youtube_url: this.memberYoutubeUrl,
        };

        this.selectedMembers.push(newMember);
        // Initialize sendEmailToMembers for new member using the checkbox value
        if (newMember.email) {
          this.$set(this.sendEmailToMembers, newMember.email, this.sendEmailToNewMember);
        }
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.sendEmailToNewMember = false;
        this.showMemberTypeRadio = false;
      },
      addExistingMember() {
        if (this.selectedMember && !this.selectedMembers.some(m => m.id === this.selectedMember.id)) {
          this.selectedMembers.push(this.selectedMember);
          // Initialize sendEmailToMembers for selected member if they don't have user_id and have email
          if (!this.selectedMember.user_id && this.selectedMember.email) {
            this.$set(this.sendEmailToMembers, this.selectedMember.email, false);
          }
          this.$nextTick(() => {
            this.selectedMember = "";
          });
          this.showMemberTypeRadio = false;
        }
      },
      addMember() {
        const nameInput = document.getElementById('member_name');    
        if (!nameInput.checkValidity()) {
          nameInput.reportValidity();
          return;
        }

        const emailInput = document.getElementById('member_email');    
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        const youtubeInput = document.getElementById('member_youtube_url');
        if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
          youtubeInput.reportValidity();
          return;
        }

        const newMember = {
          id: 'new_' + Date.now(),
          name: this.memberName,
          email: this.memberEmail,
          youtube_url: this.memberYoutubeUrl,
        };

        this.selectedMembers.push(newMember);
        // Initialize sendEmailToMembers for new member using the checkbox value
        if (newMember.email) {
          this.$set(this.sendEmailToMembers, newMember.email, this.sendEmailToNewMember);
        }
        this.memberSearchResults = [];
        this.memberName = "";
        this.memberEmail = "";
        this.memberYoutubeUrl = "";
        this.sendEmailToNewMember = false;
        this.showMemberTypeRadio = false;
      },
      setFocusBasedOnMemberType() {
        this.$nextTick(() => {
          if (this.memberType === 'create_new') {
            const nameInput = document.getElementById('member_name');
            if (nameInput) {
              nameInput.focus();
            }
          }
        });
      },
      showAddMemberForm() {        
        this.showMemberTypeRadio = true;
        this.editMemberId = "";
        this.setFocusBasedOnMemberType();
      },
      clearEventUrl() {
        this.event.event_url = "";
      },
      onChangeVenueType(type) {
        if (type === 'in_person' && !this.isInPerson) {
            this.venueType = '{{ (count($venues) > 0 ? 'use_existing' : 'create_new'); }}';
            this.selectedVenue = '';
            this.venueName = '';
            this.venueEmail = '';
            this.venueAddress1 = '';
            this.venueCity = '';
            this.venueState = '';
            this.venuePostalCode = '';
        }
        
        this.savePreferences();
      },
      savePreferences() {
        localStorage.setItem('eventPreferences', JSON.stringify({
          isInPerson: this.isInPerson,
          isOnline: this.isOnline,
          ticketsEnabled: this.event.tickets_enabled
        }));
      },
      loadPreferences() {
        const preferences = JSON.parse(localStorage.getItem('eventPreferences'));
        @if (! $event->exists && $selectedVenue)
        this.isInPerson = true;
        if (preferences) {
          this.isOnline = preferences.isOnline;          
          @if ($role->isPro())
            this.event.tickets_enabled = preferences.ticketsEnabled ?? false;
          @else
            this.event.tickets_enabled = false;
          @endif
        }
        @else
        if (preferences) {
          this.isInPerson = preferences.isInPerson;
          this.isOnline = preferences.isOnline;
          @if ($role->isPro())
            this.event.tickets_enabled = preferences.ticketsEnabled ?? false;
          @else
            this.event.tickets_enabled = false;
          @endif
        }
        @endif
      },
      validateForm(event) {
        this.formSubmitAttempted = true;

        if (! this.isFormValid) {
          event.preventDefault();
          alert("{{ __('messages.please_select_venue_or_participant') }}");
          return;
        }

        // Check custom fields if tickets are enabled
        if (this.event.tickets_enabled) {
          const hasInvalidEventFields = Object.values(this.eventCustomFields || {}).some(field => !field.name);
          const hasInvalidTicketFields = this.tickets.some(ticket =>
            Object.values(ticket.custom_fields || {}).some(field => !field.name)
          );
          const hasInvalidTicketTypes = this.tickets.length > 1 && this.tickets.some(ticket => !ticket.type);

          if (hasInvalidEventFields || hasInvalidTicketFields) {
            event.preventDefault();
            alert("{{ __('messages.please_fill_in_custom_field_names') }}");
            return;
          }

          if (hasInvalidTicketTypes) {
            event.preventDefault();
            alert("{{ __('messages.please_fill_in_ticket_types') }}");
            return;
          }
        }
      },
      addTicket() {
        this.tickets.push({
            id: null,
            type: '',
            quantity: null,
            price: null,
            description: '',
            custom_fields: {},
        });
      },
      removeTicket(index) {
        this.tickets.splice(index, 1);
      },
      addEventCustomField() {
        const fieldCount = Object.keys(this.eventCustomFields || {}).length;
        if (fieldCount >= 8) return;
        const fieldKey = 'field' + (fieldCount + 1);
        this.eventCustomFields = {
          ...this.eventCustomFields,
          [fieldKey]: { name: '', type: 'string', required: false }
        };
      },
      removeEventCustomField(fieldKey) {
        const newFields = { ...this.eventCustomFields };
        delete newFields[fieldKey];
        this.eventCustomFields = newFields;
      },
      getEventCustomFieldCount() {
        return Object.keys(this.eventCustomFields || {}).length;
      },
      addTicketCustomField(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        if (!ticket.custom_fields) {
          ticket.custom_fields = {};
        }
        const fieldCount = Object.keys(ticket.custom_fields).length;
        if (fieldCount >= 8) return;
        const fieldKey = 'field' + (fieldCount + 1);
        ticket.custom_fields = {
          ...ticket.custom_fields,
          [fieldKey]: { name: '', type: 'string', required: false }
        };
      },
      removeTicketCustomField(ticketIndex, fieldKey) {
        const ticket = this.tickets[ticketIndex];
        const newFields = { ...ticket.custom_fields };
        delete newFields[fieldKey];
        ticket.custom_fields = newFields;
      },
      getTicketCustomFieldCount(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        return Object.keys(ticket.custom_fields || {}).length;
      },
      toggleExpireUnpaid() {
        if (! this.event.expire_unpaid_tickets) {
          this.event.expire_unpaid_tickets = 24;
        } else {
          this.event.expire_unpaid_tickets = 0;
        }
      },
      toggleCuratorGroupSelection(curatorId) {
        const groupSelection = document.getElementById(`curator_group_${curatorId}`);
        if (groupSelection) {
          const checkbox = document.getElementById(`curator_${curatorId}`);
          if (checkbox && checkbox.checked) {
            groupSelection.style.display = 'block';
          } else {
            groupSelection.style.display = 'none';
          }
        }
      },
      initializeCuratorGroupSelections() {
        // Show group selection for curators that are already checked
        const curatorCheckboxes = document.querySelectorAll('input[name="curators[]"]');
        curatorCheckboxes.forEach(checkbox => {
          if (checkbox.checked) {
            const curatorId = checkbox.value;
            this.toggleCuratorGroupSelection(curatorId);
          }
        });
      },
      initializeRecurringEndDatePicker() {
        // Use multiple nextTick calls to ensure Vue has fully rendered the v-if element
        this.$nextTick(() => {
          this.$nextTick(() => {
            const endDateInput = document.querySelector('.datepicker-end-date');
            // Make sure element exists and is not already initialized
            if (endDateInput && !endDateInput._flatpickr) {
              // Get the value from Vue model or input's value attribute
              const inputValue = this.event.recurring_end_value || endDateInput.value || null;
              
              // Clear the value attribute - flatpickr will set it via defaultDate
              if (inputValue) {
                endDateInput.removeAttribute('value');
              }
              
              var f = flatpickr(endDateInput, {
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
                defaultDate: inputValue,
                onChange: (selectedDates, dateStr, instance) => {
                  // Update Vue model with the actual value (YYYY-MM-DD format)
                  this.event.recurring_end_value = dateStr;
                }
              });
              
              // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
              if (f._input) {
                f._input.onkeydown = () => false;
              }
            }
          });
        });
      },
    },
    computed: {
      filteredMembers() {
        return this.members.filter(member => !this.selectedMembers.some(selected => selected.id === member.id));
      },
      isFormValid() {        
        var hasSubdomain = this.venueName || this.selectedMembers.length > 0;

        return hasSubdomain;
      },
      hasLimitedPaidTickets() {
        return this.tickets.some(ticket => ticket.price > 0 && ticket.quantity > 0);
      },
      hasSameTicketQuantities() {
        if (this.tickets.length <= 1) {
          return false;
        }
        
        // Check that all tickets have quantities set
        const ticketsWithQuantities = this.tickets.filter(ticket => ticket.quantity > 0);
        if (ticketsWithQuantities.length !== this.tickets.length) {
          return false;
        }
        
        // Check that all quantities are the same
        const quantities = ticketsWithQuantities.map(ticket => ticket.quantity);
        return new Set(quantities).size === 1;
      },
      getSameTicketQuantity() {
        if (!this.hasSameTicketQuantities) {
          return null;
        }
        return this.tickets.find(ticket => ticket.quantity > 0).quantity;
      },
      getTotalTicketQuantity() {
        // Always return the sum of all quantities for display purposes
        return this.tickets.reduce((total, ticket) => total + (ticket.quantity || 0), 0);
      },
      getCombinedTotalQuantity() {
        // For combined mode, the total should be the same as the individual quantity
        // since we're treating it as a single pool of tickets
        if (this.hasSameTicketQuantities) {
          return this.getSameTicketQuantity;
        }
        return this.tickets.reduce((total, ticket) => total + (ticket.quantity || 0), 0);
      }
    },
    watch: {
      venueType() {
        this.venueEmail = "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];

        this.$nextTick(() => {
            $("#venue_country").countrySelect({
                defaultCountry: "{{ $role && $role->country_code ? $role->country_code : '' }}",
            });
        });
      },
      memberType() {
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
      },
      selectedVenue() {
        this.venueName = this.selectedVenue ? this.selectedVenue.name : "";
        this.venueEmail = this.selectedVenue ? this.selectedVenue.email : "";
        this.venueAddress1 = this.selectedVenue ? this.selectedVenue.address1 : "";
        this.venueCity = this.selectedVenue ? this.selectedVenue.city : "";
        this.venueState = this.selectedVenue ? this.selectedVenue.state : "";
        this.venuePostalCode = this.selectedVenue ? this.selectedVenue.postal_code : "";
        this.venueCountryCode = this.selectedVenue ? this.selectedVenue.country_code : "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
      },
      isInPerson(newValue) {
        this.savePreferences();
      },
      isOnline(newValue) {
        if (!newValue) {
          this.clearEventUrl();
        }
        this.savePreferences();
      },
      selectedMembers: {
        handler(newValue, oldValue) {
          if (!this.eventName && newValue.length === 1) {
            this.eventName = newValue[0].name;
          }
          
          // Clean up sendEmailToMembers for removed members
          // Note: Email changes are handled by the checkbox binding using the current email
          if (oldValue && Array.isArray(oldValue)) {
            oldValue.forEach(oldMember => {
              const newMember = newValue.find(m => m.id === oldMember.id);
              if (!newMember && oldMember.email) {
                // Member was removed - clean up
                if (this.sendEmailToMembers[oldMember.email] !== undefined) {
                  delete this.sendEmailToMembers[oldMember.email];
                }
              }
            });
          }
        },
        deep: true
      },
      'event.tickets_enabled'(newValue) {
        this.savePreferences();
      },
      'event.recurring_end_type'(newValue, oldValue) {
        // Clear the value when switching between types
        if (oldValue && newValue !== oldValue) {
          this.event.recurring_end_value = null;
        }
        
        if (newValue === 'on_date') {
          this.initializeRecurringEndDatePicker();
        }
      },
    },
    mounted() {
      this.showMemberTypeRadio = this.selectedMembers.length === 0;

      const isCloned = @json($isCloned ?? false);

      if (this.event.id) {
        // Existing event - use event data
        this.isInPerson = !!this.event.venue || !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
      } else if (isCloned) {
        // Cloned event - use cloned data, don't load from localStorage
        this.isInPerson = !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
      } else {
        // New event - load from localStorage
        this.loadPreferences();

        if (!this.isInPerson && !this.isOnline) {
          this.isInPerson = true;
        }
      }

      if (this.event.id) {
        this.eventName = this.event.name;
      } else if (isCloned && this.event.name) {
        // Cloned event - preserve the cloned event name
        this.eventName = this.event.name;
      } else if (this.selectedMembers.length === 1) {
        // New event with single member - use member name
        this.eventName = this.selectedMembers[0].name;
      }

      // Initialize curator group selections
      this.initializeCuratorGroupSelections();
      
      // Initialize recurring end date picker if needed
      if (this.event.recurring_end_type === 'on_date') {
        this.initializeRecurringEndDatePicker();
      }
      
      // Initialize sendEmailToMembers for existing selectedMembers
      this.selectedMembers.forEach(member => {
        if (((member.id && member.id.toString().startsWith('new_')) || !member.user_id) && member.email) {
          this.$set(this.sendEmailToMembers, member.email, false);
        }
      });
    }
  }).mount('#app')

  // Google Calendar sync functions
  function syncEvent(subdomain, eventId) {
    const statusDiv = document.getElementById(`sync-status-${eventId}`);
    statusDiv.classList.remove('hidden');
    
    fetch(`/google-calendar/sync-event/${subdomain}/${eventId}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      statusDiv.classList.add('hidden');
      if (data.error) {
        alert('Error: ' + data.error);
      } else {
        location.reload(); // Refresh to show updated sync status
      }
    })
    .catch(error => {
      statusDiv.classList.add('hidden');
      alert('Error: ' + error.message);
    });
  }

  function unsyncEvent(subdomain, eventId) {
    if (!confirm('Are you sure you want to remove this event from Google Calendar?')) {
      return;
    }
    
    const statusDiv = document.getElementById(`sync-status-${eventId}`);
    statusDiv.classList.remove('hidden');
    
    fetch(`/google-calendar/unsync-event/${subdomain}/${eventId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      statusDiv.classList.add('hidden');
      if (data.error) {
        alert('Error: ' + data.error);
      } else {
        location.reload(); // Refresh to show updated sync status
      }
    })
    .catch(error => {
      statusDiv.classList.add('hidden');
      alert('Error: ' + error.message);
    });
  }

// Prevent browser from restoring scroll position
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

// Scroll to top immediately (before DOMContentLoaded)
window.scrollTo(0, 0);
document.documentElement.scrollTop = 0;
if (document.body) {
    document.body.scrollTop = 0;
}

// Section navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const sectionLinks = document.querySelectorAll('.section-nav-link');
    const sections = document.querySelectorAll('.section-content');
    
    // Ensure we're at the top
    window.scrollTo(0, 0);
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    
    // Prevent browser from scrolling to hash on page load
    if (window.location.hash) {
        // Temporarily remove hash to prevent auto-scroll
        const hash = window.location.hash;
        history.replaceState(null, null, ' ');
        window.scrollTo(0, 0);
        // Restore hash without scrolling
        setTimeout(function() {
            if (history.pushState) {
                history.replaceState(null, null, hash);
            }
            window.scrollTo(0, 0);
        }, 0);
    }
    
    // Function to show a specific section and hide others
    function showSection(sectionId, preventScroll = false) {
        sections.forEach(section => {
            if (section.id === sectionId) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
        
        // Update active link
        sectionLinks.forEach(link => {
            if (link.getAttribute('data-section') === sectionId) {
                link.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[#4E81FA]');
                link.classList.remove('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
            } else {
                link.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[#4E81FA]');
                link.classList.add('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
            }
        });
        
        // Update URL hash
        if (history.pushState) {
            history.pushState(null, null, '#' + sectionId);
        } else {
            window.location.hash = sectionId;
        }
        
        // Prevent scroll if requested
        if (preventScroll) {
            window.scrollTo(0, 0);
        }
    }
    
    // Handle navigation link clicks
    sectionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);
        });
    });
    
    // Check if we're on a large screen
    function isLargeScreen() {
        return window.matchMedia('(min-width: 1024px)').matches;
    }
    
    // Initialize: show first section on large screens, all on small screens
    function initializeSections() {
        if (isLargeScreen()) {
            // Check URL hash first
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                showSection(hash, true); // Prevent scroll on initial load
            } else {
                // Show first section
                const firstSection = sections[0];
                if (firstSection) {
                    showSection(firstSection.id, true); // Prevent scroll on initial load
                }
            }
        } else {
            // On small screens, show all sections
            sections.forEach(section => {
                section.style.display = 'block';
            });
        }
    }
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initializeSections();
        }, 250);
    });
    
    // Handle hash changes
    window.addEventListener('hashchange', function() {
        if (isLargeScreen()) {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById(hash)) {
                showSection(hash);
            }
        }
    });
    
    // Initialize on page load
    initializeSections();
    
    // Form validation error handling
    const form = document.querySelector('form');
    if (form) {
        // Field to section mapping
        const fieldSectionMap = {
            'event_name': 'section-details',
            'starts_at': 'section-details'
        };

        // Function to find section containing a field
        function findSectionForField(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return null;
            
            // Find the section containing this field
            let parent = field.closest('.section-content');
            if (parent) {
                return parent.id;
            }
            
            // Fallback to mapping
            return fieldSectionMap[fieldId] || null;
        }

        // Function to highlight section navigation link
        function highlightSectionError(sectionId) {
            if (!sectionId) return;
            
            const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
            if (sectionLink) {
                sectionLink.classList.add('validation-error');
            }
        }

        // Function to clear section error highlight
        function clearSectionError(fieldId) {
            const sectionId = findSectionForField(fieldId);
            if (!sectionId) return;
            
            const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
            if (sectionLink) {
                sectionLink.classList.remove('validation-error');
            }
        }

        // Handle invalid event on required fields
        const requiredFields = ['event_name', 'starts_at'];

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('invalid', function(e) {
                    // Only handle on large screens where sections are hidden
                    if (!isLargeScreen()) {
                        return;
                    }
                    
                    const sectionId = findSectionForField(fieldId);
                    if (sectionId) {
                        // Highlight the navigation link (don't change the displayed section)
                        highlightSectionError(sectionId);
                    }
                }, true); // Use capture phase to run before browser's default handling
            }
        });

        // Form submit handler - check validity before submission
        form.addEventListener('submit', function(e) {
            // Only check on large screens where sections are hidden
            if (!isLargeScreen()) {
                return; // Let browser handle validation normally
            }

            // Check if form is valid
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Find first invalid field
                let firstInvalidField = null;
                let firstInvalidSection = null;

                for (const fieldId of requiredFields) {
                    const field = document.getElementById(fieldId);
                    if (field && !field.checkValidity()) {
                        firstInvalidField = field;
                        firstInvalidSection = findSectionForField(fieldId);
                        break;
                    }
                }

                if (firstInvalidField && firstInvalidSection) {
                    // Highlight the navigation link (don't change the displayed section)
                    highlightSectionError(firstInvalidSection);
                    
                    // Trigger validation on the field to show browser message
                    setTimeout(() => {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidField.reportValidity();
                    }, 100);
                }
            }
        });

        // Add input listeners to clear error state when fields become valid
        const monitoredFields = ['event_name', 'starts_at'];
        monitoredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        clearSectionError(fieldId);
                    }
                });
                field.addEventListener('change', function() {
                    if (this.checkValidity()) {
                        clearSectionError(fieldId);
                    }
                });
            }
        });
    }
    
    // Scroll to top on page load - use multiple methods to ensure it works
    function scrollToTop() {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
    }
    
    // Scroll immediately
    scrollToTop();
    
    // Scroll after a short delay to override any hash navigation
    setTimeout(scrollToTop, 0);
    setTimeout(scrollToTop, 10);
    setTimeout(scrollToTop, 50);
    
    // Use requestAnimationFrame to ensure it happens after layout
    requestAnimationFrame(function() {
        scrollToTop();
        requestAnimationFrame(scrollToTop);
    });
});

// Also scroll to top when window fully loads (backup)
window.addEventListener('load', function() {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    
    // Additional scroll after load
    setTimeout(function() {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    }, 0);
});
</script>

</x-app-admin-layout>