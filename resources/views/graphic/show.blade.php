<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            let graphicData = null;
            let currentSettings = @json($graphicSettings);
            const isPro = {{ $isPro ? 'true' : 'false' }};
            const isEnterprise = {{ $isEnterprise ? 'true' : 'false' }};
            const currentUserEmail = '{{ auth()->user()->email }}';
            const hasRecurringEvents = @json($hasRecurringEvents);

            function copyToClipboard(text, buttonId) {
                navigator.clipboard.writeText(text).then(function() {
                    const button = document.getElementById(buttonId);
                    const originalText = button.innerHTML;
                    button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>{{ __("messages.copied") }}';
                    button.classList.add('bg-green-500', 'hover:bg-green-600');
                    button.classList.remove('bg-blue-500', 'hover:bg-blue-600');

                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-green-500', 'hover:bg-green-600');
                        button.classList.add('bg-blue-500', 'hover:bg-blue-600');
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
                const linkType = document.querySelector('input[name="link_type"]:checked')?.value ||
                                 document.querySelector('input[name="link_type_mobile"]:checked')?.value || 'schedule';
                const aiPrompt = document.getElementById('ai_prompt')?.value ||
                                 document.getElementById('ai_prompt_mobile')?.value || '';
                const textTemplate = document.getElementById('text_template')?.value ||
                                     document.getElementById('text_template_mobile')?.value || '';
                const useScreenCapture = document.getElementById('use_screen_capture')?.checked ||
                                         document.getElementById('use_screen_capture_mobile')?.checked || false;
                const excludeRecurring = document.getElementById('exclude_recurring')?.checked ||
                                         document.getElementById('exclude_recurring_mobile')?.checked || false;

                return {
                    link_type: linkType,
                    ai_prompt: aiPrompt,
                    text_template: textTemplate,
                    use_screen_capture: useScreenCapture,
                    exclude_recurring: excludeRecurring
                };
            }

            // Sync settings between desktop and mobile forms
            function syncFormFields() {
                // Sync link type
                const linkTypeDesktop = document.querySelector('input[name="link_type"]:checked');
                const linkTypeMobile = document.querySelector('input[name="link_type_mobile"]:checked');
                if (linkTypeDesktop && linkTypeMobile) {
                    if (linkTypeDesktop.value !== linkTypeMobile.value) {
                        const mobileRadio = document.querySelector(`input[name="link_type_mobile"][value="${linkTypeDesktop.value}"]`);
                        if (mobileRadio) mobileRadio.checked = true;
                    }
                }

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
                const directParam = formSettings.link_type === 'registration' ? '&direct=1' : '';
                const screenCaptureParam = formSettings.use_screen_capture ? '&use_screen_capture=1' : '';
                const aiPromptParam = formSettings.ai_prompt ? '&ai_prompt=' + encodeURIComponent(formSettings.ai_prompt) : '';
                const textTemplateParam = formSettings.text_template ? '&text_template=' + encodeURIComponent(formSettings.text_template) : '';
                const excludeRecurringParam = formSettings.exclude_recurring ? '&exclude_recurring=1' : '';

                fetch('{{ route("event.generate_graphic_data", ["subdomain" => $role->subdomain, "layout" => $layout]) }}' + directParam + screenCaptureParam + aiPromptParam + textTemplateParam + excludeRecurringParam)
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 404) {
                                return response.json().then(data => {
                                    throw new Error(data.error || '{{ __("messages.no_events_found") }}');
                                });
                            }
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

                        const downloadBtn = document.getElementById('downloadBtn');
                        downloadBtn.onclick = downloadImage;
                    })
                    .catch(error => {
                        console.error('Error loading graphic:', error);

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
                // Update link type radio buttons (desktop and mobile)
                const linkType = currentSettings.link_type || 'schedule';
                ['link_type', 'link_type_mobile'].forEach(name => {
                    const radioToCheck = document.querySelector(`input[name="${name}"][value="${linkType}"]`);
                    if (radioToCheck) {
                        radioToCheck.checked = true;
                    }
                });

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
            }

            function toggleScreenCapture() {
                // Placeholder for any screen capture toggle logic if needed
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

                // Get link type from desktop or mobile
                const linkTypeChecked = document.querySelector('input[name="link_type"]:checked') ||
                                        document.querySelector('input[name="link_type_mobile"]:checked');

                const settings = {
                    enabled: emailEnabled ? emailEnabled.checked : false,
                    frequency: frequency,
                    send_day: parseInt(sendDay),
                    send_hour: sendHour ? parseInt(sendHour.value) : 9,
                    ai_prompt: aiPrompt ? aiPrompt.value : '',
                    text_template: textTemplate ? textTemplate.value : '',
                    link_type: linkTypeChecked ? linkTypeChecked.value : 'schedule',
                    layout: '{{ $layout }}',
                    use_screen_capture: useScreenCapture ? useScreenCapture.checked : false,
                    exclude_recurring: excludeRecurring ? excludeRecurring.checked : false,
                    recipient_emails: recipientEmails ? recipientEmails.value : ''
                };

                fetch('{{ route("event.save_graphic_settings", ["subdomain" => $role->subdomain]) }}', {
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
                        showNotification('{{ __("messages.settings_saved") }}', 'success');
                    } else {
                        showNotification(data.message || '{{ __("messages.error") }}', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error saving settings:', error);
                    showNotification('{{ __("messages.error") }}', 'error');
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

                fetch('{{ route("event.graphic_test_email", ["subdomain" => $role->subdomain]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({})
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
                    showNotification('{{ __("messages.error") }}', 'error');
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

            document.addEventListener('DOMContentLoaded', function() {
                updateSettingsUI();
                initTextareaResize();

                // Add change listeners for both desktop and mobile link type radios
                document.querySelectorAll('input[name="link_type"], input[name="link_type_mobile"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        currentSettings.link_type = this.value;
                        // Sync to other form
                        const otherName = this.name === 'link_type' ? 'link_type_mobile' : 'link_type';
                        const otherRadio = document.querySelector(`input[name="${otherName}"][value="${this.value}"]`);
                        if (otherRadio) otherRadio.checked = true;
                    });
                });

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

            <div class="text-right">
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.events_graphic') }}
                </h2>
            </div>
        @else
            <div>
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
        settingsOpen: localStorage.getItem('graphic_settings_open') === 'true',
        setTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('graphic_page_tab', tab);
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
                            placeholder="*{day_name}* {date_dmy} | {time}&#10;*{event_name}*:&#10;{venue} | {city}&#10;{url}"></textarea>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.text_template_help') }}</p>
                    </div>

                    <!-- AI Prompt (Pro Feature) -->
                    @if ($isPro)
                    <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.ai_text_prompt') }}</h4>
                        </div>
                        <div>
                            <textarea id="ai_prompt_mobile" rows="5" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm resize-y" placeholder="{{ __('messages.ai_prompt_placeholder') }}"></textarea>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.ai_prompt_help') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Link Type Options -->
                    <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.options') }}</h4>
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="link_type_mobile" value="schedule" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.link_to_event_page') }}</span>
                            </label>
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" name="link_type_mobile" value="registration" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.link_to_registration') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Recurring Events Option -->
                    @if ($hasRecurringEvents)
                    <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.recurring_events') }}</h4>
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" id="exclude_recurring_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" onchange="syncFormFields()">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.exclude_recurring_events') }}</span>
                        </label>
                    </div>
                    @endif

                    <!-- Screen Capture Rendering (Enterprise Feature) -->
                    @if ($isEnterprise)
                    <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.screen_capture_rendering') }}</h4>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="use_screen_capture_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" onchange="toggleScreenCapture()">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.enable_screen_capture') }}</span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.screen_capture_help') }}</p>
                    </div>
                    @endif

                    <!-- Email Scheduling (Enterprise Feature) -->
                    @if ($isEnterprise)
                    <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.email_scheduling') }}</h4>
                        </div>

                        <div>
                            <!-- Enable Toggle -->
                            <div class="flex items-center gap-3 mb-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="email_enabled_mobile" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" onchange="toggleEmailOptions()">
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

                                <button id="testEmailBtnMobile" onclick="sendTestEmail()" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ __('messages.send_test_email') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Buttons (side by side on mobile) -->
                    <div class="flex gap-2">
                        <button id="saveSettingsBtnMobile" onclick="saveSettings()" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('messages.save_settings') }}
                        </button>
                        <button id="runBtnMobile" onclick="loadGraphic()" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
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
                                placeholder="*{day_name}* {date_dmy} | {time}&#10;*{event_name}*:&#10;{venue} | {city}&#10;{url}"></textarea>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.text_template_help') }}</p>
                        </div>

                        <!-- AI Prompt (Pro Feature) -->
                        @if ($isPro)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.ai_text_prompt') }}</h4>
                            </div>
                            <div>
                                <textarea id="ai_prompt" rows="5" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm resize-y" placeholder="{{ __('messages.ai_prompt_placeholder') }}"></textarea>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.ai_prompt_help') }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Link Type Options -->
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.options') }}</h4>
                            <div class="flex flex-col gap-2">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="link_type" value="schedule" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.link_to_event_page') }}</span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input type="radio" name="link_type" value="registration" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.link_to_registration') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Recurring Events Option -->
                        @if ($hasRecurringEvents)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.recurring_events') }}</h4>
                            <label class="flex items-center cursor-pointer group">
                                <input type="checkbox" id="exclude_recurring" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" onchange="syncFormFields()">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-gray-100">{{ __('messages.exclude_recurring_events') }}</span>
                            </label>
                        </div>
                        @endif

                        <!-- Screen Capture Rendering (Enterprise Feature) -->
                        @if ($isEnterprise)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.screen_capture_rendering') }}</h4>
                            </div>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="use_screen_capture" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" onchange="toggleScreenCapture()">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.enable_screen_capture') }}</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.screen_capture_help') }}</p>
                        </div>
                        @endif

                        <!-- Email Scheduling (Enterprise Feature) -->
                        @if ($isEnterprise)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.email_scheduling') }}</h4>
                            </div>

                            <div>
                                <!-- Enable Toggle -->
                                <div class="flex items-center gap-3 mb-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="email_enabled" class="w-4 h-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700" onchange="toggleEmailOptions()">
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

                                    <button id="testEmailBtn" onclick="sendTestEmail()" class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('messages.send_test_email') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Buttons -->
                        <div class="flex flex-row gap-2">
                            <button id="saveSettingsBtn" onclick="saveSettings()" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('messages.save_settings') }}
                            </button>
                            <button id="runBtn" onclick="loadGraphic()" class="flex-1 inline-flex items-center justify-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
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
                                onclick="copyToClipboard(document.getElementById('eventText').value, 'copyTextBtn')"
                                id="copyTextBtn"
                                class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-colors"
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
                                onclick="downloadImage()"
                                class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors"
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
