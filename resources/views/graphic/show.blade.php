<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            let graphicData = null;

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

                const linkType = localStorage.getItem('graphic_link_type') || 'schedule';
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

            document.addEventListener('DOMContentLoaded', function() {
                const savedType = localStorage.getItem('graphic_link_type') || 'schedule';
                const radioToCheck = document.querySelector(`input[name="link_type"][value="${savedType}"]`);
                if (radioToCheck) {
                    radioToCheck.checked = true;
                }

                document.querySelectorAll('input[name="link_type"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        localStorage.setItem('graphic_link_type', this.value);
                        loadGraphic();
                    });
                });

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
        <!-- Settings Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900/50 p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ __('messages.link_type') }}:</span>
                <div class="flex flex-wrap gap-x-6 gap-y-2">
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
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Image -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900/50 overflow-hidden">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900/50 overflow-hidden flex flex-col">
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
</x-app-admin-layout>
