<x-app-admin-layout>
    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'newsletters'])

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.segments') }}</h2>
            <a href="{{ route('admin.newsletters.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
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
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 min-w-0">{{ $segment->name }}</h3>
                    <div class="shrink-0 space-x-3">
                        <a href="{{ route('admin.newsletters.segment.edit', ['hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}" class="text-[#4E81FA] hover:text-blue-700 text-sm">{{ __('messages.edit') }}</a>
                        <form method="POST" action="{{ route('admin.newsletters.segment.delete', ['hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}"
                            class="inline js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">{{ __('messages.delete') }}</button>
                        </form>
                    </div>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('messages.type') }}:
                    @if ($segment->type === 'all_users')
                        {{ __('messages.all_platform_users') }}
                    @elseif ($segment->type === 'plan_tier')
                        {{ __('messages.plan_tier') }}{{ !empty($segment->filter_criteria['plan_type']) ? ': ' . $segment->filter_criteria['plan_type'] : '' }}
                    @elseif ($segment->type === 'signup_date')
                        {{ __('messages.signup_date') }}
                    @elseif ($segment->type === 'admins')
                        {{ __('messages.admins') }}
                    @elseif ($segment->type === 'manual')
                        {{ __('messages.manual') }}
                    @else
                        {{ $segment->type }}
                    @endif
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.recipients') }}: {{ number_format($segment->recipient_count) }}
                </p>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Create New Segment --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.create_segment') }}</h3>

            <form method="POST" action="{{ route('admin.newsletters.segment.store') }}" x-data="{ segmentType: 'all_users' }">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="segment_name" :value="__('messages.name')" />
                        <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label :value="__('messages.type')" />
                        <select name="type" x-model="segmentType" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                            <option value="all_users">{{ __('messages.all_platform_users') }}</option>
                            <option value="plan_tier">{{ __('messages.plan_tier') }}</option>
                            <option value="signup_date">{{ __('messages.signup_date') }}</option>
                            <option value="admins">{{ __('messages.admins') }}</option>
                            <option value="manual">{{ __('messages.manual') }}</option>
                        </select>
                    </div>

                    <div x-show="segmentType === 'plan_tier'" x-cloak>
                        <x-input-label :value="__('messages.plan_tier')" />
                        <select name="filter_criteria[plan_type]" x-bind:disabled="segmentType !== 'plan_tier'" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                            <option value="free">Free</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>

                    <div x-show="segmentType === 'signup_date'" x-cloak>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label :value="__('messages.date_from')" />
                                <x-text-input name="filter_criteria[date_from]" type="date" class="mt-1 block w-full" x-bind:disabled="segmentType !== 'signup_date'" />
                            </div>
                            <div>
                                <x-input-label :value="__('messages.date_to')" />
                                <x-text-input name="filter_criteria[date_to]" type="date" class="mt-1 block w-full" x-bind:disabled="segmentType !== 'signup_date'" />
                            </div>
                        </div>
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
    </div>
    <script {!! nonce_attr() !!}>
        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.js-confirm-form');
            if (form) {
                if (!confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });
    </script>
</x-app-admin-layout>
