<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            let graphicData = null;
            
            function copyToClipboard(text, buttonId) {
                navigator.clipboard.writeText(text).then(function() {
                    const button = document.getElementById(buttonId);
                    const originalText = button.textContent;
                    button.textContent = '{{ __("messages.copied") }}';
                    button.classList.add('bg-green-500', 'hover:bg-green-600');
                    button.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                    
                    setTimeout(function() {
                        button.textContent = originalText;
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
                
                // Get the base64 image data from the loaded image
                const img = document.getElementById('graphicImage');
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Set canvas dimensions to match image
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                
                // Draw the image onto the canvas
                ctx.drawImage(img, 0, 0);
                
                // Convert canvas to blob and download
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
                
                // Show spinner, hide content
                spinner.classList.remove('hidden');
                content.classList.add('hidden');
                errorDiv.classList.add('hidden');
                
                fetch('{{ route("event.generate_graphic_data", ["subdomain" => $role->subdomain, "layout" => $layout]) }}')
                    .then(response => {
                        if (!response.ok) {
                            // Handle 404 specifically for no events
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
                        
                        // Update image
                        const img = document.getElementById('graphicImage');
                        img.src = 'data:image/png;base64,' + data.image;
                        
                        // Update text
                        const textarea = document.getElementById('eventText');
                        textarea.value = data.text;
                        
                        // Update download URL
                        const downloadBtn = document.getElementById('downloadBtn');
                        downloadBtn.onclick = downloadImage;
                        
                        // Show content, hide spinner
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
            
            // Load graphic when page loads
            document.addEventListener('DOMContentLoaded', loadGraphic);
        </script>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.events_graphic') }}</h1>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="flex justify-center items-center py-20">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 dark:border-blue-400 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-400 text-lg">{{ __('messages.generating_graphic') }}...</p>
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

            <!-- Two Column Layout -->
            <div id="graphicContent" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 min-h-screen">
                <!-- Left Column - Image -->
                <div class="space-y-4 flex flex-col">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button 
                            id="downloadBtn"
                            onclick="downloadImage()"
                            class="inline-flex items-center justify-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-md transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('messages.download_image') }}
                        </button>
                    </div>
                    
                    <!-- Image Display -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-gray-900/50 p-4 flex-1 flex items-center justify-center">
                        <div class="relative max-w-full">
                            <img 
                                id="graphicImage"
                                src="" 
                                alt="{{ __('messages.events_graphic') }}"
                                class="w-full h-auto rounded-lg max-w-full"
                                style="max-height: 70vh; object-fit: contain;"
                            />
                        </div>
                    </div>
                </div>

                <!-- Right Column - Event Text -->
                <div class="space-y-4 flex flex-col">
                    <div class="flex justify-between items-center">
                        <button 
                            onclick="copyToClipboard(document.getElementById('eventText').value, 'copyTextBtn')"
                            id="copyTextBtn"
                            class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-md transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            {{ __('messages.copy_text') }}
                        </button>
                    </div>
                    
                    <!-- Text Display -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-gray-900/50 p-4 flex-1">
                        <textarea 
                            id="eventText"
                            readonly 
                            class="w-full h-full p-4 border border-gray-200 dark:border-gray-700 rounded-lg resize-none font-mono text-sm leading-relaxed whitespace-pre-wrap overflow-y-auto bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                            style="direction: rtl; text-align: right; font-family: 'Courier New', monospace; min-height: 400px;"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-8 flex justify-start">
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 dark:bg-gray-700 hover:bg-gray-600 dark:hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('messages.back_to_schedule') }}
                </a>
            </div>
        </div>
    </div>
</x-app-admin-layout>
