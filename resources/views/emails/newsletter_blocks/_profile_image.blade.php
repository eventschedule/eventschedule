@if ($role->profile_image_url)
<tr>
    <td align="center" style="padding-bottom: 20px;">
        <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" style="max-width: 200px; max-height: 100px; border-radius: 8px;" />
    </td>
</tr>
@endif
