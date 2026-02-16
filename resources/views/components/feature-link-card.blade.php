@props([
    'name',
    'description',
    'url',
    'iconColor' => 'blue',
])

@php
    $colorMap = [
        'amber' => [
            'border_hover' => 'hover:border-amber-300 dark:hover:border-amber-500/30',
            'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
            'name_hover' => 'group-hover:text-amber-600 dark:group-hover:text-amber-400',
            'arrow_hover' => 'group-hover:text-amber-500 dark:group-hover:text-amber-400',
        ],
        'blue' => [
            'border_hover' => 'hover:border-blue-300 dark:hover:border-blue-500/30',
            'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
            'name_hover' => 'group-hover:text-blue-600 dark:group-hover:text-blue-400',
            'arrow_hover' => 'group-hover:text-blue-500 dark:group-hover:text-blue-400',
        ],
        'emerald' => [
            'border_hover' => 'hover:border-emerald-300 dark:hover:border-emerald-500/30',
            'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
            'name_hover' => 'group-hover:text-emerald-600 dark:group-hover:text-emerald-400',
            'arrow_hover' => 'group-hover:text-emerald-500 dark:group-hover:text-emerald-400',
        ],
        'green' => [
            'border_hover' => 'hover:border-green-300 dark:hover:border-green-500/30',
            'icon_bg' => 'bg-green-100 dark:bg-green-500/20',
            'name_hover' => 'group-hover:text-green-600 dark:group-hover:text-green-400',
            'arrow_hover' => 'group-hover:text-green-500 dark:group-hover:text-green-400',
        ],
        'lime' => [
            'border_hover' => 'hover:border-lime-300 dark:hover:border-lime-500/30',
            'icon_bg' => 'bg-lime-100 dark:bg-lime-500/20',
            'name_hover' => 'group-hover:text-lime-600 dark:group-hover:text-lime-400',
            'arrow_hover' => 'group-hover:text-lime-500 dark:group-hover:text-lime-400',
        ],
        'orange' => [
            'border_hover' => 'hover:border-orange-300 dark:hover:border-orange-500/30',
            'icon_bg' => 'bg-orange-100 dark:bg-orange-500/20',
            'name_hover' => 'group-hover:text-orange-600 dark:group-hover:text-orange-400',
            'arrow_hover' => 'group-hover:text-orange-500 dark:group-hover:text-orange-400',
        ],
        'rose' => [
            'border_hover' => 'hover:border-rose-300 dark:hover:border-rose-500/30',
            'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
            'name_hover' => 'group-hover:text-rose-600 dark:group-hover:text-rose-400',
            'arrow_hover' => 'group-hover:text-rose-500 dark:group-hover:text-rose-400',
        ],
        'sky' => [
            'border_hover' => 'hover:border-sky-300 dark:hover:border-sky-500/30',
            'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
            'name_hover' => 'group-hover:text-sky-600 dark:group-hover:text-sky-400',
            'arrow_hover' => 'group-hover:text-sky-500 dark:group-hover:text-sky-400',
        ],
        'teal' => [
            'border_hover' => 'hover:border-teal-300 dark:hover:border-teal-500/30',
            'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
            'name_hover' => 'group-hover:text-teal-600 dark:group-hover:text-teal-400',
            'arrow_hover' => 'group-hover:text-teal-500 dark:group-hover:text-teal-400',
        ],
    ];
    $classes = $colorMap[$iconColor] ?? $colorMap['blue'];
@endphp

<a href="{{ $url }}" class="group block">
    <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 {{ $classes['border_hover'] }} hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
        <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $classes['icon_bg'] }} flex items-center justify-center">
            {{ $icon }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-gray-900 dark:text-white {{ $classes['name_hover'] }} transition-colors">{{ $name }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</div>
        </div>
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 {{ $classes['arrow_hover'] }} flex-shrink-0 mt-0.5 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</a>
