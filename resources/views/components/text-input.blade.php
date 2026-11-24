@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[rgb(var(--ap-border-strong))] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-0 rounded-xl', 'dir' => 'auto', 'autocomplete' => 'off']) !!}>
