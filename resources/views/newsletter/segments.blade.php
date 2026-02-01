<x-app-admin-layout>
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.segments') }}</h2>
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

        {{-- Existing Segments --}}
        @if ($segments->count())
        <div class="space-y-4 mb-8">
            @foreach ($segments as $segment)
            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $segment->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('messages.type') }}: {{ $segment->type === 'all_followers' ? __('messages.all_followers') : ($segment->type === 'ticket_buyers' ? __('messages.ticket_buyers') : ($segment->type === 'manual' ? __('messages.manual') : __('messages.group'))) }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.recipients') }}: {{ number_format($segment->recipient_count) }}
                        </p>
                        @if ($segment->type === 'manual')
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.manual_entries') }}: {{ $segment->segment_users_count }}
                        </p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('newsletter.segment.delete', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}"
                        onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">{{ __('messages.delete') }}</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Create New Segment --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.create_segment') }}</h3>

            <form method="POST" action="{{ route('newsletter.segment.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}" x-data="{ segmentType: 'all_followers' }">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="segment_name" :value="__('messages.name')" />
                        <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label :value="__('messages.type')" />
                        <select name="type" x-model="segmentType" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                            <option value="all_followers">{{ __('messages.all_followers') }}</option>
                            <option value="ticket_buyers">{{ __('messages.ticket_buyers') }}</option>
                            <option value="manual">{{ __('messages.manual') }}</option>
                        </select>
                    </div>

                    <div x-show="segmentType === 'manual'" x-cloak>
                        <x-input-label :value="__('messages.email_list')" />
                        <textarea name="emails" rows="6"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                            placeholder="{{ __('messages.email_list_placeholder') }}"></textarea>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.email_list_help') }}</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600">
                            {{ __('messages.create_segment') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-admin-layout>
