@php
    $template = $template ?? 'modern';
@endphp
@if (!empty($block['data']['contentHtml']))
@if ($template === 'classic')
<tr>
    <td style="background-color: #faf9f6; padding: 20px 30px;">
        <div style="font-size: 15px; line-height: 1.8; color: {{ $style['textColor'] }}; font-family: '{{ $style['fontFamily'] }}', sans-serif;">
            {!! $block['data']['contentHtml'] !!}
        </div>
    </td>
</tr>
@elseif ($template === 'minimal')
<tr>
    <td style="padding: 14px 30px;">
        <div style="font-size: 15px; line-height: 1.5; color: {{ $style['textColor'] }}; font-family: '{{ $style['fontFamily'] }}', sans-serif;">
            {!! $block['data']['contentHtml'] !!}
        </div>
    </td>
</tr>
@elseif ($template === 'bold')
<tr>
    <td style="background-color: #16213e; padding: 20px 30px;">
        <div style="font-size: 15px; line-height: 1.6; color: #e0e0e0; font-family: '{{ $style['fontFamily'] }}', sans-serif;">
            {!! $block['data']['contentHtml'] !!}
        </div>
    </td>
</tr>
@elseif ($template === 'compact')
<tr>
    <td style="padding: 10px 30px;">
        <div style="font-size: 14px; line-height: 1.4; color: {{ $style['textColor'] }}; font-family: '{{ $style['fontFamily'] }}', sans-serif;">
            {!! $block['data']['contentHtml'] !!}
        </div>
    </td>
</tr>
@else
{{-- Modern (default) --}}
<tr>
    <td style="padding: 20px 30px;">
        <div style="font-size: 15px; line-height: 1.6; color: {{ $style['textColor'] }}; font-family: '{{ $style['fontFamily'] }}', sans-serif;">
            {!! $block['data']['contentHtml'] !!}
        </div>
    </td>
</tr>
@endif
@endif
