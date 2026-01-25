<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            let graphicData = null;
            let currentSettings = @json($graphicSettings);
            const isPro = {{ $isPro ? 'true' : 'false' }};
            const isEnterprise = {{ $isEnterprise ? 'true' : 'false' }};
            const currentUserEmail = '{{ auth()->user()->email }}';

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

            function loadGraphic() {
                const spinner = document.getElementById('loadingSpinner');
                const content = document.getElementById('graphicContent');
                const errorDiv = document.getElementById('errorMessage');

                spinner.classList.remove('hidden');
                content.classList.add('hidden');
                errorDiv.classList.add('hidden');

                const linkType = currentSettings.link_type || 'schedule';
                const directParam = linkType === 'registration' ? '&direct=1' : '';

                fetch('{{ route("event.generate_graphic_data", ["subdomain" => $role->subdomain, "layout" => $layout]) }}' + directParam)
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

                        const img = document.getElementById('graphicImage');
                        img.src = 'data:image/png;base64,' + data.image;

                        const textarea = document.getElementById('eventText');
                        textarea.value = data.text;

                        const downloadBtn = document.getElementById('downloadBtn');
                        downloadBtn.onclick = downloadImage;

                        spinner.classList.add('hidden');
                        content.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error loading graphic:', error);
                        const errorTextDiv = document.getElementById('errorMessageText');
                        if (errorTextDiv) {
                            errorTextDiv.textContent = error.message || '{{ __("messages.error_loading_graphic") }}';
                        }
                        errorDiv.classList.remove('hidden');
                        spinner.classList.add('hidden');
                    });
            }

            function updateSettingsUI() {
                // Update link type radio buttons
                const linkType = currentSettings.link_type || 'schedule';
                const radioToCheck = document.querySelector(`input[name="link_type"][value="${linkType}"]`);
                if (radioToCheck) {
                    radioToCheck.checked = true;
                }

                // Update email scheduling fields
                const enabledCheckbox = document.getElementById('email_enabled');
                if (enabledCheckbox) {
                    enabledCheckbox.checked = currentSettings.enabled || false;
                    toggleEmailOptions();
                }

                const frequency = document.getElementById('frequency');
                if (frequency) {
                    frequency.value = currentSettings.frequency || 'weekly';
                    updateDaySelector();
                }

                // Set send_day value for both weekly and monthly selectors
                const sendDayValue = currentSettings.send_day || 1;
                const weeklyDay = document.getElementById('weekly_day');
                if (weeklyDay) {
                    weeklyDay.value = sendDayValue;
                }
                const monthlyDay = document.getElementById('monthly_day');
                if (monthlyDay) {
                    monthlyDay.value = sendDayValue;
                }

                const sendHour = document.getElementById('send_hour');
                if (sendHour) {
                    sendHour.value = currentSettings.send_hour || 9;
                }

                const aiPrompt = document.getElementById('ai_prompt');
                if (aiPrompt) {
                    aiPrompt.value = currentSettings.ai_prompt || '';
                }

                const useScreenCapture = document.getElementById('use_screen_capture');
                if (useScreenCapture) {
                    useScreenCapture.checked = currentSettings.use_screen_capture || false;
                }

                const recipientEmails = document.getElementById('recipient_emails');
                if (recipientEmails) {
                    recipientEmails.value = currentSettings.recipient_emails || '';
                }
            }

            function toggleScreenCapture() {
                // Placeholder for any screen capture toggle logic if needed
            }

            function updateDaySelector() {
                const frequencyEl = document.getElementById('frequency');
                const weeklyDayContainer = document.getElementById('weekly_day_container');
                const monthlyDayContainer = document.getElementById('monthly_day_container');

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
            }

            function toggleEmailOptions() {
                const enabledCheckbox = document.getElementById('email_enabled');
                const optionsContainer = document.getElementById('email_options_container');
                const recipientEmails = document.getElementById('recipient_emails');

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
            }

            function saveSettings() {
                const saveBtn = document.getElementById('saveSettingsBtn');
                const originalText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '{{ __("messages.saving") }}';

                const frequency = document.getElementById('frequency').value;
                let sendDay = 1;
                if (frequency === 'weekly') {
                    sendDay = document.getElementById('weekly_day').value;
                } else if (frequency === 'monthly') {
                    sendDay = document.getElementById('monthly_day').value;
                }

                const emailEnabled = document.getElementById('email_enabled');
                const sendHour = document.getElementById('send_hour');
                const aiPrompt = document.getElementById('ai_prompt');
                const useScreenCapture = document.getElementById('use_screen_capture');
                const recipientEmails = document.getElementById('recipient_emails');

                // Validate recipient emails is required when email scheduling is enabled
                if (emailEnabled && emailEnabled.checked && recipientEmails && !recipientEmails.value.trim()) {
                    showNotification('{{ __("messages.recipient_emails_required") }}', 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                    recipientEmails.focus();
                    return;
                }

                const settings = {
                    enabled: emailEnabled ? emailEnabled.checked : false,
                    frequency: frequency,
                    send_day: parseInt(sendDay),
                    send_hour: sendHour ? parseInt(sendHour.value) : 9,
                    ai_prompt: aiPrompt ? aiPrompt.value : '',
                    link_type: document.querySelector('input[name="link_type"]:checked').value,
                    layout: '{{ $layout }}',
                    use_screen_capture: useScreenCapture ? useScreenCapture.checked : false,
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
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                });
            }

            function sendTestEmail() {
                const testBtn = document.getElementById('testEmailBtn');
                const originalText = testBtn.innerHTML;
                testBtn.disabled = true;
                testBtn.innerHTML = '{{ __("messages.sending") }}...';

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
                    testBtn.disabled = false;
                    testBtn.innerHTML = originalText;
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

            document.addEventListener('DOMContentLoaded', function() {
                updateSettingsUI();

                document.querySelectorAll('input[name="link_type"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        currentSettings.link_type = this.value;
                    });
                });

                const frequencySelect = document.getElementById('frequency');
                if (frequencySelect) {
                    frequencySelect.addEventListener('change', updateDaySelector);
                }

                loadGraphic();
            });
        </script>
    </x-slot>

    <!-- Header with Cancel Button -->
    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <div class="flex items-center gap-3">
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}"
                   class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
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
                    {{ __('messages.cancel') }}
                </a>
            </div>
        @endif
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="flex justify-center items-center py-20">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 dark:border-blue-400 mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.generating_graphic') }}...</p>
        </div>
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
    <div id="graphicContent" class="hidden">
        <!-- Outer Panel (desktop only) -->
        <div class="lg:bg-white lg:dark:bg-gray-800 lg:rounded-lg lg:shadow lg:dark:shadow-gray-900/50 lg:p-6">
            <!-- Three Column Layout on Desktop -->
            <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr_1fr] gap-6">
                <!-- Left Column - Settings -->
                <div class="bg-white dark:bg-gray-800 lg:bg-gray-50 lg:dark:bg-gray-900/50 rounded-lg shadow lg:shadow-none dark:shadow-gray-900/50 overflow-hidden lg:border lg:border-gray-200 lg:dark:border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.settings') }}</h3>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50">
                        <!-- AI Prompt (Pro Feature) -->
                        @if ($isPro)
                        <div class="mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.ai_text_prompt') }}</h4>
                            </div>
                            <div>
                                <textarea id="ai_prompt" rows="3" class="w-full px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm resize-none" placeholder="{{ __('messages.ai_prompt_placeholder') }}"></textarea>
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
                        <div class="flex flex-col gap-2">
                            <button id="saveSettingsBtn" onclick="saveSettings()" class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('messages.save_settings') }}
                            </button>
                            <button id="runBtn" onclick="loadGraphic()" class="inline-flex items-center justify-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('messages.run') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Middle Column - Image -->
                <div class="bg-white dark:bg-gray-800 lg:bg-gray-50 lg:dark:bg-gray-900/50 rounded-lg shadow lg:shadow-none dark:shadow-gray-900/50 overflow-hidden lg:border lg:border-gray-200 lg:dark:border-gray-700">
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
                    <div class="p-4 flex items-center justify-center bg-gray-50 dark:bg-gray-900/50" style="min-height: 400px;">
                        <img
                            id="graphicImage"
                            src=""
                            alt="{{ __('messages.events_graphic') }}"
                            class="max-w-full h-auto rounded shadow-sm"
                            style="max-height: 60vh; object-fit: contain;"
                        />
                    </div>
                </div>

                <!-- Right Column - Event Text -->
                <div class="bg-white dark:bg-gray-800 lg:bg-gray-50 lg:dark:bg-gray-900/50 rounded-lg shadow lg:shadow-none dark:shadow-gray-900/50 overflow-hidden flex flex-col lg:border lg:border-gray-200 lg:dark:border-gray-700">
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
                        <textarea
                            id="eventText"
                            readonly
                            class="w-full h-full p-3 border border-gray-200 dark:border-gray-700 rounded-md resize-none font-mono text-sm leading-relaxed whitespace-pre-wrap overflow-y-auto bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none"
                            style="min-height: 360px;"
                            dir="{{ $role->isRtl() ? 'rtl' : 'ltr' }}"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
