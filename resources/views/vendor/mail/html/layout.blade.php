{{--
    The shell for Laravel's Markdown mail (MailMessage notifications, verify email, password reset),
    matching <x-email.layout>'s account voice. Laravel inlines themes/default.css into it, so the
    light design lives there; everything in the <style> below sits inside @media, which the inliner
    leaves alone. dir is repeated on the layout tables because Gmail strips it from <html>.
--}}
@php($dir = is_rtl() ? 'rtl' : 'ltr')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir }}" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<!--[if mso]>
<style>table, td, p, a, span, h1 { font-family: 'Segoe UI', Arial, sans-serif !important; mso-line-height-rule: exactly; }</style>
<![endif]-->
<style>
@media only screen and (max-width: 620px) {
.inner-body { width: 100% !important; }
.footer { width: 100% !important; }
.content-cell { padding: 28px 20px !important; }
.wrapper-cell { padding: 16px 8px 24px !important; }
.button-table { width: 100% !important; }
.button { display: block !important; }
}
@media (prefers-color-scheme: dark) {
body, .wrapper, .body { background-color: #0b1220 !important; }
.inner-body { background-color: #111827 !important; border-color: #1f2937 !important; }
.header-name, .inner-body h1, .inner-body h2, .inner-body h3 { color: #f8fafc !important; }
.inner-body p, .inner-body li, .table td { color: #cbd5e1 !important; }
.inner-body p a, .inner-body li a { color: #628ffb !important; }
.panel-content { background-color: #1a2332 !important; border-color: #273244 !important; }
.subcopy, .table th, .table td { border-color: #273244 !important; }
.subcopy p, .table th { color: #94a3b8 !important; }
.footer p, .footer a { color: #94a3b8 !important; }
}
</style>
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
<td class="wrapper-cell" align="center">
<!--[if mso]><table role="presentation" align="center" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
{{ $header ?? '' }}
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0">
<table class="inner-body" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
{{-- dir="auto": some of these mails are English whatever the locale (VerifyEmail hardcodes its
     copy), and a right-to-left cell flips their punctuation ("!Hello"). The body takes its
     direction from its own first words; a translated mail still opens right to left. --}}
<td class="content-cell" dir="auto">
{{ Illuminate\Mail\Markdown::parse($slot) }}

{{ $subcopy ?? '' }}
</td>
</tr>
</table>
</td>
</tr>
{{ $footer ?? '' }}
</table>
<!--[if mso]></td></tr></table><![endif]-->
</td>
</tr>
</table>
</body>
</html>
