<button onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ $url }}'; return false;}" {{ $attributes->merge(['class' => 'inline-flex items-center px-4 py-2 bg-red-500 dark:bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 dark:hover:bg-red-800 focus:bg-red-600 dark:focus:bg-red-800 active:bg-red-700 dark:active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ __('messages.delete') }}
</button>
