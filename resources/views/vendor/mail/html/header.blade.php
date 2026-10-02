{{-- The app's name as a wordmark, never a link and never a logo image: on a selfhosted or white-label
     install the name is theirs, and an image would be ours. --}}
@props(['url'])
<tr>
<td class="header" style="text-align: {{ is_rtl() ? 'right' : 'left' }};">
<span class="header-name">{{ $slot }}</span>
</td>
</tr>
