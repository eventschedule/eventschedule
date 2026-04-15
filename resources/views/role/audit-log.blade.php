<x-app-admin-layout>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.audit_log') }}</h2>
            <x-secondary-link :href="route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])">
                {{ __('messages.back') }}
            </x-secondary-link>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('role.audit_log', ['subdomain' => $role->subdomain]) }}" class="ap-card rounded-xl shadow p-4">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1 min-w-0">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.category') }}</label>
                        <select name="category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                            <option value="">{{ __('messages.all') }}</option>
                            @foreach (['schedule', 'event', 'sale', 'subscription', 'boost'] as $cat)
                                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.from') }}</label>
                        <input type="text" name="from" value="{{ request('from') }}" class="datepicker-filter w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm" placeholder="{{ __('messages.from') }}" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.to') }}</label>
                        <input type="text" name="to" value="{{ request('to') }}" class="datepicker-filter w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm" placeholder="{{ __('messages.to') }}" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.search') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_audit_log') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                    </div>
                </div>
                <div class="flex items-end gap-2 shrink-0">
                    <x-brand-button type="submit">
                        {{ __('messages.filter') }}
                    </x-brand-button>
                    <x-secondary-link :href="route('role.audit_log', ['subdomain' => $role->subdomain])">
                        {{ __('messages.clear') }}
                    </x-secondary-link>
                </div>
            </div>
        </form>

        {{-- Results --}}
        <div class="ap-card rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <x-sortable-header column="created_at" :sortBy="$sortBy" :sortDir="$sortDir" class="px-4 py-3">{{ __('messages.time') }}</x-sortable-header>
                            <x-sortable-header column="user_id" :sortBy="$sortBy" :sortDir="$sortDir" class="px-4 py-3">{{ __('messages.user') }}</x-sortable-header>
                            <x-sortable-header column="action" :sortBy="$sortBy" :sortDir="$sortDir" class="px-4 py-3">{{ __('messages.action') }}</x-sortable-header>
                            <x-sortable-header column="metadata" :sortBy="$sortBy" :sortDir="$sortDir" class="px-4 py-3">{{ __('messages.details') }}</x-sortable-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $log->created_at->format('M j, Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                @if ($log->user)
                                    {{ $log->user->name }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">{{ __('messages.system') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
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
                                        'subscription.create' => __('messages.audit_subscription_created'),
                                        'subscription.swap' => __('messages.audit_subscription_changed'),
                                        'subscription.cancel' => __('messages.audit_subscription_cancelled'),
                                        'subscription.resume' => __('messages.audit_subscription_resumed'),
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
                                        default => $log->action,
                                    };
                                    $actionColor = match(explode('.', $log->action)[0] ?? '') {
                                        'event' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                                        'schedule' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'subscription' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        'boost' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                        'sale' => match($log->action) {
                                            'sale.paid', 'sale.checkout' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                            'sale.cancel' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                            'sale.refund' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
                                            'sale.expired' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                            default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        },
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $actionColor }}">
                                    {{ $actionLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
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
                                    {{ Str::limit($detailLabel, 80) }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('messages.no_audit_log_entries') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
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
                window.location.href = url.toString();
            }
        });
    </script>

</x-app-admin-layout>
