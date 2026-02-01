@if (!empty($block['data']['contentHtml']))
<tr>
    <td style="background-color: #ffffff; padding: 20px 30px;">
        <div style="font-size: 15px; line-height: 1.6; color: {{ $style['textColor'] }}; font-family: {{ $style['fontFamily'] }}, sans-serif;">
            {!! $block['data']['contentHtml'] !!}
        </div>
    </td>
</tr>
@endif
