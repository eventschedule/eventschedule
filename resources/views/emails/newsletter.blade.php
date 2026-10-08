{{--
    The newsletter's shell. Its five designs and the owner's colours come from
    App\Utils\NewsletterTheme ($nl); every block reads its colours, type and spacing from there.

    Built like the x-email layout: everything that matters is an inline style, so the mail still
    reads where a client throws the style block away; the block only adds the phone rules. dir is
    repeated on every layout table because Gmail strips it from the html and body tags.

    It declares the light scheme only. The colours are the owner's own choice and the builder's
    preview shows them, so a mail client is told not to recolour them; the old shell declared both
    schemes and shipped no dark styles.

    A block is one or more table rows of the sheet. A banner, and the title band of Modern and Bold,
    run edge to edge; everything else keeps the gutter (class nl-g, which a phone narrows).
--}}
@php
    $nl = \App\Utils\NewsletterTheme::make($newsletter->template ?? 'modern', $style, ! empty($isRtl));

    $hasBanner = $role?->header_image_url && ! in_array($role?->header_image, \App\Models\Role::HEADER_IMAGE_KEYWORDS, true);
    $blocks = array_values(array_filter($blocks, fn ($b) => match ($b['type'] ?? '') {
        'header_banner' => $hasBanner,
        'profile_image' => (bool) $role?->profile_image_url,
        default => view()->exists('emails.newsletter_blocks._'.($b['type'] ?? '')),
    }));

    // A logo that opens the mail is its masthead, not a picture in the flow.
    $masthead = (($blocks[0]['type'] ?? '') === 'profile_image') ? array_shift($blocks) : null;

    $bleeds = fn (?array $b) => $b !== null && ((($b['type'] ?? '') === 'header_banner' && ! in_array($nl->design, ['minimal', 'compact'], true))
        || (($b['type'] ?? '') === 'heading' && ($b['data']['level'] ?? 'h1') === 'h1'
            && in_array($nl->design, ['modern', 'bold'], true) && filled($b['data']['text'] ?? null)));

    // What an inbox shows after the subject: the owner's own line, else the opening of the first
    // text block as READ (its rendered HTML, so no markdown asterisks), never the subject again.
    $preheader = trim((string) ($style['previewText'] ?? ''));
    if ($preheader === '') {
        foreach ($blocks as $b) {
            if (($b['type'] ?? '') === 'text' && filled($b['data']['contentHtml'] ?? null)) {
                $preheader = Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(preg_replace('/<\/p>|<br\s*\/?>/i', ' ', $b['data']['contentHtml']) ?? ''), ENT_QUOTES | ENT_HTML5))), 140, '');
                break;
            }
        }
    }

    $senderName = $role ? $role->name : config('app.name');
    $pagePad = $nl->sheeted ? '32px 12px 40px' : '0';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $nl->dir }}" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ $newsletter->subject }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<style>table, td, p, a, span, h1, h2, h3, div { mso-line-height-rule: exactly;{!! $nl->outlookFont ? ' font-family: '.$nl->outlookFont.' !important;' : '' !!} }</style>
<![endif]-->
<style>
body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: separate; }
img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
u + .nl-body a { color: inherit; }
@media only screen and (max-width: 620px) {
.nl-page { padding: 0 0 36px !important; }
.nl-sheet { border-radius: 0 !important; border-left: 0 !important; border-right: 0 !important; }
.nl-round { border-radius: 0 !important; }
.nl-g { padding-left: 20px !important; padding-right: 20px !important; }
.nl-mast { padding: 20px 20px 16px !important; }
.nl-h1 { font-size: 26px !important; line-height: 32px !important; }
.nl-band { padding-top: 28px !important; padding-bottom: 26px !important; }
.nl-btn-wrap { width: 100% !important; }
.nl-btn-a { display: block !important; }
.nl-stack { display: block !important; width: 100% !important; max-width: 100% !important; }
.nl-stack-pad { padding: 16px 0 0 !important; }
.nl-thumb { width: 88px !important; }
.nl-thumb img { max-width: 88px !important; height: auto !important; }
.nl-lead-title { font-size: 21px !important; line-height: 27px !important; }
.nl-lead-img { width: 100% !important; }
.nl-lead-img img { width: 100% !important; max-width: 100% !important; height: auto !important; }
.nl-foot { padding-left: 20px !important; padding-right: 20px !important; }
.nl-g5 { padding-left: 15px !important; padding-right: 15px !important; }
.nl-spon { width: 50% !important; }
.nl-soc { padding: 6px 8px !important; }
}
</style>
</head>
<body class="nl-body" style="margin: 0; padding: 0; width: 100%; background-color: {{ $nl->ground }}; -webkit-font-smoothing: antialiased;">
@if ($preheader !== '')
<div style="display: none; max-height: 0px; max-width: 0px; overflow: hidden; opacity: 0; mso-hide: all;">{{ $preheader }}{!! str_repeat('&#847;&zwnj;&nbsp;', 30) !!}</div>
@endif
<div role="article" aria-roledescription="email" aria-label="{{ $newsletter->subject }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $nl->dir }}" style="background-color: {{ $nl->ground }};">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->ground }}" style="background-color: {{ $nl->ground }};">
<tr>
<td align="center" class="nl-page" style="padding: {{ $pagePad }};">
<!--[if mso]><table role="presentation" dir="{{ $nl->dir }}" align="center" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto;">
@if ($nl->design === 'compact')
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td bgcolor="{{ $nl->accent }}" style="height: 4px; line-height: 4px; font-size: 0; background-color: {{ $nl->accent }};">&nbsp;</td>
</tr>
</table>
</td>
</tr>
@endif
@if ($masthead && $nl->design === 'modern')
<x-newsletter.masthead :nl="$nl" :role="$role" />
@endif
<tr>
<td class="nl-sheet" bgcolor="{{ $nl->sheet }}" style="background-color: {{ $nl->sheet }};{{ $nl->sheeted ? ' border: 1px solid '.$nl->rule.'; border-radius: '.$nl->sheetRadius.'px;' : '' }}">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
@if ($masthead && $nl->design !== 'modern')
<x-newsletter.masthead :nl="$nl" :role="$role" />
@endif
@if (! $bleeds($blocks[0] ?? null) && ! ($masthead && $nl->design !== 'modern'))
<tr>
<td style="height: {{ $nl->top }}px; line-height: {{ $nl->top }}px; font-size: 0;">&nbsp;</td>
</tr>
@endif
@foreach ($blocks as $i => $block)
@include('emails.newsletter_blocks._'.$block['type'], [
    'block' => $block,
    'first' => $i === 0 && ! ($masthead && $nl->design !== 'modern'),
    'beforeBleed' => $bleeds($blocks[$i + 1] ?? null),
])
@endforeach
<tr>
<td style="height: {{ $nl->sheeted ? 12 : 4 }}px; line-height: {{ $nl->sheeted ? 12 : 4 }}px; font-size: 0;">&nbsp;</td>
</tr>
</table>
</td>
</tr>
<tr>
<td align="center" class="nl-foot" style="padding: {{ $nl->sheeted ? '26px 24px 0' : '0 '.$nl->gutter.'px 40px' }}; text-align: center;">
@if (! $nl->sheeted)
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="border-top: 1px solid {{ $nl->rule }}; height: 24px; line-height: 24px; font-size: 0;">&nbsp;</td>
</tr>
</table>
@endif
<p dir="auto" style="margin: 0 0 6px; {{ $nl->type(14, 20, $nl->footInk, 600) }}">{{ filled($style['footerText'] ?? null) ? $style['footerText'] : $senderName }}</p>
@if ($role)
<p style="margin: 0 0 6px; {{ $nl->type(13, 19, $nl->footInk) }} text-wrap: balance;">{{ __('messages.newsletter_why_receiving', ['schedule' => $role->name]) }}</p>
@endif
<p style="margin: 0; {{ $nl->type(13, 19, $nl->footInk) }}">
{{-- OUTSIDE the $showBranding gate below on purpose: this is a recipient service link, not
     branding, so a Pro schedule must not lose it. '#' in the composer preview. --}}
@if (! empty($manageUrl))
<a href="{{ $manageUrl }}" style="color: {{ $nl->footInk }}; text-decoration: underline;">{{ __('messages.subscription_manage_account') }}</a><span style="color: {{ $nl->footInk }};">&nbsp;&nbsp;&middot;&nbsp;&nbsp;</span>
@endif
<a href="{{ $unsubscribeUrl }}" style="color: {{ $nl->footInk }}; text-decoration: underline;">{{ __('messages.unsubscribe') }}</a>
</p>
@if (! empty($showBranding))
<p style="margin: 14px 0 0; {{ $nl->type(12, 18, $nl->footInk) }}"><a href="https://eventschedule.com" style="color: {{ $nl->footInk }}; text-decoration: none;">{{ __('messages.powered_by_event_schedule') }}</a></p>
@endif
</td>
</tr>
</table>
<!--[if mso]></td></tr></table><![endif]-->
</td>
</tr>
</table>
</div>
</body>
</html>
