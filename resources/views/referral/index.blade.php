<x-app-admin-layout>

    <x-slot name="head">
        {{-- The link and its Copy button on one line, and the three steps as a numbered row. --}}
        <style {!! nonce_attr() !!}>
            .referral-link-row {
              display: flex;
              flex-wrap: wrap;
              gap: 0.5rem;
            }
            .referral-link-row input {
              flex: 1 1 14rem;
              min-width: 0;
            }
            .referral-steps {
              display: grid;
              grid-template-columns: repeat(3, minmax(0, 1fr));
              gap: 1.25rem;
              margin: 0;
              padding: 0;
              list-style: none;
            }
            .referral-step {
              display: flex;
              gap: 0.75rem;
            }
            .referral-step-number {
              display: flex;
              flex: none;
              align-items: center;
              justify-content: center;
              width: 1.75rem;
              height: 1.75rem;
              border-radius: 0.5rem;
              background: var(--ap-tint-2);
              font-size: 0.8125rem;
              font-weight: 600;
              color: rgb(var(--ap-ink-2));
            }
            .referral-step h3 {
              margin: 0.1875rem 0 0;
              font-size: 0.875rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .referral-step p {
              margin: 0.125rem 0 0;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            /* A chip that opens its cell has nothing to stand off from. */
            .event-chip.referral-plan {
              margin-inline-start: 0;
            }
            /* Written with the list's own selector: the kit sets a form in a row's last cell
               inline, for the one-word forms most rows hold. */
            .page-table .c-actions form.referral-apply {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: flex-end;
              gap: 0.5rem;
              white-space: normal;
            }
            @media (max-width: 767.98px) {
              .referral-steps {
                grid-template-columns: minmax(0, 1fr);
                gap: 1rem;
              }
            }
            @media (max-width: 639.98px) {
              .referral-link-row > button {
                flex: 1 1 100%;
              }
              .page-table .c-actions form.referral-apply {
                justify-content: flex-start;
              }
              /* The schedule picker (a select, or the box the searchable select puts in its place). */
              .referral-apply > select,
              .referral-apply > div {
                flex: 1 1 10rem;
              }
            }
        </style>
    </x-slot>

    @php
        // The name of a plan in the reader's language: it was printed as ucfirst('enterprise').
        $planName = fn ($plan) => in_array($plan, ['pro', 'enterprise'], true) ? __('messages.' . $plan) : ucfirst((string) $plan);
        $statusTones = ['subscribed' => 'is-info', 'qualified' => 'is-warn', 'credited' => 'is-on', 'expired' => 'is-bad'];
        $statusLabels = [
            'pending' => __('messages.pending'),
            'subscribed' => __('messages.status_subscribed'),
            'qualified' => __('messages.qualified'),
            'credited' => __('messages.credited'),
            'expired' => __('messages.expired'),
        ];
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.referral_program')" :lead="__('messages.referral_plan_page_description')" />

        <div class="page-stack">
            <x-page-flash :keys="['message' => 'success', 'error' => 'error']" />

            {{-- The link --}}
            <x-page-card id="referral-link" :title="__('messages.your_referral_link')" :lead="__('messages.referral_link_description')">
                <div class="referral-link-row">
                    <label for="referral-url-input" class="sr-only">{{ __('messages.your_referral_link') }}</label>
                    <input type="text" value="{{ $referralUrl }}" readonly dir="ltr"
                        id="referral-url-input"
                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <x-brand-button id="copy-referral-link" class="gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        <span id="copy-btn-text" aria-live="polite">{{ __('messages.copy_link') }}</span>
                    </x-brand-button>
                </div>
            </x-page-card>

            {{-- The four figures, as one strip: they were four cards, two rows of a phone. --}}
            <div id="referral-dashboard" class="ap-card rounded-xl page-stats is-auto">
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($totalReferrals) }}</div>
                    <div class="page-stat-label">{{ __('messages.total_referrals') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($awaitingSubscription) }}</div>
                    <div class="page-stat-label">{{ __('messages.awaiting_subscription') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($awaitingQualification) }}</div>
                    <div class="page-stat-label">{{ __('messages.awaiting_qualification') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value {{ $creditsEarned > 0 ? 'is-good' : '' }}">{{ number_format($creditsEarned) }}</div>
                    <div class="page-stat-label">{{ __('messages.credits_earned') }}</div>
                </div>
            </div>

            {{-- Credits waiting to be put on a schedule --}}
            @if ($qualifiedCredits->isNotEmpty())
            <x-page-card id="referral-credits" :title="__('messages.credits_ready_to_apply')" flush>
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.referral_credit') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.apply_credit') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($qualifiedCredits as $credit)
                        <tr>
                            <td class="c-main c-strong">
                                {{ plan_price($credit->plan_type === 'enterprise' ? $entMonthly : $proMonthly) }} {{ __('messages.credit') }}
                                <span class="event-chip">{{ $planName($credit->plan_type) }}</span>
                            </td>
                            <td class="c-actions">
                                <form action="{{ route('referrals.apply_credit') }}" method="POST" class="referral-apply">
                                    @csrf
                                    <input type="hidden" name="referral_id" value="{{ \App\Utils\UrlUtils::encodeId($credit->id) }}">
                                    <select name="role_id" required data-searchable aria-label="{{ __('messages.select_schedule') }}"
                                        class="w-56 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <option value="">{{ __('messages.select_schedule') }}</option>
                                        @foreach ($ownedRoles as $role)
                                        <option value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-brand-button type="submit">{{ __('messages.apply_credit') }}</x-brand-button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-page-card>
            @endif

            {{-- How it works --}}
            <x-page-card id="referral-how-it-works" :title="__('messages.how_it_works')">
                <ol class="referral-steps">
                    @foreach ([1, 2, 3] as $step)
                    <li class="referral-step">
                        <span class="referral-step-number" aria-hidden="true">{{ $step }}</span>
                        <div>
                            <h3>{{ __('messages.referral_step_' . $step . '_title') }}</h3>
                            <p>{{ __('messages.referral_step_' . $step . '_description') }}</p>
                        </div>
                    </li>
                    @endforeach
                </ol>
                <x-slot name="foot">
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        {{-- Pre-formatted by plan_price(), so the symbol follows the installation's
                             currency and a translator cannot desync it. This replaced a scheme where
                             each locale carried its own symbol and placement (English "$5", French
                             "5 $"); the trade-off is that every locale now prefixes, which is what
                             MoneyUtils::format() already does for every other price in the app. --}}
                        {{ __('messages.referral_credit_values', ['pro' => plan_price($proMonthly), 'enterprise' => plan_price($entMonthly)]) }}
                    </div>
                </x-slot>
            </x-page-card>

            {{-- Who signed up through the link, and where each one stands --}}
            @if ($referralHistory->isNotEmpty())
            <x-page-card id="referral-history" :title="__('messages.referral_history')" flush>
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.referred_user') }}</th>
                            <x-page-sort column="created_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.date') }}</x-page-sort>
                            <th scope="col">{{ __('messages.plan_tier') }}</th>
                            <x-page-sort column="status" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.status') }}</x-page-sort>
                            <th scope="col">{{ __('messages.credited_to') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referralHistory as $referral)
                        @php
                            // Enough of the address to recognise who it is, and no more.
                            $email = $referral->referredUser->email ?? '';
                            $parts = explode('@', $email);
                            $masked = $email === '' ? '' : substr($parts[0], 0, 2) . '***@' . ($parts[1] ?? '');
                        @endphp
                        <tr>
                            <td class="c-main c-strong c-wrap"><bdi dir="ltr">{{ $masked !== '' ? $masked : '-' }}</bdi></td>
                            <td class="c-date">{{ $referral->created_at->translatedFormat('M j, Y') }}</td>
                            <td>@if ($referral->plan_type)<span class="event-chip referral-plan">{{ $planName($referral->plan_type) }}</span>@endif</td>
                            <td>
                                <span class="event-status {{ $statusTones[$referral->status] ?? '' }}">{{ $statusLabels[$referral->status] ?? ucfirst((string) $referral->status) }}</span>
                            </td>
                            <td class="c-wrap" data-label="{{ __('messages.credited_to') }}">@if ($referral->creditedRole)<bdi>{{ $referral->creditedRole->name }}</bdi>@endif</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($referralHistory->hasPages())
                <x-slot name="foot">{{ $referralHistory->links() }}</x-slot>
                @endif
            </x-page-card>
            @endif
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('click', function(e) {
            var header = e.target.closest('[data-sort]');
            if (header) {
                var url = new URL(window.location.href);
                var currentSort = url.searchParams.get('sort_by') || 'created_at';
                var currentDir = url.searchParams.get('sort_dir') || 'desc';
                var sortBy = header.getAttribute('data-sort');
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        });

        // The label is read once: a second press inside the two seconds used to take "Copied!"
        // for the button's own name and leave it there.
        var copyText = document.getElementById('copy-btn-text');
        var copyLabel = copyText.textContent;
        var copyTimer = null;
        document.getElementById('copy-referral-link').addEventListener('click', function() {
            var input = document.getElementById('referral-url-input');
            navigator.clipboard.writeText(input.value).then(function() {
                copyText.textContent = @json(__('messages.link_copied'));
                clearTimeout(copyTimer);
                copyTimer = setTimeout(function() {
                    copyText.textContent = copyLabel;
                }, 2000);
            }).catch(function() {
                // No clipboard here (an old browser, a page not served over https): the link is
                // selected instead, ready to be copied by hand.
                input.focus();
                input.select();
            });
        });
    </script>

</x-app-admin-layout>
