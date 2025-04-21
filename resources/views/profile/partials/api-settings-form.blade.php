<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('API Settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Manage your API access settings.') }}
            <a href="{{ route('api.documentation') }}" class="text-indigo-600 hover:text-indigo-900">
                {{ __('View API Documentation') }}
            </a>
        </p>
    </header>

    <form method="post" action="{{ route('api-settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <div class="flex items-center">
                <input type="checkbox" name="enable_api" value="1" id="enable_api" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:ring-offset-gray-900" 
                       {{ auth()->user()->api_key ? 'checked' : '' }}>
                <label for="enable_api" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Enable API Access') }}
                </label>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Disabling and re-enabling will generate a new API key.') }}
            </p>
        </div>

        @if(auth()->user()->api_key)
            <div class="mt-4">
                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                    {{ __('API Key') }}
                </label>
                <div class="mt-1 relative">
                    <input type="text" id="api_key" 
                           value="{{ session('show_new_api_key') ? auth()->user()->api_key : substr(auth()->user()->api_key, 0, 6) . str_repeat('•', 26) }}" 
                           class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm {{ session('show_new_api_key') ? 'rounded-r-none pr-12' : '' }} font-mono" 
                           readonly>
                    @if(session('show_new_api_key'))
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            <div class="h-full w-px bg-gray-300 dark:bg-gray-600"></div>
                            <button type="button" 
                                    onclick="copyApiKey()" 
                                    class="px-3 border border-l-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-r-md flex items-center justify-center group h-full"
                                    title="{{ __('Copy to clipboard') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     class="h-5 w-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" 
                                     fill="none" 
                                     viewBox="0 0 24 24" 
                                     stroke="currentColor">
                                    <path stroke-linecap="round" 
                                          stroke-linejoin="round" 
                                          stroke-width="2" 
                                          d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                <span id="copy-feedback" 
                                      class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded opacity-0 transition-opacity">
                                    {{ __('Copied!') }}
                                </span>
                            </button>
                        </div>
                    @endif
                </div>
                @if(session('show_new_api_key'))
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Make sure to copy your API key now. You won\'t be able to see it in full again.') }}
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md hover:bg-gray-700 dark:hover:bg-gray-300">
                {{ __('Save') }}
            </button>

            @if (session('success'))
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ session('success') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
function copyApiKey() {
    const apiKeyInput = document.getElementById('api_key');
    apiKeyInput.select();
    document.execCommand('copy');
    
    const feedback = document.getElementById('copy-feedback');
    feedback.classList.remove('opacity-0');
    
    setTimeout(() => {
        feedback.classList.add('opacity-0');
    }, 2000);
}
</script> 