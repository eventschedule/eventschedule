@if ($newsletter->ab_test_id)
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <p class="text-sm text-blue-800 dark:text-blue-200">
            {{ __('messages.ab_test_variant') }}: <strong>{{ $newsletter->ab_variant }}</strong>
        </p>
        @if ($newsletter->abTest && $newsletter->abTest->winner_variant)
        <p class="text-sm text-green-700 dark:text-green-300 mt-2">
            {{ __('messages.winner') }}: {{ $newsletter->abTest->winner_variant }}
        </p>
        @endif
    </div>
@else
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('messages.ab_test_description') }}</p>
    <div x-data="{ showAbForm: false }">
        <button type="button" @click="showAbForm = !showAbForm"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
            {{ __('messages.create_ab_test') }}
        </button>

        <div x-show="showAbForm" x-cloak class="mt-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
            <form method="POST" action="{{ route('newsletter.ab_test', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label :value="__('messages.test_field')" />
                        <select name="test_field" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                            <option value="subject">{{ __('messages.subject') }}</option>
                            <option value="content_above">{{ __('messages.content_above_events') }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label :value="__('messages.sample_percentage')" />
                            <x-text-input type="number" name="sample_percentage" value="20" min="5" max="50" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label :value="__('messages.wait_hours')" />
                            <x-text-input type="number" name="winner_wait_hours" value="4" min="1" max="72" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div>
                        <x-input-label :value="__('messages.winner_criteria')" />
                        <select name="winner_criteria" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                            <option value="open_rate">{{ __('messages.open_rate') }}</option>
                            <option value="click_rate">{{ __('messages.click_rate') }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600">
                            {{ __('messages.create_ab_test') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
