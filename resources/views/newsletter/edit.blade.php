<x-app-admin-layout>
    <div class="max-w-[1400px] mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.edit_newsletter') }}</h2>
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

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        @if ($newsletter->status === 'scheduled')
        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-md">
            <div class="flex justify-between items-center">
                <p class="text-yellow-700 dark:text-yellow-300">
                    {{ __('messages.scheduled_for') }}: {{ $newsletter->scheduled_at->format('M j, Y g:i A') }}
                </p>
                <form method="POST" action="{{ route('newsletter.cancel', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-yellow-700 dark:text-yellow-300 underline hover:text-yellow-900 dark:hover:text-yellow-100">
                        {{ __('messages.cancel_schedule') }}
                    </button>
                </form>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('newsletter.update', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}">
            @csrf
            @method('PUT')
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
