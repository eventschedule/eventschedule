<x-app-admin-layout>
    <div class="max-w-[1400px] mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.create_newsletter') }}</h2>
            <a href="{{ route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                {{ __('messages.back') }}
            </a>
        </div>

        @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('newsletter.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
            @csrf
            @php
                $newsletter = new \App\Models\Newsletter(['template' => 'modern', 'style_settings' => \App\Models\Newsletter::defaultStyleSettingsForRole($role)]);
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
