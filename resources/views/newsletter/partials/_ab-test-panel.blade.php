{{-- The A/B test of a newsletter, printed into the builder's Settings tab (the builder asks for
     this HTML and shows it as is). Its button opens the form with data-toggle-target, which the
     layout hears. --}}
@if ($newsletter->ab_test_id)
    <x-page-notice tone="info">
        {{ __('messages.ab_test_variant') }}: <strong>{{ $newsletter->ab_variant }}</strong>
        @if ($newsletter->abTest && $newsletter->abTest->winner_variant)
        <p class="mt-1">{{ __('messages.winner') }}: {{ $newsletter->abTest->winner_variant }}</p>
        @endif
    </x-page-notice>
@else
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('messages.ab_test_description') }}</p>
    <div>
        <button type="button" data-toggle-target="#ab-test-form" class="page-tool">
            {{ __('messages.create_ab_test') }}
        </button>

        <div id="ab-test-form" class="hidden news-ab-form">
            <form method="POST" action="{{ route('newsletter.ab_test', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="ab_test_field" :value="__('messages.test_field')" />
                        <select id="ab_test_field" name="test_field" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <option value="subject">{{ __('messages.subject') }}</option>
                            <option value="blocks">{{ __('messages.content_above_events') }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="ab_sample_percentage" :value="__('messages.sample_percentage')" />
                            <x-text-input id="ab_sample_percentage" type="number" name="sample_percentage" value="20" min="5" max="50" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="ab_winner_wait_hours" :value="__('messages.wait_hours')" />
                            <x-text-input id="ab_winner_wait_hours" type="number" name="winner_wait_hours" value="4" min="1" max="72" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="ab_winner_criteria" :value="__('messages.winner_criteria')" />
                        <select id="ab_winner_criteria" name="winner_criteria" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <option value="open_rate">{{ __('messages.open_rate') }}</option>
                            <option value="click_rate">{{ __('messages.click_rate') }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <x-brand-button type="submit" size="sm">{{ __('messages.create_ab_test') }}</x-brand-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
