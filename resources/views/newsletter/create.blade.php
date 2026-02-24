<x-app-admin-layout>
    <div class="max-w-[1400px] mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.create_newsletter') }}</h2>
            <a href="{{ route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </a>
        </div>

        @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
        @endif

        @if ($role->newsletterLimit() !== null)
        @php
            $newsletterLimit = $role->newsletterLimit();
            $newsletterUsed = $role->newslettersSentThisMonth();
        @endphp
        <div class="mb-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <span>{{ __('messages.newsletters_used', ['used' => $newsletterUsed, 'limit' => $newsletterLimit]) }}</span>
            @if (config('cashier.key') && $role->actualPlanTier() !== 'enterprise' && $newsletterLimit < 1000)
            <a href="{{ route('role.subscribe', ['subdomain' => $role->subdomain]) }}" class="text-[#4E81FA] hover:underline text-xs font-medium">
                {{ __('messages.newsletter_upgrade_plan') }}
            </a>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('newsletter.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
            @csrf
            @php
                $newsletter = new \App\Models\Newsletter(['template' => $defaultTemplate, 'style_settings' => $defaultStyleSettings, 'segment_ids' => $defaultSegmentIds]);
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
