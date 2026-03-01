<x-app-admin-layout>
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.edit_segment') }}</h2>
            <a href="{{ route('newsletter.segments', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
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

        @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Segment Info --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6 mb-6">
            <form method="POST" action="{{ route('newsletter.segment.update', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <x-input-label for="segment_name" :value="__('messages.name')" />
                        <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" :value="$segment->name" required />
                    </div>

                    <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ __('messages.type') }}: {{ $segment->type === 'all_followers' ? __('messages.all_followers') : ($segment->type === 'ticket_buyers' ? __('messages.ticket_buyers') : ($segment->type === 'manual' ? __('messages.manual') : __('messages.group'))) }}</span>
                        <span>{{ __('messages.recipients') }}: {{ number_format($recipientCount) }}</span>
                        <span>{{ __('messages.created') }}: {{ $segment->created_at->format('M j, Y') }}</span>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600">
                            {{ __('messages.save_changes') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Add Subscriber (manual segments only) --}}
        @if ($segment->type === 'manual')
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.add_subscriber') }}</h3>
            <form method="POST" action="{{ route('newsletter.segment.user.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}"
                class="flex flex-col sm:flex-row gap-3">
                @csrf
                <x-text-input name="name" type="text" class="flex-1" :placeholder="__('messages.name')" />
                <x-text-input name="email" type="email" class="flex-1" :placeholder="__('messages.email')" required />
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600 whitespace-nowrap">
                    {{ __('messages.add_subscriber') }}
                </button>
            </form>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                <a href="{{ route('newsletter.import', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}" class="text-[#4E81FA] hover:text-[#3D6FE8]">
                    {{ __('messages.import_in_bulk') }}
                </a>
            </p>
        </div>
        @endif

        {{-- Subscribers Table --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ __('messages.subscribers') }} ({{ number_format($recipientCount) }})
            </h3>

            @php
                $subscriberList = $segment->type === 'manual' ? $subscribers->items() : $subscribers;
            @endphp

            @if (count($subscriberList) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.email') }}</th>
                            @if ($segment->type === 'manual')
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.date_added') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-data="{ editingId: null }">
                        @foreach ($subscriberList as $subscriber)
                        @if ($segment->type === 'manual')
                        @php $encodedId = \App\Utils\UrlUtils::encodeId($subscriber->id); @endphp
                        {{-- Display row --}}
                        <tr x-show="editingId !== '{{ $encodedId }}'">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $subscriber->created_at?->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-right">
                                <div class="flex gap-3 justify-end">
                                    <button type="button" @click="editingId = '{{ $encodedId }}'" class="text-[#4E81FA] hover:text-[#3D6FE8]">{{ __('messages.edit') }}</button>
                                    <form method="POST" action="{{ route('newsletter.segment.user.delete', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id), 'userHash' => $encodedId]) }}"
                                        class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">{{ __('messages.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        {{-- Edit row --}}
                        <tr x-show="editingId === '{{ $encodedId }}'" x-cloak>
                            <td colspan="4" class="px-4 py-3">
                                <form method="POST" action="{{ route('newsletter.segment.user.update', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id), 'userHash' => $encodedId]) }}"
                                    class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                                    @csrf
                                    @method('PUT')
                                    <x-text-input name="name" type="text" class="flex-1 text-sm" :value="$subscriber->name" :placeholder="__('messages.name')" />
                                    <x-text-input name="email" type="email" class="flex-1 text-sm" :value="$subscriber->email" :placeholder="__('messages.email')" required />
                                    <div class="flex gap-2">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-xs text-white hover:bg-blue-600">
                                            {{ __('messages.save_changes') }}
                                        </button>
                                        <button type="button" @click="editingId = null" class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600">
                                            {{ __('messages.cancel') }}
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @else
                        {{-- Read-only row for non-manual segments --}}
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($segment->type === 'manual' && $subscribers instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $subscribers->links() }}
            </div>
            @endif

            @if ($segment->type !== 'manual' && $recipientCount > 50)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                {{ __('messages.showing_first_of', ['count' => number_format($recipientCount)]) }}
            </p>
            @endif
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_subscribers') }}</p>
            @endif
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
