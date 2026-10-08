{{--
    Sponsor logos. Cells are inline blocks of a fixed width, so four sit across a desktop and two
    across a phone: as four table cells of 120px they needed 544px, and a phone shrank the whole
    mail to fit them. Outlook, which ignores inline-block, is given a table row instead. On a dark
    page every logo stands on the same light tile, or the one with a white ground is a lone box.
--}}
@php
    $sponsors = array_values(array_filter($block['data']['resolvedSponsors'] ?? [], fn ($s) => ! empty($s['logo_url']) || ! empty($s['display_name'])));
    $sponsorTitle = $block['data']['sponsorTitle'] ?? '';
    $cell = $nl->design === 'compact' ? 104 : 124;
    $logo = $cell - 28;
    $tile = $nl->dark ? 'background-color: #ffffff; padding: 8px; border-radius: '.min(8, $nl->cardRadius).'px;' : '';
@endphp
@if (! empty($sponsors))
<tr>
<td align="center" class="nl-g" style="padding: 0 {{ $nl->gutter - 8 }}px {{ $nl->gap - 12 }}px; text-align: center; font-size: 0;">
@if ($sponsorTitle)
<p dir="auto" style="margin: 0 0 16px; {{ $nl->labelType($nl->ink3) }} text-align: center;">{{ $sponsorTitle }}</p>
@endif
<!--[if mso]><table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0"><tr><![endif]-->
@foreach ($sponsors as $sponsor)
@php
    // Client-built JSON that nothing validates, shown in the builder's preview too: linked only
    // through safeHref().
    $sponsorHref = \App\Utils\UrlUtils::safeHref($sponsor['url'] ?? null);
    $tier = in_array($sponsor['tier'] ?? '', ['gold', 'silver', 'bronze'], true) ? $sponsor['tier'] : null;
@endphp
<!--[if mso]><td width="{{ $cell }}" valign="top"><![endif]-->
<div class="nl-spon" style="display: inline-block; width: {{ $cell }}px; vertical-align: top; text-align: center; margin: 0 0 12px;">
@if ($sponsorHref)<a href="{{ $sponsorHref }}" target="_blank" rel="noopener" style="text-decoration: none;">@endif
@if (! empty($sponsor['logo_url']))
<span style="display: inline-block; {{ $tile }}"><img src="{{ $sponsor['logo_url'] }}" alt="{{ $sponsor['display_name'] ?? '' }}" width="{{ $nl->dark ? $logo - 16 : $logo }}" style="display: block; margin: 0 auto; width: {{ $nl->dark ? $logo - 16 : $logo }}px; height: auto; border: 0; border-radius: {{ $nl->dark ? 0 : min(8, $nl->cardRadius) }}px;"></span>
@endif
{{-- The tier sits right under the logo, so it is on one line across the row whether a name
     under it runs to one line or two. --}}
@if ($tier)
<span style="display: block; margin: 8px 0 0; {{ $nl->type(10, 15, $nl->ink3, 700) }}{{ $nl->tracking(0.1) }} text-transform: uppercase;">{{ __('messages.'.$tier) }}</span>
@endif
@if (! empty($sponsor['display_name']))
<span dir="auto" style="display: block; margin: {{ $tier ? 2 : 8 }}px 4px 0; {{ $nl->type(13, 18, $nl->ink2, 600) }}">{{ $sponsor['display_name'] }}</span>
@endif
@if ($sponsorHref)</a>@endif
</div>
<!--[if mso]></td><![endif]-->
@endforeach
<!--[if mso]></tr></table><![endif]-->
</td>
</tr>
@endif
