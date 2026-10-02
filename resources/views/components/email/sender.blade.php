{{--
    Who the mail is from, above the card: the schedule's picture and name, or the app's name as a
    wordmark. Rendered by <x-email.layout> only.

    The picture sits on a white tile so a transparent dark logo stays visible in dark mode, a wide
    logo included. Outlook for Windows gets the initial instead: it cannot show the WebP derivative
    or round an image, so the [if mso] branch is the only one it reads. A wide logo usually carries
    the name already, so it stands alone, with the name as its alt text for clients that block
    images. A picture with no recorded size keeps its own shape inside the tile.
--}}
@props(['theme'])
@php($t = $theme)
@php($nameStyle = 'font-size: 15px; line-height: 20px; font-weight: 600; color: #0f172a; font-family: '.\App\Utils\EmailTheme::FONT.';')
@if ($t->voice === 'account')
<span class="es-ink" style="font-size: 17px; line-height: 24px; font-weight: 700; letter-spacing: -0.01em; color: #0f172a; font-family: {{ \App\Utils\EmailTheme::FONT }};">{{ $t->senderName }}</span>
@elseif ($t->avatarUrl && $t->avatarWide)
<!--[if !mso]><!-->
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-tile" bgcolor="#ffffff" style="padding: 6px 10px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
<img src="{{ $t->avatarUrl }}" width="{{ $t->avatarWidth }}" height="{{ $t->avatarHeight }}" alt="{{ $t->senderName }}" style="display: block; width: {{ $t->avatarWidth }}px; height: {{ $t->avatarHeight }}px; max-width: 100%; border: 0;">
</td>
</tr>
</table>
<!--<![endif]-->
<!--[if mso]><span style="{{ $nameStyle }}">{{ $t->senderName }}</span><![endif]-->
@else
<table role="presentation" dir="{{ $t->dir }}" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="44" valign="middle" style="width: 44px;">
@if ($t->avatarUrl)
<!--[if !mso]><!-->
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-tile" width="44" height="44" align="center" valign="middle" style="width: 44px; height: 44px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
@if ($t->avatarWidth > 0)
<img src="{{ $t->avatarUrl }}" width="{{ $t->avatarWidth }}" height="{{ $t->avatarHeight }}" alt="" style="display: block; margin: 0 auto; width: {{ $t->avatarWidth }}px; height: {{ $t->avatarHeight }}px; border: 0; border-radius: 11px;">
@else
<img src="{{ $t->avatarUrl }}" alt="" style="display: block; margin: 0 auto; width: auto; height: auto; max-width: 42px; max-height: 42px; border: 0; border-radius: 11px;">
@endif
</td>
</tr>
</table>
<!--<![endif]-->
<!--[if mso]>
@endif
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-tint" width="44" height="44" align="center" valign="middle" bgcolor="{{ $t->accentTint }}" style="width: 44px; height: 44px; background-color: {{ $t->accentTint }}; border-radius: 12px; text-align: center;">
<span class="es-accent-ink" aria-hidden="true" style="font-size: 18px; line-height: 44px; font-weight: 700; color: {{ $t->accentInk }}; font-family: {{ \App\Utils\EmailTheme::FONT }};">{{ $t->initial }}</span>
</td>
</tr>
</table>
@if ($t->avatarUrl)
<![endif]-->
@endif
</td>
<td valign="middle" style="padding-{{ $t->start }}: 12px; text-align: {{ $t->start }};">
<span class="es-ink" dir="auto" style="{{ $nameStyle }}">{{ $t->senderName }}</span>
</td>
</tr>
</table>
@endif
