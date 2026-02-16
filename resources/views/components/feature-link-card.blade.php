@props([
    'name',
    'description',
    'url',
    'iconColor' => 'blue',
])

<a href="{{ $url }}" class="group block">
    <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-{{ $iconColor }}-300 dark:hover:border-{{ $iconColor }}-500/30 hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-{{ $iconColor }}-100 dark:bg-{{ $iconColor }}-500/20 flex items-center justify-center">
            {{ $icon }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-gray-900 dark:text-white group-hover:text-{{ $iconColor }}-600 dark:group-hover:text-{{ $iconColor }}-400 transition-colors">{{ $name }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</div>
        </div>
        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-{{ $iconColor }}-500 dark:group-hover:text-{{ $iconColor }}-400 flex-shrink-0 mt-0.5 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</a>
