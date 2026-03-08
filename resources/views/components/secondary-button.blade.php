<button {{ $attributes->merge(['type' => 'button', 'class' => 'ap-secondary-btn inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition-all duration-200 hover:scale-105 hover:shadow-md']) }}>
    {{ $slot }}
</button>
