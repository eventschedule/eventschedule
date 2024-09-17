<x-app-admin-layout>

    @vite([
    'resources/js/countrySelect.min.js',
    'resources/css/countrySelect.min.css',
    ])

    <x-slot name="head">

        <style>
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
            
            updatePreview();
            onChangeBackground();
            onChangeCountry();
            onChangeFont();

            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const file = input.files[0];
                const reader = new FileReader();

                reader.onloadend = function () {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                }

                if (file) {
                    reader.readAsDataURL(file);
                } else {
                    preview.src = '';
                    preview.style.display = 'none';
                }
            }

            $('#profile_image').on('change', function() {
                previewImage(this, 'profile_image_preview');
            });

            $('#background_image').on('change', function() {
                previewImage(this, 'background_image_preview');
                updatePreview();
            });
        });

        function onChangeCountry() {
            var selected = $('#country').countrySelect('getSelectedCountryData');
            $('#country_code').val(selected.iso2);
        }

        function onChangeBackground() {
            var background = $('#background').find(':selected').val();

            $('#style_background_image').hide();
            $('#style_background_gradient').hide();

            if (background == 'image') {
                $('#style_background_image').fadeIn();
            } else if (background == 'gradient') {
                $('#style_background_gradient').fadeIn();
            }
        }

        function onChangeFont() {
            var font_family = $('#font_family').find(':selected').text();
            var link = document.createElement('link');

            link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(font_family.trim()) + ':wght@400;700&display=swap';
            link.rel = 'stylesheet';

            document.head.appendChild(link);

            link.onload = function() {
                updatePreview();
            };
        }

        function updatePreview() {
            var background = $('#background').find(':selected').val();
            var backgroundColor = $('#background_color').val();
            var backgroundColors = $('#background_colors').val();
            var backgroundRotation = $('#background_rotation').val();
            var fontColor = $('#font_color').val();
            var fontFamily = $('#font_family').find(':selected').val();
            var name = $('#name').val();

            if (!name) {
                name = "{{ __('messages.preview') }}";
            }

            $('#preview')
                .css('color', fontColor)
                .css('font-family', fontFamily)
                .css('background-size', 'cover')
                .css('background-position', 'center')
                .text(name);

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
                $('#preview')
                    .css('background-color', '')
                    .css('background-image', 'url("{{ $role->background_image_url }}")');
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
                    $('#address_response').text("{{ __('messages.an_error_occurred') }}" + ': ' + error);
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

        </script>

    </x-slot>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
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
                            {{ __('messages.details') }}
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
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $role->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="profile_image" :value="__('messages.square_profile_image')" />
                            <input id="profile_image" name="profile_image" type="file" class="mt-1 block w-full"
                                :value="old('profile_image')" accept="image/png, image/jpeg" />
                            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

                            <img id="profile_image_preview" src="#" alt="Profile Image Preview" style="max-height:120px; display:none;" class="pt-3" />

                            @if ($role->profile_image_url)
                            <img src="{{ $role->profile_image_url }}" style="max-height:120px" class="pt-3" />
                            <a href="#"
                                onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'profile']) }}'; }"
                                class="hover:underline">
                                {{ __('messages.delete_image') }}
                            </a>
                            @endif
                        </div>

                    </div>
                </div>

                @if ($role->canHaveAddress())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.address') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="address1" :value="__('messages.street_address')" />
                            <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full"
                                :value="old('address1', $role->address1)" autocomplete="off" />
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
                            <x-input-label for="country" :value="__('messages.country')" />
                            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                                :value="old('country')" onchange="onChangeCountry()" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            <input type="hidden" id="country_code" name="country_code" />
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center space-x-4">
                                <x-secondary-button id="view_map_button" onclick="viewMap()">{{ __('messages.view_map') }}</x-secondary-button>
                                <x-secondary-button id="validate_button" onclick="onValidateClick()">{{ __('messages.validate_address') }}</x-secondary-button>
                                <x-secondary-button id="accept_button" onclick="acceptAddress(event)" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
                            </div>
                        </div>

                        <div id="address_response" class="mb-6 hidden text-gray-900 dark:text-gray-100"></div>
                        <input type="hidden" name="formatted_address" id="formatted_address"/>
                        <input type="hidden" name="google_place_id" id="google_place_id"/>
                        <input type="hidden" name="geo_address" id="geo_address"/>
                        <input type="hidden" name="geo_lat" id="geo_lat"/>
                        <input type="hidden" name="geo_lon" id="geo_lon"/>

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

                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div>

                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                        {{ __('messages.style') }}
                    </h2>

                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="w-full lg:w-1/2">
                            <div class="mb-6">
                                <x-input-label for="font_family" :value="__('messages.font_family')" />
                                <select id="font_family" name="font_family" onchange="onChangeFont()"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
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

                            <div class="mb-6">
                                <x-input-label for="accent_color" :value="__('messages.accent_color')" />
                                <x-text-input id="accent_color" name="accent_color" type="color" class="mt-1 block w-1/2"
                                    :value="old('accent_color', $role->accent_color)" />
                                <x-input-error class="mt-2" :messages="$errors->get('accent_color')" />
                            </div>

                            <div class="mb-6">
                                <x-input-label for="background" :value="__('messages.background')" />
                                <select id="background" name="background"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    oninput="onChangeBackground(); updatePreview();">
                                    @foreach(['gradient', 'image'] as $background)
                                    <option value="{{ $background }}"
                                        {{ $role->background == $background ? 'SELECTED' : '' }}>
                                        {{ __('messages.' . $background) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('background')" />
                            </div>

                            <div class="mb-6" id="style_background_image" style="display:none">
                                <x-input-label for="background_image" :value="__('messages.image')" />
                                <input id="background_image" name="background_image" type="file" class="mt-1 block w-full"
                                    :value="old('background_image')" oninput="updatePreview()" accept="image/png, image/jpeg" />
                                <div class="text-xs pt-1">
                                    <a href="https://www.pexels.com" target="_blank" class="hover:underline">{{ __('messages.we_recommend', ['name' => 'pexels.com']) }}</a>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('background_image')" />

                                <img id="background_image_preview" src="#" alt="Background Image Preview" style="max-height:120px; display:none;" class="pt-3" />

                                @if ($role->background_image_url)
                                <img src="{{ $role->background_image_url }}" style="max-height:120px" class="pt-3" />
                                <a href="#"
                                    onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'background']) }}'; return false; }"
                                    class="hover:underline">
                                    {{ __('messages.delete_image') }}
                                </a>
                                @endif
                            </div>

                            <div id="style_background_gradient" style="display:none">
                                <div class="mb-6">
                                    <x-input-label for="background_colors" :value="__('messages.colors')" />
                                    <select id="background_colors" name="background_colors" oninput="updatePreview()"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        @foreach($gradients as $gradient => $name)
                                        <option value="{{ $gradient }}"
                                            {{ $role->background_colors == $gradient || (! array_key_exists($role->background_colors, $gradients) && ! $gradient) ? 'SELECTED' : '' }}>
                                            {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-xs pt-1">
                                        <a href="https://uigradients.com" target="_blank" class="hover:underline">{{ __('messages.gradients_from', ['name' => 'uiGradients']) }}</a>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('background_colors')" />

                                    <div id="custom_colors" style="display:none">
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
                                        class="mt-1 block w-1/2" oninput="updatePreview()"
                                        :value="old('background_rotation', $role->background_rotation)" min="0" max="360" />
                                    <x-input-error class="mt-2" :messages="$errors->get('background_rotation')" />
                                </div>
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
                            {{ __('messages.settings') }}
                        </h2>
                        
                        @if ($role->exists)
                        <div class="mb-6">
                            <x-input-label for="new_subdomain" :value="__('messages.subdomain')" />
                            <x-text-input id="new_subdomain" name="new_subdomain" type="text" class="mt-1 block w-full"
                                :value="old('new_subdomain', $role->subdomain)" required minlength="4" maxlength="50" />
                            <x-input-error class="mt-2" :messages="$errors->get('new_subdomain')" />
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="language_code" :value="__('messages.language') " />
                            <select name="language_code" id="language_code" required
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
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
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
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

                        @if ($role->isVenue())
                        <div class="mb-6">
                            <x-checkbox name="accept_talent_requests"
                                label="{{ __('messages.accept_talent_requests') }}"
                                checked="{{ old('accept_talent_requests', $role->accept_talent_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_talent_requests')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="accept_vendor_requests"
                                label="{{ __('messages.accept_vendor_requests') }}"
                                checked="{{ old('accept_vendor_requests', $role->accept_vendor_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_vendor_requests')" />
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


            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6 mt-3">
            <div class="flex gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
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