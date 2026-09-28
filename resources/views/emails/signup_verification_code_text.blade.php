{{ __('messages.your_verification_code') }}: {{ $code }}

{{ __('messages.signup_verification_code_heading') }}

{{ __('messages.hello') }},

{{ __('messages.signup_verification_code_intro') }}

{{ __('messages.your_verification_code') }}: {{ $code }}

{{ __('messages.signup_verification_code_expiry') }}
@if (! empty($continueUrl))

{{ __('messages.continue_signup') }}: {!! $continueUrl !!}
@endif

{{ __('messages.signup_verification_code_security_notice') }}

{{ __('messages.thank_you_for_using') }}
