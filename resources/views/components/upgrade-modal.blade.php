@props(['name', 'tier' => 'pro', 'subdomain', 'docsUrl' => null])

@if (config('app.hosted'))
<x-modal :name="$name" maxWidth="sm">
    <div class="p-6 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4">
            <svg class="h-6 w-6 text-[#4E81FA]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10A2,2 0 0,1 6,8H15V6A3,3 0 0,0 12,3A3,3 0 0,0 9,6H7A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,17A2,2 0 0,0 14,15A2,2 0 0,0 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17Z" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ $tier === 'enterprise' ? __('messages.upgrade_feature_title_enterprise') : __('messages.upgrade_feature_title_pro') }}
        </h3>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            {{ $slot }}
        </p>

        @if ($docsUrl)
        <p class="text-sm mb-4">
            <a href="{{ $docsUrl }}" target="_blank" class="text-[#4E81FA] hover:underline">{{ __('messages.learn_more') }} &rarr;</a>
        </p>
        @else
        <div class="mb-2"></div>
        @endif

        <div class="flex flex-row gap-3">
            <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.cancel') }}
            </button>
            <a href="{{ route('role.subscribe', ['subdomain' => $subdomain, 'tier' => $tier]) }}" target="_blank"
               class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white shadow-sm transition-all duration-200 hover:bg-[#3D6FE8] focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.upgrade') }}
            </a>
        </div>
    </div>
</x-modal>
@endif
