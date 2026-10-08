{{-- Owner-written markdown. contentHtml is only ever what NewsletterService rendered from it.
     The last paragraph, list or quote keeps its own gap (NewsletterTheme::prose()), so the cell's
     is that much smaller. --}}
@if (filled($block['data']['contentHtml'] ?? null))
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap - $nl->para }}px; text-align: {{ $nl->start }};">
<div dir="auto" style="{{ $nl->bodyType() }} word-break: break-word;">{!! $nl->prose($block['data']['contentHtml']) !!}</div>
</td>
</tr>
@endif
