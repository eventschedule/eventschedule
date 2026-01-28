@props(['disabled' => false])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-3 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[#3D6FE8] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm']) }} @disabled($disabled)>
    {{ $slot }}
</button>
