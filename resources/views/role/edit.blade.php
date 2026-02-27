<x-app-admin-layout>

    @vite([
    'resources/js/color-picker.js',
    ])

    <!-- Step Indicator for Add Event Flow -->
    @if(session('pending_request'))
        <div class="my-6">
            <x-step-indicator :compact="true" />
        </div>
    @endif

    <x-slot name="head">

        <style {!! nonce_attr() !!}>
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

        .dark #preview {
            border-color: #374151;
        }

        .color-nav-button {
            padding: 0.5rem 0.75rem;
            min-height: 38px;
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
        var allFonts = @json($fonts);

        var languageSubsetMap = {
            'he': 'hebrew',
            'ar': 'arabic',
            'ru': 'cyrillic'
        };

        function filterFontsByLanguage(langCode) {
            var requiredSubset = languageSubsetMap[langCode];
            if (!requiredSubset) {
                return allFonts;
            }
            return allFonts.filter(function(font) {
                return font.subsets && font.subsets.indexOf(requiredSubset) !== -1;
            });
        }

        function updateFontOptions() {
            var langCode = $('#language_code').val();
            var filteredFonts = filterFontsByLanguage(langCode);
            var select = document.getElementById('font_family');
            var currentValue = select.value;

            select.innerHTML = '';
            filteredFonts.forEach(function(font) {
                var option = document.createElement('option');
                option.value = font.value;
                option.textContent = font.label;
                if (font.value === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            // If current font is not compatible, select the first available font
            if (select.value !== currentValue) {
                select.selectedIndex = 0;
                onChangeFont();
            }

            updateFontNavButtons();
        }

        document.addEventListener('DOMContentLoaded', () => {
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

            updateFontOptions();

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
            const requireAccountSection = document.getElementById('require_account_section');
            const requireApprovalSection = document.getElementById('require_approval_section');
            const requestTermsSection = document.getElementById('request_terms_section');

            if (acceptRequestsCheckbox && requireAccountSection) {
                requireAccountSection.style.display = acceptRequestsCheckbox.checked ? 'block' : 'none';
                acceptRequestsCheckbox.addEventListener('change', function() {
                    requireAccountSection.style.display = this.checked ? 'block' : 'none';
                });
            }

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

            const requireApprovalCheckbox = document.querySelector('input[name="require_approval"][type="checkbox"]');
            const approvedSubdomainsSection = document.getElementById('approved_subdomains_section');

            if (acceptRequestsCheckbox && requireApprovalCheckbox && approvedSubdomainsSection) {
                function toggleApprovedSubdomains() {
                    approvedSubdomainsSection.style.display = (acceptRequestsCheckbox.checked && requireApprovalCheckbox.checked) ? 'block' : 'none';
                }
                toggleApprovedSubdomains();
                acceptRequestsCheckbox.addEventListener('change', toggleApprovedSubdomains);
                requireApprovalCheckbox.addEventListener('change', toggleApprovedSubdomains);
            }

            document.querySelectorAll('[data-subdomain-search]').forEach(function(el) {
                setupSubdomainAutocomplete(el);
            });

            document.addEventListener('click', function(e) {
                document.querySelectorAll('[data-subdomain-dropdown]').forEach(function(dropdown) {
                    if (!dropdown.parentElement.contains(e.target)) {
                        dropdown.classList.add('hidden');
                        dropdown.innerHTML = '';
                    }
                });
            });

            $('#profile_image').on('change', function() {
                previewImage(this, 'profile_image_preview');
            });

            $('#header_image').on('input', function() {
                var headerImageUrl = $(this).find(':selected').val();
                if (headerImageUrl && headerImageUrl !== 'none') {
                    // Preset header selected
                    headerImageUrl = "{{ asset('images/headers/thumbs') }}" + '/' + headerImageUrl + '.jpg';
                    $('#header_image_preview').attr('src', headerImageUrl).show();
                    $('#delete_header_image_button').hide();
                } else if (headerImageUrl === '') {
                    // Custom option selected - show existing custom image if available
                    var existingCustomUrl = '{{ $role->header_image_url }}';
                    $('#header_image_preview').hide();
                    if (existingCustomUrl) {
                        $('#delete_header_image_button').show();
                    }
                } else {
                    // 'none' selected
                    $('#header_image_preview').hide();
                    $('#delete_header_image_button').hide();
                }
            });

            $('#header_image_url').on('change', function() {
                previewImage(this, 'header_image_url_preview');
                updatePreview();
            });

            $('#background_image_url').on('change', function() {
                previewImage(this, 'background_image_preview');
                updatePreview();
            });
        });

        function clearRoleFileInput(inputId, previewId, filenameId) {
            const input = document.getElementById(inputId);
            input.value = '';
            const preview = document.getElementById(previewId);
            const clearBtn = document.getElementById(previewId + '_clear');
            const filenameSpan = document.getElementById(filenameId);
            if (preview) {
                preview.src = '';
                preview.style.display = 'none';
            }
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }
            if (filenameSpan) {
                filenameSpan.textContent = '';
            }
            updatePreview();
        }

        function clearHeaderFileInput() {
            const input = document.getElementById('header_image_url');
            input.value = '';
            document.getElementById('header_image_url_filename').textContent = '';
            document.getElementById('header_image_url_preview_clear').style.display = 'none';
            // Hide the custom header preview, but keep preset header preview visible if any
            const headerSelect = document.getElementById('header_image');
            if (headerSelect.value === '') {
                document.getElementById('header_image_preview').style.display = 'none';
            }
            updatePreview();
        }

        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const clearBtn = document.getElementById(previewId + '_clear');
            const warningElement = document.getElementById(previewId.split('_')[0] + '_image_size_warning');

            if (!input || !input.files || !input.files[0]) {
                if (preview) {
                    preview.src = '';
                }
                if (clearBtn) {
                    clearBtn.style.display = 'none';
                } else if (preview) {
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
                        warningMessage += @json(__('messages.image_size_warning'), JSON_UNESCAPED_UNICODE);
                    }

                    if (width !== height && previewId == 'profile_image_preview') {
                        if (warningMessage) warningMessage += " ";
                        warningMessage += @json(__('messages.image_not_square'), JSON_UNESCAPED_UNICODE);
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

                    // Always show the preview, even with warnings
                    preview.src = reader.result;
                    if (clearBtn) {
                        clearBtn.style.display = 'inline-block';
                    } else {
                        preview.style.display = 'block';
                    }
                    updatePreview();

                    if (previewId === 'background_image_preview') {
                        $('#style_background_image img:not(#background_image_preview)').hide();
                        $('#style_background_image a').hide();
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

        function onChangeCountry() {
            var ci = window.getCountryInput('country_code');
            if (ci) {
                var selected = ci.getSelectedCountryData();
                $('#country_code').val(selected.iso2);
            }
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

        function getContrastColor(hex) {
            hex = hex.replace('#', '');
            var r = parseInt(hex.substring(0, 2), 16) / 255;
            var g = parseInt(hex.substring(2, 4), 16) / 255;
            var b = parseInt(hex.substring(4, 6), 16) / 255;
            r = r <= 0.03928 ? r / 12.92 : Math.pow((r + 0.055) / 1.055, 2.4);
            g = g <= 0.03928 ? g / 12.92 : Math.pow((g + 0.055) / 1.055, 2.4);
            b = b <= 0.03928 ? b / 12.92 : Math.pow((b + 0.055) / 1.055, 2.4);
            var luminance = 0.2126 * r + 0.7152 * g + 0.0722 * b;
            return luminance > 0.25 ? '#000000' : '#ffffff';
        }

        function updatePreview() {
            var isDark = document.documentElement.classList.contains('dark');
            var background = $('input[name="background"]:checked').val();
            var backgroundColor = $('#background_color').val();
            var backgroundColors = $('#background_colors').val();
            var backgroundRotation = $('#background_rotation').val();
            var fontColor = isDark ? '#F3F4F6' : '#151B26';
            var fontFamily = $('#font_family').find(':selected').text().trim();
            var accentColor = $('#accent_color').val() || '#4E81FA';
            var langCode = $('#language_code').val();
            var isRtl = (langCode === 'ar' || langCode === 'he');
            var followTranslations = {
                @foreach(config('app.supported_languages') as $lang)
                    '{{ $lang }}': @json(__('messages.follow', [], $lang), JSON_UNESCAPED_UNICODE),
                @endforeach
            };
            var followText = followTranslations[langCode] || followTranslations['en'];
            var name = $('#name').val();
            var headerImage = $('#header_image').val();
            var profileImagePreview = $('#profile_image_preview').attr('src');
            var existingProfileImage = '{{ $role->profile_image_url }}';

            if (! name) {
                name = @json(__('messages.preview'), JSON_UNESCAPED_UNICODE);
            } else if (name.length > 20) {
                name = name.substring(0, 20) + '...';
            }

            $('#font_preview').text(name);

            // Build header image HTML
            var headerHtml = '';
            if (headerImage && headerImage !== 'none' && headerImage !== '') {
                var headerUrl = "{{ asset('images/headers/thumbs') }}" + '/' + headerImage + '.jpg';
                headerHtml = '<div class="w-full h-16 bg-cover bg-center rounded-t-lg" style="background-image: url(\'' + headerUrl + '\')"></div>';
            } else if (headerImage === '') {
                // Custom header image
                var customHeaderUrl = $('#header_image_url_preview').attr('src') || '{{ $role->header_image_url }}';
                if (customHeaderUrl) {
                    headerHtml = '<div class="w-full h-16 bg-cover bg-center rounded-t-lg" style="background-image: url(\'' + customHeaderUrl + '\')"></div>';
                }
            }

            // Build profile image HTML
            var profileHtml = '';
            var profileSrc = profileImagePreview && profileImagePreview !== '#' ? profileImagePreview : existingProfileImage;
            if (profileSrc) {
                var marginTop = headerHtml ? '-mt-6' : '-mt-8';
                var profileBg = isDark ? '#111827' : '#F5F9FE';
                var profileAlign = isRtl ? ' ml-auto' : '';
                profileHtml = '<div class="' + marginTop + ' mb-2' + profileAlign + '"><div class="w-12 h-12 rounded-lg p-0.5 shadow-sm" style="background-color: ' + profileBg + '"><img src="' + profileSrc + '" class="w-full h-full object-cover rounded-lg" /></div></div>';
            }

            // Build content HTML with accent color elements
            var contentTopPadding = !profileSrc && !headerHtml ? 'pt-3' : '';
            var cardBg = isDark ? '#111827' : '#F5F9FE';
            var contentHtml =
                '<div dir="' + (isRtl ? 'rtl' : 'ltr') + '" class="rounded-lg flex flex-col ' + (profileSrc && !headerHtml ? 'mt-8' : '') + '" style="background-color: ' + cardBg + '">' +
                    headerHtml +
                    '<div class="px-3 pb-3 flex flex-col">' +
                        profileHtml +
                        '<div class="flex items-center justify-between gap-2 ' + contentTopPadding + '">' +
                            '<div class="text-sm font-semibold text-[#151B26]" style="color: ' + fontColor + "; font-family: '" + fontFamily + "';\">" + name + '</div>' +
                            '<div class="rounded-md px-2.5 py-1 text-xs font-semibold shadow-sm" style="background-color: ' + accentColor + '; color: ' + getContrastColor(accentColor) + '">' +
                                followText +
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
                    $('#custom_gradient_preview').css('background', 'linear-gradient(to right, ' + customColor1 + ', ' + customColor2 + ')');
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
            $('#address_response').text(@json(__('messages.searching'), JSON_UNESCAPED_UNICODE) + '...').show();
            $('#accept_button').hide();
            var ci = window.getCountryInput('country_code');
            var country = ci ? ci.getSelectedCountryData() : null;
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
                        $('#address_response').text(@json(__('messages.address_not_found'), JSON_UNESCAPED_UNICODE));    
                    }
                },
                error: function(xhr, status, error) {
                    $('#address_response').text(@json(__('messages.an_error_occurred'), JSON_UNESCAPED_UNICODE));
                }
            });
        }

        function viewMap() {
            var address = [
                $('#address1').val(),
                $('#city').val(),
                $('#state').val(),
                $('#postal_code').val(),
                (window.getCountryInput('country_code') ? window.getCountryInput('country_code').getSelectedCountryData().name : '')
            ].filter(Boolean).join(', ');

            if (address) {
                var url = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address);
                window.open(url, '_blank');
            } else {
                alert(@json(__('messages.please_enter_address'), JSON_UNESCAPED_UNICODE));
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
                updatePreview();
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
                updatePreview();
                updateImageNavButtons();
                toggleCustomImageInput();
            }
        }

        function toggleCustomImageInput() {
            const select = document.getElementById('background_image');
            const customInput = document.getElementById('custom_image_input');
            const existingImg = document.getElementById('background_image_existing');
            const thumbPreview = document.getElementById('background_image_thumb_preview');
            const bgValue = select.value;
            const isCustom = bgValue === '';

            // Show/hide built-in background preview thumbnail
            if (thumbPreview) {
                if (bgValue && bgValue !== 'none' && bgValue !== '') {
                    thumbPreview.src = "{{ asset('images/backgrounds/thumbs') }}" + '/' + bgValue + '.jpg';
                    thumbPreview.style.display = '';
                } else {
                    thumbPreview.style.display = 'none';
                }
            }

            if (existingImg) {
                existingImg.style.display = isCustom ? '' : 'none';
            }

            const hasExistingImage = existingImg && existingImg.style.display !== 'none';
            customInput.style.display = (isCustom && !hasExistingImage) ? 'block' : 'none';
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
                updatePreview();
                updateHeaderNavButtons();
                toggleCustomHeaderInput();
            }
        }

        function toggleCustomHeaderInput() {
            const select = document.getElementById('header_image');
            const customInput = document.getElementById('custom_header_input');
            const deleteBtn = document.getElementById('delete_header_image_button');
            const headerPreview = document.getElementById('header_image_preview');
            const headerValue = select.value;
            const isCustom = headerValue === '';

            // Show/hide built-in header preview thumbnail
            if (headerPreview) {
                if (headerValue && headerValue !== 'none' && headerValue !== '') {
                    headerPreview.src = "{{ asset('images/headers/thumbs') }}" + '/' + headerValue + '.jpg';
                    headerPreview.style.display = '';
                } else {
                    headerPreview.style.display = 'none';
                }
            }

            // Show/hide existing custom image with delete button
            if (deleteBtn) {
                deleteBtn.style.display = isCustom ? '' : 'none';
            }

            const hasExistingCustomImage = deleteBtn && deleteBtn.style.display !== 'none';
            customInput.style.display = (isCustom && !hasExistingCustomImage) ? 'block' : 'none';
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
        enctype="multipart/form-data"
        novalidate
        id="edit-form">

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
                            <a href="#section-subschedules" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-subschedules">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                                {{ __('messages.subschedules') }}
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
                            <x-input-label for="name" :value="__('messages.schedule_name') . ' *'" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $role->name)" required data-action="update-preview-on-input" />
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
                            <x-input-label for="short_description" :value="__('messages.short_description')" />
                            <x-text-input id="short_description" name="short_description" type="text" class="mt-1 block w-full"
                                :value="old('short_description', $role->short_description)" maxlength="200" />
                            <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                        </div>

                        @if ($role->short_description_en)
                        <div class="mb-6">
                            <x-input-label for="short_description_en" :value="__('messages.short_description') . ' (' . __('messages.english') . ')'" />
                            <x-text-input id="short_description_en" name="short_description_en" type="text" class="mt-1 block w-full"
                                :value="old('short_description_en', $role->short_description_en)" maxlength="200" />
                            <x-input-error class="mt-2" :messages="$errors->get('short_description_en')" />
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
                                data-action="language-change"
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
                                'et' => 'estonian',
                                'ru' => 'russian',
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
                            <select name="timezone" id="timezone" required {{ is_demo_mode() ? 'disabled' : '' }} data-searchable
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
                            <x-input-label for="country_code" :value="__('messages.country')" />
                            <x-country-input id="country_code" name="country_code" :value="old('country_code', $role->country_code)" />
                            <x-input-error class="mt-2" :messages="$errors->get('country_code')" />
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center space-x-4">
                                <x-secondary-button id="view_map_button">{{ __('messages.view_map') }}</x-secondary-button>
                                @if (config('services.google.backend'))
                                <x-secondary-button id="validate_button">{{ __('messages.validate_address') }}</x-secondary-button>
                                <x-secondary-button id="accept_button" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
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

                        <div class="mb-6">
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

                        <div class="mb-6">
                            <x-input-label for="phone" :value="__('messages.phone_number')" />
                            <x-phone-input name="phone" id="role_phone" :value="old('phone', $role->phone)" :country="$role->country_code ?? 'us'" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />

                            @if (config('app.hosted') && $role->exists && $role->phone && !$role->phone_verified_at)
                            <div id="role-phone-verify-section">
                                <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                                    {{ __('messages.your_phone_is_unverified') }}
                                </p>

                                @if (\App\Services\SmsService::isConfigured())
                                <div id="role-phone-verify-ui" class="mt-2">
                                    <button type="button" id="role-phone-send-code-btn"
                                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800">
                                        {{ __('messages.click_here_to_verify_phone') }}
                                    </button>

                                    <div id="role-phone-code-input" style="display: none;" class="mt-2 flex items-center gap-2">
                                        <input type="text" id="role-phone-verification-code" maxlength="6" placeholder="000000"
                                            class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm text-center tracking-widest" />
                                        <button type="button" id="role-phone-verify-code-btn"
                                            class="inline-flex items-center px-3 py-2 bg-[#4E81FA] text-white text-sm font-medium rounded-md hover:bg-[#3d6de8] transition-colors">
                                            {{ __('messages.verify') }}
                                        </button>
                                    </div>

                                    <p id="role-phone-verify-message" class="mt-2 text-sm" style="display: none;"></p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-checkbox name="show_phone" label="{{ __('messages.show_phone_number') }}"
                                checked="{{ old('show_phone', $role->show_phone) }}"
                                data-custom-attribute="value" />
                            <x-input-error class="mt-2" :messages="$errors->get('show_phone')" />
                        </div>

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
                            <x-input-label for="country_code" :value="__('messages.country')" />
                            <x-country-input id="country_code" name="country_code" :value="old('country_code', $role->country_code)" />
                            <x-input-error class="mt-2" :messages="$errors->get('country_code')" />
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
                            <button type="button" data-style-tab="branding" id="style-tab-branding"
                                class="style-tab-button flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-[#4E81FA] text-[#4E81FA]">
                                {{ __('messages.branding') }}
                            </button>
                            <button type="button" data-style-tab="background" id="style-tab-background"
                                class="style-tab-button flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300">
                                {{ __('messages.background') }}
                            </button>
                            <button type="button" data-style-tab="advanced" id="style-tab-advanced"
                                class="style-tab-button flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300">
                                {{ __('messages.advanced') }}
                            </button>
                        </nav>
                    </div>


                    <!-- Branding Tab Content -->
                    <div id="style-content-branding">
                            <div class="mb-6">
                                <x-input-label :value="__('messages.square_profile_image')" />
                                <input id="profile_image" name="profile_image" type="file" class="hidden"
                                    accept="image/png, image/jpeg" data-file-trigger="profile_image" data-filename-target="profile_image_filename" data-preview-target="profile_image_preview" />
                                <div id="profile_image_choose" style="{{ $role->profile_image_url ? 'display:none' : '' }}">
                                    <div class="mt-1 flex items-center gap-3">
                                        <button type="button" data-trigger-file-input="profile_image"
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                            <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('messages.choose_file') }}
                                        </button>
                                        <span id="profile_image_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                                    <p id="profile_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                        {{ __('messages.image_size_warning') }}
                                    </p>
                                </div>

                                <div id="profile_image_preview_clear" class="relative inline-block pt-3" style="display: none;">
                                    <img id="profile_image_preview" src="#" alt="Profile Image Preview" style="max-height:120px;" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                    <button type="button" data-clear-file-input="profile_image" data-clear-preview="profile_image_preview" data-clear-filename="profile_image_filename" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                </div>

                                @if ($role->profile_image_url)
                                <div id="profile_image_existing" class="relative inline-block mt-4 pt-1" data-show-on-delete="profile_image_choose">
                                    <img src="{{ $role->profile_image_url }}" style="max-height:120px" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                    <button type="button"
                                        data-delete-image-url="{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'profile']) }}"
                                        data-delete-image-token="{{ csrf_token() }}"
                                        data-delete-image-parent="true"
                                        style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                @endif
                            </div>

                            <div class="mb-6">
                                <x-input-label for="accent_color" :value="__('messages.accent_color')" />
                                <x-text-input id="accent_color" name="accent_color" type="color" class="mt-1 block w-1/2"
                                    :value="old('accent_color', $role->accent_color)" data-action="update-preview-on-input" />
                                <x-input-error class="mt-2" :messages="$errors->get('accent_color')" />
                            </div>

                            <div class="mb-6">
                                <x-input-label for="font_family" :value="__('messages.font_family')" />
                                <div class="flex items-center gap-1">
                                    <select id="font_family" name="font_family" data-searchable data-action="font-family-change"
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach($fonts as $font)
                                        <option value="{{ $font->value }}"
                                            {{ $role->font_family == $font->value ? 'SELECTED' : '' }}>
                                            {{ $font->label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button"
                                            id="prev_font"
                                            class="color-nav-button"
                                            data-nav-action="changeFont" data-nav-direction="-1"
                                            title="{{ __('messages.previous') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                            id="next_font"
                                            class="color-nav-button"
                                            data-nav-action="changeFont" data-nav-direction="1"
                                            title="{{ __('messages.next') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('font_family')" />
                                <div id="font_preview" class="mt-3 text-4xl text-gray-900 dark:text-gray-100" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
                                    {{ $role->name }}
                                </div>
                            </div>
                    </div>

                    <!-- Background Tab Content -->
                    <div id="style-content-background" style="display: none;">

                            @php
                                $effectiveHeaderImage = $role->header_image;
                                if ($role->header_image_url && ($effectiveHeaderImage == 'none' || !$effectiveHeaderImage)) {
                                    $effectiveHeaderImage = ''; // Custom
                                }
                            @endphp
                            <div class="mb-6">
                                <x-input-label for="header_image" :value="__('messages.header_image')" />
                                <div class="flex items-center gap-1">
                                    <select id="header_image" name="header_image"
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                        data-action="header-image-input">
                                        <option value="none" {{ $effectiveHeaderImage == 'none' || (!$effectiveHeaderImage && !$role->header_image_url) ? 'SELECTED' : '' }}>
                                            {{ __('messages.none') }}</option>
                                        @foreach($headers as $header => $name)
                                        <option value="{{ $header }}"
                                            {{ $effectiveHeaderImage == $header ? 'SELECTED' : '' }}>
                                            {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button"
                                            id="prev_header"
                                            class="color-nav-button"
                                            data-nav-action="changeHeaderImage" data-nav-direction="-1"
                                            title="{{ __('messages.previous') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                            id="next_header"
                                            class="color-nav-button"
                                            data-nav-action="changeHeaderImage" data-nav-direction="1"
                                            title="{{ __('messages.next') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>

                                <div id="custom_header_input" style="display:none" class="mt-4">
                                    <input id="header_image_url" name="header_image_url" type="file" class="hidden"
                                        accept="image/png, image/jpeg" data-file-trigger="header_image_url" data-filename-target="header_image_url_filename" />
                                    <div class="mt-1 flex items-center gap-3">
                                        <button type="button" data-trigger-file-input="header_image_url"
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                            <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('messages.choose_file') }}
                                        </button>
                                        <span id="header_image_url_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                    </div>
                                    <div id="header_image_url_preview_clear" class="relative inline-block pt-3" style="display: none;">
                                        <img id="header_image_url_preview" src="#" alt="Header Image Preview" style="max-height:120px;" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                        <button type="button" id="clear-header-file-btn" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('header_image_url')" />
                                    <p id="header_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                        {{ __('messages.image_size_warning') }}
                                    </p>
                                </div>

                                <img id="header_image_preview"
                                    src="{{ $role->header_image && $role->header_image !== 'none' ? asset('images/headers/' . $role->header_image . '.png') : $role->header_image_url }}"
                                    alt="Header Image Preview"
                                    style="max-height:120px; {{ $effectiveHeaderImage && $effectiveHeaderImage !== 'none' ? '' : 'display:none;' }}"
                                    class="pt-3" />

                                @if ($role->header_image_url)
                                <div id="delete_header_image_button" class="relative inline-block mt-4 pt-1" style="display: {{ $effectiveHeaderImage ? 'none' : 'block' }};">
                                    <img src="{{ $role->header_image_url }}" style="max-height:120px" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                    <button type="button"
                                        data-delete-image-url="{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'header']) }}"
                                        data-delete-image-token="{{ csrf_token() }}"
                                        data-delete-image-parent="true"
                                        style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                @endif

                            </div>

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
                                            data-action="background-type-change">
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
                                    :value="old('background_color', $role->background_color)" data-action="update-preview-on-input" />
                                <x-input-error class="mt-2" :messages="$errors->get('background_color')" />
                            </div>

                            @php
                                $effectiveBackgroundImage = $role->background_image;
                                if ($role->background_image_url && !$effectiveBackgroundImage) {
                                    $effectiveBackgroundImage = ''; // Custom
                                }
                            @endphp
                            <div class="mb-6" id="style_background_image" style="display:none">
                                <x-input-label for="image" :value="__('messages.image')" />
                                <div class="flex items-center gap-1">
                                    <select id="background_image" name="background_image"
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                        data-action="background-image-input">
                                        @foreach($backgrounds as $background => $name)
                                        <option value="{{ $background }}"
                                            {{ $effectiveBackgroundImage == $background ? 'SELECTED' : '' }}>
                                            {{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button"
                                            id="prev_image"
                                            class="color-nav-button"
                                            data-nav-action="changeBackgroundImage" data-nav-direction="-1"
                                            title="{{ __('messages.previous') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>
                                    <button type="button"
                                            id="next_image"
                                            class="color-nav-button"
                                            data-nav-action="changeBackgroundImage" data-nav-direction="1"
                                            title="{{ __('messages.next') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>

                                <img id="background_image_thumb_preview"
                                    src="{{ $role->background_image && $role->background_image !== 'none' ? asset('images/backgrounds/thumbs/' . $role->background_image . '.jpg') : '' }}"
                                    alt="Background Image Preview"
                                    style="max-height:200px; max-width:100%; {{ $effectiveBackgroundImage && $effectiveBackgroundImage !== 'none' && $effectiveBackgroundImage !== '' ? '' : 'display:none;' }}"
                                    class="pt-3" />

                                <div id="custom_image_input" style="display:none" class="mt-4">
                                    <input id="background_image_url" name="background_image_url" type="file" class="hidden"
                                        accept="image/png, image/jpeg" data-file-trigger="background_image_url" data-filename-target="background_image_url_filename" data-update-preview="true" />
                                    <div class="mt-1 flex items-center gap-3">
                                        <button type="button" data-trigger-file-input="background_image_url"
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                            <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('messages.choose_file') }}
                                        </button>
                                        <span id="background_image_url_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                    </div>
                                    <p id="background_image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                        {{ __('messages.image_size_warning') }}
                                    </p>

                                    <div id="background_image_preview_clear" class="relative inline-block pt-3" style="display: none;">
                                        <img id="background_image_preview" src="" alt="Background Image Preview" style="max-height:120px;" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                        <button type="button" data-clear-file-input="background_image_url" data-clear-preview="background_image_preview" data-clear-filename="background_image_url_filename" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </div>

                                    @if ($role->background_image_url)
                                    <div id="background_image_existing" class="relative inline-block mt-4 pt-1">
                                        <img src="{{ $role->background_image_url }}" style="max-height:120px" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                        <button type="button"
                                            data-delete-image-url="{{ route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'background']) }}"
                                            data-delete-image-token="{{ csrf_token() }}"
                                            data-delete-image-parent="true"
                                            style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                            class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div id="style_background_gradient" style="display:none">
                                <div class="mb-6">
                                    <x-input-label for="background_colors" :value="__('messages.colors')" />
                                    <div class="flex items-center gap-1">
                                        <select id="background_colors" name="background_colors" data-action="background-colors-input"
                                            class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                            @foreach($gradients as $gradient => $name)
                                            <option value="{{ $gradient }}"
                                                {{ $role->background_colors == $gradient || (! array_key_exists($role->background_colors, $gradients) && ! $gradient) ? 'SELECTED' : '' }}>
                                                {{ $name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button"
                                                id="prev_color"
                                                class="color-nav-button"
                                                data-nav-action="changeBackgroundColor" data-nav-direction="-1"
                                                title="{{ __('messages.previous') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                                id="next_color"
                                                class="color-nav-button"
                                                data-nav-action="changeBackgroundColor" data-nav-direction="1"
                                                title="{{ __('messages.next') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-xs pt-1">
                                        <x-link href="https://uigradients.com" target="_blank">{{ __('messages.gradients_from', ['name' => 'uiGradients']) }}</x-link>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('background_colors')" />

                                    <div id="custom_colors" style="display:none" class="mt-4">
                                        <div class="flex items-center gap-3">
                                            <x-text-input id="custom_color1" name="custom_color1" type="color"
                                                class="block flex-1 h-10"
                                                :value="old('custom_color1', $role->background_colors ? explode(', ', $role->background_colors)[0] : '')"
                                                data-action="update-preview-on-input" />
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor"
                                                class="w-5 h-5 text-gray-400 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                            <x-text-input id="custom_color2" name="custom_color2" type="color"
                                                class="block flex-1 h-10"
                                                :value="old('custom_color2', $role->background_colors ? explode(', ', $role->background_colors)[1] : '')"
                                                data-action="update-preview-on-input" />
                                        </div>
                                        <div id="custom_gradient_preview" class="mt-2 h-3 rounded-full"></div>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="background_rotation" :value="__('messages.rotation')" />
                                    <div class="flex items-center gap-3 mt-1">
                                        <input id="background_rotation" name="background_rotation" type="range"
                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                                            data-action="rotation-input"
                                            value="{{ old('background_rotation', $role->background_rotation ?? 0) }}" min="0" max="360" />
                                        <span id="rotation_value" class="text-sm text-gray-600 dark:text-gray-400 w-12 text-end">{{ old('background_rotation', $role->background_rotation ?? 0) }}°</span>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('background_rotation')" />
                                </div>
                            </div>
                    </div>

                    <!-- Advanced Tab Content -->
                    <div id="style-content-advanced" style="display: none;">
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
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.default_layout_help') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('event_layout')" />
                            </div>

                            <div class="mb-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                                <x-input-label for="custom_css" :value="__('messages.custom_css')" />
                                @if ($role->isPro())
                                <textarea id="custom_css" name="custom_css" {{ is_demo_mode() ? 'disabled' : '' }}
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm font-mono text-sm"
                                    rows="6">{{ old('custom_css', $role->custom_css) }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.custom_css_help') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('custom_css')" />
                                @elseif ($role->custom_css)
                                <textarea disabled
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm font-mono text-sm opacity-60"
                                    rows="6">{{ $role->custom_css }}</textarea>
                                <p class="mt-1 text-sm text-amber-600 dark:text-amber-400">{{ __('messages.custom_css_grandfathered') }}</p>
                                @else
                                <textarea disabled
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm font-mono text-sm opacity-60"
                                    rows="3" placeholder="/* CSS */"></textarea>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('messages.custom_css_enterprise_only') }}
                                    @if (config('app.hosted'))
                                    - <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-custom-css')"
                                        class="text-[#4E81FA] hover:underline font-medium">{{ __('messages.upgrade_to_pro_plan') }}</button>
                                    @endif
                                </p>
                                @endif
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

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-subschedules">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        {{ __('messages.subschedules') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-subschedules" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            {{ __('messages.subschedules') }}
                        </h2>

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
                                            <div class="mb-4">
                                                <x-input-label :value="__('messages.color')" />
                                                <div class="vue-color-picker" data-props="{{ json_encode([
                                                    'name' => 'groups[' . (is_object($group) ? $group->id : $i) . '][color]',
                                                    'initialColor' => is_object($group) ? $group->color : ($group['color'] ?? ''),
                                                    'colors' => ['#EF4444','#F97316','#EAB308','#84CC16','#22C55E','#14B8A6','#06B6D4','#0EA5E9','#3B82F6','#6366F1','#A855F7','#EC4899','#F43F5E','#6B7280'],
                                                    'clearLabel' => __('messages.clear'),
                                                ]) }}"></div>
                                            </div>
                                            @if((is_object($group) && $group->slug) || (is_array($group) && !empty($group['slug'])))
                                            <div class="mb-4" id="group-url-display-{{ is_object($group) ? $group->id : $i }}">
                                                <p class="text-sm text-gray-500 flex items-center gap-2">
                                                    <x-link href="{{ $role->getGuestUrl(true) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}" target="_blank">
                                                        {{ \App\Utils\UrlUtils::clean($role->getGuestUrl(true)) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}
                                                    </x-link>
                                                    <button type="button" data-action="copy-group-url" data-copy-url="{{ $role->getGuestUrl(true) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
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
                                                    <button type="button" data-action="toggle-group-slug" data-group-id="{{ is_object($group) ? $group->id : $i }}" id="edit-button-{{ is_object($group) ? $group->id : $i }}" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                                        {{ __('messages.edit') }}
                                                    </button>
                                                    @if((is_object($group) && $group->slug) || (is_array($group) && !empty($group['slug'])))
                                                    <button type="button" data-action="toggle-group-slug" data-group-id="{{ is_object($group) ? $group->id : $i }}" class="hidden text-sm text-[#4E81FA] hover:text-blue-700" id="cancel-button-{{ is_object($group) ? $group->id : $i }}">
                                                        {{ __('messages.cancel') }}
                                                    </button>
                                                    @endif
                                                </div>
                                                <button type="button" data-action="remove-parent-item" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                            @else
                                            <div class="flex gap-4 items-center justify-end">
                                                <button type="button" data-action="remove-parent-item" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" data-action="add-group-field" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                    + {{ __('messages.add_subschedule') }}
                                </button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('groups')" />
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
                            <nav class="flex space-x-2 sm:space-x-6" aria-label="Tabs">
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[#4E81FA] text-[#4E81FA]" data-tab="general">
                                    {{ __('messages.general') }}
                                </button>
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="custom-fields">
                                    {{ __('messages.custom_fields') }}
                                </button>
                                @if ($role->isCurator() || ((config('app.hosted') || config('app.is_testing')) && $role->isVenue()))
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="requests">
                                    {{ __('messages.requests') }}
                                </button>
                                @endif
                                <button type="button" class="settings-tab flex-1 sm:flex-initial text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="advanced">
                                    {{ __('messages.advanced') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: General -->
                        <div id="settings-tab-general" class="settings-tab-content">

                        @if ($role->exists)
                        <div class="mb-6" id="url-display">
                            <x-input-label :value="__('messages.schedule_url')" />
                            <p class="text-sm text-gray-500 flex items-center gap-2 mt-1">
                                <x-link href="{{ $role->getGuestUrl(true) }}" target="_blank">
                                    {{ \App\Utils\UrlUtils::clean($role->getGuestUrl(true)) }}
                                </x-link>
                                <button type="button" data-action="copy-role-url" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                    </svg>
                                </button>
                                </button>
                            </p>
                            <x-secondary-button type="button" data-action="toggle-subdomain-edit" class="mt-3">
                                {{ __('messages.edit') }}
                            </x-secondary-button>
                        </div>
                        @if (!is_demo_mode())
                        <div class="hidden" id="subdomain-edit">
                            <div class="mb-6">
                                <x-input-label for="new_subdomain" :value="__('messages.subdomain')" />
                                <x-text-input id="new_subdomain" name="new_subdomain" type="text" class="mt-1 block w-full"
                                    :value="old('new_subdomain', $role->subdomain)" required minlength="4" maxlength="50"
                                    pattern="[a-z0-9-]+" data-action="subdomain-sanitize" />
                                <x-input-error class="mt-2" :messages="$errors->get('new_subdomain')" />
                            </div>

                            <div x-data="{ domain: '{{ old('custom_domain', $role->custom_domain) }}', mode: '{{ old('custom_domain_mode', $role->custom_domain_mode ?? 'redirect') }}' }">
                                <x-input-label for="custom_domain" :value="__('messages.custom_domain')" />
                                @if ($role->isEnterprise())
                                <x-text-input id="custom_domain" name="custom_domain" type="url" class="mt-1 block w-full"
                                    :value="old('custom_domain', $role->custom_domain)" x-model="domain" />
                                <x-input-error class="mt-2" :messages="$errors->get('custom_domain')" />

                                {{-- Domain mode selector --}}
                                <div class="mt-3" x-show="domain" x-cloak>
                                    @if (config('services.digitalocean.app_hostname'))
                                    <x-input-label :value="__('messages.custom_domain_mode')" />
                                    <div class="mt-2 space-y-2">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="radio" name="custom_domain_mode" value="redirect" x-model="mode"
                                                class="mt-1 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.custom_domain_mode_redirect') }}</span>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.custom_domain_mode_redirect_desc') }}</p>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="radio" name="custom_domain_mode" value="direct" x-model="mode"
                                                class="mt-1 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.custom_domain_mode_direct') }}</span>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.custom_domain_mode_direct_desc') }}</p>
                                            </div>
                                        </label>
                                    </div>
                                    @else
                                    <input type="hidden" name="custom_domain_mode" value="">
                                    @endif

                                    {{-- Redirect mode: Cloudflare guide link --}}
                                    <div x-show="mode === 'redirect'" x-cloak class="mt-3">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <x-link href="{{ marketing_url('/docs/creating-schedules#custom-domain') }}" target="_blank" class="text-sm">
                                                {{ __('messages.custom_domain_setup_guide') }}
                                            </x-link>
                                        </p>
                                    </div>

                                    {{-- Direct mode: CNAME instructions and status --}}
                                    <div x-show="mode === 'direct'" x-cloak class="mt-3">
                                        @if (config('services.digitalocean.app_hostname'))
                                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4">

                                            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('messages.custom_domain_cname_instructions', ['hostname' => config('services.digitalocean.app_hostname')]) }}</p>
                                            <ol class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-decimal list-inside mb-3">
                                                <li>{{ __('messages.cname_step_1') }}</li>
                                                <li>{{ __('messages.cname_step_2') }}</li>
                                                <li>{{ __('messages.cname_step_3') }}</li>
                                                <li>{{ __('messages.cname_step_4') }}</li>
                                            </ol>
                                            <div class="flex items-center gap-2">
                                                <code class="text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded px-2 py-1 font-mono select-all text-gray-900 dark:text-gray-100">{{ config('services.digitalocean.app_hostname') }}</code>
                                                <button type="button" data-action="copy-hostname" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy') }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Status badge --}}
                                        @if ($role->custom_domain_mode === 'direct' && $role->custom_domain_status)
                                        <div class="mt-3">
                                            @if ($role->custom_domain_status === 'active')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    {{ __('messages.domain_active') }}
                                                </span>
                                            @elseif ($role->custom_domain_status === 'pending')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                    {{ __('messages.domain_pending') }}
                                                </span>
                                            @elseif ($role->custom_domain_status === 'failed')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                    {{ __('messages.domain_failed') }}
                                                </span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <x-text-input id="custom_domain" name="custom_domain" type="url" class="mt-1 block w-full"
                                    :value="old('custom_domain', $role->custom_domain)" disabled />
                                @if (config('app.hosted'))
                                <div class="text-xs pt-1">
                                    <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-custom-domain')"
                                        class="text-[#4E81FA] hover:underline font-medium">
                                        {{ __('messages.requires_enterprise_plan') }}
                                    </button>
                                </div>
                                @else
                                <div class="text-xs pt-1 text-gray-500">{{ __('messages.requires_enterprise_plan') }}</div>
                                @endif
                                @endif
                            </div>

                            <x-secondary-button type="button" data-action="toggle-subdomain-edit" class="mt-3 mb-6">
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

                            <x-link href="{{ marketing_url('/docs/creating-schedules#settings-general') }}" target="_blank" class="text-sm mt-2">
                                {{ __('messages.show_available_variables') }}
                            </x-link>
                            <x-input-error class="mt-2" :messages="$errors->get('slug_pattern')" />
                        </div>

                        </div>
                        <!-- End Tab Content: General -->

                        <!-- Tab Content: Custom Fields -->
                        <div id="settings-tab-custom-fields" class="settings-tab-content hidden">
                        @if ($role->isPro())
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('messages.event_custom_fields_help') }}
                        </p>

                        <div id="event-custom-fields-container">
                            <input type="hidden" name="event_custom_fields_submitted" value="1">
                            @php
                                $eventCustomFields = $role->event_custom_fields ?? [];
                                uasort($eventCustomFields, fn($a, $b) => ($a['index'] ?? 999) <=> ($b['index'] ?? 999));
                                $fieldIndex = 0;
                            @endphp
                            @foreach($eventCustomFields as $fieldKey => $field)
                            <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg event-custom-field-item" data-field-key="{{ $fieldKey }}" data-field-index="{{ $field['index'] ?? '' }}">
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
                                            data-action="toggle-field-options"
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
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="event_custom_fields[{{ $fieldKey }}][required]"
                                                id="event_field_required_{{ $fieldKey }}"
                                                value="1"
                                                {{ !empty($field['required']) ? 'checked' : '' }}
                                                class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                            <label for="event_field_required_{{ $fieldKey }}" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                        </div>
                                        <input type="hidden" name="event_custom_fields[{{ $fieldKey }}][index]" value="{{ $field['index'] ?? '' }}">
                                        @if(!empty($field['index']))
                                        <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">→ {custom_{{ $field['index'] }}}</span>
                                        @endif
                                    </div>
                                    <button type="button" data-action="remove-custom-field" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                        {{ __('messages.remove') }}
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" data-action="add-custom-field" id="add-event-custom-field-btn" class="text-sm text-[#4E81FA] hover:text-blue-700 {{ count($eventCustomFields) >= 8 ? 'hidden' : '' }}">
                            + {{ __('messages.add_field') }}
                        </button>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('messages.event_custom_fields_graphic_help') }}
                        </p>
                        @else
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                {{ __('messages.custom_fields_pro_only') }}
                            </p>
                            @if (config('app.hosted'))
                            <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-custom-fields')"
                                class="text-[#4E81FA] hover:underline font-medium text-sm">{{ __('messages.upgrade_to_pro_plan') }}</button>
                            @endif
                        </div>
                        @endif
                        </div>
                        <!-- End Tab Content: Custom Fields -->

                        <!-- Tab Content: Requests -->
                        @if ($role->isCurator() || ((config('app.hosted') || config('app.is_testing')) && $role->isVenue()))
                        <div id="settings-tab-requests" class="settings-tab-content hidden">

                        @if ((config('app.hosted') || config('app.is_testing')) && ($role->isVenue() || $role->isCurator()))
                        <div class="mb-6">
                            <x-checkbox name="accept_requests"
                                label="{{ __('messages.accept_requests') }}"
                                checked="{{ old('accept_requests', $role->accept_requests) }}"
                                data-custom-attribute="value" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.accept_requests_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('accept_requests')" />
                        </div>
                        <div class="mb-6" id="require_account_section">
                            <x-checkbox name="require_account"
                                label="{{ __('messages.require_account') }}"
                                checked="{{ old('require_account', $role->exists ? $role->require_account : true) }}"
                                data-custom-attribute="value" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.require_account_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('require_account')" />
                        </div>
                        <div class="mb-6" id="require_approval_section">
                            <x-checkbox name="require_approval"
                                label="{{ __('messages.require_approval') }}"
                                checked="{{ old('require_approval', $role->exists ? $role->require_approval : true) }}"
                                data-custom-attribute="value" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.require_approval_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('require_approval')" />
                        </div>
                        <div class="mb-6" id="approved_subdomains_section">
                            <x-input-label :value="__('messages.approved_schedules')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">{{ __('messages.approved_schedules_help') }}</p>
                            <div id="approved-subdomains-items">
                                @foreach(old('approved_subdomains', $role->approved_subdomains ?? []) as $i => $subdomain)
                                    <div class="mb-2 relative">
                                        <div class="flex items-center">
                                            <input type="text" data-subdomain-search value="{{ isset($approvedSubdomainNames[$subdomain]) ? $approvedSubdomainNames[$subdomain] . ' (' . $subdomain . ')' : $subdomain }}" placeholder="{{ __('messages.search_schedules_autocomplete') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm bg-gray-50 dark:bg-gray-800" readonly autocomplete="off" />
                                            <button type="button" data-action="remove-parent-item"
                                                class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
                                        </div>
                                        <input type="hidden" name="approved_subdomains[]" value="{{ $subdomain }}" />
                                        <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto z-50"></div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" data-action="add-approved-subdomain" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                + {{ __('messages.add_schedule') }}
                            </button>
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

                        </div>
                        @endif
                        <!-- End Tab Content: Requests -->

                        <!-- Tab Content: Advanced -->
                        <div id="settings-tab-advanced" class="settings-tab-content hidden">
                            <div class="mb-6">
                                <x-input-label for="first_day_of_week" :value="__('messages.first_day_of_week')" />
                                <select name="first_day_of_week" id="first_day_of_week"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                    @foreach ([0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'] as $value => $dayName)
                                    <option value="{{ $value }}" {{ old('first_day_of_week', $role->first_day_of_week ?? 0) == $value ? 'selected' : '' }}>
                                        {{ __('messages.' . $dayName) }}
                                    </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('first_day_of_week')" />
                            </div>

                        @if ((config('app.hosted') || config('app.is_testing')) && ($role->isVenue() || $role->isCurator()))
                        <div class="mb-6" id="import_form_fields_section">
                            <x-input-label :value="__('messages.import_form_fields')" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.import_form_fields_help') }}</p>
                            @php
                                $importFields = $role->import_config['fields'] ?? [];
                                $requiredFields = $role->import_config['required_fields'] ?? [];
                                $fieldItems = [
                                    'short_description' => __('messages.short_description'),
                                    'description' => __('messages.description'),
                                    'ticket_price' => __('messages.price'),
                                    'coupon_code' => __('messages.coupon_code'),
                                    'registration_url' => __('messages.registration_url'),
                                    'category_id' => __('messages.category'),
                                ];
                                if (($role->groups ?? collect())->count() > 0) {
                                    $fieldItems['group_id'] = __('messages.subschedules');
                                }
                            @endphp
                            <div class="space-y-3">
                                @foreach ($fieldItems as $fieldKey => $fieldLabel)
                                @php
                                    $isChecked = old('import_fields.' . $fieldKey, $importFields[$fieldKey] ?? false);
                                    $isRequired = old('required_fields.' . $fieldKey, $requiredFields[$fieldKey] ?? false);
                                @endphp
                                <div class="flex items-center gap-3">
                                    <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                        <input type="hidden" name="import_fields[{{ $fieldKey }}]" value="0">
                                        <input type="checkbox" id="import_field_{{ $fieldKey }}" name="import_fields[{{ $fieldKey }}]" value="1"
                                            {{ $isChecked ? 'checked' : '' }}
                                            class="sr-only peer import-field-toggle" data-field="{{ $fieldKey }}">
                                        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[#4E81FA] transition-colors"></div>
                                        <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                    </label>
                                    <label for="import_field_{{ $fieldKey }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 w-36 cursor-pointer">{{ $fieldLabel }}</label>
                                    <label id="required_label_{{ $fieldKey }}" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 cursor-pointer" style="{{ $isChecked ? '' : 'display: none;' }}">
                                        <input type="hidden" name="required_fields[{{ $fieldKey }}]" value="0">
                                        <input type="checkbox" name="required_fields[{{ $fieldKey }}]" value="1"
                                            {{ $isRequired ? 'checked' : '' }}
                                            class="h-3.5 w-3.5 rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA]">
                                        {{ __('messages.required') }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-checkbox name="direct_registration"
                                label="{{ __('messages.direct_registration') }}"
                                checked="{{ old('direct_registration', $role->direct_registration) }}"
                                data-custom-attribute="value" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.direct_registration_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('direct_registration')" />
                        </div>

                        </div>
                        <!-- End Tab Content: Advanced -->

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
                                                <button type="button" data-action="remove-parent-item" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" data-action="add-import-url" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                    + {{ __('messages.add') }}
                                </button>
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
                                                <button type="button" data-action="remove-parent-item" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" data-action="add-import-city" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                    + {{ __('messages.add') }}
                                </button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('import_cities')" />
                        </div>

                        @if ($role->exists)
                        <div class="mb-6">
                            <x-secondary-button id="test-import-btn" type="button">
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
                            <nav class="flex space-x-2 sm:space-x-6" aria-label="Tabs">
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
                                    <x-secondary-button type="button" id="sync-events-button">
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
                <p class="text-sm text-gray-500 dark:text-gray-400 -mt-4 mb-6">{{ __('messages.email_settings_help') }}</p>

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

        <script {!! nonce_attr() !!}>
        (function() {
            var isDirty = false;
            var mainForm = document.currentScript.closest('form');

            mainForm.addEventListener('input', function() { isDirty = true; });
            mainForm.addEventListener('change', function() { isDirty = true; });
            mainForm.addEventListener('submit', function(e) {
                if (!e.defaultPrevented) { isDirty = false; }
            });

            window.addEventListener('beforeunload', function(e) {
                if (isDirty && !window._skipUnsavedWarning) { e.preventDefault(); e.returnValue = ''; }
            });

        })();
        </script>
    </form>

</x-app-admin-layout>

<script {!! nonce_attr() !!}>
// Scroll guard: force page to stay at top during all initialization.
var _scrollGuard = function() { window.scrollTo(0, 0); };
window.addEventListener('scroll', _scrollGuard);

// Toggle "Required" label visibility for import fields
document.querySelectorAll('.import-field-toggle').forEach(function(toggle) {
    var field = toggle.dataset.field;
    var label = document.getElementById('required_label_' + field);
    if (!label) return;
    label.style.display = toggle.checked ? '' : 'none';
    toggle.addEventListener('change', function() {
        label.style.display = this.checked ? '' : 'none';
        if (!this.checked) {
            var cb = label.querySelector('input[type=checkbox]');
            if (cb) cb.checked = false;
        }
    });
});

// Style sub-tab navigation
function showStyleTab(tabName) {
    // Hide all style tab contents
    const tabContents = ['branding', 'background', 'advanced'];
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
    var savedStyleTab = localStorage.getItem('styleActiveTab');
    if (savedStyleTab) {
        // Migrate old tab names to new names
        if (savedStyleTab === 'images') savedStyleTab = 'branding';
        if (savedStyleTab === 'settings') savedStyleTab = 'advanced';
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
        <div class="mb-4">
            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.color') }}</label>
            <div class="vue-color-picker" data-props='${JSON.stringify({
                name: "groups[new_" + idx + "][color]",
                initialColor: "",
                colors: ["#EF4444","#F97316","#EAB308","#84CC16","#22C55E","#14B8A6","#06B6D4","#0EA5E9","#3B82F6","#6366F1","#A855F7","#EC4899","#F43F5E","#6B7280"],
                clearLabel: @json(__('messages.clear')),
            })}'></div>
        </div>
        <div class="flex gap-4 items-center justify-end">
            <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm" data-action="remove-parent-item">
                {{ __('messages.remove') }}
            </button>
        </div>
    `;
    container.appendChild(div);
    div.querySelectorAll('.vue-color-picker').forEach(window.mountColorPicker);
}

function copyHostname(button) {
    const hostname = '{{ config("services.digitalocean.app_hostname") }}';
    navigator.clipboard.writeText(hostname).then(() => {
        const originalHTML = button.innerHTML;
        button.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-green-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        `;
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    }).catch(() => {});
}

function copyRoleUrl(button) {
    const url = '{{ $role->exists ? $role->getGuestUrl(true) : "" }}';
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
    }).catch(() => {});
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
    }).catch(() => {});
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
        alert(@json(__('messages.please_enter_urls_or_cities'), JSON_UNESCAPED_UNICODE));
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = @json(__('messages.testing'), JSON_UNESCAPED_UNICODE) + '...';
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
        showImportOutput('', @json(__("messages.import_test_error")) + ': ' + error.message, false);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
    @else
    // For new roles, just show a message
    alert(@json(__('messages.save_role_first_to_test_import'), JSON_UNESCAPED_UNICODE));
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
                        <button data-action="close-import-output" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
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
            <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm" data-action="remove-parent-item">
                {{ __('messages.remove') }}
            </button>
        </div>
    `;
    container.appendChild(div);
}

function addApprovedSubdomainField() {
    const container = document.getElementById('approved-subdomains-items');
    const div = document.createElement('div');
    div.className = 'mb-2 relative';
    div.innerHTML = `
        <div class="flex items-center">
            <input type="text" data-subdomain-search placeholder="{{ __('messages.search_schedules_autocomplete') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" autocomplete="off" />
            <button type="button" data-action="remove-parent-item"
                class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
        </div>
        <input type="hidden" name="approved_subdomains[]" value="" />
        <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto z-50"></div>
    `;
    container.appendChild(div);
    const searchInput = div.querySelector('[data-subdomain-search]');
    setupSubdomainAutocomplete(searchInput);
    searchInput.focus();
}

function setupSubdomainAutocomplete(inputEl) {
    if (!inputEl) return;
    const wrapper = inputEl.closest('.relative');
    const hiddenInput = wrapper.querySelector('input[name="approved_subdomains[]"]');
    const dropdown = wrapper.querySelector('[data-subdomain-dropdown]');
    let debounceTimer = null;
    const currentSubdomain = '{{ $role->subdomain ?? '' }}';

    inputEl.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(debounceTimer);

        if (q.length < 2) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function() {
            const selectedSubdomains = [currentSubdomain];
            document.querySelectorAll('#approved-subdomains-items input[name="approved_subdomains[]"]').forEach(function(h) {
                if (h !== hiddenInput && h.value) {
                    selectedSubdomains.push(h.value);
                }
            });
            var excludeParams = '';
            selectedSubdomains.forEach(function(s) {
                excludeParams += '&exclude[]=' + encodeURIComponent(s);
            });
            const url = '{{ route("role.search-subdomains") }}' + '?q=' + encodeURIComponent(q) + excludeParams;
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(res) { return res.json(); })
            .then(function(results) {
                dropdown.innerHTML = '';
                if (results.length === 0) {
                    dropdown.classList.add('hidden');
                    return;
                }
                results.forEach(function(item) {
                    const row = document.createElement('div');
                    row.className = 'px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700';
                    const nameText = item.name || item.subdomain;
                    const cityText = item.city ? ' <span class="text-xs text-gray-400">' + escapeHtml(item.city) + '</span>' : '';
                    row.innerHTML = '<div class="font-medium text-sm text-gray-900 dark:text-gray-100">' + escapeHtml(nameText) + cityText + '</div>'
                        + '<div class="text-xs text-gray-500 dark:text-gray-400">' + escapeHtml(item.subdomain) + '.eventschedule.com</div>';
                    row.addEventListener('click', function() {
                        hiddenInput.value = item.subdomain;
                        inputEl.value = nameText + ' (' + item.subdomain + ')';
                        inputEl.readOnly = true;
                        inputEl.classList.add('bg-gray-50', 'dark:bg-gray-800');
                        dropdown.classList.add('hidden');
                        dropdown.innerHTML = '';
                    });
                    dropdown.appendChild(row);
                });
                dropdown.classList.remove('hidden');
            });
        }, 300);
    });
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
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
            <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm" data-action="remove-parent-item">
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

    fetch('{{ url('/google-calendar/calendars') }}')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            select.innerHTML = '<option value="">' + @json(__('messages.select_a_calendar'), JSON_UNESCAPED_UNICODE) + '</option>';
            
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
                select.innerHTML = '<option value="">' + @json(__('messages.no_calendars_available'), JSON_UNESCAPED_UNICODE) + '</option>';
            }
        })
        .catch(error => {
            console.error('Error loading calendars:', error);
            let errorMessage = @json(__('messages.error_loading_calendars'), JSON_UNESCAPED_UNICODE);
            
            if (error.message.includes('401')) {
                errorMessage = @json(__('messages.google_calendar_not_connected'), JSON_UNESCAPED_UNICODE);
            } else if (error.message.includes('403')) {
                errorMessage = @json(__('messages.access_denied_calendar'), JSON_UNESCAPED_UNICODE);
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
    
    fetch('{{ url('/google-calendar/sync/' . $role->subdomain) }}', {
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
                testEmailResult.textContent = @json(__('messages.please_enter_from_address'), JSON_UNESCAPED_UNICODE);
                testEmailResult.classList.remove('hidden');
                return;
            }
            
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                testEmailResult.textContent = @json(__('messages.invalid_email_address'), JSON_UNESCAPED_UNICODE);
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
            sendTestEmailBtn.textContent = @json(__('messages.sending'), JSON_UNESCAPED_UNICODE) + '...';
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
                sendTestEmailBtn.textContent = @json(__('messages.send_test_email'), JSON_UNESCAPED_UNICODE);
                
                if (data.success) {
                    testEmailResult.className = 'mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-800 dark:text-green-200';
                    testEmailResult.textContent = data.message || @json(__('messages.test_email_sent'), JSON_UNESCAPED_UNICODE);
                } else {
                    testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                    testEmailResult.textContent = data.error || @json(__('messages.failed_to_send_test_email'), JSON_UNESCAPED_UNICODE);
                }
                testEmailResult.classList.remove('hidden');
            })
            .catch(error => {
                sendTestEmailBtn.disabled = false;
                sendTestEmailBtn.textContent = @json(__('messages.send_test_email'), JSON_UNESCAPED_UNICODE);
                testEmailResult.className = 'mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-800 dark:text-red-200';
                testEmailResult.textContent = @json(__('messages.failed_to_send_test_email'), JSON_UNESCAPED_UNICODE);
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

    // Form validation error handling
    const form = document.getElementById('edit-form');
    if (form) {
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

        // Highlight sections with server-side validation errors
        document.querySelectorAll('.section-content').forEach(section => {
            const hasErrors = section.querySelectorAll('ul.text-red-600, ul.text-red-400').length > 0;
            if (hasErrors) {
                highlightSectionError(section.id);
            }
        });

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
            } else {
                // Disable all submit buttons and show saving state
                var submitButtons = form.querySelectorAll('button[type="submit"]');
                submitButtons.forEach(function(btn) {
                    btn.disabled = true;
                    btn.textContent = @json(__('messages.saving'));
                });
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
    window.scrollTo(0, 0);
    requestAnimationFrame(function() {
        var nameField = document.getElementById('name');
        if (nameField) {
            nameField.focus({ preventScroll: true });
        }
        window.scrollTo(0, 0);
        setTimeout(function() {
            window.removeEventListener('scroll', _scrollGuard);
        }, 300);
    });
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
        if (document.getElementById('settings-tab-' + savedSettingsTab)) {
            switchSettingsTab(savedSettingsTab);
        } else {
            switchSettingsTab('general');
        }
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
                showCaldavResult(@json(__('messages.fill_all_fields'), JSON_UNESCAPED_UNICODE), 'error');
                return;
            }

            if (!serverUrl.startsWith('https://')) {
                showCaldavResult(@json(__('messages.caldav_https_required'), JSON_UNESCAPED_UNICODE), 'error');
                return;
            }

            caldavTestBtn.disabled = true;
            caldavTestBtn.textContent = @json(__('messages.testing'), JSON_UNESCAPED_UNICODE);

            fetch('{{ url('/caldav/test-connection') }}', {
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
                caldavTestBtn.textContent = @json(__('messages.test_connection'), JSON_UNESCAPED_UNICODE);

                if (data.success) {
                    showCaldavResult(@json(__('messages.connection_successful'), JSON_UNESCAPED_UNICODE), 'success');
                    // Discover calendars
                    discoverCaldavCalendars(serverUrl, username, password);
                } else {
                    showCaldavResult(data.message || @json(__('messages.connection_failed'), JSON_UNESCAPED_UNICODE), 'error');
                }
            })
            .catch(error => {
                caldavTestBtn.disabled = false;
                caldavTestBtn.textContent = @json(__('messages.test_connection'), JSON_UNESCAPED_UNICODE);
                showCaldavResult(@json(__('messages.connection_failed'), JSON_UNESCAPED_UNICODE) + ': ' + error.message, 'error');
            });
        });
    }

    // Discover Calendars
    function discoverCaldavCalendars(serverUrl, username, password) {
        fetch('{{ url('/caldav/discover-calendars') }}', {
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
                caldavCalendarSelect.innerHTML = '<option value="">' + @json(__('messages.select_a_calendar'), JSON_UNESCAPED_UNICODE) + '</option>';
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
                showCaldavResult(@json(__('messages.no_calendars_found'), JSON_UNESCAPED_UNICODE), 'error');
            }
        })
        .catch(error => {
            showCaldavResult(@json(__('messages.error_discovering_calendars'), JSON_UNESCAPED_UNICODE) + ': ' + error.message, 'error');
        });
    }

    // Connect
    if (caldavConnectBtn) {
        caldavConnectBtn.addEventListener('click', function() {
            if (!caldavSubdomain) {
                showCaldavResult(@json(__('messages.save_schedule_first'), JSON_UNESCAPED_UNICODE), 'error');
                return;
            }

            const serverUrl = document.getElementById('caldav_server_url').value.trim();
            const username = document.getElementById('caldav_username').value.trim();
            const password = document.getElementById('caldav_password').value.trim();
            const calendarUrl = caldavCalendarSelect.value;
            const syncDirection = document.querySelector('input[name="caldav_sync_direction_new"]:checked')?.value || 'to';

            if (!calendarUrl) {
                showCaldavResult(@json(__('messages.select_a_calendar'), JSON_UNESCAPED_UNICODE), 'error');
                return;
            }

            caldavConnectBtn.disabled = true;
            caldavConnectBtn.textContent = @json(__('messages.connecting'), JSON_UNESCAPED_UNICODE);

            fetch('{{ url('/caldav/settings') }}/' + caldavSubdomain, {
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
                caldavConnectBtn.textContent = @json(__('messages.connect'), JSON_UNESCAPED_UNICODE);

                if (data.success) {
                    showCaldavResult(data.message || @json(__('messages.caldav_settings_saved'), JSON_UNESCAPED_UNICODE), 'success');
                    // Reload page to show connected state
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showCaldavResult(data.message || @json(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE), 'error');
                }
            })
            .catch(error => {
                caldavConnectBtn.disabled = false;
                caldavConnectBtn.textContent = @json(__('messages.connect'), JSON_UNESCAPED_UNICODE);
                showCaldavResult(@json(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE) + ': ' + error.message, 'error');
            });
        });
    }

    // Handle sync direction changes when already connected
    const caldavSyncDirectionRadios = document.querySelectorAll('input[name="caldav_sync_direction"]');
    if (caldavSyncDirectionRadios.length > 0 && caldavSubdomain) {
        caldavSyncDirectionRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                const syncDirection = this.value;

                fetch('{{ url('/caldav/sync-direction') }}/' + caldavSubdomain, {
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
                        showCaldavResult(data.message || @json(__('messages.sync_direction_updated'), JSON_UNESCAPED_UNICODE), 'success');
                        setTimeout(() => {
                            if (caldavTestResult) {
                                caldavTestResult.classList.add('hidden');
                            }
                        }, 2000);
                    } else {
                        showCaldavResult(data.message || @json(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE), 'error');
                    }
                })
                .catch(error => {
                    showCaldavResult(@json(__('messages.failed_to_save_settings'), JSON_UNESCAPED_UNICODE) + ': ' + error.message, 'error');
                });
            });
        });
    }

    // Disconnect
    if (caldavDisconnectBtn) {
        caldavDisconnectBtn.addEventListener('click', function() {
            if (!caldavSubdomain) return;

            if (!confirm(@json(__('messages.confirm_disconnect_caldav'), JSON_UNESCAPED_UNICODE))) {
                return;
            }

            caldavDisconnectBtn.disabled = true;
            caldavDisconnectBtn.textContent = @json(__('messages.disconnecting'), JSON_UNESCAPED_UNICODE);

            fetch('{{ url('/caldav/disconnect') }}/' + caldavSubdomain, {
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
                    caldavDisconnectBtn.textContent = @json(__('messages.disconnect'), JSON_UNESCAPED_UNICODE);
                    alert(data.message || @json(__('messages.failed_to_disconnect'), JSON_UNESCAPED_UNICODE));
                }
            })
            .catch(error => {
                caldavDisconnectBtn.disabled = false;
                caldavDisconnectBtn.textContent = @json(__('messages.disconnect'), JSON_UNESCAPED_UNICODE);
                alert(@json(__('messages.failed_to_disconnect'), JSON_UNESCAPED_UNICODE) + ': ' + error.message);
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
let eventCustomFieldCounter = {{ max(array_map(function($key) { return str_starts_with($key, 'new_') ? (int) substr($key, 4) + 1 : 0; }, array_keys($role->event_custom_fields ?? ['_' => 0]))) }};
// Track used indices for stable custom field variables
let usedEventFieldIndices = @json(array_values(array_filter(array_map(fn($f) => $f['index'] ?? null, $role->event_custom_fields ?? []))));
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

function getNextAvailableFieldIndex() {
    for (let i = 1; i <= 8; i++) {
        if (!usedEventFieldIndices.includes(i)) {
            return i;
        }
    }
    return null;
}

function addEventCustomField() {
    const container = document.getElementById('event-custom-fields-container');
    const currentCount = container.querySelectorAll('.event-custom-field-item').length;

    if (currentCount >= 8) {
        return;
    }

    const fieldKey = 'new_' + eventCustomFieldCounter;
    eventCustomFieldCounter++;

    // Assign next available index
    const fieldIndex = getNextAvailableFieldIndex();
    if (fieldIndex) {
        usedEventFieldIndices.push(fieldIndex);
    }

    const fieldHtml = `
        <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg event-custom-field-item" data-field-key="${fieldKey}" data-field-index="${fieldIndex || ''}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_name') !!} *</label>
                    <input type="text" name="event_custom_fields[${fieldKey}][name]"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" required />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_type') !!}</label>
                    <select name="event_custom_fields[${fieldKey}][type]"
                        data-action="toggle-field-options"
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
                <div class="flex items-center gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="event_custom_fields[${fieldKey}][required]"
                            id="event_field_required_${fieldKey}"
                            value="1"
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                        <label for="event_field_required_${fieldKey}" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{!! __('messages.field_required') !!}</label>
                    </div>
                    <input type="hidden" name="event_custom_fields[${fieldKey}][index]" value="${fieldIndex || ''}">
                    ${fieldIndex ? `<span class="text-xs text-gray-400 dark:text-gray-500 font-mono">→ {custom_${fieldIndex}}</span>` : ''}
                </div>
                <button type="button" data-action="remove-custom-field" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
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
        // Free up the index when removing a field
        const fieldIndex = parseInt(fieldItem.dataset.fieldIndex);
        if (fieldIndex) {
            usedEventFieldIndices = usedEventFieldIndices.filter(i => i !== fieldIndex);
        }
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

function deleteRoleImage(url, token, element) {
    if (!confirm(@json(__('messages.are_you_sure'), JSON_UNESCAPED_UNICODE))) {
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
            if (element) {
                var showTarget = element.dataset.showOnDelete;
                element.remove();
                if (showTarget) {
                    var target = document.getElementById(showTarget);
                    if (target) target.style.display = '';
                }
                if (typeof toggleCustomHeaderInput === 'function') toggleCustomHeaderInput();
                if (typeof toggleCustomImageInput === 'function') toggleCustomImageInput();
            } else {
                location.reload();
            }
        } else {
            alert(@json(__('messages.failed_to_delete_image')));
        }
    });
}

// ============================================================
// Event delegation and addEventListener bindings
// (replaces all inline event handlers)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // --- Navigation action map for prev/next buttons ---
    var navActionMap = {
        changeHeaderImage: changeHeaderImage,
        changeBackgroundImage: changeBackgroundImage,
        changeBackgroundColor: changeBackgroundColor,
        changeFont: changeFont
    };

    // --- Delegated click handler on document ---
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) {
            // Check for nav action buttons
            var navBtn = e.target.closest('[data-nav-action]');
            if (navBtn) {
                var fn = navActionMap[navBtn.dataset.navAction];
                if (fn) fn(parseInt(navBtn.dataset.navDirection));
                return;
            }

            // Check for style tab buttons
            var styleTabBtn = e.target.closest('[data-style-tab]');
            if (styleTabBtn) {
                showStyleTab(styleTabBtn.dataset.styleTab);
                return;
            }

            // Check for trigger file input buttons
            var triggerBtn = e.target.closest('[data-trigger-file-input]');
            if (triggerBtn) {
                var fileInput = document.getElementById(triggerBtn.dataset.triggerFileInput);
                if (fileInput) fileInput.click();
                return;
            }

            // Check for clear file input buttons
            var clearBtn = e.target.closest('[data-clear-file-input]');
            if (clearBtn) {
                clearRoleFileInput(clearBtn.dataset.clearFileInput, clearBtn.dataset.clearPreview, clearBtn.dataset.clearFilename);
                return;
            }

            // Check for delete image buttons
            var deleteBtn = e.target.closest('[data-delete-image-url]');
            if (deleteBtn) {
                deleteRoleImage(deleteBtn.dataset.deleteImageUrl, deleteBtn.dataset.deleteImageToken, deleteBtn.parentElement);
                return;
            }

            // Check for clear header file button
            if (e.target.closest('#clear-header-file-btn')) {
                clearHeaderFileInput();
                return;
            }

            return;
        }

        var action = btn.dataset.action;

        switch (action) {
            case 'copy-role-url':
                copyRoleUrl(btn);
                break;
            case 'copy-hostname':
                copyHostname(btn);
                break;
            case 'copy-group-url':
                copyGroupUrl(btn, btn.dataset.copyUrl);
                break;
            case 'toggle-subdomain-edit':
                toggleSubdomainEdit();
                break;
            case 'toggle-group-slug':
                toggleGroupSlugEdit(btn.dataset.groupId);
                break;
            case 'remove-parent-item':
                btn.parentElement.parentElement.remove();
                break;
            case 'add-group-field':
                addGroupField();
                break;
            case 'add-import-url':
                addImportUrlField();
                break;
            case 'add-approved-subdomain':
                addApprovedSubdomainField();
                break;
            case 'add-import-city':
                addImportCityField();
                break;
            case 'add-custom-field':
                addEventCustomField();
                break;
            case 'remove-custom-field':
                removeEventCustomField(btn);
                break;
            case 'close-import-output':
                closeImportOutput();
                break;
        }
    });

    // --- Delegated input handler for data-action elements ---
    document.addEventListener('input', function(e) {
        var el = e.target.closest('[data-action]');
        if (!el) return;

        var action = el.dataset.action;

        switch (action) {
            case 'update-preview-on-input':
                updatePreview();
                break;
            case 'header-image-input':
                updatePreview();
                updateHeaderNavButtons();
                toggleCustomHeaderInput();
                break;
            case 'background-image-input':
                updatePreview();
                updateImageNavButtons();
                toggleCustomImageInput();
                break;
            case 'background-colors-input':
                updatePreview();
                updateColorNavButtons();
                break;
            case 'rotation-input':
                updatePreview();
                document.getElementById('rotation_value').textContent = el.value + '\u00B0';
                break;
            case 'subdomain-sanitize':
                el.value = el.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
                break;
        }
    });

    // --- Delegated change handler for data-action elements ---
    document.addEventListener('change', function(e) {
        var el = e.target.closest('[data-action]');
        if (!el) return;

        var action = el.dataset.action;

        switch (action) {
            case 'background-type-change':
                onChangeBackground();
                updatePreview();
                break;
            case 'font-family-change':
                onChangeFont();
                updateFontNavButtons();
                break;
            case 'toggle-field-options':
                toggleEventFieldOptions(el);
                break;
            case 'header-image-input':
                updatePreview();
                updateHeaderNavButtons();
                toggleCustomHeaderInput();
                break;
            case 'background-image-input':
                updatePreview();
                updateImageNavButtons();
                toggleCustomImageInput();
                break;
            case 'background-colors-input':
                updatePreview();
                updateColorNavButtons();
                break;
            case 'language-change':
                updateFontOptions();
                updatePreview();
                break;
        }
    });

    // --- File input change handlers (delegated) ---
    document.addEventListener('change', function(e) {
        var el = e.target.closest('[data-file-trigger]');
        if (!el) return;

        var filenameTarget = el.dataset.filenameTarget;
        if (filenameTarget) {
            var filenameEl = document.getElementById(filenameTarget);
            if (filenameEl) {
                filenameEl.textContent = el.files[0] ? el.files[0].name : '';
            }
        }

        var previewTarget = el.dataset.previewTarget;
        if (previewTarget) {
            previewImage(el, previewTarget);
        }

        if (el.dataset.updatePreview === 'true') {
            updatePreview();
        }
    });

    // --- Specific element listeners ---

    // View map button
    var viewMapBtn = document.getElementById('view_map_button');
    if (viewMapBtn) {
        viewMapBtn.addEventListener('click', function() { viewMap(); });
    }

    // Validate address button
    var validateBtn = document.getElementById('validate_button');
    if (validateBtn) {
        validateBtn.addEventListener('click', function() { onValidateClick(); });
    }

    // Accept address button
    var acceptBtn = document.getElementById('accept_button');
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function(e) { acceptAddress(e); });
    }

    // Test import button
    var testImportBtn = document.getElementById('test-import-btn');
    if (testImportBtn) {
        testImportBtn.addEventListener('click', function() { testImport(this); });
    }

    // Sync events button
    var syncEventsBtn = document.getElementById('sync-events-button');
    if (syncEventsBtn) {
        syncEventsBtn.addEventListener('click', function() { syncEvents(); });
    }

    // Re-render preview when dark mode changes
    new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                updatePreview();
            }
        });
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    // Role phone verification
    var roleSendCodeBtn = document.getElementById('role-phone-send-code-btn');
    var roleVerifyCodeBtn = document.getElementById('role-phone-verify-code-btn');

    if (roleSendCodeBtn) {
        roleSendCodeBtn.addEventListener('click', function() {
            roleSendCodeBtn.disabled = true;
            roleSendCodeBtn.textContent = '...';

            fetch('{{ $role->exists ? route("role.phone.send_code", ["subdomain" => $role->subdomain]) : "" }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            }).then(function(r) { return r.json(); }).then(function(data) {
                var msgEl = document.getElementById('role-phone-verify-message');
                if (data.success) {
                    document.getElementById('role-phone-code-input').style.display = '';
                    roleSendCodeBtn.style.display = 'none';
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-green-600 dark:text-green-400';
                    msgEl.style.display = '';
                } else {
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                    msgEl.style.display = '';
                    roleSendCodeBtn.disabled = false;
                    roleSendCodeBtn.textContent = @json(__('messages.click_here_to_verify_phone'), JSON_UNESCAPED_UNICODE);
                }
            }).catch(function() {
                roleSendCodeBtn.disabled = false;
                roleSendCodeBtn.textContent = @json(__('messages.click_here_to_verify_phone'), JSON_UNESCAPED_UNICODE);
            });
        });
    }

    if (roleVerifyCodeBtn) {
        roleVerifyCodeBtn.addEventListener('click', function() {
            var code = document.getElementById('role-phone-verification-code').value;
            roleVerifyCodeBtn.disabled = true;

            fetch('{{ $role->exists ? route("role.phone.verify_code", ["subdomain" => $role->subdomain]) : "" }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code: code })
            }).then(function(r) { return r.json(); }).then(function(data) {
                var msgEl = document.getElementById('role-phone-verify-message');
                if (data.success) {
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-green-600 dark:text-green-400';
                    msgEl.style.display = '';
                    document.getElementById('role-phone-code-input').style.display = 'none';
                    var section = document.getElementById('role-phone-verify-section');
                    if (section) {
                        var p = document.createElement('p');
                        p.className = 'text-sm mt-2 text-green-600 dark:text-green-400';
                        p.textContent = data.message;
                        section.innerHTML = '';
                        section.appendChild(p);
                    }
                } else {
                    msgEl.textContent = data.message;
                    msgEl.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                    msgEl.style.display = '';
                    roleVerifyCodeBtn.disabled = false;
                }
            }).catch(function() {
                roleVerifyCodeBtn.disabled = false;
            });
        });
    }

});

</script>

<x-upgrade-modal name="upgrade-custom-css" tier="pro" :subdomain="$role->subdomain" docsUrl="{{ route('marketing.docs.schedule_styling') }}#custom-css">
    {{ __('messages.upgrade_feature_description_custom_css') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-custom-fields" tier="pro" :subdomain="$role->subdomain" docsUrl="{{ marketing_url('/features/custom-fields') }}">
    {{ __('messages.upgrade_feature_description_custom_fields') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-custom-domain" tier="enterprise" :subdomain="$role->subdomain" docsUrl="{{ route('marketing.docs.sharing') }}#schedule-url">
    {{ __('messages.upgrade_feature_description_custom_domain') }}
</x-upgrade-modal>