{{--
    The tenant 404. Deliberately NOT errors/404.blade.php: that one is the PLATFORM page and links
    to marketing_url('/'), /features, /pricing, /docs - which on a customer's custom domain sends
    their visitors to eventschedule.com. This page offers exactly one way out, back into the
    schedule the visitor was already looking at.

    Self-contained (inline, nonce'd CSS, no @vite) so it cannot depend on a built manifest or on
    the guest portal's Vue shell, both of which are things an error page should not need.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ __('messages.guest_not_found_heading') }} - {{ $role->translatedName() }}</title>
    <style {!! nonce_attr() !!}>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-color: #f9fafb;
            color: #111827;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            line-height: 1.6;
        }
        .card { max-width: 34rem; width: 100%; text-align: center; }
        .schedule { font-size: .875rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; margin: 0 0 1.25rem; }
        .code { font-size: 4.5rem; font-weight: 700; line-height: 1; margin: 0 0 .5rem; color: #4E81FA; }
        h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 .75rem; }
        p { margin: 0 0 2rem; color: #4b5563; }
        .action {
            display: inline-block;
            padding: .75rem 1.5rem;
            border-radius: .75rem;
            background-color: #4E81FA;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
        }
        .action:hover { background-color: #3b6ce0; }
        @media (prefers-color-scheme: dark) {
            body { background-color: #111827; color: #f9fafb; }
            .schedule { color: #9ca3af; }
            p { color: #d1d5db; }
        }
    </style>
</head>
<body>
    <div class="card">
        <p class="schedule">{{ $role->translatedName() }}</p>
        <p class="code">404</p>
        <h1>{{ __('messages.guest_not_found_heading') }}</h1>
        <p>{{ __('messages.guest_not_found_message') }}</p>
        <a class="action" href="{{ $homeUrl }}">{{ __('messages.back_to_schedule') }}</a>
    </div>
</body>
</html>
