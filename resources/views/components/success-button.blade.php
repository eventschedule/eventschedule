@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white shadow-sm hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition-all duration-200 hover:scale-105 hover:shadow-md']) }} @disabled($disabled)>
    {{ $slot }}
</button>
