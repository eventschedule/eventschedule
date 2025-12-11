<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeUserAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $ua = $request->userAgent() ?? '';

        // Sanitize the UA before session middleware writes it to MySQL
        $ua = $this->sanitizeUserAgent($ua);

        $request->headers->set('User-Agent', $ua);

        return $next($request);
    }

    protected function sanitizeUserAgent(string $ua): string
    {
        if ($ua === '') {
            return 'Unknown';
        }

        // Remove invalid UTF-8 sequences fully
        $ua = mb_convert_encoding($ua, 'UTF-8', 'UTF-8');

        // Remove all non-printable ASCII (UA spec)
        // Keeps characters 0x20–0x7E only.
        $ua = preg_replace('/[^\x20-\x7E]/', '', $ua);

        // Truncate to safe DB length (Laravel default is 255)
        $ua = substr($ua, 0, 255);

        return $ua ?: 'Unknown';
    }
}
