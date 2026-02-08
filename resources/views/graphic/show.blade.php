<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            let graphicData = null;
            let currentSettings = @json($graphicSettings ?? []);
            const isPro = {{ $isPro ? 'true' : 'false' }};
            const isEnterprise = {{ $isEnterprise ? 'true' : 'false' }};
            const currentUserEmail = '{{ auth()->user()->email }}';
            const hasRecurringEvents = @json($hasRecurringEvents);
            const maxEvents = {{ $maxEvents ?? 20 }};
            let currentLayout = '{{ $layout }}';
            const directRegistration = {{ $role->direct_registration ? 'true' : 'false' }};

            function copyToClipboard(text, buttonId) {
                navigator.clipboard.writeText(text).then(function() {
                    const button = document.getElementById(buttonId);
                    const originalText = button.innerHTML;
                    button.innerHTML = `<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>{{ __("messages.copied") }}`;
                    button.classList.add('bg-green-500', 'hover:bg-green-600');
                    button.classList.remove('bg-[#4E81FA]', 'hover:bg-[#3D6FE8]');

                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-500', 'hover:bg-green-600');
                        button.classList.add('bg-[#4E81FA]', 'hover:bg-[#3D6FE8]');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Could not copy text: ', err);
                    alert('{{ __("messages.copy_failed") }}');
                });
            }

            function downloadImage() {
                if (!graphicData) return;

                const img = document.getElementById('graphicImage');
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                ctx.drawImage(img, 0, 0);

                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = '{{ $role->subdomain }}-upcoming-events.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }, 'image/png');
            }

            function getFormSettings() {
                // Check desktop first, fall back to mobile
                const aiPrompt = document.getElementById('ai_prompt')?.value ||
                                 document.getElementById('ai_prompt_mobile')?.value || '';
                const textTemplate = document.getElementById('text_template')?.value ||
                                     document.getElementById('text_template_mobile')?.value || '';
                const useScreenCapture = document.getElementById('use_screen_capture')?.checked ||
                                         document.getElementById('use_screen_capture_mobile')?.checked || false;
                const excludeRecurring = document.getElementById('exclude_recurring')?.checked ||
                                         document.getElementById('exclude_recurring_mobile')?.checked || false;
                const layout = document.querySelector('input[name="layout"]:checked')?.value ||
                               document.querySelector('input[name="layout_mobile"]:checked')?.value || 'grid';
                const datePosition = document.getElementById('date_position')?.value ||
                                     document.getElementById('date_position_mobile')?.value || '';
                const eventCount = document.getElementById('event_count')?.value ||
                                   document.getElementById('event_count_mobile')?.value || '';
                const maxPerRow = document.getElementById('max_per_row')?.value ||
                                  document.getElementById('max_per_row_mobile')?.value || '';
                const overlayText = document.getElementById('overlay_text')?.value ||
                                    document.getElementById('overlay_text_mobile')?.value || '';
                const urlIncludeHttps = document.getElementById('url_include_https')?.checked ||
                                        document.getElementById('url_include_https_mobile')?.checked || false;
                const urlIncludeId = document.getElementById('url_include_id')?.checked ||
                                     document.getElementById('url_include_id_mobile')?.checked || false;

                return {
                    direct_registration: directRegistration,
                    ai_prompt: aiPrompt,
                    text_template: textTemplate,
                    use_screen_capture: useScreenCapture,
                    exclude_recurring: excludeRecurring,
                    layout: layout,
                    date_position: datePosition,
                    event_count: eventCount,
                    max_per_row: maxPerRow,
                    overlay_text: overlayText,
                    url_include_https: urlIncludeHttps,
                    url_include_id: urlIncludeId
                };
            }

            // Sync settings between desktop and mobile forms
            function syncFormFields() {
                // Sync AI prompt
                const aiPromptDesktop = document.getElementById('ai_prompt');
                const aiPromptMobile = document.getElementById('ai_prompt_mobile');
                if (aiPromptDesktop && aiPromptMobile) {
                    aiPromptMobile.value = aiPromptDesktop.value;
                }

                // Sync text template
                const textTemplateDesktop = document.getElementById('text_template');
                const textTemplateMobile = document.getElementById('text_template_mobile');
                if (textTemplateDesktop && textTemplateMobile) {
                    textTemplateMobile.value = textTemplateDesktop.value;
                }

                // Sync screen capture
                const screenCaptureDesktop = document.getElementById('use_screen_capture');
                const screenCaptureMobile = document.getElementById('use_screen_capture_mobile');
                if (screenCaptureDesktop && screenCaptureMobile) {
                    screenCaptureMobile.checked = screenCaptureDesktop.checked;
                }

                // Sync exclude recurring
                const excludeRecurringDesktop = document.getElementById('exclude_recurring');
                const excludeRecurringMobile = document.getElementById('exclude_recurring_mobile');
                if (excludeRecurringDesktop && excludeRecurringMobile) {
                    excludeRecurringMobile.checked = excludeRecurringDesktop.checked;
                }

                // Sync email enabled
                const emailEnabledDesktop = document.getElementById('email_enabled');
                const emailEnabledMobile = document.getElementById('email_enabled_mobile');
                if (emailEnabledDesktop && emailEnabledMobile) {
                    emailEnabledMobile.checked = emailEnabledDesktop.checked;
                }

                // Sync frequency
                const frequencyDesktop = document.getElementById('frequency');
                const frequencyMobile = document.getElementById('frequency_mobile');
                if (frequencyDesktop && frequencyMobile) {
                    frequencyMobile.value = frequencyDesktop.value;
                }

                // Sync weekly day
                const weeklyDayDesktop = document.getElementById('weekly_day');
                const weeklyDayMobile = document.getElementById('weekly_day_mobile');
                if (weeklyDayDesktop && weeklyDayMobile) {
                    weeklyDayMobile.value = weeklyDayDesktop.value;
                }

                // Sync monthly day
                const monthlyDayDesktop = document.getElementById('monthly_day');
                const monthlyDayMobile = document.getElementById('monthly_day_mobile');
                if (monthlyDayDesktop && monthlyDayMobile) {
                    monthlyDayMobile.value = monthlyDayDesktop.value;
                }

                // Sync send hour
                const sendHourDesktop = document.getElementById('send_hour');
                const sendHourMobile = document.getElementById('send_hour_mobile');
                if (sendHourDesktop && sendHourMobile) {
                    sendHourMobile.value = sendHourDesktop.value;
                }

                // Sync recipient emails
                const recipientEmailsDesktop = document.getElementById('recipient_emails');
                const recipientEmailsMobile = document.getElementById('recipient_emails_mobile');
                if (recipientEmailsDesktop && recipientEmailsMobile) {
                    recipientEmailsMobile.value = recipientEmailsDesktop.value;
                }

                // Sync layout
                const layoutDesktop = document.querySelector('input[name="layout"]:checked');
                const layoutMobile = document.querySelector('input[name="layout_mobile"]:checked');
                if (layoutDesktop && layoutMobile) {
                    if (layoutDesktop.value !== layoutMobile.value) {
                        const mobileRadio = document.querySelector(`input[name="layout_mobile"][value="${layoutDesktop.value}"]`);
                        if (mobileRadio) mobileRadio.checked = true;
                    }
                }

                // Sync date position
                const datePositionDesktop = document.getElementById('date_position');
                const datePositionMobile = document.getElementById('date_position_mobile');
                if (datePositionDesktop && datePositionMobile) {
                    datePositionMobile.value = datePositionDesktop.value;
                }

                // Sync event count
                const eventCountDesktop = document.getElementById('event_count');
                const eventCountMobile = document.getElementById('event_count_mobile');
                if (eventCountDesktop && eventCountMobile) {
                    eventCountMobile.value = eventCountDesktop.value;
                }

                // Sync max per row
                const maxPerRowDesktop = document.getElementById('max_per_row');
                const maxPerRowMobile = document.getElementById('max_per_row_mobile');
                if (maxPerRowDesktop && maxPerRowMobile) {
                    maxPerRowMobile.value = maxPerRowDesktop.value;
                }

                // Sync overlay text
                const overlayTextDesktop = document.getElementById('overlay_text');
                const overlayTextMobile = document.getElementById('overlay_text_mobile');
                if (overlayTextDesktop && overlayTextMobile) {
                    overlayTextMobile.value = overlayTextDesktop.value;
                }

                // Sync URL include HTTPS
                const urlIncludeHttpsDesktop = document.getElementById('url_include_https');
                const urlIncludeHttpsMobile = document.getElementById('url_include_https_mobile');
                if (urlIncludeHttpsDesktop && urlIncludeHttpsMobile) {
                    urlIncludeHttpsMobile.checked = urlIncludeHttpsDesktop.checked;
                }

                // Sync URL include ID
                const urlIncludeIdDesktop = document.getElementById('url_include_id');
                const urlIncludeIdMobile = document.getElementById('url_include_id_mobile');
                if (urlIncludeIdDesktop && urlIncludeIdMobile) {
                    urlIncludeIdMobile.checked = urlIncludeIdDesktop.checked;
                }
            }

            function loadGraphic() {
                const errorDiv = document.getElementById('errorMessage');

                // Get individual section elements
                const textSpinner = document.getElementById('textLoadingSpinner');
                const textContent = document.getElementById('eventText');
                const graphicSpinner = document.getElementById('graphicLoadingSpinner');
                const graphicImage = document.getElementById('graphicImage');

                // Show spinners, hide content
                textSpinner.classList.remove('hidden');
                textContent.classList.add('hidden');
                graphicSpinner.classList.remove('hidden');
                graphicImage.classList.add('hidden');
                errorDiv.classList.add('hidden');

                // Get current form values instead of saved settings
                const formSettings = getFormSettings();
                const layoutParam = '?layout=' + encodeURIComponent(formSettings.layout);
                const directParam = formSettings.direct_registration ? '&direct=1' : '';
                const screenCaptureParam = formSettings.use_screen_capture ? '&use_screen_capture=1' : '';
                const aiPromptParam = formSettings.ai_prompt ? '&ai_prompt=' + encodeURIComponent(formSettings.ai_prompt) : '';
                const textTemplateParam = formSettings.text_template ? '&text_template=' + encodeURIComponent(formSettings.text_template) : '';
                const excludeRecurringParam = formSettings.exclude_recurring ? '&exclude_recurring=1' : '';
                const datePositionParam = formSettings.date_position ? '&date_position=' + encodeURIComponent(formSettings.date_position) : '';
                const eventCountParam = formSettings.event_count ? '&event_count=' + encodeURIComponent(formSettings.event_count) : '';
                const maxPerRowParam = formSettings.max_per_row ? '&max_per_row=' + encodeURIComponent(formSettings.max_per_row) : '';
                const overlayTextParam = formSettings.overlay_text ? '&overlay_text=' + encodeURIComponent(formSettings.overlay_text) : '';
                const urlIncludeHttpsParam = formSettings.url_include_https ? '&url_include_https=1' : '';
                const urlIncludeIdParam = formSettings.url_include_id ? '&url_include_id=1' : '';

                fetch('{{ route("event.generate_graphic_data", ["subdomain" => $role->subdomain]) }}' + layoutParam + directParam + screenCaptureParam + aiPromptParam + textTemplateParam + excludeRecurringParam + datePositionParam + eventCountParam + maxPerRowParam + overlayTextParam + urlIncludeHttpsParam + urlIncludeIdParam)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        graphicData = data;

                        // Hide spinners, show content
                        textSpinner.classList.add('hidden');
                        textContent.classList.remove('hidden');
                        graphicSpinner.classList.add('hidden');
                        graphicImage.classList.remove('hidden');

                        graphicImage.src = 'data:image/png;base64,' + data.image;
                        textContent.value = data.text;
                    })
                    .catch(error => {
                        console.warn('Error loading graphic:', error);

                        // Hide spinners on error too
                        textSpinner.classList.add('hidden');
                        textContent.classList.remove('hidden');
                        graphicSpinner.classList.add('hidden');
                        graphicImage.classList.remove('hidden');

                        const errorTextDiv = document.getElementById('errorMessageText');
                        if (errorTextDiv) {
                            errorTextDiv.textContent = error.message || '{{ __("messages.error_loading_graphic") }}';
                        }
                        errorDiv.classList.remove('hidden');
                    });
            }

            function updateSettingsUI() {
                // Update email scheduling fields (desktop and mobile)
                ['email_enabled', 'email_enabled_mobile'].forEach(id => {
                    const enabledCheckbox = document.getElementById(id);
                    if (enabledCheckbox) {
                        enabledCheckbox.checked = currentSettings.enabled || false;
                    }
                });
                toggleEmailOptions();

                ['frequency', 'frequency_mobile'].forEach(id => {
                    const frequency = document.getElementById(id);
                    if (frequency) {
                        frequency.value = currentSettings.frequency || 'weekly';
                    }
                });
                updateDaySelector();

                // Set send_day value for both weekly and monthly selectors
                const sendDayValue = currentSettings.send_day || 1;
                ['weekly_day', 'weekly_day_mobile'].forEach(id => {
                    const weeklyDay = document.getElementById(id);
                    if (weeklyDay) {
                        weeklyDay.value = sendDayValue;
                    }
                });
                ['monthly_day', 'monthly_day_mobile'].forEach(id => {
                    const monthlyDay = document.getElementById(id);
                    if (monthlyDay) {
                        monthlyDay.value = sendDayValue;
                    }
                });

                ['send_hour', 'send_hour_mobile'].forEach(id => {
                    const sendHour = document.getElementById(id);
                    if (sendHour) {
                        sendHour.value = currentSettings.send_hour || 9;
                    }
                });

                ['ai_prompt', 'ai_prompt_mobile'].forEach(id => {
                    const aiPrompt = document.getElementById(id);
                    if (aiPrompt) {
                        aiPrompt.value = currentSettings.ai_prompt || '';
                    }
                });

                ['text_template', 'text_template_mobile'].forEach(id => {
                    const textTemplate = document.getElementById(id);
                    if (textTemplate) {
                        const savedValue = currentSettings.text_template || '';
                        // If empty, use default template for better UX
                        const defaultTemplate = `*{day_name}* {date_dmy} | {time}
*{event_name}*:
{short_description}
{venue} | {city}
{url}`;
                        textTemplate.value = savedValue || defaultTemplate;
                    }
                });

                ['use_screen_capture', 'use_screen_capture_mobile'].forEach(id => {
                    const useScreenCapture = document.getElementById(id);
                    if (useScreenCapture) {
                        useScreenCapture.checked = currentSettings.use_screen_capture || false;
                    }
                });

                ['exclude_recurring', 'exclude_recurring_mobile'].forEach(id => {
                    const excludeRecurring = document.getElementById(id);
                    if (excludeRecurring) {
                        excludeRecurring.checked = currentSettings.exclude_recurring || false;
                    }
                });

                ['recipient_emails', 'recipient_emails_mobile'].forEach(id => {
                    const recipientEmails = document.getElementById(id);
                    if (recipientEmails) {
                        recipientEmails.value = currentSettings.recipient_emails || '';
                    }
                });

                // Update layout radio buttons (desktop and mobile)
                const layout = currentSettings.layout || 'grid';
                ['layout', 'layout_mobile'].forEach(name => {
                    const radioToCheck = document.querySelector(`input[name="${name}"][value="${layout}"]`);
                    if (radioToCheck) {
                        radioToCheck.checked = true;
                    }
                });
                currentLayout = layout;
                toggleDatePositionVisibility();
                toggleMaxPerRowVisibility();

                // Update date position selects
                ['date_position', 'date_position_mobile'].forEach(id => {
                    const datePosition = document.getElementById(id);
                    if (datePosition) {
                        datePosition.value = currentSettings.date_position || '';
                    }
                });

                // Update event count selects
                ['event_count', 'event_count_mobile'].forEach(id => {
                    const eventCount = document.getElementById(id);
                    if (eventCount) {
                        eventCount.value = currentSettings.event_count || '';
                    }
                });

                // Update max per row selects
                ['max_per_row', 'max_per_row_mobile'].forEach(id => {
                    const maxPerRow = document.getElementById(id);
                    if (maxPerRow) {
                        maxPerRow.value = currentSettings.max_per_row || '';
                    }
                });
                toggleMaxPerRowVisibility();

                // Update overlay text inputs
                ['overlay_text', 'overlay_text_mobile'].forEach(id => {
                    const overlayText = document.getElementById(id);
                    if (overlayText) {
                        overlayText.value = currentSettings.overlay_text || '';
                    }
                });
                toggleOverlayTextVisibility();

                // Update URL include HTTPS checkboxes
                ['url_include_https', 'url_include_https_mobile'].forEach(id => {
                    const urlIncludeHttps = document.getElementById(id);
                    if (urlIncludeHttps) {
                        urlIncludeHttps.checked = currentSettings.url_include_https || false;
                    }
                });

                // Update URL include ID checkboxes
                ['url_include_id', 'url_include_id_mobile'].forEach(id => {
                    const urlIncludeId = document.getElementById(id);
                    if (urlIncludeId) {
                        urlIncludeId.checked = currentSettings.url_include_id || false;
                    }
                });
            }

            function toggleDatePositionVisibility() {
                const layout = document.querySelector('input[name="layout"]:checked')?.value ||
                               document.querySelector('input[name="layout_mobile"]:checked')?.value || 'grid';
                currentLayout = layout;

                // Show date position for grid and row layouts
                ['date_position_container', 'date_position_container_mobile'].forEach(id => {
                    const container = document.getElementById(id);
                    if (container) {
                        if (layout === 'grid' || layout === 'row') {
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    }
                });
            }

            function toggleMaxPerRowVisibility() {
                const layout = document.querySelector('input[name="layout"]:checked')?.value ||
                               document.querySelector('input[name="layout_mobile"]:checked')?.value || 'grid';

                // Show max per row only for row layout
                ['max_per_row_container', 'max_per_row_container_mobile'].forEach(id => {
                    const container = document.getElementById(id);
                    if (container) {
                        if (layout === 'row') {
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    }
                });
            }

            function toggleOverlayTextVisibility() {
                // Show overlay text input when date position is overlay or above
                ['', '_mobile'].forEach(suffix => {
                    const datePosition = document.getElementById('date_position' + suffix);
                    const overlayTextContainer = document.getElementById('overlay_text_container' + suffix);
                    if (datePosition && overlayTextContainer) {
                        if (datePosition.value === 'overlay' || datePosition.value === 'above') {
                            overlayTextContainer.classList.remove('hidden');
                        } else {
                            overlayTextContainer.classList.add('hidden');
                        }
                    }
                });
            }

            function toggleScreenCapture() {
                const useScreenCapture = document.getElementById('use_screen_capture')?.checked ||
                                         document.getElementById('use_screen_capture_mobile')?.checked || false;

                // Elements to hide when screen capture is enabled
                const containerIds = [
                    'layout_type_container', 'layout_type_container_mobile',
                    'date_position_container', 'date_position_container_mobile',
                    'max_per_row_container', 'max_per_row_container_mobile'
                ];

                containerIds.forEach(id => {
                    const container = document.getElementById(id);
                    if (container) {
                        if (useScreenCapture) {
                            container.classList.add('hidden');
                        } else {
                            container.classList.remove('hidden');
                        }
                    }
                });

                // Re-apply layout visibility rules when turning off screen capture
                if (!useScreenCapture) {
                    toggleDatePositionVisibility();
                    toggleMaxPerRowVisibility();
                }
            }

            function updateDaySelector() {
                // Update both desktop and mobile day selectors
                [
                    { freq: 'frequency', weekly: 'weekly_day_container', monthly: 'monthly_day_container' },
                    { freq: 'frequency_mobile', weekly: 'weekly_day_container_mobile', monthly: 'monthly_day_container_mobile' }
                ].forEach(ids => {
                    const frequencyEl = document.getElementById(ids.freq);
                    const weeklyDayContainer = document.getElementById(ids.weekly);
                    const monthlyDayContainer = document.getElementById(ids.monthly);

                    if (!frequencyEl || !weeklyDayContainer || !monthlyDayContainer) return;

                    const frequency = frequencyEl.value;

                    if (frequency === 'weekly') {
                        weeklyDayContainer.classList.remove('hidden');
                        monthlyDayContainer.classList.add('hidden');
                    } else if (frequency === 'monthly') {
                        weeklyDayContainer.classList.add('hidden');
                        monthlyDayContainer.classList.remove('hidden');
                    } else {
                        weeklyDayContainer.classList.add('hidden');
                        monthlyDayContainer.classList.add('hidden');
                    }
                });
            }

            function toggleEmailOptions() {
                // Handle both desktop and mobile email options
                [
                    { checkbox: 'email_enabled', container: 'email_options_container', emails: 'recipient_emails' },
                    { checkbox: 'email_enabled_mobile', container: 'email_options_container_mobile', emails: 'recipient_emails_mobile' }
                ].forEach(ids => {
                    const enabledCheckbox = document.getElementById(ids.checkbox);
                    const optionsContainer = document.getElementById(ids.container);
                    const recipientEmails = document.getElementById(ids.emails);

                    if (!enabledCheckbox || !optionsContainer) return;

                    if (enabledCheckbox.checked) {
                        optionsContainer.classList.remove('hidden');
                        if (recipientEmails) {
                            recipientEmails.required = true;
                            if (!recipientEmails.value.trim()) {
                                recipientEmails.value = currentUserEmail;
                            }
                        }
                    } else {
                        optionsContainer.classList.add('hidden');
                        if (recipientEmails) {
                            recipientEmails.required = false;
                        }
                    }
                });
            }

            function gatherSettings() {
                // Get values from desktop or mobile (whichever has value)
                const frequencyEl = document.getElementById('frequency') || document.getElementById('frequency_mobile');
                const frequency = frequencyEl ? frequencyEl.value : 'weekly';
                let sendDay = 1;
                if (frequency === 'weekly') {
                    const weeklyDayEl = document.getElementById('weekly_day') || document.getElementById('weekly_day_mobile');
                    sendDay = weeklyDayEl ? weeklyDayEl.value : 1;
                } else if (frequency === 'monthly') {
                    const monthlyDayEl = document.getElementById('monthly_day') || document.getElementById('monthly_day_mobile');
                    sendDay = monthlyDayEl ? monthlyDayEl.value : 1;
                }

                const emailEnabled = document.getElementById('email_enabled') || document.getElementById('email_enabled_mobile');
                const sendHour = document.getElementById('send_hour') || document.getElementById('send_hour_mobile');
                const aiPrompt = document.getElementById('ai_prompt') || document.getElementById('ai_prompt_mobile');
                const textTemplate = document.getElementById('text_template') || document.getElementById('text_template_mobile');
                const useScreenCapture = document.getElementById('use_screen_capture') || document.getElementById('use_screen_capture_mobile');
                const excludeRecurring = document.getElementById('exclude_recurring') || document.getElementById('exclude_recurring_mobile');
                const recipientEmails = document.getElementById('recipient_emails') || document.getElementById('recipient_emails_mobile');

                // Get layout from desktop or mobile
                const layoutChecked = document.querySelector('input[name="layout"]:checked') ||
                                      document.querySelector('input[name="layout_mobile"]:checked');

                // Get date position
                const datePosition = document.getElementById('date_position') || document.getElementById('date_position_mobile');

                // Get event count
                const eventCount = document.getElementById('event_count') || document.getElementById('event_count_mobile');

                // Get max per row
                const maxPerRow = document.getElementById('max_per_row') || document.getElementById('max_per_row_mobile');

                // Get overlay text
                const overlayText = document.getElementById('overlay_text') || document.getElementById('overlay_text_mobile');

                // Get URL formatting options
                const urlIncludeHttps = document.getElementById('url_include_https') || document.getElementById('url_include_https_mobile');
                const urlIncludeId = document.getElementById('url_include_id') || document.getElementById('url_include_id_mobile');

                return {
                    enabled: emailEnabled ? emailEnabled.checked : false,
                    frequency: frequency,
                    send_day: parseInt(sendDay),
                    send_hour: sendHour ? parseInt(sendHour.value) : 9,
                    ai_prompt: aiPrompt ? aiPrompt.value : '',
                    text_template: textTemplate ? textTemplate.value : '',
                    layout: layoutChecked ? layoutChecked.value : 'grid',
                    use_screen_capture: useScreenCapture ? useScreenCapture.checked : false,
                    exclude_recurring: excludeRecurring ? excludeRecurring.checked : false,
                    recipient_emails: recipientEmails ? recipientEmails.value : '',
                    date_position: datePosition ? datePosition.value || null : null,
                    event_count: eventCount && eventCount.value ? parseInt(eventCount.value) : null,
                    max_per_row: maxPerRow && maxPerRow.value ? parseInt(maxPerRow.value) : null,
                    overlay_text: overlayText ? overlayText.value : '',
                    url_include_https: urlIncludeHttps ? urlIncludeHttps.checked : false,
                    url_include_id: urlIncludeId ? urlIncludeId.checked : false
                };
            }

            function saveSettingsToServer(settings) {
                return fetch('{{ route("event.save_graphic_settings", ["subdomain" => $role->subdomain]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(settings)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentSettings = data.settings;
                        return data;
                    } else {
                        throw new Error(data.message || '{{ __("messages.error") }}');
                    }
                });
            }

            function saveSettings() {
                // Handle both desktop and mobile save buttons
                const saveBtn = document.getElementById('saveSettingsBtn');
                const saveBtnMobile = document.getElementById('saveSettingsBtnMobile');
                const originalText = saveBtn ? saveBtn.innerHTML : '';
                const originalTextMobile = saveBtnMobile ? saveBtnMobile.innerHTML : '';

                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '{{ __("messages.saving") }}';
                }
                if (saveBtnMobile) {
                    saveBtnMobile.disabled = true;
                    saveBtnMobile.innerHTML = '{{ __("messages.saving") }}';
                }

                const settings = gatherSettings();

                const emailEnabled = document.getElementById('email_enabled') || document.getElementById('email_enabled_mobile');
                const recipientEmails = document.getElementById('recipient_emails') || document.getElementById('recipient_emails_mobile');

                // Validate recipient emails is required when email scheduling is enabled
                if (emailEnabled && emailEnabled.checked && recipientEmails && !recipientEmails.value.trim()) {
                    showNotification('{{ __("messages.recipient_emails_required") }}', 'error');
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                    }
                    if (saveBtnMobile) {
                        saveBtnMobile.disabled = false;
                        saveBtnMobile.innerHTML = originalTextMobile;
                    }
                    recipientEmails.focus();
                    return;
                }

                saveSettingsToServer(settings)
                .then(() => {
                    showNotification('{{ __("messages.settings_saved") }}', 'success');
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    showNotification(error.message || '{{ __("messages.error") }}', 'error');
                })
                .finally(() => {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                    }
                    if (saveBtnMobile) {
                        saveBtnMobile.disabled = false;
                        saveBtnMobile.innerHTML = originalTextMobile;
                    }
                });
            }

            function sendTestEmail() {
                // Handle both desktop and mobile test buttons
                const testBtn = document.getElementById('testEmailBtn');
                const testBtnMobile = document.getElementById('testEmailBtnMobile');
                const originalText = testBtn ? testBtn.innerHTML : '';
                const originalTextMobile = testBtnMobile ? testBtnMobile.innerHTML : '';

                if (testBtn) {
                    testBtn.disabled = true;
                    testBtn.innerHTML = '{{ __("messages.sending") }}...';
                }
                if (testBtnMobile) {
                    testBtnMobile.disabled = true;
                    testBtnMobile.innerHTML = '{{ __("messages.sending") }}...';
                }

                const settings = gatherSettings();

                if (!settings.recipient_emails.trim()) {
                    showNotification('{{ __("messages.recipient_emails_required") }}', 'error');
                    if (testBtn) {
                        testBtn.disabled = false;
                        testBtn.innerHTML = originalText;
                    }
                    if (testBtnMobile) {
                        testBtnMobile.disabled = false;
                        testBtnMobile.innerHTML = originalTextMobile;
                    }
                    const recipientEmails = document.getElementById('recipient_emails') || document.getElementById('recipient_emails_mobile');
                    if (recipientEmails) recipientEmails.focus();
                    return;
                }

                // Save settings first, then send test email
                saveSettingsToServer(settings)
                .then(() => {
                    return fetch('{{ route("event.graphic_test_email", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('{{ __("messages.test_email_sent") }}', 'success');
                    } else {
                        showNotification(data.message || '{{ __("messages.error") }}', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error sending test email:', error);
                    showNotification(error.message || '{{ __("messages.error") }}', 'error');
                })
                .finally(() => {
                    if (testBtn) {
                        testBtn.disabled = false;
                        testBtn.innerHTML = originalText;
                    }
                    if (testBtnMobile) {
                        testBtnMobile.disabled = false;
                        testBtnMobile.innerHTML = originalTextMobile;
                    }
                });
            }

            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                notification.textContent = message;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }

            // Textarea height persistence functions
            const minTextareaHeight = 100; // Minimum height in pixels (roughly 5 rows)

            function saveTextareaHeight(id, height) {
                if (height >= minTextareaHeight) {
                    localStorage.setItem('textarea_height_' + id, height);
                }
            }

            function restoreTextareaHeights() {
                const textareaIds = ['text_template', 'text_template_mobile', 'ai_prompt', 'ai_prompt_mobile'];
                textareaIds.forEach(id => {
                    const textarea = document.getElementById(id);
                    const savedHeight = localStorage.getItem('textarea_height_' + id);
                    if (textarea && savedHeight && parseInt(savedHeight) >= minTextareaHeight) {
                        textarea.style.height = savedHeight + 'px';
                    }
                });
            }

            function initTextareaResize() {
                const textareaIds = ['text_template', 'text_template_mobile', 'ai_prompt', 'ai_prompt_mobile'];
                const resizeObserver = new ResizeObserver(entries => {
                    entries.forEach(entry => {
                        const id = entry.target.id;
                        const height = entry.target.offsetHeight;
                        if (height > 0) {
                            saveTextareaHeight(id, height);
                        }
                    });
                });

                textareaIds.forEach(id => {
                    const textarea = document.getElementById(id);
                    if (textarea) {
                        resizeObserver.observe(textarea);
                    }
                });

                restoreTextareaHeights();
            }

            function uploadGraphicHeaderImage(file) {
                const formData = new FormData();
                formData.append('header_image', file);

                // Show loading state
                ['header_image_preview', 'header_image_preview_mobile'].forEach(id => {
                    const container = document.getElementById(id);
                    if (container) {
                        container.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400">{{ __("messages.uploading") }}...</div>';
                        container.classList.remove('hidden');
                    }
                });

                fetch('{{ route("event.graphic_upload_header_image", ["subdomain" => $role->subdomain]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update preview for both desktop and mobile
                        ['header_image_preview', 'header_image_preview_mobile'].forEach(id => {
                            const container = document.getElementById(id);
                            if (container) {
                                container.innerHTML = `
                                    <div class="relative inline-block">
                                        <img src="${data.url}" alt="{{ __('messages.graphic_header_image') }}" class="max-h-24 rounded-md border border-gray-200 dark:border-gray-600">
                                        <button type="button" data-action="delete-header-image" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                `;
                                container.classList.remove('hidden');
                            }
                        });
                        currentSettings.header_image_url = data.filename;
                        showNotification('{{ __("messages.saved") }}', 'success');
                        // Reload the graphic preview
                        loadGraphic();
                    } else {
                        showNotification(data.error || '{{ __("messages.error") }}', 'error');
                        ['header_image_preview', 'header_image_preview_mobile'].forEach(id => {
                            const container = document.getElementById(id);
                            if (container && !currentSettings.header_image_url) {
                                container.classList.add('hidden');
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error uploading header image:', error);
                    showNotification('{{ __("messages.error") }}', 'error');
                    ['header_image_preview', 'header_image_preview_mobile'].forEach(id => {
                        const container = document.getElementById(id);
                        if (container && !currentSettings.header_image_url) {
                            container.classList.add('hidden');
                        }
                    });
                });
            }

            function deleteGraphicHeaderImage() {
                if (!confirm('{{ __("messages.are_you_sure") }}')) {
                    return;
                }

                fetch('{{ route("event.graphic_delete_header_image", ["subdomain" => $role->subdomain]) }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide preview for both desktop and mobile
                        ['header_image_preview', 'header_image_preview_mobile'].forEach(id => {
                            const container = document.getElementById(id);
                            if (container) {
                                container.innerHTML = '';
                                container.classList.add('hidden');
                            }
                        });
                        // Clear file inputs
                        ['header_image', 'header_image_mobile'].forEach(id => {
                            const input = document.getElementById(id);
                            if (input) input.value = '';
                        });
                        currentSettings.header_image_url = null;
                        showNotification('{{ __("messages.deleted") }}', 'success');
                        // Reload the graphic preview
                        loadGraphic();
                    } else {
                        showNotification(data.error || '{{ __("messages.error") }}', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error deleting header image:', error);
                    showNotification('{{ __("messages.error") }}', 'error');
                });
            }

            function initHeaderImagePreview() {
                const headerImageUrl = currentSettings.header_image_url;
                if (headerImageUrl) {
                    @if(config('filesystems.default') == 'local')
                    const imageUrl = '{{ url("/storage") }}/' + headerImageUrl;
                    @else
                    const imageUrl = '{{ Storage::url("") }}' + headerImageUrl;
                    @endif

                    ['header_image_preview', 'header_image_preview_mobile'].forEach(id => {
                        const container = document.getElementById(id);
                        if (container) {
                            container.innerHTML = `
                                <div class="relative inline-block">
                                    <img src="${imageUrl}" alt="{{ __('messages.graphic_header_image') }}" class="max-h-24 rounded-md border border-gray-200 dark:border-gray-600">
                                    <button type="button" data-action="delete-header-image" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            `;
                            container.classList.remove('hidden');
                        }
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                updateSettingsUI();
                initTextareaResize();

                // Add change listeners for both desktop and mobile frequency selects
                ['frequency', 'frequency_mobile'].forEach(id => {
                    const frequencySelect = document.getElementById(id);
                    if (frequencySelect) {
                        frequencySelect.addEventListener('change', function() {
                            // Sync to other form
                            const otherId = id === 'frequency' ? 'frequency_mobile' : 'frequency';
                            const otherSelect = document.getElementById(otherId);
                            if (otherSelect) otherSelect.value = this.value;
                            updateDaySelector();
                        });
                    }
                });

                // Add change listeners for email enabled checkboxes
                ['email_enabled', 'email_enabled_mobile'].forEach(id => {
                    const checkbox = document.getElementById(id);
                    if (checkbox) {
                        checkbox.addEventListener('change', function() {
                            // Sync to other form
                            const otherId = id === 'email_enabled' ? 'email_enabled_mobile' : 'email_enabled';
                            const otherCheckbox = document.getElementById(otherId);
                            if (otherCheckbox) otherCheckbox.checked = this.checked;
                            toggleEmailOptions();
                        });
                    }
                });

                // Add change listeners for layout radio buttons
                document.querySelectorAll('input[name="layout"], input[name="layout_mobile"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        currentLayout = this.value;
                        // Sync to other form
                        const otherName = this.name === 'layout' ? 'layout_mobile' : 'layout';
                        const otherRadio = document.querySelector(`input[name="${otherName}"][value="${this.value}"]`);
                        if (otherRadio) otherRadio.checked = true;
                        toggleDatePositionVisibility();
                        toggleMaxPerRowVisibility();
                    });
                });

                // Add change listeners for date position selects
                ['date_position', 'date_position_mobile'].forEach(id => {
                    const select = document.getElementById(id);
                    if (select) {
                        select.addEventListener('change', function() {
                            const otherId = id === 'date_position' ? 'date_position_mobile' : 'date_position';
                            const otherSelect = document.getElementById(otherId);
                            if (otherSelect) otherSelect.value = this.value;
                            toggleOverlayTextVisibility();
                        });
                    }
                });

                // Add change listeners for overlay text inputs
                ['overlay_text', 'overlay_text_mobile'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.addEventListener('input', function() {
                            const otherId = id === 'overlay_text' ? 'overlay_text_mobile' : 'overlay_text';
                            const otherInput = document.getElementById(otherId);
                            if (otherInput) otherInput.value = this.value;
                        });
                    }
                });

                // Add change listeners for event count selects
                ['event_count', 'event_count_mobile'].forEach(id => {
                    const select = document.getElementById(id);
                    if (select) {
                        select.addEventListener('change', function() {
                            const otherId = id === 'event_count' ? 'event_count_mobile' : 'event_count';
                            const otherSelect = document.getElementById(otherId);
                            if (otherSelect) otherSelect.value = this.value;
                        });
                    }
                });

                // Add change listeners for max per row selects
                ['max_per_row', 'max_per_row_mobile'].forEach(id => {
                    const select = document.getElementById(id);
                    if (select) {
                        select.addEventListener('change', function() {
                            const otherId = id === 'max_per_row' ? 'max_per_row_mobile' : 'max_per_row';
                            const otherSelect = document.getElementById(otherId);
                            if (otherSelect) otherSelect.value = this.value;
                        });
                    }
                });

                // Apply initial screen capture visibility state
                toggleScreenCapture();

                // Initialize header image preview if exists
                initHeaderImagePreview();

                // --- Migrated inline event handlers ---

                // File input change handlers for header image upload
                ['header_image', 'header_image_mobile'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.addEventListener('change', function() {
                            uploadGraphicHeaderImage(this.files[0]);
                        });
                    }
                });

                // Choose file buttons that trigger file inputs
                const headerImageBtn = document.getElementById('header_image_btn');
                if (headerImageBtn) {
                    headerImageBtn.addEventListener('click', function() {
                        document.getElementById('header_image').click();
                    });
                }
                const headerImageBtnMobile = document.getElementById('header_image_btn_mobile');
                if (headerImageBtnMobile) {
                    headerImageBtnMobile.addEventListener('click', function() {
                        document.getElementById('header_image_mobile').click();
                    });
                }

                // Screen capture toggle checkboxes
                ['use_screen_capture', 'use_screen_capture_mobile'].forEach(id => {
                    const checkbox = document.getElementById(id);
                    if (checkbox) {
                        checkbox.addEventListener('change', function() {
                            toggleScreenCapture();
                        });
                    }
                });

                // syncFormFields checkboxes
                ['url_include_https', 'url_include_https_mobile', 'url_include_id', 'url_include_id_mobile', 'exclude_recurring', 'exclude_recurring_mobile'].forEach(id => {
                    const checkbox = document.getElementById(id);
                    if (checkbox) {
                        checkbox.addEventListener('change', function() {
                            syncFormFields();
                        });
                    }
                });

                // Test email buttons
                ['testEmailBtn', 'testEmailBtnMobile'].forEach(id => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        btn.addEventListener('click', function() {
                            sendTestEmail();
                        });
                    }
                });

                // Save settings buttons
                ['saveSettingsBtn', 'saveSettingsBtnMobile'].forEach(id => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        btn.addEventListener('click', function() {
                            saveSettings();
                        });
                    }
                });

                // Run / load graphic buttons
                ['runBtn', 'runBtnMobile'].forEach(id => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        btn.addEventListener('click', function() {
                            loadGraphic();
                        });
                    }
                });

                // Copy text button
                const copyTextBtn = document.getElementById('copyTextBtn');
                if (copyTextBtn) {
                    copyTextBtn.addEventListener('click', function() {
                        copyToClipboard(document.getElementById('eventText').value, 'copyTextBtn');
                    });
                }

                // Download image button
                const downloadBtn = document.getElementById('downloadBtn');
                if (downloadBtn) {
                    downloadBtn.addEventListener('click', function() {
                        downloadImage();
                    });
                }

                // Event delegation for dynamically created delete header image buttons
                document.addEventListener('click', function(e) {
                    const deleteBtn = e.target.closest('[data-action="delete-header-image"]');
                    if (deleteBtn) {
                        deleteGraphicHeaderImage();
                    }
                });

                loadGraphic();
            });
        </script>
    </x-slot>

    <!-- Header with Back Button -->
    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <div class="flex items-center gap-3">
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.back') }}
                </a>
            </div>

            <div class="flex items-center text-right">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.events_graphic') }}
                </h2>
            </div>
        @else
            <div class="flex items-center">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.events_graphic') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.back') }}
                </a>
            </div>
        @endif
    </div>

    <!-- Error Message -->
    <div id="errorMessage" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">{{ __('messages.error') }}</h3>
                <div id="errorMessageText" class="mt-2 text-sm text-red-700 dark:text-red-200"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="graphicContent" x-data="{
        activeTab: localStorage.getItem('graphic_page_tab') || 'graphic',
        settingsTab: localStorage.getItem('graphic_settings_tab') || 'graphic',
        settingsOpen: localStorage.getItem('graphic_settings_open') === 'true',
        setTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('graphic_page_tab', tab);
        },
        setSettingsTab(tab) {
            this.settingsTab = tab;
            localStorage.setItem('graphic_settings_tab', tab);
        },
        toggleSettings() {
            this.settingsOpen = !this.settingsOpen;
            localStorage.setItem('graphic_settings_open', this.settingsOpen);
        }
    }">
        <!-- Mobile Collapsible Settings -->
        <div class="lg:hidden mb-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900/50 overflow-hidden">
                <!-- Collapsible Header -->
                <button
                    @click="toggleSettings()"
                    class="w-full px-4 py-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.settings') }}</h3>
                    <svg
                        class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
                        :class="settingsOpen ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Collapsible Content -->
                <div x-show="settingsOpen" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="p-4 bg-gray-50 dark:bg-gray-900/50">

                    <!-- Settings Tab Navigation -->
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                        <nav class="flex space-x-4" aria-label="Settings Tabs">
                            <button type="button"
                                @click="setSettingsTab('graphic')"
                                :class="settingsTab === 'graphic' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                class="px-3 py-2 text-sm font-medium border-b-2">
                                {{ __('messages.graphic') }}
                            </button>
                            <button type="button"
                                @click="setSettingsTab('text')"
                                :class="settingsTab === 'text' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                class="px-3 py-2 text-sm font-medium border-b-2">
                                {{ __('messages.text') }}
                            </button>
                            <button type="button"
                                @click="setSettingsTab('automation')"
                                :class="settingsTab === 'automation' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                class="px-3 py-2 text-sm font-medium border-b-2">
                                {{ __('messages.automation') }}
                            </button>
                        </nav>
                    </div>

                    <!-- Graphic Tab Content -->
                    <div x-show="settingsTab === 'graphic'" x-cloak>
                        <!-- Layout Type -->
                        <div id="layout_type_container_mobile" class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.layout_type') }}</h4>
                            <div class="flex flex-row flex-wrap gap-x-4 gap-y-2">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="layout_mobile" value="grid" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" checked>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.grid_layout') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="layout_mobile" value="row" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.row_layout') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="layout_mobile" value="list" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.list_layout') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Show Text (only for grid and row layouts) -->
                        <div id="date_position_container_mobile" class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.show_text') }}</h4>
                            <select id="date_position_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                <option value="">{{ __('messages.date_position_none') }}</option>
                                <option value="overlay">{{ __('messages.date_position_overlay') }}</option>
                                <option value="above">{{ __('messages.date_position_above') }}</option>
                            </select>

                            <!-- Overlay Text Input (shown when overlay/above selected) -->
                            <div id="overlay_text_container_mobile" class="mt-3 hidden">
                                <input type="text" id="overlay_text_mobile" placeholder="{{ __('messages.overlay_text_placeholder') }}" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ __('messages.overlay_text_help') }}
                                    <a href="{{ marketing_url('/docs/event-graphics#variables') }}" target="_blank" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300">{{ __('messages.view_variables') }}</a>
                                </p>
                            </div>
                        </div>

                        <!-- Max Per Row (only for row layout) -->
                        <div id="max_per_row_container_mobile" class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700 hidden">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.max_per_row') }}</h4>
                            <select id="max_per_row_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                <option value="">{{ __('messages.no_limit') }}</option>
                                @for ($i = 2; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.max_per_row_help') }}</p>
                        </div>

                        <!-- Event Count -->
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.event_count') }}</h4>
                            <select id="event_count_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                <option value="">{{ __('messages.all_available') }}</option>
                                @for ($i = 1; $i <= min($maxEvents ?? 20, 20); $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.event_count_help') }}</p>
                        </div>

                        <!-- Header Image -->
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.graphic_header_image') }}</h4>
                            <input type="file" id="header_image_mobile" class="hidden" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="flex items-center gap-3">
                                <button type="button" id="header_image_btn_mobile" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ __('messages.choose_file') }}
                                </button>
                            </div>
                            <div id="header_image_preview_mobile" class="mt-3 hidden"></div>
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.graphic_header_help') }}</p>
                        </div>

                        <!-- Screen Capture Rendering (Enterprise Feature) -->
                        @if ($isEnterprise)
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.screen_capture_rendering') }}</h4>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="use_screen_capture_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.enable_screen_capture') }}</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.screen_capture_help') }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Text Tab Content -->
                    <div x-show="settingsTab === 'text'" x-cloak>
                        <!-- Text Template -->
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.text_template') }}</h4>
                                <a href="{{ route('marketing.docs.event_graphics') }}" target="_blank"
                                   class="text-xs text-blue-500 hover:text-blue-400">
                                    {{ __('messages.view_docs') }}
                                </a>
                            </div>
                            <textarea id="text_template_mobile" rows="5"
                                aria-label="{{ __('messages.text_template') }}"
                                class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-xs font-mono resize-y"
                                placeholder="*{day_name}* {date_dmy} | {time}&#10;*{event_name}*:&#10;{short_description}&#10;{venue} | {city}&#10;{url}"></textarea>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.text_template_help') }}</p>
                        </div>

                        <!-- AI Prompt (Pro Feature) -->
                        @if ($isPro)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.ai_text_prompt') }}</h4>
                            </div>
                            <div>
                                <textarea id="ai_prompt_mobile" rows="5" dir="auto" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm resize-y" placeholder="{{ __('messages.ai_prompt_placeholder') }}"></textarea>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.ai_prompt_help') }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- URL Format Section -->
                        <div class="mb-5">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.url_format') }}</h4>
                            <div class="flex flex-col gap-2">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="checkbox" id="url_include_https_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.url_include_https') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="checkbox" id="url_include_id_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.url_include_id') }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Automation Tab Content -->
                    <div x-show="settingsTab === 'automation'" x-cloak>
                        <!-- Recurring Events Option -->
                        @if ($hasRecurringEvents)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.recurring_events') }}</h4>
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" id="exclude_recurring_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.exclude_recurring_events') }}</span>
                            </label>
                        </div>
                        @endif

                        <!-- Email Scheduling (Enterprise Feature) -->
                        @if ($isEnterprise)
                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.email_scheduling') }}</h4>
                            </div>

                            <div>
                                <!-- Enable Toggle -->
                                <div class="flex items-center gap-3 mb-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="email_enabled_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.enable_scheduled_emails') }}</span>
                                    </label>
                                </div>

                                <!-- Schedule Row (shown/hidden based on checkbox) -->
                                <div id="email_options_container_mobile" class="hidden">
                                    <div class="flex flex-col gap-3 mb-4">
                                        <div>
                                            <label for="frequency_mobile" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.frequency') }}</label>
                                            <select id="frequency_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                <option value="daily">{{ __('messages.daily') }}</option>
                                                <option value="weekly">{{ __('messages.weekly') }}</option>
                                                <option value="monthly">{{ __('messages.monthly') }}</option>
                                            </select>
                                        </div>

                                        <div id="weekly_day_container_mobile" class="hidden">
                                            <label for="weekly_day_mobile" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.send_on') }}</label>
                                            <select id="weekly_day_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                <option value="0">{{ __('messages.sunday') }}</option>
                                                <option value="1">{{ __('messages.monday') }}</option>
                                                <option value="2">{{ __('messages.tuesday') }}</option>
                                                <option value="3">{{ __('messages.wednesday') }}</option>
                                                <option value="4">{{ __('messages.thursday') }}</option>
                                                <option value="5">{{ __('messages.friday') }}</option>
                                                <option value="6">{{ __('messages.saturday') }}</option>
                                            </select>
                                        </div>

                                        <div id="monthly_day_container_mobile" class="hidden">
                                            <label for="monthly_day_mobile" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.day_of_month') }}</label>
                                            <select id="monthly_day_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                @for ($i = 1; $i <= 28; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>

                                        <div>
                                            <label for="send_hour_mobile" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.send_at_hour') }}</label>
                                            <select id="send_hour_mobile" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                @for ($i = 0; $i < 24; $i++)
                                                    <option value="{{ $i }}">{{ sprintf('%02d:00', $i) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="recipient_emails_mobile" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            {{ __('messages.recipient_emails') }}
                                        </label>
                                        <input type="text" id="recipient_emails_mobile"
                                            class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                            placeholder="{{ __('messages.recipient_emails_placeholder') }}">
                                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.recipient_emails_help') }}</p>
                                    </div>

                                    <button id="testEmailBtnMobile" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('messages.send_test_email') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($hasRecurringEvents)
                                {{ __('messages.enterprise_feature_email_scheduling') }}
                            @else
                                {{ __('messages.enterprise_feature_email_scheduling') }}
                            @endif
                        </p>
                        @endif
                    </div>

                    <!-- Buttons (side by side on mobile) -->
                    <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button id="saveSettingsBtnMobile" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-[#4E81FA] hover:bg-[#3D6FE8] text-white text-sm font-semibold rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('messages.save_settings') }}
                        </button>
                        <button id="runBtnMobile" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ __('messages.run') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Tab Buttons -->
        <div class="flex mb-4 lg:hidden bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
            <button
                @click="setTab('graphic')"
                :class="activeTab === 'graphic' ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'"
                class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors"
            >
                {{ __('messages.graphic') }}
            </button>
            <button
                @click="setTab('text')"
                :class="activeTab === 'text' ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'"
                class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors"
            >
                {{ __('messages.text') }}
            </button>
        </div>

        <!-- Outer Panel (desktop only) -->
        <div class="lg:bg-white lg:dark:bg-gray-800 lg:rounded-lg lg:shadow lg:dark:shadow-gray-900/50 lg:p-6">
            <!-- Two Column Layout on Desktop -->
            <div class="grid grid-cols-1 lg:grid-cols-[45%_1fr] gap-6">
                <!-- Left Column - Settings (Desktop only) -->
                <div class="hidden lg:block bg-gray-50 dark:bg-gray-900/50 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.settings') }}</h3>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50">
                        <!-- Settings Tab Navigation -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                            <nav class="flex space-x-4" aria-label="Settings Tabs">
                                <button type="button"
                                    @click="setSettingsTab('graphic')"
                                    :class="settingsTab === 'graphic' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="px-3 py-2 text-sm font-medium border-b-2">
                                    {{ __('messages.graphic') }}
                                </button>
                                <button type="button"
                                    @click="setSettingsTab('text')"
                                    :class="settingsTab === 'text' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="px-3 py-2 text-sm font-medium border-b-2">
                                    {{ __('messages.text') }}
                                </button>
                                <button type="button"
                                    @click="setSettingsTab('automation')"
                                    :class="settingsTab === 'automation' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                                    class="px-3 py-2 text-sm font-medium border-b-2">
                                    {{ __('messages.automation') }}
                                </button>
                            </nav>
                        </div>

                        <!-- Graphic Tab Content -->
                        <div x-show="settingsTab === 'graphic'" x-cloak>
                            <!-- Layout Type -->
                            <div id="layout_type_container" class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.layout_type') }}</h4>
                                <div class="flex flex-row flex-wrap gap-x-4 gap-y-2">
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" name="layout" value="grid" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" checked>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.grid_layout') }}</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" name="layout" value="row" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.row_layout') }}</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="radio" name="layout" value="list" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.list_layout') }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Show Text (only for grid and row layouts) -->
                            <div id="date_position_container" class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.show_text') }}</h4>
                                <select id="date_position" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    <option value="">{{ __('messages.date_position_none') }}</option>
                                    <option value="overlay">{{ __('messages.date_position_overlay') }}</option>
                                    <option value="above">{{ __('messages.date_position_above') }}</option>
                                </select>

                                <!-- Overlay Text Input (shown when overlay/above selected) -->
                                <div id="overlay_text_container" class="mt-3 hidden">
                                    <input type="text" id="overlay_text" placeholder="{{ __('messages.overlay_text_placeholder') }}" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        {{ __('messages.overlay_text_help') }}
                                        <a href="{{ marketing_url('/docs/event-graphics#variables') }}" target="_blank" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300">{{ __('messages.view_variables') }}</a>
                                    </p>
                                </div>
                            </div>

                            <!-- Max Per Row (only for row layout) -->
                            <div id="max_per_row_container" class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700 hidden">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.max_per_row') }}</h4>
                                <select id="max_per_row" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    <option value="">{{ __('messages.no_limit') }}</option>
                                    @for ($i = 2; $i <= 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.max_per_row_help') }}</p>
                            </div>

                            <!-- Event Count -->
                            <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.event_count') }}</h4>
                                <select id="event_count" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                    <option value="">{{ __('messages.all_available') }}</option>
                                    @for ($i = 1; $i <= min($maxEvents ?? 20, 20); $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.event_count_help') }}</p>
                            </div>

                            <!-- Header Image -->
                            <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.graphic_header_image') }}</h4>
                                <input type="file" id="header_image" class="hidden" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="flex items-center gap-3">
                                    <button type="button" id="header_image_btn" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('messages.choose_file') }}
                                    </button>
                                </div>
                                <div id="header_image_preview" class="mt-3 hidden"></div>
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.graphic_header_help') }}</p>
                            </div>

                            <!-- Screen Capture Rendering (Enterprise Feature) -->
                            @if ($isEnterprise)
                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.screen_capture_rendering') }}</h4>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="use_screen_capture" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.enable_screen_capture') }}</span>
                                    </label>
                                </div>
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.screen_capture_help') }}</p>
                            </div>
                            @endif
                        </div>

                        <!-- Text Tab Content -->
                        <div x-show="settingsTab === 'text'" x-cloak>
                            <!-- Text Template -->
                            <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.text_template') }}</h4>
                                    <a href="{{ route('marketing.docs.event_graphics') }}" target="_blank"
                                       class="text-xs text-blue-500 hover:text-blue-400">
                                        {{ __('messages.view_docs') }}
                                    </a>
                                </div>
                                <textarea id="text_template" rows="5"
                                    aria-label="{{ __('messages.text_template') }}"
                                    class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-xs font-mono resize-y"
                                    placeholder="*{day_name}* {date_dmy} | {time}&#10;*{event_name}*:&#10;{short_description}&#10;{venue} | {city}&#10;{url}"></textarea>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.text_template_help') }}</p>
                            </div>

                            <!-- AI Prompt (Pro Feature) -->
                            @if ($isPro)
                            <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.ai_text_prompt') }}</h4>
                                </div>
                                <div>
                                    <textarea id="ai_prompt" rows="5" dir="auto" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm resize-y" placeholder="{{ __('messages.ai_prompt_placeholder') }}"></textarea>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.ai_prompt_help') }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- URL Format Section -->
                            <div class="mb-5">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.url_format') }}</h4>
                                <div class="flex flex-col gap-2">
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" id="url_include_https" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.url_include_https') }}</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" id="url_include_id" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.url_include_id') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Automation Tab Content -->
                        <div x-show="settingsTab === 'automation'" x-cloak>
                            <!-- Recurring Events Option -->
                            @if ($hasRecurringEvents)
                            <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.recurring_events') }}</h4>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="checkbox" id="exclude_recurring" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.exclude_recurring_events') }}</span>
                                </label>
                            </div>
                            @endif

                            <!-- Email Scheduling (Enterprise Feature) -->
                            @if ($isEnterprise)
                            <div class="mb-5">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.email_scheduling') }}</h4>
                                </div>

                                <div>
                                    <!-- Enable Toggle -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" id="email_enabled" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.enable_scheduled_emails') }}</span>
                                        </label>
                                    </div>

                                    <!-- Schedule Row (shown/hidden based on checkbox) -->
                                    <div id="email_options_container" class="hidden">
                                        <div class="flex flex-col gap-3 mb-4">
                                            <div>
                                                <label for="frequency" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.frequency') }}</label>
                                                <select id="frequency" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                    <option value="daily">{{ __('messages.daily') }}</option>
                                                    <option value="weekly">{{ __('messages.weekly') }}</option>
                                                    <option value="monthly">{{ __('messages.monthly') }}</option>
                                                </select>
                                            </div>

                                            <div id="weekly_day_container" class="hidden">
                                                <label for="weekly_day" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.send_on') }}</label>
                                                <select id="weekly_day" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                    <option value="0">{{ __('messages.sunday') }}</option>
                                                    <option value="1">{{ __('messages.monday') }}</option>
                                                    <option value="2">{{ __('messages.tuesday') }}</option>
                                                    <option value="3">{{ __('messages.wednesday') }}</option>
                                                    <option value="4">{{ __('messages.thursday') }}</option>
                                                    <option value="5">{{ __('messages.friday') }}</option>
                                                    <option value="6">{{ __('messages.saturday') }}</option>
                                                </select>
                                            </div>

                                            <div id="monthly_day_container" class="hidden">
                                                <label for="monthly_day" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.day_of_month') }}</label>
                                                <select id="monthly_day" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                    @for ($i = 1; $i <= 28; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>

                                            <div>
                                                <label for="send_hour" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.send_at_hour') }}</label>
                                                <select id="send_hour" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                                    @for ($i = 0; $i < 24; $i++)
                                                        <option value="{{ $i }}">{{ sprintf('%02d:00', $i) }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="recipient_emails" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                                {{ __('messages.recipient_emails') }}
                                            </label>
                                            <input type="text" id="recipient_emails"
                                                class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                                                placeholder="{{ __('messages.recipient_emails_placeholder') }}">
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.recipient_emails_help') }}</p>
                                        </div>

                                        <button id="testEmailBtn" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ __('messages.send_test_email') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if ($hasRecurringEvents)
                                    {{ __('messages.enterprise_feature_email_scheduling') }}
                                @else
                                    {{ __('messages.enterprise_feature_email_scheduling') }}
                                @endif
                            </p>
                            @endif
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-row gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button id="saveSettingsBtn" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-[#4E81FA] hover:bg-[#3D6FE8] text-white text-sm font-semibold rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('messages.save_settings') }}
                            </button>
                            <button id="runBtn" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('messages.run') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Text + Image Stacked -->
                <div class="flex flex-col gap-6">
                    <!-- Event Text Section (Desktop always visible, Mobile only on text tab) -->
                    <div x-show="activeTab === 'text'" x-cloak class="lg:!block bg-white dark:bg-gray-800 lg:bg-gray-50 lg:dark:bg-gray-900/50 rounded-lg shadow lg:shadow-none dark:shadow-gray-900/50 overflow-hidden flex flex-col lg:border lg:border-gray-200 lg:dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-end">
                            <button
                                id="copyTextBtn"
                                class="inline-flex items-center px-3 py-1.5 bg-[#4E81FA] hover:bg-[#3D6FE8] text-white text-sm font-semibold rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('messages.copy') }}
                            </button>
                        </div>
                        <div class="p-4 flex-1 bg-gray-50 dark:bg-gray-900/50">
                            <!-- Text Loading Spinner -->
                            <div id="textLoadingSpinner" class="flex justify-center items-center py-10">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 dark:border-blue-400"></div>
                            </div>
                            <textarea
                                id="eventText"
                                readonly
                                class="hidden w-full h-full p-3 border border-gray-200 dark:border-gray-700 rounded-md resize-none font-mono text-sm leading-relaxed whitespace-pre-wrap overflow-y-auto bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none min-h-[50vh] lg:min-h-[200px]"
                                dir="{{ $role->isRtl() ? 'rtl' : 'ltr' }}"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Graphic Image Section (Desktop always visible, Mobile only on graphic tab) -->
                    <div x-show="activeTab === 'graphic'" x-cloak class="lg:!block bg-white dark:bg-gray-800 lg:bg-gray-50 lg:dark:bg-gray-900/50 rounded-lg shadow lg:shadow-none dark:shadow-gray-900/50 overflow-hidden lg:border lg:border-gray-200 lg:dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-end">
                            <button
                                id="downloadBtn"
                                class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                {{ __('messages.download') }}
                            </button>
                        </div>
                        <div class="p-4 flex items-center justify-center bg-gray-50 dark:bg-gray-900/50" style="min-height: 300px;">
                            <!-- Graphic Loading Spinner -->
                            <div id="graphicLoadingSpinner" class="flex justify-center items-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 dark:border-green-400"></div>
                            </div>
                            <img
                                id="graphicImage"
                                src=""
                                alt="{{ __('messages.events_graphic') }}"
                                class="hidden max-w-full h-auto rounded shadow-sm"
                                style="max-height: 50vh; object-fit: contain;"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
