{{ __('messages.boost_statement_mail_subject', ['month' => $month]) }}

{{ __($statement->failure_code === 'no_card' ? 'messages.boost_statement_mail_no_card' : 'messages.boost_statement_mail_'.$statement->status) }}

@foreach ($statement->lines as $line)
{{ $line->campaign?->event?->translatedName() ?? $line->campaign?->event_name ?? __('messages.deleted_event') }}{{ $line->period !== $statement->period ? ' ('.__('messages.boost_statement_correction', ['month' => $line->period]).')' : '' }}: {{ __('messages.boost_statement_total_spend') }} {{ $money($line->spend) }}, {{ __('messages.boost_statement_total_fee') }} {{ $money($line->fee + $line->adjustment) }}
@endforeach

{{ __('messages.boost_statement_total_spend') }}: {{ $money($statement->total_spend) }}
{{ __('messages.boost_statement_total_fee') }}: {{ $money($statement->total_fee) }}
@if ((float) $statement->total_adjustments != 0)
{{ __('messages.boost_statement_adjustments') }}: {{ $money($statement->total_adjustments) }}
@endif
@if ((float) $statement->carried_in != 0)
{{ __('messages.boost_statement_carried_in') }}: {{ $money($statement->carried_in) }}
@endif

{{ __('messages.boost_statement_view') }}: {{ $url }}
