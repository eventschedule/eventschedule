<div class="mt-8 flow-root">
    @if($entries->count() > 0)
    <!-- Desktop Table View -->
    <div class="hidden md:block -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">
                                {{ __('messages.name') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.email') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.event') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.date') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.status') }}
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.created_at') }}
                            </th>
                            <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6">
                                <span class="sr-only">{{ __('messages.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-[#1e1e1e]">
                        @foreach($entries as $entry)
                        <tr>
                            <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                {{ $entry->name }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $entry->email }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $entry->event?->name }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $entry->event_date }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @if($entry->status === 'waiting')
                                    <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">{{ __('messages.waiting') }}</span>
                                @elseif($entry->status === 'notified')
                                    <span class="inline-flex items-center rounded-full bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-300">{{ __('messages.notified') }}</span>
                                @elseif($entry->status === 'purchased')
                                    <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300">{{ __('messages.purchased') }}</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('messages.expired') }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $entry->created_at->format('M j, Y g:ia') }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6">
                                <button type="button" onclick="handleWaitlistRemove('{{ \App\Utils\UrlUtils::encodeId($entry->id) }}')"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    {{ __('messages.remove') }}
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="md:hidden space-y-4">
        @foreach($entries as $entry)
        <div class="bg-white dark:bg-[#1e1e1e] rounded-lg shadow-sm border border-gray-200 dark:border-[#2d2d30] p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $entry->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $entry->email }}</p>
                </div>
                @if($entry->status === 'waiting')
                    <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">{{ __('messages.waiting') }}</span>
                @elseif($entry->status === 'notified')
                    <span class="inline-flex items-center rounded-full bg-yellow-50 dark:bg-yellow-900/30 px-2 py-1 text-xs font-medium text-yellow-700 dark:text-yellow-300">{{ __('messages.notified') }}</span>
                @elseif($entry->status === 'purchased')
                    <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-300">{{ __('messages.purchased') }}</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-gray-50 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('messages.expired') }}</span>
                @endif
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $entry->event?->name }} - {{ $entry->event_date }}</p>
            <div class="flex justify-between items-center mt-3">
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $entry->created_at->format('M j, Y g:ia') }}</p>
                <button type="button" onclick="handleWaitlistRemove('{{ \App\Utils\UrlUtils::encodeId($entry->id) }}')"
                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                    {{ __('messages.remove') }}
                </button>
            </div>
        </div>
        @endforeach
    </div>

    @if($entries instanceof \Illuminate\Pagination\LengthAwarePaginator && $entries->hasPages())
    <div class="mt-4">
        {{ $entries->links() }}
    </div>
    @endif
    @else
    <div class="text-center py-12">
        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.waitlist_empty') }}</p>
    </div>
    @endif
</div>
