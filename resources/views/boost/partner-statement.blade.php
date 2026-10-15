<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // One month's fee: what was charged and why, campaign by campaign, with the corrections
        // Facebook made to earlier months on lines of their own. A charge that did not go through
        // can be paid from here, on the card in the Boosts settings.
        $bpMonthName = fn (string $period) => \Carbon\Carbon::createFromFormat('Y-m-d', $period.'-01')->translatedFormat('F Y');
        $bpRate = rtrim(rtrim(number_format((float) $partner->fee_rate * 100, 2, '.', ''), '0'), '.');
        $bpStateTones = ['paid' => 'is-on', 'pending' => 'is-info', 'carried' => '', 'failed' => 'is-bad', 'action_required' => 'is-warn'];
        $bpCurrency = $statement->currency;
        $bpMoney = fn ($amount) => \App\Utils\MoneyUtils::format($amount, $bpCurrency);
        $bpUnpaid = in_array($statement->status, ['failed', 'action_required'], true);
    @endphp

    <div class="page-shell">
        <x-page-header :title="__('messages.boost_statement_title', ['month' => $bpMonthName($statement->period)])"
            :lead="__('messages.boost_statement_lead', ['rate' => $bpRate]).' '.__('messages.currency').': '.$statement->currency"
            :back="route('boost.partner.statements')" :back-label="__('messages.boost_partner_tab_statements')">
            <x-slot name="actions">
                <span class="event-status {{ $bpStateTones[$statement->status] ?? '' }}">{{ __('messages.boost_statement_state_'.$statement->status) }}</span>
            </x-slot>
        </x-page-header>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            @if ($bpUnpaid)
            <x-page-notice :tone="$statement->status === 'failed' ? 'error' : 'warn'">
                {{ $statement->failure_code === 'no_card' ? __('messages.boost_statement_mail_no_card') : ($statement->status === 'failed' ? __('messages.boost_statement_failed_text') : __('messages.boost_statement_action_text')) }}
                <x-slot name="action">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('profile.edit') }}#section-boost-card" class="event-link">{{ __('messages.boost_partner_update_card') }}</a>
                        {{-- Pay now needs a card on file; without one the link above is the way on. --}}
                        @if ($partner->hasCard())
                        <form method="POST" action="{{ route('boost.partner.statement_pay', ['period' => $statement->period]) }}">
                            @csrf
                            <input type="hidden" name="currency" value="{{ $statement->currency }}">
                            <x-brand-button type="submit">{{ __('messages.pay_now') }}</x-brand-button>
                        </form>
                        @endif
                    </div>
                </x-slot>
            </x-page-notice>
            @endif

            <x-page-card :title="__('messages.boost_statement_summary')">
                <dl class="page-kv">
                    <div>
                        <dt>{{ __('messages.spend') }}</dt>
                        <dd>{{ $bpMoney($statement->total_spend) }}</dd>
                    </div>
                    <div>
                        <dt>{{ __('messages.boost_statement_fee_rate', ['rate' => $bpRate]) }}</dt>
                        <dd>{{ $bpMoney($statement->total_fee) }}</dd>
                    </div>
                    @if ((float) $statement->total_adjustments !== 0.0)
                    <div>
                        <dt>{{ __('messages.boost_statement_adjustments') }}</dt>
                        <dd>{{ $bpMoney($statement->total_adjustments) }}</dd>
                    </div>
                    @endif
                    @if ((float) $statement->carried_in !== 0.0)
                    <div>
                        <dt>{{ __('messages.boost_statement_carried_in') }}</dt>
                        <dd>{{ $bpMoney($statement->carried_in) }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt>{{ $statement->status === 'paid' ? __('messages.boost_statement_charged') : __('messages.boost_statement_due') }}</dt>
                        <dd>
                            {{ $bpMoney($statement->status === 'paid' ? $statement->charged_amount : $statement->amountDue()) }}
                            @if ($statement->status === 'paid' && $statement->charged_at)
                            <small>{{ $statement->charged_at->copy()->tz($partner->ad_account_timezone ?: config('app.timezone'))->translatedFormat('M j, Y') }}</small>
                            @endif
                        </dd>
                    </div>
                </dl>
                <x-slot name="foot">
                    {{ __('messages.boost_statement_timezone', ['timezone' => $partner->ad_account_timezone ?: 'UTC']) }}
                    @if ($statement->status === 'carried')
                    {{ __('messages.boost_statement_carried_text') }}
                    @endif
                </x-slot>
            </x-page-card>

            @if ($statement->lines->isNotEmpty())
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table is-wide">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('messages.event') }}</th>
                                <th scope="col">{{ __('messages.boost_partner_month') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.spend') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.boost_statement_total_fee') }}</th>
                                <th scope="col" class="c-num">{{ __('messages.boost_statement_adjustment') }}</th>
                                <th scope="col">{{ __('messages.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statement->lines as $bpLine)
                            @php $bpLineCampaign = $bpLine->campaign; @endphp
                            <tr>
                                <td class="c-main c-strong">
                                    @if ($bpLineCampaign)
                                    <a href="{{ route('boost.show', ['hash' => $bpLineCampaign->hashedId()]) }}" class="event-link"><bdi>{{ $bpLineCampaign->event?->translatedName() ?? $bpLineCampaign->event_name ?? __('messages.deleted_event') }}</bdi></a>
                                    @if (filled($bpLineCampaign->schedule_name))
                                    <span class="c-sub"><bdi>{{ $bpLineCampaign->schedule_name }}</bdi></span>
                                    @endif
                                    @else
                                    {{ __('messages.deleted_event') }}
                                    @endif
                                </td>
                                <td class="c-quiet" data-label="{{ __('messages.boost_partner_month') }}">{{ $bpMonthName($bpLine->period) }}</td>
                                <td class="c-num" data-label="{{ __('messages.spend') }}">{{ $bpMoney($bpLine->spend) }}</td>
                                <td class="c-num" data-label="{{ __('messages.boost_statement_total_fee') }}">{{ $bpMoney($bpLine->fee) }}</td>
                                <td class="c-num" data-label="{{ __('messages.boost_statement_adjustment') }}">@if ((float) $bpLine->adjustment !== 0.0){{ $bpMoney($bpLine->adjustment) }}@endif</td>
                                {{-- A correction is a line for an earlier month than the statement's, with
                                     an amount: it is said from a key, in the reader's language, since
                                     the line stores no words for it. --}}
                                <td class="c-quiet c-wrap" data-label="{{ __('messages.notes') }}">@if ((float) $bpLine->adjustment !== 0.0 && $bpLine->period !== $statement->period){{ __('messages.boost_statement_correction', ['month' => $bpMonthName($bpLine->period)]) }}@elseif (filled($bpLine->note))<bdi>{{ $bpLine->note }}</bdi>@endif</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="__('messages.boost_statement_no_lines')" />
            </div>
            @endif
        </div>
    </div>

</x-app-admin-layout>
