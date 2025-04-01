<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
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
                <input type="checkbox" name="enable_api" value="1" id="enable_api" class="rounded border-gray-300" 
                       {{ auth()->user()->api_key ? 'checked' : '' }}>
                <label for="enable_api" class="ml-2 text-sm text-gray-600">
                    {{ __('Enable API Access') }}
                </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('Disabling and re-enabling will generate a new API key.') }}
            </p>
        </div>

        @if(auth()->user()->api_key)
            <div class="mt-4">
                <label class="block font-medium text-sm text-gray-700">
                    {{ __('API Key') }}
                </label>
                <div class="mt-1">
                    <input type="text" id="api_key" 
                           value="{{ session('show_new_api_key') ? auth()->user()->api_key : str_repeat('*', 28) . substr(auth()->user()->api_key, -4) }}" 
                           class="block w-full border-gray-300 rounded-md shadow-sm" 
                           readonly>
                </div>
                @if(session('show_new_api_key'))
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Make sure to copy your API key now. You won\'t be able to see it in full again.') }}
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                {{ __('Save') }}
            </button>

            @if (session('success'))
                <p class="text-sm text-gray-600">{{ session('success') }}</p>
            @endif
        </div>
    </form>
</section> 