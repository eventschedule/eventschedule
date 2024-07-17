<x-app-layout>

    <x-slot name="head">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

        <style>
        /* Ensure the modal overlay covers everything */
        .modal-overlay {
            z-index: 50;
        }

        /* Adjust the z-index of the Leaflet map container if needed */
        #map {
            z-index: 1;
        }
        </style>
    </x-slot>

    <script>
    function showAdd() {
        $('#add_modal').fadeIn(function() {
            $('#link').focus();
        });
    }

    function hideAdd() {
        $('#add_modal').fadeOut();
    }

    document.addEventListener("DOMContentLoaded", function() {
        var map = L.map('map');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        //L.marker([51.505, -0.09]).addTo(map);
        //.bindPopup('A pretty CSS3 popup.<br> Easily customizable.')
        //.openPopup();

        function geocodeAddress(address) {
            const nominatimUrl =
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;

            //console.log('## ADDRESS: ' + nominatimUrl);

            var parts = address.split(', ');
            if (parts.length <= 1) {
                return;
            }
            parts.shift();
            var next_address = parts.join(', ');

            axios.get(nominatimUrl)
                .then(response => {
                    if (response.data.length > 0) {
                        const result = response.data[0];
                        const lat = result.lat;
                        const lon = result.lon;

                        map.setView([lat, lon], 13);

                        //L.marker([lat, lon]).addTo(map);
                    } else {
                        console.log('Address not found.');
                        geocodeAddress(next_address);
                    }
                })
                .catch(error => {
                    console.error('Error geocoding address:', error);
                    geocodeAddress(next_address);
                });
        }

        geocodeAddress('{{ $role->fullAddress() }}');
    });
    </script>

    <div class="pt-2">
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="#" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="-ml-1 mr-1 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                            clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
            </nav>
            <!--
            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div class="flex">
                            <a href="#"
                                class="mr-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ __(ucwords($role->type)) }}</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                            <a href="#" aria-current="page"
                                class="mr-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $role->name }}</a>
                        </div>
                    </li>
                </ol>
            </nav>
            -->
        </div>
        <div class="mt-2 md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    {{ $role->name }}</h2>

                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                    @if($role->email)
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path
                                d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                        <div class="mt-1">
                            <a href="mailto:{{ $role->email }}">
                                {{ $role->email }}
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($role->phone)
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path
                                d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
                        </svg>
                        <div class="mt-1">
                            <a href="tel:{{ $role->phone }}">
                                {{ $role->phone }}
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($role->website)
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="mr-1.5 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path
                                d="M10.59,13.41C11,13.8 11,14.44 10.59,14.83C10.2,15.22 9.56,15.22 9.17,14.83C7.22,12.88 7.22,9.71 9.17,7.76V7.76L12.71,4.22C14.66,2.27 17.83,2.27 19.78,4.22C21.73,6.17 21.73,9.34 19.78,11.29L18.29,12.78C18.3,11.96 18.17,11.14 17.89,10.36L18.36,9.88C19.54,8.71 19.54,6.81 18.36,5.64C17.19,4.46 15.29,4.46 14.12,5.64L10.59,9.17C9.41,10.34 9.41,12.24 10.59,13.41M13.41,9.17C13.8,8.78 14.44,8.78 14.83,9.17C16.78,11.12 16.78,14.29 14.83,16.24V16.24L11.29,19.78C9.34,21.73 6.17,21.73 4.22,19.78C2.27,17.83 2.27,14.66 4.22,12.71L5.71,11.22C5.7,12.04 5.83,12.86 6.11,13.65L5.64,14.12C4.46,15.29 4.46,17.19 5.64,18.36C6.81,19.54 8.71,19.54 9.88,18.36L13.41,14.83C14.59,13.66 14.59,11.76 13.41,10.59C13,10.2 13,9.56 13.41,9.17Z" />
                        </svg>
                        <div class="mt-1">
                            <a href="{{ $role->website }}" target="_blank">
                                {{ \App\Utils\UrlUtils::clean($role->website) }}
                            </a>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <div class="mt-4 flex flex-shrink-0 md:ml-4 md:mt-0">

                <span class="hidden sm:block">
                    <a href="{{ url('/edit/' . $role->subdomain) }}">
                        <button type="button"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                            </svg>
                            {{ __('Edit') }}
                        </button>
                    </a>
                </span>

                <span class="ml-3 hidden sm:block">
                    <a href="{{ url('/view/' . $role->subdomain) }}" target="_blank">
                        <button type="button"
                            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M12.232 4.232a2.5 2.5 0 013.536 3.536l-1.225 1.224a.75.75 0 001.061 1.06l1.224-1.224a4 4 0 00-5.656-5.656l-3 3a4 4 0 00.225 5.865.75.75 0 00.977-1.138 2.5 2.5 0 01-.142-3.667l3-3z" />
                                <path
                                    d="M11.603 7.963a.75.75 0 00-.977 1.138 2.5 2.5 0 01.142 3.667l-3 3a2.5 2.5 0 01-3.536-3.536l1.225-1.224a.75.75 0 00-1.061-1.06l-1.224 1.224a4 4 0 105.656 5.656l3-3a4 4 0 00-.225-5.865z" />
                            </svg>
                            {{ __('View') }}
                        </button>
                    </a>
                </span>

                <span class="sm:ml-3">
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ __('Publish') }}
                    </button>
                </span>
            </div>
        </div>
    </div>

    <div class="pt-5">
        <!-- Dropdown menu on small screens -->
        <div class="sm:hidden">
            <label for="current-tab" class="sr-only">Select a tab</label>
            <select id="current-tab" name="current-tab"
                class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600">
                <option selected>Overview</option>
                <option>Events</option>
                <option>Requests</option>
                <option>Audience</option>
                <option>Team</option>
            </select>
        </div>

        <!-- Tabs at small breakpoint and up -->
        <div class="hidden sm:block">
            <nav class="-mb-px flex space-x-8">
                <!-- Current: "border-indigo-500 text-indigo-600", Default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700" -->
                <a href="#" aria-current="page"
                    class="whitespace-nowrap border-b-2 border-indigo-500 px-1 pb-4 text-sm font-medium text-indigo-600">Overview</a>
                <a href="#"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Events</a>
                <a href="#"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Requests</a>
                <a href="#"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Audience</a>
                <a href="#"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Team</a>
            </nav>
        </div>


    </div>

    <div class="mt-5 overflow-hidden rounded-lg bg-white shadow-md">
        <div class="px-4 py-5 sm:p-6">
            {{ $role->description }}
        </div>
    </div>

    <div class="py-5">
        <div id="map" style="height: 200px;"></div>
    </div>

    <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="bg-white rounded-lg shadow-md p-6">

                <h2 class="font-bold mb-2 flex justify-between items-center">
                    {{ __('Social Links') }}
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        onclick="showAdd()">
                        {{ __('Add') }}
                    </button>
                </h2>
                <p class="text-gray-700">
                <ul role="list" class="divide-y divide-gray-200">
                    @foreach(json_decode($role->social_links) as $link)
                    <a href="{{ $link->url }}" target="_blank">
                        <li class="py-4">
                            <div class="flex">
                                <div class="mr-4 flex-shrink-0 self-center">
                                    <svg class="h-16 w-16 border border-gray-300 bg-white text-gray-300"
                                        preserveAspectRatio="none" stroke="currentColor" fill="none"
                                        viewBox="0 0 200 200" aria-hidden="true">
                                        <path vector-effect="non-scaling-stroke" stroke-width="1"
                                            d="M0 0l200 200M0 200L200 0" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold break-all">{{ $link->name }}</h4>
                                    <p class="mt-1 break-all">{{ \App\Utils\UrlUtils::clean($link->url) }}</p>
                                    <button type="button"
                                        class="mt-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                        onclick="showAdd()">
                                        {{ __('Remove') }}
                                    </button>
                                </div>
                            </div>
                        </li>
                    </a>
                    @endforeach
                </ul>
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="font-bold mb-2 flex justify-between items-center">
                    {{ __('Payment Links') }}
                </h2>
                <p class="text-gray-700">...</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="font-bold mb-2 flex justify-between items-center">
                    {{ __('YouTube Videos') }}
                </h2>
                <p class="text-gray-700">...</p>
            </div>

        </div>
    </div>


    <div id="add_modal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!--
    Background backdrop, show/hide based on modal state.

    Entering: "ease-out duration-300"
      From: "opacity-0"
      To: "opacity-100"
    Leaving: "ease-in duration-200"
      From: "opacity-100"
      To: "opacity-0"
  -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!--
        Modal panel, show/hide based on modal state.

        Entering: "ease-out duration-300"
          From: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          To: "opacity-100 translate-y-0 sm:scale-100"
        Leaving: "ease-in duration-200"
          From: "opacity-100 translate-y-0 sm:scale-100"
          To: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      -->
                <form method="POST" action="{{ url('/update/links/' . $role->subdomain) }}">

                    @csrf

                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2x1 sm:p-6">
                        <div>
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.59,13.41C11,13.8 11,14.44 10.59,14.83C10.2,15.22 9.56,15.22 9.17,14.83C7.22,12.88 7.22,9.71 9.17,7.76V7.76L12.71,4.22C14.66,2.27 17.83,2.27 19.78,4.22C21.73,6.17 21.73,9.34 19.78,11.29L18.29,12.78C18.3,11.96 18.17,11.14 17.89,10.36L18.36,9.88C19.54,8.71 19.54,6.81 18.36,5.64C17.19,4.46 15.29,4.46 14.12,5.64L10.59,9.17C9.41,10.34 9.41,12.24 10.59,13.41M13.41,9.17C13.8,8.78 14.44,8.78 14.83,9.17C16.78,11.12 16.78,14.29 14.83,16.24V16.24L11.29,19.78C9.34,21.73 6.17,21.73 4.22,19.78C2.27,17.83 2.27,14.66 4.22,12.71L5.71,11.22C5.7,12.04 5.83,12.86 6.11,13.65L5.64,14.12C4.46,15.29 4.46,17.19 5.64,18.36C6.81,19.54 8.71,19.54 9.88,18.36L13.41,14.83C14.59,13.66 14.59,11.76 13.41,10.59C13,10.2 13,9.56 13.41,9.17Z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                                    {{ __('Add Link') }}</h3>
                                <div class="mt-2">

                                    <x-text-input id="link" name="link" type="url" class="mt-1 block w-full" required
                                        autofocus />
                                    <x-input-error class="mt-2" :messages="$errors->get('url')" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2">{{ __('Save') }}</button>
                            <button type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                onclick="hideAdd()">{{ __('Cancel') }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

</x-app-layout>