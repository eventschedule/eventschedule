{{--
    Owner-written HTML (ticket notes, an event description), in body type. The slot must already be
    sanitised. dir="auto": the owner's language need not be the mail's, and an English note inside a
    Hebrew mail otherwise flips its trailing punctuation to the front.

    Markdown arrives as bare <p>, <ul> and <ol>, which a mail client spaces with its own defaults
    (a 1em gap above the first paragraph). Bare tags are given the system's spacing inline, so it
    holds where a client strips the <style> block; a tag that already carries attributes is left alone.
--}}
@aware(['theme' => null])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $html = preg_replace(
        ['/<p>/i', '/<(ul|ol)>/i', '/<li>/i'],
        ['<p style="margin: 0 0 12px;">', '<$1 style="margin: 0 0 12px; padding-'.$theme->start.': 22px;">', '<li style="margin: 0 0 4px;">'],
        (string) $slot
    );
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-ink-2" dir="auto" style="padding: 0 0 16px; font-size: 16px; line-height: 26px; color: #334155; text-align: {{ $theme->start }}; word-break: break-word;">
{!! $html !!}
</td>
</tr>
</table>
