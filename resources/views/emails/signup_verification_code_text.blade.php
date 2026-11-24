{{ __('messages.signup_verification_code_heading') }}: {{ $code }}

{{ ! empty($continueUrl) ? __('messages.signup_verification_code_intro') : __('messages.signup_verification_code_intro_any') }}

{{ $code }}
{{ __('messages.signup_code_expires_short') }}
@if (! empty($continueUrl))

{{ __('messages.continue_signup') }}: {!! $continueUrl !!}
@endif

{{ __('messages.signup_code_never_share') }} {{ __('messages.signup_code_never_asked') }}

{{ __('messages.signup_code_not_you', ['app' => config('app.name')]) }}
