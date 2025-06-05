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

        .country-select {
            width: 100%;
        }

        #preview {
            border: 1px solid #dbdbdb;
            border-radius: 4px;
            height: 150px;
            width: 100%;
            text-align: center;
            vertical-align: middle;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            font-size: 3rem;
        }

        .color-select-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .color-nav-button {
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            background: white;
            cursor: pointer;
        }

        .color-nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .color-nav-button:hover:not(:disabled) {
            background: #f3f4f6;
        }

        </style>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            $("#country").countrySelect({
                defaultCountry: '{{ old('country_code', $role->country_code) }}',
            });
            $('#background').val('{{ old('background', $role->background) }}');
            $('#background_colors').val('{{ old('background_colors', $role->background_colors) }}');
            $('#font_family').val('{{ old('font_family', $role->font_family) }}');
            $('#language_code').val('{{ old('language_code', $role->language_code) }}');
            $('#timezone').val('{{ old('timezone', $role->timezone) }}');
            
            $('#header_image').trigger('input');
            
            updatePreview();
            onChangeBackground();
            onChangeCountry();
            onChangeFont();
            updateImageNavButtons();
            toggleCustomImageInput();
            updateHeaderNavButtons();
            toggleCustomHeaderInput();

            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const warningElement = document.getElementById(previewId.split('_')[0] + '_image_size_warning');

                if (!input || !input.files || !input.files[0]) {
                    console.log('no file')
                    if (preview) {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                    if (warningElement) {
                        warningElement.textContent = '';
                        warningElement.style.display = 'none';
                    }
                    updatePreview();
                    return;
                }

                const file = input.files[0];
                const reader = new FileReader();

                reader.onloadend = function () {
                    const img = new Image();
                    img.onload = function() {
                        const width = this.width;
                        const height = this.height;
                        const fileSize = file.size / 1024 / 1024; // in MB
                        let warningMessage = '';

                        if (fileSize > 2.5) {
                            warningMessage += "{{ __('messages.image_size_warning') }}";
                        }

                        if (width !== height && previewId == 'profile_image_preview') {
                            if (warningMessage) warningMessage += " ";
                            warningMessage += "{{ __('messages.image_not_square') }}";
                        }

                        if (warningElement) {
                            if (warningMessage) {
                                warningElement.textContent = warningMessage;
                                warningElement.style.display = 'block';
                            } else {
                                warningElement.textContent = '';
                                warningElement.style.display = 'none';
                            }
                        }

                        if (warningMessage == '') {
                            preview.src = reader.result;
                            preview.style.display = 'block';
                            updatePreview();
                            
                            if (previewId === 'background_image_preview') {
                                $('#style_background_image img:not(#background_image_preview)').hide();
                                $('#style_background_image a').hide();
                            }
                        } else {
                            preview.src = '';
                            preview.style.display = 'none';
                        }
                    };
                    img.src = reader.result;
                }

                if (file) {
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                    if (warningElement) {
                        warningElement.textContent = '';
                        warningElement.style.display = 'none';
                    }
                    updatePreview();
                }
            }

            $('#profile_image').on('change', function() {
                previewImage(this, 'profile_image_preview');
            });

            $('#header_image').on('input', function() {
                var headerImageUrl = $(this).find(':selected').val();
                if (headerImageUrl) {
                    headerImageUrl = "{{ asset('images/headers') }}" + '/' + headerImageUrl + '.png';
                    $('#header_image_preview').attr('src', headerImageUrl).show();
                    $('#delete_header_image').hide();
                } else if ({{ $role->header_image_url ? 'true' : 'false' }}) {
                    $('#header_image_preview').attr('src', '{{ $role->header_image_url }}').show();
                    $('#delete_header_image').show();
                } else {
                    $('#header_image_preview').hide();
                    $('#delete_header_image').hide();
                }
            });

            $('#header_image_url').on('change', function() {
                previewImage(this, 'header_image_preview');
                $('#header_image_preview').show();
            });

            $('#background_image_url').on('change', function() {
                previewImage(this, 'background_image_preview');
                updatePreview();
            });
        });

        function onChangeCountry() {
            var selected = $('#country').countrySelect('getSelectedCountryData');
            $('#country_code').val(selected.iso2);
        }

        function onChangeBackground() {
            var background = $('input[name="background"]:checked').val();

            $('#style_background_image').hide();
            $('#style_background_gradient').hide();
            $('#style_background_solid').hide();
            
            if (background == 'image') {
                $('#style_background_image').show();
            } else if (background == 'gradient') {
                $('#style_background_gradient').show();
            } else if (background == 'solid') {
                $('#style_background_solid').show();
            }
        }

        function onChangeFont() {
            /*
            var font_family = $('#font_family').find(':selected').text();
            var link = document.createElement('link');

            link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(font_family.trim()) + ':wght@400;700&display=swap';
            link.rel = 'stylesheet';

            document.head.appendChild(link);

            link.onload = function() {
                updatePreview();
            };
            */
        }

        function updatePreview() {
            var background = $('input[name="background"]:checked').val();
            var backgroundColor = $('#background_color').val();
            var backgroundColors = $('#background_colors').val();
            var backgroundRotation = $('#background_rotation').val();
            var fontColor = $('#font_color').val();
            var fontFamily = $('#font_family').find(':selected').val();
            var name = $('#name').val();

            if (! name) {
                name = "{{ __('messages.preview') }}";
            } else if (name.length > 10) {
                name = name.substring(0, 10) + '...';
            }

            $('#preview')
                .css('color', fontColor)
                .css('font-family', fontFamily)
                .css('background-size', 'cover')
                .css('background-position', 'center')
                .html('<div class="bg-[#F5F9FE] rounded-2xl px-6 py-4 flex flex-col">' + name + '</div>');

            if (background == 'gradient') {
                $('#custom_colors').toggle(backgroundColors == '');
                if (backgroundColors == '') {
                    var customColor1 = $('#custom_color1').val();
                    var customColor2 = $('#custom_color2').val();
                    backgroundColors = customColor1 + ', ' + customColor2;
                }

                if (!backgroundRotation) {
                    backgroundRotation = '0';
                }

                var gradient = 'linear-gradient(' + backgroundRotation + 'deg, ' + backgroundColors + ')';

                $('#preview')
                    .css('background-color', '')
                    .css('background-image', gradient);
            } else if (background == 'image') {

                var backgroundImageUrl = $('#background_image').find(':selected').val();
                if (backgroundImageUrl) {
                    backgroundImageUrl = "{{ asset('images/backgrounds') }}" + '/' + $('#background_image').find(':selected').val() + '.png';
                } else {
                    backgroundImageUrl = $('#background_image_preview').attr('src') || "{{ $role->background_image_url }}";
                }

                $('#preview')
                    .css('background-color', '')
                    .css('background-image', 'url("' + backgroundImageUrl + '")');
            } else {
                $('#preview').css('background-image', '')
                    .css('background-color', backgroundColor);
            }
        }

        function onValidateClick() {
            $('#address_response').text("{{ __('messages.searching') }}...").show();
            $('#accept_button').hide();
            var country = $('#country').countrySelect('getSelectedCountryData');
            $.post({
                url: '{{ route('validate_address') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    address1: $('#address1').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postal_code: $('#postal_code').val(),                    
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
                    $('#address_response').text("{{ __('messages.an_error_occurred') }}");
                }
            });
        }

        function viewMap() {
            var address = [
                $('#address1').val(),
                $('#city').val(),
                $('#state').val(),
                $('#postal_code').val(),
                $('#country').countrySelect('getSelectedCountryData').name
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
                $('#address1').val(validatedAddress['address1']);
                $('#city').val(validatedAddress['city']);
                $('#state').val(validatedAddress['state']);
                $('#postal_code').val(validatedAddress['postal_code']);
                $("#country").countrySelect("selectCountry", validatedAddress['country_code']);
                                
                // Hide the address response and accept button after accepting
                $('#address_response').hide();
                $('#accept_button').hide();
            }
        }

        function updateColorNavButtons() {
            const select = document.getElementById('background_colors');
            const prevButton = document.getElementById('prev_color');
            const nextButton = document.getElementById('next_color');
            
            prevButton.disabled = select.selectedIndex === 0;
            nextButton.disabled = select.selectedIndex === select.options.length - 1;
        }

        function changeBackgroundColor(direction) {
            const select = document.getElementById('background_colors');
            const newIndex = select.selectedIndex + direction;
            
            if (newIndex >= 0 && newIndex < select.options.length) {
                select.selectedIndex = newIndex;
                select.dispatchEvent(new Event('input'));
                updateColorNavButtons();
            }
        }

        function updateImageNavButtons() { 
            const select = document.getElementById('background_image');
            const prevButton = document.getElementById('prev_image');
            const nextButton = document.getElementById('next_image');

            prevButton.disabled = select.selectedIndex === 0;
            nextButton.disabled = select.selectedIndex === select.options.length - 1;
        }

        function changeBackgroundImage(direction) {
            const select = document.getElementById('background_image');
            const newIndex = select.selectedIndex + direction;
            
            if (newIndex >= 0 && newIndex < select.options.length) {
                select.selectedIndex = newIndex;
                select.dispatchEvent(new Event('input'));
                updateImageNavButtons();
            }
        }

        function toggleCustomImageInput() {
            const select = document.getElementById('background_image');
            const customInput = document.getElementById('custom_image_input');
            customInput.style.display = select.value === '' ? 'block' : 'none';
        }

        function updateHeaderNavButtons() { 
            const select = document.getElementById('header_image');
            const prevButton = document.getElementById('prev_header');
            const nextButton = document.getElementById('next_header');

            prevButton.disabled = select.selectedIndex === 0;
            nextButton.disabled = select.selectedIndex === select.options.length - 1;
        }

        function changeHeaderImage(direction) {
            const select = document.getElementById('header_image');
            const newIndex = select.selectedIndex + direction;
            
            if (newIndex >= 0 && newIndex < select.options.length) {
                select.selectedIndex = newIndex;
                select.dispatchEvent(new Event('input'));
                updateHeaderNavButtons();
            }
        }

        function toggleCustomHeaderInput() {
            const select = document.getElementById('header_image');
            const customInput = document.getElementById('custom_header_input');
            customInput.style.display = select.value === '' ? 'block' : 'none';
        }


        </script>

    </x-slot>

    <h2 class="pt-2 my-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
        {{ $title }}
    </h2>

    <form method="post"
        action="{{ $role->exists ? route('role.update', ['subdomain' => $role->subdomain]) : route('role.store') }}"
        enctype="multipart/form-data">

        @csrf
        @if($role->exists)
        @method('put')
        @endif

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.' . $role->type . '_details') }}
                        </h2>

                        @if(! $role->exists)
                        <input type="hidden" name="type" value="{{ $role->type }}"/>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="name" :value="__('messages.name') . ' *'" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $role->name)" required autofocus oninput="updatePreview()" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.description')" />
                            <textarea id="description" name="description"
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('description', $role->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="profile_image" :value="__('messages.square_profile_image')" />
                            <input id="profile_image" name="profile_image" type="file" class="mt-1 block w-full text-gray-900 dark:text-gray-100"
                                :value="old('profile_image')" accept="image/png, image/jpeg" />
                            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                            <p id="profile_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                {{ __('messages.image_size_warning') }}
                            </p>

                            <img id="profile_image_preview" src="#" alt="Profile Image Preview" style="max-height:120px; display:none;" class="pt-3" />

                            @if ($role->profile_image_url)
                            <img src="{{ $role->profile_image_url }}" style="max-height:120px" class="pt-3" />
                            <a href="#"
                                onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'profile']) }}'; }"
                                class="hover:underline text-gray-900 dark:text-gray-100">
                                {{ __('messages.delete_image') }}
                            </a>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-input-label for="header_image" :value="__('messages.header_image')" />
                            <div class="color-select-container">
                                <select id="header_image" name="header_image"
                                    class="flex-grow border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                    oninput="updatePreview(); updateHeaderNavButtons(); toggleCustomHeaderInput();">
                                    @foreach($headers as $header => $name)
                                    <option value="{{ $header }}"
                                        {{ $role->header_image == $header ? 'SELECTED' : '' }}>
                                        {{ $name }}</option>
                                    @endforeach
                                </select>

                                <button type="button" 
                                        id="prev_header" 
                                        class="color-nav-button" 
                                        onclick="changeHeaderImage(-1)"
                                        title="{{ __('messages.previous') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                                                                                
                                <button type="button" 
                                        id="next_header" 
                                        class="color-nav-button" 
                                        onclick="changeHeaderImage(1)"
                                        title="{{ __('messages.next') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>

                            <div id="custom_header_input" style="display:none" class="mt-2">
                                <input id="header_image_url" name="header_image_url" type="file" 
                                    class="mt-1 block w-full text-gray-900 dark:text-gray-100" 
                                    :value="old('header_image_url')" 
                                    accept="image/png, image/jpeg" />
                                <x-input-error class="mt-2" :messages="$errors->get('header_image_url')" />
                                <p id="header_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                    {{ __('messages.image_size_warning') }}
                                </p>
                            </div>

                            <img id="header_image_preview" 
                                src="{{ $role->header_image ? asset('images/headers/' . $role->header_image . '.png') : $role->header_image_url }}" 
                                alt="Header Image Preview" 
                                style="max-height:120px; {{ $role->header_image || $role->header_image_url ? '' : 'display:none;' }}" 
                                class="pt-3" />

                            @if ($role->header_image_url)
                            <a href="#" id="delete_header_image" style="display: {{ $role->header_image ? 'none' : 'block' }};"
                                onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'header']) }}'; }"
                                class="hover:underline text-gray-900 dark:text-gray-100">
                                {{ __('messages.delete_image') }}
                            </a>
                            @endif

                        </div>

                    </div>
                </div>

                @if ($role->isVenue())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.venue_address') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="address1" :value="__('messages.street_address') . ' *'" />
                            <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full"
                                :value="old('address1', $role->address1)" autocomplete="off" required />
                            <x-input-error class="mt-2" :messages="$errors->get('address1')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="city" :value="__('messages.city')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city', $role->city)" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="state" :value="__('messages.state_province')" />
                            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                                :value="old('state', $role->state)" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="postal_code" :value="__('messages.postal_code')" />
                            <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full"
                                :value="old('postal_code', $role->postal_code)" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center space-x-4">
                                <x-secondary-button id="view_map_button" onclick="viewMap()">{{ __('messages.view_map') }}</x-secondary-button>
                                @if (config('services.google.backend'))
                                <x-secondary-button id="validate_button" onclick="onValidateClick()">{{ __('messages.validate_address') }}</x-secondary-button>
                                <x-secondary-button id="accept_button" onclick="acceptAddress(event)" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
                                @endif
                            </div>
                        </div>

                        <div id="address_response" class="mb-6 hidden text-gray-900 dark:text-gray-100"></div>

                    </div>
                </div>
                @endif

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.contact_info') }}
                        </h2>

                        <div class="mb-3">
                            <x-input-label for="email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $role->exists ? $role->email : $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="show_email" label="{{ __('messages.show_email_address') }}"
                                checked="{{ old('show_email', $role->show_email) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('show_email')" />
                        </div>

                        <!--
                        <div class="mb-6">
                            <x-input-label for="phone" :value="__('messages.phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                :value="old('phone', $role->phone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>
                        -->

                        <div class="mb-6">
                            <x-input-label for="website" :value="__('messages.website')" />
                            <x-text-input id="website" name="website" type="url" class="mt-1 block w-full"
                                :value="old('website', $role->website)" />
                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                        </div>

                        @if($role->isCurator())
                        <div class="mb-6">
                            <x-input-label for="city" :value="__('messages.city')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city', $role->city)" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="country" :value="__('messages.country')" />
                            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                                :value="old('country')" onchange="onChangeCountry()" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            <input type="hidden" id="country_code" name="country_code" />
                        </div>
                        @endif


                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div>

                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                        {{ __('messages.schedule_style') }}
                    </h2>

                    <div class="flex flex-col xl:flex-row xl:gap-12">
                        <div class="w-full lg:w-1/2">
                            <!--
                            <div class="mb-6">
                                <x-input-label for="font_family" :value="__('messages.font_family')" />
                                <select id="font_family" name="font_family" onchange="onChangeFont()"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                    @foreach($fonts as $font)
                                    <option value="{{ $font->value }}"
                                        {{ $role->font_family == $font->value ? 'SELECTED' : '' }}>
                                        {{ $font->label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('font_family')" />
                            </div>

                            <div class="mb-6">
                                <x-input-label for="font_color" :value="__('messages.font_color')" />
                                <x-text-input id="font_color" name="font_color" type="color" class="mt-1 block w-1/2"
                                    :value="old('font_color', $role->font_color)" oninput="updatePreview()" />
                                <x-input-error class="mt-2" :messages="$errors->get('font_color')" />
                            </div>
                            -->

                            <div class="mb-6">
                                <x-input-label :value="__('messages.background')" />
                                <div class="mt-2 space-y-2">
                                    @foreach(['gradient', 'solid', 'image'] as $background)
                                    <div class="flex items-center">
                                        <input type="radio" 
                                            id="background_type_{{ $background }}" 
                                            name="background" 
                                            value="{{ $background }}"
                                            {{ $role->background == $background ? 'checked' : '' }}
                                            class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4"
                                            onchange="onChangeBackground(); updatePreview();">
                                        <label for="background_type_{{ $background }}" class="ml-2 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.' . $background) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('background')" />
                            </div>

                            <div class="mb-6" id="style_background_solid" style="display:none">
                                <x-input-label for="background_color" :value="__('messages.background_color')" />
                                <x-text-input id="background_color" name="background_color" type="color" class="mt-1 block w-1/2"
                                    :value="old('background_color', $role->background_color)" oninput="updatePreview()" />
                                <x-input-error class="mt-2" :messages="$errors->get('background_color')" />
                            </div>

                            <div class="mb-6" id="style_background_image" style="display:none">
                                <x-input-label for="image" :value="__('messages.image')" />
                                <div class="color-select-container">
                                    <select id="background_image" name="background_image"
                                        class="flex-grow border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm w-64 max-w-64"
                                        oninput="onChangeBackground(); updatePreview(); updateImageNavButtons(); toggleCustomImageInput();">
                                        @foreach($backgrounds as $background => $name)
                                        <option value="{{ $background }}"
                                            {{ $role->background_image == $background ? 'SELECTED' : '' }}>
                                            {{ $name }}</option>
                                        @endforeach
                                    </select>

                                    <button type="button" 
                                            id="prev_image" 
                                            class="color-nav-button" 
                                            onclick="changeBackgroundImage(-1)"
                                            title="{{ __('messages.previous') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>
                                                                                
                                    <button type="button" 
                                            id="next_image" 
                                            class="color-nav-button" 
                                            onclick="changeBackgroundImage(1)"
                                            title="{{ __('messages.next') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
        
                                </div>

                                <div id="custom_image_input" style="display:none">
                                    <input id="background_image_url" name="background_image_url" type="file" 
                                        class="mt-1 block w-full text-gray-900 dark:text-gray-100" 
                                        :value="old('background_image_url')" 
                                        oninput="updatePreview()" 
                                        accept="image/png, image/jpeg" />
                                    <p id="background_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                        {{ __('messages.image_size_warning') }}
                                    </p>

                                    <img id="background_image_preview" src="" alt="Background Image Preview" style="max-height:120px; display:none;" class="pt-3" />

                                    @if ($role->background_image_url)
                                    <img src="{{ $role->background_image_url }}" style="max-height:120px" class="pt-3" />
                                    <a href="#"
                                        onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'background']) }}'; } return false;"
                                        class="hover:underline text-gray-900 dark:text-gray-100">
                                        {{ __('messages.delete_image') }}
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <div id="style_background_gradient" style="display:none">
                                <div class="mb-6">
                                    <x-input-label for="background_colors" :value="__('messages.colors')" />
                                    <div class="color-select-container">
                                        <select id="background_colors" name="background_colors" oninput="updatePreview(); updateColorNavButtons()"
                                            class="flex-grow border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm w-64 max-w-64">
                                            @foreach($gradients as $gradient => $name)
                                            <option value="{{ $gradient }}"
                                                {{ $role->background_colors == $gradient || (! array_key_exists($role->background_colors, $gradients) && ! $gradient) ? 'SELECTED' : '' }}>
                                                {{ $name }}</option>
                                            @endforeach
                                        </select>
                                    
                                        <button type="button" 
                                                id="prev_color" 
                                                class="color-nav-button" 
                                                onclick="changeBackgroundColor(-1)"
                                                title="{{ __('messages.previous') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                            </svg>
                                        </button>
                                                                                
                                        <button type="button" 
                                                id="next_color" 
                                                class="color-nav-button" 
                                                onclick="changeBackgroundColor(1)"
                                                title="{{ __('messages.next') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-xs pt-1">
                                        <a href="https://uigradients.com" target="_blank" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.gradients_from', ['name' => 'uiGradients']) }}</a>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('background_colors')" />

                                    <div id="custom_colors" style="display:none" class="mt-4">
                                        <x-text-input id="custom_color1" name="custom_color1" type="color"
                                            class="mt-1 block w-1/2"
                                            :value="old('custom_color1', $role->background_colors ? explode(', ', $role->background_colors)[0] : '')"
                                            oninput="updatePreview()" />
                                        <x-text-input id="custom_color2" name="custom_color2" type="color"
                                            class="mt-1 block w-1/2"
                                            :value="old('custom_color2', $role->background_colors ? explode(', ', $role->background_colors)[1] : '')"
                                            oninput="updatePreview()" />
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="background_rotation" :value="__('messages.rotation')" />
                                    <x-text-input id="background_rotation" name="background_rotation" type="number"
                                        class="mt-1 block w-32 max-w-32" oninput="updatePreview()"
                                        :value="old('background_rotation', $role->background_rotation)" min="0" max="360" />
                                    <x-input-error class="mt-2" :messages="$errors->get('background_rotation')" />
                                </div>
                            </div>

                            <div class="mb-6">
                                <x-input-label for="accent_color" :value="__('messages.accent_color')" />
                                <x-text-input id="accent_color" name="accent_color" type="color" class="mt-1 block w-1/2"
                                    :value="old('accent_color', $role->accent_color)" />
                                <x-input-error class="mt-2" :messages="$errors->get('accent_color')" />
                            </div>

                        </div>

                        <div class="w-full flex-grow">
                            <x-input-label :value="__('messages.preview')" />
                            <div id="preview" class="h-full w-full flex-grow"></div>
                        </div>
                    </div>
                </div>

                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.schedule_settings') }}
                        </h2>
                        
                        @if ($role->exists)
                        <div class="mb-6">
                            <x-input-label for="new_subdomain" :value="__('messages.subdomain')" />
                            <x-text-input id="new_subdomain" name="new_subdomain" type="text" class="mt-1 block w-full"
                                :value="old('new_subdomain', $role->subdomain)" required minlength="4" maxlength="50"
                                pattern="[a-z0-9-]+" oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')" />
                            <x-input-error class="mt-2" :messages="$errors->get('new_subdomain')" />
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="language_code" :value="__('messages.language') " />
                            <select name="language_code" id="language_code" required
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                @foreach([
                                'ar' => 'arabic',
                                'en' => 'english',
                                'nl' => 'dutch',
                                'fr' => 'french',
                                'de' => 'german',
                                'he' => 'hebrew',
                                'it' => 'italian',
                                'pt' => 'portuguese',
                                'es' => 'spanish',
                                ] as $key => $value)
                                <option value="{{ $key }}" {{ $role->language_code == $key ? 'SELECTED' : '' }}>
                                    {{ __('messages.' . $value) }}
                                </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('language_code')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="timezone" :value="__('messages.timezone')" />
                            <select name="timezone" id="timezone" required
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                @foreach(\Carbon\CarbonTimeZone::listIdentifiers() as $timezone)
                                <option value="{{ $timezone }}" {{ $role->timezone == $timezone ? 'SELECTED' : '' }}>
                                    {{ $timezone }}
                                </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="use_24_hour_time" label="{{ __('messages.use_24_hour_time_format') }}"
                                checked="{{ old('use_24_hour_time', $role->use_24_hour_time) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('use_24_hour_time')" />
                        </div>

                        @if ($role->isVenue() && config('app.hosted'))
                        <div class="mb-6">
                            <x-checkbox name="accept_requests"
                                label="{{ __('messages.accept_requests') }}"
                                checked="{{ old('accept_requests', $role->accept_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_requests')" />
                        </div>

                        @elseif ($role->isCurator())

                        <div class="mb-6">
                            <x-checkbox name="is_open"
                                label="{{ __('messages.allow_everyone_to_add_events') }}"
                                checked="{{ old('is_open', $role->is_open) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('is_open')" />
                        </div>

                        @endif

                        <!--
                        <div class="mb-6">
                            <x-checkbox name="is_unlisted"
                                label="{{ __('messages.is_unlisted') }}"
                                checked="{{ old('is_unlisted', $role->is_unlisted) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('is_unlisted')" />
                        </div>
                        -->

                        
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.groups') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="groups" :value="__('messages.groups')" />
                            <div id="groups-list">
                                @php $groups = $role->groups ?? []; @endphp
                                <div id="group-items">
                                    @foreach(old('groups', $groups) as $i => $group)
                                        <div class="flex items-center mb-2">
                                            <x-text-input name="groups[{{ is_object($group) ? $group->id : $i }}][name]" type="text" class="mr-2 block w-full" :value="is_object($group) ? $group->name : $group['name'] ?? ''" placeholder="Group name" />
                                            <x-text-input name="groups[{{ is_object($group) ? $group->id : $i }}][slug]" type="text" class="mr-2 block w-1/3" :value="is_object($group) ? $group->slug : $group['slug'] ?? ''" placeholder="Slug" />
                                            <button type="button" class="text-red-600" onclick="this.parentElement.remove()">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded" onclick="addGroupField()">+ {{ __('messages.add_group') }}</button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('groups')" />
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6 mt-3">
            @if (! $role->exists)
            <p class="text-base dark:text-gray-400 text-gray-600 pb-2">
                {{ __('messages.note_all_schedules_are_publicly_listed') }}
            </p>
            @endif

            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                    <x-cancel-button></x-cancel-button>
                </div>
                <div>
                    @if ($role->exists && $role->user_id == $user->id)
                    <x-delete-button :url="route('role.delete', ['subdomain' => $role->subdomain])">
                    </x-delete-button>
                    @endif
                </div>
            </div>

        </div>

    </form>

</x-app-admin-layout>

<script>
function addGroupField() {
    const container = document.getElementById('group-items');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'flex items-center mb-2';
    div.innerHTML = `<input name="groups[new_${idx}][name]" type="text" class="mr-2 block w-full" placeholder="Group name" />
        <input name="groups[new_${idx}][slug]" type="text" class="mr-2 block w-1/3" placeholder="Slug" />
        <button type="button" class="text-red-600" onclick="this.parentElement.remove()">&times;</button>`;
    container.appendChild(div);
}
</script>