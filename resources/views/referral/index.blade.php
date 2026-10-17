<x-app-admin-layout>

    <x-slot name="head">
        {{-- A credit waiting to be spent first, then the reward and the link, then one track that is
             How it works for a newcomer and the count at each step for everybody else, then the
             history, each row saying what comes next. --}}
        <style {!! nonce_attr() !!}>
            .referral-share-head {
              display: flex;
              align-items: flex-start;
              gap: 0.875rem;
              margin-bottom: 1rem;
            }
            .referral-share-head .dashboard-icon {
              flex: none;
            }
            .referral-headline {
              margin: 0;
              font-size: 1.125rem;
              font-weight: 600;
              line-height: 1.5rem;
              color: rgb(var(--ap-ink));
            }
            .referral-share-line {
              max-width: 44rem;
              margin: 0.25rem 0 0;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
            .referral-link-row {
              display: flex;
              flex-wrap: wrap;
              gap: 0.5rem;
            }
            .referral-link-row input {
              flex: 1 1 14rem;
              min-width: 0;
            }

            /* Four steps on one line, joined from each number to the next. */
            .referral-track {
              display: grid;
              grid-template-columns: repeat(4, minmax(0, 1fr));
              gap: 1.25rem;
              margin: 0;
              padding: 0;
              list-style: none;
            }
            .referral-step {
              position: relative;
            }
            .referral-step:not(:last-child)::after {
              content: '';
              position: absolute;
              top: 0.875rem;
              inset-inline-start: 2.5rem;
              inset-inline-end: -0.5rem;
              height: 1px;
              background: rgb(var(--ap-border));
            }
            .referral-step-number {
              display: flex;
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
            /* A step somebody is on. */
            .referral-step.is-live .referral-step-number {
              background: var(--brand-button-bg);
              color: #fff;
            }
            .referral-step-body {
              margin-top: 0.625rem;
              min-width: 0;
            }
            .referral-step-figure {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0 0.375rem;
              margin: 0 0 0.25rem;
            }
            .referral-step-count {
              font-size: 1.5rem;
              font-weight: 700;
              line-height: 1.875rem;
              font-variant-numeric: tabular-nums;
              color: rgb(var(--ap-ink));
            }
            .referral-step-count.is-zero {
              color: rgb(var(--ap-ink-3));
              font-weight: 600;
            }
            .referral-step-caption {
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .referral-step h3 {
              margin: 0;
              font-size: 0.875rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .referral-step p.referral-step-text {
              margin: 0.125rem 0 0;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .referral-step p.referral-step-next {
              margin: 0.375rem 0 0;
              font-size: 0.8125rem;
              font-weight: 500;
              color: var(--brand-blue);
            }

            /* A credit: what it is worth, where it goes, and what that will do. */
            .referral-credit-value {
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .referral-credit-what {
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
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
            .referral-outcome,
            .referral-none {
              flex: 1 1 100%;
              margin: 0;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
              white-space: normal;
            }
            .referral-none {
              display: flex;
              flex-wrap: wrap;
              justify-content: flex-end;
              gap: 0.25rem 0.75rem;
            }
            .page-table .referral-detail {
              color: rgb(var(--ap-ink-2));
            }

            @media (max-width: 767.98px) {
              /* The track runs down the page: the number at the start, its words beside it. */
              .referral-track {
                grid-template-columns: minmax(0, 1fr);
                gap: 0;
              }
              .referral-step {
                display: flex;
                gap: 0.75rem;
                padding-bottom: 1rem;
              }
              .referral-step:last-child {
                padding-bottom: 0;
              }
              .referral-step-number {
                flex: none;
              }
              .referral-step:not(:last-child)::after {
                top: 2.25rem;
                bottom: 0.5rem;
                inset-inline-start: 0.875rem;
                inset-inline-end: auto;
                width: 1px;
                height: auto;
              }
              .referral-step-body {
                margin-top: 0.1875rem;
              }
            }
            @media (max-width: 639.98px) {
              .referral-link-row > button {
                flex: 1 1 100%;
              }
              .page-table .c-actions form.referral-apply,
              .referral-none {
                justify-content: flex-start;
              }
              /* The schedule picker (a select, or the box the searchable select puts in its place). */
              .referral-apply > select,
              .referral-apply > div {
                flex: 1 1 10rem;
              }
              .page-table .referral-detail {
                flex: 1 1 100%;
                color: rgb(var(--ap-ink-3));
              }
            }
        </style>
    </x-slot>

    @php
        // The name of a plan in the reader's language: it was printed as ucfirst('enterprise').
        $planName = fn ($plan) => in_array($plan, ['pro', 'enterprise'], true) ? __('messages.' . $plan) : ucfirst((string) $plan);
        $day = fn ($date) => $date->translatedFormat('M j, Y');
        // The words of the track's steps, never "Qualified" or "Credited".
        $statusTones = ['subscribed' => 'is-info', 'qualified' => 'is-warn', 'credited' => 'is-on', 'expired' => 'is-bad'];
        $statusLabels = [
            'pending' => __('messages.referral_status_pending'),
            'subscribed' => __('messages.status_subscribed'),
            'qualified' => __('messages.referral_status_qualified'),
            'credited' => __('messages.referral_status_credited'),
            'expired' => __('messages.expired'),
        ];
        $hasReferrals = $totalReferrals > 0;
        $trackLead = $hasReferrals
            ? __('messages.referral_track_total', ['count' => number_format($totalReferrals)])
                . ($expiredCount > 0 ? ' · ' . __('messages.referral_track_expired', ['count' => number_format($expiredCount)]) : '')
            : null;
        // With one schedule there is nothing to choose: it is chosen, and what it gets is said.
        $onlyRole = $ownedRoles->count() === 1 ? $ownedRoles->first() : null;
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.referral_program')" />

        <div class="page-stack">
            <x-page-flash :keys="['message' => 'success', 'error' => 'error']" />

            {{-- A credit waiting to be spent: the email and the push that announce it link here. --}}
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
                            <td class="c-main">
                                <div class="referral-credit-value">{{ plan_price(\App\Services\ReferralCreditService::amount($credit->plan_type)) }} {{ __('messages.credit') }}</div>
                                <div class="referral-credit-what">{{ __('messages.referral_credit_month', ['plan' => $planName(\App\Services\ReferralCreditService::tier($credit->plan_type))]) }}</div>
                            </td>
                            <td class="c-actions">
                                @if ($ownedRoles->isEmpty())
                                <p class="referral-none">
                                    <span>{{ __('messages.referral_no_schedule') }}</span>
                                    @foreach (['talent', 'venue', 'curator'] as $type)
                                    <x-link href="{{ route('new', ['type' => $type]) }}" class="referral-make">{{ __('messages.' . $type) }}</x-link>
                                    @endforeach
                                </p>
                                @else
                                <form action="{{ route('referrals.apply_credit') }}" method="POST" class="referral-apply">
                                    @csrf
                                    <input type="hidden" name="referral_id" value="{{ \App\Utils\UrlUtils::encodeId($credit->id) }}">
                                    <select name="role_id" required data-searchable data-referral-pick="referral-outcome-{{ $credit->id }}" aria-label="{{ __('messages.select_schedule') }}" aria-describedby="referral-outcome-{{ $credit->id }}" v-pre
                                        class="w-56 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <option value="">{{ __('messages.select_schedule') }}</option>
                                        @foreach ($ownedRoles as $role)
                                        <option value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}" data-outcome="{{ $outcomes[$credit->id][$role->id] }}" @selected($onlyRole !== null)>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-brand-button type="submit">{{ __('messages.apply_credit') }}</x-brand-button>
                                    <p class="referral-outcome" id="referral-outcome-{{ $credit->id }}" aria-live="polite" data-empty="{{ __('messages.referral_apply_pick') }}" v-pre>{{ $onlyRole ? $outcomes[$credit->id][$onlyRole->id] : __('messages.referral_apply_pick') }}</p>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-page-card>
            @endif

            {{-- The reward, then the link --}}
            <x-page-card id="referral-link">
                <div class="referral-share-head">
                    <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10" style="--icon-glow: rgba(59, 130, 246, 0.15)">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="referral-headline">{{ __('messages.referral_share_headline') }}</h2>
                        {{-- Pre-formatted by plan_price(), so the symbol follows the installation's
                             currency and a translator cannot desync it. --}}
                        <p class="referral-share-line">{{ __('messages.referral_share_line', ['pro' => plan_price($proMonthly), 'enterprise' => plan_price($entMonthly)]) }}</p>
                    </div>
                </div>
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

            {{-- How it works, and once there is a referral, how many are at each step. Each referral
                 is counted once, where it stands now; the four figures this replaced left out a
                 credit waiting to be spent and one that had expired. --}}
            <x-page-card id="referral-dashboard" :title="$hasReferrals ? __('messages.your_referrals') : __('messages.how_it_works')" :lead="$trackLead">
                <ol class="referral-track">
                    @foreach ([1, 2, 3, 4] as $step)
                    <li class="referral-step{{ $hasReferrals && $stepCounts[$step - 1] > 0 ? ' is-live' : '' }}">
                        <span class="referral-step-number" aria-hidden="true">{{ $step }}</span>
                        <div class="referral-step-body">
                            @if ($hasReferrals)
                            <p class="referral-step-figure">
                                <span class="referral-step-count{{ $stepCounts[$step - 1] === 0 ? ' is-zero' : '' }}">{{ number_format($stepCounts[$step - 1]) }}</span>
                                <span class="referral-step-caption">{{ __('messages.referral_track_' . $step . '_count') }}</span>
                            </p>
                            @endif
                            <h3>{{ __('messages.referral_track_' . $step . '_title') }}</h3>
                            <p class="referral-step-text">{{ __('messages.referral_track_' . $step . '_description') }}</p>
                            @if ($step === 2 && $nextCreditAt)
                            <p class="referral-step-next">{{ __('messages.referral_next_credit', ['date' => $day($nextCreditAt)]) }}</p>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ol>
                <x-slot name="foot">{{ __('messages.referral_fine_print') }}</x-slot>
            </x-page-card>

            {{-- Who signed up through the link, where each one stands, and what comes next --}}
            @if ($referralHistory->isNotEmpty())
            <x-page-card id="referral-history" :title="__('messages.referral_history')" flush>
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.referred_user') }}</th>
                            <x-page-sort column="created_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.date') }}</x-page-sort>
                            <x-page-sort column="status" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.status') }}</x-page-sort>
                            <th scope="col">{{ __('messages.details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referralHistory as $referral)
                        @php
                            // Enough of the address to recognise who it is, and no more.
                            $email = $referral->referredUser->email ?? '';
                            $parts = explode('@', $email);
                            $masked = $email === '' ? '' : substr($parts[0], 0, 2) . '***@' . ($parts[1] ?? '');

                            // Escaped here, so the one piece of markup (the schedule's name, set
                            // apart for a right-to-left page) can be put in after.
                            $detail = match ($referral->status) {
                                'pending' => e(__('messages.referral_detail_pending', ['date' => $day($referral->created_at->copy()->addDays(\App\Services\ReferralCreditService::PENDING_DAYS))])),
                                'subscribed' => $referral->subscribed_at
                                    ? e(__('messages.referral_detail_subscribed', ['date' => $day($referral->subscribed_at->copy()->addDays(\App\Services\ReferralCreditService::DAYS))]))
                                    : '',
                                'qualified' => '<a href="#referral-credits" class="event-link">' . e(__('messages.referral_detail_qualified')) . '</a>',
                                'credited' => $referral->creditedRole
                                    ? str_replace('%SCHEDULE%', '<bdi>' . e($referral->creditedRole->name) . '</bdi>', e(__('messages.referral_detail_credited', ['schedule' => '%SCHEDULE%', 'date' => $day($referral->credited_at ?? $referral->updated_at)])))
                                    : e(__('messages.referral_detail_credited_gone', ['date' => $day($referral->credited_at ?? $referral->updated_at)])),
                                'expired' => e(__($referral->subscribed_at ? 'messages.referral_detail_ended_early' : 'messages.referral_detail_never_subscribed')),
                                default => '',
                            };
                        @endphp
                        <tr>
                            <td class="c-main c-strong c-wrap"><bdi dir="ltr">{{ $masked !== '' ? $masked : '-' }}</bdi></td>
                            <td class="c-date">{{ $day($referral->created_at) }}</td>
                            <td>
                                <span class="{{ trim('event-status ' . ($statusTones[$referral->status] ?? '')) }}">{{ $statusLabels[$referral->status] ?? ucfirst((string) $referral->status) }}</span>
                                @if ($referral->plan_type)<span class="event-chip">{{ $planName($referral->plan_type) }}</span>@endif
                            </td>
                            <td class="c-wrap referral-detail" v-pre>{!! $detail !!}</td>
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

        // The line under a credit's picker says what Apply will do on the schedule picked. Each
        // option carries its own sentence, worked out by the same rule the apply follows.
        document.querySelectorAll('select[data-referral-pick]').forEach(function(select) {
            var line = document.getElementById(select.getAttribute('data-referral-pick'));
            select.addEventListener('change', function() {
                var option = select.options[select.selectedIndex];
                line.textContent = option && option.getAttribute('data-outcome')
                    ? option.getAttribute('data-outcome')
                    : line.getAttribute('data-empty');
            });
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
