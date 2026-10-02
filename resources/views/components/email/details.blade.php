{{--
    A grid of label-above-value pairs (<x-email.item>), two columns wide and stacked on a phone.

    The items are inline-block halves, so they wrap on their own where a client strips the
    <style> block, and Outlook for Windows (which ignores inline-block widths) simply lists them.
    font-size 0 on the wrapper removes the whitespace between them, which would otherwise push the
    second half onto its own line. Pass panel to raise the grid on a tinted surface.
--}}
@aware(['theme' => null])
@props(['panel' => false])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 {{ $panel ? 24 : 4 }}px;">
@if ($panel)
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-panel" bgcolor="#f8fafc" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
<tr>
<td style="padding: 20px 24px 0;">
@endif
<div dir="{{ $theme->dir }}" style="font-size: 0; line-height: 0; text-align: {{ $theme->start }};">{{ $slot }}</div>
@if ($panel)
</td>
</tr>
</table>
@endif
</td>
</tr>
</table>
