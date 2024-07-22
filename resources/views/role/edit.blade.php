<x-app-layout>

    @vite([
    'resources/js/jquery-3.3.1.min.js',
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
            onChangeBackground();
            onChangeFont();

            $("#country").countrySelect({
                defaultCountry: '{{ $role->country_code }}',
            });
        });

        function toggleAddress() {
            var type = $('input[name="type"]:checked').val();
            if (type == 'talent') {
                $('#address').fadeOut();
            } else {
                $('#address').fadeIn();
            }
        }

        function onChangeCountry() {
            var selected = $('#country').countrySelect('getSelectedCountryData');
            $('#country_code').val(selected.iso2);
        }

        function onChangeBackground() {
            var background = $('#background').find(":selected").val();

            if (background == 'image') {
                $('#style_background_image').fadeIn();
            } else {
                $('#style_background_image').fadeOut();
            }

            if (background == 'gradient') {
                $('#style_background_gradient').fadeIn();
            } else {
                $('#style_background_gradient').fadeOut();
            }
        }

        function onChangeFont() {
            var font_family = $('#font_family').find(":selected").val();

            var link = document.createElement('link');
            link.href = 'https://fonts.googleapis.com/css2?family=' + font_family + ':wght@400;700&display=swap';
            link.rel = 'stylesheet';
            document.head.appendChild(link);

            link.onload = function() {
                updatePreview();
                //document.getElementById("title").style.fontFamily = "'Roboto', sans-serif";
            };            
        }

        function updatePreview() {
            var background = $('#background').find(":selected").val();
            var backgroundColors = $('#background_colors').val();
            var backgroundRotation = $('#background_rotation').val();
            var fontColor = $('#font_color').val();
            var fontFamily = $('#font_family').find(":selected").val();
            var name = $('#name').val();
            
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

                if (! backgroundRotation) {
                    backgroundRotation = '0';
                }

                var gradient = 'linear-gradient(' + backgroundRotation + 'deg, ' + backgroundColors + ')';

                $('#preview').css('background-image', gradient);
            } else if (background == 'image') {
                $('#preview').css('background-image', 'url("{{ $role->background_image_url }}")');
            } else {
                $('#preview').css('background-image', '');
            }
        }
        </script>

    </x-slot>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ $title }}
    </h2>

    <form method="POST"
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
                            {{ __('Details') }}
                        </h2>

                        @if(! $role->exists)
                        <fieldset>
                            <x-input-label for="type" :value="__('Type')" />
                            <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0"
                                onclick="toggleAddress()">
                                <div class="flex items-center">
                                    <input id="venue" name="type" type="radio" value="venue" checked
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="venue"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Venue</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="talent" name="type" type="radio" value="talent"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="talent"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Talent</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="vendor" name="type" type="radio" value="vendor"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="vendor"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                                </div>
                            </div>
                        </fieldset>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="name" :value="__('Name') . ' *'" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $role->name)" required autofocus oninput="updatePreview()"/>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $role->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="profile_image" :value="__('Profile Image')" />
                            <input id="profile_image" name="profile_image" type="file" class="mt-1 block w-full"
                                :value="old('profile_image')" />
                            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

                            @if ($role->profile_image_url)
                            <img src="{{ $role->profile_image_url }}" style="max-height:120px" class="pt-3" />
                            @endif
                        </div>

                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Contact Info') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="email" :value="__('Email' . ' *')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $role->exists ? $role->email : auth()->user()->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                                :value="old('phone', $role->phone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="website" :value="__('Website')" />
                            <x-text-input id="website" name="website" type="url" class="mt-1 block w-full"
                                :value="old('website', $role->website)" />
                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                        </div>

                    </div>
                </div>


                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Address') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="address1" :value="__('Street Address')" />
                            <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full"
                                :value="old('address1', $role->address1)" />
                            <x-input-error class="mt-2" :messages="$errors->get('address1')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city', $role->city)" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="state" :value="__('State / Province')" />
                            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full"
                                :value="old('state', $role->state)" />
                            <x-input-error class="mt-2" :messages="$errors->get('state')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="postal_code" :value="__('Postal Code')" />
                            <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full"
                                :value="old('postal_code', $role->postal_code)" />
                            <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="country" :value="__('Country')" />
                            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                                :value="old('country', $role->country)" onchange="onChangeCountry()" />
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            <input type="hidden" id="country_code" name="country_code" />
                        </div>

                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Page Style') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="design" :value="__('Design')" />
                            <select id="design" name="design"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach(['clean', 'compact', 'dark'] as $design)
                                <option value="{{ $design }}" {{ $role->design == $design ? 'SELECTED' : '' }}>
                                    {{ __(ucwords($design)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('design')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="font_family" :value="__('Font Family')" />
                            <select id="font_family" name="font_family" onchange="onChangeFont()"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($fonts as $font)
                                <option value="{{ $font->value }}" {{ $role->font_family == $font->value ? 'SELECTED' : '' }}>
                                    {{ $font->label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('font_family')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="font_color" :value="__('Font Color')" />
                            <x-text-input id="font_color" name="font_color" type="color" class="mt-1 block w-1/2"
                                :value="old('font_color', $role->font_color)" oninput="updatePreview()" />
                            <x-input-error class="mt-2" :messages="$errors->get('font_color')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="background" :value="__('Background')" />
                            <select id="background" name="background"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                oninput="onChangeBackground(); updatePreview();">
                                @foreach(['default', 'image', 'gradient'] as $background)
                                <option value="{{ $background }}"
                                    {{ $role->background == $background ? 'SELECTED' : '' }}>
                                    {{ __(ucwords($background)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('background')" />
                        </div>


                        <div class="mb-6" id="style_background_image" style="display:none">
                            <x-input-label for="background_image" :value="__('Image')" />
                            <input id="background_image" name="background_image" type="file" class="mt-1 block w-full"
                                :value="old('background_image')" oninput="updatePreview()" />
                            <x-input-error class="mt-2" :messages="$errors->get('background_image')" />

                            @if ($role->background_image_url)
                            <img src="{{ $role->background_image_url }}" style="max-height:120px" class="pt-3" />
                            @endif
                        </div>

                        <div id="style_background_gradient" style="display:none">
                            <div class="mb-6">
                                <x-input-label for="background_colors" :value="__('Colors')" />
                                <select id="background_colors" name="background_colors" oninput="updatePreview()"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    @foreach($gradients as $gradient => $name)
                                    <option value="{{ $gradient }}"
                                        {{ $role->background_colors == $gradient || (! array_key_exists($role->background_colors, $gradients) && ! $gradient) ? 'SELECTED' : '' }}>
                                        {{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('background_colors')" />

                                <div id="custom_colors" style="display:none">
                                    <x-text-input id="custom_color1" name="custom_color1" type="color"
                                        class="mt-1 block w-1/2" :value="old('custom_color1', $role->background_colors ? explode(', ', $role->background_colors)[0] : '')"
                                        oninput="updatePreview()" />
                                    <x-text-input id="custom_color2" name="custom_color2" type="color"
                                        class="mt-1 block w-1/2" :value="old('custom_color2', $role->background_colors ? explode(', ', $role->background_colors)[1] : '')"
                                        oninput="updatePreview()" />
                                </div>
                            </div>

                            <div class="mb-6">
                                <x-input-label for="background_rotation" :value="__('Rotation')" />
                                <x-text-input id="background_rotation" name="background_rotation" type="number"
                                    class="mt-1 block w-1/2" oninput="updatePreview()"
                                    :value="old('background_rotation', $role->background_rotation)" min="0" max="360" />
                                <x-input-error class="mt-2" :messages="$errors->get('background_rotation')" />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label :value="__('Preview')" />
                            <div id="preview"></div>
                        </div>
                    </div>

                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg" id="address">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Settings') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="visibility" :value="__('Visibility')" />
                            <select id="visibility" name="visibility"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach(['private', 'unlisted', 'public'] as $level)
                                <option value="{{ $level }}" {{ $role->visibility == $level ? 'SELECTED' : '' }}>
                                    {{ __(ucwords($level)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('visibility')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="accept_talent_requests" label="{{ __('Accept talent requests') }}"
                                checked="{{ old('accept_talent_requests', $role->accept_talent_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_talent_requests')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="accept_vendor_requests" label="{{ __('Accept vendor requests') }}"
                                checked="{{ old('accept_vendor_requests', $role->accept_vendor_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_vendor_requests')" />
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="use_24_hour_time" label="{{ __('Use 24-hour time format') }}"
                                checked="{{ old('use_24_hour_time', $role->use_24_hour_time) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('use_24_hour_time')" />
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6 mt-3">
            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </div>
        </div>

    </form>

</x-app-layout>