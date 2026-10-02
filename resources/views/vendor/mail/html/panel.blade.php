{{-- A tinted panel with a 1px border all round, never a side stripe (CLAUDE.md). --}}
<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-content">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
