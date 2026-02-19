@if ($role?->header_image_url)
<tr>
    <td align="center" style="padding-bottom: 20px;">
        <img src="{{ $role?->header_image_url }}" alt="{{ $role?->name }}" width="600" style="max-width: 600px; width: 100%; height: auto; display: block; border-radius: {{ $style['buttonRadius'] === 'rounded' ? '8px' : '0' }};" />
    </td>
</tr>
@endif
