{{-- The schedules this person follows, as one list (.page-table): a table from a tablet up, a
     stack of rows on a phone. This partial is also the answer to the filter box's request, so it
     holds markup only: the script that wires the rows lives in role/index.

     A contact column is drawn only when some row has something to put in it. All three stood
     empty for most people, pushing the one button of the row a screen's width from its name. --}}
@php
    $shownEmail = fn ($role) => $role->show_email && $role->email;
    $shownPhone = fn ($role) => $role->show_phone && $role->phone_verified_at && $role->phone;
    $hasEmail = $roles->contains($shownEmail);
    $hasPhone = $roles->contains($shownPhone);
    $hasWebsite = $roles->contains(fn ($role) => (bool) $role->website);
    $menuItem = 'group flex items-center w-full px-4 py-2.5 text-sm text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors';
    $menuIcon = 'w-4 h-4 me-2 shrink-0 text-gray-400 dark:text-gray-500';
@endphp

@if ($roles->count() > 0)
<div class="ap-card rounded-xl overflow-hidden">
    <table class="page-table is-hover following-list">
        <thead>
            <tr>
                <th scope="col" class="c-check">
                    <input type="checkbox" id="select-all" aria-label="{{ __('messages.select_all') }}"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                </th>
                <x-page-sort column="name" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.name') }}</x-page-sort>
                @if ($hasEmail)
                <x-page-sort column="email" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.email') }}</x-page-sort>
                @endif
                @if ($hasPhone)
                <x-page-sort column="phone" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.phone') }}</x-page-sort>
                @endif
                @if ($hasWebsite)
                <x-page-sort column="website" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.website') }}</x-page-sort>
                @endif
                <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
            @php $menuId = 'following-menu-'.$loop->index; @endphp
            <tr>
                <td class="c-check">
                    <input type="checkbox" class="row-checkbox h-4 w-4 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                        value="{{ $role->subdomain }}" aria-label="{{ $role->getDisplayName(false) }}"
                        data-has-email="{{ $role->email ? 'true' : 'false' }}">
                </td>
                <td class="c-main c-strong">
                    @if ($role->isClaimed())
                    <a href="{{ $role->getGuestUrl() }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $role->getDisplayName(false) }}</bdi></a>
                    @else
                    <bdi>{{ $role->getDisplayName(false) }}</bdi>
                    @endif
                    <span class="c-sub">{{ __('messages.' . $role->type) }}</span>
                </td>
                {{-- No space inside an empty cell: the list drops a cell from a phone's row only
                     when it is truly empty. --}}
                @if ($hasEmail)
                <td class="c-contact">@if ($shownEmail($role))<a href="mailto:{{ $role->email }}" class="event-link"><bdi>{{ $role->email }}</bdi></a>@endif</td>
                @endif
                @if ($hasPhone)
                <td class="c-contact">@if ($shownPhone($role))<a href="tel:{{ $role->phone }}" class="event-link"><bdi dir="ltr">{{ $role->phone }}</bdi></a>@endif</td>
                @endif
                @if ($hasWebsite)
                {{-- Another owner's website: linked only through safeHref(), since a
                     javascript: value would run here, in the app. --}}
                <td class="c-contact">@if ($followingWebsiteHref = \App\Utils\UrlUtils::safeHref($role->website))<a href="{{ $followingWebsiteHref }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ App\Utils\UrlUtils::clean($role->website) }}</bdi></a>@elseif ($role->website)<bdi>{{ App\Utils\UrlUtils::clean($role->website) }}</bdi>@endif</td>
                @endif
                <td class="c-actions">
                    {{-- One of the layout's shared menus (.popup-toggle[data-popup-target] in
                         layouts/app): it places the menu under its button, keeps it on the
                         screen, closes it on Escape and keeps aria-expanded honest. Each row's
                         menu was an Alpine island of its own before. --}}
                    <div class="relative inline-block text-start">
                        <button type="button" class="popup-toggle page-tool" data-popup-target="{{ $menuId }}" aria-haspopup="true" aria-expanded="false">
                            {{ __('messages.actions') }}
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="{{ $menuId }}" class="ap-dropdown pop-up-menu hidden absolute z-10 mt-2 w-56 divide-y divide-gray-100 dark:divide-white/[0.06] rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical">
                            @if (auth()->user()->isEditor($role->subdomain))
                            <div class="py-1" data-popup-target="{{ $menuId }}">
                                <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}" class="{{ $menuItem }}" role="menuitem">
                                    <svg class="{{ $menuIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <span>{{ __('messages.edit') }}</span>
                                </a>
                            </div>
                            @endif

                            @if ($role->isClaimed())
                            <div class="py-1" data-popup-target="{{ $menuId }}">
                                <button type="button" class="{{ $menuItem }}" role="menuitem" data-copy-feed="{{ route('feed.ical', ['subdomain' => $role->subdomain]) }}">
                                    <svg class="{{ $menuIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ __('messages.copy_ical_feed') }}</span>
                                </button>
                                <button type="button" class="{{ $menuItem }}" role="menuitem" data-copy-feed="{{ route('feed.rss', ['subdomain' => $role->subdomain]) }}">
                                    <svg class="{{ $menuIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                    </svg>
                                    <span>{{ __('messages.copy_rss_feed') }}</span>
                                </button>
                            </div>
                            @endif

                            @if (auth()->user()->google_token)
                            <div class="py-1" data-popup-target="{{ $menuId }}">
                                @if (isset($memberSyncCalendarIds[$role->id]))
                                <button type="button" class="{{ $menuItem }}" role="menuitem" data-calendar-unsync="{{ $role->subdomain }}">
                                    <svg class="{{ $menuIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ __('messages.unsync_from_my_calendar') }}</span>
                                </button>
                                @else
                                <button type="button" class="{{ $menuItem }}" role="menuitem" data-calendar-sync="{{ $role->subdomain }}">
                                    <svg class="{{ $menuIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ __('messages.sync_to_my_calendar') }}</span>
                                </button>
                                @endif
                            </div>
                            @endif

                            <div class="py-1" data-popup-target="{{ $menuId }}">
                                {{-- A form, not a link: unfollowing changes something, so it is
                                     posted with the page's token. data-confirm on a form is asked
                                     by the layout before it is sent. --}}
                                <form method="POST" action="{{ route('role.unfollow', ['subdomain' => $role->subdomain]) }}" data-confirm="{{ __('messages.are_you_sure') }}">
                                @csrf
                                <button type="submit"
                                   class="group flex items-center w-full px-4 py-2.5 text-sm text-start text-red-700 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem">
                                    <svg class="w-4 h-4 me-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span>{{ __('messages.unfollow') }}</span>
                                </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@elseif (request()->filled('filter'))
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.no_results_found')" :text="__('messages.following_filter_empty')"
        icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
</div>
@else
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.no_following')" :text="__('messages.start_following_schedules')"
        icon="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
</div>
@endif
