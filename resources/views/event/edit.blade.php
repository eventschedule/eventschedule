<x-app-admin-layout>

@if (config('app.load_vite_assets'))
    @vite([
        'resources/js/countrySelect.min.js',
        'resources/css/countrySelect.min.css',
    ])
@else
    @php
        $countryAssets = vite_assets([
            'resources/js/countrySelect.min.js',
            'resources/css/countrySelect.min.css',
        ]);
    @endphp

    @foreach ($countryAssets['css'] as $stylesheet)
        <link rel="stylesheet" href="{{ $stylesheet }}">
    @endforeach

    @foreach ($countryAssets['js'] as $script)
        <script type="module" src="{{ $script }}" defer {!! nonce_attr() !!}></script>
    @endforeach
@endif

<!-- Step Indicator for Add Event Flow -->
@if(session('pending_request'))
    <div class="my-6">
        <x-step-indicator :compact="true" />
    </div>
@endif

<x-slot name="head">
  <style>
    button {
      min-width: 100px;
      min-height: 40px;
    }
  </style>
  <script src="{{ asset('js/vue.global.prod.js') }}"></script>
  <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr === 'function') {
            const pickerResult = flatpickr('.datepicker', {
                allowInput: true,
                enableTime: true,
                altInput: true,
                time_24hr: "{{ $role && $role->use_24_hour_time ? 'true' : 'false' }}",
                altFormat: "{{ $role && $role->use_24_hour_time ? 'M j, Y • H:i' : 'M j, Y • h:i K' }}",
                dateFormat: "Y-m-d H:i:S",
            });

            const pickerInstances = Array.isArray(pickerResult) ? pickerResult : [pickerResult];

            pickerInstances
                .filter(instance => instance && instance._input)
                .forEach(instance => {
                    // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
                    instance._input.onkeydown = () => false;
                });
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

    </script>

</x-slot>

@php
    $absoluteEventUrlBase = $role->getGuestUrl()
        ?: url('/' . $role->subdomain);

    $baseEventUrl = $absoluteEventUrlBase
        ? \App\Utils\UrlUtils::clean($absoluteEventUrlBase)
        : '';

    $slugExample = \Illuminate\Support\Str::slug(__('messages.event_name'));
    if ($slugExample === '') {
        $slugExample = 'event';
    }

    $oldSlugValue = old('slug');
    $initialSlug = $oldSlugValue !== null ? $oldSlugValue : ($event->slug ?? '');
    $slugWasManuallyEdited = $event->exists ? true : ($oldSlugValue !== null && $oldSlugValue !== '');
@endphp

<div id="app">

  <h2 class="pt-2 my-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
    {{ $title }}
  </h2>

  <form method="POST"
        x-on:submit="validateForm"
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
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.event_venue') }}
                        </h2>

                        <div class="mb-6">
                            <fieldset>
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center">
                                        <input id="in_person" name="event_type" type="checkbox" v-model="isInPerson"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            x-on:change="ensureOneChecked('in_person')">
                                        <label for="in_person" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.in_person') }}
                                        </label>
                                    </div>
                                    <div class="flex items-center pl-3">
                                        <input id="online" name="event_type" type="checkbox" v-model="isOnline"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            x-on:change="ensureOneChecked('online')">
                                        <label for="online" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.online') }}
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <x-text-input name="venue_id" v-bind:value="selectedVenue ? selectedVenue.id : ''" type="hidden" />

                        <div :class="{ 'hidden': !(isInPerson || shouldBypassPreferences) }">
                            <div class="mb-6" :class="{ 'hidden': !shouldShowVenueForm }">
                                <div :class="{ 'hidden': selectedVenue && !shouldBypassPreferences }">
                                    <fieldset v-if="hasAnyVenues">
                                        <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                            <div class="flex items-center">
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

                                    <div :class="{ 'hidden': !shouldShowExistingVenueDropdown }">
                                        <select id="selected_venue"
                                                :required="hasAnyVenues"
                                                :disabled="!hasAnyVenues"
                                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}"
                                                v-model="selectedVenue">
                                                <template v-if="!hasAnyVenues">
                                                    <option value="" selected>{{ __('messages.no_venues_listed') }}</option>
                                                </template>
                                                <template v-else>
                                                    <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                                    <option v-for="venue in availableVenues" :key="venue.id" :value="venue">
                                                        @{{ venue.name || venue.address1 }} <template v-if="venue.email">(@{{ venue.email }})</template>
                                                    </option>
                                                </template>
                                        </select>
                                    </div>
                                </div>

                                <div v-show="showAddressFields()">
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
                                                x-on:blur="searchVenues" v-model="venueEmail" autocomplete="off" />
                                        </div>
                                        @if (config('app.hosted'))
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ __('messages.an_email_will_be_sent') }}
                                        </p>
                                        @endif
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                                    </div>

                                    <div v-if="sanitizedVenueSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="venue in sanitizedVenueSearchResults" :key="venue.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate">
                                                        <a :href="venue.url" target="_blank" class="hover:underline">@{{ venue.name }}</a>:
                                                        @{{ venue.address1 }}
                                                    </span>
                                                </div>
                                                <x-primary-button x-on:click="selectVenue(venue)" type="button">
                                                    {{ __('messages.select') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_address1" :value="__('messages.street_address') . ' *'" />
                                        <x-text-input id="venue_address1" name="venue_address1" type="text"
                                            class="mt-1 block w-full" required v-model="venueAddress1" autocomplete="off" />
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
                                            <x-primary-button v-if="showVenueAddressFields" type="button" x-on:click="updateSelectedVenue()">{{ __('messages.done') }}</x-primary-button>
                                        </div>
                                    </div>

                                    <div id="address_response" class="mb-6 hidden text-gray-900 dark:text-gray-100"></div>

                                </div>
                            </div>

              
                            <div v-show="selectedVenue && !showVenueAddressFields" class="mb-6">
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
                                        <x-secondary-button v-if="!selectedVenue.user_id" x-on:click="editSelectedVenue" type="button" class="mr-2">
                                            {{ __('messages.edit') }}
                                        </x-secondary-button>
                                        <x-secondary-button x-on:click="clearSelectedVenue" type="button">
                                            {{ __('messages.remove') }}
                                        </x-secondary-button>
                                    </div>
                                </div>
                            </div>                        

                        </div>

                        <div v-if="isOnline">
                            <x-input-label for="event_url" :value="__('messages.event_url') . ' *'" />
                            <x-text-input id="event_url" name="event_url" type="url" class="mt-1 block w-full"
                                v-model="event.event_url" required autofocus autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('event_url')" />
                        </div>
                    </div>
                </div>


                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.event_participants') . ($role->isVenue() ? ' - ' . __('messages.optional') : '') }}
                            <span v-if="sanitizedSelectedMembers.length > 1">(@{{ sanitizedSelectedMembers.length }})</span>
                        </h2>

                        <div>
                            <div v-if="sanitizedSelectedMembers.length > 0" class="mb-6">
                                <div v-for="member in sanitizedSelectedMembers" :key="member.id" class="flex items-center justify-between mb-2">
                                    <input type="hidden" v-bind:name="'members[' + member.id + '][email]'" v-bind:value="member.email" />
                                    <div v-show="editMemberId === member.id" class="w-full">
                                        <div class="mb-6">
                                            <x-input-label :value="__('messages.name') . ' *'" />
                                            <div class="flex mt-1">
                                                <x-text-input v-bind:id="'edit_member_name_' + member.id" 
                                                    v-bind:name="'members[' + member.id + '][name]'" type="text" class="mr-2 block w-full"
                                                    v-model="selectedMembers.find(m => m.id === member.id).name" required autofocus
                                                    x-on:keydown.enter.prevent="editMember()" autocomplete="off" />
                                                <x-primary-button x-on:click="editMember()" type="button">
                                                    {{ __('messages.done') }}
                                                </x-primary-button>
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('member_name')" />
                                        </div>

                                        <div class="mb-6">  
                                            <x-input-label for="edit_member_email" :value="__('messages.email')" />
                                            <x-text-input v-bind:id="'edit_member_email_' + member.id" 
                                                v-bind:name="'members[' + member.id + '][email]'" type="email" class="mr-2 block w-full" 
                                                v-model="selectedMembers.find(m => m.id === member.id).email" x-on:keydown.enter.prevent="editMember()" autocomplete="off" />
                                        </div>

                                        <div class="mb-6">
                                            <x-input-label for="edit_member_youtube_url" :value="__('messages.youtube_video_url')" />
                                            <x-text-input v-bind:id="'edit_member_youtube_url_' + member.id" 
                                                v-bind:name="'members[' + member.id + '][youtube_url]'" type="url" class="mr-2 block w-full" 
                                                v-model="selectedMembers.find(m => m.id === member.id).youtube_url" x-on:keydown.enter.prevent="editMember()" autocomplete="off" />
                                        </div>

                                    </div>
                                    <div v-show="editMemberId !== member.id" class="flex justify-between w-full">
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
                                        <div>
                                            <x-secondary-button v-if="!member.user_id" x-on:click="editMember(member)" type="button" class="mr-2">
                                                {{ __('messages.edit') }}
                                            </x-secondary-button>
                                            <x-secondary-button x-on:click="removeMember(member)" type="button">
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
                                    <select v-model="selectedMember" x-on:change="addExistingMember" id="selected_member"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
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
                                            <x-text-input id="member_name" x-on:keydown.enter.prevent="addMember"
                                                v-model="memberName" type="text" class="mr-2 block w-full" required autocomplete="off" />
                                            <x-primary-button x-on:click="addMember" type="button">
                                                {{ __('messages.add') }}
                                            </x-primary-button>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="member_email" :value="__('messages.email')" />
                                        <div class="flex mt-1">
                                            <x-text-input id="member_email" name="member_email" type="email" class="mr-2 block w-full"
                                            x-on:keydown.enter.prevent="addMember" x-on:blur="searchMembers" v-model="memberEmail" autocomplete="off" />
                                        </div>
                                        @if (config('app.hosted'))
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ __('messages.an_email_will_be_sent') }}
                                        </p>
                                        @endif
                                        <x-input-error class="mt-2" :messages="$errors->get('member_email')" />
                                    </div>

                                    <div v-if="sanitizedMemberSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="member in sanitizedMemberSearchResults" :key="member.id" class="flex items-center justify-between">
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
                                                <x-primary-button x-on:click="selectMember(member)" type="button">
                                                    {{ __('messages.select') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="member_youtube_url" :value="__('messages.youtube_video_url')" />
                                        <x-text-input id="member_youtube_url" x-on:keydown.enter.prevent="addMember"
                                            v-model="memberYoutubeUrl" type="url" class="mr-2 block w-full" autocomplete="off" />
                                    </div>
                                
                                </div>
                                
                            </div>

                            <div v-if="!showMemberTypeRadio" class="mt-4 flex justify-end">
                                <x-secondary-button x-on:click="showAddMemberForm" type="button">
                                    {{ __('messages.add') }}
                                </x-secondary-button>
                            </div>
                        </div>
                    </div>
                </div>

                @if (! $role->isVenue() || $user->isMember($role->subdomain) || $user->canEditEvent($event))
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.event_details') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="event_name" :value="__('messages.event_name') . ' *'" />
                            <x-text-input id="event_name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $event->name)"
                                v-model="eventName"
                                required autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="event_slug" :value="__('messages.url')" />
                            <x-text-input id="event_slug"
                                name="slug"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="255"
                                v-model="eventSlug"
                                x-on:input="onSlugInput"
                                autocomplete="off" />
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <span class="truncate">
                                    <template v-if="eventUrlBase">
                                        <template v-if="canUseEventUrl">
                                            <a :href="fullEventUrl" target="_blank" class="hover:underline">
                                                @{{ displayEventUrl }}
                                            </a>
                                        </template>
                                        <template v-else>
                                            @{{ displayEventUrl }}
                                        </template>
                                    </template>
                                    <template v-else>
                                        @{{ eventSlug || slugPreviewPlaceholder }}
                                    </template>
                                </span>
                                <button v-if="canUseEventUrl" type="button" x-on:click="copyEventUrl"
                                    class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                    title="{{ __('messages.copy_url') }}">
                                    <template v-if="eventUrlCopied">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </template>
                                    <template v-else>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                        </svg>
                                    </template>
                                </button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>

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
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach(get_translated_categories() as $id => $label)
                                    <option value="{{ $id }}" {{ old('category_id', $event->category_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        @if (! $role->isCurator())
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
                        @endif

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
                            <div class="mt-4">
                                <x-media-picker
                                    name="flyer_media_variant_id"
                                    asset-input-name="flyer_media_asset_id"
                                    context="flyer"
                                    :initial-url="$event->flyer_image_url"
                                    label="{{ __('Choose from library') }}"
                                />
                            </div>
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

                        @php
                            $curators = $user->allCurators();
                            $curators = $curators->filter(function($curator) use ($subdomain) {
                                return $curator->subdomain !== $subdomain;
                            });
                        @endphp
                        
                        @if ($curators->count() > 0)
                        <div class="mb-6">
                            <x-input-label class="mb-2" for="curators" :value="__(count($curators) > 1 ? 'messages.add_to_schedules' : 'messages.add_to_schedule')" />
                            
                            @foreach($curators as $curator)
                            <div class="mb-4">
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" 
                                           id="curator_{{ $curator->encodeId() }}" 
                                           name="curators[]" 
                                           value="{{ $curator->encodeId() }}"
                                           {{ (! $event->exists && ($role->subdomain == $curator->subdomain || session('pending_request') == $curator->subdomain || (isset($preselectedCurators) && in_array($curator->encodeId(), $preselectedCurators)))) || $event->curators->contains($curator->id) ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                           x-on:change="toggleCuratorGroupSelection('{{ $curator->encodeId() }}')">
                                    <label for="curator_{{ $curator->encodeId() }}" class="ml-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $curator->name }}
                                    </label>
                                    @if($curator->accept_requests && $curator->request_terms)
                                    <div class="ml-2 relative group">
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
                                    @endif
                                </div>
                                
                                @if($curator->groups && count($curator->groups) > 0)
                                <div id="curator_group_{{ $curator->encodeId() }}" class="ml-6 mb-2" style="display: none;">
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
                        @endif

                    </div>
                </div>

                    @if ($event->user_id == $user->id)
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                        <div class="max-w-xl">                                                
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                                {{ __('messages.event_tickets') }}
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
                                            <a href="{{ route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan']) }}" class="hover:underline text-gray-600 dark:text-gray-400" target="_blank">
                                                {{ __('messages.requires_pro_plan') }}
                                            </a>
                                        </div>
                                        @endif
                                    </label>
                                </div>
                            </div>

                            @if ($role->isPro())
                            <div v-show="event.tickets_enabled">

                                @if ($user->stripe_completed_at || $user->invoiceninja_api_key || $user->payment_url)
                                <div class="mb-6">
                                    <x-input-label for="payment_method" :value="__('messages.payment_method')"/>
                                    <select id="payment_method" name="payment_method" v-model="event.payment_method" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="cash">Cash</option>
                                        @if ($user->stripe_completed_at)
                                        <option value="stripe">Stripe - {{ $user->stripe_company_name }}</option>
                                        @endif
                                        @if ($user->invoiceninja_api_key)
                                        <option value="invoiceninja">Invoice Ninja - {{ $user->invoiceninja_company_name }}</option>
                                        @endif
                                        @if ($user->payment_url)
                                        <option value="payment_url">{{ __('messages.payment_url') }} - {{ $user->paymentUrlHost() }}</option>
                                        @endif
                                    </select>
                                    <div class="text-xs pt-1">
                                        <a href="{{ route('profile.edit') }}" class="hover:underline text-gray-600 dark:text-gray-400" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <div class="mb-6">
                                    <x-input-label for="ticket_currency_code" :value="__('messages.currency')"/>
                                    <select id="ticket_currency_code" name="ticket_currency_code" v-model="event.ticket_currency_code" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach ($currencies as $currency)
                                        @if ($loop->index == 2)
                                        <option disabled>──────────</option>
                                        @endif
                                        @php
                                            $currencyValue = data_get($currency, 'value');
                                            $currencyLabel = data_get($currency, 'label', $currencyValue);
                                        @endphp
                                        @continue(empty($currencyValue))
                                        <option value="{{ $currencyValue }}" {{ $event->ticket_currency_code == $currencyValue ? 'selected' : '' }}>
                                            {{ $currencyValue }} - {{ $currencyLabel }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if (! $user->stripe_completed_at && ! $user->invoiceninja_api_key && ! $user->payment_url)
                                    <div class="text-xs pt-1">
                                        <a href="{{ route('profile.edit') }}" class="hover:underline text-gray-600 dark:text-gray-400" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </a>
                                    </div>
                                    @endif
                                </div>

                                <div class="mb-6" v-show="event.payment_method == 'cash'">
                                    <x-input-label for="payment_instructions" :value="__('messages.payment_instructions')" />
                                    <textarea id="payment_instructions" name="payment_instructions" v-model="event.payment_instructions" rows="4"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                </div>

                                <div class="mb-6">
                                    <div v-for="(ticket, index) in tickets" :key="index" 
                                        :class="{'mt-4 p-4 border rounded-lg': tickets.length > 1, 'mt-4': tickets.length === 1}">
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
                                                    class="mt-1 block w-full" required />
                                            </div>
                                            <div v-if="tickets.length > 1" class="flex items-end">
                                                <x-secondary-button x-on:click="removeTicket(index)" type="button" class="mt-1">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <x-input-label :value="__('messages.description')" />
                                            <textarea v-bind:name="`tickets[${index}][description]`" v-model="ticket.description" rows="4"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                        </div>
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

                                    <x-secondary-button x-on:click="addTicket" type="button" class="mt-4">
                                        {{ __('messages.add_type') }}
                                    </x-secondary-button>
                                </div>

                                <br/>

                                <div class="mb-6">
                                    <x-input-label for="ticket_notes" :value="__('messages.ticket_notes')" />
                                    <textarea id="ticket_notes" name="ticket_notes" v-model="event.ticket_notes" rows="4"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                </div>

                                <div v-if="hasLimitedPaidTickets">
                                    <div class="mb-6">
                                        <div class="flex items-center">
                                            <input id="expire_unpaid_tickets_checkbox" name="expire_unpaid_tickets_checkbox" type="checkbox" 
                                                v-model="showExpireUnpaid"
                                                class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                                x-on:change="toggleExpireUnpaid">
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

                                @if ($user->isMember($subdomain))
                                <div class="mb-6">
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
                @endif

            </div>
        </div>
    
        <div class="max-w-7xl mx-auto space-y-6 pt-4">
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

                <div>
                    @if ($event->exists)
                    <x-delete-button
                        :url="route('event.delete', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)])"
                       >
                    </x-delete-button>
                    @endif
                </div>
            </div>
        </div>

        @if ($event->exists && auth()->user()->google_token)
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <h3 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">
                {{ __('Google Calendar Sync') }}
            </h3>
            <p class="text-sm text-blue-700 dark:text-blue-300 mb-3">
                {{ __('Sync this event with your Google Calendar.') }}
            </p>
            
            <div class="flex items-center space-x-4">
                @if ($event->isSyncedToGoogleCalendar())
                    <div class="flex items-center text-green-600 dark:text-green-400">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">{{ __('Synced to Google Calendar') }}</span>
                    </div>
                    <button type="button" 
                            onclick="unsyncEvent({{ $event->id }})"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                        {{ __('Remove from Google Calendar') }}
                    </button>
                @else
                    <div class="flex items-center text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">{{ __('Not synced to Google Calendar') }}</span>
                    </div>
                    <button type="button" 
                            onclick="syncEvent({{ $event->id }})"
                            class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Sync to Google Calendar') }}
                    </button>
                @endif
            </div>
            
            <div id="sync-status-{{ $event->id }}" class="hidden mt-3">
                <div class="flex items-center text-blue-600 dark:text-blue-400">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">{{ __('Syncing...') }}</span>
                </div>
            </div>
        </div>
        @endif

    </form>
</div>

<script {!! nonce_attr() !!}>
  const { createApp } = Vue;

  const toIterableArray = rawCollection => {
    if (Array.isArray(rawCollection)) {
      return rawCollection;
    }

    if (rawCollection && typeof rawCollection === 'object') {
      return Object.values(rawCollection);
    }

    return [];
  };

  const hasNonEmptyId = item => {
    if (!item || typeof item !== 'object' || !('id' in item)) {
      return false;
    }

    const { id } = item;

    if (id === null || typeof id === 'undefined') {
      return false;
    }

    if (typeof id === 'string') {
      return id.trim().length > 0;
    }

    return true;
  };

  const sanitizeCollection = rawCollection => {
    const seen = new Set();

    return toIterableArray(rawCollection).reduce((carry, rawItem) => {
      if (!rawItem || typeof rawItem !== 'object') {
        return carry;
      }

      if (!hasNonEmptyId(rawItem)) {
        return carry;
      }

      const normalizedId = typeof rawItem.id === 'string'
        ? rawItem.id.trim()
        : rawItem.id;

      if (seen.has(normalizedId)) {
        return carry;
      }

      seen.add(normalizedId);

      if (normalizedId !== rawItem.id) {
        carry.push({
          ...rawItem,
          id: normalizedId,
        });

        return carry;
      }

      carry.push(rawItem);

      return carry;
    }, []);
  };

  if (typeof window !== 'undefined') {
    window.appReadyForTesting = false;
  }

  function hasBrowserTestingCookie() {
    if (typeof document === 'undefined' || !document.cookie) {
      return false;
    }

    return document.cookie.split(';').some(rawCookie => {
      const cookie = rawCookie.trim().toLowerCase();

      if (!cookie.startsWith('browser_testing=')) {
        return false;
      }

      const value = cookie.split('=')[1];

      return value === '1' || value === 'true';
    });
  }

  const vueApp = createApp({
    data() {
      return {
        event: {
          ...@json($event),
          tickets_enabled: {{ $event->tickets_enabled ? 'true' : 'false' }},
          total_tickets_mode: @json($event->total_tickets_mode ?? 'individual'),
        },
        venues: sanitizeCollection(@json($venues ?? [])),
        members: sanitizeCollection(@json($members ?? [])),
        shouldBypassPreferences: hasBrowserTestingCookie() || @json(is_browser_testing()),
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
        selectedMembers: sanitizeCollection(@json($selectedMembers ?? [])),
        memberSearchResults: [],
        selectedMember: "",
        editMemberId: "",
        memberEmail: "",
        memberName: "",
        memberYoutubeUrl: "",
        showMemberTypeRadio: true,
        showVenueAddressFields: false,
        isInPerson: @json($event->exists ? (bool) ($selectedVenue ?? false) : true),
        isOnline: @json($event->exists ? (bool) $event->event_url : false),
        eventName: @json($event->name ?? ''),
        eventSlug: @json($initialSlug),
        slugManuallyEdited: @json($slugWasManuallyEdited),
        slugPreviewPlaceholder: @json($slugExample),
        eventUrlBase: @json($baseEventUrl),
        eventUrlBaseAbsolute: @json($absoluteEventUrlBase),
        tickets: @json($event->tickets ?? [new Ticket()]).map(ticket => ({
          ...ticket,
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
        showExpireUnpaid: @json($event->expire_unpaid_tickets > 0),
        soldLabel: "{{ __('messages.sold_reserved') }}",
        isRecurring: @json($event->days_of_week ? true : false),
        eventUrlCopied: false,
      }
    },
    methods: {
      slugify(value) {
        if (!value) {
          return '';
        }

        const text = value.toString();
        const normalized = typeof text.normalize === 'function' ? text.normalize('NFD') : text;

        return normalized
          .replace(/[\u0300-\u036f]/g, '')
          .replace(/[^a-zA-Z0-9\s-]/g, '')
          .trim()
          .replace(/\s+/g, '-')
          .replace(/-+/g, '-')
          .toLowerCase();
      },
      updateSlugFromName(value) {
        if (this.slugManuallyEdited && this.eventSlug) {
          return;
        }

        this.eventSlug = this.slugify(value);
      },
      onSlugInput(event) {
        const sanitized = this.slugify(event.target.value);
        this.eventSlug = sanitized;
        this.slugManuallyEdited = sanitized.length > 0;
      },
      copyEventUrl() {
        if (!this.fullEventUrl) {
          return;
        }

        navigator.clipboard.writeText(this.fullEventUrl).then(() => {
          this.eventUrlCopied = true;

          setTimeout(() => {
            this.eventUrlCopied = false;
          }, 2000);
        });
      },
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
          this.venueSearchResults = sanitizeCollection(data);
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
      },
      selectVenue(venue) {
        if (!venue || typeof venue !== 'object') {
          return;
        }

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
          this.memberSearchResults = sanitizeCollection(data);
        })
        .catch(error => {
          console.error('Error searching members:', error);
        });
      },
      selectMember(member) {
        const [sanitizedMember] = sanitizeCollection([member]);
        if (!sanitizedMember) {
          return;
        }

        if (! this.selectedMembers.some(m => m && m.id === sanitizedMember.id)) {
          this.selectedMembers.push(sanitizedMember);
        }
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.showMemberTypeRadio = false;
      },
      removeMember(member) {
        if (!member || !member.id) {
          return;
        }

        this.selectedMembers = this.selectedMembers.filter(m => m && m.id !== member.id);
        if (this.sanitizedSelectedMembers.length === 0) {
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
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.showMemberTypeRadio = false;
      },
      addExistingMember() {
        const [sanitizedMember] = sanitizeCollection([this.selectedMember]);
        if (sanitizedMember && !this.selectedMembers.some(m => m && m.id === sanitizedMember.id)) {
          this.selectedMembers.push(sanitizedMember);
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
        this.memberSearchResults = [];
        this.memberName = "";
        this.memberEmail = "";
        this.memberYoutubeUrl = "";
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
      ensureOneChecked(type) {
        if (!this.isInPerson && !this.isOnline) {
            if (type === 'in_person') {
                this.isOnline = true;
            } else {
                this.isInPerson = true;
            }
        }

        // Clear venue if in-person is unchecked
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
        if (this.shouldBypassPreferences) {
          return;
        }

        try {
          localStorage.setItem('eventPreferences', JSON.stringify({
            isInPerson: this.isInPerson,
            isOnline: this.isOnline,
            ticketsEnabled: this.event.tickets_enabled
          }));
        } catch (error) {
          console.error('Error saving event preferences:', error);
        }
      },
      loadPreferences() {
        if (this.shouldBypassPreferences) {
          return null;
        }

        let preferences = null;

        try {
          preferences = JSON.parse(localStorage.getItem('eventPreferences'));
        } catch (error) {
          preferences = null;
        }

        if (!preferences || typeof preferences !== 'object') {
          return null;
        }

        @if (! $event->exists && $selectedVenue)
        this.isInPerson = true;
        this.isOnline = preferences.isOnline;
        @if ($role->isPro())
          this.event.tickets_enabled = preferences.ticketsEnabled ?? false;
        @else
          this.event.tickets_enabled = false;
        @endif
        @else
        this.isInPerson = preferences.isInPerson;
        this.isOnline = preferences.isOnline;
        @if ($role->isPro())
          this.event.tickets_enabled = preferences.ticketsEnabled ?? false;
        @else
          this.event.tickets_enabled = false;
        @endif
        @endif

        return preferences;
      },
      validateForm(event) {
        const sanitizedSlug = this.slugify(this.eventSlug);
        this.eventSlug = sanitizedSlug;
        this.event.slug = sanitizedSlug || null;

        if (! this.isFormValid) {
          event.preventDefault();
          alert("{{ __('messages.please_select_venue_or_participant') }}");
        }
      },
      addTicket() {
        this.tickets.push({
            id: null,
            type: '',
            quantity: null,
            price: null,
            description: '',
        });
      },
      removeTicket(index) {
        this.tickets.splice(index, 1);
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
    },
    computed: {
      availableVenues() {
        return sanitizeCollection(this.venues);
      },
      sanitizedVenueSearchResults() {
        return sanitizeCollection(this.venueSearchResults);
      },
      sanitizedMemberSearchResults() {
        return sanitizeCollection(this.memberSearchResults);
      },
      sanitizedSelectedMembers() {
        return sanitizeCollection(this.selectedMembers);
      },
      hasAnyVenues() {
        return this.availableVenues.length > 0;
      },
      shouldShowVenueForm() {
        if (this.shouldBypassPreferences) {
          return true;
        }

        if (this.hasAnyVenues) {
          return true;
        }

        return !this.selectedVenue || this.showVenueAddressFields;
      },
      shouldShowExistingVenueDropdown() {
        if (this.shouldBypassPreferences) {
          return true;
        }

        if (!this.hasAnyVenues) {
          return true;
        }

        return this.venueType === 'use_existing';
      },
      displayEventUrl() {
        const base = this.eventUrlBase ? this.eventUrlBase.replace(/\/$/, '') : '';
        const slug = this.eventSlug || this.slugPreviewPlaceholder;

        if (base) {
          return slug ? `${base}/${slug}` : base;
        }

        return slug;
      },
      fullEventUrl() {
        const base = this.eventUrlBaseAbsolute ? this.eventUrlBaseAbsolute.replace(/\/$/, '') : '';
        const slug = this.eventSlug;

        if (base && slug) {
          return `${base}/${slug}`;
        }

        return '';
      },
      canUseEventUrl() {
        return !!this.event.id && !!this.fullEventUrl;
      },
      filteredMembers() {
        const selectedIds = new Set(
          this.sanitizedSelectedMembers.map(member => member.id)
        );

        return sanitizeCollection(this.members)
          .filter(member => !selectedIds.has(member.id));
      },
      isFormValid() {
        var hasSubdomain = this.venueName || this.sanitizedSelectedMembers.length > 0;
        var hasVenue = this.venueAddress1 || this.event.event_url;

        return hasSubdomain && hasVenue;
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
      eventName(newValue) {
        if (!this.event.id && !this.slugManuallyEdited) {
          this.updateSlugFromName(newValue);
        }
      },
      eventSlug(newValue) {
        if (!this.event.id && !newValue) {
          this.slugManuallyEdited = false;
          this.updateSlugFromName(this.eventName);
        }

        this.event.slug = newValue || null;
      },
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
        handler(newValue) {
          const validMembers = Array.isArray(newValue)
            ? newValue.filter(member => member && typeof member === 'object' && member.name)
            : [];

          if (!this.eventName && validMembers.length === 1) {
            this.eventName = validMembers[0].name;
          }
        },
        deep: true
      },
      'event.tickets_enabled'(newValue) {
        this.savePreferences();
      },
    },
    mounted() {
      if (!this.shouldBypassPreferences && hasBrowserTestingCookie()) {
        this.shouldBypassPreferences = true;
      }

      this.venues = sanitizeCollection(this.venues);
      this.members = sanitizeCollection(this.members);
      this.selectedMembers = sanitizeCollection(this.selectedMembers);

      this.showMemberTypeRadio = this.sanitizedSelectedMembers.length === 0;

      if (this.event.id) {
        this.isInPerson = !!this.event.venue || !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
      } else {
        const preferences = this.loadPreferences();

        if (this.shouldBypassPreferences) {
          this.isInPerson = true;
          this.isOnline = false;
          if (this.hasAnyVenues) {
            this.venueType = 'use_existing';
          }
        } else if (!this.isInPerson && !this.isOnline) {
          this.isInPerson = true;
        }
      }

      if (this.event.id) {
        this.eventName = this.event.name;
      } else {
        const initialMembers = this.sanitizedSelectedMembers;

        if (initialMembers.length === 1) {
          this.eventName = initialMembers[0].name;
        }
      }

      // Initialize curator group selections
      this.initializeCuratorGroupSelections();

      if (!this.event.id) {
        this.updateSlugFromName(this.eventName);
      }

      this.event.slug = this.eventSlug || null;

      if (typeof window !== 'undefined') {
        window.appReadyForTesting = true;
      }
    }
  })

  let appInstance;

  if (typeof window !== 'undefined') {
    window.appBootstrapError = null;
  }

  try {
    appInstance = vueApp.mount('#app');
  } catch (error) {
    if (typeof window !== 'undefined') {
      const details = error instanceof Error ? (error.stack || error.message) : String(error);

      window.appBootstrapError = details;
      window.appReadyForTesting = true;
    }

    throw error;
  }

  app = appInstance;

  if (typeof window !== 'undefined') {
    window.app = appInstance;

    const markAppReady = () => {
      window.appReadyForTesting = true;
    };

    markAppReady();

    window.setTimeout(() => {
      if (!window.appReadyForTesting) {
        markAppReady();
      }
    }, 0);
  }

  // Google Calendar sync functions
  function syncEvent(eventId) {
    const statusDiv = document.getElementById(`sync-status-${eventId}`);
    statusDiv.classList.remove('hidden');
    
    fetch(`/google-calendar/sync-event/${eventId}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => response.json())
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

  function unsyncEvent(eventId) {
    if (!confirm('Are you sure you want to remove this event from Google Calendar?')) {
      return;
    }
    
    const statusDiv = document.getElementById(`sync-status-${eventId}`);
    statusDiv.classList.remove('hidden');
    
    fetch(`/google-calendar/unsync-event/${eventId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => response.json())
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
</script>

</x-app-admin-layout>