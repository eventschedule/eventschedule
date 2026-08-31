<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
    </div>
    <div class="mt-6 sm:ms-16 sm:mt-0 sm:flex-none">
        <x-brand-link href="{{ route('role.qr_code', ['subdomain' => $role->subdomain]) }}">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M3,11H5V13H3V11M11,5H13V9H11V5M9,11H13V15H11V13H9V11M15,11H17V13H19V11H21V13H19V15H21V19H19V21H17V19H13V21H11V17H15V15H17V13H15V11M19,19V15H17V19H19M15,3H21V9H15V3M17,5V7H19V5H17M3,3H9V9H3V3M5,5V7H7V5H5M3,15H9V21H3V15M5,17V19H7V17H5Z" />
            </svg>
            {{ __('messages.qr_code') }}
        </x-brand-link>
    </div>
</div>


@php
    $hasSubscribers = $subscribers && $subscribers->total() > 0;
@endphp

@if($followers->isEmpty() && ! $hasSubscribers)

<div class="text-center pt-20">
    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
        aria-hidden="true">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21" />
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_followers') }}</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.share_your_event_schedule_link') }}</p>

    {{-- A link the organizer can actually copy. Until now the only thing this page offered was a
         QR code DOWNLOAD, with nowhere to copy the URL as text - so the one action the empty state
         asks for had no affordance. Deep-links straight to the subscribe form. --}}
    @php
        $subscribeShareUrl = ($role->getGuestUrl(true) ?: $role->getGuestUrl());
        $subscribeShareUrl .= (str_contains($subscribeShareUrl, '?') ? '&' : '?').'subscribe=1';
    @endphp
    <div class="mx-auto mt-6 max-w-xl text-start">
        <label for="subscribe-share-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('messages.audience_share_link') }}
        </label>
        <div class="flex gap-2">
            <input type="text" id="subscribe-share-url" readonly value="{{ $subscribeShareUrl }}"
                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            <button type="button" id="copy-subscribe-link"
                class="inline-flex items-center rounded-lg bg-[var(--brand-button-bg)] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[var(--brand-button-bg-hover)] transition-colors">
                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                <span id="copy-subscribe-text">{{ __('messages.copy_link') }}</span>
            </button>
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.audience_share_link_help') }}</p>
    </div>

    <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('copy-subscribe-link');
        var input = document.getElementById('subscribe-share-url');
        var label = document.getElementById('copy-subscribe-text');
        if (!btn || !input || !label) return;
        btn.addEventListener('click', function () {
            input.select();
            navigator.clipboard.writeText(input.value).then(function () {
                var original = label.textContent;
                label.textContent = @json(__('messages.copied'), JSON_UNESCAPED_UNICODE);
                setTimeout(function () { label.textContent = original; }, 2000);
            }).catch(function () {});
        });
    });
    </script>
    <div class="mt-3">
        @if ($role->custom_domain)
        <x-link href="{{ $role->custom_domain }}" target="_blank">
            {{ \App\Utils\UrlUtils::clean($role->custom_domain) }}
        </x-link>
        @else
        <x-link href="{{ $role->getGuestUrl() }}" target="_blank">
            @if (config('app.hosted'))
                {{ $role->subdomain . '.eventschedule.com' }}
            @else
                {{ config('app.url') . '/' . $role->subdomain }}
            @endif
        </x-link>
        @endif
    </div>
</div>

@else

{{--
    Account followers. Guarded on isNotEmpty() because the branch above only takes the empty-state
    branch when BOTH lists are empty - so a schedule whose whole audience is account-less (the
    likeliest state for any schedule using the subscribe panel) used to render this table with its
    four column headers and no rows, unlabelled, directly above "Email subscribers".
--}}
@if ($followers->isNotEmpty())
<div class="mt-8">
    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
        {{ __('messages.followers') }} ({{ number_format($followersWithRoles->total()) }})
    </h3>

<div class="mt-4 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black/5 dark:ring-gray-700 md:rounded-lg">
                <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <x-sortable-header column="name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'" class="py-3.5 ps-4 pe-3 sm:ps-6">{{ __('messages.name') }}</x-sortable-header>
                                <x-sortable-header column="email" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.email') }}</x-sortable-header>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.schedule') }}
                                </th>
                                <x-sortable-header column="pivot_created_at" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.date') }}</x-sortable-header>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($followersWithRoles as $follower)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                    @if($follower->name)
                                        {{ $follower->name }}
                                    @else
                                        <span class="italic text-gray-400 dark:text-gray-500">{{ __('messages.no_name') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $follower->email }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($follower->roles->isNotEmpty())
                                        @php
                                            $firstRole = $follower->roles->first();
                                        @endphp                 
                                        @if ($firstRole->isClaimed())
                                            <x-link href="{{ $firstRole->getGuestUrl() }}" target="_blank">
                                                {{ $firstRole->name }}
                                            </x-link>
                                        @endif
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $follower->pivot->created_at->format(get_use_24_hour_time($role) ? 'M jS, Y • H:i' : 'M jS, Y • g:i A') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($followersWithRoles->hasPages())
<div class="mt-6 flex items-center justify-between">
    <div class="flex-1 flex justify-between sm:hidden">
        @if ($followersWithRoles->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default leading-5 rounded-lg">
                {{ __('messages.previous') }}
            </span>
        @else
            <a href="{{ $followersWithRoles->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 leading-5 rounded-lg hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:ring ring-gray-300 dark:ring-gray-600 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] active:bg-gray-100 dark:active:bg-gray-700 active:text-gray-700 dark:active:text-gray-300 transition ease-in-out duration-150">
                {{ __('messages.previous') }}
            </a>
        @endif

        @if ($followersWithRoles->hasMorePages())
            <a href="{{ $followersWithRoles->nextPageUrl() }}" class="ms-3 relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 leading-5 rounded-lg hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:ring ring-gray-300 dark:ring-gray-600 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] active:bg-gray-100 dark:active:bg-gray-700 active:text-gray-700 dark:active:text-gray-300 transition ease-in-out duration-150">
                {{ __('messages.next') }}
            </a>
        @else
            <span class="ms-3 relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default leading-5 rounded-lg">
                {{ __('messages.next') }}
            </span>
        @endif
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
        </div>
        <div>
            {{ $followersWithRoles->links() }}
        </div>
    </div>
</div>
@endif
</div>
@endif
@endif

@if ($hasSubscribers)
{{--
    Account-less subscribers: people who gave this schedule an email address on the guest portal
    without creating an account. Owner-facing only - these addresses must never appear on a guest
    surface, an embed or public stats.
--}}
@php
    $canManageSubscribers = auth()->user() && auth()->user()->isEditor($role->subdomain);
    $subscriberPending = $subscriberStats['pending'] ?? 0;
@endphp
<div class="mt-10">
    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
        {{ __('messages.all_subscribers') }} ({{ number_format($subscribers->total()) }})
    </h3>

    @if ($subscriberStats)
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ __('messages.subscriber_breakdown', [
            'confirmed' => number_format($subscriberStats['confirmed']),
            'pending' => number_format($subscriberStats['pending']),
            'unsubscribed' => number_format($subscriberStats['unsubscribed']),
        ]) }}
    </p>
    @endif

    @if ($subscriberPending)
    {{-- The pending count and the recipient count on a send legitimately differ, because an
         unconfirmed row is never resolved as a recipient. That was documented only in a source
         comment, which is no help to the owner staring at the discrepancy. --}}
    <div class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <p class="text-sm text-amber-700 dark:text-amber-300">
            {{ __('messages.subscriber_pending_warning', ['count' => number_format($subscriberPending)]) }}
        </p>
    </div>
    @endif

    <div class="mt-4 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black/5 dark:ring-gray-700 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">{{ __('messages.name') }}</th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.email') }}</th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.status') }}</th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.date') }}</th>
                                @if ($canManageSubscribers)
                                <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6"><span class="sr-only">{{ __('messages.delete') }}</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($subscribers as $subscriber)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                    @if ($subscriber->name)
                                        <x-user-text>{{ $subscriber->name }}</x-user-text>
                                    @else
                                        <span class="italic text-gray-400 dark:text-gray-500">{{ __('messages.no_name') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $subscriber->email }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @if ($subscriber->has_unsubscribed)
                                        <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300">{{ __('messages.subscriber_unsubscribed') }}</span>
                                    @elseif ($subscriber->confirmed_at)
                                        <span class="inline-flex items-center rounded-md bg-green-50 dark:bg-green-500/10 px-2 py-1 text-xs font-medium text-green-700 dark:text-green-400">{{ __('messages.subscriber_confirmed') }}</span>
                                    @else
                                        {{-- Never mailed. The amber panel above the table explains
                                             the resulting discrepancy to the owner. --}}
                                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-1 text-xs font-medium text-amber-700 dark:text-amber-400">{{ __('messages.subscriber_pending') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $subscriber->created_at->format(get_use_24_hour_time($role) ? 'M jS, Y • H:i' : 'M jS, Y • g:i A') }}
                                </td>
                                @if ($canManageSubscribers)
                                {{-- isEditor, matching RoleSubscriberController::remove(). viewAdmin
                                     admits isMember, which includes viewers - who used to see this
                                     button on every row and get a bare 403 on click. --}}
                                <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6">
                                    <form method="POST" action="{{ route('role.subscribers.remove', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($subscriber->id)]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="{{ __('messages.are_you_sure') }}"
                                            class="text-sm text-red-600 dark:text-red-400 hover:underline">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $subscribers->links() }}
    </div>
</div>
@endif
