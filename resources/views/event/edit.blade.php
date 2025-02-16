<x-app-admin-layout>

@vite([
    'resources/js/countrySelect.min.js',
    'resources/css/countrySelect.min.css',
])

<x-slot name="head">
  <style>
    button {
      min-width: 100px;
      min-height: 40px;
    }
  </style>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        var f = flatpickr('.datepicker', {
            allowInput: true,
            enableTime: true,
            altInput: true,
            time_24hr: "{{ $role && $role->use_24_hour_time ? 'true' : 'false' }}",
            altFormat: "{{ $role && $role->use_24_hour_time ? 'M j, Y • H:i' : 'M j, Y • h:i K' }}",
            dateFormat: "Y-m-d H:i:S",
        });
        // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
        f._input.onkeydown = () => false;

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

  <h2 class="pt-2 my-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
    {{ $title }}
  </h2>

  <form method="POST"
        @submit="validateForm"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain]) }}"
        enctype="multipart/form-data">

        @csrf

        @if ($event->exists)
        @method('put')
        @endif

        <x-text-input name="venue_name" type="hidden" v-model="venueName" />                                                                
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
                                            @change="ensureOneChecked('in_person')">
                                        <label for="in_person" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.in_person') }}
                                        </label>
                                    </div>
                                    <div class="flex items-center pl-3">
                                        <input id="online" name="event_type" type="checkbox" v-model="isOnline"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            @change="ensureOneChecked('online')">
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
                                        <select required id="selected_venue"
                                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
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
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ __('messages.an_email_will_be_sent') }}
                                        </p>
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                                    </div>

                                    <div v-if="venueSearchResults.length" class="mb-6">
                                        <div class="mt-2 space-y-2">
                                            <div v-for="venue in venueSearchResults" :key="venue.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">
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
                                            <x-secondary-button id="validate_button" onclick="onValidateClick()">{{ __('messages.validate_address') }}</x-secondary-button>
                                            <x-secondary-button id="accept_button" onclick="acceptAddress(event)" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
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
                            {{ __('messages.event_participants') }}
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
                                                    v-model="selectedMembers.find(m => m.id === member.id).name" required autofocus
                                                    @keydown.enter.prevent="editMember()" autocomplete="off" />
                                                <x-primary-button @click="editMember()" type="button">
                                                    {{ __('messages.done') }}
                                                </x-primary-button>
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('member_name')" />
                                        </div>

                                        <div class="mb-6">
                                            <x-input-label for="edit_member_youtube_url" :value="__('messages.youtube_video_url')" />
                                            <x-text-input v-bind:id="'edit_member_youtube_url_' + member.id" 
                                                v-bind:name="'members[' + member.id + '][youtube_url]'" type="url" class="mr-2 block w-full" 
                                                v-model="selectedMembers.find(m => m.id === member.id).youtube_url" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                        </div>

                                    </div>
                                    <div v-show="editMemberId !== member.id" class="flex justify-between w-full">
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-900 dark:text-gray-100">
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
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                        <option v-for="member in filteredMembers" :key="member.id" :value="member">
                                            @{{ member.name }} <template v-if="member.email">(@{{ member.email }})</template>
                                        </option>
                                    </select>
                                </div>

                                <!--
                                <div v-if="memberType === 'search_create'">
                                    <div v-if="!memberEmail" class="mb-6">
                                        <x-input-label for="member_search_email" :value="__('messages.email') . ' *'" />
                                        <div class="flex mt-1">
                                            <x-text-input id="member_search_email" v-model="memberSearchEmail" type="email" class="block w-full mr-2"
                                                :placeholder="''" required autofocus @keydown.enter.prevent="searchMembers" autocomplete="off" />
                                            <x-primary-button @click="searchMembers" type="button">
                                                {{ __('messages.search') }}
                                            </x-primary-button>
                                        </div>
                                    </div>

                                    <div v-if="memberSearchResults.length" class="mb-6">
                                        <x-input-label :value="__('messages.search_results')" />
                                        <div class="mt-2 space-y-2">
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
                                                <x-primary-button @click="addMember(member)" type="button">
                                                    {{ __('messages.add') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="memberEmail" class="mb-6">
                                        <x-input-label for="member_email" :value="__('messages.email') . ' *'" />
                                        <div class="flex mt-1">
                                            <x-text-input id="member_email" name="member_email" type="email" class="mr-2 block w-full"
                                                v-model="memberEmail" required readonly autocomplete="off" />
                                            <x-secondary-button @click="clearMemberSearch" type="button">
                                                {{ __('messages.clear') }}
                                            </x-secondary-button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            {{ __('messages.an_email_will_be_sent') }}
                                        </p>
                                        <x-input-error class="mt-2" :messages="$errors->get('member_email')" />
                                    </div>

                                    <div v-if="memberEmail">
                                        <div class="mb-6">
                                            <x-input-label for="member_name" :value="__('messages.name') . ' *'" />
                                            <div class="flex mt-1">
                                                <x-text-input id="member_name" name="member_name" type="text" class="mr-2 block w-full"
                                                    v-model="memberName" required autofocus @keydown.enter.prevent="addNewMember" autocomplete="off" />
                                                <x-primary-button @click="addNewMember" type="button">
                                                    {{ __('messages.add') }}
                                                </x-primary-button>
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('member_name')" />
                                        </div>

                                        <div class="mb-6">
                                            <x-input-label for="member_youtube_url" :value="__('messages.youtube_video_url')" />
                                            <x-text-input id="member_youtube_url"
                                                v-model="memberYoutubeUrl" type="url" class="mr-2 block w-full" 
                                                @keydown.enter.prevent="addNewMember" autocomplete="off" />
                                        </div>

                                    </div>
                                </div>
                                -->

                                <div v-if="memberType === 'create_new'"> 
                                    <div class="mb-6">
                                        <x-input-label for="no_contact_member_name" :value="__('messages.name') . ' *'" />
                                        <div class="flex mt-1">
                                            <x-text-input id="no_contact_member_name" @keydown.enter.prevent="addNoContactMember"
                                                v-model="memberName" type="text" class="mr-2 block w-full" required autocomplete="off" />
                                            <x-primary-button @click="addNoContactMember" type="button">
                                                {{ __('messages.add') }}
                                            </x-primary-button>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="no_contact_member_youtube_url" :value="__('messages.youtube_video_url')" />
                                        <x-text-input id="no_contact_member_youtube_url" @keydown.enter.prevent="addNoContactMember"
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
                            @if ($event->exists)
                            <p class="text-sm text-gray-500 flex items-center gap-2">
                                <a href="{{ $event->getGuestUrl($subdomain, $isUnique ? false : null) }}" target="_blank" class="hover:underline">
                                    {{ \App\Utils\UrlUtils::clean($event->getGuestUrl($subdomain, $isUnique ? false : null)) }}
                                </a>
                                <button type="button" onclick="copyEventUrl(this)" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                    </svg>
                                </button>
                            </p>
                            @endif
                        </div>

                        <!--
                        <div class="mb-6">
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
                                    placeholder="{{ __('messages.auto_generated') }}"
                                    autocomplete="off" />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                        </div>
                        -->

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
                        <x-input-label for="flyer_image_url" :value="__('messages.flyer_image')" />
                        <input id="flyer_image_url" name="flyer_image_url" type="file" class="mt-1 block w-full text-gray-900 dark:text-gray-100" 
                                accept="image/png, image/jpeg" onchange="previewImage(this);" />
                            <x-input-error class="mt-2" :messages="$errors->get('flyer_image_url')" />
                            <p id="image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                {{ __('messages.image_size_warning') }}
                            </p>

                            <div id="image_preview" class="mt-3" style="display: none;">
                                <img id="preview_img" src="#" alt="Preview" style="max-height:120px" />
                            </div>

                            @if ($event->flyer_image_url)
                            <img src="{{ $event->flyer_image_url }}" style="max-height:120px" class="pt-3" />
                            <a href="#"
                                onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('event.delete_image', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id), 'image_type' => 'flyer']) }}'; }"
                                class="hover:underline text-gray-900 dark:text-gray-100">
                                {{ __('messages.delete_image') }}
                            </a>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.description')" />
                            <textarea id="description" name="description"
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                autocomplete="off">{{ old('description', $event->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        @php
                            $curators = $user->editableCurators();
                        @endphp
                        
                        @if ($curators->count() > 0)
                        <div class="mb-6">
                            <x-input-label for="curators" :value="__(count($curators) > 1 ? 'messages.add_to_schedules' : 'messages.add_to_schedule')" />
                            @foreach($curators as $curator)
                            <div class="flex items-center mb-4 mt-1">
                                <input type="checkbox" 
                                       id="curator_{{ $curator->encodeId() }}" 
                                       name="curators[]" 
                                       value="{{ $curator->encodeId() }}"
                                       {{ (! $event->exists && $role->subdomain == $curator->subdomain) || $event->curators->contains($curator->id) ? 'checked' : '' }}
                                       class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                <label for="curator_{{ $curator->encodeId() }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                                    {{ $curator->name }}
                                </label>
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

                                @if ($user->stripe_completed_at || $user->invoiceninja_api_key)
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
                                        <option value="{{ $currency->value }}" {{ $event->ticket_currency_code == $currency->value ? 'selected' : '' }}>
                                            {{ $currency->value }} - {{ $currency->label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if (! $user->stripe_completed_at && ! $user->invoiceninja_api_key)
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
                                                <x-secondary-button @click="removeTicket(index)" type="button" class="mt-1">
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

                                    <x-secondary-button @click="addTicket" type="button" class="mt-4">
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
    
        <div class="max-w-7xl mx-auto space-y-6 pt-8">
            @if (! $event->exists)
            <p class="text-base dark:text-gray-400 text-gray-600 pb-2">
                {{ __('messages.note_all_events_are_publicly_listed') }}
            </p>
            @endif

            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>                    
                    <x-cancel-button></x-cancel-button>
                </div>

                <div>
                    @if ($event->exists)
                    <x-delete-button
                        :url="route('event.delete', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)])"
                       >
                    </x-delete-button>
                    @endif
                </div>
            </div>
        </div>

    </form>
</div>

<script>
  const { createApp, ref } = Vue

  app = createApp({
    data() {
      return {
        event: {
          ...@json($event),
          tickets_enabled: {{ $event->tickets_enabled ? 'true' : 'false' }},
        },
        venues: @json($venues),
        members: @json($members ?? []),
        venueType: "{{ count($venues) > 0 ? 'use_existing' : 'create_new' }}",
        memberType: "{{ 'use_existing' }}",
        venueName: "{{ $selectedVenue ? $selectedVenue->name : '' }}",
        venueEmail: "{{ $selectedVenue ? $selectedVenue->email : '' }}",
        venueAddress1: "{{ $selectedVenue ? $selectedVenue->address1 : '' }}",
        venueCity: "{{ $selectedVenue ? $selectedVenue->city : '' }}",
        venueState: "{{ $selectedVenue ? $selectedVenue->state : '' }}",
        venuePostalCode: "{{ $selectedVenue ? $selectedVenue->postal_code : '' }}",
        venueCountryCode: "{{ $selectedVenue ? $selectedVenue->country_code : '' }}",
        venueSearchEmail: "",
        venueSearchResults: [],
        selectedVenue: @json($selectedVenue ? $selectedVenue->toData() : ""),
        selectedMembers: @json($selectedMembers ?? []),
        memberSearchEmail: "",
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
      }
    },
    methods: {
      clearSelectedVenue() {
        this.selectedVenue = "";
      },
      editSelectedVenue() {
        this.showVenueAddressFields = true;
        

        this.$nextTick(() => {
            console.log('this.venueCountryCode', this.venueCountryCode);
            $("#venue_country").countrySelect({
                defaultCountry: this.venueCountryCode,
            });
        });
      },
      updateSelectedVenue() {
        this.showVenueAddressFields = false;
      },
      searchVenues() {
        console.log('Searching venues...');

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

          /*
          if (data.length === 0) {
            this.venueEmail = this.venueSearchEmail;
            this.$nextTick(() => {
              const venueNameInput = document.getElementById('venue_name');
              if (venueNameInput) {
                venueNameInput.focus();
              }

              $("#venue_country").countrySelect({
                defaultCountry: "{{ $role && $role->country_code ? $role->country_code : '' }}",
              });
            });
          }
          */
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
        const emailInput = document.getElementById('member_search_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

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
            this.memberEmail = this.memberSearchEmail;
            this.memberName = '';
            this.memberYoutubeUrl = '';
            this.$nextTick(() => {
              const memberNameInput = document.getElementById('member_name');
              if (memberNameInput) {
                memberNameInput.focus();
              }

            });
          }
        })
        .catch(error => {
          console.error('Error searching members:', error);
        });
      },
      clearMemberSearch() {
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.memberSearchEmail = "";
        this.memberSearchResults = [];
        
        this.$nextTick(() => {
          const emailInput = document.getElementById('member_search_email');
          if (emailInput) {
            emailInput.focus();
          }
        });
      },
      addMember(member) {
        if (!this.selectedMembers.some(m => m.id === member.id)) {
          this.selectedMembers.push(member);
        }
        this.memberSearchResults = this.memberSearchResults.filter(m => m.id !== member.id);
        this.memberSearchEmail = "";
        this.memberSearchResults = [];
        this.showMemberTypeRadio = false;
      },
      removeMember(member) {
        this.selectedMembers = this.selectedMembers.filter(m => m.id !== member.id);
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
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.memberSearchEmail = "";
        this.showMemberTypeRadio = false;
      },
      addExistingMember() {
        if (this.selectedMember && !this.selectedMembers.some(m => m.id === this.selectedMember.id)) {
          this.selectedMembers.push(this.selectedMember);
          this.$nextTick(() => {
            this.selectedMember = "";
          });
          this.showMemberTypeRadio = false;
        }
      },
      addNoContactMember() {
        const nameInput = document.getElementById('no_contact_member_name');    
        if (!nameInput.checkValidity()) {
          nameInput.reportValidity();
          return;
        }

        const youtubeInput = document.getElementById('no_contact_member_youtube_url');
        if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
          youtubeInput.reportValidity();
          return;
        }

        const newMember = {
          id: 'new_' + Date.now(),
          name: this.memberName,
          email: null,
          youtube_url: this.memberYoutubeUrl,
        };
        this.selectedMembers.push(newMember);
        this.memberName = "";
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
    },
    computed: {
      filteredMembers() {
        return this.members.filter(member => !this.selectedMembers.some(selected => selected.id === member.id));
      },
      isFormValid() {        
        var hasSubdomain = this.venueName || this.selectedMembers.length > 0;
        var hasVenue = this.venueAddress1 || this.event.event_url;

        return hasSubdomain && hasVenue;
      },
      hasLimitedPaidTickets() {
        return this.tickets.some(ticket => ticket.price > 0 && ticket.quantity > 0);
      }
    },
    watch: {
      venueType() {
        this.venueEmail = "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
        this.setFocusBasedOnVenueType();

        this.$nextTick(() => {
            $("#venue_country").countrySelect({
                defaultCountry: "{{ $role && $role->country_code ? $role->country_code : '' }}",
            });
        });
      },
      memberType() {
        this.memberSearchEmail = "";
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
        this.setFocusBasedOnMemberType();
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
          if (!this.eventName && newValue.length === 1) {
            this.eventName = newValue[0].name;
          }
        },
        deep: true
      },
      'event.tickets_enabled'(newValue) {
        this.savePreferences();
      },
    },
    mounted() {
      this.setFocusBasedOnVenueType();
      this.setFocusBasedOnMemberType();
      this.showMemberTypeRadio = this.selectedMembers.length === 0;

      if (this.event.id) {
        this.isInPerson = !!this.event.venue_id;
        this.isOnline = !!this.event.event_url;
      } else {
        this.loadPreferences();

        if (!this.isInPerson && !this.isOnline) {
          this.isInPerson = true;
        }
      }

      if (this.event.id) {
        this.eventName = this.event.name;
      } else if (this.selectedMembers.length === 1) {
        this.eventName = this.selectedMembers[0].name;
      }

      this.ensureOneChecked('in_person');
    }
  }).mount('#app')
</script>

</x-app-admin-layout>