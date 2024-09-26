<x-app-admin-layout>

@vite([
    'resources/js/countrySelect.min.js',
    'resources/css/countrySelect.min.css',
])

<x-slot name="head">
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        var f = flatpickr('.datepicker', {
            allowInput: true,
            enableTime: true,
            altInput: true,
            time_24hr: "{{ $venue && $venue->use_24_hour_time ? 'true' : 'false' }}",
            altFormat: "{{ $venue && $venue->use_24_hour_time ? 'M j, Y • H:i' : 'M j, Y • h:i K' }}",
            dateFormat: "Y-m-d H:i:S",
        });
        // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
        f._input.onkeydown = () => false;

        $("#venue_country").countrySelect({
            defaultCountry: "{{ $venue ? $venue->country_code : '' }}",
        });
    });

    function onChangeCountry() {
        var selected = $('#venue_country').countrySelect('getSelectedCountryData');
        $('#venue_country_code').val(selected.iso2);
    }

    function onChangeDateType() {
        var value = $('input[name="schedule_type"]:checked').val();
        if (value == 'one_time') {
            $('#days_of_week_div').hide();
        } else {
            $('#days_of_week_div').show();
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
                    var address = response['formatted_address'];
                    $('#address_response').text(address);
                    $('#accept_button').show();
                    $('#address_response').data('validated_address', response);
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
            $("#venue_country").countrySelect("selectCountry", validatedAddress['country_code']);
            
            // Update hidden fields
            $('#formatted_address').val(validatedAddress['formatted_address']);
            $('#google_place_id').val(validatedAddress['google_place_id']);
            $('#geo_address').val(validatedAddress['geo_address']);
            $('#geo_lat').val(validatedAddress['geo_lat']);
            $('#geo_lon').val(validatedAddress['geo_lon']);
            
            // Hide the address response and accept button after accepting
            $('#address_response').hide();
            $('#accept_button').hide();
        }
    }

    function previewImage(input) {
        var preview = document.getElementById('preview_img');
        var previewDiv = document.getElementById('image_preview');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewDiv.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '#';
            previewDiv.style.display = 'none';
        }
    }

    </script>

</x-slot>

<div id="app">
  <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
    {{ $title }}
  </h2>

  <form method="POST"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain]) }}"
        enctype="multipart/form-data">

        @csrf

        @if ($event->exists)
        @method('put')
        @endif

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.venue') }}
                        </h2>

                        <div v-if="!selectedVenue">
                            <fieldset>                                
                                <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                    <div v-if="Object.keys(venues).length > 0" class="flex items-center">
                                        <input id="use_existing_venue" name="venue_type" type="radio" value="use_existing" v-model="venueType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="use_existing_venue"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="search_create_venue" name="venue_type" type="radio" value="search_create" v-model="venueType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="search_create_venue"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.search_create') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="private_address_venue" name="venue_type" type="radio" value="private_address" v-model="venueType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="private_address_venue"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.private_address') }}</label>
                                    </div>
                                </div>
                            </fieldset>

                            <div v-if="venueType === 'use_existing'">
                                <select name="venue_id"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        v-model="event.venue_id">
                                        <option value="" disabled selected>{{ __('messages.please_select') }}</option>                                
                                        <option v-for="venue in venues" :key="venue.id" :value="venue.id">@{{ venue.name }}</option>
                                </select>
                            </div>

                            <div v-if="venueType === 'search_create'">

                                <div v-if="!venueEmail" class="mb-6">
                                    <x-input-label for="venue_search_email" :value="__('messages.email')" />
                                    <div class="flex mt-1">
                                        <x-text-input id="venue_search_email" v-model="venueSearchEmail" type="email" class="block w-full mr-2"
                                            :placeholder="''" required autofocus @keydown.enter.prevent="searchVenues" />
                                        <x-primary-button @click="searchVenues" type="button">
                                            {{ __('messages.search') }}
                                        </x-primary-button>
                                    </div>
                                </div>

                                <div v-if="venueSearchResults.length" class="mb-6">
                                    <x-input-label :value="__('messages.search_results')" />
                                    <div class="mt-2 space-y-2">
                                        <div v-for="venue in venueSearchResults" :key="venue.id" class="flex items-center">
                                            <input :id="'venue_' + venue.id" type="radio" :value="venue.id" v-model="event.venue_id"
                                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                            <label :for="'venue_' + venue.id" class="ml-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                @{{ venue.name }} (@{{ venue.email }})
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="selectedVenue" class="mb-6">
                                    <x-input-label :value="__('messages.selected_venue')" />
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @{{ selectedVenue.name }} (@{{ selectedVenue.email }})
                                    </p>
                                </div>

                                <div v-if="venueEmail && !event.id">
                                    <div class="mb-6">
                                        <x-input-label for="venue_email" :value="__('messages.email') . ' *'" />
                                        <div class="flex mt-1">
                                            <x-text-input id="venue_email" name="venue_email" type="email" class="mr-2 block w-full"
                                                v-model="venueEmail" required disabled />
                                            <x-primary-button @click="clearVenueSearch" type="button">
                                                {{ __('messages.clear') }}
                                            </x-primary-button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ __('messages.an_email_will_be_sent') }}
                                        </p>
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_name" :value="__('messages.name') . ' *'" />
                                        <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full"
                                            v-model="venueName" required autofocus />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                                    </div>
                                </div>
                            </div>

                            <div v-if="showAddressFields()">
                                <div class="mb-6">
                                    <x-input-label for="venue_address1" :value="__('messages.street_address') . ' *'" />
                                    <x-text-input id="venue_address1" name="venue_address1" type="text"
                                        class="mt-1 block w-full" required
                                        :value="old('venue_address1', $venue ? $venue->address1 : '')" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('venue_address1')" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="venue_city" :value="__('messages.city')" />
                                    <x-text-input id="venue_city" name="venue_city" type="text" class="mt-1 block w-full"
                                        :value="old('venue_city', $venue ? $venue->city : '')" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('venue_city')" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="venue_state" :value="__('messages.state_province')" />
                                    <x-text-input id="venue_state" name="venue_state" type="text" class="mt-1 block w-full"
                                        :value="old('venue_state', $venue ? $venue->state : '')" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('venue_state')" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="venue_postal_code" :value="__('messages.postal_code')" />
                                    <x-text-input id="venue_postal_code" name="venue_postal_code" type="text"
                                        class="mt-1 block w-full"
                                        :value="old('venue_postal_code', $venue ? $venue->postal_code : '')" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('venue_postal_code')" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="venue_country" :value="__('messages.country')" />
                                    <x-text-input id="venue_country" name="venue_country" type="text" class="mt-1 block w-full"
                                        :value="old('venue_country')" onchange="onChangeCountry()" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                                    <input type="hidden" id="venue_country_code" name="venue_country_code" />
                                </div>

                                <div class="mb-6">
                                    <div class="flex items-center space-x-4">
                                        <x-secondary-button id="view_map_button" onclick="viewMap()">{{ __('messages.view_map') }}</x-secondary-button>
                                        <x-secondary-button id="validate_button" onclick="onValidateClick()">{{ __('messages.validate_address') }}</x-secondary-button>
                                        <x-secondary-button id="accept_button" onclick="acceptAddress(event)" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
                                    </div>
                                </div>

                                <div id="address_response" class="mb-6 hidden text-gray-900 dark:text-gray-100"></div>

                            </div>
                        </div>

                        <div v-else>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.selected_venue') }}: @{{ selectedVenue.name }}
                            </p>
                            <button @click="clearSelectedVenue" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">
                                {{ __('messages.change_venue') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.members') }}
                        </h2>

                        <div>
                            <div v-if="selectedMembers && selectedMembers.length > 0" class="mb-6">
                                <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.selected_members') }}</h3>
                                <div v-for="member in selectedMembers" :key="member.id" class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-900 dark:text-gray-100">@{{ member.name }} (@{{ member.email || __('messages.no_contact_info') }})</span>
                                    <x-secondary-button @click="removeMember(member)" type="button">
                                        {{ __('messages.remove') }}
                                    </x-secondary-button>
                                </div>
                            </div>

                            <fieldset>                                
                                <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                    <div v-if="Object.keys(members).length > 0" class="flex items-center">
                                        <input id="use_existing_members" name="member_type" type="radio" value="use_existing" v-model="memberType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="use_existing_members"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="search_create_members" name="member_type" type="radio" value="search_create" v-model="memberType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="search_create_members"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.search_create') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="no_contact_info_members" name="member_type" type="radio" value="no_contact_info" v-model="memberType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="no_contact_info_members"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.no_contact_info') }}</label>
                                    </div>
                                </div>
                            </fieldset>

                            <div v-if="memberType === 'use_existing'">
                                <select v-model="selectedExistingMember" @change="addExistingMember" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="" disabled selected>{{ __('messages.select_member') }}</option>
                                    <option v-for="member in members" :key="member.id" :value="member">@{{ member.name }}</option>
                                </select>
                            </div>

                            <div v-if="memberType === 'search_create'">
                                <div class="mb-6">
                                    <x-input-label for="member_search_email" :value="__('messages.search_members')" />
                                    <div class="flex mt-1">
                                        <x-text-input id="member_search_email" v-model="memberSearchEmail" type="email" class="block w-full mr-2"
                                            :placeholder="__('messages.enter_email')" @keydown.enter.prevent="searchMembers" />
                                        <x-primary-button @click="searchMembers" type="button">
                                            {{ __('messages.search') }}
                                        </x-primary-button>
                                    </div>
                                </div>

                                <div v-if="memberSearchResults.length" class="mb-6">
                                    <x-input-label :value="__('messages.search_results')" />
                                    <div class="mt-2 space-y-2">
                                        <div v-for="member in memberSearchResults" :key="member.id" class="flex items-center justify-between">
                                            <span class="text-sm text-gray-900 dark:text-gray-100">@{{ member.name }} (@{{ member.email }})</span>
                                            <x-secondary-button @click="addMember(member)" type="button">
                                                {{ __('messages.add') }}
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="showNewMemberForm" class="mb-6">
                                    <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.add_new_member') }}</h3>
                                    <div class="mb-4">
                                        <x-input-label for="new_member_email" :value="__('messages.email') . ' *'" />
                                        <x-text-input id="new_member_email" v-model="newMemberEmail" type="email" class="mt-1 block w-full" required />
                                    </div>
                                    <div class="mb-4">
                                        <x-input-label for="new_member_name" :value="__('messages.name') . ' *'" />
                                        <x-text-input id="new_member_name" v-model="newMemberName" type="text" class="mt-1 block w-full" required />
                                    </div>
                                    <div class="flex justify-end">
                                        <x-primary-button @click="addNewMember" type="button">
                                            {{ __('messages.add_member') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="memberType === 'no_contact_info'">
                                <div class="mb-4">
                                    <x-input-label for="no_contact_member_name" :value="__('messages.name') . ' *'" />
                                    <x-text-input id="no_contact_member_name" v-model="noContactMemberName" type="text" class="mt-1 block w-full" required />
                                </div>
                                <div class="flex justify-end">
                                    <x-primary-button @click="addNoContactMember" type="button">
                                        {{ __('messages.add_member') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.details') }}
                        </h2>

                        @if (! $role->isCurator())
                        <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                            <div class="flex items-center">
                                <input id="one_time" name="schedule_type" type="radio" value="one_time" onchange="onChangeDateType()" {{ $event->days_of_week ? '' : 'CHECKED' }}
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="one_time"
                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.one_time') }}</label>
                            </div>
                            <div class="flex items-center">
                                <input id="recurring" name="schedule_type" type="radio" value="recurring" onchange="onChangeDateType()"  {{ $event->days_of_week ? 'CHECKED' : '' }}
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="recurring"
                                    class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.recurring') }}</label>
                            </div>
                        </div>

                        <div id="days_of_week_div" class="mb-6 {{ ! $event || ! $event->days_of_week ? 'hidden' : '' }}">
                            <x-input-label for="duration" :value="__('messages.days_of_week')" />
                            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $index => $day)
                            <label for="days_of_week_{{ $index }}" class="mr-3 text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">
                                <input type="checkbox" id="days_of_week_{{ $index }}" name="days_of_week_{{ $index }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                    {{ $event && $event->days_of_week && $event->days_of_week[$index] == '1' ? 'checked' : '' }}/> &nbsp;
                                {{ __('messages.' . $day) }}
                            </label>
                            @endforeach
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="starts_at"
                                :value="__('messages.date_and_time') . ($venue && $venue->user_id ? '' : ' *')" />
                            <x-text-input type="text" id="starts_at" name="starts_at" class="datepicker"
                                :value="old('starts_at', $event->localStartsAt())"
                                :required="! $venue || ! $venue->user_id" />
                            <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="duration" :value="__('messages.duration_in_hours')" />
                            <x-text-input type="number" id="duration" name="duration"
                                :value="old('duration', $event->duration)" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration')" />
                        </div>
                        
                        @php
                            $curators = $user->editableCurators();
                        @endphp
                        
                        @if($curators->count() > 0 && $role->isTalent())
                        <div class="mb-6">
                            <x-input-label for="curators" :value="__('messages.add_to_schedules')" />
                            @foreach($curators as $curator)
                            <div class="flex items-center mb-4 mt-1">
                                <input type="checkbox" 
                                       id="curator_{{ $curator->id }}" 
                                       name="curators[]" 
                                       value="{{ $curator->id }}"
                                       {{ $event->curators->contains($curator->id) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="curator_{{ $curator->id }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    {{ $curator->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div class="mb-6">
                        <x-input-label for="flyer_image" :value="__('messages.flyer_image')" />
                        <input id="flyer_image" name="flyer_image" type="file" class="mt-1 block w-full" 
                                accept="image/png, image/jpeg" onchange="previewImage(this);" />
                            <x-input-error class="mt-2" :messages="$errors->get('flyer_image')" />

                            <div id="image_preview" class="mt-3" style="display: none;">
                                <img id="preview_img" src="#" alt="Preview" style="max-height:120px" />
                            </div>

                            @if ($event->flyer_image_url)
                            <img src="{{ $event->flyer_image_url }}" style="max-height:120px" class="pt-3" />
                            <a href="#"
                                onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('event.delete_image', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id), 'image_type' => 'flyer']) }}'; }"
                                class="hover:underline">
                                {{ __('messages.delete_image') }}
                            </a>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.description')" />
                            <textarea id="description" name="description"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $event->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                    </div>
                </div>

                
            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>                    
                    <x-cancel-button></x-cancel-button>
                </div>

                <div>
                    @if ($event->exists)
                    <x-delete-button
                        :url="route('event.delete', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)])">
                    </x-delete-button>
                    @endif
                </div>
            </div>
        </div>

        <template v-for="member in selectedMembers">
            <input type="hidden" name="members[]" :value="member.id">
        </template>

    </form>
</div>

<script>
  const { createApp, ref } = Vue

  createApp({
    data() {
      return {
        event: @json($event),
        venues: @json($venues),
        members: @json($members ?? []),
        venueType: "private_address",
        memberType: 'search_create',
        venueName: "{{ old('venue_name', $venue ? $venue->name : '') }}",
        venueEmail: "{{ old('venue_email', $venue ? $venue->email : request()->venue_email) }}",
        venueSearchEmail: "",
        venueSearchResults: [],
        selectedMembers: @json($event->members ?? []),
        memberSearchEmail: "",
        memberSearchResults: [],
        showNewMemberForm: false,
        newMemberEmail: "",
        newMemberName: "",
        selectedExistingMember: null,
        noContactMemberName: "",
      }
    },
    methods: {
      clearSelectedVenue() {
        this.event.venue_id = "";
      },
      searchVenues() {
        const emailInput = document.getElementById('venue_search_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        fetch(`/search_roles?type=venue&search=${encodeURIComponent(this.venueSearchEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          console.log(data);
          this.venueSearchResults = data;

          if (data.length === 0) {
            this.venueEmail = this.venueSearchEmail;
            this.$nextTick(() => {
              const venueNameInput = document.getElementById('venue_name');
              if (venueNameInput) {
                venueNameInput.focus();
              }
            });
          }
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
      },
      clearVenueSearch() {
        this.venueEmail = "";
        this.venueName = "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
        
        this.$nextTick(() => {
          const emailInput = document.getElementById('venue_search_email');
          if (emailInput) {
            emailInput.focus();
          }
        });
      },
      setFocusBasedOnVenueType() {
        this.$nextTick(() => {
          if (this.venueType === 'use_existing') {
            const venueSelect = document.querySelector('select[name="venue_id"]');
            if (venueSelect) {
              venueSelect.focus();
            }
          } else if (this.venueType === 'search_create') {
            const searchInput = document.getElementById('venue_search_email');
            if (searchInput) {
              searchInput.focus();
            }
          }
        });
      },
      showAddressFields() {
        return (this.venueType === 'use_existing' && this.selectedVenue && !this.selectedVenue.user_id) 
            || this.venueType === 'private_address' 
            || (this.venueType === 'search_create' && this.venueEmail);
      },
      searchMembers() {
        if (!this.memberSearchEmail) return;

        fetch(`/search_roles?type=member&search=${encodeURIComponent(this.memberSearchEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          this.memberSearchResults = data;
          if (data.length === 0) {
            this.showNewMemberForm = true;
            this.newMemberEmail = this.memberSearchEmail;
          } else {
            this.showNewMemberForm = false;
          }
        })
        .catch(error => {
          console.error('Error searching members:', error);
        });
      },
      addMember(member) {
        if (!this.selectedMembers.some(m => m.id === member.id)) {
          this.selectedMembers.push(member);
        }
        this.memberSearchResults = this.memberSearchResults.filter(m => m.id !== member.id);
      },
      removeMember(member) {
        this.selectedMembers = this.selectedMembers.filter(m => m.id !== member.id);
      },
      addNewMember() {
        if (!this.newMemberEmail || !this.newMemberName) return;

        const newMember = {
          id: `new_${Date.now()}`,
          name: this.newMemberName,
          email: this.newMemberEmail
        };
        this.selectedMembers.push(newMember);
        this.showNewMemberForm = false;
        this.newMemberEmail = "";
        this.newMemberName = "";
        this.memberSearchEmail = "";
      },
      addExistingMember() {
        if (this.selectedExistingMember && !this.selectedMembers.some(m => m.id === this.selectedExistingMember.id)) {
          this.selectedMembers.push(this.selectedExistingMember);
          this.$nextTick(() => {
            this.selectedExistingMember = null;
          });
        }
      },
      addNoContactMember() {
        if (!this.noContactMemberName) return;

        const newMember = {
          id: `no_contact_${Date.now()}`,
          name: this.noContactMemberName,
          email: null
        };
        this.selectedMembers.push(newMember);
        this.noContactMemberName = "";
      },
    },
    computed: {
      selectedVenue() {
        return this.venues[this.event.venue_id];
      }
    },
    watch: {
      venueType() {
        this.event.venue_id = "";
        this.venueEmail = "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
        this.setFocusBasedOnVenueType();
      }
    },
    mounted() {
        this.setFocusBasedOnVenueType();
    }
  }).mount('#app')
</script>

</x-app-admin-layout>