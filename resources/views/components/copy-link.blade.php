@props(['id', 'value', 'label' => null])

{{-- A read-only URL with a Copy button beside it. The input is there so the link can still be
     selected by hand where the clipboard API is unavailable (an http:// selfhost install). --}}
<div {{ $attributes->merge(['class' => 'flex gap-2']) }}>
    <input type="text" id="{{ $id }}" readonly dir="ltr" value="{{ $value }}" @if ($label) aria-label="{{ $label }}" @endif
        class="flex-1 min-w-0 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
    <button type="button" data-copy-link="{{ $id }}"
        class="inline-flex shrink-0 items-center rounded-lg bg-[var(--brand-button-bg)] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[var(--brand-button-bg-hover)] transition-colors">
        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
        </svg>
        <span data-copy-link-label>{{ __('messages.copy_link') }}</span>
    </button>
</div>

@once
<script {!! nonce_attr() !!}>
// One delegated listener for every copy link on the page.
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-copy-link]');
    if (! btn) return;
    var input = document.getElementById(btn.getAttribute('data-copy-link'));
    var label = btn.querySelector('[data-copy-link-label]');
    if (! input || ! label) return;
    input.select();
    if (! navigator.clipboard) return;
    navigator.clipboard.writeText(input.value).then(function () {
        var original = label.textContent;
        label.textContent = @json(__('messages.copied'), JSON_UNESCAPED_UNICODE);
        setTimeout(function () { label.textContent = original; }, 2000);
    }).catch(function () {});
});
</script>
@endonce
