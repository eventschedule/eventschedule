<x-app-admin-layout>

    {{-- What has been done on one schedule, newest first. It hangs from the schedule, so the way
         back names it; the filters are one row, and the list is the kit's (a stack of rows on a
         phone, where the table ran off the edge). --}}
    <div class="page-shell">
        <x-page-header :title="__('messages.audit_log')" :lead="__('messages.audit_log_lead')"
            :back="route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])" :back-label="$role->name" />

        @php
            // The categories were printed as their keys with a capital letter, in English, in
            // every language.
            $categories = ['boost' => __('messages.boost'), 'event' => __('messages.event'), 'sale' => __('messages.sales'), 'schedule' => __('messages.schedule'), 'subscription' => __('messages.subscription')];
        @endphp
        <form method="GET" action="{{ route('role.audit_log', ['subdomain' => $role->subdomain]) }}" class="page-filters">
            <label class="page-filter">
                <span>{{ __('messages.category') }}</span>
                <select name="category" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                    <option value="">{{ __('messages.all') }}</option>
                    @foreach ($categories as $cat => $catLabel)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $catLabel }}</option>
                    @endforeach
                </select>
            </label>
            <div class="page-filter-pair">
                <label class="page-filter">
                    <span>{{ __('messages.from') }}</span>
                    <input type="text" name="from" value="{{ request('from') }}" class="datepicker-filter w-36 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm" placeholder="{{ __('messages.from') }}">
                </label>
                <label class="page-filter">
                    <span>{{ __('messages.to') }}</span>
                    <input type="text" name="to" value="{{ request('to') }}" class="datepicker-filter w-36 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm" placeholder="{{ __('messages.to') }}">
                </label>
            </div>
            <label class="page-filter is-grow">
                <span>{{ __('messages.search') }}</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_action_or_details') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
            </label>
            <div class="is-end">
                @if (request()->hasAny(['category', 'from', 'to', 'search']) && collect(request()->only(['category', 'from', 'to', 'search']))->filter()->isNotEmpty())
                <x-secondary-link :href="route('role.audit_log', ['subdomain' => $role->subdomain])">{{ __('messages.clear') }}</x-secondary-link>
                @endif
                <x-brand-button type="submit">{{ __('messages.filter') }}</x-brand-button>
            </div>
        </form>

        @if ($logs->isEmpty())
        <div class="ap-card rounded-xl">
            <x-page-empty :title="__('messages.no_audit_log_entries')"
                icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
        </div>
        @else
        <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table">
                    <thead>
                        <tr>
                            <x-page-sort column="created_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.time') }}</x-page-sort>
                            <x-page-sort column="user_id" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.user') }}</x-page-sort>
                            <x-page-sort column="action" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.action') }}</x-page-sort>
                            <x-page-sort column="metadata" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.details') }}</x-page-sort>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                        <tr>
                            <td class="c-date">{{ $log->created_at->translatedFormat('M j, Y H:i:s') }}</td>
                            <td class="c-main c-strong">
                                @if ($log->user)
                                    <bdi>{{ $log->user->name }}</bdi>
                                @else
                                    <span class="c-quiet">{{ __('messages.system') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionLabel = match($log->action) {
                                        'event.create' => __('messages.audit_event_created'),
                                        'event.update' => __('messages.audit_event_updated'),
                                        'event.delete' => __('messages.audit_event_deleted'),
                                        'event.accept' => __('messages.audit_event_accepted'),
                                        'event.decline' => __('messages.audit_event_declined'),
                                        'event.publish' => __('messages.audit_event_published'),
                                        'schedule.create' => __('messages.audit_schedule_created'),
                                        'schedule.update' => __('messages.audit_schedule_updated'),
                                        'schedule.delete' => __('messages.audit_schedule_deleted'),
                                        'schedule.member_add' => __('messages.audit_member_added'),
                                        'schedule.member_remove' => __('messages.audit_member_removed'),
                                        'schedule.feed_add' => __('messages.audit_feed_added'),
                                        'schedule.feed_update' => __('messages.audit_feed_updated'),
                                        'schedule.feed_remove' => __('messages.audit_feed_removed'),
                                        'schedule.video_remove' => __('messages.audit_video_removed'),
                                        'schedule.transfer_initiate' => __('messages.audit_transfer_initiated'),
                                        'schedule.transfer_accept' => __('messages.audit_transfer_accepted'),
                                        'schedule.transfer_decline' => __('messages.audit_transfer_declined'),
                                        'schedule.transfer_cancel' => __('messages.audit_transfer_cancelled'),
                                        'subscription.create' => __('messages.audit_subscription_created'),
                                        'subscription.swap' => __('messages.audit_subscription_changed'),
                                        'subscription.cancel' => __('messages.audit_subscription_cancelled'),
                                        'subscription.resume' => __('messages.audit_subscription_resumed'),
                                        'subscription.ticket_trial_start' => __('messages.audit_ticket_trial_started'),
                                        'boost.create' => __('messages.audit_boost_created'),
                                        'boost.pause' => __('messages.audit_boost_paused'),
                                        'boost.resume' => __('messages.audit_boost_resumed'),
                                        'boost.cancel' => __('messages.audit_boost_cancelled'),
                                        'sale.checkout' => __('messages.checkout'),
                                        'sale.paid' => __('messages.paid'),
                                        'sale.cancel' => __('messages.cancelled'),
                                        'sale.refund' => __('messages.refunded'),
                                        'sale.checkin' => __('messages.checked_in'),
                                        'sale.expired' => __('messages.expired'),
                                        'sale.installment_paid' => __('messages.audit_installment_paid'),
                                        'sale.installment_failed' => __('messages.audit_installment_failed'),
                                        'sale.seat_blocked' => __('messages.audit_seat_blocked'),
                                        'sale.seat_unblocked' => __('messages.audit_seat_unblocked'),
                                        'sale.seat_released' => __('messages.audit_seat_released'),
                                        'sale.seat_exchanged' => __('messages.audit_seat_exchanged'),
                                        'sale.seat_booked' => __('messages.audit_seat_booked'),
                                        default => $log->action,
                                    };
                                    // The kit's status mark, by what the entry means: something went
                                    // through, something was called off or removed, something needs a
                                    // look. Everything else is quiet. The pill used to be coloured by
                                    // CATEGORY, so a cancelled order and a paid one could not be told
                                    // apart from across the room, and a new event was cyan for no reason.
                                    $actionTone = match($log->action) {
                                        'sale.paid', 'sale.checkout', 'sale.checkin', 'sale.installment_paid', 'sale.seat_booked',
                                        'event.accept', 'event.publish', 'schedule.transfer_accept',
                                        'subscription.create', 'subscription.resume' => 'is-on',
                                        'sale.cancel', 'sale.seat_released', 'event.delete', 'event.decline', 'schedule.delete',
                                        'schedule.member_remove', 'schedule.feed_remove', 'schedule.transfer_decline', 'schedule.transfer_cancel',
                                        'subscription.cancel', 'boost.cancel' => 'is-bad',
                                        'sale.expired', 'sale.installment_failed' => 'is-warn',
                                        default => '',
                                    };
                                @endphp
                                <span class="event-status {{ $actionTone }}">{{ $actionLabel }}</span>
                            </td>
                            <td class="c-quiet c-wrap">
                                @php
                                    $actionPrefix = explode('.', $log->action)[0] ?? '';
                                    if (in_array($actionPrefix, ['event', 'schedule', 'subscription', 'boost'])) {
                                        $detailLabel = $log->metadata ?? '';
                                    } else {
                                        $detail = $log->metadata ?? '';
                                        $detail = preg_replace('/event_id:\d+/', '', $detail);
                                        $detail = trim($detail, ': ');
                                        $detailLabel = match($detail) {
                                            'stripe', 'stripe_checkout' => 'Stripe',
                                            'stripe_amount_mismatch', 'stripe_checkout_amount_mismatch' => 'Stripe (' . __('messages.amount_mismatch') . ')',
                                            'invoiceninja', 'invoiceninja_purchase', 'invoiceninja_event_purchase' => 'Invoice Ninja',
                                            'invoiceninja_amount_mismatch' => 'Invoice Ninja (' . __('messages.amount_mismatch') . ')',
                                            'rsvp_cancel' => 'RSVP',
                                            'guest_cancel', 'payment_url_cancel' => __('messages.guest'),
                                            'payment_url' => __('messages.payment_link'),
                                            'auto_expire' => __('messages.automatic'),
                                            'cancel', 'refund', 'mark_paid', 'delete' => '',
                                            default => $detail,
                                        };
                                    }
                                @endphp
                                @if ($detailLabel)
                                    <bdi>{{ Str::limit($detailLabel, 80) }}</bdi>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            var localeConfig = fpLocale ? { locale: fpLocale } : {};
            document.querySelectorAll('.datepicker-filter').forEach(function(el) {
                flatpickr(el, Object.assign({
                    allowInput: true,
                    enableTime: false,
                    altInput: true,
                    altFormat: "M j, Y",
                    dateFormat: "Y-m-d",
                }, localeConfig));
            });
        });
        document.addEventListener('click', function(e) {
            var header = e.target.closest('[data-sort]');
            if (header) {
                var url = new URL(window.location.href);
                var currentSort = url.searchParams.get('sort_by') || 'created_at';
                var currentDir = url.searchParams.get('sort_dir') || 'desc';
                var sortBy = header.getAttribute('data-sort');
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
                // A new order starts at its first page: page 3 of the old order is no place in the new one.
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        });
    </script>

</x-app-admin-layout>
