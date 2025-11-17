<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('messages.access_denied') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
            :root {
                color-scheme: light dark;
            }
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: radial-gradient(circle at top, #eef2ff, #f8fafc 60%);
                color: #0f172a;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
            }
            .card {
                width: 100%;
                max-width: 480px;
                background: rgba(255, 255, 255, 0.9);
                border-radius: 1.5rem;
                padding: 2.5rem;
                box-shadow: 0 25px 60px rgba(79, 70, 229, 0.15);
                backdrop-filter: blur(10px);
            }
            .code-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                font-size: 0.85rem;
                color: #4f46e5;
                background: rgba(79, 70, 229, 0.12);
                border-radius: 999px;
                padding: 0.4rem 1rem;
                letter-spacing: 0.08em;
            }
            h1 {
                margin: 1.25rem 0 0.5rem;
                font-size: 2rem;
                line-height: 1.2;
            }
            p {
                margin: 0;
                color: #475569;
                line-height: 1.6;
            }
            .actions {
                margin-top: 1.75rem;
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            a.button {
                flex: 1;
                text-align: center;
                text-decoration: none;
                font-weight: 600;
                border-radius: 999px;
                padding: 0.75rem 1rem;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            a.button.primary {
                background: #4f46e5;
                color: #fff;
                box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            }
            a.button.secondary {
                background: transparent;
                color: #4f46e5;
                border: 1px solid rgba(79, 70, 229, 0.3);
            }
            a.button:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 25px rgba(79, 70, 229, 0.2);
            }
            @media (max-width: 480px) {
                .card {
                    padding: 2rem;
                }
                .actions {
                    flex-direction: column;
                }
            }
        </style>
    </head>
    <body>
        @php
            $requestAccessEmail = config('mail.from.address') ?: 'support@eventschedule.com';
        @endphp
        <div class="card">
            <span class="code-badge">403</span>
            <h1>{{ __('messages.access_denied') }}</h1>
            <p>{{ __('messages.access_denied_description') }}</p>
            <div class="actions">
                <a href="{{ url()->previous() ?? url('/') }}" class="button secondary">{{ __('messages.go_back') }}</a>
                <a href="mailto:{{ $requestAccessEmail }}" class="button primary">{{ __('messages.request_access') }}</a>
            </div>
        </div>
    </body>
</html>
