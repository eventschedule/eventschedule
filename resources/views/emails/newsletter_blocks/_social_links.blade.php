@php
    // Client-built JSON that nothing validates as a link, shown in the builder's preview too: each
    // is linked only through safeHref(), and one with no safe href leaves its icon out, as in the
    // sponsor block.
    $links = [];
    foreach ($block['data']['links'] ?? [] as $link) {
        $linkHref = \App\Utils\UrlUtils::safeHref($link['url'] ?? null);

        if ($linkHref && ! empty($link['platform'])) {
            $links[] = ['href' => $linkHref, 'platform' => $link['platform']];
        }
    }

    $disc = $nl->discFill;
    $side = ['bold' => 44, 'compact' => 30][$nl->design] ?? 38;
    $icon = (int) round($side * 0.47);
    $discRadius = $nl->radius === 0 ? 0 : (int) ($side / 2);
@endphp
@if (count($links))
<tr>
<td align="center" class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->design === 'minimal' ? $nl->gap : $nl->gap - 8 }}px; text-align: center;">
@if ($nl->design === 'minimal')
{{-- Each name is its own inline block, so a row too long for a phone breaks between names and
     leaves no separator hanging at the end of a line. --}}
<p style="margin: 0; text-align: center; font-size: 0; text-wrap: balance;">
@foreach ($links as $link)
<a href="{{ $link['href'] }}" target="_blank" rel="noopener" class="nl-soc" style="display: inline-block; padding: 6px 12px; {{ $nl->type(13, 20, $nl->accentInk, 600) }}{{ $nl->tracking(0.08) }} text-transform: uppercase; text-decoration: underline;">{{ ucfirst($link['platform']) }}</a>
@endforeach
</p>
@else
{{-- Each disc is an inline block, so seven of them wrap on a phone: as cells of one table row
     they could not, and a schedule's links are not capped. The link is inside the cell, because
     Outlook does not follow one wrapped round a table; Outlook is given one row. --}}
<div style="font-size: 0; text-align: center;">
<!--[if mso]><table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0"><tr><![endif]-->
@foreach ($links as $link)
<!--[if mso]><td style="padding: 0 6px;"><![endif]-->
<div style="display: inline-block; vertical-align: top; margin: 0 6px 8px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $disc }}" style="background-color: {{ $disc }}; border-radius: {{ $discRadius }}px;">
<tr>
<td width="{{ $side }}" height="{{ $side }}" align="center" valign="middle" style="width: {{ $side }}px; height: {{ $side }}px; text-align: center; vertical-align: middle;">
<a href="{{ $link['href'] }}" target="_blank" rel="noopener" style="display: block; text-decoration: none;"><img src="{{ url('/images/social-icons/'.$link['platform'].'.png') }}" alt="{{ ucfirst($link['platform']) }}" width="{{ $icon }}" height="{{ $icon }}" style="display: block; margin: 0 auto; border: 0;"></a>
</td>
</tr>
</table>
</div>
<!--[if mso]></td><![endif]-->
@endforeach
<!--[if mso]></tr></table><![endif]-->
</div>
@endif
</td>
</tr>
@endif
