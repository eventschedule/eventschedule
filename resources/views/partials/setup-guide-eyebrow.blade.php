{{-- The setup guide on the two wizard screens that have no sidebar: its ring and one line of
     encouragement in the heading's eyebrow. Nothing to open here; the screen IS the step.
     $initialFrom names an input whose first letter the ring's centre takes as it is typed, the
     same initial the guide's pill carries once the schedule exists. --}}
<p class="inline-flex max-w-full items-center justify-center gap-2 text-sm sm:text-base font-semibold text-[var(--brand-blue)]">
    <span class="relative inline-flex h-7 w-7 shrink-0 items-center justify-center">
        @include('partials.setup-guide-ring', ['size' => 28, 'total' => 3, 'done' => 1])
        <span class="absolute inset-0 flex items-center justify-center text-[11px] font-bold leading-none transition-opacity duration-200"
            data-setup-guide-initial aria-hidden="true"></span>
    </span>
    <span>{{ $line }}</span>
</p>
@if (! empty($initialFrom))
<script {!! nonce_attr() !!}>
    {{-- On DOMContentLoaded: this line sits above the form, so the field does not exist yet
         when the script is parsed. --}}
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById(@json($initialFrom));
        var centre = document.querySelector('[data-setup-guide-initial]');

        if (! input || ! centre) {
            return;
        }

        {{-- Array.from() splits on code points, so a Hebrew or emoji first letter is not cut in half. --}}
        function show() {
            var first = Array.from(input.value.trim())[0] || '';
            centre.textContent = first.toLocaleUpperCase();
            centre.style.opacity = first ? '1' : '0';
        }

        input.addEventListener('input', show);
        show();
    });
</script>
@endif
