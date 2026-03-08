@if($roles->count() > 0)
<div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
        <div class="overflow-hidden shadow ring-1 ring-black/5 md:rounded-lg">
            <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="py-3.5 ps-4 pe-3 sm:ps-6 w-10">
                                <input type="checkbox" id="select-all"
                                    class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            </th>
                            <x-sortable-header column="name" :sortBy="$sortBy" :sortDir="$sortDir" class="py-3.5 ps-4 pe-3 sm:ps-6">{{ __('messages.name') }}</x-sortable-header>
                            <x-sortable-header column="type" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.type') }}</x-sortable-header>
                            <x-sortable-header column="email" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.email') }}</x-sortable-header>
                            <x-sortable-header column="phone" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.phone') }}</x-sortable-header>
                            <x-sortable-header column="website" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.website') }}</x-sortable-header>
                            <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6">
                                <span class="sr-only">{{ __('messages.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach ($roles as $role)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="py-4 ps-4 pe-3 sm:ps-6 w-10">
                                <input type="checkbox" class="row-checkbox h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                                    value="{{ $role->subdomain }}"
                                    data-has-email="{{ $role->email ? 'true' : 'false' }}">
                            </td>
                            <td class="py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 max-w-xs">
                                @if ($role->isClaimed())
                                <a href="{{ $role->getGuestUrl() }}"
                                    target="_blank" class="hover:underline break-words">{{ $role->getDisplayName(false) }}
                                </a>
                                @else
                                <p class="text-sm text-gray-500 break-words">
                                    {{ $role->getDisplayName(false) }}
                                </p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('messages.' . $role->type) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if ($role->show_email)
                                    <a href="mailto:{{ $role->email }}" class="hover:underline">{{ $role->email }}</a>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if ($role->show_phone && $role->phone_verified_at)
                                    <a href="tel:{{ $role->phone }}" class="hover:underline">{{ $role->phone }}</a>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <a href="{{ $role->website }}"
                                    target="_blank" class="hover:underline">{{ App\Utils\UrlUtils::clean($role->website) }}</a>
                            </td>
                            <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6">
                                <div class="relative inline-block" x-data="{
                                    open: false,
                                    positionDropdown() {
                                        if (!this.open) return;
                                        const button = this.$refs.button;
                                        const dropdown = this.$refs.dropdown;
                                        const rect = button.getBoundingClientRect();

                                        dropdown.style.position = 'fixed';
                                        dropdown.style.top = `${rect.bottom + 4}px`;
                                        dropdown.style.zIndex = '1000';

                                        const isRtl = document.documentElement.dir === 'rtl';
                                        if (isRtl) {
                                            dropdown.style.left = `${rect.left}px`;
                                        } else {
                                            dropdown.style.right = `${window.innerWidth - rect.right}px`;
                                        }
                                    }
                                }">
                                    <button @click="open = !open; $nextTick(() => positionDropdown())"
                                            x-ref="button"
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                        {{ __('messages.actions') }}
                                        <svg class="-me-1 ms-2 h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-ref="dropdown"
                                         @click.away="open = false"
                                         class="ap-dropdown w-48 origin-top-right rounded-lg py-1 ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none"
                                         role="menu"
                                         x-cloak
                                         aria-orientation="vertical">

                                        @if($role->isClaimed())
                                        <button @click="open = false; copyFeedUrl('{{ route('feed.ical', ['subdomain' => $role->subdomain]) }}', $el)"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150"
                                                role="menuitem">
                                            <svg class="w-4 h-4 me-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>{{ __('messages.copy_ical_feed') }}</span>
                                        </button>

                                        <button @click="open = false; copyFeedUrl('{{ route('feed.rss', ['subdomain' => $role->subdomain]) }}', $el)"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150"
                                                role="menuitem">
                                            <svg class="w-4 h-4 me-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                            </svg>
                                            <span>{{ __('messages.copy_rss_feed') }}</span>
                                        </button>

                                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                        @endif

                                        <button @click="open = false; if (confirm('{{ __('messages.are_you_sure') }}')) location.href = '{{ route('role.unfollow', ['subdomain' => $role->subdomain]) }}'"
                                                class="flex items-center px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150"
                                                role="menuitem">
                                            <svg class="w-4 h-4 me-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <span>{{ $role->email ? __('messages.unfollow') : __('messages.delete') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@else
<div class="text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.no_following') }}</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.start_following_schedules') }}</p>
</div>
@endif

<script {!! nonce_attr() !!}>
function copyFeedUrl(url, button) {
    navigator.clipboard.writeText(url).then(() => {
        const span = button.querySelector('span');
        const originalText = span.textContent;
        span.textContent = '{{ __('messages.copied') }}';
        const svg = button.querySelector('svg');
        const originalSvg = svg.innerHTML;
        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
        svg.classList.add('text-green-500');
        setTimeout(() => {
            span.textContent = originalText;
            svg.innerHTML = originalSvg;
            svg.classList.remove('text-green-500');
        }, 2000);
    }).catch(() => {});
}
</script>
