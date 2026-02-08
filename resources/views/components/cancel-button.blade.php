<button data-cancel-url="{{ \App\Utils\UrlUtils::getBackUrl() }}" {{ $attributes->merge(['class' => 'js-cancel-btn inline-flex items-center justify-center px-6 py-3 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-sm text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 focus:bg-gray-400 dark:focus:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md']) }}>
    {{ __('messages.cancel') }}
</button>

@once
<script {!! nonce_attr() !!}>
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-cancel-btn');
        if (btn) {
            e.preventDefault();
            window.location = btn.getAttribute('data-cancel-url');
        }
    });
</script>
@endonce
