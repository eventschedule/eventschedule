<!-- Previous/Next Navigation -->
<div class="flex flex-col sm:flex-row gap-4 mt-12 pt-8 border-t border-gray-200 dark:border-white/10">
    @if($prevDoc)
        <a href="{{ route($prevDoc['route']) }}" class="flex-1 group">
            <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">
                <div class="text-sm text-gray-500 mb-1">Previous</div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $prevDoc['title'] }}</span>
                </div>
            </div>
        </a>
    @else
        <div class="flex-1"></div>
    @endif

    @if($nextDoc)
        <a href="{{ route($nextDoc['route']) }}" class="flex-1 group">
            <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 transition-colors text-right">
                <div class="text-sm text-gray-500 mb-1">Next</div>
                <div class="flex items-center justify-end gap-2">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $nextDoc['title'] }}</span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
        </a>
    @else
        <div class="flex-1"></div>
    @endif
</div>
