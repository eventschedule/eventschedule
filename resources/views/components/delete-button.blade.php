<button data-confirm-message="{{ __('messages.are_you_sure') }}" data-delete-url="{{ $url }}" {{ $attributes->merge(['class' => 'js-delete-btn inline-flex items-center justify-center px-4 py-2 bg-red-500 dark:bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 dark:hover:bg-red-800 focus:bg-red-600 dark:focus:bg-red-800 active:bg-red-700 dark:active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md']) }}>
    {{ __('messages.delete') }}
</button>

@once
<script {!! nonce_attr() !!}>
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-delete-btn');
        if (btn) {
            e.preventDefault();
            var confirmed = confirm(btn.getAttribute('data-confirm-message'));
            if (confirmed) {
                location.href = btn.getAttribute('data-delete-url');
            }
        }
    });
</script>
@endonce
