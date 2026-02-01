@php
    $template = $template ?? 'modern';
@endphp
@if ($role->profile_image_url)
@if ($template === 'classic')
<tr>
    <td align="center" style="padding-bottom: 20px;">
        <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" width="200" style="max-width: 200px; height: auto; border-radius: 0;" />
    </td>
</tr>
@elseif ($template === 'minimal')
<tr>
    <td align="center" style="padding-bottom: 14px;">
        <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" width="150" style="max-width: 150px; height: auto; border-radius: 4px;" />
    </td>
</tr>
@elseif ($template === 'bold')
<tr>
    <td align="center" style="padding-bottom: 20px;">
        <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" width="200" style="max-width: 200px; height: auto; border-radius: 8px; border: 3px solid {{ $style['accentColor'] }};" />
    </td>
</tr>
@elseif ($template === 'compact')
<tr>
    <td align="center" style="padding-bottom: 10px;">
        <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" width="80" style="max-width: 80px; height: auto; border-radius: 4px;" />
    </td>
</tr>
@else
{{-- Modern (default) --}}
<tr>
    <td align="center" style="padding-bottom: 20px;">
        <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" width="200" style="max-width: 200px; height: auto; border-radius: 8px;" />
    </td>
</tr>
@endif
@endif
