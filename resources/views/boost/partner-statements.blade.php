<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // The platform's fee, month by month: a share of what the partner's boosts spent on
        // Facebook, charged to the card in the Boosts settings. This month is an estimate until it
        // closes, so it stands apart from the statements.
        $bpRate = rtrim(rtrim(number_format((float) $partner->fee_rate * 100, 2, '.', ''), '0'), '.');
        $bpStateTones = ['paid' => 'is-on', 'pending' => 'is-info', 'carried' => '', 'failed' => 'is-bad', 'action_required' => 'is-warn'];
        $bpMonthName = fn (string $period) => \Carbon\Carbon::createFromFormat('Y-m-d', $period.'-01')->translatedFormat('F Y');
        // A statement is one month in one currency: an account that moved to an ad account in
        // another currency has two for that month, each named by its currency.
        $bpManyCurrencies = $statements->pluck('currency')->unique()->count() > 1;
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.boost')" :lead="__('messages.boost_partner_lead')" />

        @include('boost.partials.partner-nav', ['tab' => 'statements'])

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            @if (! $partner->hasCard())
            <x-page-notice tone="warn">
                {{ __('messages.boost_statement_no_card') }}
                <x-slot name="action"><a href="{{ route('profile.edit') }}#section-boost-card" class="event-link">{{ __('messages.boost_partner_add_card') }}</a></x-slot>
            </x-page-notice>
            @endif

            <x-page-card :title="__('messages.boost_statement_this_month')"
                :lead="__('messages.boost_statement_estimate_lead', ['month' => $bpMonthName($estimate['period']), 'rate' => $bpRate])">
                <dl class="page-kv">
                    <div>
                        <dt>{{ __('messages.spend') }}</dt>
                        <dd>{{ \App\Utils\MoneyUtils::format($estimate['spend'], $estimate['currency']) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('messages.boost_statement_fee_rate', ['rate' => $bpRate]) }}</dt>
                        <dd>{{ \App\Utils\MoneyUtils::format($estimate['fee'], $estimate['currency']) }}</dd>
                    </div>
                </dl>
            </x-page-card>

            @if ($statements->isNotEmpty())
            <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table is-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.boost_partner_month') }}</th>
                            <th scope="col">{{ __('messages.status') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.spend') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.boost_statement_total_fee') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.boost_statement_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statements as $bpStatement)
                        <tr>
                            <td class="c-main c-strong">
                                <a href="{{ route('boost.partner.statement', ['period' => $bpStatement->period, 'currency' => $bpStatement->currency]) }}" class="event-link">{{ $bpMonthName($bpStatement->period) }}</a>
                                @if ($bpManyCurrencies)
                                <span class="c-sub" dir="ltr">{{ $bpStatement->currency }}</span>
                                @endif
                            </td>
                            <td><span class="event-status {{ $bpStateTones[$bpStatement->status] ?? '' }}">{{ __('messages.boost_statement_state_'.$bpStatement->status) }}</span></td>
                            <td class="c-num" data-label="{{ __('messages.spend') }}">{{ \App\Utils\MoneyUtils::format($bpStatement->total_spend, $bpStatement->currency) }}</td>
                            <td class="c-num" data-label="{{ __('messages.boost_statement_total_fee') }}">{{ \App\Utils\MoneyUtils::format($bpStatement->total_fee, $bpStatement->currency) }}</td>
                            <td class="c-num" data-label="{{ __('messages.boost_statement_amount') }}">{{ \App\Utils\MoneyUtils::format($bpStatement->status === 'paid' ? $bpStatement->charged_amount : $bpStatement->amountDue(), $bpStatement->currency) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.boost_statement_none')" :text="__('messages.boost_statement_none_text')"
                    icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </div>
            @endif
        </div>
    </div>

</x-app-admin-layout>
