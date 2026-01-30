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
    <link rel="icon" href="/images/favicon.png">
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
        .code-wrapper {
            position: relative; margin-bottom: 1rem;
        }
        code {
            display: block; background: #1f2937; color: #e5e7eb; padding: 0.75rem 1rem;
            border-radius: 0.375rem; font-size: 0.875rem;
            overflow-x: auto; padding-right: 2.5rem;
        }
        .copy-btn {
            position: absolute; top: 0.5rem; right: 0.5rem;
            background: transparent; border: none; cursor: pointer;
            color: #9ca3af; padding: 0.25rem; border-radius: 0.25rem;
            display: flex; align-items: center; justify-content: center;
        }
        .copy-btn:hover { color: #e5e7eb; }
        .copy-btn svg { width: 1rem; height: 1rem; }
        a { color: #2563eb; text-decoration: underline; }
        a:hover { color: #1d4ed8; }
        .external-icon { width: 0.75rem; height: 0.75rem; display: inline-block; vertical-align: middle; margin-left: 0.25rem; }
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
        <div class="code-wrapper">
            <code>cp .env.example .env</code>
            <button class="copy-btn" onclick="copyCommand(this)" title="Copy to clipboard">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
            </button>
        </div>
        <p>After creating the file, update it with your settings and refresh this page.</p>
        <p>For full installation instructions, visit the <a href="https://eventschedule.com/docs/selfhost/installation" target="_blank" rel="noopener noreferrer">selfhost installation guide<svg class="external-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></a>.</p>
    </div>
    <script>
    function copyCommand(btn) {
        var text = "cp .env.example .env";
        var originalHTML = btn.innerHTML;
        function showSuccess() {
            btn.innerHTML = \'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>\';
            btn.style.color = "#4ade80";
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.style.color = "";
            }, 2000);
        }
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(showSuccess);
        } else {
            var ta = document.createElement("textarea");
            ta.value = text;
            ta.style.position = "fixed";
            ta.style.opacity = "0";
            document.body.appendChild(ta);
            ta.select();
            document.execCommand("copy");
            document.body.removeChild(ta);
            showSuccess();
        }
    }
    </script>
</body>
</html>';
    exit;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
