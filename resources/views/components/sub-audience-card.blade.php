@props([
    'name',
    'description',
    'iconColor' => 'cyan',
    'blogSlug' => null,
])

@php
    $blogPost = $blogSlug ? get_sub_audience_blog($blogSlug) : null;
@endphp

<div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-{{ $iconColor }}-200 transition-all">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-{{ $iconColor }}-100 mb-4">
        {{ $icon }}
    </div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $name }}</h3>
    <p class="text-gray-600 text-sm">{{ $description }}</p>

    @if($blogPost)
        <a href="{{ blog_url('/' . $blogPost->slug) }}" class="inline-flex items-center text-{{ $iconColor }}-600 hover:text-{{ $iconColor }}-800 text-sm font-medium mt-3 group">
            Learn more
            <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    @endif
</div>
