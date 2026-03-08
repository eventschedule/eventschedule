<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-gradient-to-b from-red-500 to-red-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-red-600 hover:to-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105 hover:shadow-md']) }}>
    {{ $slot }}
</button>
