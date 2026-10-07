@php
    $subscriptionsCount = $subscriptionsCount ?? 0;
    $installmentsCount = $installmentsCount ?? 0;
    $giftCardsCount = $giftCardsCount ?? 0;

    // ONE list of tabs. It feeds the strip (from a tablet up) and the dropdown a phone gets: the
    // old strip had no way to scroll, so on a phone Installments and Gift cards were off the edge
    // of the page and could not be reached at all.
    $salesTabs = array_values(array_filter([
        ['id' => 'sales', 'label' => __('messages.sales'), 'count' => 0],
        $waitlistCount > 0 ? ['id' => 'waitlist', 'label' => __('messages.waitlist'), 'count' => $waitlistCount] : null,
        $hasPro ? ['id' => 'feedback', 'label' => __('messages.feedback'), 'count' => 0] : null,
        ($hasPro || $subscriptionsCount > 0) ? ['id' => 'subscriptions', 'label' => __('messages.subscriptions'), 'count' => $subscriptionsCount] : null,
        ($hasPro || $installmentsCount > 0) ? ['id' => 'installments', 'label' => __('messages.installments'), 'count' => $installmentsCount] : null,
        ($hasPro || $giftCardsCount > 0) ? ['id' => 'gift-cards', 'label' => __('messages.gift_cards'), 'count' => $giftCardsCount] : null,
    ]));
    $hasTabs = count($salesTabs) > 1;
@endphp

<x-app-admin-layout>

    <x-slot name="head">
        {{-- What this page needs beyond the kit (partials/admin-page-styles). The lists of the
             other tabs (ticket/*_table) are drawn into this page and lean on these rules too. --}}
        <style {!! nonce_attr() !!}>
            /* Seven columns of orders want more than the 64rem column of the portal's other lists. */
            .sales-shell {
              max-width: 80rem;
            }
            /* A row that is closed. The kit lays a phone's rows out as flex, which outranks the
               hidden class and the hidden attribute alike. */
            .page-table tr.hidden,
            .page-table tr[hidden] {
              display: none;
            }
            /* The filter box with its clear button inside it, and the switch beside it. */
            .sales-filter-box {
              position: relative;
            }
            /* !important because the kit sizes a filter's box that way (it has to, against the
               layout's own rule): without it typed text ran under the clear button. */
            .page-filters .sales-filter-box input {
              padding-inline-end: 2.5rem !important;
            }
            .sales-filter-clear {
              position: absolute;
              inset-block: 0;
              inset-inline-end: 0.5rem;
              display: flex;
              align-items: center;
              border: 0;
              background: none;
              color: rgb(var(--ap-ink-4));
              cursor: pointer;
            }
            .sales-filter-clear[hidden] {
              display: none;
            }
            .sales-filter-clear:hover {
              color: rgb(var(--ap-ink-2));
            }
            .sales-switch {
              align-self: center;
            }
            /* The little arrow that opens a row, before the name it belongs to. */
            .sales-customer {
              display: flex;
              align-items: flex-start;
              gap: 0.5rem;
              min-width: 0;
            }
            .sales-open {
              flex: none;
              margin-block: 0.0625rem 0;
              margin-inline: -0.25rem 0;
              border: 0;
              border-radius: 0.25rem;
              padding: 0.125rem;
              background: none;
              color: rgb(var(--ap-ink-3));
              cursor: pointer;
            }
            .sales-open:hover {
              color: rgb(var(--ap-ink));
            }
            .sales-open:focus-visible {
              outline: 2px solid var(--brand-blue);
              outline-offset: 1px;
            }
            .sales-open svg {
              display: block;
              width: 1rem;
              height: 1rem;
              transition: transform 0.2s;
            }
            [dir="rtl"] .sales-open svg {
              transform: scaleX(-1);
            }
            .sales-open[aria-expanded="true"] svg,
            [dir="rtl"] .sales-open[aria-expanded="true"] svg {
              transform: rotate(90deg);
            }
            /* What an opened row shows: set in from the arrow, on the list's own tint. */
            .page-table tr.sales-detail > td {
              border-top: 0;
              padding-top: 0.5rem;
              padding-bottom: 0.75rem;
              background: var(--ap-tint-1);
            }
            .sales-detail-body {
              padding-inline-start: 1.5rem;
            }
            .sales-list .c-guest-actions > * + * {
              margin-inline-start: 0.875rem;
            }
            .sales-guest-mark {
              flex: none;
              padding-inline-start: 1.5rem;
              color: rgb(var(--ap-ink-4));
            }
            .sales-fields {
              display: grid;
              gap: 0.25rem;
              margin: 0;
              font-size: 0.8125rem;
            }
            .sales-fields > div {
              display: flex;
              flex-wrap: wrap;
              gap: 0.125rem 0.5rem;
            }
            .sales-fields dt {
              font-weight: 500;
              color: rgb(var(--ap-ink-3));
            }
            .sales-fields dd {
              margin: 0;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            .sales-fields .sales-fields-group dt {
              margin-top: 0.25rem;
              font-weight: 600;
              color: rgb(var(--ap-ink-2));
            }
            .sales-fields .is-nested {
              padding-inline-start: 0.75rem;
            }
            .sales-note {
              margin: 0 0 0.5rem;
              font-size: 0.8125rem;
              font-style: italic;
              color: rgb(var(--ap-ink-2));
              overflow-wrap: anywhere;
            }
            .sales-lines {
              margin: 0.5rem 0 0;
              padding: 0;
              list-style: none;
              font-size: 0.8125rem;
            }
            .sales-lines li {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0.125rem 1rem;
              border-top: 1px solid rgb(var(--ap-border));
              padding: 0.375rem 0;
              color: rgb(var(--ap-ink-2));
            }
            .sales-lines li > :first-child {
              flex: 1 1 12rem;
              min-width: 0;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            .sales-detail-actions {
              display: flex;
              flex-wrap: wrap;
              gap: 0.5rem 1.25rem;
              margin-top: 0.625rem;
            }
            /* The orders: seven columns in the width of a laptop, so the cells sit a little
               closer than the kit's, a code or a gift card goes under the total it took off, and
               a long reference breaks rather than pushing the row's menu off the card. */
            .sales-list .c-total {
              white-space: nowrap;
            }
            .sales-list .c-total .event-chip {
              display: table;
              margin: 0.25rem 0 0;
            }
            .sales-list .c-reference {
              max-width: 10.5rem;
              font-size: 0.8125rem;
            }
            .sales-menu {
              padding: 0.375rem;
            }
            .sales-menu svg {
              width: 1.25rem;
              height: 1.25rem;
            }
            /* The kit gives a phone's small links room for a thumb; under a name that room reads
               as an indent. */
            .sales-shell .page-table .c-sub a.event-link {
              margin-inline: 0;
              padding-inline: 0;
            }
            /* An address and a phone number under a name: a line each. */
            .sales-shell .page-table .sales-customer .c-sub a,
            .sales-shell .page-table .sales-customer .c-sub > span {
              display: block;
            }
            .sales-shell .page-filters > .is-end {
              align-self: center;
            }
            .sales-shell .page-table .event-status {
              white-space: nowrap;
            }
            @media (min-width: 640px) {
              .sales-shell .page-table.is-wide th,
              .sales-shell .page-table.is-wide td {
                padding-inline: 0.625rem;
              }
              .sales-shell .page-table.is-wide th:first-child,
              .sales-shell .page-table.is-wide td:first-child {
                padding-inline-start: 1rem;
              }
              .sales-shell .page-table.is-wide th:last-child,
              .sales-shell .page-table.is-wide td:last-child {
                padding-inline-end: 1rem;
              }
              .sales-shell .page-table .c-event {
                min-width: 8rem;
              }
              /* When the next payment is due, and what went wrong with it, in words that keep
                 whole. */
              .sales-shell .page-table .c-due {
                min-width: 7.5rem;
              }
              .sales-shell .page-table .c-due .c-sub,
              .sales-shell .page-table .c-plan .c-sub {
                overflow-wrap: normal;
              }
              .sales-shell .page-table .c-due .event-status {
                white-space: normal;
              }
              .sales-list .c-event {
                min-width: 10rem;
              }
              .sales-list .c-reference {
                max-width: 8rem;
              }
              /* A name keeps to its line and an address that is too long for the column is cut
                 with an ellipsis (it is whole in its title, and on a phone, where it has the
                 row): broken wherever it met the edge, "zofia.kowalska@gmail.co" over "m" read
                 as two things. */
              .sales-shell .page-table .sales-customer .page-person-text {
                max-width: 12.5rem;
              }
              .sales-shell .page-table .sales-customer .c-sub a,
              .sales-shell .page-table .sales-customer .c-sub > span {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
              }
              .sales-shell .page-table.is-wide td.c-main {
                min-width: 9rem;
              }
              .sales-fold-bar > .page-actions {
                margin-inline-start: auto;
              }
            }
            .sales-warning {
              max-width: 17rem;
            }
            .sales-rating {
              display: inline-flex;
              align-items: center;
              gap: 0.125rem;
              margin-inline-start: 0.5rem;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }
            .sales-rating svg,
            .sales-stars svg {
              width: 0.9375rem;
              height: 0.9375rem;
            }
            .sales-rating svg,
            .sales-stars .is-on {
              color: #f59e0b;
            }
            .sales-stars {
              display: inline-flex;
              gap: 0.0625rem;
              vertical-align: middle;
              color: rgb(var(--ap-ink-4));
            }
            /* A word under a figure starts with a capital, whatever the sentence it was written for. */
            .sales-shell .page-stat-label::first-letter {
              text-transform: uppercase;
            }
            /* A card that folds (the two queues of the Feedback tab): its head is the summary. */
            details.sales-fold > summary {
              list-style: none;
              cursor: pointer;
            }
            details.sales-fold > summary::-webkit-details-marker {
              display: none;
            }
            details.sales-fold > summary .sales-fold-arrow {
              flex: none;
              width: 1rem;
              height: 1rem;
              color: rgb(var(--ap-ink-3));
              transition: transform 0.2s;
            }
            [dir="rtl"] details.sales-fold > summary .sales-fold-arrow {
              transform: scaleX(-1);
            }
            details.sales-fold[open] > summary .sales-fold-arrow {
              transform: rotate(90deg);
            }
            details.sales-fold:not([open]) > summary {
              border-bottom: 0;
            }
            details.sales-fold > summary:focus-visible {
              outline: 2px solid var(--brand-blue);
              outline-offset: -2px;
            }
            .sales-fold-title {
              display: flex;
              align-items: center;
              gap: 0.5rem;
              min-width: 0;
            }
            .sales-fold-bar {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.5rem 1rem;
              border-bottom: 1px solid rgb(var(--ap-border));
              padding: 0.625rem 1.25rem;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .sales-fold-bar form {
              display: inline-flex;
            }
            .sales-comment {
              max-width: 22rem;
            }
            @media (max-width: 639.98px) {
              /* An order on a phone: who, what for, then the total and what became of it, then
                 the reference with the row's menu at the end. */
              .sales-list .c-event {
                flex: 1 1 100%;
              }
              .sales-list .c-total {
                order: 1;
              }
              .sales-list .c-total .event-chip {
                display: inline-block;
                margin: 0;
                margin-inline-start: 0.25rem;
              }
              .sales-list .c-status {
                order: 2;
              }
              .sales-list .c-date {
                order: 3;
              }
              .sales-list .c-reference {
                order: 4;
                flex: 1 1 55%;
                max-width: none;
                min-width: 0;
              }
              .sales-list .c-actions {
                order: 5;
              }
              .sales-list .c-guest-actions {
                order: 5;
                flex: 1 1 100%;
              }
              .sales-list .c-guest-actions > * + * {
                margin-inline-start: 0.875rem;
              }
              .sales-detail-body,
              .sales-guest-mark {
                padding-inline-start: 0;
              }
              .sales-comment,
              .sales-shell .page-table .c-plan {
                flex: 1 1 100%;
                max-width: none;
              }
              .sales-shell .page-table .c-plan .c-sub {
                display: inline;
                margin-inline-start: 0.5rem;
              }
              /* "collected" and "outstanding" were written for the middle of a sentence. */
              .sales-shell .page-table td.c-cap::before {
                text-transform: capitalize;
              }
            }
        </style>
    </x-slot>

    {{-- Orders across every schedule this person runs. The Sales list has seven columns, so the
         page takes more than the 64rem column the portal's other lists keep to. --}}
    <div class="page-shell sales-shell">
        <x-page-header :title="__('messages.sales')" :lead="__('messages.sales_lead')">
            <x-slot name="actions">
                {{-- Import and the check-in DASHBOARD are Pro, and are hidden rather than shown
                     locked: greyed-out buttons dominating the head of the page is a poor first
                     impression of a tier meant to feel generous. Scanning itself is NOT Pro - a
                     free schedule that sold a ticket must still be able to admit the holder at
                     the door. --}}
                @if ($hasPro)
                <x-secondary-link href="{{ route('sales.import', request()->only(['filter', 'include_past'])) }}">
                    {{ __('messages.import') }}
                </x-secondary-link>
                <x-secondary-link href="{{ route('checkin.index') }}">
                    {{ __('messages.checkin_dashboard') }}
                </x-secondary-link>
                @endif
                <x-brand-link href="{{ route('ticket.scan') }}">
                    <svg class="-ms-0.5 me-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                    </svg>
                    {{ __('messages.scan_ticket') }}
                </x-brand-link>
            </x-slot>
        </x-page-header>

        {{-- An attendee import ends here with how many were imported and how many rows were
             skipped, flashed as `status`, a key the layout does not toast: the result was shown
             nowhere. --}}
        <x-page-flash :keys="['status' => 'success']" class="mb-4" />

        @include('partials.team-access-notice', ['roles' => $planBlockedRoles])

        @if ($hasTabs)
        <div class="ap-tabs-select md:hidden">
            <label for="sales-tab-select" class="sr-only">{{ __('messages.select_a_tab') }}</label>
            <select id="sales-tab-select" autocomplete="off" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                @foreach ($salesTabs as $salesTab)
                <option value="tab-{{ $salesTab['id'] }}">{{ $salesTab['label'] }}{{ $salesTab['count'] > 0 ? ' ('.number_format($salesTab['count']).')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="ap-tabs-wrap hidden md:block" id="sales-tabs">
            <div class="ap-tabs" role="tablist" aria-label="{{ __('messages.sales') }}">
                @foreach ($salesTabs as $salesTab)
                <button type="button" id="tab-{{ $salesTab['id'] }}" class="ap-tab sales-tab" role="tab"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $salesTab['id'] }}-panel">
                    {{ $salesTab['label'] }}
                    @if ($salesTab['count'] > 0)
                    <span class="ap-tab-count">{{ number_format($salesTab['count']) }}</span>
                    @endif
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <div id="sales-panel" @if ($hasTabs) role="tabpanel" aria-labelledby="tab-sales" @endif>
            <div class="page-filters" role="search">
                <div class="is-grow sales-filter-box">
                    <label for="filter" class="sr-only">{{ __('messages.filter') }}</label>
                    <x-text-input type="text" name="filter" id="filter" placeholder="{{ __('messages.filter') }}"
                        value="{{ request()->filter }}" autocomplete="off" class="block w-full"/>
                    <button type="button" id="clear-filter" class="sales-filter-clear" hidden>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="sr-only">{{ __('messages.clear') }}</span>
                    </button>
                </div>
                <div class="sales-switch">
                    <x-toggle name="include_past_sales" id="include-past-sales" :label="__('messages.include_past_events')" :checked="request()->query('include_past') == 1" />
                </div>
                {{-- Export is Pro, and hidden rather than locked like the two above. It sits with
                     the list because it exports what the list is showing: the same filter, the
                     same "Include past events". --}}
                @if ($hasPro)
                <div class="is-end">
                    <a href="{{ route('sales.export') }}" id="export-sales" class="page-tool">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        {{ __('messages.export') }}
                    </a>
                </div>
                @endif
            </div>

            <div id="sales-table">
                @include('ticket.sales_table')
            </div>
        </div>

        @if ($waitlistCount > 0)
        <div id="waitlist-panel" role="tabpanel" aria-labelledby="tab-waitlist" hidden>
            <div class="page-head">
                <p class="page-lead">{{ __('messages.waitlist_lead') }}</p>
                <div class="page-actions">
                    <x-toggle name="include_past_waitlist" id="include-past-waitlist" :label="__('messages.include_past_events')" />
                </div>
            </div>
            {{-- Filled by loadWaitlist() the first time the tab is opened. --}}
            <div id="waitlist-table"></div>
        </div>
        @endif

        @if ($hasPro)
        <div id="feedback-panel" role="tabpanel" aria-labelledby="tab-feedback" hidden>
            {{-- Filled by loadFeedback() the first time the tab is opened. --}}
            <div id="feedback-table"></div>
        </div>
        @endif

        @if ($hasPro || $subscriptionsCount > 0)
        <div id="subscriptions-panel" role="tabpanel" aria-labelledby="tab-subscriptions" hidden>
            @include('ticket.subscriptions_table', ['subscriptions' => $subscriptions ?? collect()])
        </div>
        @endif

        @if ($hasPro || $installmentsCount > 0)
        <div id="installments-panel" role="tabpanel" aria-labelledby="tab-installments" hidden>
            @include('ticket.installments_table', [
                'installments' => $installments ?? collect(),
                'installmentTotals' => $installmentTotals ?? collect(),
                'installmentForecast' => $installmentForecast ?? collect(),
            ])
        </div>
        @endif

        @if ($hasPro || $giftCardsCount > 0)
        <div id="gift-cards-panel" role="tabpanel" aria-labelledby="tab-gift-cards" hidden>
            @include('ticket.gift_cards_table', ['giftCards' => $giftCards ?? collect()])
        </div>
        @endif

        {{-- Refund dialog. Plain markup driven by the plain DOM script below, like everything else
             on this page: asking for one number does not need a Vue mount. --}}
        <div id="refund-dialog" class="fixed inset-0 z-50 items-center justify-center p-4" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="refund-dialog-title">
            <div class="absolute inset-0 bg-black/50" data-refund-dismiss></div>
            <div class="ap-card relative w-full max-w-md rounded-xl p-6">
                <h2 id="refund-dialog-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.refund_ticket') }}</h2>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.refund_remaining') }}: <span id="refund-dialog-remaining" class="font-semibold text-gray-900 dark:text-white"></span>
                </p>

                <div class="mt-4">
                    <label for="refund-dialog-amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.refund_amount') }}</label>
                    <input type="number" step="0.01" min="0.01" id="refund-dialog-amount"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                    <p id="refund-dialog-error" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;"></p>
                </div>

                <div class="page-form-actions">
                    <button type="button" data-refund-dismiss class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.cancel') }}
                    </button>
                    <x-danger-button type="button" id="refund-dialog-confirm">
                        {{ __('messages.refund') }}
                    </x-danger-button>
                </div>
            </div>
        </div>
    </div>

<script {!! nonce_attr() !!}>
var salesSortBy = '{{ $sortBy ?? '' }}';
var salesSortDir = '{{ $sortDir ?? 'desc' }}';
var waitlistSortBy = '';
var waitlistSortDir = 'desc';
var feedbackSortBy = '';
var feedbackSortDir = 'desc';

@if ($hasTabs)
// The tabs. A tab is a button that shows its pane (id "tab-x" shows "x-panel"); the phone's
// dropdown presses the same buttons, so there is one way in.
var salesTabsWrap = document.getElementById('sales-tabs');
var salesTabSelect = document.getElementById('sales-tab-select');

function paintSalesTabs() {
    var strip = salesTabsWrap.querySelector('.ap-tabs');
    var start = Math.abs(strip.scrollLeft);
    salesTabsWrap.classList.toggle('more-before', start > 4);
    salesTabsWrap.classList.toggle('more-after', start + strip.clientWidth < strip.scrollWidth - 4);
}

function setActiveTab(activeId) {
    document.querySelectorAll('.sales-tab').forEach(function(tab) {
        var active = tab.id === activeId;
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
        var panel = document.getElementById(tab.getAttribute('aria-controls'));
        if (panel) {
            panel.hidden = ! active;
        }
        if (active) {
            var strip = salesTabsWrap.querySelector('.ap-tabs');
            strip.scrollLeft = tab.offsetLeft - (strip.clientWidth - tab.offsetWidth) / 2;
        }
    });
    salesTabSelect.value = activeId;
    paintSalesTabs();
}

document.querySelectorAll('.sales-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        setActiveTab(tab.id);
    });
});

salesTabSelect.addEventListener('change', function() {
    var tab = document.getElementById(salesTabSelect.value);
    if (tab) {
        tab.click();
    }
});

salesTabsWrap.querySelector('.ap-tabs').addEventListener('scroll', paintSalesTabs, { passive: true });
window.addEventListener('resize', paintSalesTabs);
window.addEventListener('load', paintSalesTabs);

@if ($hasPro || $giftCardsCount > 0)
// Gift card owner actions (mark paid / cancel / refund / resend email)
document.addEventListener('click', function(e) {
    var actionButton = e.target.closest('[data-gift-card-action], [data-gift-card-resend]');
    if (!actionButton) return;

    var confirmMessage = actionButton.getAttribute('data-confirm-message');
    if (confirmMessage && !window.confirm(confirmMessage)) {
        return;
    }

    var giftCardId = actionButton.getAttribute('data-gift-card-id');
    var isResend = actionButton.hasAttribute('data-gift-card-resend');
    var url = isResend
        ? '{{ route('gift_card.resend_email', ['gift_card_id' => ':id']) }}'.replace(':id', giftCardId)
        : '{{ route('gift_card.action', ['gift_card_id' => ':id']) }}'.replace(':id', giftCardId);

    actionButton.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify(isResend ? {} : { action: actionButton.getAttribute('data-gift-card-action') }),
    })
    .then(function(response) { return response.json().then(function(data) { return { ok: response.ok, data: data }; }); })
    .then(function(result) {
        if (!result.ok) {
            alert(result.data.error || @json(__('messages.error')));
            actionButton.disabled = false;
            return;
        }
        if (isResend) {
            actionButton.textContent = @json(__('messages.sent'));
        } else {
            window.location.href = '{{ route('sales', ['tab' => 'gift-cards']) }}';
        }
    })
    .catch(function() {
        alert(@json(__('messages.error')));
        actionButton.disabled = false;
    });
});
@endif

@if ($waitlistCount > 0)
document.getElementById('tab-waitlist').addEventListener('click', function() {
    if (!document.getElementById('waitlist-panel').dataset.loaded) {
        loadWaitlist();
        document.getElementById('waitlist-panel').dataset.loaded = '1';
    }
});

function loadWaitlist(url) {
    var fetchUrl = url || '{{ route("waitlist.index") }}';
    var u = new URL(fetchUrl, window.location.origin);
    if (waitlistSortBy) {
        u.searchParams.set('sort_by', waitlistSortBy);
        u.searchParams.set('sort_dir', waitlistSortDir);
    }
    if (document.getElementById('include-past-waitlist').checked) {
        u.searchParams.set('include_past', '1');
    }
    fetchUrl = u.toString();
    fetch(fetchUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Request failed');
        return response.text();
    })
    .then(html => {
        document.getElementById('waitlist-table').innerHTML = html;
        bindWaitlistPagination();
    })
    .catch(function() {
        document.getElementById('waitlist-table').innerHTML = salesLoadError;
    });
}

function bindWaitlistPagination() {
    document.querySelectorAll('#waitlist-table nav[role="navigation"] a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            loadWaitlist(this.href);
        });
    });
}

document.getElementById('include-past-waitlist').addEventListener('change', function() {
    loadWaitlist();
});

function handleWaitlistRemove(entryId) {
    if (!confirm(@json(__("messages.are_you_sure")))) return;

    fetch(`{{ url('/waitlist/remove') }}/${entryId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Request failed');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            loadWaitlist();
            Toastify({
                text: @json(__("messages.waitlist_removed")),
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: { background: '#4BB543' }
            }).showToast();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(@json(__("messages.an_error_occurred")));
    });
}
@endif

@if ($hasPro)
document.getElementById('tab-feedback').addEventListener('click', function() {
    if (!document.getElementById('feedback-panel').dataset.loaded) {
        loadFeedback();
        document.getElementById('feedback-panel').dataset.loaded = '1';
    }
});

function loadFeedback(url) {
    var fetchUrl = url || '{{ route("sales") }}?tab=feedback';
    if (feedbackSortBy) {
        var u = new URL(fetchUrl, window.location.origin);
        u.searchParams.set('sort_by', feedbackSortBy);
        u.searchParams.set('sort_dir', feedbackSortDir);
        fetchUrl = u.toString();
    }
    fetch(fetchUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Request failed');
        return response.text();
    })
    .then(html => {
        document.getElementById('feedback-table').innerHTML = html;
        bindFeedbackPagination();
    })
    .catch(error => {
        document.getElementById('feedback-table').innerHTML = salesLoadError;
    });
}

function bindFeedbackPagination() {
    document.querySelectorAll('#feedback-table nav[role="navigation"] a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var url = new URL(this.href);
            url.searchParams.set('tab', 'feedback');
            loadFeedback(url.toString());
        });
    });
}

function resendFeedbackEmail(saleId, btn) {
    if (btn) {
        btn.disabled = true;
    }
    fetch(`{{ url('/sales/resend-feedback') }}/${saleId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok && response.status !== 422) throw new Error('Request failed');
        return response.json();
    })
    .then(data => {
        Toastify({
            text: data.error || data.message || @json(__("messages.email_sent_successfully")),
            duration: 3000,
            position: 'center',
            stopOnFocus: true,
            style: { background: data.error ? '#FF0000' : '#4BB543' }
        }).showToast();
    })
    .catch(error => {
        console.error('Error:', error);
        Toastify({
            text: @json(__("messages.failed_to_send_email")),
            duration: 3000,
            position: 'center',
            stopOnFocus: true,
            style: { background: '#FF0000' }
        }).showToast();
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
        }
    });
}

document.addEventListener('click', function(e) {
    var btn = e.target.closest('[data-resend-feedback]');
    if (!btn) return;
    e.preventDefault();
    resendFeedbackEmail(btn.getAttribute('data-resend-feedback'), btn);
});
@endif

// What a pane says when its list could not be fetched.
var salesLoadError = '<div class="ap-card rounded-xl"><div class="page-empty is-compact"><h3>' + @json(e(__('messages.an_error_occurred'))) + '</h3></div></div>';

// Open the tab the address names (?tab=feedback), which is how the emails and the redirects
// after an action arrive here.
var tabFromUrl = document.getElementById('tab-' + (new URLSearchParams(window.location.search).get('tab') || ''));
if (tabFromUrl && tabFromUrl.classList.contains('sales-tab')) {
    tabFromUrl.click();
} else {
    paintSalesTabs();
}
@endif

document.addEventListener('click', function(e) {
    var header = e.target.closest('[data-sort]');
    if (!header) return;

    var panel = header.closest('[id$="-panel"], [id$="-table"]');
    if (!panel) return;
    var panelId = panel.id;

    var sortCol = header.getAttribute('data-sort');

    if (panelId === 'sales-panel' || panelId === 'sales-table') {
        salesSortDir = (salesSortBy === sortCol && salesSortDir === 'asc') ? 'desc' : 'asc';
        salesSortBy = sortCol;
        updateResults(document.getElementById('filter').value);
    } else if (panelId === 'waitlist-panel' || panelId === 'waitlist-table') {
        waitlistSortDir = (waitlistSortBy === sortCol && waitlistSortDir === 'asc') ? 'desc' : 'asc';
        waitlistSortBy = sortCol;
        loadWaitlist();
    } else if (panelId === 'feedback-panel' || panelId === 'feedback-table') {
        feedbackSortDir = (feedbackSortBy === sortCol && feedbackSortDir === 'asc') ? 'desc' : 'asc';
        feedbackSortBy = sortCol;
        loadFeedback();
    }
});

let timeoutId;
const filterInput = document.getElementById('filter');
const clearButton = document.getElementById('clear-filter');

// Export sales CSV.
// Wrapped in the SAME condition as the button itself (the row of filters above): without this, a
// free user hits a null dereference here and every listener registered below - the filter, the
// clear button, the include-past toggle, updateResults() - is never reached. Same pattern the
// Subscriptions and Gift Cards tab listeners already use.
@if ($hasPro)
document.getElementById('export-sales').addEventListener('click', function(e) {
    e.preventDefault();
    const filter = document.getElementById('filter').value;
    var exportUrl = '{{ route("sales.export") }}';
    var params = [];
    if (filter) params.push('filter=' + encodeURIComponent(filter));
    if (document.getElementById('include-past-sales').checked) params.push('include_past=1');
    if (params.length) exportUrl += '?' + params.join('&');
    window.location.href = exportUrl;
});
@endif

// Show/hide clear button based on input content
filterInput.addEventListener('input', function(e) {
    clearTimeout(timeoutId);
    clearButton.hidden = ! e.target.value;

    timeoutId = setTimeout(() => {
        updateResults(e.target.value);
    }, 500);
});

// Clear input and trigger search immediately
clearButton.addEventListener('click', function() {
    filterInput.value = '';
    clearButton.hidden = true;
    updateResults(''); // Call directly without timeout
    filterInput.focus();
});

// Show clear button if filter has a value on initial load
clearButton.hidden = ! filterInput.value;

// Toggle past events for sales
document.getElementById('include-past-sales').addEventListener('change', function() {
    updateResults(filterInput.value);
});

function updateResults(value) {
    var url = window.location.pathname + '?filter=' + encodeURIComponent(value);
    if (salesSortBy) {
        url += '&sort_by=' + encodeURIComponent(salesSortBy) + '&sort_dir=' + encodeURIComponent(salesSortDir);
    }
    if (document.getElementById('include-past-sales').checked) {
        url += '&include_past=1';
    }
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Request failed');
        return response.text();
    })
    .then(html => {
        const salesTable = document.getElementById('sales-table');
        if (salesTable) {
            salesTable.innerHTML = html;
        }
    });
}

function handleAction(saleId, action, refundAmount, idempotencyKey) {
    // The refund dialog has already confirmed, and asked for more than a yes/no.
    if (idempotencyKey === undefined && !confirm(@json(__("messages.are_you_sure")))) {
        return;
    }

    const payload = { action: action };
    if (refundAmount !== undefined && refundAmount !== null && refundAmount !== '') {
        payload.refund_amount = refundAmount;
    }
    // Sent so a resubmission of THIS request settles once. Without it the server mints a fresh key
    // per attempt, which cannot deduplicate anything and lets a double-click refund twice.
    if (idempotencyKey) {
        payload.idempotency_key = idempotencyKey;
    }

    fetch(`{{ url('/sales/action') }}/${saleId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.error || 'HTTP ' + response.status);
        }
        return data;
    })
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            // Refresh the table
            updateResults(document.getElementById('filter').value);

            // The server's own message wins where it sends one: only it knows whether a refund
            // moved money in full, in part, or merely changed a status on a rail that never held
            // any. Guessing from the action alone is what made "Successfully refunded ticket"
            // appear for years while nothing was refunded.
            var message = data.message || '';
            if (message) {
                // already decided
            } else if (action === 'mark_paid') {
                message = @json(__("messages.mark_paid_success"));
            } else if (action === 'refund') {
                message = @json(__("messages.refund_success"));
            } else if (action === 'cancel') {
                message = @json(__("messages.cancel_success"));
            } else if (action === 'delete') {
                message = @json(__("messages.delete_success"));
            }

            if (message) {
                Toastify({
                    text: message,
                    duration: 3000,
                    position: 'center',
                    stopOnFocus: true,
                    style: {
                        background: '#4BB543',
                    }
                }).showToast();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || @json(__("messages.an_error_occurred")));
    });
}

// A row that opens: the people a purchase named, what the buyer answered, a pass's visits, a
// gift card's history. The button names its rows (data-toggle-row="key", or data-sale-id on an
// order) and they carry the class detail-row-<key>.
document.addEventListener('click', function(e) {
    var button = e.target.closest('[data-toggle-row]');
    if (!button) return;

    var key = button.getAttribute('data-toggle-row') || button.getAttribute('data-sale-id');
    var open = button.getAttribute('aria-expanded') !== 'true';
    document.querySelectorAll('.detail-row-' + key).forEach(function(row) {
        row.classList.toggle('hidden', ! open);
    });
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
});

// The menu at the end of an order's row. The layout owns the menu itself (onPopUpClick places it
// under its button, keeps it on the screen and closes it on Escape or a click elsewhere); this
// only says which button was pressed, and then does what the chosen entry asks. Heard on the way
// DOWN and stopped there: the layout closes every menu on a click that reaches the document, so
// a click that got that far would close this one and then open it again.
document.addEventListener('click', function(e) {
    if (! e.target.closest) return;
    var toggle = e.target.closest('[data-popup-toggle]');
    if (! toggle) return;

    // One menu at a time: opening this one closes whichever was open.
    var menu = document.getElementById(toggle.getAttribute('data-popup-toggle'));
    if (menu && menu.classList.contains('hidden')) {
        hidePopUp();
    }

    onPopUpClick(toggle.getAttribute('data-popup-toggle'), {
        currentTarget: toggle,
        stopPropagation: function() { e.stopPropagation(); }
    });

    var resendId = toggle.getAttribute('data-resend-email');
    if (resendId) {
        resendEmail(resendId);
    }

    var saleAction = toggle.getAttribute('data-sale-action');
    if (saleAction) {
        var saleId = toggle.getAttribute('data-sale-id');

        // Only a gateway-backed refund asks for an amount. A status-only "mark as refunded" and
        // every other action keep the plain confirm they have always had.
        if (saleAction === 'refund' && toggle.getAttribute('data-refund-remaining')) {
            openRefundDialog(
                saleId,
                toggle.getAttribute('data-refund-remaining'),
                toggle.getAttribute('data-refund-remaining-formatted'),
                toggle.getAttribute('data-refund-decimals')
            );
        } else {
            handleAction(saleId, saleAction);
        }
    }
}, true);

var refundDialogSaleId = null;
var refundDialogMax = 0;
var refundDialogDecimals = 2;
var refundDialogKey = null;

function newIdempotencyKey() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return window.crypto.randomUUID();
    }

    return 'k' + Date.now() + Math.random().toString(36).slice(2);
}

// Half a minor unit of this sale's currency: 0.005 where cents exist, 0.5 for JPY. The server
// measures with the same figure, so "everything" means the same thing on both sides.
function refundDialogTolerance() {
    return 0.5 / Math.pow(10, refundDialogDecimals);
}

function openRefundDialog(saleId, remaining, remainingFormatted, decimals) {
    refundDialogSaleId = saleId;
    refundDialogMax = parseFloat(remaining);
    refundDialogDecimals = (decimals === undefined || decimals === null) ? 2 : parseInt(decimals, 10);
    // One key per opening of the dialog, so every submission of THIS refund carries the same one.
    refundDialogKey = newIdempotencyKey();

    var dialog = document.getElementById('refund-dialog');
    var amount = document.getElementById('refund-dialog-amount');

    document.getElementById('refund-dialog-remaining').textContent = remainingFormatted || remaining;
    document.getElementById('refund-dialog-error').style.display = 'none';
    document.getElementById('refund-dialog-confirm').disabled = false;

    // Defaults to the whole remaining balance, so the common case is one click and the partial
    // case is an edit rather than a calculation. Rounded to the currency's own precision: the
    // remainder can carry a third decimal, and a JPY sale has none at all.
    amount.value = refundDialogMax.toFixed(refundDialogDecimals);
    amount.step = refundDialogDecimals === 0 ? '1' : (1 / Math.pow(10, refundDialogDecimals)).toFixed(refundDialogDecimals);
    amount.max = refundDialogMax;

    dialog.style.display = 'flex';
    amount.focus();
    amount.select();
}

function closeRefundDialog() {
    document.getElementById('refund-dialog').style.display = 'none';
    refundDialogSaleId = null;
}

document.addEventListener('click', function (e) {
    if (e.target.closest('[data-refund-dismiss]')) {
        closeRefundDialog();
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && refundDialogSaleId) {
        closeRefundDialog();
    }
});

document.getElementById('refund-dialog-confirm').addEventListener('click', function () {
    var amount = parseFloat(document.getElementById('refund-dialog-amount').value);
    var error = document.getElementById('refund-dialog-error');
    var tolerance = refundDialogTolerance();

    // Checked here only to save a round trip; SaleRefundService re-asserts the ceiling under the
    // sale's lock, which is the check that actually counts.
    if (!(amount > 0) || amount - refundDialogMax > tolerance) {
        error.textContent = @json(__('messages.refund_amount_invalid'));
        error.style.display = 'block';
        return;
    }

    // Refunding the whole remainder sends NO amount, so the gateway gives back exactly what it
    // holds. The figure above is rounded to the currency's precision while the balance is stored
    // to three decimals, so naming it would leave a fraction behind and the sale would never
    // reach `refunded`.
    var isFullRemainder = Math.abs(amount - refundDialogMax) <= tolerance;

    // Guards the double-click. The key below makes a resubmission safe even if this does not fire.
    this.disabled = true;

    var saleId = refundDialogSaleId;
    var key = refundDialogKey;
    closeRefundDialog();
    handleAction(saleId, 'refund', isFullRemainder ? undefined : amount, key);
});

function resendEmail(saleId) {
    fetch(`{{ url('/sales/resend-email') }}/${saleId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Request failed');
        return response.json();
    })
    .then(data => {
        if (data.error) {
            Toastify({
                text: data.error,
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#FF0000',
                }
            }).showToast();
        } else {
            Toastify({
                text: data.message || @json(__("messages.email_sent_successfully")),
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#4BB543',
                }
            }).showToast();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toastify({
            text: @json(__("messages.failed_to_send_email")),
            duration: 3000,
            position: 'center',
            stopOnFocus: true,
            style: {
                background: '#FF0000',
            }
        }).showToast();
    });
}

// The text actions that sit in a row rather than in a menu: a guest's "Send email" and a
// waitlist entry's "Remove".
document.addEventListener('click', function (e) {
    if (! e.target.closest) return;
    var resendBtn = e.target.closest('.js-resend-email');
    if (resendBtn) { resendEmail(resendBtn.getAttribute('data-id')); return; }
    var waitlistBtn = e.target.closest('.js-waitlist-remove');
    if (waitlistBtn && typeof handleWaitlistRemove === 'function') { handleWaitlistRemove(waitlistBtn.getAttribute('data-id')); }
}, true);

</script>

</x-app-admin-layout>
