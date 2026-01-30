@props([
    'name',
    'description',
    'iconColor' => 'cyan',
    'blogSlug' => null,
    'features' => [],
])

@php
    $blogPost = $blogSlug ? get_sub_audience_blog($blogSlug) : null;
@endphp

<div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-{{ $iconColor }}-200 dark:hover:border-{{ $iconColor }}-500/30 transition-all">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-{{ $iconColor }}-100 dark:bg-{{ $iconColor }}-500/20 mb-4">
        {{ $icon }}
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $name }}</h3>
    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $description }}</p>

    @if(!empty($features))
        <div class="flex flex-wrap gap-1.5 mt-3">
            @foreach($features as $feature)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $iconColor }}-50 text-{{ $iconColor }}-700 dark:bg-{{ $iconColor }}-500/10 dark:text-{{ $iconColor }}-300">{{ $feature }}</span>
            @endforeach
        </div>
    @endif

    @if($blogPost)
        <a href="{{ blog_url('/' . $blogPost->slug) }}" class="inline-flex items-center text-{{ $iconColor }}-600 hover:text-{{ $iconColor }}-800 dark:text-{{ $iconColor }}-400 dark:hover:text-{{ $iconColor }}-300 text-sm font-medium mt-3 group">
            Learn more
            <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    @endif
</div>
