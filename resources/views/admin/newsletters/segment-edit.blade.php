<x-app-admin-layout>
    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'newsletters'])

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.edit_segment') }}</h2>
            <a href="{{ route('admin.newsletters.segments') }}"
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
            <form method="POST" action="{{ route('admin.newsletters.segment.update', ['hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <x-input-label for="segment_name" :value="__('messages.name')" />
                        <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" :value="$segment->name" required />
                    </div>

                    <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ __('messages.type') }}:
                            @if ($segment->type === 'all_users')
                                {{ __('messages.all_platform_users') }}
                            @elseif ($segment->type === 'plan_tier')
                                {{ __('messages.plan_tier') }}
                            @elseif ($segment->type === 'signup_date')
                                {{ __('messages.signup_date') }}
                            @elseif ($segment->type === 'admins')
                                {{ __('messages.admins') }}
                            @elseif ($segment->type === 'manual')
                                {{ __('messages.manual') }}
                            @else
                                {{ $segment->type }}
                            @endif
                        </span>
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
            <form method="POST" action="{{ route('admin.newsletters.segment.user.store', ['hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}"
                id="add-user-form" class="relative">
                @csrf
                <input type="hidden" name="user_id" id="selected-user-id">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <x-text-input type="text" id="user-search-input" class="block w-full" :placeholder="__('messages.search_users')" autocomplete="off" />
                        <div id="user-search-results" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto hidden">
                        </div>
                    </div>
                    <button type="submit" id="add-user-btn" disabled class="inline-flex items-center justify-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600 whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('messages.add_subscriber') }}
                    </button>
                </div>
            </form>
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
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($subscriberList as $subscriber)
                        @if ($segment->type === 'manual')
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $subscriber->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $subscriber->created_at?->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-right">
                                <form method="POST" action="{{ route('admin.newsletters.segment.user.delete', ['hash' => \App\Utils\UrlUtils::encodeId($segment->id), 'userHash' => \App\Utils\UrlUtils::encodeId($subscriber->id)]) }}"
                                    class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @else
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
    </div>

    <script {!! nonce_attr() !!}>
        // Confirm delete forms
        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.js-confirm-form');
            if (form) {
                if (!confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });

        @if ($segment->type === 'manual')
        // User search autocomplete
        (function() {
            const searchInput = document.getElementById('user-search-input');
            const resultsContainer = document.getElementById('user-search-results');
            const userIdInput = document.getElementById('selected-user-id');
            const addBtn = document.getElementById('add-user-btn');
            let searchTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                const q = this.value.trim();

                // Reset selection when user types
                userIdInput.value = '';
                addBtn.disabled = true;

                if (q.length < 2) {
                    resultsContainer.classList.add('hidden');
                    return;
                }

                searchTimer = setTimeout(async function() {
                    try {
                        const res = await fetch('{{ route("admin.users.search") }}?q=' + encodeURIComponent(q));
                        const data = await res.json();

                        if (data.length > 0) {
                            resultsContainer.innerHTML = '';
                            data.forEach(function(user) {
                                const div = document.createElement('div');
                                div.className = 'px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-sm';
                                div.innerHTML = '<span class="text-gray-900 dark:text-gray-100">' + escapeHtml(user.name || '') + '</span>' +
                                    '<span class="text-gray-500 dark:text-gray-400 ml-2">' + escapeHtml(user.email) + '</span>';
                                div.addEventListener('click', function() {
                                    userIdInput.value = user.id;
                                    searchInput.value = (user.name ? user.name + ' - ' : '') + user.email;
                                    resultsContainer.classList.add('hidden');
                                    addBtn.disabled = false;
                                });
                                resultsContainer.appendChild(div);
                            });
                            resultsContainer.classList.remove('hidden');
                        } else {
                            resultsContainer.classList.add('hidden');
                        }
                    } catch (err) {
                        resultsContainer.classList.add('hidden');
                    }
                }, 300);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.classList.add('hidden');
                }
            });

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        })();
        @endif
    </script>
</x-app-admin-layout>
