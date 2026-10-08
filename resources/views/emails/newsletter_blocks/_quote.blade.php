{{-- Someone's words. A quiet panel with a large mark, never a stripe down one side. --}}
@php
    $text = $block['data']['text'] ?? '';
    $author = $block['data']['author'] ?? '';
    $title = $block['data']['title'] ?? '';
    $plain = in_array($nl->design, ['minimal', 'classic'], true);
    $centre = $nl->design === 'classic';
    $size = ['bold' => [22, 32], 'compact' => [15, 22], 'classic' => [21, 32]][$nl->design] ?? [19, 29];
    $pad = $nl->design === 'compact' ? '14px 16px' : '24px 26px';
@endphp
@if ($text)
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" @if (! $plain) bgcolor="{{ $nl->panel }}" @endif style="{{ $plain ? ($centre ? 'border-top: 1px solid '.$nl->rule.'; border-bottom: 1px solid '.$nl->rule.';' : '') : 'background-color: '.$nl->panel.'; border-radius: '.$nl->cardRadius.'px;' }}">
<tr>
<td style="padding: {{ $plain ? ($centre ? '26px 12px' : '0') : $pad }}; text-align: {{ $centre ? 'center' : $nl->start }};">
<div aria-hidden="true" style="font-family: Georgia, Times New Roman, serif; font-size: {{ $nl->design === 'compact' ? 30 : 46 }}px; line-height: {{ $nl->design === 'compact' ? 22 : 34 }}px; height: {{ $nl->design === 'compact' ? 16 : 26 }}px; color: {{ $nl->accentInk }};">{!! $nl->dir === 'rtl' ? '&rdquo;' : '&ldquo;' !!}</div>
<p dir="auto" style="margin: 0; {{ $nl->type($size[0], $size[1], $nl->ink, $nl->design === 'bold' ? 700 : 400) }}{{ $nl->design === 'bold' ? '' : $nl->italic() }}">{{ $text }}</p>
@if ($author)
<p dir="auto" style="margin: 14px 0 0; {{ $nl->smallType($nl->ink, 700) }}">{{ $author }}@if ($title)<span style="font-weight: 400; color: {{ $nl->ink3 }};">&nbsp;&nbsp;&middot;&nbsp;&nbsp;{{ $title }}</span>@endif</p>
@endif
</td>
</tr>
</table>
</td>
</tr>
@endif
