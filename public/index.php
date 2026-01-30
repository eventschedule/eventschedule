<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Check if .env file exists (selfhosted setup)...
if (! file_exists(__DIR__.'/../.env')) {
    http_response_code(500);
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Required</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 1rem;
            background: #f3f4f6; color: #1f2937;
        }
        .card {
            background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 480px; width: 100%; padding: 2.5rem;
        }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; }
        p { line-height: 1.6; margin-bottom: 1rem; color: #4b5563; }
        code {
            display: block; background: #1f2937; color: #e5e7eb; padding: 0.75rem 1rem;
            border-radius: 0.375rem; font-size: 0.875rem; margin-bottom: 1rem;
            overflow-x: auto;
        }
        a { color: #2563eb; text-decoration: underline; }
        a:hover { color: #1d4ed8; }
        @media (prefers-color-scheme: dark) {
            body { background: #111827; color: #f9fafb; }
            .card { background: #1f2937; }
            p { color: #d1d5db; }
            code { background: #111827; color: #e5e7eb; }
            a { color: #60a5fa; }
            a:hover { color: #93bbfd; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Setup Required</h1>
        <p>The <strong>.env</strong> configuration file is missing. To get started, copy the example file:</p>
        <code>cp .env.example .env</code>
        <p>After creating the file, update it with your settings and refresh this page.</p>
        <p>For full installation instructions, visit the <a href="https://eventschedule.com/docs/selfhost/installation">selfhost installation guide</a>.</p>
    </div>
</body>
</html>';
    exit;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
