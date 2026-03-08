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
            height: 210px;
            width: 100%;
            overflow: hidden;
            background-size: cover;
            background-position: center;
            padding: 10px;
            display: flex;
            align-items: flex-start;
        }

        .dark #preview {
            border-color: #2d2d30;
        }

        .color-nav-button {
            padding: 0.5rem 0.75rem;
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: linear-gradient(to bottom, #ffffff, #f9fafb);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .color-nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .color-nav-button:hover:not(:disabled) {
            background: linear-gradient(to bottom, #f3f4f6, #eef0f3);
        }

        .color-nav-button:active:not(:disabled) {
            background: linear-gradient(to bottom, #e8eaed, #e5e7eb);
        }

        .dark .color-nav-button {
            border-color: #2d2d30;
            background: linear-gradient(to bottom, #2d2d30, #282828);
        }

        .dark .color-nav-button:hover:not(:disabled) {
            background: linear-gradient(to bottom, #3e3e42, #383838);
        }

        .dark .color-nav-button:active:not(:disabled) {
            background: linear-gradient(to bottom, #252526, #222222);
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
            color: var(--brand-blue);
            border-color: var(--brand-blue);
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

            const eventRequestFormSection = document.getElementById('event_request_form_section');
            if (acceptRequestsCheckbox && eventRequestFormSection) {
                eventRequestFormSection.style.display = acceptRequestsCheckbox.checked ? 'block' : 'none';
                acceptRequestsCheckbox.addEventListener('change', function() {
                    eventRequestFormSection.style.display = this.checked ? 'block' : 'none';
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
                @foreach(array_keys(config('app.supported_languages')) as $lang)
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
            } else if (name.length > 25) {
                name = name.substring(0, 25) + '...';
            }

            $('#font_preview').text(name);

            // Resolve header image URL
            var headerUrl = '';
            if (headerImage && headerImage !== 'none' && headerImage !== '') {
                headerUrl = "{{ asset('images/headers/thumbs') }}" + '/' + headerImage + '.jpg';
            } else if (headerImage === '') {
                var customSrc = $('#header_image_url_preview').attr('src');
                headerUrl = (customSrc && customSrc !== '#') ? customSrc : '{{ $role->header_image_url }}';
            }

            // Build header image HTML
            var headerHtml = '';
            if (headerUrl) {
                headerHtml = '<div style="position: relative; width: 100%; height: 90px; border-radius: 12px 12px 0 0; overflow: hidden; flex-shrink: 0;">' +
                    '<div style="width: 100%; height: 100%; background-image: url(\'' + headerUrl + '\'); background-size: cover; background-position: center;"></div>' +
                    '<div style="position: absolute; inset: 0; background: rgba(0,0,0,0.2); border-radius: 12px 12px 0 0;"></div>' +
                '</div>';
            }

            // Resolve profile image
            var profileSrc = profileImagePreview && profileImagePreview !== '#' ? profileImagePreview : existingProfileImage;

            // Build profile image HTML
            var profileHtml = '';
            var profileBorderColor = isDark ? '#1e1e1e' : '#ffffff';
            var cardOverflow = 'hidden';
            var cardMarginTop = '';
            if (profileSrc && headerUrl) {
                // Overlapping profile image (matches GP -mt-[100px] scaled down)
                var profileAlign = isRtl ? 'margin-left: auto; margin-right: 0;' : 'margin-right: auto; margin-left: 0;';
                profileHtml = '<div style="position: relative; z-index: 10; margin-top: -26px; margin-bottom: 4px; ' + profileAlign + '">' +
                    '<div style="width: 38px; height: 38px; border-radius: 6px; background-color: ' + profileBorderColor + '; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                        '<img src="' + profileSrc + '" style="width: 34px; height: 34px; border-radius: 5px; object-fit: cover;" />' +
                    '</div>' +
                '</div>';
            } else if (profileSrc) {
                // No header image: profile protrudes above card (matches GP pt-16 + -mt-[100px])
                cardOverflow = 'visible';
                cardMarginTop = 'margin-top: 18px;';
                var profileAlign = isRtl ? 'margin-left: auto; margin-right: 0;' : 'margin-right: auto; margin-left: 0;';
                profileHtml = '<div style="position: relative; z-index: 10; margin-top: -20px; margin-bottom: 4px; ' + profileAlign + '">' +
                    '<div style="width: 38px; height: 38px; border-radius: 6px; background-color: ' + profileBorderColor + '; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' +
                        '<img src="' + profileSrc + '" style="width: 34px; height: 34px; border-radius: 5px; object-fit: cover;" />' +
                    '</div>' +
                '</div>';
            }

            // Card background (semi-transparent to show background through edges)
            var cardBg = isDark ? 'rgba(30,30,30,0.95)' : 'rgba(255,255,255,0.95)';

            // Build content HTML
            var contentTopPadding = !profileSrc && !headerUrl ? 'padding-top: 10px;' : '';
            var contentHtml =
                '<div dir="' + (isRtl ? 'rtl' : 'ltr') + '" style="width: 100%; border-radius: 16px; background-color: ' + cardBg + '; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; flex-direction: column; overflow: ' + cardOverflow + '; ' + cardMarginTop + '">' +
                    headerHtml +
                    '<div style="position: relative; z-index: 5; padding: 8px 16px 14px; display: flex; flex-direction: column; ' + contentTopPadding + '">' +
                        profileHtml +
                        '<div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">' +
                            '<div style="font-size: 13px; font-weight: 600; color: ' + fontColor + '; font-family: \'' + fontFamily + '\', sans-serif; line-height: 1.3; min-width: 0;">' + name + '</div>' +
                            '<div style="flex-shrink: 0;">' +
                                '<div style="display: inline-block; border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 600; background-color: ' + accentColor + '; color: ' + getContrastColor(accentColor) + '; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">' +
                                    followText +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            // Apply content to preview container
            var $preview = $('#preview');
            $preview.html(contentHtml);

            // Reset background styles before applying new ones
            $preview.css('background-color', '').css('background-image', '');

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
                $preview.css('background-image', gradient);
            } else if (background == 'image') {

                var backgroundImageUrl = $('#background_image').find(':selected').val();
                if (backgroundImageUrl) {
                    backgroundImageUrl = "{{ asset('images/backgrounds/thumbs') }}" + '/' + $('#background_image').find(':selected').val() + '.jpg';
                } else {
                    backgroundImageUrl = $('#background_image_preview').attr('src') || "{{ $role->background_image_url }}";
                }

                $preview.css('background-image', 'url("' + backgroundImageUrl + '")');
            } else {
                $preview.css('background-color', backgroundColor);
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
                   class="js-cancel-btn inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
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
                   class="js-cancel-btn inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
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
                            <a href="#section-details" class="section-nav-link" data-section="section-details">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                {{ __('messages.details') }}
                            </a>
                            @if ($role->isVenue())
                            <a href="#section-address" class="section-nav-link" data-section="section-address">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                {{ __('messages.venue_address') }}
                            </a>
                            @endif
                            <a href="#section-style" class="section-nav-link" data-section="section-style">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                </svg>
                                {{ __('messages.schedule_style') }}
                            </a>
                            @if ($role->exists)
                            <a href="#section-links" class="section-nav-link" data-section="section-links">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                </svg>
                                {{ __('messages.videos_and_links') }}
                            </a>
                            @endif
                            <a href="#section-subschedules" class="section-nav-link" data-section="section-subschedules">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                {{ __('messages.customize') }}
                            </a>
                            <a href="#section-settings" class="section-nav-link" data-section="section-settings">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ __('messages.schedule_settings') }}
                            </a>
                            <a href="#section-engagement" class="section-nav-link" data-section="section-engagement">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                {{ __('messages.engagement') }}
                            </a>
                            @if (! config('app.hosted'))
                            <a href="#section-auto-import" class="section-nav-link" data-section="section-auto-import">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                {{ __('messages.auto_import_settings') }}
                            </a>
                            @endif
                            <a href="#section-integrations" class="section-nav-link" data-section="section-integrations">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
                                </svg>
                                {{ __('messages.integrations') }}
                            </a>
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
                <button type="button" class="mobile-section-header" data-section="section-details">
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
                <div id="section-details" class="section-content">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            {{ __('messages.details') }}
                            @if (config('services.google.gemini_key') && !is_demo_mode())
                                @if ($role->isEnterprise())
                                    <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'ai-schedule-details')"
                                        class="ml-auto inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600"
                                        title="{{ __('messages.ai_details_generator') }}">
                                        <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                        </svg>
                                        {{ __('messages.ai_generator') }}
                                    </button>
                                @elseif (config('app.hosted'))
                                    <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-ai-details')"
                                        class="ml-auto inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 opacity-75"
                                        title="{{ __('messages.ai_details_generator') }}">
                                        <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                        </svg>
                                        {{ __('messages.ai_generator') }}
                                    </button>
                                @endif
                            @endif
                        </h2>

                        @if(! $role->exists)
                        <input type="hidden" name="type" value="{{ $role->type }}"/>
                        @endif

                        <!-- Tab Navigation -->
                        <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                                <button type="button" class="details-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="general">
                                    {{ __('messages.general') }}
                                </button>
                                <button type="button" class="details-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="localization">
                                    {{ __('messages.localization') }}
                                </button>
                                <button type="button" class="details-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="contact">
                                    {{ __('messages.contact_info') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: General -->
                        <div id="details-tab-general" class="details-tab-content">

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
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">{{ old('description', $role->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        </div>

                        <!-- Tab Content: Localization -->
                        <div id="details-tab-localization" class="details-tab-content hidden">

                        <div class="mb-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                            <x-input-label for="language_code" :value="__('messages.language') " />
                            <select name="language_code" id="language_code" required {{ is_demo_mode() ? 'disabled' : '' }}
                                data-action="language-change"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                @foreach(config('app.supported_languages') as $key => $value)
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
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                @foreach(\Carbon\CarbonTimeZone::listIdentifiers() as $timezone)
                                <option value="{{ $timezone }}" {{ $role->timezone == $timezone ? 'SELECTED' : '' }}>
                                    {{ $timezone }}
                                </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                        </div>

                        <div class="mb-6">
                            <x-toggle name="use_24_hour_time" label="{{ __('messages.use_24_hour_time_format') }}"
                                checked="{{ old('use_24_hour_time', $role->use_24_hour_time) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('use_24_hour_time')" />
                        </div>

                        </div>

                        <!-- Tab Content: Contact Info -->
                        <div id="details-tab-contact" class="details-tab-content hidden">

                        <div class="mb-6">
                            <x-input-label for="email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $role->exists ? $role->email : $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="mb-6">
                            <x-toggle name="show_email" label="{{ __('messages.show_email_address') }}"
                                checked="{{ old('show_email', $role->show_email) }}" />
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
                                        class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                                        {{ __('messages.click_here_to_verify_phone') }}
                                    </button>

                                    <div id="role-phone-code-input" style="display: none;" class="mt-2 flex items-center gap-2">
                                        <input type="text" id="role-phone-verification-code" maxlength="6" placeholder="000000"
                                            class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-center tracking-widest" />
                                        <button type="button" id="role-phone-verify-code-btn"
                                            class="inline-flex items-center px-3 py-2 bg-[var(--brand-button-bg)] text-white text-sm font-medium rounded-lg hover:bg-[var(--brand-button-bg-hover)] transition-colors">
                                            {{ __('messages.verify') }}
                                        </button>
                                    </div>

                                    <p id="role-phone-verify-message" class="mt-2 text-sm" style="display: none;"></p>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        @if (old('phone', $role->phone))
                        <div class="mb-6">
                            <x-toggle name="show_phone" label="{{ __('messages.show_phone_number') }}"
                                checked="{{ old('show_phone', $role->show_phone) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('show_phone')" />
                        </div>
                        @endif

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
                </div>

                @if ($role->isVenue())
                <button type="button" class="mobile-section-header" data-section="section-address">
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
                <div id="section-address" class="section-content lg:mt-0">
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

                <button type="button" class="mobile-section-header" data-section="section-style">
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
                <div id="section-style" class="section-content lg:mt-0">
                    <div>

                    <div class="flex flex-col xl:flex-row xl:gap-12">
                        <div class="w-full xl:w-1/2">

                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                        </svg>
                        {{ __('messages.schedule_style') }}
                        @if (config('services.google.gemini_key') && !is_demo_mode())
                            @if ($role->isEnterprise())
                                <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'ai-style-generator')"
                                    class="ml-auto inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600"
                                    title="{{ __('messages.ai_style_generator') }}">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                    </svg>
                                    {{ __('messages.ai_generator') }}
                                </button>
                            @elseif (config('app.hosted'))
                                <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-ai-style')"
                                    class="ml-auto inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 opacity-75"
                                    title="{{ __('messages.ai_style_generator') }}">
                                    <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                    </svg>
                                    {{ __('messages.ai_generator') }}
                                </button>
                            @endif
                        @endif
                    </h2>

                    <!-- Sub-Tab Navigation -->
                    <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                        <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                            <button type="button" data-style-tab="branding" id="style-tab-branding"
                                class="style-tab-button text-center whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium border-[var(--brand-blue)] text-[var(--brand-blue)]">
                                {{ __('messages.branding') }}
                            </button>
                            <button type="button" data-style-tab="background" id="style-tab-background"
                                class="style-tab-button text-center whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300">
                                {{ __('messages.background') }}
                            </button>
                            <button type="button" data-style-tab="advanced" id="style-tab-advanced"
                                class="style-tab-button text-center whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300">
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
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600">
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
                                    <img id="profile_image_preview" src="#" alt="Profile Image Preview" style="max-height:120px;" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="if($el.src && !$el.src.endsWith('#')) window.dispatchEvent(new CustomEvent('show-lightbox', {detail: $el.src}))" />
                                    <button type="button" data-clear-file-input="profile_image" data-clear-preview="profile_image_preview" data-clear-filename="profile_image_filename" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                </div>

                                @if ($role->profile_image_url)
                                <div id="profile_image_existing" class="relative inline-block mt-4 pt-1" data-show-on-delete="profile_image_choose">
                                    <img src="{{ $role->profile_image_url }}" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="window.dispatchEvent(new CustomEvent('show-lightbox', {detail: '{{ $role->profile_image_url }}'}))" />
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
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
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
                                if ($role->header_image_url && !$effectiveHeaderImage) {
                                    $effectiveHeaderImage = ''; // Custom
                                }
                            @endphp
                            <div class="mb-6">
                                <x-input-label for="header_image" :value="__('messages.header_image')" />
                                <div class="flex items-center gap-1">
                                    <select id="header_image" name="header_image"
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                        data-searchable data-action="header-image-input">
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
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600">
                                            <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('messages.choose_file') }}
                                        </button>
                                        <span id="header_image_url_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                    </div>
                                    <div id="header_image_url_preview_clear" class="relative inline-block pt-3" style="display: none;">
                                        <img id="header_image_url_preview" src="#" alt="Header Image Preview" style="max-height:120px;" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="if($el.src && !$el.src.endsWith('#')) window.dispatchEvent(new CustomEvent('show-lightbox', {detail: $el.src}))" />
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
                                    class="pt-3 cursor-pointer" @click="if($el.src) window.dispatchEvent(new CustomEvent('show-lightbox', {detail: $el.src}))" />

                                @if ($role->header_image_url)
                                <div id="delete_header_image_button" class="relative inline-block mt-4 pt-1" style="display: {{ $effectiveHeaderImage ? 'none' : 'block' }};">
                                    <img src="{{ $role->header_image_url }}" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="window.dispatchEvent(new CustomEvent('show-lightbox', {detail: '{{ $role->header_image_url }}'}))" />
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
                                            class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4"
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
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                        data-searchable data-action="background-image-input">
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
                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600">
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
                                        <img id="background_image_preview" src="" alt="Background Image Preview" style="max-height:120px;" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="if($el.src && $el.src !== window.location.href) window.dispatchEvent(new CustomEvent('show-lightbox', {detail: $el.src}))" />
                                        <button type="button" data-clear-file-input="background_image_url" data-clear-preview="background_image_preview" data-clear-filename="background_image_url_filename" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                    </div>
                                </div>

                                @if ($role->background_image_url)
                                <div id="background_image_existing" class="relative inline-block mt-4 pt-1">
                                    <img src="{{ $role->background_image_url }}" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="window.dispatchEvent(new CustomEvent('show-lightbox', {detail: '{{ $role->background_image_url }}'}))" />
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

                            <div id="style_background_gradient" style="display:none">
                                <div class="mb-6">
                                    <x-input-label for="background_colors" :value="__('messages.colors')" />
                                    <div class="flex items-center gap-1">
                                        <select id="background_colors" name="background_colors" data-searchable data-action="background-colors-input"
                                            class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
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
                                            class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm font-mono text-sm"
                                    rows="6">{{ old('custom_css', $role->custom_css) }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.custom_css_help') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('custom_css')" />
                                @elseif ($role->custom_css)
                                <textarea disabled
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm font-mono text-sm opacity-60"
                                    rows="6">{{ $role->custom_css }}</textarea>
                                <p class="mt-1 text-sm text-amber-600 dark:text-amber-400">{{ __('messages.custom_css_grandfathered') }}</p>
                                @else
                                <textarea disabled
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm font-mono text-sm opacity-60"
                                    rows="3" placeholder="/* CSS */"></textarea>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('messages.custom_css_enterprise_only') }}
                                    @if (config('app.hosted'))
                                    - <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-custom-css')"
                                        class="text-[var(--brand-blue)] hover:underline font-medium">{{ __('messages.upgrade_to_pro_plan') }}</button>
                                    @endif
                                </p>
                                @endif
                                @if (is_demo_mode())
                                <div class="mt-2 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ __('messages.demo_mode_settings_disabled') }}</span>
                                    </p>
                                </div>
                                @endif
                            </div>
                    </div>


                        </div>

                        <!-- Preview (always visible, right column on desktop) -->
                        <div class="w-full xl:w-1/2 mt-6 xl:mt-0">
                            <x-input-label :value="__('messages.preview')" />
                            <div id="preview" class="h-[210px] w-full"></div>
                        </div>
                    </div>

                    </div>
                </div>

                @if ($role->exists)
                <button type="button" class="mobile-section-header" data-section="section-links">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                        {{ __('messages.videos_and_links') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-links" class="section-content lg:mt-0">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                            {{ __('messages.videos_and_links') }}
                        </h2>

                        <!-- Tab Navigation -->
                        <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                                <button type="button" class="links-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="youtube_videos">
                                    {{ __('messages.youtube_videos') }}
                                </button>
                                <button type="button" class="links-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="social_links">
                                    {{ __('messages.social_links') }}
                                </button>
                                <button type="button" class="links-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="payment_links">
                                    {{ __('messages.payment_links') }}
                                </button>
                            </nav>
                        </div>

                        {{-- YouTube Videos --}}
                        <div id="links-tab-youtube_videos" class="links-tab-content">
                            <ul role="list" class="link-list divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                {!! (!$role->youtube_links || $role->youtube_links == '[]') ? 'style="display:none"' : '' !!}>
                                @if ($role->youtube_links && $role->youtube_links != '[]')
                                @foreach(json_decode($role->youtube_links) as $link)
                                @if ($link)
                                <li class="p-4 bg-white dark:bg-gray-800" data-link-url="{{ $link->url }}">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-shrink-0 text-gray-500 dark:text-gray-400 pt-1">
                                            <x-url-icon class="w-5 h-5" color="currentColor">
                                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                                            </x-url-icon>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <x-link href="{{ $link->url }}" target="_blank" hideIcon class="block">
                                                <h4 class="text-sm font-semibold break-words line-clamp-2 text-gray-900 dark:text-gray-100">{{ $link->name }}</h4>
                                                <img src="{{ $link->thumbnail_url }}" class="mt-2 rounded"/>
                                            </x-link>
                                        </div>
                                        <button type="button"
                                            class="btn-remove-link flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 text-sm"
                                            data-link-type="youtube_links" data-link-url="{{ $link->url }}">
                                            {{ __('messages.remove') }}
                                        </button>
                                    </div>
                                </li>
                                @endif
                                @endforeach
                                @endif
                            </ul>
                            <div class="link-empty-state text-center py-8"
                                {!! ($role->youtube_links && $role->youtube_links != '[]') ? 'style="display:none"' : '' !!}>
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_youtube_videos') }}</p>
                            </div>
                            <button type="button"
                                class="btn-show-add-link text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] mt-4"
                                data-link-type="youtube_links">
                                + {{ __('messages.add_video') }}
                            </button>
                        </div>

                        {{-- Social Links --}}
                        <div id="links-tab-social_links" class="links-tab-content hidden">
                            <ul role="list" class="link-list divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                {!! (!$role->social_links || $role->social_links == '[]') ? 'style="display:none"' : '' !!}>
                                @if ($role->social_links && $role->social_links != '[]')
                                @foreach(json_decode($role->social_links) as $link)
                                @if ($link)
                                <li class="p-4 bg-white dark:bg-gray-800" data-link-url="{{ $link->url }}">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
                                            <x-url-icon class="w-5 h-5" color="currentColor">
                                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                                            </x-url-icon>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <x-link href="{{ $link->url }}" target="_blank" hideIcon class="block">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ \App\Utils\UrlUtils::getBrand($link->url) }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ \App\Utils\UrlUtils::clean($link->url) }}</p>
                                            </x-link>
                                        </div>
                                        <button type="button"
                                            class="btn-remove-link flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 text-sm"
                                            data-link-type="social_links" data-link-url="{{ $link->url }}">
                                            {{ __('messages.remove') }}
                                        </button>
                                    </div>
                                </li>
                                @endif
                                @endforeach
                                @endif
                            </ul>
                            <div class="link-empty-state text-center py-8"
                                {!! ($role->social_links && $role->social_links != '[]') ? 'style="display:none"' : '' !!}>
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_social_links') }}</p>
                            </div>
                            <button type="button"
                                class="btn-show-add-link text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] mt-4"
                                data-link-type="social_links">
                                + {{ __('messages.add_link') }}
                            </button>
                        </div>

                        {{-- Payment Links --}}
                        <div id="links-tab-payment_links" class="links-tab-content hidden">
                            <ul role="list" class="link-list divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                {!! (!$role->payment_links || $role->payment_links == '[]') ? 'style="display:none"' : '' !!}>
                                @if ($role->payment_links && $role->payment_links != '[]')
                                @foreach(json_decode($role->payment_links) as $link)
                                @if ($link)
                                <li class="p-4 bg-white dark:bg-gray-800" data-link-url="{{ $link->url }}">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 text-gray-500 dark:text-gray-400">
                                            <x-url-icon class="w-5 h-5" color="currentColor">
                                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                                            </x-url-icon>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <x-link href="{{ $link->url }}" target="_blank" hideIcon class="block">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ \App\Utils\UrlUtils::getBrand($link->url) }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ \App\Utils\UrlUtils::clean($link->url) }}</p>
                                            </x-link>
                                        </div>
                                        <button type="button"
                                            class="btn-remove-link flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 text-sm"
                                            data-link-type="payment_links" data-link-url="{{ $link->url }}">
                                            {{ __('messages.remove') }}
                                        </button>
                                    </div>
                                </li>
                                @endif
                                @endforeach
                                @endif
                            </ul>
                            <div class="link-empty-state text-center py-8"
                                {!! ($role->payment_links && $role->payment_links != '[]') ? 'style="display:none"' : '' !!}>
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_payment_links') }}</p>
                            </div>
                            <button type="button"
                                class="btn-show-add-link text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] mt-4"
                                data-link-type="payment_links">
                                + {{ __('messages.add_link') }}
                            </button>
                        </div>

                        <input type="hidden" name="youtube_links" id="youtube_links_data"
                            value="{{ $role->youtube_links ?? '[]' }}">
                        <input type="hidden" name="social_links" id="social_links_data"
                            value="{{ $role->social_links ?? '[]' }}">
                        <input type="hidden" name="payment_links" id="payment_links_data"
                            value="{{ $role->payment_links ?? '[]' }}">

                    </div>
                </div>

                {{-- Add Link Modal --}}
                <div id="add_link_modal" class="hidden relative z-10" aria-labelledby="add-link-modal-title" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" aria-hidden="true"></div>
                    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <input type="hidden" id="link_type" />
                                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-start shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                    <div>
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100" id="add-link-modal-title">
                                                {{ __('messages.add_link') }}</h3>
                                            <div class="mt-2">
                                                <x-text-input id="link" type="url" class="mt-1 block w-full" autofocus />
                                                <p id="link-error" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display:none"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                        <x-brand-button type="button" id="btn-save-link" class="w-full sm:col-start-2">{{ __('messages.save') }}</x-brand-button>
                                        <button type="button"
                                            class="btn-hide-add-link mt-3 inline-flex w-full items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors sm:col-start-1 sm:mt-0"
                                            >{{ __('messages.cancel') }}</button>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                @endif

                <button type="button" class="mobile-section-header" data-section="section-subschedules">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        {{ __('messages.customize') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-subschedules" class="section-content lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                            </svg>
                            {{ __('messages.customize') }}
                        </h2>

                        <!-- Tab Navigation -->
                        <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                                <button type="button" class="customize-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="subschedules">
                                    {{ __('messages.subschedules') }}
                                </button>
                                <button type="button" class="customize-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="custom-fields">
                                    {{ __('messages.custom_fields') }}
                                </button>
                                <button type="button" class="customize-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="custom-labels">
                                    {{ __('messages.custom_labels') }}
                                </button>
                                <button type="button" class="customize-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="sponsors">
                                    {{ __('messages.sponsors') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: Sub-schedules -->
                        <div id="customize-tab-subschedules" class="customize-tab-content">

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
                                                    <x-link href="{{ $role->getGuestUrl(true) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}" target="_blank" class="min-w-0 break-all">
                                                        {{ \App\Utils\UrlUtils::clean($role->getGuestUrl(true)) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}
                                                    </x-link>
                                                    <button type="button" data-action="copy-group-url" data-copy-url="{{ $role->getGuestUrl(true) }}/{{ is_object($group) ? $group->slug : $group['slug'] ?? '' }}" class="flex-shrink-0 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
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
                                                    <button type="button" data-action="toggle-group-slug" data-group-id="{{ is_object($group) ? $group->id : $i }}" id="edit-button-{{ is_object($group) ? $group->id : $i }}" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                                        {{ __('messages.edit') }}
                                                    </button>
                                                    @if((is_object($group) && $group->slug) || (is_array($group) && !empty($group['slug'])))
                                                    <button type="button" data-action="toggle-group-slug" data-group-id="{{ is_object($group) ? $group->id : $i }}" class="hidden text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" id="cancel-button-{{ is_object($group) ? $group->id : $i }}">
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
                                <button type="button" data-action="add-group-field" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                    + {{ __('messages.add_subschedule') }}
                                </button>
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('groups')" />
                        </div>

                        </div>
                        <!-- End Tab Content: Sub-schedules -->

                        <!-- Tab Content: Custom Fields -->
                        <div id="customize-tab-custom-fields" class="customize-tab-content hidden">
                        @if ($role->isPro())
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('messages.event_custom_fields_help') }}
                        </p>

                        <input type="hidden" name="event_custom_fields_submitted" value="1">
                        <div id="event-custom-fields-container">
                            @php
                                $eventCustomFields = $role->event_custom_fields ?? [];
                                $fieldIndex = 0;
                            @endphp
                            @foreach($eventCustomFields as $fieldKey => $field)
                            <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg event-custom-field-item flex items-start gap-3" data-field-key="{{ $fieldKey }}" data-field-index="{{ $field['index'] ?? '' }}">
                                <div class="custom-field-drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0 mt-1 {{ count($eventCustomFields) > 1 ? '' : 'hidden' }}">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
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
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                            <option value="string" {{ ($field['type'] ?? 'string') === 'string' ? 'selected' : '' }}>{{ __('messages.type_string') }}</option>
                                            <option value="multiline_string" {{ ($field['type'] ?? '') === 'multiline_string' ? 'selected' : '' }}>{{ __('messages.type_multiline_string') }}</option>
                                            <option value="switch" {{ ($field['type'] ?? '') === 'switch' ? 'selected' : '' }}>{{ __('messages.type_switch') }}</option>
                                            <option value="date" {{ ($field['type'] ?? '') === 'date' ? 'selected' : '' }}>{{ __('messages.type_date') }}</option>
                                            <option value="dropdown" {{ ($field['type'] ?? '') === 'dropdown' ? 'selected' : '' }}>{{ __('messages.type_dropdown') }}</option>
                                            <option value="multiselect" {{ ($field['type'] ?? '') === 'multiselect' ? 'selected' : '' }}>{{ __('messages.type_multiselect') }}</option>
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
                                <div class="mt-3 event-field-options-container" style="{{ in_array($field['type'] ?? '', ['dropdown', 'multiselect']) ? '' : 'display: none;' }}">
                                    <x-input-label :value="__('messages.field_options')" class="text-sm" />
                                    <x-text-input type="text" name="event_custom_fields[{{ $fieldKey }}][options]"
                                        value="{{ $field['options'] ?? '' }}"
                                        class="mt-1 block w-full"
                                        :placeholder="__('messages.options_placeholder')" />
                                </div>
                                <div class="mt-3">
                                    <x-input-label :value="__('messages.ai_prompt_custom_field')" class="text-sm" />
                                    <textarea name="event_custom_fields[{{ $fieldKey }}][ai_prompt]"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm ai-prompt-textarea"
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
                                                class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
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
                            </div>
                            @endforeach
                        </div>

                        <button type="button" data-action="add-custom-field" id="add-event-custom-field-btn" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] {{ count($eventCustomFields) >= 10 ? 'hidden' : '' }}">
                            + {{ __('messages.add_field') }}
                        </button>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('messages.event_custom_fields_graphic_help') }}
                        </p>
                        @else
                        <x-upgrade-prompt tier="pro" :learnMoreUrl="route('marketing.custom_fields')" :subdomain="$role->subdomain">
                            <x-slot:icon>
                                <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </x-slot:icon>
                            {{ __('messages.custom_fields_pro_only') }}
                        </x-upgrade-prompt>
                        @endif
                        </div>
                        <!-- End Tab Content: Custom Fields -->

                        <!-- Tab Content: Sponsors -->
                        <div id="customize-tab-sponsors" class="customize-tab-content hidden">
                        @if ($role->isPro())
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.sponsor_logos_help') }}</p>

                            <input type="hidden" name="existing_sponsors" id="existing_sponsors_input" value="{{ $role->sponsor_logos ?? '[]' }}" />

                            @php
                                $existingSponsors = json_decode($role->sponsor_logos ?? '[]', true) ?: [];
                            @endphp

                            <div id="sponsors-list" class="space-y-3 mb-6">
                                @foreach ($existingSponsors as $index => $sponsor)
                                @php
                                    $logoUrl = '';
                                    if (!empty($sponsor['logo'])) {
                                        if (str_starts_with($sponsor['logo'], 'demo_')) {
                                            $logoUrl = url('/images/demo/' . $sponsor['logo']);
                                        } elseif (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                                            $logoUrl = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $sponsor['logo'];
                                        } elseif (config('filesystems.default') == 'local') {
                                            $logoUrl = url('/storage/' . $sponsor['logo']);
                                        } else {
                                            $logoUrl = $sponsor['logo'];
                                        }
                                    }
                                @endphp
                                <div class="sponsor-item flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg" data-sponsor='@json($sponsor)' data-logo-url="{{ $logoUrl }}">
                                    <div class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-shrink-0 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 flex items-center justify-center overflow-hidden" style="width: 120px; height: 80px;">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $sponsor['name'] ?? '' }}" class="max-w-full max-h-full object-contain" />
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $sponsor['name'] ?? '' }}</div>
                                        @if (!empty($sponsor['tier']))
                                            <span class="inline-block text-xs px-1.5 py-0.5 rounded
                                                {{ $sponsor['tier'] === 'gold' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                                                {{ $sponsor['tier'] === 'silver' ? 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' : '' }}
                                                {{ $sponsor['tier'] === 'bronze' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : '' }}
                                            ">{{ __('messages.' . $sponsor['tier']) }}</span>
                                        @endif
                                        @if (!empty($sponsor['url']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $sponsor['url'] }}</div>
                                        @endif
                                    </div>
                                    <button type="button" data-action="edit-sponsor" class="flex-shrink-0 text-gray-400 hover:text-[var(--brand-blue)] transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button type="button" data-action="remove-sponsor" class="flex-shrink-0 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>

                            <div id="sponsor-limit-message" class="mb-4 {{ count($existingSponsors) >= 12 ? '' : 'hidden' }}">
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>{{ __('messages.max_sponsors_reached') }}</span>
                                    </p>
                                </div>
                            </div>

                            <div id="add-sponsor-form" class="{{ count($existingSponsors) >= 12 ? 'hidden' : '' }}">
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg border-dashed">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <x-input-label for="new_sponsor_name_input" :value="__('messages.sponsor_name')" />
                                            <input type="text" id="new_sponsor_name_input" maxlength="100"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm" />
                                        </div>
                                        <div>
                                            <x-input-label for="new_sponsor_url_input" :value="__('messages.sponsor_url')" />
                                            <input type="url" id="new_sponsor_url_input" maxlength="500"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <x-input-label :value="__('messages.sponsor_tier')" />
                                            <select id="new_sponsor_tier_input"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm">
                                                <option value="">—</option>
                                                <option value="gold">{{ __('messages.gold') }}</option>
                                                <option value="silver">{{ __('messages.silver') }}</option>
                                                <option value="bronze">{{ __('messages.bronze') }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label id="sponsor-logo-label" :value="__('messages.logo') . ' *'" />
                                            <input type="file" id="new_sponsor_logo_input" accept="image/*"
                                                class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[var(--brand-button-bg)] file:text-white hover:file:bg-[var(--brand-button-bg-hover)]"
                                                />
                                            <img id="sponsor_logo_preview" src="#" alt="Logo Preview" style="max-height:120px; display:none;" class="mt-2 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" @click="if($el.src && !$el.src.endsWith('#')) window.dispatchEvent(new CustomEvent('show-lightbox', {detail: $el.src}))" />
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" data-action="add-sponsor" id="sponsor-action-btn"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-[var(--brand-button-bg)] rounded-lg hover:bg-[var(--brand-button-bg-hover)] transition-colors">
                                            <svg id="sponsor-action-icon" class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span id="sponsor-action-text">{{ __('messages.add_sponsor') }}</span>
                                        </button>
                                        <button type="button" data-action="cancel-edit-sponsor" id="cancel-edit-sponsor-btn"
                                            class="hidden inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                            {{ __('messages.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="new-sponsor-inputs-container"></div>
                        @else
                            <x-upgrade-prompt tier="pro" :learnMoreUrl="route('marketing.custom_css')" :subdomain="$role->subdomain">
                                <x-slot:icon>
                                    <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.41a2.25 2.25 0 013.182 0l2.909 2.91M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                                    </svg>
                                </x-slot:icon>
                                {{ __('messages.upgrade_sponsor_logos') }}
                            </x-upgrade-prompt>
                        @endif
                        </div>
                        <!-- End Tab Content: Sponsors -->

                        <!-- Tab Content: Custom Labels -->
                        <div id="customize-tab-custom-labels" class="customize-tab-content hidden">
                        @if ($role->isPro())
                        <input type="hidden" name="custom_labels_submitted" value="1">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ __('messages.custom_labels_help') }}
                        </p>

                        <div class="flex gap-2 mb-6">
                            <select id="custom-label-select" data-searchable class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                <option value="">{{ __('messages.select_label_to_customize') }}</option>
                                @php
                                    $existingLabelKeys = array_keys($role->custom_labels ?? []);
                                    $availableLabels = collect(\App\Models\Role::getCustomizableLabels())
                                        ->filter(fn($v, $k) => !in_array($k, $existingLabelKeys))
                                        ->mapWithKeys(fn($v, $k) => [$k => __('messages.' . $k)])
                                        ->sort(SORT_LOCALE_STRING);
                                @endphp
                                @foreach($availableLabels as $labelKey => $labelTranslated)
                                    <option value="{{ $labelKey }}">{{ $labelTranslated }}</option>
                                @endforeach
                            </select>
                            <button type="button" data-action="add-custom-label" disabled
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-[var(--brand-button-bg)] rounded-lg hover:bg-[var(--brand-button-bg-hover)] transition-colors opacity-50 cursor-not-allowed">
                                <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('messages.add') }}
                            </button>
                        </div>

                        <div id="custom-labels-list" class="space-y-3">
                            @foreach(collect($role->custom_labels ?? [])->sortBy(fn($v, $k) => __('messages.' . $k), SORT_LOCALE_STRING) as $labelKey => $labelData)
                            <div class="custom-label-item p-4 border border-gray-200 dark:border-gray-700 rounded-lg" data-label-key="{{ $labelKey }}">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.' . $labelKey) }}</span>
                                    <button type="button" data-action="remove-custom-label" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                        {{ __('messages.remove') }}
                                    </button>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <x-text-input type="text" name="custom_labels[{{ $labelKey }}][value]"
                                            value="{{ $labelData['value'] ?? '' }}"
                                            class="mt-1 block w-full"
                                            placeholder="{{ __('messages.' . $labelKey) }}"
                                            maxlength="200" />
                                    </div>
                                    @if($role->language_code !== 'en')
                                    <div>
                                        <x-input-label :value="__('messages.english_name')" class="text-sm" />
                                        <x-text-input type="text" name="custom_labels[{{ $labelKey }}][value_en]"
                                            value="{{ $labelData['value_en'] ?? '' }}"
                                            class="mt-1 block w-full"
                                            :placeholder="__('messages.auto_translated_placeholder')"
                                            maxlength="200" />
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <x-upgrade-prompt tier="pro" :learnMoreUrl="route('marketing.custom_labels')" :subdomain="$role->subdomain">
                            <x-slot:icon>
                                <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6z" />
                                </svg>
                            </x-slot:icon>
                            {{ __('messages.custom_labels_pro_only') }}
                        </x-upgrade-prompt>
                        @endif
                        </div>
                        <!-- End Tab Content: Custom Labels -->

                    </div>
                </div>

                <button type="button" class="mobile-section-header" data-section="section-settings">
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
                <div id="section-settings" class="section-content lg:mt-0">
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
                        <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                                <button type="button" class="settings-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="general">
                                    {{ __('messages.general') }}
                                </button>
                                <button type="button" class="settings-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="notifications">
                                    {{ __('messages.notifications') }}
                                </button>
                                <button type="button" class="settings-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="advanced">
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
                                <x-link href="{{ $role->getGuestUrl(true) }}" target="_blank" class="min-w-0 break-all">
                                    {{ \App\Utils\UrlUtils::clean($role->getGuestUrl(true)) }}
                                </x-link>
                                <button type="button" data-action="copy-role-url" class="flex-shrink-0 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
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
                        <div class="hidden" id="subdomain-edit" x-data="{ domain: '{{ old('custom_domain', $role->custom_domain) }}', mode: '{{ old('custom_domain_mode', $role->custom_domain_mode) ?: 'subdomain' }}' }">
                            <input type="hidden" name="custom_domain_mode" :value="mode === 'subdomain' ? '' : mode">

                            {{-- Mode radios (hosted only) --}}
                            @if (config('app.hosted'))
                            <div class="mb-6">
                                <x-input-label :value="__('messages.custom_domain_mode')" />
                                <div class="mt-2 space-y-2">
                                    {{-- Subdomain (always enabled) --}}
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="radio" value="subdomain" x-model="mode"
                                            x-on:change="domain = ''"
                                            class="mt-1 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.custom_domain_mode_subdomain') }}</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.custom_domain_mode_subdomain_desc') }}</p>
                                        </div>
                                    </label>
                                    {{-- Redirect (Enterprise-gated) --}}
                                    <label class="flex items-start gap-3{{ $role->isEnterprise() ? ' cursor-pointer' : '' }}">
                                        <input type="radio" value="redirect" x-model="mode"
                                            class="mt-1 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                                            {{ !$role->isEnterprise() ? 'disabled' : '' }}>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white{{ !$role->isEnterprise() ? ' opacity-50' : '' }}">{{ __('messages.custom_domain_mode_redirect') }}</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400{{ !$role->isEnterprise() ? ' opacity-50' : '' }}">{{ __('messages.custom_domain_mode_redirect_desc') }}</p>
                                            @if (!$role->isEnterprise())
                                            <div class="text-xs pt-1">
                                                <button type="button" x-on:click.prevent="$dispatch('open-modal', 'upgrade-custom-domain')"
                                                    class="text-[var(--brand-blue)] hover:underline font-medium">
                                                    {{ __('messages.upgrade_enterprise_custom_domain') }}
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </label>
                                    {{-- Direct (Enterprise-gated, DO config required) --}}
                                    @if (config('services.digitalocean.app_hostname'))
                                    <label class="flex items-start gap-3{{ $role->isEnterprise() ? ' cursor-pointer' : '' }}">
                                        <input type="radio" value="direct" x-model="mode"
                                            class="mt-1 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                                            {{ !$role->isEnterprise() ? 'disabled' : '' }}>
                                        <div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white{{ !$role->isEnterprise() ? ' opacity-50' : '' }}">{{ __('messages.custom_domain_mode_direct') }}</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400{{ !$role->isEnterprise() ? ' opacity-50' : '' }}">{{ __('messages.custom_domain_mode_direct_desc') }}</p>
                                            @if (!$role->isEnterprise())
                                            <div class="text-xs pt-1">
                                                <button type="button" x-on:click.prevent="$dispatch('open-modal', 'upgrade-custom-domain')"
                                                    class="text-[var(--brand-blue)] hover:underline font-medium">
                                                    {{ __('messages.upgrade_enterprise_custom_domain') }}
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </label>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Custom domain (shown for redirect/direct modes) --}}
                            <div class="mb-6" x-show="mode === 'redirect' || mode === 'direct'" x-cloak>
                                <x-input-label for="custom_domain" :value="__('messages.custom_domain')" />
                                @if ($role->isEnterprise())
                                <x-text-input id="custom_domain" name="custom_domain" type="url" class="mt-1 block w-full"
                                    :value="old('custom_domain', $role->custom_domain)" x-model="domain" />
                                <x-input-error class="mt-2" :messages="$errors->get('custom_domain')" />

                                {{-- CNAME instructions and status --}}
                                <div class="mt-3" x-show="domain" x-cloak>
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
                                @endif
                            </div>

                            {{-- Subdomain/Path field --}}
                            <div class="mb-6" x-show="mode !== 'direct'" x-cloak>
                                <x-input-label for="new_subdomain" :value="config('app.hosted') ? __('messages.subdomain') : __('messages.path')" />
                                <x-text-input id="new_subdomain" name="new_subdomain" type="text" class="mt-1 block w-full"
                                    :value="old('new_subdomain', $role->subdomain)" x-bind:required="mode !== 'direct'" minlength="4" maxlength="50"
                                    pattern="[a-z0-9-]+" data-action="subdomain-sanitize" />
                                <x-input-error class="mt-2" :messages="$errors->get('new_subdomain')" />
                            </div>

                            <div x-show="mode === 'redirect' || mode === 'direct'" x-cloak class="mb-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <x-link href="{{ marketing_url('/docs/creating-schedules#custom-domain') }}" target="_blank" class="text-sm">
                                        {{ __('messages.custom_domain_setup_guide') }}
                                    </x-link>
                                </p>
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

                        <!-- Tab Content: Notifications -->
                        <div id="settings-tab-notifications" class="settings-tab-content hidden">

                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('messages.notification_settings_help') }}</p>

                        <div class="mb-6" id="notification_new_request_section">
                            <x-toggle name="notification_new_request"
                                label="{{ __('messages.notify_new_request') }}"
                                checked="{{ old('notification_new_request', $notificationSettings['new_request'] ?? false) }}"
                                help="{{ __('messages.notify_new_request_help') }}" />
                        </div>

                        <div class="mb-6">
                            <x-toggle name="notification_new_fan_content"
                                label="{{ __('messages.notify_new_fan_content') }}"
                                checked="{{ old('notification_new_fan_content', $notificationSettings['new_fan_content'] ?? false) }}"
                                help="{{ __('messages.notify_new_fan_content_help') }}" />
                        </div>

                        @php $emailDisabled = config('app.hosted') && ! $role->hasEmailSettings(); @endphp

                        @if ($emailDisabled)
                            <hr class="my-6 border-gray-200 dark:border-gray-700">
                            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>
                                        {{ __('messages.notification_requires_email_settings') }}
                                        <a href="#section-integrations" class="js-email-settings-link text-[var(--brand-blue)] hover:underline font-medium">{{ __('messages.configure_email_settings') }}</a>
                                    </span>
                                </p>
                            </div>
                        @endif

                        <div class="mb-6">
                            <x-toggle name="notification_new_sale"
                                label="{{ __('messages.notify_new_sale') }}"
                                checked="{{ old('notification_new_sale', $notificationSettings['new_sale'] ?? false) }}"
                                help="{{ __('messages.notify_new_sale_help') }}"
                                :disabled="$emailDisabled" />
                        </div>

                        <div class="mb-6">
                            <x-toggle name="notification_new_feedback"
                                label="{{ __('messages.notify_new_feedback') }}"
                                checked="{{ old('notification_new_feedback', $notificationSettings['new_feedback'] ?? false) }}"
                                help="{{ __('messages.notify_new_feedback_help') }}"
                                :disabled="$emailDisabled" />
                        </div>

                        <div class="mb-6">
                            <x-toggle name="notification_new_poll_option"
                                label="{{ __('messages.notify_new_poll_option') }}"
                                checked="{{ old('notification_new_poll_option', $notificationSettings['new_poll_option'] ?? false) }}"
                                help="{{ __('messages.notify_new_poll_option_help') }}"
                                :disabled="$emailDisabled" />
                        </div>

                        </div>
                        <!-- End Tab Content: Notifications -->

                        <!-- Tab Content: Advanced -->
                        <div id="settings-tab-advanced" class="settings-tab-content hidden">
                            <div class="mb-6">
                                <x-input-label for="first_day_of_week" :value="__('messages.first_day_of_week')" />
                                <select name="first_day_of_week" id="first_day_of_week"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                    @foreach ([0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'] as $value => $dayName)
                                    <option value="{{ $value }}" {{ old('first_day_of_week', $role->first_day_of_week ?? 0) == $value ? 'selected' : '' }}>
                                        {{ __('messages.' . $dayName) }}
                                    </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('first_day_of_week')" />
                            </div>

                        @if (isset($availableCurators) && $availableCurators->count() > 0 && !$role->isCurator())
                            <div class="mb-6">
                                <x-input-label :value="__('messages.default_curator_schedules')" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.default_curator_schedules_help') }}</p>
                                @php $selectedCuratorIds = old('default_curator_ids', $role->default_curator_ids ?? []); @endphp
                                <input type="hidden" name="default_curator_ids[]" value="">
                                @foreach ($availableCurators as $curator)
                                <label class="flex items-center mb-2">
                                    <input type="checkbox" name="default_curator_ids[]" value="{{ $curator->id }}"
                                        {{ in_array($curator->id, $selectedCuratorIds ?: []) ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)] dark:bg-gray-900" />
                                    <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ $curator->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="mb-6">
                            <x-toggle name="direct_registration"
                                label="{{ __('messages.direct_registration') }}"
                                checked="{{ old('direct_registration', $role->direct_registration) }}"
                                help="{{ __('messages.direct_registration_help') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('direct_registration')" />
                        </div>

                        @if (config('app.hosted') || config('app.is_testing'))
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
                                        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                        <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                    </label>
                                    <label for="import_field_{{ $fieldKey }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 w-36 cursor-pointer">{{ $fieldLabel }}</label>
                                    <label id="required_label_{{ $fieldKey }}" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 cursor-pointer" style="{{ $isChecked ? '' : 'display: none;' }}">
                                        <input type="hidden" name="required_fields[{{ $fieldKey }}]" value="0">
                                        <input type="checkbox" name="required_fields[{{ $fieldKey }}]" value="1"
                                            {{ $isRequired ? 'checked' : '' }}
                                            class="h-3.5 w-3.5 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        {{ __('messages.required') }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        </div>
                        <!-- End Tab Content: Advanced -->

                    </div>
                </div>

                <button type="button" class="mobile-section-header" data-section="section-engagement">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        {{ __('messages.engagement') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-engagement" class="section-content lg:mt-0">
                    <div class="max-w-2xl">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            {{ __('messages.engagement') }}
                        </h2>

                        @php $showRequestsTab = $role->isCurator() || ((config('app.hosted') || config('app.is_testing')) && ($role->isVenue() || $role->isTalent())); @endphp

                        <!-- Tab Navigation -->
                        <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                                @if ($showRequestsTab)
                                <button type="button" class="engagement-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="requests">
                                    {{ __('messages.requests') }}
                                </button>
                                @endif
                                <button type="button" class="engagement-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 {{ $showRequestsTab ? 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' : 'border-[var(--brand-blue)] text-[var(--brand-blue)]' }}" data-tab="fan_content">
                                    {{ __('messages.fan_content') }}
                                </button>
                                <button type="button" class="engagement-tab text-center whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="feedback">
                                    {{ __('messages.feedback') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content: Requests -->
                        @if ($showRequestsTab)
                        <div id="engagement-tab-requests" class="engagement-tab-content">

                        @if ((config('app.hosted') || config('app.is_testing')) && ($role->isVenue() || $role->isCurator() || $role->isTalent()))
                        <div class="mb-6">
                            <x-toggle name="accept_requests"
                                label="{{ __('messages.accept_requests') }}"
                                checked="{{ old('accept_requests', $role->accept_requests) }}"
                                help="{{ __($role->isTalent() ? 'messages.accept_requests_help_talent' : 'messages.accept_requests_help') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('accept_requests')" />
                        </div>
                        @if (! $role->isTalent())
                        <div class="mb-6" id="event_request_form_section">
                            <x-input-label :value="__('messages.event_request_form')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">{{ __('messages.event_request_form_help') }}</p>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                                    <input type="radio" name="event_request_form" value="import"
                                        {{ old('event_request_form', $role->event_request_form ?? 'import') === 'import' ? 'checked' : '' }}
                                        class="mt-0.5 h-4 w-4 border-gray-300" style="accent-color: var(--brand-blue)">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.ai_import') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.ai_import_description') }}</div>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                                    <input type="radio" name="event_request_form" value="booking"
                                        {{ old('event_request_form', $role->event_request_form ?? 'import') === 'booking' ? 'checked' : '' }}
                                        class="mt-0.5 h-4 w-4 border-gray-300" style="accent-color: var(--brand-blue)">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.booking_form') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.booking_form_description') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endif
                        <div class="mb-6" id="require_account_section">
                            <x-toggle name="require_account"
                                label="{{ __('messages.require_account') }}"
                                checked="{{ old('require_account', $role->exists ? $role->require_account : true) }}"
                                help="{{ __('messages.require_account_help') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('require_account')" />
                        </div>
                        <div class="mb-6" id="require_approval_section">
                            <x-toggle name="require_approval"
                                label="{{ __('messages.require_approval') }}"
                                checked="{{ old('require_approval', $role->exists ? $role->require_approval : true) }}"
                                help="{{ __('messages.require_approval_help') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('require_approval')" />
                        </div>
                        <div class="mb-6" id="approved_subdomains_section">
                            <x-input-label :value="__('messages.approved_schedules')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">{{ __('messages.approved_schedules_help') }}</p>
                            <div id="approved-subdomains-items">
                                @foreach(old('approved_subdomains', $role->approved_subdomains ?? []) as $i => $subdomain)
                                    <div class="mb-2 relative">
                                        <div class="flex items-center">
                                            <input type="text" data-subdomain-search value="{{ isset($approvedSubdomainNames[$subdomain]) ? $approvedSubdomainNames[$subdomain] . ' (' . $subdomain . ')' : $subdomain }}" placeholder="{{ __('messages.search_schedules_autocomplete') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800" readonly autocomplete="off" />
                                            <button type="button" data-action="remove-parent-item"
                                                class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
                                        </div>
                                        <input type="hidden" name="approved_subdomains[]" value="{{ $subdomain }}" />
                                        <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto z-50"></div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" data-action="add-approved-subdomain" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                + {{ __('messages.add_schedule') }}
                            </button>
                        </div>
                        <div class="mb-6" id="request_terms_section">
                            <x-input-label for="request_terms" :value="__('messages.request_terms')" />
                            <textarea id="request_terms" name="request_terms"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                rows="4"
                                dir="auto"
                                placeholder="{{ __('messages.enter_request_terms') }}">{{ old('request_terms', $role->request_terms) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('request_terms')" />
                        </div>
                        @endif

                        </div>
                        @endif
                        <!-- End Tab Content: Requests -->

                        <!-- Tab Content: Fan Content -->
                        <div id="engagement-tab-fan_content" class="engagement-tab-content {{ $showRequestsTab ? 'hidden' : '' }}">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.fan_content_help') }}</p>
                            <div class="mb-4">
                                <x-toggle name="fan_comments_enabled"
                                    label="{{ __('messages.fan_comments_enabled') }}"
                                    checked="{{ old('fan_comments_enabled', $role->fan_comments_enabled) }}" />
                            </div>
                            <div class="mb-4">
                                <x-toggle name="fan_photos_enabled"
                                    label="{{ __('messages.fan_photos_enabled') }}"
                                    checked="{{ old('fan_photos_enabled', $role->fan_photos_enabled) }}" />
                            </div>
                            <div class="mb-4">
                                <x-toggle name="fan_videos_enabled"
                                    label="{{ __('messages.fan_videos_enabled') }}"
                                    checked="{{ old('fan_videos_enabled', $role->fan_videos_enabled) }}" />
                            </div>
                        </div>
                        <!-- End Tab Content: Fan Content -->

                        <!-- Tab Content: Feedback -->
                        <div id="engagement-tab-feedback" class="engagement-tab-content hidden">

                        @php $emailDisabled = config('app.hosted') && ! $role->hasEmailSettings(); @endphp

                        @if ($emailDisabled)
                            <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>
                                        {{ __('messages.notification_requires_email_settings') }}
                                        <a href="#section-integrations" class="js-email-settings-link text-[var(--brand-blue)] hover:underline font-medium">{{ __('messages.configure_email_settings') }}</a>
                                    </span>
                                </p>
                            </div>
                        @endif

                        <div class="mb-6">
                            <x-toggle name="feedback_enabled"
                                label="{{ __('messages.feedback_enabled') }}"
                                checked="{{ old('feedback_enabled', $role->feedback_enabled) }}"
                                help="{{ __('messages.feedback_enabled_help') }}"
                                :disabled="$emailDisabled" />
                        </div>

                        <div class="mb-6" id="feedback-delay-wrapper" style="{{ $role->feedback_enabled && ! $emailDisabled ? '' : 'display: none;' }}">
                            <x-input-label for="feedback_delay_hours" value="{{ __('messages.feedback_delay') }}" />
                            <select id="feedback_delay_hours" name="feedback_delay_hours" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" {{ $emailDisabled ? 'disabled' : '' }}>
                                @foreach ([1, 2, 6, 12, 24, 48] as $hours)
                                <option value="{{ $hours }}" {{ old('feedback_delay_hours', $role->feedback_delay_hours ?? 24) == $hours ? 'selected' : '' }}>
                                    {{ $hours }} {{ __('messages.feedback_hours') }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        </div>
                        <!-- End Tab Content: Feedback -->
                    </div>
                </div>

                @if (! config('app.hosted'))
                <button type="button" class="mobile-section-header" data-section="section-auto-import">
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
                <div id="section-auto-import" class="section-content lg:mt-0">
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
                                <button type="button" data-action="add-import-url" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
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
                                <button type="button" data-action="add-import-city" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
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

                <button type="button" class="mobile-section-header" data-section="section-integrations">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
                        </svg>
                        {{ __('messages.integrations') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-integrations" class="section-content lg:mt-0">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
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
                        <div class="ap-tab-container border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                                @if (config('app.hosted'))
                                <button type="button" class="integration-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-[var(--brand-blue)] text-[var(--brand-blue)]" data-tab="email">
                                    {{ __('messages.email_settings') }}
                                </button>
                                @endif
                                <button type="button" class="integration-tab text-center px-3 py-2 text-sm font-medium border-b-2 {{ config('app.hosted') ? 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' : 'border-[var(--brand-blue)] text-[var(--brand-blue)]' }}" data-tab="google">
                                    Google Calendar
                                </button>
                                <button type="button" class="integration-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="caldav">
                                    {{ __('messages.caldav_calendar') }}
                                </button>
                                <button type="button" class="integration-tab feeds-tab text-center px-3 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600" data-tab="feeds">
                                    {{ __('messages.feeds') }}
                                </button>
                            </nav>
                        </div>

                        @if (config('app.hosted'))
                        <!-- Tab Content: Email Settings -->
                        <div id="integration-tab-email" class="integration-tab-content">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('messages.email_settings_help') }}</p>

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
                                <select id="email_settings_encryption" name="email_settings[encryption]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
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
                        @endif

                        <!-- Tab Content: Google Calendar -->
                        <div id="integration-tab-google" class="integration-tab-content {{ config('app.hosted') ? 'hidden' : '' }}">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                {{ __('messages.sync_events_between_schedules') }}
                            </p>

                            @if (auth()->user()->google_token)
                            <div class="space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                                <!-- Calendar Selection -->
                                <div>
                                    <x-input-label for="google-calendar-select" :value="__('messages.select_google_calendar')" />
                                    <select id="google-calendar-select" name="google_calendar_id" data-searchable class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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
                                    <select id="caldav_calendar_url" data-searchable class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        <option value="">{{ __('messages.select_a_calendar') }}</option>
                                    </select>
                                </div>

                                <div id="caldav-sync-direction-container" class="hidden">
                                    <x-input-label :value="__('messages.sync_direction')" />
                                    <div class="mt-2 space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="caldav_sync_direction_new" value="to" checked
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.to_caldav') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="caldav_sync_direction_new" value="from"
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
                                            <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="font-medium">{{ __('messages.from_caldav') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="caldav_sync_direction_new" value="both"
                                                   class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
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

                        <!-- Tab Content: Feeds -->
                        <div id="integration-tab-feeds" class="integration-tab-content hidden">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                {{ __('messages.feeds_description') }}
                            </p>

                            @if ($role->exists && $role->subdomain)
                            <div class="space-y-6">
                                <!-- iCal Feed -->
                                <div>
                                    <x-input-label :value="__('messages.ical_feed')" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-2">{{ __('messages.ical_feed_description') }}</p>
                                    <div class="flex items-center gap-2">
                                        <x-text-input id="ical_feed_url" type="text" class="block w-full" readonly
                                            value="{{ route('feed.ical', ['subdomain' => $role->subdomain]) }}" />
                                        <button type="button" onclick="copyFeedUrl('ical_feed_url', this)" class="shrink-0 inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- RSS Feed -->
                                <div>
                                    <x-input-label :value="__('messages.rss_feed')" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-2">{{ __('messages.rss_feed_description') }}</p>
                                    <div class="flex items-center gap-2">
                                        <x-text-input id="rss_feed_url" type="text" class="block w-full" readonly
                                            value="{{ route('feed.rss', ['subdomain' => $role->subdomain]) }}" />
                                        <button type="button" onclick="copyFeedUrl('rss_feed_url', this)" class="shrink-0 inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">{{ __('messages.save_schedule_first_for_feeds') }}</p>
                            @endif
                        </div>

                        </div>
                    </div>
                </div>

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

<script src="{{ asset('js/sortable.min.js') }}" {!! nonce_attr() !!}></script>

<script {!! nonce_attr() !!}>
var isNewSchedule = {{ $role->exists ? 'false' : 'true' }};

// Scroll guard: force page to stay at top during all initialization.
var _scrollGuard = function() { window.scrollTo(0, 0); };
window.addEventListener('scroll', _scrollGuard);

// Toggle feedback delay dropdown visibility
var feedbackToggle = document.querySelector('input[name="feedback_enabled"][type="checkbox"]');
var feedbackDelayWrapper = document.getElementById('feedback-delay-wrapper');
if (feedbackToggle && feedbackDelayWrapper) {
    feedbackToggle.addEventListener('change', function() {
        feedbackDelayWrapper.style.display = this.checked ? '' : 'none';
    });
}

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
            button.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
            button.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        } else {
            button.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
            button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }
    });

    localStorage.setItem('styleActiveTab', tabName);
}

// Restore active style tab from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    if (!isNewSchedule) {
        var savedStyleTab = localStorage.getItem('styleActiveTab');
        if (savedStyleTab) {
            // Migrate old tab names to new names
            if (savedStyleTab === 'images') savedStyleTab = 'branding';
            if (savedStyleTab === 'settings') savedStyleTab = 'advanced';
            if (savedStyleTab === 'sponsors') savedStyleTab = 'branding';
            showStyleTab(savedStyleTab);
        }
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
            <input name="groups[new_${idx}][name]" type="text" id="group_name_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
        </div>
        @if($role->language_code !== 'en' || app()->getLocale() !== 'en')
        <div class="mb-4">
            <label for="group_name_en_new_${idx}" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.english_name') }}</label>
            <input name="groups[new_${idx}][name_en]" type="text" id="group_name_en_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
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

function copyFeedUrl(inputId, button) {
    const url = document.getElementById(inputId).value;
    navigator.clipboard.writeText(url).then(() => {
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
        <div id="import-output-modal" class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white dark:bg-gray-800">
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
                        <button data-action="close-import-output" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
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
            <input name="import_urls[new_${idx}]" type="url" id="import_url_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
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
            <input type="text" data-subdomain-search placeholder="{{ __('messages.search_schedules_autocomplete') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" autocomplete="off" />
            <button type="button" data-action="remove-parent-item"
                class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
        </div>
        <input type="hidden" name="approved_subdomains[]" value="" />
        <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto z-50"></div>
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
            <input name="import_cities[new_${idx}]" type="text" id="import_city_new_${idx}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" placeholder="{{ __('messages.placeholder_city') }}" />
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
                    if (data.details) {
                        testEmailResult.textContent += '\n\n' + data.details;
                        testEmailResult.style.whiteSpace = 'pre-wrap';
                    }
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
                link.classList.add('nav-active', 'bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[var(--brand-blue)]');
                link.classList.remove('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
            } else {
                link.classList.remove('nav-active', 'bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[var(--brand-blue)]');
                link.classList.add('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
            }
        });

        // Sync mobile accordion headers
        syncMobileHeaders(sectionId);

        // Update URL hash
        if (history.replaceState) {
            history.replaceState(null, null, '#' + sectionId);
        } else {
            window.location.hash = sectionId;
        }

        // Prevent scroll if requested
        if (preventScroll) {
            window.scrollTo(0, 0);
        }
    }

    // Reset to first tab if the section is already active
    function resetToFirstTab(sectionId) {
        const section = document.getElementById(sectionId);
        if (section && section.style.display === 'block') {
            const tabs = section.querySelectorAll('[data-tab], [data-style-tab]');
            for (const tab of tabs) {
                if (tab.offsetParent !== null) {
                    tab.click();
                    break;
                }
            }
            return true;
        }
        return false;
    }

    // Handle navigation link clicks
    sectionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            if (!resetToFirstTab(sectionId)) {
                showSection(sectionId);
            }
        });
    });

    // Handle mobile accordion header clicks
    mobileHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');
            if (!resetToFirstTab(sectionId)) {
                showSection(sectionId);
            }
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
    if (!isNewSchedule) {
        const savedTab = localStorage.getItem('integrationActiveTab');
        if (savedTab && document.getElementById('integration-tab-' + savedTab)) {
            switchIntegrationTab(savedTab);
        }
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
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
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

    // Email settings link handler
    document.querySelectorAll('.js-email-settings-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var emailTab = document.querySelector('.integration-tab[data-tab="email"]');
            if (emailTab) {
                switchIntegrationTab('email');
                localStorage.setItem('integrationActiveTab', 'email');
            }
            // Navigate to integrations section
            var section = document.getElementById('section-integrations');
            if (section) {
                document.querySelectorAll('.section-nav-link').forEach(function(navLink) {
                    if (navLink.dataset.section === 'section-integrations') {
                        navLink.click();
                    }
                });
            }
        });
    });
});

// Engagement tabs switching
document.addEventListener('DOMContentLoaded', function() {
    const engagementTabs = document.querySelectorAll('.engagement-tab');
    const engagementTabContents = document.querySelectorAll('.engagement-tab-content');

    if (engagementTabs.length === 0) return;

    // Restore active tab from localStorage
    if (!isNewSchedule) {
        const savedEngagementTab = localStorage.getItem('engagementActiveTab');
        if (savedEngagementTab) {
            if (document.getElementById('engagement-tab-' + savedEngagementTab)) {
                switchEngagementTab(savedEngagementTab);
            } else {
                switchEngagementTab(document.querySelector('.engagement-tab').dataset.tab);
            }
        }
    }

    engagementTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchEngagementTab(tabName);
            localStorage.setItem('engagementActiveTab', tabName);
        });
    });

    function switchEngagementTab(tabName) {
        // Update tab buttons
        engagementTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        // Update tab contents
        engagementTabContents.forEach(content => {
            const contentId = content.id.replace('engagement-tab-', '');
            if (contentId === tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
});

// Links tabs switching
document.addEventListener('DOMContentLoaded', function() {
    const linksTabs = document.querySelectorAll('.links-tab');
    const linksTabContents = document.querySelectorAll('.links-tab-content');

    if (linksTabs.length === 0) return;

    // Restore active tab from localStorage
    if (!isNewSchedule) {
        const savedLinksTab = localStorage.getItem('linksActiveTab');
        if (savedLinksTab) {
            if (document.getElementById('links-tab-' + savedLinksTab)) {
                switchLinksTab(savedLinksTab);
            } else {
                switchLinksTab(document.querySelector('.links-tab').dataset.tab);
            }
        }
    }

    linksTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchLinksTab(tabName);
            localStorage.setItem('linksActiveTab', tabName);
        });
    });

    function switchLinksTab(tabName) {
        // Update tab buttons
        linksTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        // Update tab contents
        linksTabContents.forEach(content => {
            const contentId = content.id.replace('links-tab-', '');
            if (contentId === tabName) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }
});

// Customize tabs switching
document.addEventListener('DOMContentLoaded', function() {
    const customizeTabs = document.querySelectorAll('.customize-tab');
    const customizeTabContents = document.querySelectorAll('.customize-tab-content');

    // Restore active tab from localStorage
    if (!isNewSchedule) {
        const savedCustomizeTab = localStorage.getItem('customizeActiveTab');
        if (savedCustomizeTab) {
            if (document.getElementById('customize-tab-' + savedCustomizeTab)) {
                switchCustomizeTab(savedCustomizeTab);
            } else {
                switchCustomizeTab('subschedules');
            }
        }
    }

    customizeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchCustomizeTab(tabName);
            localStorage.setItem('customizeActiveTab', tabName);
        });
    });

    function switchCustomizeTab(tabName) {
        // Update tab buttons
        customizeTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        // Update tab contents
        customizeTabContents.forEach(content => {
            const contentId = content.id.replace('customize-tab-', '');
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
    if (!isNewSchedule) {
        const savedSettingsTab = localStorage.getItem('settingsActiveTab');
        if (savedSettingsTab) {
            // Migrate old tab name
            if (savedSettingsTab === 'custom-fields') {
                localStorage.removeItem('settingsActiveTab');
                switchSettingsTab('general');
            } else if (document.getElementById('settings-tab-' + savedSettingsTab)) {
                switchSettingsTab(savedSettingsTab);
            } else {
                switchSettingsTab('general');
            }
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
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
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

// Details tabs switching
document.addEventListener('DOMContentLoaded', function() {
    const detailsTabs = document.querySelectorAll('.details-tab');
    const detailsTabContents = document.querySelectorAll('.details-tab-content');

    // Restore active tab from localStorage
    if (!isNewSchedule) {
        const savedDetailsTab = localStorage.getItem('detailsActiveTab');
        if (savedDetailsTab) {
            if (document.getElementById('details-tab-' + savedDetailsTab)) {
                switchDetailsTab(savedDetailsTab);
            } else {
                switchDetailsTab('general');
            }
        }
    }

    detailsTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchDetailsTab(tabName);
            localStorage.setItem('detailsActiveTab', tabName);
        });
    });

    function switchDetailsTab(tabName) {
        // Update tab buttons
        detailsTabs.forEach(tab => {
            if (tab.dataset.tab === tabName) {
                tab.classList.add('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            } else {
                tab.classList.remove('border-[var(--brand-blue)]', 'text-[var(--brand-blue)]');
                tab.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400', 'hover:text-gray-700', 'dark:hover:text-gray-300', 'hover:border-gray-300', 'dark:hover:border-gray-600');
            }
        });

        // Update tab contents
        detailsTabContents.forEach(content => {
            const contentId = content.id.replace('details-tab-', '');
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
    for (let i = 1; i <= 10; i++) {
        if (!usedEventFieldIndices.includes(i)) {
            return i;
        }
    }
    return null;
}

function addEventCustomField() {
    const container = document.getElementById('event-custom-fields-container');
    const currentCount = container.querySelectorAll('.event-custom-field-item').length;

    if (currentCount >= 10) {
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
        <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg event-custom-field-item flex items-start gap-3" data-field-key="${fieldKey}" data-field-index="${fieldIndex || ''}">
            <div class="custom-field-drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0 mt-1">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_name') !!} *</label>
                    <input type="text" name="event_custom_fields[${fieldKey}][name]"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" required />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_type') !!}</label>
                    <select name="event_custom_fields[${fieldKey}][type]"
                        data-action="toggle-field-options"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                        <option value="string">{!! __('messages.type_string') !!}</option>
                        <option value="multiline_string">{!! __('messages.type_multiline_string') !!}</option>
                        <option value="switch">{!! __('messages.type_switch') !!}</option>
                        <option value="date">{!! __('messages.type_date') !!}</option>
                        <option value="dropdown">{!! __('messages.type_dropdown') !!}</option>
                        <option value="multiselect">{!! __('messages.type_multiselect') !!}</option>
                    </select>
                </div>
            </div>
            @if($role->language_code !== 'en')
            <div class="mt-3">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.english_name') !!}</label>
                <input type="text" name="event_custom_fields[${fieldKey}][name_en]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                    placeholder="{!! __('messages.auto_translated_placeholder') !!}" />
            </div>
            @endif
            <div class="mt-3 event-field-options-container" style="display: none;">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.field_options') !!}</label>
                <input type="text" name="event_custom_fields[${fieldKey}][options]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                    placeholder="{!! __('messages.options_placeholder') !!}" />
            </div>
            <div class="mt-3">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.ai_prompt_custom_field') !!}</label>
                <textarea name="event_custom_fields[${fieldKey}][ai_prompt]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm ai-prompt-textarea"
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
                            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
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
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
    const newTextarea = container.querySelector('.event-custom-field-item:last-child .ai-prompt-textarea');
    if (newTextarea) {
        newTextarea.placeholder = getRandomAiPromptPlaceholder();
    }
    // Show all drag handles when there are 2+ fields
    const fieldItems = container.querySelectorAll('.event-custom-field-item');
    if (fieldItems.length > 1) {
        container.querySelectorAll('.custom-field-drag-handle').forEach(h => h.classList.remove('hidden'));
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
        // Hide drag handles when only 1 field remains
        const container = document.getElementById('event-custom-fields-container');
        if (container.querySelectorAll('.event-custom-field-item').length <= 1) {
            container.querySelectorAll('.custom-field-drag-handle').forEach(h => h.classList.add('hidden'));
        }
        updateEventCustomFieldButton();
    }
}

function toggleEventFieldOptions(selectElement) {
    const fieldItem = selectElement.closest('.event-custom-field-item');
    const optionsContainer = fieldItem.querySelector('.event-field-options-container');
    if (selectElement.value === 'dropdown' || selectElement.value === 'multiselect') {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
}

function updateEventCustomFieldButton() {
    const container = document.getElementById('event-custom-fields-container');
    const currentCount = container.querySelectorAll('.event-custom-field-item').length;
    const addButton = document.getElementById('add-event-custom-field-btn');
    if (currentCount >= 10) {
        addButton.classList.add('hidden');
    } else {
        addButton.classList.remove('hidden');
    }
}

@if ($role->isPro())
var customLabelDefaults = @json(collect(\App\Models\Role::getCustomizableLabels())->mapWithKeys(fn($v, $k) => [$k => __('messages.' . $k)]));
var showEnField = {{ ($role->language_code !== 'en') ? 'true' : 'false' }};

// Enable/disable Add button based on select value
document.getElementById('custom-label-select').addEventListener('change', function() {
    var btn = document.querySelector('[data-action="add-custom-label"]');
    if (this.value) {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});

function addCustomLabel() {
    var select = document.getElementById('custom-label-select');
    var key = select.value;
    if (!key) return;

    // Check for duplicates
    if (document.querySelector('.custom-label-item[data-label-key="' + key + '"]')) return;

    var displayName = customLabelDefaults[key] || key;

    var enFieldHtml = '';
    if (showEnField) {
        enFieldHtml = `
            <div>
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{!! __('messages.english_name') !!}</label>
                <input type="text" name="custom_labels[${key}][value_en]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                    placeholder="{!! __('messages.auto_translated_placeholder') !!}"
                    maxlength="200" />
            </div>`;
    }

    var newItem = document.createElement('div');
    newItem.className = 'custom-label-item p-4 border border-gray-200 dark:border-gray-700 rounded-lg';
    newItem.dataset.labelKey = key;
    newItem.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${displayName}</span>
            <button type="button" data-action="remove-custom-label" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                {!! __('messages.remove') !!}
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <input type="text" name="custom_labels[${key}][value]"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                    placeholder="${displayName}"
                    maxlength="200" />
            </div>
            ${enFieldHtml}
        </div>
    `;

    // Insert in alphabetical order
    var list = document.getElementById('custom-labels-list');
    var items = list.querySelectorAll('.custom-label-item');
    var inserted = false;
    for (var i = 0; i < items.length; i++) {
        var itemName = items[i].querySelector('span').textContent.trim();
        if (displayName.localeCompare(itemName) < 0) {
            list.insertBefore(newItem, items[i]);
            inserted = true;
            break;
        }
    }
    if (!inserted) {
        list.appendChild(newItem);
    }

    // Remove option from select
    var option = select.querySelector('option[value="' + key + '"]');
    if (option) option.remove();
    select.value = '';

    // Disable Add button
    var btn = document.querySelector('[data-action="add-custom-label"]');
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
}

function removeCustomLabel(btn) {
    var item = btn.closest('.custom-label-item');
    if (!item) return;

    var key = item.dataset.labelKey;
    var displayName = customLabelDefaults[key] || key;

    // Restore option to select in sorted position
    var select = document.getElementById('custom-label-select');
    var option = document.createElement('option');
    option.value = key;
    option.textContent = displayName;

    var options = select.querySelectorAll('option');
    var inserted = false;
    for (var i = 1; i < options.length; i++) { // skip first placeholder option
        if (displayName.localeCompare(options[i].textContent) < 0) {
            select.insertBefore(option, options[i]);
            inserted = true;
            break;
        }
    }
    if (!inserted) {
        select.appendChild(option);
    }

    item.remove();
}
@endif

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
// Sponsor logo management
// ============================================================
var sponsorFileCounter = 0;
var editingSponsorItem = null;

function updateSponsorHiddenInput() {
    var items = document.querySelectorAll('#sponsors-list .sponsor-item');
    var sponsors = [];
    items.forEach(function(item) {
        if (item.dataset.newIdx !== undefined) return;
        var data = JSON.parse(item.dataset.sponsor);
        sponsors.push(data);
    });
    var input = document.getElementById('existing_sponsors_input');
    if (input) input.value = JSON.stringify(sponsors);
    updateSponsorLimitVisibility();
}

function updateSponsorLimitVisibility() {
    var count = document.querySelectorAll('#sponsors-list .sponsor-item').length;
    var limitMsg = document.getElementById('sponsor-limit-message');
    var addForm = document.getElementById('add-sponsor-form');
    if (limitMsg) limitMsg.classList.toggle('hidden', count < 12);
    if (addForm) addForm.classList.toggle('hidden', count >= 12);
}

function previewSponsorLogo(input) {
    var preview = document.getElementById('sponsor_logo_preview');
    if (!input || !input.files || !input.files[0]) {
        if (preview) { preview.style.display = 'none'; preview.src = '#'; }
        return;
    }
    var reader = new FileReader();
    reader.onloadend = function() {
        preview.src = reader.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}

function buildSponsorCardHTML(name, tierBadge, urlDisplay, logoSrc) {
    return '<div class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg></div>' +
        '<div class="flex-shrink-0 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 flex items-center justify-center overflow-hidden" style="width: 120px; height: 80px;">' + (logoSrc ? '<img src="' + logoSrc + '" class="max-w-full max-h-full object-contain" />' : '') + '</div>' +
        '<div class="flex-1 min-w-0"><div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">' + escapeHtml(name) + '</div>' + tierBadge + urlDisplay + '</div>' +
        '<button type="button" data-action="edit-sponsor" class="flex-shrink-0 text-gray-400 hover:text-[var(--brand-blue)] transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>' +
        '<button type="button" data-action="remove-sponsor" class="flex-shrink-0 text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>';
}

function buildTierBadge(tier) {
    if (tier === 'gold') return '<span class="inline-block text-xs px-1.5 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">' + @json(__('messages.gold')) + '</span>';
    if (tier === 'silver') return '<span class="inline-block text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">' + @json(__('messages.silver')) + '</span>';
    if (tier === 'bronze') return '<span class="inline-block text-xs px-1.5 py-0.5 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">' + @json(__('messages.bronze')) + '</span>';
    return '';
}

function editSponsor(btn) {
    var item = btn.closest('.sponsor-item');
    if (!item) return;

    // Reset any previous edit state
    resetSponsorEditState();

    var sponsorData = JSON.parse(item.dataset.sponsor);
    editingSponsorItem = item;

    // Populate form fields
    var nameInput = document.getElementById('new_sponsor_name_input');
    var urlInput = document.getElementById('new_sponsor_url_input');
    var tierInput = document.getElementById('new_sponsor_tier_input');
    var fileInput = document.getElementById('new_sponsor_logo_input');

    if (nameInput) nameInput.value = sponsorData.name || '';
    if (urlInput) urlInput.value = sponsorData.url || '';
    if (tierInput) tierInput.value = sponsorData.tier || '';
    if (fileInput) fileInput.value = '';

    // Highlight the card being edited
    item.classList.add('ring-2', 'ring-[var(--brand-blue)]');

    // Switch button to Save mode
    var actionText = document.getElementById('sponsor-action-text');
    var actionIcon = document.getElementById('sponsor-action-icon');
    if (actionText) actionText.textContent = @json(__('messages.save'));
    if (actionIcon) actionIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';

    // Show cancel button
    var cancelBtn = document.getElementById('cancel-edit-sponsor-btn');
    if (cancelBtn) cancelBtn.classList.remove('hidden');

    // Remove asterisk from logo label (logo optional when editing)
    var logoLabel = document.getElementById('sponsor-logo-label');
    if (logoLabel) logoLabel.textContent = logoLabel.textContent.replace(' *', '');

    // Scroll form into view
    var form = document.getElementById('add-sponsor-form');
    if (form) form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetSponsorEditState() {
    if (editingSponsorItem) {
        editingSponsorItem.classList.remove('ring-2', 'ring-[var(--brand-blue)]');
    }
    editingSponsorItem = null;

    // Clear form
    var nameInput = document.getElementById('new_sponsor_name_input');
    var urlInput = document.getElementById('new_sponsor_url_input');
    var tierInput = document.getElementById('new_sponsor_tier_input');
    var fileInput = document.getElementById('new_sponsor_logo_input');
    if (nameInput) nameInput.value = '';
    if (urlInput) urlInput.value = '';
    if (tierInput) tierInput.value = '';
    if (fileInput) fileInput.value = '';
    var logoPreview = document.getElementById('sponsor_logo_preview');
    if (logoPreview) { logoPreview.style.display = 'none'; logoPreview.src = '#'; }

    // Restore Add mode
    var actionText = document.getElementById('sponsor-action-text');
    var actionIcon = document.getElementById('sponsor-action-icon');
    if (actionText) actionText.textContent = @json(__('messages.add_sponsor'));
    if (actionIcon) actionIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />';

    // Hide cancel button
    var cancelBtn = document.getElementById('cancel-edit-sponsor-btn');
    if (cancelBtn) cancelBtn.classList.add('hidden');

    // Restore asterisk on logo label
    var logoLabel = document.getElementById('sponsor-logo-label');
    if (logoLabel && logoLabel.textContent.indexOf('*') === -1) {
        logoLabel.textContent = logoLabel.textContent + ' *';
    }
}

function cancelEditSponsor() {
    resetSponsorEditState();
}

function addSponsor() {
    var nameInput = document.getElementById('new_sponsor_name_input');
    var urlInput = document.getElementById('new_sponsor_url_input');
    var tierInput = document.getElementById('new_sponsor_tier_input');
    var fileInput = document.getElementById('new_sponsor_logo_input');

    var name = nameInput ? nameInput.value.trim() : '';
    var url = urlInput ? urlInput.value.trim() : '';
    var tier = tierInput ? tierInput.value : '';
    var hasNewFile = fileInput && fileInput.files.length > 0;

    // If editing an existing sponsor
    if (editingSponsorItem) {
        var item = editingSponsorItem;
        var tierBadge = buildTierBadge(tier);
        var urlDisplay = url ? '<div class="text-xs text-gray-500 dark:text-gray-400 truncate">' + escapeHtml(url) + '</div>' : '';

        if (item.dataset.newIdx !== undefined) {
            // Editing a new (unsaved) sponsor: update hidden inputs
            var idx = item.dataset.newIdx;
            var container = document.getElementById('new-sponsor-inputs-container');
            var nameHidden = container.querySelector('[name="new_sponsor_names[' + idx + ']"]');
            var urlHidden = container.querySelector('[name="new_sponsor_urls[' + idx + ']"]');
            var tierHidden = container.querySelector('[name="new_sponsor_tiers[' + idx + ']"]');
            if (nameHidden) nameHidden.value = name;
            if (urlHidden) urlHidden.value = url;
            if (tierHidden) tierHidden.value = tier;

            if (hasNewFile) {
                var dt = new DataTransfer();
                dt.items.add(fileInput.files[0]);
                var fileHidden = container.querySelector('[name="new_sponsor_logos[' + idx + ']"]');
                if (fileHidden) fileHidden.files = dt.files;
            }

            // Update card display
            var oldSponsorData = JSON.parse(item.dataset.sponsor);
            item.dataset.sponsor = JSON.stringify({name: name, logo: oldSponsorData.logo, url: url || null, tier: tier});

            if (hasNewFile) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    item.dataset.logoUrl = e.target.result;
                    item.innerHTML = buildSponsorCardHTML(name, tierBadge, urlDisplay, e.target.result);
                    resetSponsorEditState();
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                var logoSrc = item.dataset.logoUrl || '';
                item.innerHTML = buildSponsorCardHTML(name, tierBadge, urlDisplay, logoSrc);
                resetSponsorEditState();
            }
        } else {
            // Editing an existing (saved) sponsor
            if (hasNewFile) {
                // Convert to "new" sponsor with hidden file inputs
                var oldSponsorData = JSON.parse(item.dataset.sponsor);
                var idx = sponsorFileCounter++;
                var container = document.getElementById('new-sponsor-inputs-container');

                var dt = new DataTransfer();
                dt.items.add(fileInput.files[0]);

                var fileHidden = document.createElement('input');
                fileHidden.type = 'file';
                fileHidden.name = 'new_sponsor_logos[' + idx + ']';
                fileHidden.style.display = 'none';
                fileHidden.files = dt.files;
                container.appendChild(fileHidden);

                var nameHidden = document.createElement('input');
                nameHidden.type = 'hidden';
                nameHidden.name = 'new_sponsor_names[' + idx + ']';
                nameHidden.value = name;
                container.appendChild(nameHidden);

                var urlHidden = document.createElement('input');
                urlHidden.type = 'hidden';
                urlHidden.name = 'new_sponsor_urls[' + idx + ']';
                urlHidden.value = url;
                container.appendChild(urlHidden);

                var tierHidden = document.createElement('input');
                tierHidden.type = 'hidden';
                tierHidden.name = 'new_sponsor_tiers[' + idx + ']';
                tierHidden.value = tier;
                container.appendChild(tierHidden);

                item.dataset.newIdx = idx;
                item.dataset.sponsor = JSON.stringify({name: name, logo: '', url: url || null, tier: tier});

                var reader = new FileReader();
                reader.onload = function(e) {
                    item.dataset.logoUrl = e.target.result;
                    item.innerHTML = buildSponsorCardHTML(name, tierBadge, urlDisplay, e.target.result);
                    resetSponsorEditState();
                    updateSponsorHiddenInput();
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                // No new logo: just update data-sponsor JSON
                var oldSponsorData = JSON.parse(item.dataset.sponsor);
                item.dataset.sponsor = JSON.stringify({name: name, logo: oldSponsorData.logo, url: url || null, tier: tier});
                var logoSrc = item.dataset.logoUrl || '';
                item.innerHTML = buildSponsorCardHTML(name, tierBadge, urlDisplay, logoSrc);
                resetSponsorEditState();
                updateSponsorHiddenInput();
            }
        }
        return;
    }

    // Adding a new sponsor
    if (!fileInput || !hasNewFile) return;

    var file = fileInput.files[0];

    var count = document.querySelectorAll('#sponsors-list .sponsor-item').length;
    if (count >= 12) return;

    var idx = sponsorFileCounter++;

    // Create hidden file inputs for form submission
    var container = document.getElementById('new-sponsor-inputs-container');
    var dt = new DataTransfer();
    dt.items.add(file);

    var fileHidden = document.createElement('input');
    fileHidden.type = 'file';
    fileHidden.name = 'new_sponsor_logos[' + idx + ']';
    fileHidden.style.display = 'none';
    fileHidden.files = dt.files;
    container.appendChild(fileHidden);

    var nameHidden = document.createElement('input');
    nameHidden.type = 'hidden';
    nameHidden.name = 'new_sponsor_names[' + idx + ']';
    nameHidden.value = name;
    container.appendChild(nameHidden);

    var urlHidden = document.createElement('input');
    urlHidden.type = 'hidden';
    urlHidden.name = 'new_sponsor_urls[' + idx + ']';
    urlHidden.value = url;
    container.appendChild(urlHidden);

    var tierHidden = document.createElement('input');
    tierHidden.type = 'hidden';
    tierHidden.name = 'new_sponsor_tiers[' + idx + ']';
    tierHidden.value = tier;
    container.appendChild(tierHidden);

    // Create preview card
    var reader = new FileReader();
    reader.onload = function(e) {
        var tierBadge = buildTierBadge(tier);
        var urlDisplay = url ? '<div class="text-xs text-gray-500 dark:text-gray-400 truncate">' + escapeHtml(url) + '</div>' : '';

        var div = document.createElement('div');
        div.className = 'sponsor-item flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg';
        div.dataset.sponsor = JSON.stringify({name: name, logo: '', url: url || null, tier: tier});
        div.dataset.newIdx = idx;
        div.dataset.logoUrl = e.target.result;
        div.innerHTML = buildSponsorCardHTML(name, tierBadge, urlDisplay, e.target.result);

        document.getElementById('sponsors-list').appendChild(div);
        updateSponsorLimitVisibility();
    };
    reader.readAsDataURL(file);

    // Reset form inputs
    if (nameInput) nameInput.value = '';
    if (urlInput) urlInput.value = '';
    if (tierInput) tierInput.value = '';
    fileInput.value = '';
}

function removeSponsor(btn) {
    var item = btn.closest('.sponsor-item');
    if (!item) return;

    // If removing the item being edited, reset edit state first
    if (editingSponsorItem === item) {
        resetSponsorEditState();
    }

    // If it's a new (not yet saved) sponsor, remove its hidden file inputs
    if (item.dataset.newIdx !== undefined) {
        var idx = item.dataset.newIdx;
        var container = document.getElementById('new-sponsor-inputs-container');
        container.querySelectorAll('[name="new_sponsor_logos[' + idx + ']"], [name="new_sponsor_names[' + idx + ']"], [name="new_sponsor_urls[' + idx + ']"], [name="new_sponsor_tiers[' + idx + ']"]').forEach(function(el) {
            el.remove();
        });
    }

    item.remove();
    updateSponsorHiddenInput();
}

// ============================================================
// Event delegation and addEventListener bindings
// (replaces all inline event handlers)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // --- Initialize SortableJS for custom fields ---
    var customFieldsList = document.getElementById('event-custom-fields-container');
    if (customFieldsList && typeof Sortable !== 'undefined') {
        Sortable.create(customFieldsList, {
            handle: '.custom-field-drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
        });
    }

    // --- Initialize SortableJS for sponsor logos ---
    var sponsorsList = document.getElementById('sponsors-list');
    if (sponsorsList && typeof Sortable !== 'undefined') {
        Sortable.create(sponsorsList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function() {
                updateSponsorHiddenInput();
            }
        });
    }

    // --- Sponsor logo preview ---
    var sponsorLogoInput = document.getElementById('new_sponsor_logo_input');
    if (sponsorLogoInput) {
        sponsorLogoInput.addEventListener('change', function() {
            previewSponsorLogo(this);
        });
    }

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
            case 'add-sponsor':
                addSponsor();
                break;
            case 'remove-sponsor':
                removeSponsor(btn);
                break;
            case 'edit-sponsor':
                editSponsor(btn);
                break;
            case 'cancel-edit-sponsor':
                cancelEditSponsor();
                break;
            case 'add-custom-label':
                addCustomLabel();
                break;
            case 'remove-custom-label':
                removeCustomLabel(btn);
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

    // --- Videos & Links section ---
    var addLinkModal = document.getElementById('add_link_modal');
    if (addLinkModal) {
        // Link data state (parsed from hidden inputs)
        var linkData = {
            youtube_links: JSON.parse(document.getElementById('youtube_links_data').value || '[]'),
            social_links: JSON.parse(document.getElementById('social_links_data').value || '[]'),
            payment_links: JSON.parse(document.getElementById('payment_links_data').value || '[]')
        };
        var currentLinkType = '';

        // Helper: update hidden input and trigger unsaved changes warning
        function updateLinkInput(linkType) {
            var input = document.getElementById(linkType + '_data');
            input.value = JSON.stringify(linkData[linkType]);
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Helper: get tab content div from link type
        function getTabDiv(linkType) {
            var tabKey = linkType === 'youtube_links' ? 'youtube_videos' : linkType;
            return document.getElementById('links-tab-' + tabKey);
        }

        // Helper: toggle list/empty-state visibility
        function toggleEmptyState(linkType) {
            var tab = getTabDiv(linkType);
            var ul = tab.querySelector('.link-list');
            var empty = tab.querySelector('.link-empty-state');
            var hasItems = linkData[linkType].filter(function(l) { return !!l; }).length > 0;
            ul.style.display = hasItems ? '' : 'none';
            empty.style.display = hasItems ? 'none' : '';
        }

        // Helper: create a generic link icon SVG element
        function createLinkIcon() {
            var div = document.createElement('div');
            div.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>';
            return div.firstChild;
        }

        // Helper: create an <li> for a YouTube link
        function createYoutubeLi(link) {
            var li = document.createElement('li');
            li.className = 'p-4 bg-white dark:bg-gray-800';
            li.setAttribute('data-link-url', link.url);

            var row = document.createElement('div');
            row.className = 'flex items-start gap-4';

            var iconWrap = document.createElement('div');
            iconWrap.className = 'flex-shrink-0 text-gray-500 dark:text-gray-400 pt-1';
            iconWrap.appendChild(createLinkIcon());

            var content = document.createElement('div');
            content.className = 'flex-1 min-w-0';
            var a = document.createElement('a');
            a.href = link.url;
            a.target = '_blank';
            a.className = 'block';
            var h4 = document.createElement('h4');
            h4.className = 'text-sm font-semibold break-words line-clamp-2 text-gray-900 dark:text-gray-100';
            h4.textContent = link.name || link.url;
            a.appendChild(h4);
            if (link.thumbnail_url) {
                var img = document.createElement('img');
                img.src = link.thumbnail_url;
                img.className = 'mt-2 rounded';
                a.appendChild(img);
            }
            content.appendChild(a);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-remove-link flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 text-sm';
            removeBtn.setAttribute('data-link-type', 'youtube_links');
            removeBtn.setAttribute('data-link-url', link.url);
            removeBtn.textContent = @json(__('messages.remove'));

            row.appendChild(iconWrap);
            row.appendChild(content);
            row.appendChild(removeBtn);
            li.appendChild(row);
            return li;
        }

        // Helper: create an <li> for a social/payment link
        function createTextLinkLi(link, linkType) {
            var li = document.createElement('li');
            li.className = 'p-4 bg-white dark:bg-gray-800';
            li.setAttribute('data-link-url', link.url);

            var row = document.createElement('div');
            row.className = 'flex items-center gap-4';

            var iconWrap = document.createElement('div');
            iconWrap.className = 'flex-shrink-0 text-gray-500 dark:text-gray-400';
            iconWrap.appendChild(createLinkIcon());

            var content = document.createElement('div');
            content.className = 'flex-1 min-w-0';
            var a = document.createElement('a');
            a.href = link.url;
            a.target = '_blank';
            a.className = 'block';
            var h4 = document.createElement('h4');
            h4.className = 'text-sm font-semibold text-gray-900 dark:text-gray-100';
            h4.textContent = link.brand || link.url;
            a.appendChild(h4);
            var p = document.createElement('p');
            p.className = 'text-sm text-gray-500 dark:text-gray-400 truncate';
            p.textContent = link.clean_url || link.url;
            a.appendChild(p);
            content.appendChild(a);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-remove-link flex-shrink-0 text-red-600 hover:text-red-800 dark:text-red-400 text-sm';
            removeBtn.setAttribute('data-link-type', linkType);
            removeBtn.setAttribute('data-link-url', link.url);
            removeBtn.textContent = @json(__('messages.remove'));

            row.appendChild(iconWrap);
            row.appendChild(content);
            row.appendChild(removeBtn);
            li.appendChild(row);
            return li;
        }

        // Open modal
        document.querySelectorAll('.btn-show-add-link').forEach(function(btn) {
            btn.addEventListener('click', function() {
                currentLinkType = this.getAttribute('data-link-type');
                document.getElementById('link_type').value = currentLinkType;
                document.getElementById('link').value = '';
                document.getElementById('link-error').style.display = 'none';
                addLinkModal.style.display = '';
                addLinkModal.classList.remove('hidden');
                var linkInput = document.getElementById('link');
                if (linkInput) linkInput.focus();
            });
        });

        // Close modal
        document.querySelectorAll('.btn-hide-add-link').forEach(function(btn) {
            btn.addEventListener('click', function() {
                addLinkModal.style.display = 'none';
                addLinkModal.classList.add('hidden');
            });
        });

        // Save link (fetch preview, append to list)
        var saveLinkBtn = document.getElementById('btn-save-link');
        saveLinkBtn.addEventListener('click', function() {
            var linkInput = document.getElementById('link');
            var linkUrl = linkInput.value.trim();
            var errorEl = document.getElementById('link-error');
            errorEl.style.display = 'none';

            if (!linkUrl) {
                errorEl.textContent = @json(__('messages.field_required'));
                errorEl.style.display = '';
                return;
            }

            // Loading state
            var origText = saveLinkBtn.textContent;
            saveLinkBtn.disabled = true;
            saveLinkBtn.textContent = @json(__('messages.saving'));

            fetch(@json($role->subdomain ? route('role.preview_link', ['subdomain' => $role->subdomain]) : ''), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ url: linkUrl })
            })
            .then(function(r) {
                if (!r.ok) return r.json().then(function(d) { throw d; });
                return r.json();
            })
            .then(function(link) {
                // Add to data array
                linkData[currentLinkType].push({
                    name: link.name,
                    url: link.url,
                    thumbnail_url: link.thumbnail_url
                });

                // Append to DOM
                var tab = getTabDiv(currentLinkType);
                var ul = tab.querySelector('.link-list');
                var li;
                if (currentLinkType === 'youtube_links') {
                    li = createYoutubeLi(link);
                } else {
                    li = createTextLinkLi(link, currentLinkType);
                }
                ul.appendChild(li);

                // Update state
                updateLinkInput(currentLinkType);
                toggleEmptyState(currentLinkType);

                // Close modal
                addLinkModal.style.display = 'none';
                addLinkModal.classList.add('hidden');
                linkInput.value = '';
            })
            .catch(function(err) {
                errorEl.textContent = (err && err.error) ? err.error :
                    ((err && err.errors && err.errors.url) ? err.errors.url[0] : @json(__('messages.error_occurred')));
                errorEl.style.display = '';
            })
            .finally(function() {
                saveLinkBtn.disabled = false;
                saveLinkBtn.textContent = origText;
            });
        });

        // Remove link (event delegation for both Blade-rendered and JS-appended items)
        document.querySelectorAll('.links-tab-content').forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                var btn = e.target.closest('.btn-remove-link');
                if (!btn) return;

                var linkType = btn.getAttribute('data-link-type');
                var linkUrl = btn.getAttribute('data-link-url');

                // Remove from data array
                linkData[linkType] = linkData[linkType].filter(function(l) {
                    return l && l.url !== linkUrl;
                });

                // Remove from DOM
                var li = btn.closest('li');
                if (li) li.remove();

                // Update state
                updateLinkInput(linkType);
                toggleEmptyState(linkType);
            });
        });
    }

});

</script>

@if (config('services.google.gemini_key') && !is_demo_mode() && $role->isEnterprise())
<x-ai-generate-modal
    name="ai-style-generator"
    :title="__('messages.ai_style_generator')"
    :description="__('messages.ai_style_description')"
    :fields="[
        ['key' => 'profile_image', 'label' => __('messages.profile_image'), 'has_value' => (bool)$role->profile_image_url],
        ['key' => 'header_image', 'label' => __('messages.header_image'), 'has_value' => (bool)$role->header_image_url],
        ['key' => 'accent_color', 'label' => __('messages.accent_color'), 'has_value' => strtolower($role->accent_color) !== '#007bff'],
        ['key' => 'font', 'label' => __('messages.font_family'), 'has_value' => $role->font_family !== 'Roboto'],
        ['key' => 'background_image', 'label' => __('messages.background_image'), 'has_value' => (bool)$role->background_image_url],
    ]"
    endpoint="{{ $role->exists ? url('/'.$role->subdomain.'/generate-style') : url('/generate-style') }}"
    imageEndpoint="{{ $role->exists ? url('/'.$role->subdomain.'/generate-style-image') : url('/generate-style-image') }}"
    :imageElements="['profile_image', 'header_image', 'background_image']"
    :promptEndpoint="$role->exists ? url('/'.$role->subdomain.'/get-style-prompt') : url('/get-style-prompt')"
    successCallback="handleAiStyleResults"
    extraDataCallback="getStyleExtraData"
    checkValuesCallback="getStyleCurrentValues"
    savedInstructions="{{ $role->ai_style_instructions }}"
    saveInstructionsField="ai_style_instructions"
    :errorMessage="__('messages.ai_style_generation_failed')"
/>

<script {!! nonce_attr() !!}>
window.getStyleExtraData = function() {
    return {
        accent_color: document.getElementById('accent_color') ? document.getElementById('accent_color').value : '',
        font_family: document.getElementById('font_family') ? document.getElementById('font_family').value : '',
        name: document.getElementById('name').value,
        type: document.querySelector('input[name="type"]') ? document.querySelector('input[name="type"]').value : (document.querySelector('select[name="type"]') ? document.querySelector('select[name="type"]').value : ''),
        short_description: document.getElementById('short_description') ? document.getElementById('short_description').value : '',
    };
};

window.getStyleCurrentValues = function() {
    var values = [];
    var accentColor = document.getElementById('accent_color');
    if (accentColor && accentColor.value.toLowerCase() !== '#007bff') values.push('accent_color');
    var fontFamily = document.getElementById('font_family');
    if (fontFamily && fontFamily.value !== 'Roboto') values.push('font');
    var profileExisting = document.getElementById('profile_image_existing');
    var profileFileInput = document.getElementById('profile_image');
    if (profileExisting || (profileFileInput && profileFileInput.files && profileFileInput.files.length > 0)) values.push('profile_image');
    var headerExisting = document.getElementById('delete_header_image_button');
    var headerFileInput = document.getElementById('header_image_url');
    if ((headerExisting && headerExisting.style.display !== 'none') || (headerFileInput && headerFileInput.files && headerFileInput.files.length > 0)) values.push('header_image');
    var bgExisting = document.getElementById('background_image_existing');
    var bgFileInput = document.getElementById('background_image_url');
    if (bgExisting || (bgFileInput && bgFileInput.files && bgFileInput.files.length > 0)) values.push('background_image');
    return values;
};

window.handleAiStyleResults = function(data) {
    // Apply accent color
    if (data.accent_color) {
        var accentInput = document.getElementById('accent_color');
        if (accentInput) {
            accentInput.value = data.accent_color;
            accentInput.dispatchEvent(new Event('input', { bubbles: true }));
            accentInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    // Apply font
    if (data.font_family) {
        var fontSelect = document.getElementById('font_family');
        if (fontSelect) {
            fontSelect.value = data.font_family;
            fontSelect.dispatchEvent(new Event('change', { bubbles: true }));
            var fontAction = fontSelect.getAttribute('data-action');
            if (fontAction) {
                fontSelect.dispatchEvent(new CustomEvent('action', { detail: fontAction, bubbles: true }));
            }
        }
    }

    // Apply profile image
    if (data.profile_image_url && data.profile_image_filename) {
        var existingAiProfile = document.getElementById('ai_profile_image');
        if (existingAiProfile) existingAiProfile.remove();
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ai_profile_image';
        input.id = 'ai_profile_image';
        input.value = data.profile_image_filename;
        document.getElementById('edit-form').appendChild(input);

        var previewClear = document.getElementById('profile_image_preview_clear');
        var previewImg = document.getElementById('profile_image_preview');
        if (previewImg && previewClear) {
            previewImg.src = data.profile_image_url;
            previewClear.style.display = '';
        }
        var chooseDiv = document.getElementById('profile_image_choose');
        if (chooseDiv) chooseDiv.style.display = 'none';
        var existingDiv = document.getElementById('profile_image_existing');
        if (existingDiv) existingDiv.style.display = 'none';
    }

    // Apply header image
    if (data.header_image_url && data.header_image_filename) {
        var existingAiHeader = document.getElementById('ai_header_image');
        if (existingAiHeader) existingAiHeader.remove();
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ai_header_image';
        input.id = 'ai_header_image';
        input.value = data.header_image_filename;
        document.getElementById('edit-form').appendChild(input);

        var headerPreviewClear = document.getElementById('header_image_url_preview_clear');
        var headerPreviewImg = document.getElementById('header_image_url_preview');
        if (headerPreviewImg && headerPreviewClear) {
            headerPreviewImg.src = data.header_image_url;
            headerPreviewClear.style.display = '';
        }
        var headerSelect = document.getElementById('header_image');
        if (headerSelect) {
            headerSelect.value = '';
            headerSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        var customHeaderInput = document.getElementById('custom_header_input');
        if (customHeaderInput) customHeaderInput.style.display = '';
    }

    // Apply background image
    if (data.background_image_url && data.background_image_filename) {
        var existingAiBg = document.getElementById('ai_background_image');
        if (existingAiBg) existingAiBg.remove();
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ai_background_image';
        input.id = 'ai_background_image';
        input.value = data.background_image_filename;
        document.getElementById('edit-form').appendChild(input);

        var bgPreviewClear = document.getElementById('background_image_preview_clear');
        var bgPreviewImg = document.getElementById('background_image_preview');
        if (bgPreviewImg && bgPreviewClear) {
            bgPreviewImg.src = data.background_image_url;
            bgPreviewClear.style.display = '';
        }
        var bgRadio = document.getElementById('background_type_image');
        if (bgRadio) {
            bgRadio.checked = true;
            bgRadio.dispatchEvent(new Event('change', { bubbles: true }));
        }
        var bgSelect = document.getElementById('background_image');
        if (bgSelect) {
            bgSelect.value = '';
            bgSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        var customImageInput = document.getElementById('custom_image_input');
        if (customImageInput) customImageInput.style.display = '';
    }

    // Switch to branding tab
    var brandingTab = document.querySelector('[data-style-tab="branding"]');
    if (brandingTab) brandingTab.click();
};
</script>
@endif

<x-upgrade-modal name="upgrade-ai-style" tier="enterprise" :subdomain="$role->subdomain" learnMoreUrl="{{ route('marketing.ai') }}">
    {{ __('messages.upgrade_feature_description_ai_style') }}
</x-upgrade-modal>

@if (config('services.google.gemini_key') && !is_demo_mode() && $role->isEnterprise())
<x-ai-generate-modal
    name="ai-schedule-details"
    :title="__('messages.ai_details_generator')"
    :description="__('messages.ai_details_description')"
    :fields="[
        ['key' => 'short_description', 'label' => __('messages.short_description'), 'has_value' => (bool)$role->short_description],
        ['key' => 'description', 'label' => __('messages.description'), 'has_value' => (bool)$role->description],
    ]"
    endpoint="{{ $role->exists ? url('/'.$role->subdomain.'/generate-schedule-details') : url('/generate-schedule-details') }}"
    :promptEndpoint="$role->exists ? url('/'.$role->subdomain.'/get-schedule-details-prompt') : url('/get-schedule-details-prompt')"
    successCallback="handleAiScheduleDetailsResults"
    extraDataCallback="getScheduleDetailsExtraData"
    checkValuesCallback="getScheduleDetailsCurrentValues"
    :showInstructions="true"
    :instructionsLabel="__('messages.ai_additional_instructions')"
    :instructionsPlaceholder="__('messages.ai_additional_instructions_placeholder')"
    savedInstructions="{{ $role->ai_content_instructions }}"
    saveInstructionsField="ai_content_instructions"
    :errorMessage="__('messages.ai_details_generation_failed')"
/>

<script {!! nonce_attr() !!}>
window.getScheduleDetailsExtraData = function() {
    var descTextarea = document.getElementById('description');
    var descValue = descTextarea && descTextarea._easyMDE ? descTextarea._easyMDE.value() : (descTextarea ? descTextarea.value : '');
    return {
        name: document.getElementById('name').value,
        short_description: document.getElementById('short_description').value,
        type: document.querySelector('input[name="type"]') ? document.querySelector('input[name="type"]').value : (document.querySelector('select[name="type"]') ? document.querySelector('select[name="type"]').value : ''),
        description: descValue
    };
};

window.getScheduleDetailsCurrentValues = function() {
    var values = [];
    var shortDesc = document.getElementById('short_description');
    if (shortDesc && shortDesc.value.trim()) values.push('short_description');
    var descTextarea = document.getElementById('description');
    var descValue = descTextarea && descTextarea._easyMDE ? descTextarea._easyMDE.value() : (descTextarea ? descTextarea.value : '');
    if (descValue.trim()) values.push('description');
    return values;
};

window.handleAiScheduleDetailsResults = function(data) {
    if (data.short_description) {
        var el = document.getElementById('short_description');
        el.value = data.short_description;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (data.description) {
        var textarea = document.getElementById('description');
        if (textarea._easyMDE) {
            textarea._easyMDE.value(data.description);
        } else {
            textarea.value = data.description;
        }
    }
};
</script>
@endif

<x-upgrade-modal name="upgrade-ai-details" tier="enterprise" :subdomain="$role->subdomain" learnMoreUrl="{{ route('marketing.ai') }}">
    {{ __('messages.upgrade_feature_description_ai_details') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-custom-css" tier="pro" :subdomain="$role->subdomain" learnMoreUrl="{{ route('marketing.custom_css') }}">
    {{ __('messages.upgrade_feature_description_custom_css') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-custom-fields" tier="pro" :subdomain="$role->subdomain" learnMoreUrl="{{ route('marketing.custom_fields') }}">
    {{ __('messages.upgrade_feature_description_custom_fields') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-custom-domain" tier="enterprise" :subdomain="$role->subdomain" learnMoreUrl="{{ route('marketing.custom_domain') }}">
    {{ __('messages.upgrade_feature_description_custom_domain') }}
</x-upgrade-modal>