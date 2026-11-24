<button {{ $attributes->merge(['type' => 'button', 'class' => 'ap-btn-sm ap-secondary-btn inline-flex items-center justify-center px-4 py-2 border border-[rgb(var(--ap-border-strong))] dark:border-gray-600 rounded-xl font-semibold text-sm text-gray-900 dark:text-gray-200 focus:outline-none disabled:opacity-25 transition-all duration-200']) }}>
    {{ $slot }}
</button>
