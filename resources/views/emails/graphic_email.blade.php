@php($rtl = in_array($role->language_code, ['he', 'ar']))
<pre @if($rtl) dir="rtl" @endif style="font-family: sans-serif; white-space: pre-wrap;">{{ $eventText }}</pre>
