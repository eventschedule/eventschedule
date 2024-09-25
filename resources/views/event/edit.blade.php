<x-app-admin-layout>

<x-slot name="head">
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
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
                                        <input id="use_existing" name="venue_type" type="radio" value="use_existing" v-model="venueType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="use_existing"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="search_create" name="venue_type" type="radio" value="search_create" v-model="venueType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="search_create"
                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.search_create') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="private_address" name="venue_type" type="radio" value="private_address" v-model="venueType"
                                            class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                        <label for="private_address"
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
                                    <div class="flex mt-1">
                                        <x-text-input id="venue_search_email" v-model="venueSearchEmail" type="email" class="block w-full mr-2"
                                            :placeholder="__('messages.enter_email')" required autofocus @keyup.enter="searchVenues" />
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
                                        <x-input-label for="venue_email" :value="__('messages.email')" />
                                        <div class="flex">
                                            <x-text-input id="venue_email" name="venue_email" type="email" class="mt-1 mr-2 block w-full"
                                                v-model="venueEmail" required disabled />
                                            <x-primary-button @click="searchVenues" type="button">
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
            </div>
        </div>
    </form>
</div>

<script>
  const { createApp, ref } = Vue

  createApp({
    data() {
      return {
        event: @json($event),
        venues: @json($venues),
        venueType: "{{ count($venues) ? 'use_existing' : 'search_create' }}",
        venueName: "{{ old('venue_name', $venue ? $venue->name : '') }}",
        venueEmail: "{{ old('venue_email', $venue ? $venue->email : request()->venue_email) }}",
        venueSearchEmail: "",
        venueSearchResults: [],
      }
    },
    methods: {
      clearSelectedVenue() {
        this.event.venue_id = "";
      },
      searchVenues() {
        if (!this.venueSearchEmail) return;

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
          }
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
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
      }
    },
    mounted() {
        //
    }
  }).mount('#app')
</script>

</x-app-admin-layout>