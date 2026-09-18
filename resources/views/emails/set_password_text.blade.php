{{--
    {!! !!} throughout, not {{ }}. This is the text/plain part, and Blade's {{ }} runs e() wherever
    it is used - so an address with an apostrophe arrives as o&#039;brien@x.com, and the URL survives
    today only because route('password.reset') puts the token in the PATH, leaving one query
    parameter and therefore no & to turn into &amp;. Add a second and every plain-text link breaks.
--}}
{!! __('messages.set_password_heading') !!}

{!! __('messages.set_password_body', ['email' => $email]) !!}

{!! __('messages.set_password_button') !!}: {!! $resetUrl !!}

{!! __('messages.set_password_expires', ['minutes' => $expiresInMinutes]) !!} {!! __('messages.set_password_ignore') !!}
