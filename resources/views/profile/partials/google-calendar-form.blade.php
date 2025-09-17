<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Google Calendar Integration') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Connect your Google Calendar to automatically sync your events.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        @if (auth()->user()->google_token)
            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ __('Google Calendar Connected') }}
                    </span>
                </div>
                <a href="{{ route('google.calendar.disconnect') }}" 
                   class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                    {{ __('Disconnect') }}
                </a>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                        {{ __('Sync Options') }}
                    </h3>
                    
                    <button type="button" 
                            onclick="syncAllEvents()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ __('Sync All Events') }}
                    </button>
                </div>

                <div id="sync-status" class="hidden">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-blue-800 dark:text-blue-200">{{ __('Syncing events...') }}</span>
                        </div>
                    </div>
                </div>

                <div id="sync-results" class="hidden">
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="text-sm text-green-800 dark:text-green-200">
                            <div id="sync-message"></div>
                            <div id="sync-details" class="mt-1 text-xs"></div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Google Calendar Not Connected') }}
                    </span>
                </div>
                <a href="{{ route('google.calendar.redirect') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    {{ __('Connect Google Calendar') }}
                </a>
            </div>
        @endif
    </div>
</section>

<script>
function syncAllEvents() {
    const statusDiv = document.getElementById('sync-status');
    const resultsDiv = document.getElementById('sync-results');
    
    // Show loading status
    statusDiv.classList.remove('hidden');
    resultsDiv.classList.add('hidden');
    
    fetch('{{ route("google.calendar.sync_events") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        statusDiv.classList.add('hidden');
        resultsDiv.classList.remove('hidden');
        
        if (data.error) {
            document.getElementById('sync-message').textContent = 'Error: ' + data.error;
            resultsDiv.querySelector('.bg-green-50').classList.remove('bg-green-50', 'border-green-200');
            resultsDiv.querySelector('.bg-green-50').classList.add('bg-red-50', 'border-red-200');
            resultsDiv.querySelector('.text-green-800').classList.remove('text-green-800');
            resultsDiv.querySelector('.text-green-800').classList.add('text-red-800');
        } else {
            document.getElementById('sync-message').textContent = data.message;
            if (data.results) {
                const details = `Created: ${data.results.created}, Updated: ${data.results.updated}, Errors: ${data.results.errors}`;
                document.getElementById('sync-details').textContent = details;
            }
        }
    })
    .catch(error => {
        statusDiv.classList.add('hidden');
        resultsDiv.classList.remove('hidden');
        document.getElementById('sync-message').textContent = 'Error: ' + error.message;
        resultsDiv.querySelector('.bg-green-50').classList.remove('bg-green-50', 'border-green-200');
        resultsDiv.querySelector('.bg-green-50').classList.add('bg-red-50', 'border-red-200');
        resultsDiv.querySelector('.text-green-800').classList.remove('text-green-800');
        resultsDiv.querySelector('.text-green-800').classList.add('text-red-800');
    });
}
</script>
