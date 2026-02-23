<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1200">
    <meta name="robots" content="noindex, nofollow">

    @vite([
        'resources/css/marketing-app.css',
        'resources/css/marketing.css',
    ])

    <style>
        *, *::before, *::after {
            animation: none !important;
            transition: none !important;
        }
        .flex.flex-col.items-center.justify-center {
            padding-top: 60px !important;
        }
        h1 {
            font-size: 5rem !important;
            line-height: 1.1 !important;
            margin-bottom: 1.75rem !important;
        }
        .flex.flex-col.items-center.justify-center p {
            font-size: 1.875rem !important;
            line-height: 1.4 !important;
        }
    </style>

    {{ $headStyles ?? '' }}
</head>
<body style="margin: 0; padding: 0; width: 1200px; height: 630px; overflow: hidden; position: relative;">
    <div style="position: relative; z-index: 1;">
        {{ $slot }}
    </div>
    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 250px; background: radial-gradient(ellipse at center bottom, rgba(78, 129, 250, 0.13) 0%, transparent 70%); z-index: 2; pointer-events: none;"></div>
</body>
</html>
