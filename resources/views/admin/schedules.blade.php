<x-app-admin-layout>
    <x-slot name="head">
        {{-- Two things the page kit does not have yet. A filter's box and selects at the size of
             the list under them: the layout gives every input and select 0.75rem by 1rem of
             padding and a 1.15rem type with !important, and six of them took two rows of a laptop.
             And a figure in a strip that is a link. --}}
        <style {!! nonce_attr() !!}>
            .page-filters input[type="text"],
            .page-filters select {
              padding-block: 0.5rem !important;
              padding-inline: 0.625rem !important;
              font-size: 0.875rem !important;
              line-height: 1.25rem !important;
            }
            .page-filters select {
              padding-inline-end: 2rem !important;
            }
            a.page-stat {
              transition: background-color 0.2s;
            }
            a.page-stat:hover {
              background: var(--ap-tint-1);
            }
            a.page-stat:focus-visible {
              outline: 2px solid var(--brand-blue);
              outline-offset: -2px;
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'schedules'])

    {{-- Plans, their status and their source exist only on a hosted install. A plain selfhost
         has none (actualPlanTier() is enterprise for every schedule), so it gets the
         verification split and the columns that apply to it. --}}
    @php
        $hosted = (bool) config('app.hosted');
        $filtered = request('search') || request('plan_type') || request('status') || request('source') || request('verification') || request('owner');

        // The subscription's state as a word and the tone its mark wears. A schedule with no
        // subscription has no state to show, so the cell stays empty rather than reading "None".
        $statusMarks = [
            'active' => ['is-on', __('messages.active')],
            'trial' => ['is-info', __('messages.trial')],
            'grace_period' => ['is-warn', __('messages.grace_period')],
            'cancelled' => ['is-bad', __('messages.cancelled')],
            'past_due' => ['is-bad', __('messages.past_due')],
            'inactive' => ['', __('messages.inactive')],
        ];
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ $hosted ? __('messages.admin_schedules_lead') : __('messages.admin_schedules_lead_selfhost') }}</p>
    </div>

    <div class="page-shell page-stack">
        {{-- Saving a plan comes back here with its confirmation, which nothing on the page used
             to show: the layout's toast reads `message`, `error` and `warning`, not `success`. --}}
        <x-page-flash :keys="['success' => 'success']" />

        {{-- The counts, as two strips of plain figures where there were eight icon tiles. The
             three plan counts are verified-only, so Unverified completes the first strip: free +
             pro + enterprise + unverified is every non-demo schedule with an owner. --}}
        <div class="ap-card rounded-xl page-stats is-auto">
            @if ($hosted)
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($freeCount) }}</div>
                <div class="page-stat-label">@lang('messages.free')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($proCount) }}</div>
                <div class="page-stat-label">@lang('messages.pro')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($enterpriseCount) }}</div>
                <div class="page-stat-label">@lang('messages.enterprise')</div>
            </div>
            @else
            {{-- The three plan counts, which are verified-only, summed. --}}
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($freeCount + $proCount + $enterpriseCount) }}</div>
                <div class="page-stat-label">@lang('messages.verified')</div>
            </div>
            @endif

            {{-- Deliberately the only figure on this page that is a link: it replaces the
                 dashboard alert row that used to deep-link here. --}}
            <a href="{{ route('admin.schedules', ['verification' => 'unverified']) }}" class="page-stat">
                <div class="page-stat-value {{ $unverifiedCount > 0 ? 'is-warn' : '' }}">{{ number_format($unverifiedCount) }}</div>
                <div class="page-stat-label">@lang('messages.unverified')</div>
            </a>
        </div>

        @if ($hosted)
        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($stripePaidCount) }}</div>
                <div class="page-stat-label">@lang('messages.stripe_paid')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($manualPlanCount) }}</div>
                <div class="page-stat-label">@lang('messages.manual')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($trialCount) }}</div>
                <div class="page-stat-label">@lang('messages.on_free_trial')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $expiringSoon > 0 ? 'is-warn' : '' }}">{{ number_format($expiringSoon) }}</div>
                <div class="page-stat-label">@lang('messages.expiring_soon')</div>
            </div>
        </div>
        @endif

        <div>
            <form method="GET" action="{{ route('admin.schedules') }}" class="page-filters">
                <div class="page-filter is-grow relative">
                    <label for="schedule-search">@lang('messages.search')</label>
                    {{-- The dropdown must offer exactly what the table below can return, so it is
                         handed this page's own owner and state filters. --}}
                    <input type="text" name="search" id="schedule-search" value="{{ request('search') }}" dir="auto"
                        placeholder="{{ __('messages.search_schedules') }}" autocomplete="off" data-subdomain-autocomplete
                        data-subdomain-params="{{ 'admin_listable=1&owner='.urlencode((string) (is_array(request('owner')) ? '' : request('owner', ''))).(request('status') === 'deleted' ? '&deleted_only=1' : '') }}"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <div data-subdomain-dropdown class="hidden absolute start-0 end-0 top-full mt-1"></div>
                </div>
                @if ($hosted)
                <label class="page-filter">
                    <span>@lang('messages.plan')</span>
                    <select name="plan_type" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.all_plans')</option>
                        <option value="free" {{ request('plan_type') === 'free' ? 'selected' : '' }}>@lang('messages.free')</option>
                        <option value="pro" {{ request('plan_type') === 'pro' ? 'selected' : '' }}>@lang('messages.pro')</option>
                        <option value="enterprise" {{ request('plan_type') === 'enterprise' ? 'selected' : '' }}>@lang('messages.enterprise')</option>
                    </select>
                </label>
                @endif
                <label class="page-filter">
                    <span>@lang('messages.status')</span>
                    <select name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.all_status')</option>
                        @if ($hosted)
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>@lang('messages.active')</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>@lang('messages.expired')</option>
                        <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>@lang('messages.trial')</option>
                        @endif
                        <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>@lang('messages.deleted')</option>
                    </select>
                </label>
                <label class="page-filter">
                    <span>@lang('messages.owner')</span>
                    <select name="owner" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.claimed')</option>
                        <option value="unclaimed" {{ request('owner') === 'unclaimed' ? 'selected' : '' }}>@lang('messages.unclaimed')</option>
                        <option value="any" {{ request('owner') === 'any' ? 'selected' : '' }}>@lang('messages.all_owners')</option>
                    </select>
                </label>
                @if ($hosted)
                <label class="page-filter">
                    <span>@lang('messages.source')</span>
                    <select name="source" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.all_sources')</option>
                        <option value="stripe" {{ request('source') === 'stripe' ? 'selected' : '' }}>@lang('messages.stripe')</option>
                        <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>@lang('messages.manual')</option>
                        <option value="trial" {{ request('source') === 'trial' ? 'selected' : '' }}>@lang('messages.trial')</option>
                    </select>
                </label>
                @endif
                <label class="page-filter">
                    <span>@lang('messages.verification')</span>
                    <select name="verification" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.all')</option>
                        <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>@lang('messages.verified')</option>
                        <option value="unverified" {{ request('verification') === 'unverified' ? 'selected' : '' }}>@lang('messages.unverified')</option>
                    </select>
                </label>
                <div class="is-end">
                    @if ($filtered)
                    <a href="{{ route('admin.schedules') }}" class="page-tool">@lang('messages.clear')</a>
                    @endif
                    <x-brand-button type="submit" size="sm">@lang('messages.filter')</x-brand-button>
                </div>
            </form>

            @if ($roles->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table is-wide">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.schedule')</th>
                                <th scope="col">@lang('messages.type')</th>
                                @if ($hosted)
                                <th scope="col">@lang('messages.plan')</th>
                                <th scope="col">@lang('messages.term')</th>
                                <th scope="col">@lang('messages.expires')</th>
                                <th scope="col">@lang('messages.status')</th>
                                <th scope="col">@lang('messages.source')</th>
                                @endif
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                            <tr>
                                <td class="c-main">
                                    <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $role->name }}</bdi></a>
                                    @if ($role->is_deleted)
                                    <span class="event-chip">@lang('messages.deleted')</span>
                                    @endif
                                    @if (! $role->user_id)
                                    {{-- No owner: a venue or talent EventRepo auto-created while importing an
                                         event. It has no public page, but it does hold a subdomain. --}}
                                    <span class="event-chip">@lang('messages.unclaimed')</span>
                                    @endif
                                    {{-- Under the name: the address, then which contact is verified (a green
                                         mark for one that is, an amber one for an address or number
                                         still waiting). --}}
                                    <span class="c-sub">
                                        <span class="inline-flex flex-wrap items-center gap-x-3 gap-y-0.5">
                                            <span>
                                                <span dir="ltr">{{ $role->subdomain }}</span>
                                                {{-- Gated on is_deleted too: restore() keeps the column when it
                                                     could not reclaim the original, so a live schedule would
                                                     otherwise carry a "was ..." note forever. --}}
                                                @if ($role->is_deleted && $role->subdomain_before_delete)
                                                &middot; @lang('messages.original_subdomain', ['subdomain' => $role->subdomain_before_delete])
                                                @endif
                                            </span>
                                            @if (! $role->email && ! $role->phone)
                                            <span class="event-status is-warn">@lang('messages.unverified')</span>
                                            @else
                                            @if ($role->email)
                                            <span class="event-status {{ $role->email_verified_at ? 'is-on' : 'is-warn' }}" title="{{ $role->email_verified_at ? __('messages.email_verified') : __('messages.email_not_verified') }}">@lang('messages.email')<span class="sr-only">: {{ $role->email_verified_at ? __('messages.verified') : __('messages.unverified') }}</span></span>
                                            @endif
                                            @if ($role->phone)
                                            <span class="event-status {{ $role->phone_verified_at ? 'is-on' : 'is-warn' }}" title="{{ $role->phone_verified_at ? __('messages.phone_verified') : __('messages.phone_not_verified') }}">@lang('messages.phone')<span class="sr-only">: {{ $role->phone_verified_at ? __('messages.verified') : __('messages.unverified') }}</span></span>
                                            @endif
                                            @endif
                                        </span>
                                    </span>
                                </td>
                                <td class="c-quiet">{{ in_array($role->type, ['talent', 'venue', 'curator'], true) ? __('messages.'.$role->type) : $role->type }}</td>
                                @if ($hosted)
                                @php
                                    $planType = $role->actualPlanTier();
                                    $status = $role->subscriptionStatusLabel();
                                    $onTrial = $role->trial_ends_at && $role->onGenericTrial();
                                @endphp
                                <td class="{{ $planType === 'free' ? 'c-quiet' : 'c-strong' }}">{{ in_array($planType, ['free', 'pro', 'enterprise'], true) ? __('messages.'.$planType) : ucfirst($planType) }}</td>
                                {{-- Every schedule carries a term, a free one too. It says something
                                     only beside a plan that is paid for or on trial. --}}
                                <td class="c-quiet">@if ($planType !== 'free'){{ $role->plan_term === 'month' ? __('messages.monthly') : ($role->plan_term === 'year' ? __('messages.yearly') : '') }}@endif</td>
                                <td class="c-date" data-label="{{ __('messages.expires') }}">@if ($onTrial){{ $role->trial_ends_at->translatedFormat('M j, Y') }}<span class="c-sub">{{ trans_choice('messages.days_left_choice', (int) now()->diffInDays($role->trial_ends_at), ['count' => (int) now()->diffInDays($role->trial_ends_at)]) }}</span>@elseif ($role->plan_expires){{ \Carbon\Carbon::parse($role->plan_expires)->translatedFormat('M j, Y') }}@endif</td>
                                <td>@isset($statusMarks[$status])<span class="event-status {{ $statusMarks[$status][0] }}">{{ $statusMarks[$status][1] }}</span>@endisset</td>
                                <td class="c-quiet">@if ($role->hasActiveSubscription())@lang('messages.stripe')@elseif ($role->onGenericTrial())@lang('messages.trial')@elseif (($role->plan_type ?? 'free') !== 'free' && $role->plan_expires)@lang('messages.manual')@endif</td>
                                @endif
                                <td class="c-actions">
                                    {{-- One action beside Edit, not three: the row is already the
                                         widest on the page, and the full set (with the copy that
                                         explains what a release does) lives on the edit page. --}}
                                    <a href="{{ route('admin.schedules.edit', ['role' => $role->encodeId()]) }}" class="event-link">@lang('messages.edit')</a>
                                    @if ($role->is_deleted && $role->subdomain_before_delete)
                                    <form method="POST" action="{{ route('admin.schedules.restore', ['role' => $role->encodeId()]) }}">
                                        @csrf
                                        <button type="submit" class="event-link" data-confirm="{{ __('messages.restore_schedule_confirm') }}">@lang('messages.restore')</button>
                                    </form>
                                    @else
                                    {{-- A deleted row with no recorded original is one the API, unfollow or
                                         merge paths left behind: still deleted, still holding its name. --}}
                                    <form method="POST" action="{{ route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]) }}">
                                        @csrf
                                        <button type="submit" class="event-link is-danger"
                                            data-confirm="{{ $role->is_deleted ? __('messages.release_subdomain_confirm') : __('messages.mark_deleted_confirm') }}">{{ $role->is_deleted ? __('messages.release') : __('messages.delete') }}</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @else
            <div class="ap-card rounded-xl">
                <x-page-empty
                    :title="__('messages.no_schedules_found')"
                    :text="($filtered || $roles->currentPage() > 1) ? __('messages.no_match_filters') : __('messages.admin_schedules_empty_text')"
                    icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5">
                    @if ($filtered)
                    <x-secondary-link :href="route('admin.schedules')">@lang('messages.clear_filters')</x-secondary-link>
                    @endif
                </x-page-empty>
            </div>
            @endif

            {{-- Outside the list's own branch: a page past the last one still needs the way back. --}}
            @if ($roles->hasPages())
            <div class="page-pager">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>

    @include('admin.partials._subdomain-autocomplete')
</x-app-admin-layout>
