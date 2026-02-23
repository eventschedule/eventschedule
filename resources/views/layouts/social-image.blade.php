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
        body > div:first-child > div:first-child::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 900px;
            height: 350px;
            background: radial-gradient(ellipse, rgba(78, 129, 250, 0.22) 0%, transparent 70%);
            pointer-events: none;
            z-index: 1;
        }
        body > div:first-child > div:first-child > .flex.flex-col {
            z-index: 2 !important;
        }
    </style>

    {{ $headStyles ?? '' }}
</head>
<body style="margin: 0; padding: 0; width: 1200px; height: 630px; overflow: hidden; position: relative;">
    {{ $slot }}
</body>
</html>
