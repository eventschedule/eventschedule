{{--
    The shell every outgoing email is built on. See App\Utils\EmailTheme for the three voices.

    Everything that matters is an inline style, so the message still reads correctly where a client
    throws the <style> blocks away (Gmail on a non-Google account); the blocks only add dark mode,
    phone-width stacking and the auto-link fixes. dir is repeated on every layout table because Gmail
    strips it from <html> and <body>. The layout prints no <a> of its own: FederationWelcomeTest
    requires every link in that mail to be one the view chose.

    The preheader must stay the first thing in <body>, with style as its first attribute:
    SignupCodeStepTest pins that shape, because the code leading the preview is what mail clients'
    "Copy code" detection keys on.
--}}
@props(['theme', 'title' => '', 'preheader' => null])
@php
    $t = $theme;
    $ring = $t->darkRing ? 'border-color: #cbd5e1 !important;' : '';
    // Cast rather than ->hasActualContent(): a string passed as hero="..." or footer="..." must not crash.
    $hasHero = trim((string) ($hero ?? '')) !== '';
    $hasFooter = trim((string) ($footer ?? '')) !== '';
    // Owner mail shows the schedule up top, so the platform signs at the bottom. Account mail
    // already carries the wordmark up top, and guest mail never names the platform at all.
    $footerBrand = $t->voice === 'owner' && $t->appName;
@endphp
<!DOCTYPE html>
<html lang="{{ $t->lang }}" dir="{{ $t->dir }}" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>{{ $title }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<style>table, td, p, a, span, h1, div { font-family: 'Segoe UI', Arial, sans-serif !important; mso-line-height-rule: exactly; }</style>
<![endif]-->
<style>
body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: separate; }
img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
u + .es-body a { color: inherit; text-decoration: none; font-size: inherit; font-weight: inherit; line-height: inherit; }
@media only screen and (max-width: 620px) {
.es-page-pad { padding: 16px 8px 24px !important; }
.es-card-pad { padding: 28px 20px 12px !important; }
.es-h1 { font-size: 24px !important; line-height: 30px !important; }
.es-btn-wrap { width: 100% !important; }
.es-btn-a { display: block !important; }
.es-col { display: block !important; width: 100% !important; max-width: 100% !important; }
.es-foot-pad { padding: 20px 12px 0 !important; }
}
</style>
<style>
:root { color-scheme: light dark; supported-color-schemes: light dark; }
a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
@media (prefers-color-scheme: dark) {
.es-body, .es-page { background-color: #0b1220 !important; }
.es-card { background-color: #111827 !important; border-color: #1f2937 !important; }
.es-panel { background-color: #1a2332 !important; border-color: #273244 !important; }
.es-rule { border-color: #273244 !important; }
.es-ink { color: #f8fafc !important; }
.es-ink-2 { color: #cbd5e1 !important; }
.es-ink-3, .es-foot { color: #94a3b8 !important; }
.es-link, .es-accent-ink { color: {{ $t->darkLink }} !important; }
.es-tint { background-color: {{ $t->darkTint }} !important; }
.es-btn { {{ $ring }} }
.es-btn-ghost { background-color: #111827 !important; border-color: #273244 !important; }
.es-btn-off { background-color: #1a2332 !important; border-color: #273244 !important; }
.es-btn-off span { color: #94a3b8 !important; }
@foreach (\App\Utils\EmailTheme::TONES as $name => $c)
.es-ink-{{ $name }} { color: {{ $c['dark_ink'] }} !important; }
.es-tone-{{ $name }} { background-color: {{ $c['dark_bg'] }} !important; border-color: {{ $c['dark_border'] }} !important; }
@endforeach
}
[data-ogsb] .es-body, [data-ogsb] .es-page { background-color: #0b1220 !important; }
[data-ogsb] .es-card { background-color: #111827 !important; }
[data-ogsb] .es-panel { background-color: #1a2332 !important; }
[data-ogsc] .es-ink { color: #f8fafc !important; }
[data-ogsc] .es-ink-2 { color: #cbd5e1 !important; }
[data-ogsc] .es-ink-3, [data-ogsc] .es-foot { color: #94a3b8 !important; }
[data-ogsc] .es-link, [data-ogsc] .es-accent-ink { color: {{ $t->darkLink }} !important; }
[data-ogsb] .es-tint { background-color: {{ $t->darkTint }} !important; }
[data-ogsb] .es-btn-ghost { background-color: #111827 !important; }
[data-ogsb] .es-btn-off { background-color: #1a2332 !important; }
@foreach (\App\Utils\EmailTheme::TONES as $name => $c)
[data-ogsc] .es-ink-{{ $name }} { color: {{ $c['dark_ink'] }} !important; }
[data-ogsb] .es-tone-{{ $name }} { background-color: {{ $c['dark_bg'] }} !important; }
@endforeach
</style>
</head>
<body class="es-body" style="margin: 0; padding: 0; width: 100%; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
@if (filled($preheader))
<div style="display: none; max-height: 0px; max-width: 0px; overflow: hidden; opacity: 0; mso-hide: all;">{{ $preheader }}{!! str_repeat('&#847;&zwnj;&nbsp;', 30) !!}</div>
@endif
<div role="article" aria-roledescription="email" @if (filled($title)) aria-label="{{ $title }}" @endif lang="{{ $t->lang }}" dir="{{ $t->dir }}" class="es-page" style="background-color: #f1f5f9;">
<table role="presentation" dir="{{ $t->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-page" style="background-color: #f1f5f9;">
<tr>
<td align="center" class="es-page-pad" style="padding: 32px 16px 40px; font-family: {{ \App\Utils\EmailTheme::FONT }};">
<!--[if mso]><table role="presentation" dir="{{ $t->dir }}" align="center" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
<table role="presentation" dir="{{ $t->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto;">
@if ($t->senderName !== null)
<tr>
<td style="padding: 0 4px 16px; text-align: {{ $t->start }};">
<x-email.sender :theme="$t" />
</td>
</tr>
@endif
<tr>
<td class="es-card" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; text-align: {{ $t->start }};">
@if ($hasHero)
{{ $hero }}
@endif
<table role="presentation" dir="{{ $t->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-card-pad" style="padding: 40px 40px 16px; text-align: {{ $t->start }}; font-family: {{ \App\Utils\EmailTheme::FONT }}; color: #334155;">
{{ $slot }}
</td>
</tr>
</table>
</td>
</tr>
@if ($hasFooter || $footerBrand)
<tr>
<td align="center" class="es-foot-pad" style="padding: 24px 24px 0; text-align: center; font-family: {{ \App\Utils\EmailTheme::FONT }};">
@if ($hasFooter)
{{ $footer }}
@endif
@if ($footerBrand)
<p class="es-foot" style="margin: {{ $hasFooter ? '12px' : '0' }} 0 0; font-size: 13px; line-height: 20px; font-weight: 600; color: #475569;">{{ $t->appName }}</p>
@endif
</td>
</tr>
@endif
</table>
<!--[if mso]></td></tr></table><![endif]-->
</td>
</tr>
</table>
</div>
</body>
</html>
