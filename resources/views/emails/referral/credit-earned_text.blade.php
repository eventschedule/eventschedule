{{ __('messages.referral_credit_earned_subject') }}

{{ __('messages.hello') }} {{ $referrer->firstName() }},

{{ __('messages.referral_credit_earned_body', ['value' => $creditValue, 'plan' => ucfirst($planType)]) }}

{{ __('messages.credit_value') }}: {{ $creditValue }}
{{ __('messages.plan_tier') }}: {{ ucfirst($planType) }}

{{ __('messages.referral_credit_earned_cta') }}

{{ __('messages.view_referral_dashboard') }}: {{ $dashboardUrl }}
