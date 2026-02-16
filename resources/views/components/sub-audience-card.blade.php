@props([
    'name',
    'description',
    'iconColor' => 'cyan',
    'blogSlug' => null,
    'features' => [],
])

@php
    $blogPost = $blogSlug ? get_sub_audience_blog($blogSlug) : null;

    $colorMap = [
        'amber' => [
            'border_hover' => 'hover:border-amber-200 dark:hover:border-amber-500/30',
            'icon_bg' => 'bg-amber-100 dark:bg-amber-500/20',
            'badge_bg' => 'bg-amber-50 dark:bg-amber-500/10',
            'badge_text' => 'text-amber-700 dark:text-amber-300',
            'link_text' => 'text-amber-600 dark:text-amber-400',
            'link_hover' => 'hover:text-amber-800 dark:hover:text-amber-300',
        ],
        'blue' => [
            'border_hover' => 'hover:border-blue-200 dark:hover:border-blue-500/30',
            'icon_bg' => 'bg-blue-100 dark:bg-blue-500/20',
            'badge_bg' => 'bg-blue-50 dark:bg-blue-500/10',
            'badge_text' => 'text-blue-700 dark:text-blue-300',
            'link_text' => 'text-blue-600 dark:text-blue-400',
            'link_hover' => 'hover:text-blue-800 dark:hover:text-blue-300',
        ],
        'cyan' => [
            'border_hover' => 'hover:border-cyan-200 dark:hover:border-cyan-500/30',
            'icon_bg' => 'bg-cyan-100 dark:bg-cyan-500/20',
            'badge_bg' => 'bg-cyan-50 dark:bg-cyan-500/10',
            'badge_text' => 'text-cyan-700 dark:text-cyan-300',
            'link_text' => 'text-cyan-600 dark:text-cyan-400',
            'link_hover' => 'hover:text-cyan-800 dark:hover:text-cyan-300',
        ],
        'emerald' => [
            'border_hover' => 'hover:border-emerald-200 dark:hover:border-emerald-500/30',
            'icon_bg' => 'bg-emerald-100 dark:bg-emerald-500/20',
            'badge_bg' => 'bg-emerald-50 dark:bg-emerald-500/10',
            'badge_text' => 'text-emerald-700 dark:text-emerald-300',
            'link_text' => 'text-emerald-600 dark:text-emerald-400',
            'link_hover' => 'hover:text-emerald-800 dark:hover:text-emerald-300',
        ],
        'fuchsia' => [
            'border_hover' => 'hover:border-fuchsia-200 dark:hover:border-fuchsia-500/30',
            'icon_bg' => 'bg-fuchsia-100 dark:bg-fuchsia-500/20',
            'badge_bg' => 'bg-fuchsia-50 dark:bg-fuchsia-500/10',
            'badge_text' => 'text-fuchsia-700 dark:text-fuchsia-300',
            'link_text' => 'text-fuchsia-600 dark:text-fuchsia-400',
            'link_hover' => 'hover:text-fuchsia-800 dark:hover:text-fuchsia-300',
        ],
        'green' => [
            'border_hover' => 'hover:border-green-200 dark:hover:border-green-500/30',
            'icon_bg' => 'bg-green-100 dark:bg-green-500/20',
            'badge_bg' => 'bg-green-50 dark:bg-green-500/10',
            'badge_text' => 'text-green-700 dark:text-green-300',
            'link_text' => 'text-green-600 dark:text-green-400',
            'link_hover' => 'hover:text-green-800 dark:hover:text-green-300',
        ],
        'indigo' => [
            'border_hover' => 'hover:border-indigo-200 dark:hover:border-indigo-500/30',
            'icon_bg' => 'bg-indigo-100 dark:bg-indigo-500/20',
            'badge_bg' => 'bg-indigo-50 dark:bg-indigo-500/10',
            'badge_text' => 'text-indigo-700 dark:text-indigo-300',
            'link_text' => 'text-indigo-600 dark:text-indigo-400',
            'link_hover' => 'hover:text-indigo-800 dark:hover:text-indigo-300',
        ],
        'lime' => [
            'border_hover' => 'hover:border-lime-200 dark:hover:border-lime-500/30',
            'icon_bg' => 'bg-lime-100 dark:bg-lime-500/20',
            'badge_bg' => 'bg-lime-50 dark:bg-lime-500/10',
            'badge_text' => 'text-lime-700 dark:text-lime-300',
            'link_text' => 'text-lime-600 dark:text-lime-400',
            'link_hover' => 'hover:text-lime-800 dark:hover:text-lime-300',
        ],
        'orange' => [
            'border_hover' => 'hover:border-orange-200 dark:hover:border-orange-500/30',
            'icon_bg' => 'bg-orange-100 dark:bg-orange-500/20',
            'badge_bg' => 'bg-orange-50 dark:bg-orange-500/10',
            'badge_text' => 'text-orange-700 dark:text-orange-300',
            'link_text' => 'text-orange-600 dark:text-orange-400',
            'link_hover' => 'hover:text-orange-800 dark:hover:text-orange-300',
        ],
        'pink' => [
            'border_hover' => 'hover:border-pink-200 dark:hover:border-pink-500/30',
            'icon_bg' => 'bg-pink-100 dark:bg-pink-500/20',
            'badge_bg' => 'bg-pink-50 dark:bg-pink-500/10',
            'badge_text' => 'text-pink-700 dark:text-pink-300',
            'link_text' => 'text-pink-600 dark:text-pink-400',
            'link_hover' => 'hover:text-pink-800 dark:hover:text-pink-300',
        ],
        'purple' => [
            'border_hover' => 'hover:border-purple-200 dark:hover:border-purple-500/30',
            'icon_bg' => 'bg-purple-100 dark:bg-purple-500/20',
            'badge_bg' => 'bg-purple-50 dark:bg-purple-500/10',
            'badge_text' => 'text-purple-700 dark:text-purple-300',
            'link_text' => 'text-purple-600 dark:text-purple-400',
            'link_hover' => 'hover:text-purple-800 dark:hover:text-purple-300',
        ],
        'red' => [
            'border_hover' => 'hover:border-red-200 dark:hover:border-red-500/30',
            'icon_bg' => 'bg-red-100 dark:bg-red-500/20',
            'badge_bg' => 'bg-red-50 dark:bg-red-500/10',
            'badge_text' => 'text-red-700 dark:text-red-300',
            'link_text' => 'text-red-600 dark:text-red-400',
            'link_hover' => 'hover:text-red-800 dark:hover:text-red-300',
        ],
        'rose' => [
            'border_hover' => 'hover:border-rose-200 dark:hover:border-rose-500/30',
            'icon_bg' => 'bg-rose-100 dark:bg-rose-500/20',
            'badge_bg' => 'bg-rose-50 dark:bg-rose-500/10',
            'badge_text' => 'text-rose-700 dark:text-rose-300',
            'link_text' => 'text-rose-600 dark:text-rose-400',
            'link_hover' => 'hover:text-rose-800 dark:hover:text-rose-300',
        ],
        'sky' => [
            'border_hover' => 'hover:border-sky-200 dark:hover:border-sky-500/30',
            'icon_bg' => 'bg-sky-100 dark:bg-sky-500/20',
            'badge_bg' => 'bg-sky-50 dark:bg-sky-500/10',
            'badge_text' => 'text-sky-700 dark:text-sky-300',
            'link_text' => 'text-sky-600 dark:text-sky-400',
            'link_hover' => 'hover:text-sky-800 dark:hover:text-sky-300',
        ],
        'slate' => [
            'border_hover' => 'hover:border-slate-200 dark:hover:border-slate-500/30',
            'icon_bg' => 'bg-slate-100 dark:bg-slate-500/20',
            'badge_bg' => 'bg-slate-50 dark:bg-slate-500/10',
            'badge_text' => 'text-slate-700 dark:text-slate-300',
            'link_text' => 'text-slate-600 dark:text-slate-400',
            'link_hover' => 'hover:text-slate-800 dark:hover:text-slate-300',
        ],
        'teal' => [
            'border_hover' => 'hover:border-teal-200 dark:hover:border-teal-500/30',
            'icon_bg' => 'bg-teal-100 dark:bg-teal-500/20',
            'badge_bg' => 'bg-teal-50 dark:bg-teal-500/10',
            'badge_text' => 'text-teal-700 dark:text-teal-300',
            'link_text' => 'text-teal-600 dark:text-teal-400',
            'link_hover' => 'hover:text-teal-800 dark:hover:text-teal-300',
        ],
        'violet' => [
            'border_hover' => 'hover:border-violet-200 dark:hover:border-violet-500/30',
            'icon_bg' => 'bg-violet-100 dark:bg-violet-500/20',
            'badge_bg' => 'bg-violet-50 dark:bg-violet-500/10',
            'badge_text' => 'text-violet-700 dark:text-violet-300',
            'link_text' => 'text-violet-600 dark:text-violet-400',
            'link_hover' => 'hover:text-violet-800 dark:hover:text-violet-300',
        ],
        'yellow' => [
            'border_hover' => 'hover:border-yellow-200 dark:hover:border-yellow-500/30',
            'icon_bg' => 'bg-yellow-100 dark:bg-yellow-500/20',
            'badge_bg' => 'bg-yellow-50 dark:bg-yellow-500/10',
            'badge_text' => 'text-yellow-700 dark:text-yellow-300',
            'link_text' => 'text-yellow-600 dark:text-yellow-400',
            'link_hover' => 'hover:text-yellow-800 dark:hover:text-yellow-300',
        ],
    ];
    $classes = $colorMap[$iconColor] ?? $colorMap['cyan'];
@endphp

<div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg {{ $classes['border_hover'] }} transition-all">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl {{ $classes['icon_bg'] }} mb-4">
        {{ $icon }}
    </div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $name }}</h3>
    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $description }}</p>

    @if(!empty($features))
        <div class="flex flex-wrap gap-1.5 mt-3">
            @foreach($features as $feature)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $classes['badge_bg'] }} {{ $classes['badge_text'] }}">{{ $feature }}</span>
            @endforeach
        </div>
    @endif

    @if($blogPost)
        <a href="{{ blog_url('/' . $blogPost->slug) }}" class="inline-flex items-center {{ $classes['link_text'] }} {{ $classes['link_hover'] }} text-sm font-medium mt-3 group">
            Learn more
            <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    @endif
</div>
