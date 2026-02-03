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

        .country-select {
            width: 100%;
        }
        
        /* Hide all sections except the first one by default */
        .section-content {
            display: none;
        }
        .section-content:first-of-type {
            display: block;
        }

        #preview {
            border: 1px solid #dbdbdb;
            border-radius: 8px;
            height: 140px;
            width: 100%;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            padding: 1rem;
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

        .section-nav-link.validation-error {
            border-inline-start-color: #dc2626 !important;
        }

        @media (prefers-color-scheme: dark) {
            .section-nav-link.validation-error {
                border-inline-start-color: #ef4444 !important;
            }
        }

        .dark .section-nav-link.validation-error {
            border-inline-start-color: #ef4444 !important;
        }

        /* Mobile accordion styles */
        .mobile-section-header.active .accordion-chevron {
            transform: rotate(180deg);
        }
        .mobile-section-header.active {
            color: #4E81FA;
            border-color: #4E81FA;
        }
        .mobile-section-header.validation-error {
            border-color: #dc2626 !important;
        }
        @media (prefers-color-scheme: dark) {
            .mobile-section-header.validation-error {
                border-color: #ef4444 !important;
            }
        }
        .dark .mobile-section-header.validation-error {
            border-color: #ef4444 !important;
        }

        </style>

        <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', () => {
            $("#country").countrySelect({
                defaultCountry: '{{ old('country_code', $role->country_code) }}',
            });
            $('#background').val('{{ old('background', $role->background) }}');
            $('#background_colors').val('{{ old('background_colors', $role->background_colors) }}');

            // If stored background_colors doesn't match any preset option, select "Custom"
            var $bgColors = $('#background_colors');
            var storedBgColors = '{{ old('background_colors', $role->background_colors) }}';
            if (storedBgColors && $bgColors.val() !== storedBgColors) {
                $bgColors.val('');
                // Pre-populate custom color inputs from stored values
                var storedColors = storedBgColors.split(', ');
                if (storedColors.length >= 1) $('#custom_color1').val(storedColors[0]);
                if (storedColors.length >= 2) $('#custom_color2').val(storedColors[1]);
            }

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
            updateFontNavButtons();
            
            // Handle accept_requests checkbox
            const acceptRequestsCheckbox = document.querySelector('input[name="accept_requests"][type="checkbox"]');
            const requireApprovalSection = document.getElementById('require_approval_section');
            const requestTermsSection = document.getElementById('request_terms_section');

            if (acceptRequestsCheckbox && requireApprovalSection) {
                requireApprovalSection.style.display = acceptRequestsCheckbox.checked ? 'block' : 'none';                
                acceptRequestsCheckbox.addEventListener('change', function() {
                    requireApprovalSection.style.display = this.checked ? 'block' : 'none';
                });
            }

            if (acceptRequestsCheckbox && requestTermsSection) {
                requestTermsSection.style.display = acceptRequestsCheckbox.checked ? 'block' : 'none';                
                acceptRequestsCheckbox.addEventListener('change', function() {
                    requestTermsSection.style.display = this.checked ? 'block' : 'none';
                });
            }

            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const warningElement = document.getElementById(previewId.split('_')[0] + '_image_size_warning');

                if (!input || !input.files || !input.files[0]) {
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
                            warningMessage += {!! json_encode(__('messages.image_size_warning'), JSON_UNESCAPED_UNICODE) !!};
                        }

                        if (width !== height && previewId == 'profile_image_preview') {
                            if (warningMessage) warningMessage += " ";
                            warningMessage += {!! json_encode(__('messages.image_not_square'), JSON_UNESCAPED_UNICODE) !!};
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
                if (headerImageUrl && headerImageUrl !== 'none') {
                    // Preset header selected
                    headerImageUrl = "{{ asset('images/headers/thumbs') }}" + '/' + headerImageUrl + '.jpg';
                    $('#header_image_preview').attr('src', headerImageUrl).show();
                    $('#delete_header_image_form').hide();
                } else if (headerImageUrl === '') {
                    // Custom option selected - show existing custom image if available
                    var existingCustomUrl = '{{ $role->header_image_url }}';
                    if (existingCustomUrl) {
                        $('#header_image_preview').attr('src', existingCustomUrl).show();
                        $('#delete_header_image_form').show();
                    }
                    // Don't hide preview - let file upload handler manage it
                } else {
                    // 'none' selected
                    $('#header_image_preview').hide();
                    $('#delete_header_image_form').hide();
                }
            });

            $('#header_image_url').on('change', function() {
                previewImage(this, 'header_image_preview');
                $('#header_image_preview').show();
                updatePreview();
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
            var font_family = $('#font_family').find(':selected').text();
            var link = document.createElement('link');

            link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(font_family.trim()) + ':wght@400;700&display=swap';
            link.rel = 'stylesheet';

            document.head.appendChild(link);

            link.onload = function() {
                updatePreview();
                $('#font_preview').css('font-family', "'" + font_family.trim() + "', sans-serif");
            };
        }

        function updatePreview() {
            var background = $('input[name="background"]:checked').val();
            var backgroundColor = $('#background_color').val();
            var backgroundColors = $('#background_colors').val();
            var backgroundRotation = $('#background_rotation').val();
            var fontColor = $('#font_color').val();
            var fontFamily = $('#font_family').find(':selected').val();
            var accentColor = $('#accent_color').val() || '#4E81FA';
            var name = $('#name').val();
            var headerImage = $('#header_image').val();
            var profileImagePreview = $('#profile_image_preview').attr('src');
            var existingProfileImage = '{{ $role->profile_image_url }}';

            if (! name) {
                name = {!! json_encode(__('messages.preview'), JSON_UNESCAPED_UNICODE) !!};
            } else if (name.length > 20) {
                name = name.substring(0, 20) + '...';
            }

            // Build header image HTML
            var headerHtml = '';
            if (headerImage && headerImage !== 'none' && headerImage !== '') {
                var headerUrl = "{{ asset('images/headers/thumbs') }}" + '/' + headerImage + '.jpg';
                headerHtml = '<div class="w-full h-16 bg-cover bg-center rounded-t-lg" style="background-image: url(\'' + headerUrl + '\')"></div>';
            } else if (headerImage === '') {
                // Custom header image
                var customHeaderUrl = $('#header_image_preview').attr('src') || '{{ $role->header_image_url }}';
                if (customHeaderUrl) {
                    headerHtml = '<div class="w-full h-16 bg-cover bg-center rounded-t-lg" style="background-image: url(\'' + customHeaderUrl + '\')"></div>';
                }
            }

            // Build profile image HTML
            var profileHtml = '';
            var profileSrc = profileImagePreview && profileImagePreview !== '#' ? profileImagePreview : existingProfileImage;
            if (profileSrc) {
                var marginTop = headerHtml ? '-mt-6' : '-mt-8';
                profileHtml = '<div class="' + marginTop + ' mb-2"><div class="w-12 h-12 rounded-lg bg-[#F5F9FE] p-0.5 shadow-sm"><img src="' + profileSrc + '" class="w-full h-full object-cover rounded-lg" /></div></div>';
            }

            // Build content HTML with accent color elements
            var contentTopPadding = !profileSrc && !headerHtml ? 'pt-3' : '';
            var contentHtml =
                '<div class="bg-[#F5F9FE] rounded-lg flex flex-col ' + (profileSrc && !headerHtml ? 'mt-8' : '') + '">' +
                    headerHtml +
                    '<div class="px-3 pb-3 flex flex-col">' +
                        profileHtml +
                        '<div class="flex items-center justify-between gap-2 ' + contentTopPadding + '">' +
                            '<div class="text-sm font-semibold text-[#151B26]" style="color: ' + fontColor + '; font-family: ' + fontFamily + ';">' + name + '</div>' +
                            '<div class="flex gap-1.5">' +
                                '<div class="w-6 h-6 rounded-md flex items-center justify-center shadow-sm" style="background-color: ' + accentColor + '">' +
                                    '<svg class="w-3 h-3" fill="white" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>' +
                                '</div>' +
                                '<div class="w-6 h-6 rounded-md flex items-center justify-center shadow-sm" style="background-color: ' + accentColor + '">' +
                                    '<svg class="w-3 h-3" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93z"/></svg>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            // Apply content to preview container
            var $preview = $('#preview');
            $preview.html(contentHtml);

            // Apply background styles
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

                $preview
                    .css('background-color', '')
                    .css('background-image', gradient);
            } else if (background == 'image') {

                var backgroundImageUrl = $('#background_image').find(':selected').val();
                if (backgroundImageUrl) {
                    backgroundImageUrl = "{{ asset('images/backgrounds/thumbs') }}" + '/' + $('#background_image').find(':selected').val() + '.jpg';
                } else {
                    backgroundImageUrl = $('#background_image_preview').attr('src') || "{{ $role->background_image_url }}";
                }

                $preview
                    .css('background-color', '')
                    .css('background-image', 'url("' + backgroundImageUrl + '")');
            } else {
                $preview.css('background-image', '')
                    .css('background-color', backgroundColor);
            }
        }

        function onValidateClick() {
            $('#address_response').text({!! json_encode(__('messages.searching'), JSON_UNESCAPED_UNICODE) !!} + '...').show();
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
                        var address = response['data']['formatted_address'];
                        $('#address_response').text(address);
                        $('#accept_button').show();
                        $('#address_response').data('validated_address', response['data']);
                    } else {
                        $('#address_response').text({!! json_encode(__('messages.address_not_found'), JSON_UNESCAPED_UNICODE) !!});    
                    }
                },
                error: function(xhr, status, error) {
                    $('#address_response').text({!! json_encode(__('messages.an_error_occurred'), JSON_UNESCAPED_UNICODE) !!});
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
                alert({!! json_encode(__('messages.please_enter_address'), JSON_UNESCAPED_UNICODE) !!});
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
            // Show custom input only for 'Custom' option (empty value), not for 'none' or presets
            customInput.style.display = select.value === '' ? 'block' : 'none';
        }

        function updateFontNavButtons() {
            const select = document.getElementById('font_family');
            const prevButton = document.getElementById('prev_font');
            const nextButton = document.getElementById('next_font');

            prevButton.disabled = select.selectedIndex === 0;
            nextButton.disabled = select.selectedIndex === select.options.length - 1;
        }

        function changeFont(direction) {
            const select = document.getElementById('font_family');
            const newIndex = select.selectedIndex + direction;

            if (newIndex >= 0 && newIndex < select.options.length) {
                select.selectedIndex = newIndex;
                onChangeFont();
                updateFontNavButtons();
            }
        }


        </script>

    </x-slot>

    <!-- Header with Cancel Button -->
    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <div class="hidden lg:flex items-center gap-3">
                <a href="{{ $role->exists ? route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) : route('home') }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
                </a>
            </div>

            <div class="text-end">
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ $title }}
                </h2>
            </div>
        @else
            <div>
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ $title }}
                </h2>
            </div>

            <div class="hidden lg:flex items-center gap-3">
                <a href="{{ $role->exists ? route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) : route('home') }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
                </a>
            </div>
        @endif
    </div>

    <form method="post"
        action="{{ $role->exists ? route('role.update', ['subdomain' => $role->subdomain]) : route('role.store') }}"
        enctype="multipart/form-data">

        @csrf
        @if($role->exists)
        @method('put')
        @endif

        <div class="py-5">
            <div class="mx-auto lg:grid lg:grid-cols-12 lg:gap-6">
                <!-- Sidebar Navigation (hidden on small screens, visible on lg+) -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-6">
                        <nav class="space-y-1">
                            <a href="#section-details" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-details">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                {{ __('messages.details') }}
                            </a>
                            @if ($role->isVenue())
                            <a href="#section-address" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-address">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ __('messages.venue_address') }}
                            </a>
                            @endif
                            <a href="#section-contact-info" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-contact-info">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                {{ __('messages.contact_info') }}
                            </a>
                            <a href="#section-style" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-style">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                </svg>
                                {{ __('messages.schedule_style') }}
                            </a>
                            <a href="#section-settings" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-settings">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ __('messages.schedule_settings') }}
                            </a>
                            @if (! config('app.hosted'))
                            <a href="#section-auto-import" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-auto-import">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                {{ __('messages.auto_import_settings') }}
                            </a>
                            @endif
                            @if (! $role->exists || $role->user_id == auth()->user()->id)
                            <a href="#section-integrations" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-integrations">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                </svg>
                                {{ __('messages.integrations') }}
                            </a>
                            @endif
                            @if (config('app.hosted'))
                            <a href="#section-email-settings" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-email-settings">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                {{ __('messages.email_settings') }}
                            </a>
                            @endif
                        </nav>
                        <!-- Sidebar Save Button -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <x-primary-button class="w-full justify-center">
                                {{ __('messages.save') }}
                            </x-primary-button>
                            @if (! $role->exists)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 flex items-center justify-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-4.247m0 0A8.959 8.959 0 0 1 3 12c0-1.178.227-2.304.638-3.335" />
                                </svg>
                                {{ __('messages.note_all_schedules_are_publicly_listed') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9 space-y-6 lg:space-y-0">
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-details">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        {{ __('messages.details') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-details" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
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

                        @if ($role->name_en)
                        <div class="mb-6">
                            <x-input-label for="name_en" :value="__('messages.name_en')" />
                            <x-text-input id="name_en" name="name_en" type="text" class="mt-1 block w-full"
                                :value="old('name_en', $role->name_en)" />
                            <x-input-error class="mt-2" :messages="$errors->get('name_en')" />
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.description')" />
                            <textarea id="description" name="description"
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('description', $role->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="mb-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                            <x-input-label for="language_code" :value="__('messages.language') " />
                            <select name="language_code" id="language_code" required {{ is_demo_mode() ? 'disabled' : '' }}
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
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

                        <div class="mb-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                            <x-input-label for="timezone" :value="__('messages.timezone')" />
                            <select name="timezone" id="timezone" required {{ is_demo_mode() ? 'disabled' : '' }}
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
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
                    </div>
                </div>

                @if ($role->isVenue())
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-address">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        {{ __('messages.venue_address') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-address" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
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
                            <x-input-label for="country" :value="__('messages.country')" />
                            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                                :value="old('country')" onchange="onChangeCountry()" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('country')" />
                            <input type="hidden" id="country_code" name="country_code" />
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

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-contact-info">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        {{ __('messages.contact_info') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-contact-info" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
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

                        @if ($role->isCurator())
                        <div class="mb-6">
                            <x-input-label for="city" :value="__('messages.city')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                :value="old('city', $role->city)" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>
                        @endif

                        @if ($role->isCurator() || $role->isTalent())
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

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-style">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                        </svg>
                        {{ __('messages.schedule_style') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-style" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div>

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                        </svg>
                        {{ __('messages.schedule_style') }}
                    </h2>

                    <div class="flex flex-col xl:flex-row xl:gap-12">
                        <div class="w-full xl:w-1/2">

                    <!-- Sub-Tab Navigation -->
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-2 sm:space-x-6">
                            <button type="button" onclick="showStyleTab('images')" id="style-tab-images"
                                class="style-tab-button flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-[#4E81FA] text-[#4E81FA]">
                                {{ __('messages.images') }}
                            </button>
                            <button type="button" onclick="showStyleTab('background')" id="style-tab-background"
                                class="style-tab-button flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300">
                                {{ __('messages.background') }}
                            </button>
                            <button type="button" onclick="showStyleTab('settings')" id="style-tab-settings"
                                class="style-tab-button flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300">
                                {{ __('messages.settings') }}
                            </button>
                        </nav>
                    </div>


                    <!-- Images Tab Content -->
                    <div id="style-content-images">
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
                                <button type="button"
                                    onclick="deleteRoleImage('{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'profile']) }}', '{{ csrf_token() }}')"
                                    class="hover:underline text-gray-900 dark:text-gray-100">
                                    {{ __('messages.delete_image') }}
                                </button>
                                @endif
                            </div>

                            <div class="mb-6">
                                <x-input-label for="header_image" :value="__('messages.header_image')" />
                                <select id="header_image" name="header_image"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                    oninput="updatePreview(); updateHeaderNavButtons(); toggleCustomHeaderInput();">
                                    <option value="none" {{ $role->header_image == 'none' || (!$role->header_image && !$role->header_image_url) ? 'SELECTED' : '' }}>
                                        {{ __('messages.none') }}</option>
                                    @foreach($headers as $header => $name)
                                    <option value="{{ $header }}"
                                        {{ $role->header_image == $header ? 'SELECTED' : '' }}>
                                        {{ $name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex gap-2 mt-2">
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

                                <div id="custom_header_input" style="display:none" class="mt-4">
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
                                    src="{{ $role->header_image && $role->header_image !== 'none' ? asset('images/headers/' . $role->header_image . '.png') : $role->header_image_url }}"
                                    alt="Header Image Preview"
                                    style="max-height:120px; {{ ($role->header_image && $role->header_image !== 'none') || $role->header_image_url ? '' : 'display:none;' }}"
                                    class="pt-3" />

                                @if ($role->header_image_url)
                                <button type="button" id="delete_header_image_button"
                                    style="display: {{ $role->header_image ? 'none' : 'block' }};"
                                    onclick="deleteRoleImage('{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'header']) }}', '{{ csrf_token() }}')"
                                    class="hover:underline text-gray-900 dark:text-gray-100">
                                    {{ __('messages.delete_image') }}
                                </button>
                                @endif

                            </div>
                    </div>

                    <!-- Background Tab Content -->
                    <div id="style-content-background" style="display: none;">
                            <div class="mb-6">
                                <x-input-label :value="__('messages.background_type')" />
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
                                        <label for="background_type_{{ $background }}" class="ms-2 text-gray-900 dark:text-gray-100">
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
                                <select id="background_image" name="background_image"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                    oninput="onChangeBackground(); updatePreview(); updateImageNavButtons(); toggleCustomImageInput();">
                                    @foreach($backgrounds as $background => $name)
                                    <option value="{{ $background }}"
                                        {{ $role->background_image == $background ? 'SELECTED' : '' }}>
                                        {{ $name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex gap-2 mt-2">
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
                                    <button type="button"
                                        onclick="deleteRoleImage('{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'background']) }}', '{{ csrf_token() }}')"
                                        class="hover:underline text-gray-900 dark:text-gray-100">
                                        {{ __('messages.delete_image') }}
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <div id="style_background_gradient" style="display:none">
                                <div class="mb-6">
                                    <x-input-label for="background_colors" :value="__('messages.colors')" />
                                    <select id="background_colors" name="background_colors" oninput="updatePreview(); updateColorNavButtons()"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach($gradients as $gradient => $name)
                                        <option value="{{ $gradient }}"
                                            {{ $role->background_colors == $gradient || (! array_key_exists($role->background_colors, $gradients) && ! $gradient) ? 'SELECTED' : '' }}>
                                            {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex gap-2 mt-2">
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
                                        <x-link href="https://uigradients.com" target="_blank">{{ __('messages.gradients_from', ['name' => 'uiGradients']) }}</x-link>
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
                                    <div class="flex items-center gap-3 mt-1">
                                        <input id="background_rotation" name="background_rotation" type="range"
                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                            oninput="updatePreview(); document.getElementById('rotation_value').textContent = this.value + '°'"
                                            value="{{ old('background_rotation', $role->background_rotation ?? 0) }}" min="0" max="360" />
                                        <span id="rotation_value" class="text-sm text-gray-600 dark:text-gray-400 w-12 text-end">{{ old('background_rotation', $role->background_rotation ?? 0) }}°</span>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('background_rotation')" />
                                </div>
                            </div>
                    </div>

                    <!-- Settings Tab Content -->
                    <div id="style-content-settings" style="display: none;">
                            <div class="mb-6">
                                <x-input-label for="accent_color" :value="__('messages.accent_color')" />
                                <x-text-input id="accent_color" name="accent_color" type="color" class="mt-1 block w-1/2"
                                    :value="old('accent_color', $role->accent_color)" oninput="updatePreview()" />
                                <x-input-error class="mt-2" :messages="$errors->get('accent_color')" />
                            </div>

                            <div class="mb-6">
                                <x-input-label for="font_family" :value="__('messages.font_family')" />
                                <select id="font_family" name="font_family" onchange="onChangeFont(); updateFontNavButtons();"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                    @foreach($fonts as $font)
                                    <option value="{{ $font->value }}"
                                        {{ $role->font_family == $font->value ? 'SELECTED' : '' }}>
                                        {{ $font->label }}</option>
                                    @endforeach
                                </select>
                                <div class="flex gap-2 mt-2">
                                    <button type="button"
                                            id="prev_font"
                                            class="color-nav-button"
                                            onclick="changeFont(-1)"
                                            title="{{ __('messages.previous') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                            id="next_font"
                                            class="color-nav-button"
                                            onclick="changeFont(1)"
                                            title="{{ __('messages.next') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('font_family')" />
                                <div id="font_preview" class="mt-3 text-2xl text-gray-900 dark:text-gray-100" style="font-family: '{{ $role->font_family }}', sans-serif;">
                                    {{ $role->name }}
                                </div>
                            </div>

                            <div class="mb-6">
                                <x-input-label :value="__('messages.default_layout')" />
                                <div class="mt-2 space-y-2">
                                    @foreach(['calendar', 'list'] as $layout)
                                    <div class="flex items-center">
                                        <input type="radio"
                                            id="event_layout_{{ $layout }}"
                                            name="event_layout"
                                            value="{{ $layout }}"
                                            {{ $role->event_layout == $layout ? 'checked' : '' }}
                                            class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                        <label for="event_layout_{{ $layout }}" class="ms-2 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.' . $layout) }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('event_layout')" />
                            </div>

                            <div class="mb-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                                <x-input-label for="custom_css" :value="__('messages.custom_css')" />
                                <textarea id="custom_css" name="custom_css" {{ is_demo_mode() ? 'disabled' : '' }}
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm font-mono text-sm"
                                    rows="6">{{ old('custom_css', $role->custom_css) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('custom_css')" />
                                @if (is_demo_mode())
                                <p class="mt-1 text-sm text-yellow-600 dark:text-yellow-400">{{ __('messages.demo_mode_settings_disabled') }}</p>
                                @endif
                            </div>
                    </div>

                        </div>

                        <!-- Preview (always visible, right column on desktop) -->
                        <div class="w-full xl:w-1/2 mt-6 xl:mt-0">
                            <x-input-label :value="__('messages.preview')" />
                            <div id="preview" class="h-[140px] w-full"></div>
                        </div>
                    </div>

                    </div>
                </div>

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-settings">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('messages.schedule_settings') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-settings" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('messages.schedule_settings') }}
                        </h2>

                        @if (is_demo_mode())
                        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
                            {{ __('messages.demo_mode_settings_disabled') }}
                        </div>
                        @endif

                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-4" aria-label="Tabs">
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[#4E81FA] text-[#4E81FA]" data-tab="general">
                                    {{ __('messages.general') }}
                                </button>
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="custom-fields">
                                    {{ __('messages.custom_fields') }}
                                </button>
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="subschedules">
                                    {{ __('messages.subschedules') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: General -->
                        <div id="settings-tab-general" class="settings-tab-content">

                        @if ($role->exists)
                        <div class="mb-6" id="url-display">
                            <x-input-label :value="__('messages.schedule_url')" />
                            <p class="text-sm text-gray-500 flex items-center gap-2 mt-1">
                                <x-link href="{{ $role->getGuestUrl() }}" target="_blank">
                                    {{ \App\Utils\UrlUtils::clean($role->getGuestUrl()) }}
                                </x-link>
                                <button type="button" onclick="copyRoleUrl(this)" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                    </svg>
                                </button>
                                </button>
                            </p>
                            <x-secondary-button type="button" onclick="toggleSubdomainEdit()" class="mt-3">
                                {{ __('messages.edit') }}
                            </x-secondary-button>
                        </div>
                        @if (!is_demo_mode())
                        <div class="hidden" id="subdomain-edit">
                            <div class="mb-6">
                                <x-input-label for="new_subdomain" :value="__('messages.subdomain')" />
                                <x-text-input id="new_subdomain" name="new_subdomain" type="text" class="mt-1 block w-full"
                                    :value="old('new_subdomain', $role->subdomain)" required minlength="4" maxlength="50"
                                    pattern="[a-z0-9-]+" oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')" />
                                <x-input-error class="mt-2" :messages="$errors->get('new_subdomain')" />
                            </div>

                            <div>
                                <x-input-label for="custom_domain" :value="__('messages.custom_domain')" />
                                <x-text-input id="custom_domain" name="custom_domain" type="url" class="mt-1 block w-full"
                                    :value="old('custom_domain', $role->custom_domain)" />
                                <x-input-error class="mt-2" :messages="$errors->get('custom_domain')" />
                            </div>

                            <x-secondary-button type="button" onclick="toggleSubdomainEdit()" class="mt-3 mb-6">
                                {{ __('messages.cancel') }}
                            </x-secondary-button>
                        </div>
                        @endif
                        @endif

                        <div class="mb-6">
                            <x-input-label for="slug_pattern" :value="__('messages.slug_pattern')" />
                            <x-text-input id="slug_pattern" name="slug_pattern" type="text" class="mt-1 block w-full"
                                :value="old('slug_pattern', $role->slug_pattern)"
                                placeholder="{event_name}" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.slug_pattern_help') }}</p>

                            <x-link href="{{ marketing_url('/docs/schedule-basics#slug-variables') }}" target="_blank" class="text-sm mt-2">
                                {{ __('messages.show_available_variables') }}
                            </x-link>
                            <x-input-error class="mt-2" :messages="$errors->get('slug_pattern')" />
                        </div>

                        @if ((config('app.hosted') || config('app.is_testing')) && ($role->isVenue() || $role->isCurator()))
                        <div class="mb-6">
                            <x-checkbox name="accept_requests"
                                label="{{ __('messages.accept_requests') }}"
                                checked="{{ old('accept_requests', $role->accept_requests) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_requests')" />
                        </div>
                        <div class="mb-6" id="require_approval_section">
                            <x-checkbox name="require_approval"
                                label="{{ __('messages.require_approval') }}"
                                checked="{{ old('require_approval', $role->exists ? $role->require_approval : true) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('require_approval')" />
                        </div>
                        <div class="mb-6" id="request_terms_section">
                            <x-input-label for="request_terms" :value="__('messages.request_terms')" />
                            <textarea id="request_terms" name="request_terms"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                rows="4"
                                dir="auto"
                                placeholder="{{ __('messages.enter_request_terms') }}">{{ old('request_terms', $role->request_terms) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('request_terms')" />
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
                        <!-- End Tab Content: General -->

                        <!-- Tab Content: Custom Fields -->
                        <div id="settings-tab-custom-fields" class="settings-tab-content hidden">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('messages.event_custom_fields_help') }}
                        </p>

                        <div id="event-custom-fields-container">
                            <input type="hidden" name="event_custom_fields_submitted" value="1">
                            @php
                                $eventCustomFields = $role->event_custom_fields ?? [];
                                $fieldIndex = 0;
                            @endphp
                            @foreach($eventCustomFields as $fieldKey => $field)
                            <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg event-custom-field-item" data-field-key="{{ $fieldKey }}">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label :value="__('messages.field_name') . ' *'" class="text-sm" />
                                        <x-text-input type="text" name="event_custom_fields[{{ $fieldKey }}][name]"
                                            value="{{ $field['name'] ?? '' }}"
                                            class="mt-1 block w-full" required />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('messages.field_type')" class="text-sm" />
                                        <select name="event_custom_fields[{{ $fieldKey }}][type]"
                                            onchange="toggleEventFieldOptions(this)"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                            <option value="string" {{ ($field['type'] ?? 'string') === 'string' ? 'selected' : '' }}>{{ __('messages.type_string') }}</option>
                                            <option value="multiline_string" {{ ($field['type'] ?? '') === 'multiline_string' ? 'selected' : '' }}>{{ __('messages.type_multiline_string') }}</option>
                                            <option value="switch" {{ ($field['type'] ?? '') === 'switch' ? 'selected' : '' }}>{{ __('messages.type_switch') }}</option>
                                            <option value="date" {{ ($field['type'] ?? '') === 'date' ? 'selected' : '' }}>{{ __('messages.type_date') }}</option>
                                            <option value="dropdown" {{ ($field['type'] ?? '') === 'dropdown' ? 'selected' : '' }}>{{ __('messages.type_dropdown') }}</option>
                                        </select>
                                    </div>
                                </div>
                                @if($role->language_code !== 'en')
                                <div class="mt-3">
                                    <x-input-label :value="__('messages.english_name')" class="text-sm" />
                                    <x-text-input type="text" name="event_custom_fields[{{ $fieldKey }}][name_en]"
                                        value="{{ $field['name_en'] ?? '' }}"
                                        class="mt-1 block w-full"
                                        :placeholder="__('messages.auto_translated_placeholder')" />
                                </div>
                                @endif
                                <div class="mt-3 event-field-options-container" style="{{ ($field['type'] ?? '') === 'dropdown' ? '' : 'display: none;' }}">
                                    <x-input-label :value="__('messages.field_options')" class="text-sm" />
                                    <x-text-input type="text" name="event_custom_fields[{{ $fieldKey }}][options]"
                                        value="{{ $field['options'] ?? '' }}"
                                        class="mt-1 block w-full"
                                        :placeholder="__('messages.options_placeholder')" />
                                </div>
                                <div class="mt-3">
                                    <x-input-label :value="__('messages.ai_prompt_custom_field')" class="text-sm" />
                                    <textarea name="event_custom_fields[{{ $fieldKey }}][ai_prompt]"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm text-sm ai-prompt-textarea"
                                        rows="2"
                                        maxlength="500">{{ $field['ai_prompt'] ?? '' }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.ai_prompt_custom_field_help') }}</p>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="event_custom_fields[{{ $fieldKey }}][required]"
                                            id="event_field_required_{{ $fieldKey }}"
                                            value="1"
                                            {{ !empty($field['required']) ? 'checked' : '' }}
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                        <label for="event_field_required_{{ $fieldKey }}" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                    </div>
                                    <x-secondary-button type="button" onclick="removeEventCustomField(this)" class="text-xs py-1 px-2">
                                        {{ __('messages.remove') }}
                                    </x-secondary-button>
                                </div>
                            </div>
                            @php $fieldIndex++; @endphp
                            @endforeach
                        </div>

                        <x-secondary-button type="button" onclick="addEventCustomField()" id="add-event-custom-field-btn" class="{{ count($eventCustomFields) >= 8 ? 'hidden' : '' }}">
                            {{ __('messages.add_field') }}
                        </x-secondary-button>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('messages.event_custom_fields_graphic_help') }}
                        </p>
                        </div>
                        <!-- End Tab Content: Custom Fields -->

                        <!-- Tab Content: Subschedules -->
                        <div id="settings-tab-subschedules" class="settings-tab-content hidden">
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.subschedules_help') }}</p>
                        <div class="mb-6">
                            <div id="groups-list">
                                @php $groups = $role->groups ?? []; @endphp
                                <div id="group-items">
                                    @foreach(old('groups', $groups) as $i => $group)
                                        <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                            <div class="mb-4">
                                                <x-input-label for="group_name_{{ is_object($group) ? $group->id : $i }}" :value="__('messages.name') . ' *'" />
                                                <x-text-input name="groups[{{ is_object($group) ? $group->id : $i }}][name]" type="text" class="mt-1 block w-full" :value="is_object($group) ? $group->name : $group['name'] ?? ''" />
                                            </div>
                                            @if($role->language_code !== 'en' || app()->getLocale() !== 'en')
                                            <div class="mb-4">
                                                <x-input-label for="group_name_en_{{ is_object($group) ? $group->id : $i }}" :value="__('messages.english_name')" />
                                                <x-text-input name="groups[{{ is_object($group) ? $group->id : $i }}][name_en]" type="text" class="mt-1 block w-full" :value="is_object($group) ? $group->name_en : $group['name_en'] ?? ''" />
                                            </div>
                                            @endif
                                            @if((is_object($group) && $group->slug) || (is_array($group) && !empty($group['slug'])))
                                            <div class="mb-4" id="group-url-display-{{ is_object($group) ? $group->id : $i }}">
                                                <p class="text-sm text-gray-500 flex items-center gap-2">
                                                    <x-link href="{{ $role->getGuestUrl() }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}" target="_blank">
                                                        {{ \App\Utils\UrlUtils::clean($role->getGuestUrl()) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}
                                                    </x-link>
                                                    <button type="button" onclick="copyGroupUrl(this, '{{ $role->getGuestUrl() }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}')" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                                        </svg>
                                                    </button>
                                                </p>
                                            </div>
                                            <div class="mb-4 {{ (is_object($group) && $group->slug) || (is_array($group) && !empty($group['slug'])) ? 'hidden' : '' }}" id="group-slug-edit-{{ is_object($group) ? $group->id : $i }}">
                                                <x-input-label for="group_slug_{{ is_object($group) ? $group->id : $i }}" :value="__('messages.slug')" />
                                                <x-text-input name="groups[{{ is_object($group) ? $group->id : $i }}][slug]" type="text" class="mt-1 block w-full" :value="is_object($group) ? $group->slug : $group['slug'] ?? ''" />
                                            </div>
                                            <div class="flex gap-4 items-center justify-between">
                                                <div class="flex gap-4 items-center">
                                                    <x-secondary-button type="button" onclick="toggleGroupSlugEdit('{{ is_object($group) ? $group->id : $i }}')" id="edit-button-{{ is_object($group) ? $group->id : $i }}">
                                                        {{ __('messages.edit') }}
                                                    </x-secondary-button>
                                                    @if((is_object($group) && $group->slug) || (is_array($group) && !empty($group['slug'])))
                                                    <x-secondary-button type="button" onclick="toggleGroupSlugEdit('{{ is_object($group) ? $group->id : $i }}')" class="hidden" id="cancel-button-{{ is_object($group) ? $group->id : $i }}">
                                                        {{ __('messages.cancel') }}
                                                    </x-secondary-button>
                                                    @endif
                                                </div>
                                                <x-secondary-button onclick="this.parentElement.parentElement.remove()" type="button">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                            @else
                                            <div class="flex gap-4 items-center justify-end">
                                                <x-secondary-button onclick="this.parentElement.parentElement.remove()" type="button">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <x-secondary-button type="button" onclick="addGroupField()">
                                    {{ __('messages.add_subschedule') }}
                                </x-secondary-button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('groups')" />
                        </div>
                        </div>
                        <!-- End Tab Content: Subschedules -->

                    </div>
                </div>

                @if (! config('app.hosted'))
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-auto-import">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        {{ __('messages.auto_import_settings') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-auto-import" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            {{ __('messages.auto_import_settings') }}
                        </h2>

                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">
                                {{ __('messages.import_urls') }}
                            </h3>
                            <div id="import-urls-list">
                                <div id="import-url-items">
                                    @php $urls = $role->import_config['urls'] ?? []; @endphp
                                    @foreach(old('import_urls', $urls) as $i => $url)
                                        <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                            <div class="mb-4">
                                                <x-input-label for="import_url_{{ $i }}" :value="__('messages.url')" />
                                                <x-text-input name="import_urls[]" type="url" class="mt-1 block w-full" :value="$url" />
                                            </div>
                                            <div class="flex gap-4 items-center">
                                                <x-secondary-button onclick="this.parentElement.parentElement.remove()" type="button">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <x-secondary-button type="button" onclick="addImportUrlField()">
                                    {{ __('messages.add') }}
                                </x-secondary-button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('import_urls')" />
                        </div>

                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">
                                {{ __('messages.import_cities') }}
                            </h3>
                            <div id="import-cities-list">
                                <div id="import-city-items">
                                    @php $cities = $role->import_config['cities'] ?? []; @endphp
                                    @foreach(old('import_cities', $cities) as $i => $city)
                                        <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                            <div class="mb-4">
                                                <x-input-label for="import_city_{{ $i }}" :value="__('messages.city')" />
                                                <x-text-input name="import_cities[]" type="text" class="mt-1 block w-full" :value="$city" placeholder="{{ __('messages.placeholder_city') }}" />
                                            </div>
                                            <div class="flex gap-4 items-center">
                                                <x-secondary-button onclick="this.parentElement.parentElement.remove()" type="button">
                                                    {{ __('messages.remove') }}
                                                </x-secondary-button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <x-secondary-button type="button" onclick="addImportCityField()">
                                    {{ __('messages.add') }}
                                </x-secondary-button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('import_cities')" />
                        </div>

                        @if ($role->exists)
                        <div class="mb-6">
                            <x-secondary-button onclick="testImport(this)" type="button">
                                {{ __('messages.test_import') }}
                            </x-secondary-button>
                        </div>
                        @endif
                        
                    </div>
                </div>
                @endif

                @if (! $role->exists || $role->user_id == auth()->user()->id)
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-integrations">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                        {{ __('messages.integrations') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-integrations" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                            {{ __('messages.integrations') }}
                        </h2>

                        @if (is_demo_mode())
                        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
                            {{ __('messages.demo_mode_settings_disabled') }}
                        </div>
                        @endif

                        <div>

                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-4" aria-label="Tabs">
                                <button type="button" class="integration-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-[#4E81FA] text-[#4E81FA]" data-tab="google">
                                    Google Calendar
                                </button>
                                <button type="button" class="integration-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="caldav">
                                    {{ __('messages.caldav_calendar') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: Google Calendar -->
                        <div id="integration-tab-google" class="integration-tab-content">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                {{ __('messages.sync_events_between_schedules') }}
                            </p>

                            @if (auth()->user()->google_token)
                            <div class="space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                                <!-- Calendar Selection -->
                                <div>
                                    <x-input-label for="google-calendar-select" :value="__('messages.select_google_calendar')" />
                                    <select id="google-calendar-select" name="google_calendar_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="">{{ __('messages.loading_calendars') }}</option>
                                    </select>
                                </div>

                                <!-- Sync Direction Selection -->
                                <div>
                                    <x-input-label :value="__('messages.sync_direction')" />
                                    <div class="mt-2 space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="sync_direction"
                                                   value="to"
                                                   {{ $role->sync_direction === 'to' ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.to_google_calendar') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.to_google_calendar_description') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="sync_direction"
                                                   value="from"
                                                   {{ $role->sync_direction === 'from' ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.from_google_calendar') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.from_google_calendar_description') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="sync_direction"
                                                   value="both"
                                                   {{ $role->sync_direction === 'both' ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.bidirectional_sync') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.bidirectional_sync_description') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="sync_direction"
                                                   value=""
                                                   {{ !$role->sync_direction ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.no_sync') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.no_sync_description') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                @if (false)
                                <div>
                                    <x-secondary-button type="button" onclick="syncEvents()" id="sync-events-button">
                                        {{ __('messages.sync_events') }}
                                    </x-secondary-button>
                                </div>

                                <div id="sync-status" class="hidden">
                                    <div class="flex items-center text-blue-600 dark:text-blue-400">
                                        <svg class="animate-spin -ms-1 me-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm">{{ __('messages.syncing') }}</span>
                                    </div>
                                </div>

                                <div id="sync-results" class="hidden">
                                    <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                        <div class="text-sm text-green-800 dark:text-green-200">
                                            <div id="sync-message"></div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @else
                            <x-link href="{{ route('profile.edit') }}#section-google-calendar" target="_blank">
                                {{ __('messages.connect_google_calendar') }}
                            </x-link>
                            @endif
                        </div>

                        <!-- Tab Content: CalDAV -->
                        <div id="integration-tab-caldav" class="integration-tab-content hidden {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                {{ __('messages.caldav_description') }}
                            </p>

                            @php
                                $caldavSettings = $role->getCalDAVSettings();
                            @endphp

                            @if ($role->hasCalDAVSettings())
                            <div class="space-y-6">
                                <!-- Connected Server Info -->
                                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                    <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">{{ __('messages.connected') }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-green-700 dark:text-green-300">
                                        {{ parse_url($caldavSettings['server_url'] ?? '', PHP_URL_HOST) }}
                                    </p>
                                </div>

                                <!-- Sync Direction Selection -->
                                <div>
                                    <x-input-label :value="__('messages.sync_direction')" />
                                    <div class="mt-2 space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="caldav_sync_direction"
                                                   value="to"
                                                   {{ $role->caldav_sync_direction === 'to' ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.to_caldav') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.to_caldav_description') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="caldav_sync_direction"
                                                   value="from"
                                                   {{ $role->caldav_sync_direction === 'from' ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.from_caldav') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.from_caldav_description') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="caldav_sync_direction"
                                                   value="both"
                                                   {{ $role->caldav_sync_direction === 'both' ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.bidirectional_sync') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.bidirectional_sync_description') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio"
                                                   name="caldav_sync_direction"
                                                   value=""
                                                   {{ !$role->caldav_sync_direction ? 'checked' : '' }}
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.no_sync') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.no_sync_description') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Disconnect Button -->
                                <div>
                                    <x-danger-button type="button" id="caldav-disconnect-btn">
                                        {{ __('messages.disconnect') }}
                                    </x-danger-button>
                                </div>
                            </div>
                            @else
                            <!-- Connection Form -->
                            <div class="space-y-6" id="caldav-connection-form">
                                <div>
                                    <x-input-label for="caldav_server_url" :value="__('messages.caldav_server_url')" />
                                    <x-text-input id="caldav_server_url" type="url" class="mt-1 block w-full" placeholder="https://caldav.example.com" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('messages.caldav_server_url_help') }}
                                    </p>
                                </div>

                                <div>
                                    <x-input-label for="caldav_username" :value="__('messages.caldav_username')" />
                                    <x-text-input id="caldav_username" type="text" class="mt-1 block w-full" autocomplete="off" />
                                </div>

                                <div>
                                    <x-input-label for="caldav_password" :value="__('messages.caldav_password')" />
                                    <x-password-input id="caldav_password" class="mt-1 block w-full" autocomplete="new-password" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('messages.caldav_password_help') }}
                                    </p>
                                </div>

                                <div>
                                    <x-secondary-button type="button" id="caldav-test-btn">
                                        {{ __('messages.test_connection') }}
                                    </x-secondary-button>
                                </div>

                                <div id="caldav-test-result" class="hidden"></div>

                                <div id="caldav-calendar-select-container" class="hidden">
                                    <x-input-label for="caldav_calendar_url" :value="__('messages.select_calendar')" />
                                    <select id="caldav_calendar_url" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="">{{ __('messages.select_a_calendar') }}</option>
                                    </select>
                                </div>

                                <div id="caldav-sync-direction-container" class="hidden">
                                    <x-input-label :value="__('messages.sync_direction')" />
                                    <div class="mt-2 space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="caldav_sync_direction_new" value="to" checked
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.to_caldav') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="caldav_sync_direction_new" value="from"
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.from_caldav') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="caldav_sync_direction_new" value="both"
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.bidirectional_sync') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div id="caldav-connect-container" class="hidden">
                                    <x-primary-button type="button" id="caldav-connect-btn">
                                        {{ __('messages.connect') }}
                                    </x-primary-button>
                                </div>
                            </div>
                            @endif
                        </div>

                        </div>
                    </div>
                </div>
                @endif

                @if (config('app.hosted'))
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-email-settings">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        {{ __('messages.email_settings') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-email-settings" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    {{ __('messages.email_settings') }}
                </h2>

                @if (is_demo_mode())
                <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
                    {{ __('messages.demo_mode_settings_disabled') }}
                </div>
                @endif

                <div class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">

                @php
                    $emailSettings = $role->getEmailSettings();
                @endphp

                <div class="mb-6">
                    <x-input-label for="email_settings_host" :value="__('messages.smtp_host')" />
                    <x-text-input id="email_settings_host" name="email_settings[host]" type="text" class="mt-1 block w-full"
                        :value="old('email_settings.host', $emailSettings['host'] ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.host')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="email_settings_port" :value="__('messages.smtp_port')" />
                    <x-text-input id="email_settings_port" name="email_settings[port]" type="number" class="mt-1 block w-full"
                        :value="old('email_settings.port', $emailSettings['port'] ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.port')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="email_settings_encryption" :value="__('messages.encryption')" />
                    <select id="email_settings_encryption" name="email_settings[encryption]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                        <option value="">{{ __('messages.none') }}</option>
                        <option value="tls" {{ old('email_settings.encryption', $emailSettings['encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('email_settings.encryption', $emailSettings['encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.encryption')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="email_settings_username" :value="__('messages.smtp_username')" />
                    <x-text-input id="email_settings_username" name="email_settings[username]" type="text" class="mt-1 block w-full"
                        :value="old('email_settings.username', $emailSettings['username'] ?? '')" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.username')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="email_settings_password" :value="__('messages.smtp_password')" />
                    <x-password-input id="email_settings_password" name="email_settings[password]" class="mt-1 block w-full"
                        :value="old('email_settings.password', !empty($emailSettings['password']) ? '••••••••••' : '')"
                        autocomplete="new-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.password')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="email_settings_from_address" :value="__('messages.from_address')" />
                    <x-text-input id="email_settings_from_address" name="email_settings[from_address]" type="email" class="mt-1 block w-full"
                        :value="old('email_settings.from_address', $emailSettings['from_address'] ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.from_address')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="email_settings_from_name" :value="__('messages.from_name')" />
                    <x-text-input id="email_settings_from_name" name="email_settings[from_name]" type="text" class="mt-1 block w-full"
                        :value="old('email_settings.from_name', $emailSettings['from_name'] ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('email_settings.from_name')" />
                </div>

                @if ($role->exists && $role->subdomain)
                <div class="mb-6">
                    <x-primary-button type="button" id="send-test-email-btn">
                        {{ __('messages.send_test_email') }}
                    </x-primary-button>
                    <div id="test-email-result" class="mt-2 hidden"></div>
                </div>
                @endif

                </div>
                    </div>
                </div>
                @endif
                    
                </div> <!-- End of main content area -->
            </div> <!-- End of grid container -->

        <!-- Spacer for mobile fixed buttons -->
        <div class="lg:hidden h-24"></div>

        <!-- Mobile Fixed Save Bar -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-5 py-3 z-40 shadow-lg"
             style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            @if (! $role->exists)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 flex items-center justify-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-4.247m0 0A8.959 8.959 0 0 1 3 12c0-1.178.227-2.304.638-3.335" />
                </svg>
                {{ __('messages.note_all_schedules_are_publicly_listed') }}
            </p>
            @endif
            <div class="flex gap-3 justify-center max-w-lg mx-auto">
                <x-primary-button class="flex-1 justify-center">
                    {{ __('messages.save') }}
                </x-primary-button>
                <x-cancel-button class="flex-1 justify-center" />
            </div>
        </div>

    </form>

</x-app-admin-layout>

<script {!! nonce_attr() !!}>
// Prevent browser scroll restoration and scroll to top immediately
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);
document.documentElement.scrollTop = 0;
if (document.body) {
    document.body.scrollTop = 0;
}

// Style sub-tab navigation
function showStyleTab(tabName) {
    // Hide all style tab contents
    const tabContents = ['images', 'background', 'settings'];
    tabContents.forEach(function(tab) {
        const content = document.getElementById('style-content-' + tab);
        if (content) {
            content.style.display = tab === tabName ? 'block' : 'none';
        }
    });

    // Update tab button styles
    const tabButtons = document.querySelectorAll('.style-tab-button');
    tabButtons.forEach(function(button) {
        const isActive = button.id === 'style-tab-' + tabName;
        if (isActive) {
            button.classList.add('border-[#4E81FA]', 'text-[#4E81FA]');
            button.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        } else {
            button.classList.remove('border-[#4E81FA]', 'text-[#4E81FA]');
            button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }
    });

    localStorage.setItem('styleActiveTab', tabName);
}

// Restore active style tab from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedStyleTab = localStorage.getItem('styleActiveTab');
    if (savedStyleTab) {
        showStyleTab(savedStyleTab);
    }
});

function addGroupField() {
    const container = document.getElementById('group-items');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg';
    div.innerHTML = `
        <div class="mb-4">
            <label for="group_name_new_${idx}" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.name') }} *</label>
            <input name="groups[new_${idx}][name]" type="text" id="group_name_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
        </div>
        @if($role->language_code !== 'en' || app()->getLocale() !== 'en')
        <div class="mb-4">
            <label for="group_name_en_new_${idx}" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.english_name') }}</label>
            <input name="groups[new_${idx}][name_en]" type="text" id="group_name_en_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
        </div>
        @endif
        <div class="flex gap-4 items-center justify-end">
            <button type="button" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150" onclick="this.parentElement.parentElement.remove()">
                {{ __('messages.remove') }}
            </button>
        </div>
    `;
    container.appendChild(div);
}

function copyRoleUrl(button) {
    const url = '{{ $role->exists ? $role->getGuestUrl() : "" }}';
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

function toggleSubdomainEdit() {
    const urlDisplay = document.getElementById('url-display');
    const subdomainEdit = document.getElementById('subdomain-edit');
    
    if (urlDisplay.classList.contains('hidden')) {
        urlDisplay.classList.remove('hidden');
        subdomainEdit.classList.add('hidden');
    } else {
        urlDisplay.classList.add('hidden');
        subdomainEdit.classList.remove('hidden');
        document.getElementById('new_subdomain').focus();
    }
}

function copyGroupUrl(button, url) {
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

function toggleGroupSlugEdit(groupId) {
    const urlDisplay = document.getElementById(`group-url-display-${groupId}`);
    const slugEdit = document.getElementById(`group-slug-edit-${groupId}`);
    const cancelButton = document.getElementById(`cancel-button-${groupId}`);
    const editButton = document.getElementById(`edit-button-${groupId}`);
    
    if (urlDisplay.classList.contains('hidden')) {
        urlDisplay.classList.remove('hidden');
        slugEdit.classList.add('hidden');
        if (cancelButton) {
            cancelButton.classList.add('hidden');
        }
        if (editButton) {
            editButton.classList.remove('hidden');
        }
    } else {
        urlDisplay.classList.add('hidden');
        slugEdit.classList.remove('hidden');
        if (cancelButton) {
            cancelButton.classList.remove('hidden');
        }
        if (editButton) {
            editButton.classList.add('hidden');
        }
        document.getElementById(`group_slug_${groupId}`).focus();
    }
}

function testImport() {
    // Collect URLs from the new structure
    const urlInputs = document.querySelectorAll('input[name^="import_urls["]');
    const urls = Array.from(urlInputs).map(input => input.value.trim()).filter(url => url);
    
    // Collect cities from the new structure
    const cityInputs = document.querySelectorAll('input[name^="import_cities["]');
    const cities = Array.from(cityInputs).map(input => input.value.trim()).filter(city => city);
    
    if (urls.length === 0 && cities.length === 0) {
        alert({!! json_encode(__('messages.please_enter_urls_or_cities'), JSON_UNESCAPED_UNICODE) !!});
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = {!! json_encode(__('messages.testing'), JSON_UNESCAPED_UNICODE) !!} + '...';
    button.disabled = true;
    
    // Only test import if we have a subdomain (existing role)
    @if($role->exists)
    // Make AJAX request to run console command
    fetch('{{ route("role.test_import", ["subdomain" => $role->subdomain]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            urls: urls,
            cities: cities
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create a modal to show the detailed output
            showImportOutput(data.output, data.message);
        } else {
            // Show error output in modal
            showImportOutput(data.output || '', data.message, false);
        }
    })
    .catch(error => {
        showImportOutput('', '{{ __("messages.import_test_error") }}: ' + error.message, false);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
    @else
    // For new roles, just show a message
    alert({!! json_encode(__('messages.save_role_first_to_test_import'), JSON_UNESCAPED_UNICODE) !!});
    button.textContent = originalText;
    button.disabled = false;
    @endif
}

function showImportOutput(output, message, isSuccess = true) {
    // Create modal HTML
    const modalHtml = `
        <div id="import-output-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __("messages.import_test_results") }}
                        </h3>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <div class="w-3 h-3 rounded-full ${isSuccess ? 'bg-green-500' : 'bg-red-500'} me-2"></div>
                            <span class="font-medium ${isSuccess ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'}">
                                ${message}
                            </span>
                        </div>
                    </div>
                    
                    ${output ? `
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __("messages.console_output") }}:</h4>
                            <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 max-h-96 overflow-y-auto">
                                <pre class="text-xs text-gray-800 dark:text-gray-200 whitespace-pre-wrap">${output}</pre>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div class="flex justify-end">
                        <button onclick="closeImportOutput()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                            {{ __("messages.close") }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeImportOutput() {
    const modal = document.getElementById('import-output-modal');
    if (modal) {
        modal.remove();
    }
}

function addImportUrlField() {
    const container = document.getElementById('import-url-items');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg';
    div.innerHTML = `
        <div class="mb-4">
            <label for="import_url_new_${idx}" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.url') }}</label>
            <input name="import_urls[new_${idx}]" type="url" id="import_url_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
        </div>
        <div class="flex gap-4 items-center">
            <button type="button" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150" onclick="this.parentElement.parentElement.remove()">
                {{ __('messages.remove') }}
            </button>
        </div>
    `;
    container.appendChild(div);
}

function addImportCityField() {
    const container = document.getElementById('import-city-items');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg';
    div.innerHTML = `
        <div class="mb-4">
            <label for="import_city_new_${idx}" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.city') }}</label>
            <input name="import_cities[new_${idx}]" type="text" id="import_city_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" placeholder="{{ __('messages.placeholder_city') }}" />
        </div>
        <div class="flex gap-4 items-center">
            <button type="button" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150" onclick="this.parentElement.parentElement.remove()">
                {{ __('messages.remove') }}
            </button>
        </div>
    `;
    container.appendChild(div);
}

// Google Calendar integration functions
document.addEventListener('DOMContentLoaded', function() {
    // Only load Google calendars if the Google Calendar section is present
    const googleCalendarSelect = document.getElementById('google-calendar-select');
    if (googleCalendarSelect) {
        loadGoogleCalendars();
    }
});

function loadGoogleCalendars() {
    const select = document.getElementById('google-calendar-select');
    if (!select) {
        console.warn('Google Calendar select element not found');
        return;
    }

    fetch('/google-calendar/calendars')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            select.innerHTML = '<option value="">' + {!! json_encode(__('messages.select_a_calendar'), JSON_UNESCAPED_UNICODE) !!} + '</option>';
            
            if (data.calendars && Array.isArray(data.calendars)) {
                data.calendars.forEach(calendar => {
                    const option = document.createElement('option');
                    option.value = calendar.id;
                    option.textContent = calendar.summary + (calendar.primary ? ' (Primary)' : '');
                    if (calendar.id === '{{ $role->google_calendar_id }}') {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">' + {!! json_encode(__('messages.no_calendars_available'), JSON_UNESCAPED_UNICODE) !!} + '</option>';
            }
        })
        .catch(error => {
            console.error('Error loading calendars:', error);
            let errorMessage = {!! json_encode(__('messages.error_loading_calendars'), JSON_UNESCAPED_UNICODE) !!};
            
            if (error.message.includes('401')) {
                errorMessage = {!! json_encode(__('messages.google_calendar_not_connected'), JSON_UNESCAPED_UNICODE) !!};
            } else if (error.message.includes('403')) {
                errorMessage = {!! json_encode(__('messages.access_denied_calendar'), JSON_UNESCAPED_UNICODE) !!};
            }
            
            select.innerHTML = '<option value="">' + errorMessage + '</option>';
        });
}



function syncEvents() {
    const selectedDirection = document.querySelector('input[name="sync_direction"]:checked');
    const syncEventsButton = document.getElementById('sync-events-button');
    
    // Check if button is disabled
    if (syncEventsButton && syncEventsButton.disabled) {
        showSyncMessage('Please select a sync direction other than "No Sync" to enable syncing', 'error');
        return;
    }
    
    if (!selectedDirection || !selectedDirection.value) {
        showSyncMessage('Please select a sync direction first', 'error');
        return;
    }

    showSyncStatus();
    
    // Use the unified sync endpoint
    const requestBody = {
        sync_direction: selectedDirection.value
    };
    
    fetch('/google-calendar/sync/{{ $role->subdomain }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestBody),
    })
    .then(response => response.json())
    .then(data => {
        hideSyncStatus();
        if (data.error) {
            showSyncMessage('Error: ' + data.error, 'error');
        } else {
            showSyncMessage(data.message);
        }
    })
    .catch(error => {
        hideSyncStatus();
        showSyncMessage('Error: ' + error.message, 'error');
    });
}

function showSyncStatus() {
    document.getElementById('sync-status').classList.remove('hidden');
    document.getElementById('sync-results').classList.add('hidden');
}

function hideSyncStatus() {
    document.getElementById('sync-status').classList.add('hidden');
}

function showSyncMessage(message, type = 'success') {
    const resultsDiv = document.getElementById('sync-results');
    const messageDiv = document.getElementById('sync-message');
    
    if (!resultsDiv || !messageDiv) {
        console.error('Sync results elements not found');
        return;
    }
    
    messageDiv.textContent = message;
    
    // Get the inner div that contains the styling classes
    const innerDiv = resultsDiv.querySelector('div');
    const textDiv = innerDiv.querySelector('div');
    
    if (!innerDiv || !textDiv) {
        console.error('Sync results inner elements not found');
        return;
    }
    
    if (type === 'error') {
        // Remove green classes and add red classes
        innerDiv.className = 'p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg';
        textDiv.className = 'text-sm text-red-800 dark:text-red-200';
    } else {
        // Remove red classes and add green classes
        innerDiv.className = 'p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg';
        textDiv.className = 'text-sm text-green-800 dark:text-green-200';
    }
    
    resultsDiv.classList.remove('hidden');
}

// Add event listeners for sync direction changes
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for sync direction radio buttons
    const syncDirectionRadios = document.querySelectorAll('input[name="sync_direction"]');
    const syncEventsButton = document.getElementById('sync-events-button');
    
    syncDirectionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Enable/disable sync events button based on selection
            if (syncEventsButton) {
                syncEventsButton.disabled = !this.value || this.value === '';
            }
        });
    });
    
    // Set initial state of sync events button
    const selectedDirection = document.querySelector('input[name="sync_direction"]:checked');
    if (syncEventsButton && selectedDirection) {
        syncEventsButton.disabled = !selectedDirection.value || selectedDirection.value === '';
    }
});

// Test email functionality
document.addEventListener('DOMContentLoaded', function() {
    const sendTestEmailBtn = document.getElementById('send-test-email-btn');
    const testEmailResult = document.getElementById('test-email-result');
    
    @if ($role->exists && $role->subdomain)
    const testEmailUrl = '{{ route("role.test_email", ["subdomain" => $role->subdomain]) }}';
    @else
    const testEmailUrl = null;
    @endif
    
    if (sendTestEmailBtn && testEmailResult && testEmailUrl) {
        sendTestEmailBtn.addEventListener('click', function() {
            const fromAddressInput = document.getElementById('email_settings_from_address');
            const email = fromAddressInput ? fromAddressInput.value.trim() : '';
            
            if (!email) {
                testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                testEmailResult.textContent = {!! json_encode(__('messages.please_enter_from_address'), JSON_UNESCAPED_UNICODE) !!};
                testEmailResult.classList.remove('hidden');
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                testEmailResult.textContent = {!! json_encode(__('messages.invalid_email_address'), JSON_UNESCAPED_UNICODE) !!};
                testEmailResult.classList.remove('hidden');
                return;
            }
            
            const emailSettings = {
                host: document.getElementById('email_settings_host')?.value.trim() || '',
                port: document.getElementById('email_settings_port')?.value.trim() || '',
                encryption: document.getElementById('email_settings_encryption')?.value.trim() || '',
                username: document.getElementById('email_settings_username')?.value.trim() || '',
                password: document.getElementById('email_settings_password')?.value.trim() || '',
                from_address: email,
                from_name: document.getElementById('email_settings_from_name')?.value.trim() || ''
            };
            
            // Remove empty values
            Object.keys(emailSettings).forEach(key => {
                if (emailSettings[key] === '') {
                    delete emailSettings[key];
                }
            });
            
            // Disable button and show loading
            sendTestEmailBtn.disabled = true;
            sendTestEmailBtn.textContent = {!! json_encode(__('messages.sending'), JSON_UNESCAPED_UNICODE) !!} + '...';
            testEmailResult.classList.add('hidden');
            
            // Send AJAX request
            fetch(testEmailUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    email: email,
                    email_settings: emailSettings
                })
            })
            .then(response => response.json())
            .then(data => {
                sendTestEmailBtn.disabled = false;
                sendTestEmailBtn.textContent = {!! json_encode(__('messages.send_test_email'), JSON_UNESCAPED_UNICODE) !!};
                
                if (data.success) {
                    testEmailResult.className = 'mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200';
                    testEmailResult.textContent = data.message || {!! json_encode(__('messages.test_email_sent'), JSON_UNESCAPED_UNICODE) !!};
                } else {
                    testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                    testEmailResult.textContent = data.error || {!! json_encode(__('messages.failed_to_send_test_email'), JSON_UNESCAPED_UNICODE) !!};
                }
                testEmailResult.classList.remove('hidden');
            })
            .catch(error => {
                sendTestEmailBtn.disabled = false;
                sendTestEmailBtn.textContent = {!! json_encode(__('messages.send_test_email'), JSON_UNESCAPED_UNICODE) !!};
                testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                testEmailResult.textContent = {!! json_encode(__('messages.failed_to_send_test_email'), JSON_UNESCAPED_UNICODE) !!};
                testEmailResult.classList.remove('hidden');
                console.error('Error:', error);
            });
        });
    }
});

// Section navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const sectionLinks = document.querySelectorAll('.section-nav-link');
    const sections = document.querySelectorAll('.section-content');
    const mobileHeaders = document.querySelectorAll('.mobile-section-header');

    // Function to sync mobile accordion headers
    function syncMobileHeaders(sectionId) {
        mobileHeaders.forEach(header => {
            if (header.getAttribute('data-section') === sectionId) {
                header.classList.add('active');
            } else {
                header.classList.remove('active');
            }
        });
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

        // Sync mobile accordion headers
        syncMobileHeaders(sectionId);

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

    // Handle mobile accordion header clicks
    mobileHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);
        });
    });

    // Check if we're on a large screen
    function isLargeScreen() {
        return window.matchMedia('(min-width: 1024px)').matches;
    }

    // Initialize: show section based on hash or first section
    function initializeSections() {
        // Check URL hash first
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            showSection(hash, true);
        } else {
            // Show first section
            const firstSection = sections[0];
            if (firstSection) {
                showSection(firstSection.id, true);
            }
        }
    }

    // Handle hash changes
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            showSection(hash);
        }
    });

    // Initialize on page load
    initializeSections();

    // Multiple scroll guarantees to ensure page stays at top
    function scrollToTop() {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
    }
    scrollToTop();
    setTimeout(scrollToTop, 0);
    setTimeout(scrollToTop, 10);
    requestAnimationFrame(scrollToTop);

    // Form validation error handling
    const form = document.querySelector('form');
    if (form) {
        // Field to section mapping
        const fieldSectionMap = {
            'name': 'section-details',
            'address1': 'section-address',
            'email': 'section-contact-info'
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
            const mobileHeader = document.querySelector(`.mobile-section-header[data-section="${sectionId}"]`);
            if (mobileHeader) {
                mobileHeader.classList.add('validation-error');
            }
        }

        // Function to clear section error highlight by section ID
        function clearSectionErrorById(sectionId) {
            if (!sectionId) return;

            const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
            if (sectionLink) {
                sectionLink.classList.remove('validation-error');
            }
            const mobileHeader = document.querySelector(`.mobile-section-header[data-section="${sectionId}"]`);
            if (mobileHeader) {
                mobileHeader.classList.remove('validation-error');
            }
        }

        // Handle invalid event on ANY required field (including custom fields)
        form.addEventListener('invalid', function(e) {
            const field = e.target;
            const sectionContent = field.closest('.section-content');
            if (sectionContent) {
                highlightSectionError(sectionContent.id);
            }
        }, true); // Use capture phase since invalid doesn't bubble

        // Form submit handler - check validity before submission
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();

                // Find first invalid field across ALL form elements
                let firstInvalidField = null;
                let firstInvalidSection = null;
                const allFields = form.querySelectorAll('input, select, textarea');

                for (const field of allFields) {
                    if (!field.checkValidity()) {
                        if (!firstInvalidField) {
                            firstInvalidField = field;
                            const sectionContent = field.closest('.section-content');
                            firstInvalidSection = sectionContent ? sectionContent.id : null;
                        }
                    }
                }

                if (firstInvalidField && firstInvalidSection) {
                    showSection(firstInvalidSection);
                    highlightSectionError(firstInvalidSection);

                    setTimeout(() => {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidField.reportValidity();
                    }, 100);
                }
            }
        });

        // Clear error state when any required field becomes valid
        const allRequiredFields = form.querySelectorAll('[required]');
        allRequiredFields.forEach(field => {
            const handler = function() {
                if (this.checkValidity()) {
                    const sectionContent = this.closest('.section-content');
                    if (sectionContent) {
                        const sectionFields = sectionContent.querySelectorAll('[required]');
                        const allValid = Array.from(sectionFields).every(f => f.checkValidity());
                        if (allValid) {
                            clearSectionErrorById(sectionContent.id);
                        }
                    }
                }
            };
            field.addEventListener('input', handler);
            field.addEventListener('change', handler);
        });
    }
});

// Final scroll guarantee on window load
window.addEventListener('load', function() {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
});

// Integration tabs switching
document.addEventListener('DOMContentLoaded', function() {
    const integrationTabs = document.querySelectorAll('.integration-tab');
    const integrationTabContents = document.querySelectorAll('.integration-tab-content');

    // Restore active tab from localStorage
    const savedTab = localStorage.getItem('integrationActiveTab');
    if (savedTab) {
        switchIntegrationTab(savedTab);
    }

    integrationTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchIntegrationTab(tabName);
            localStorage.setItem('integrationActiveTab', tabName);
        });
    });

    function switchIntegrationTab(tabName) {
        // Update tab buttons
        integrationTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[#4E81FA]', 'text-[#4E81FA]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[#4E81FA]', 'text-[#4E81FA]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        // Update tab contents
        integrationTabContents.forEach(content => {
            const contentId = content.id.replace('integration-tab-', '');
            if (contentId === tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
});

// Settings tabs switching
document.addEventListener('DOMContentLoaded', function() {
    const settingsTabs = document.querySelectorAll('.settings-tab');
    const settingsTabContents = document.querySelectorAll('.settings-tab-content');

    // Restore active tab from localStorage
    const savedSettingsTab = localStorage.getItem('settingsActiveTab');
    if (savedSettingsTab) {
        switchSettingsTab(savedSettingsTab);
    }

    settingsTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchSettingsTab(tabName);
            localStorage.setItem('settingsActiveTab', tabName);
        });
    });

    function switchSettingsTab(tabName) {
        // Update tab buttons
        settingsTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[#4E81FA]', 'text-[#4E81FA]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[#4E81FA]', 'text-[#4E81FA]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        // Update tab contents
        settingsTabContents.forEach(content => {
            const contentId = content.id.replace('settings-tab-', '');
            if (contentId === tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
});

// CalDAV integration functions
document.addEventListener('DOMContentLoaded', function() {
    const caldavTestBtn = document.getElementById('caldav-test-btn');
    const caldavConnectBtn = document.getElementById('caldav-connect-btn');
    const caldavDisconnectBtn = document.getElementById('caldav-disconnect-btn');
    const caldavTestResult = document.getElementById('caldav-test-result');
    const caldavCalendarContainer = document.getElementById('caldav-calendar-select-container');
    const caldavSyncDirectionContainer = document.getElementById('caldav-sync-direction-container');
    const caldavConnectContainer = document.getElementById('caldav-connect-container');
    const caldavCalendarSelect = document.getElementById('caldav_calendar_url');

    @if ($role->exists && $role->subdomain)
    const caldavSubdomain = '{{ $role->subdomain }}';
    @else
    const caldavSubdomain = null;
    @endif

    // Test Connection
    if (caldavTestBtn) {
        caldavTestBtn.addEventListener('click', function() {
            const serverUrl = document.getElementById('caldav_server_url').value.trim();
            const username = document.getElementById('caldav_username').value.trim();
            const password = document.getElementById('caldav_password').value.trim();

            if (!serverUrl || !username || !password) {
                showCaldavResult({!! json_encode(__('messages.fill_all_fields'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                return;
            }

            if (!serverUrl.startsWith('https://')) {
                showCaldavResult({!! json_encode(__('messages.caldav_https_required'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                return;
            }

            caldavTestBtn.disabled = true;
            caldavTestBtn.textContent = {!! json_encode(__('messages.testing'), JSON_UNESCAPED_UNICODE) !!};

            fetch('/caldav/test-connection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    server_url: serverUrl,
                    username: username,
                    password: password
                }),
            })
            .then(response => response.json())
            .then(data => {
                caldavTestBtn.disabled = false;
                caldavTestBtn.textContent = {!! json_encode(__('messages.test_connection'), JSON_UNESCAPED_UNICODE) !!};

                if (data.success) {
                    showCaldavResult({!! json_encode(__('messages.connection_successful'), JSON_UNESCAPED_UNICODE) !!}, 'success');
                    // Discover calendars
                    discoverCaldavCalendars(serverUrl, username, password);
                } else {
                    showCaldavResult(data.message || {!! json_encode(__('messages.connection_failed'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                }
            })
            .catch(error => {
                caldavTestBtn.disabled = false;
                caldavTestBtn.textContent = {!! json_encode(__('messages.test_connection'), JSON_UNESCAPED_UNICODE) !!};
                showCaldavResult({!! json_encode(__('messages.connection_failed'), JSON_UNESCAPED_UNICODE) !!} + ': ' + error.message, 'error');
            });
        });
    }

    // Discover Calendars
    function discoverCaldavCalendars(serverUrl, username, password) {
        fetch('/caldav/discover-calendars', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                server_url: serverUrl,
                username: username,
                password: password
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.calendars && data.calendars.length > 0) {
                // Populate calendar select
                caldavCalendarSelect.innerHTML = '<option value="">' + {!! json_encode(__('messages.select_a_calendar'), JSON_UNESCAPED_UNICODE) !!} + '</option>';
                data.calendars.forEach(calendar => {
                    const option = document.createElement('option');
                    option.value = calendar.url;
                    option.textContent = calendar.name;
                    caldavCalendarSelect.appendChild(option);
                });

                // Show calendar selection and sync direction
                caldavCalendarContainer.classList.remove('hidden');
                caldavSyncDirectionContainer.classList.remove('hidden');
                caldavConnectContainer.classList.remove('hidden');
            } else {
                showCaldavResult({!! json_encode(__('messages.no_calendars_found'), JSON_UNESCAPED_UNICODE) !!}, 'error');
            }
        })
        .catch(error => {
            showCaldavResult({!! json_encode(__('messages.error_discovering_calendars'), JSON_UNESCAPED_UNICODE) !!} + ': ' + error.message, 'error');
        });
    }

    // Connect
    if (caldavConnectBtn) {
        caldavConnectBtn.addEventListener('click', function() {
            if (!caldavSubdomain) {
                showCaldavResult({!! json_encode(__('messages.save_schedule_first'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                return;
            }

            const serverUrl = document.getElementById('caldav_server_url').value.trim();
            const username = document.getElementById('caldav_username').value.trim();
            const password = document.getElementById('caldav_password').value.trim();
            const calendarUrl = caldavCalendarSelect.value;
            const syncDirection = document.querySelector('input[name="caldav_sync_direction_new"]:checked')?.value || 'to';

            if (!calendarUrl) {
                showCaldavResult({!! json_encode(__('messages.select_a_calendar'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                return;
            }

            caldavConnectBtn.disabled = true;
            caldavConnectBtn.textContent = {!! json_encode(__('messages.connecting'), JSON_UNESCAPED_UNICODE) !!};

            fetch('/caldav/settings/' + caldavSubdomain, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    server_url: serverUrl,
                    username: username,
                    password: password,
                    calendar_url: calendarUrl,
                    sync_direction: syncDirection
                }),
            })
            .then(response => response.json())
            .then(data => {
                caldavConnectBtn.disabled = false;
                caldavConnectBtn.textContent = {!! json_encode(__('messages.connect'), JSON_UNESCAPED_UNICODE) !!};

                if (data.success) {
                    showCaldavResult(data.message || {!! json_encode(__('messages.caldav_settings_saved'), JSON_UNESCAPED_UNICODE) !!}, 'success');
                    // Reload page to show connected state
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showCaldavResult(data.message || {!! json_encode(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                }
            })
            .catch(error => {
                caldavConnectBtn.disabled = false;
                caldavConnectBtn.textContent = {!! json_encode(__('messages.connect'), JSON_UNESCAPED_UNICODE) !!};
                showCaldavResult({!! json_encode(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE) !!} + ': ' + error.message, 'error');
            });
        });
    }

    // Handle sync direction changes when already connected
    const caldavSyncDirectionRadios = document.querySelectorAll('input[name="caldav_sync_direction"]');
    if (caldavSyncDirectionRadios.length > 0 && caldavSubdomain) {
        caldavSyncDirectionRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                const syncDirection = this.value;

                fetch('/caldav/sync-direction/' + caldavSubdomain, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        sync_direction: syncDirection
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show brief success feedback
                        showCaldavResult(data.message || {!! json_encode(__('messages.sync_direction_updated'), JSON_UNESCAPED_UNICODE) !!}, 'success');
                        setTimeout(() => {
                            if (caldavTestResult) {
                                caldavTestResult.classList.add('hidden');
                            }
                        }, 2000);
                    } else {
                        showCaldavResult(data.message || {!! json_encode(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE) !!}, 'error');
                    }
                })
                .catch(error => {
                    showCaldavResult({!! json_encode(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE) !!} + ': ' + error.message, 'error');
                });
            });
        });
    }

    // Disconnect
    if (caldavDisconnectBtn) {
        caldavDisconnectBtn.addEventListener('click', function() {
            if (!caldavSubdomain) return;

            if (!confirm({!! json_encode(__('messages.confirm_disconnect_caldav'), JSON_UNESCAPED_UNICODE) !!})) {
                return;
            }

            caldavDisconnectBtn.disabled = true;
            caldavDisconnectBtn.textContent = {!! json_encode(__('messages.disconnecting'), JSON_UNESCAPED_UNICODE) !!};

            fetch('/caldav/disconnect/' + caldavSubdomain, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show disconnected state
                    window.location.reload();
                } else {
                    caldavDisconnectBtn.disabled = false;
                    caldavDisconnectBtn.textContent = {!! json_encode(__('messages.disconnect'), JSON_UNESCAPED_UNICODE) !!};
                    alert(data.message || {!! json_encode(__('messages.failed_to_disconnect'), JSON_UNESCAPED_UNICODE) !!});
                }
            })
            .catch(error => {
                caldavDisconnectBtn.disabled = false;
                caldavDisconnectBtn.textContent = {!! json_encode(__('messages.disconnect'), JSON_UNESCAPED_UNICODE) !!};
                alert({!! json_encode(__('messages.failed_to_disconnect'), JSON_UNESCAPED_UNICODE) !!} + ': ' + error.message);
            });
        });
    }

    function showCaldavResult(message, type = 'success') {
        if (!caldavTestResult) return;

        if (type === 'error') {
            caldavTestResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
        } else {
            caldavTestResult.className = 'mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200';
        }
        caldavTestResult.textContent = message;
        caldavTestResult.classList.remove('hidden');
    }
});

// Event Custom Fields Management
let eventCustomFieldCounter = {{ count($role->event_custom_fields ?? []) }};
const aiPromptExamples = @json(array_map(fn($i) => __("messages.ai_prompt_example_$i"), range(1, 20)));
const aiPromptEgPrefix = @json(__('messages.eg'));

function getRandomAiPromptPlaceholder() {
    const usedPlaceholders = [];
    document.querySelectorAll('.ai-prompt-textarea').forEach(function(textarea) {
        if (textarea.placeholder) {
            usedPlaceholders.push(textarea.placeholder);
        }
    });
    const available = aiPromptExamples.filter(function(example) {
        return !usedPlaceholders.some(function(used) { return used.includes(example); });
    });
    const pool = available.length > 0 ? available : aiPromptExamples;
    const picked = pool[Math.floor(Math.random() * pool.length)];
    return aiPromptEgPrefix + ' ' + picked;
}

document.querySelectorAll('.ai-prompt-textarea').forEach(function(textarea) {
    if (!textarea.placeholder) {
        textarea.placeholder = getRandomAiPromptPlaceholder();
    }
});

function addEventCustomField() {
    const container = document.getElementById('event-custom-fields-container');
    const currentCount = container.querySelectorAll('.event-custom-field-item').length;

    if (currentCount >= 8) {
        return;
    }

    const fieldKey = 'new_' + eventCustomFieldCounter;
    eventCustomFieldCounter++;

    const fieldHtml = `
        <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg event-custom-field-item" data-field-key="${fieldKey}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_name') !!} *</label>
                    <input type="text" name="event_custom_fields[${fieldKey}][name]"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" required />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_type') !!}</label>
                    <select name="event_custom_fields[${fieldKey}][type]"
                        onchange="toggleEventFieldOptions(this)"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                        <option value="string">{!! __('messages.type_string') !!}</option>
                        <option value="multiline_string">{!! __('messages.type_multiline_string') !!}</option>
                        <option value="switch">{!! __('messages.type_switch') !!}</option>
                        <option value="date">{!! __('messages.type_date') !!}</option>
                        <option value="dropdown">{!! __('messages.type_dropdown') !!}</option>
                    </select>
                </div>
            </div>
            @if($role->language_code !== 'en')
            <div class="mt-3">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.english_name') !!}</label>
                <input type="text" name="event_custom_fields[${fieldKey}][name_en]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                    placeholder="{!! __('messages.auto_translated_placeholder') !!}" />
            </div>
            @endif
            <div class="mt-3 event-field-options-container" style="display: none;">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_options') !!}</label>
                <input type="text" name="event_custom_fields[${fieldKey}][options]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                    placeholder="{!! __('messages.options_placeholder') !!}" />
            </div>
            <div class="mt-3">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.ai_prompt_custom_field') !!}</label>
                <textarea name="event_custom_fields[${fieldKey}][ai_prompt]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm text-sm ai-prompt-textarea"
                    rows="2"
                    maxlength="500"></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{!! __('messages.ai_prompt_custom_field_help') !!}</p>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" name="event_custom_fields[${fieldKey}][required]"
                        id="event_field_required_${fieldKey}"
                        value="1"
                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                    <label for="event_field_required_${fieldKey}" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{!! __('messages.field_required') !!}</label>
                </div>
                <button type="button" onclick="removeEventCustomField(this)" class="inline-flex items-center justify-center px-2 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    {!! __('messages.remove') !!}
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
    const newTextarea = container.querySelector('.event-custom-field-item:last-child .ai-prompt-textarea');
    if (newTextarea) {
        newTextarea.placeholder = getRandomAiPromptPlaceholder();
    }
    updateEventCustomFieldButton();
}

function removeEventCustomField(button) {
    const fieldItem = button.closest('.event-custom-field-item');
    if (fieldItem) {
        fieldItem.remove();
        updateEventCustomFieldButton();
    }
}

function toggleEventFieldOptions(selectElement) {
    const fieldItem = selectElement.closest('.event-custom-field-item');
    const optionsContainer = fieldItem.querySelector('.event-field-options-container');
    if (selectElement.value === 'dropdown') {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
}

function updateEventCustomFieldButton() {
    const container = document.getElementById('event-custom-fields-container');
    const currentCount = container.querySelectorAll('.event-custom-field-item').length;
    const addButton = document.getElementById('add-event-custom-field-btn');
    if (currentCount >= 8) {
        addButton.classList.add('hidden');
    } else {
        addButton.classList.remove('hidden');
    }
}

function deleteRoleImage(url, token) {
    if (!confirm({!! json_encode(__('messages.are_you_sure'), JSON_UNESCAPED_UNICODE) !!})) {
        return;
    }

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('{{ __('messages.failed_to_delete_image') }}');
        }
    });
}
</script>