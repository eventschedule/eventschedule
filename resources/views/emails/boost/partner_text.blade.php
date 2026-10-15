{{ __('messages.boost_partner_mail_'.$kind.'_subject', $replace) }}

{{ __($bodyKey, $replace) }}
@if ($kind === 'declined' && $campaign?->approval_note)

{{ __('messages.boost_partner_note_from', ['page' => $replace['page']]) }}
{{ $campaign->approval_note }}
@endif
@if ($kind === 'approved' && $campaign && ! $campaign->isPaidCurrent() && $partner?->payment_instructions)

{{ __('messages.boost_partner_how_to_pay', ['page' => $replace['page']]) }}
{{ $partner->payment_instructions }}
@endif
@if ($kind === 'rejected' && $campaign?->meta_rejection_reason)

{{ __('messages.boost_partner_reason') }}: {{ $campaign->meta_rejection_reason }}
@endif
@if ($kind === 'stopped' && $stopReason && $bodyKey !== 'messages.boost_partner_stop_code_withdrawn')

{{ __('messages.boost_partner_reason') }}: {{ $stopReason }}
@endif

{{ __('messages.boost_partner_mail_'.$kind.'_button') }}: {{ $url }}
