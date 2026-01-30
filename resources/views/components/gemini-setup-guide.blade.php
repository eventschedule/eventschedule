@if (! config('services.google.gemini_key'))
<div class="bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 dark:from-amber-900/20 dark:via-orange-900/20 dark:to-yellow-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6 shadow-sm">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                {{ __('messages.setup_required_gemini') }}
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300">
                    {{ __('messages.required') }}
                </span>
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.gemini_setup_description') }}
            </p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        {{-- Step 1 --}}
        <div class="relative bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="absolute -top-3 -left-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white text-xs font-bold shadow">1</span>
            </div>
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mt-1">{{ __('messages.get_api_key') }}</h4>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.get_api_key_description') }}
            </p>
            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener"
               class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300">
                {{ __('messages.open_ai_studio') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
            </a>
        </div>

        {{-- Step 2 --}}
        <div class="relative bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="absolute -top-3 -left-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white text-xs font-bold shadow">2</span>
            </div>
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mt-1">{{ __('messages.add_to_environment') }}</h4>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.add_to_environment_description', ['file' => '.env']) }}
            </p>
            <div class="mt-3 bg-gray-900 dark:bg-gray-950 rounded-md p-2 overflow-x-auto">
                <code class="text-xs text-green-400 whitespace-nowrap">GEMINI_API_KEY=your_api_key_here</code>
            </div>
        </div>

        {{-- Step 3 --}}
        <div class="relative bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="absolute -top-3 -left-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white text-xs font-bold shadow">3</span>
            </div>
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mt-1">{{ __('messages.restart_application') }}</h4>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.restart_application_description') }}
            </p>
            <div class="mt-3 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('messages.free_tier_available') }}
            </div>
        </div>
    </div>
</div>
@endif
