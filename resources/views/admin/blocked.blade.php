<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'blocked'])

    {{-- Shutting an account out, and the list of what new accounts are refused for.

         Three things under one another, in the order an operator meets them: find the account,
         see who is blocked, and the list. A block is never decided here: the row leads to the
         account's own page (admin.blocked-account), which shows what the account owns and who
         shares its address before anything is taken down.

         The list's own answers (added, removed, already there) are the layout's toast, because
         those requests land on #list, far below where a notice at the top would be read. --}}
    @php
        $field = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $day = fn ($moment) => $moment ? $moment->copy()->setTimezone(auth()->user()->timezone ?: config('app.timezone'))->translatedFormat('M j, Y') : null;
        $typeNames = [
            \App\Services\Blocklist::EMAIL => __('messages.blocklist_type_email'),
            \App\Services\Blocklist::DOMAIN => __('messages.blocklist_type_domain'),
            \App\Services\Blocklist::IP => __('messages.blocklist_type_ip'),
        ];
        $typeValue = old('type', \App\Services\Blocklist::DOMAIN);
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.blocked_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($blockedCount) }}</div>
                <div class="page-stat-label">@lang('messages.blocked_accounts')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($entryCount) }}</div>
                <div class="page-stat-label">@lang('messages.blocklist_entries')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($refusedCount) }}</div>
                <div class="page-stat-label">@lang('messages.blocklist_refused')</div>
            </div>
        </div>

        {{-- ===================== Find an account ===================== --}}
        <div class="page-subhead">
            <h2>@lang('messages.block_find_title')</h2>
            <p>@lang('messages.block_find_lead')</p>
        </div>

        <div>
            <form method="GET" action="{{ route('admin.blocked') }}" class="page-filters">
                <div class="page-filter is-grow">
                    <label for="block-search">@lang('messages.search')</label>
                    <x-text-input id="block-search" name="search" type="text" class="block w-full" :value="$search" :placeholder="__('messages.block_find_placeholder')" autocomplete="off" dir="auto" />
                </div>
                <div class="is-end">
                    @if ($search !== '')
                    <a href="{{ route('admin.blocked') }}" class="page-tool">@lang('messages.clear')</a>
                    @endif
                    <x-brand-button type="submit" size="sm" class="!py-2">@lang('messages.search')</x-brand-button>
                </div>
            </form>

            @if ($search !== '' && $found->isEmpty())
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="__('messages.no_results_found')" />
            </div>
            @elseif ($found->isNotEmpty())
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.account')</th>
                                <th scope="col" class="c-num">@lang('messages.schedules')</th>
                                <th scope="col">@lang('messages.funnel_signed_up')</th>
                                <th scope="col">@lang('messages.status')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($found as $account)
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($account->id)]) }}" class="c-strong"><bdi>{{ $account->name ?: $account->email }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $account->email }}</span>
                                </td>
                                <td class="c-num" data-label="{{ __('messages.schedules') }}">{{ number_format($account->schedules_count) }}</td>
                                <td class="c-date" data-label="{{ __('messages.funnel_signed_up') }}">{{ $day($account->created_at) }}</td>
                                <td>
                                    @if ($account->isBlocked())
                                    <span class="event-status is-bad">@lang('messages.blocked')</span>
                                    @elseif ($account->isAdmin())
                                    <span class="event-status is-info">@lang('messages.admin')</span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($account->id)]) }}" class="event-link">@lang('messages.review')</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- ===================== Blocked accounts ===================== --}}
        <div class="page-subhead">
            <h2>@lang('messages.blocked_accounts')</h2>
            <p>@lang('messages.block_blocked_lead')</p>
        </div>

        <div>
            @if ($blocked->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.account')</th>
                                <th scope="col" class="c-num">@lang('messages.block_col_taken_down')</th>
                                <th scope="col">@lang('messages.blocked')</th>
                                <th scope="col">@lang('messages.note')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($blocked as $row)
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($row->user_id)]) }}" class="c-strong"><bdi>{{ $row->user->name ?: $row->user->email }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $row->user->email }}</span>
                                </td>
                                <td class="c-num" data-label="{{ __('messages.block_col_taken_down') }}">{{ number_format(count($row->role_ids ?? [])) }}</td>
                                <td class="c-date" data-label="{{ __('messages.blocked') }}">
                                    {{ $day($row->created_at) }}
                                    @if ($row->blocker)
                                    <span class="c-sub"><bdi>{{ $row->blocker->name }}</bdi></span>
                                    @endif
                                </td>
                                <td class="c-quiet c-wrap" data-label="{{ __('messages.note') }}">@if ($row->note)<bdi>{{ $row->note }}</bdi>@endif</td>
                                <td class="c-actions">
                                    <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($row->user_id)]) }}" class="event-link">@lang('messages.review')</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.block_none_title')" :text="__('messages.block_none_text')"
                    icon="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </div>
            @endif

            @if ($blocked->hasPages())
            <div class="page-pager">{{ $blocked->links() }}</div>
            @endif
        </div>

        {{-- ===================== The list ===================== --}}
        <div class="page-subhead" id="list">
            <h2>@lang('messages.blocklist_title')</h2>
            <p>@lang('messages.blocklist_lead')</p>
        </div>

        <div>
            <form method="POST" action="{{ route('admin.blocked.entry.store') }}" class="page-filters">
                @csrf
                <label class="page-filter">
                    <span>@lang('messages.type')</span>
                    <select name="type" class="{{ $field }}">
                        @foreach ($typeNames as $key => $name)
                        <option value="{{ $key }}" {{ $typeValue === $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="page-filter is-grow">
                    <label for="blocklist-value">@lang('messages.blocklist_value')</label>
                    <x-text-input id="blocklist-value" name="value" type="text" class="block w-full" :value="old('value')" placeholder="example.com" autocomplete="off" dir="ltr" required maxlength="255" />
                </div>
                <div class="page-filter is-grow">
                    <label for="blocklist-note">@lang('messages.note')</label>
                    <x-text-input id="blocklist-note" name="note" type="text" class="block w-full" :value="old('note')" :placeholder="__('messages.blocklist_note_placeholder')" autocomplete="off" dir="auto" maxlength="255" />
                </div>
                <div class="is-end">
                    <x-brand-button type="submit" size="sm" class="!py-2">@lang('messages.blocklist_add')</x-brand-button>
                </div>
            </form>
            <x-input-error :messages="$errors->get('value')" class="mb-3" />
            <p class="blocklist-help">@lang('messages.blocklist_value_help')</p>

            @if ($entries->count() > 0 || $type)
            <nav class="blocklist-types" aria-label="{{ __('messages.type') }}">
                <a href="{{ route('admin.blocked', array_filter(['search' => $search ?: null])) }}#list" class="page-pill" @if (! $type) aria-current="true" @endif>@lang('messages.all')</a>
                @foreach ($typeNames as $key => $name)
                <a href="{{ route('admin.blocked', array_filter(['search' => $search ?: null, 'type' => $key])) }}#list" class="page-pill" @if ($type === $key) aria-current="true" @endif>{{ $name }}</a>
                @endforeach
            </nav>
            @endif

            @if ($entries->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.blocklist_col_entry')</th>
                                <th scope="col">@lang('messages.note')</th>
                                <th scope="col">@lang('messages.date')</th>
                                <th scope="col" class="c-num">@lang('messages.blocklist_col_refused')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entries as $entry)
                            @php $from = $entry->accountBlock?->user; @endphp
                            <tr>
                                <td class="c-main c-wrap">
                                    <span class="c-strong c-mono" dir="ltr">{{ $entry->value }}</span>
                                    <span class="c-sub">{{ $typeNames[$entry->type] ?? $entry->type }}</span>
                                </td>
                                <td class="c-quiet c-wrap" data-label="{{ __('messages.note') }}">@if ($entry->note)<bdi>{{ $entry->note }}</bdi>@endif @if ($from)<a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($from->id)]) }}" class="event-link">{{ __('messages.blocklist_from_block', ['name' => $from->name ?: $from->email]) }}</a>@endif</td>
                                <td class="c-date" data-label="{{ __('messages.date') }}">{{ $day($entry->created_at) }}</td>
                                <td class="c-num" data-label="{{ __('messages.blocklist_col_refused') }}">
                                    {{ number_format($entry->refused_count) }}
                                    @if ($entry->last_refused_at)
                                    <span class="c-sub">{{ $day($entry->last_refused_at) }}</span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    <form method="POST" action="{{ route('admin.blocked.entry.remove', ['hash' => \App\Utils\UrlUtils::encodeId($entry->id)]) }}">
                                        @csrf
                                        <button type="submit" class="event-link is-danger" data-confirm="{{ __('messages.blocklist_remove_confirm', ['value' => $entry->value]) }}">@lang('messages.remove')</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif ($type || $entries->currentPage() > 1)
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="__('messages.no_results_found')" />
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.blocklist_empty_title')" :text="__('messages.blocklist_empty_text')"
                    icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
            </div>
            @endif

            @if ($entries->hasPages())
            <div class="page-pager">{{ $entries->links() }}</div>
            @endif
        </div>
    </div>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* The sentence that says what can be typed, under the row it is about, and the row of
               types above the list. Both in the kit's quiet ink. */
            .blocklist-help {
              margin: -0.25rem 0 1rem;
              font-size: 0.8125rem;
              line-height: 1.25rem;
              color: rgb(var(--ap-ink-3));
              max-width: 110ch;
            }
            .blocklist-types {
              display: flex;
              flex-wrap: wrap;
              gap: 0.5rem;
              margin-bottom: 0.75rem;
            }
            /* A heading a link lands on clears the bar above the page. */
            #list {
              scroll-margin-top: 6rem;
            }
        </style>
    </x-slot>
</x-app-admin-layout>
