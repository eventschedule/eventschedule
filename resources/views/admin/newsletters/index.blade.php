<x-app-admin-layout>
    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'newsletters'])

        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.admin_newsletters') }}</h2>
                <div class="flex gap-3">
                    <x-secondary-link href="{{ route('admin.newsletters.segments') }}">
                        {{ __('messages.segments') }}
                    </x-secondary-link>
                    <x-brand-link href="{{ route('admin.newsletters.create') }}">
                        {{ __('messages.create_admin_newsletter') }}
                    </x-brand-link>
                </div>
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

            @if ($newsletters->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.subject') }}</th>
                            <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.status') }}</th>
                            <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.sent') }}</th>
                            <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.open_rate') }}</th>
                            <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.click_rate') }}</th>
                            <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('messages.sent_date') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($newsletters as $newsletter)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                <a href="{{ in_array($newsletter->status, ['sent', 'sending']) ? route('admin.newsletters.stats', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) : route('admin.newsletters.edit', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}"
                                    class="hover:text-[#4E81FA]">
                                    {{ $newsletter->subject }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200',
                                        'scheduled' => 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300',
                                        'sending' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300',
                                        'sent' => 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$newsletter->status] ?? $statusColors['draft'] }}">
                                    {{ __('messages.newsletter_status_' . $newsletter->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ number_format($newsletter->sent_count) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $newsletter->sent_count > 0 ? round(($newsletter->open_count / $newsletter->sent_count) * 100, 1) . '%' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $newsletter->sent_count > 0 ? round(($newsletter->click_count / $newsletter->sent_count) * 100, 1) . '%' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $newsletter->sent_at ? $newsletter->sent_at->format('M j, Y') : $newsletter->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm {{ is_rtl() ? 'text-left' : 'text-right' }}">
                                <div class="flex gap-2 {{ is_rtl() ? 'justify-start' : 'justify-end' }}">
                                    @if ($newsletter->status === 'draft' || $newsletter->status === 'scheduled')
                                    <a href="{{ route('admin.newsletters.edit', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}"
                                        class="text-[#4E81FA] hover:text-blue-800">{{ __('messages.edit') }}</a>
                                    @endif
                                    @if (in_array($newsletter->status, ['sent', 'sending']))
                                    <a href="{{ route('admin.newsletters.stats', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}"
                                        class="text-[#4E81FA] hover:text-blue-800">{{ __('messages.newsletter_stats') }}</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.newsletters.clone', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">{{ __('messages.clone') }}</button>
                                    </form>
                                    @if ($newsletter->status === 'draft')
                                    <form method="POST" action="{{ route('admin.newsletters.delete', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)]) }}" class="inline js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">{{ __('messages.delete') }}</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $newsletters->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 8L12 13L4 8V6L12 11L20 6M20 4H4C2.89 4 2 4.89 2 6V18C2 19.1 2.89 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.89 21.1 4 20 4Z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_newsletters') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_newsletters_description') }}</p>
                <div class="mt-6">
                    <a href="{{ route('admin.newsletters.create') }}"
                        class="inline-flex items-center rounded-md bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-600">
                        {{ __('messages.create_admin_newsletter') }}
                    </a>
                </div>
            </div>
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
